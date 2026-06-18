<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Commande;
use App\Models\Ligne;
use App\Models\Menu;
use App\Models\TableResto;
use App\Models\Categorie;
use App\Models\Statut;
use App\Models\Historique;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CommandeController extends Controller
{
    // =========================================================
    // LISTE DES COMMANDES
    // =========================================================

    public function index(Request $request)
    {
        $query = Commande::with(['client', 'serveur', 'table', 'lignes.menu'])
            ->whereNull('void')
            ->orderByDesc('created_at');

        if ($request->filled('statut')) {
            $query->where('statut_courant', $request->statut);
        }

        if ($request->filled('type')) {
            $query->where('typecommande', $request->type);
        }

        if ($request->filled('date')) {
            $query->whereDate('datecommande', $request->date);
        } else {
            $query->whereDate('datecommande', today());
        }

        if ($request->filled('table')) {
            $query->where('idtable', $request->table);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('reference', 'like', "%{$q}%")
                    ->orWhereHas('client', function ($c) use ($q) {
                        $c->where('nom', 'like', "%{$q}%")
                          ->orWhere('prenom', 'like', "%{$q}%")
                          ->orWhere('telephone', 'like', "%{$q}%");
                    });
            });
        }

        $commandes = $query->paginate(15)->withQueryString();

        $statuts = Statut::orderBy('priorite')->whereNull('void')->get();
        $tables  = TableResto::whereNull('void')->orderBy('intitule')->get();

        $compteurs = Commande::whereDate('datecommande', today())
            ->whereNull('void')
            ->select('statut_courant', DB::raw('COUNT(*) as total'))
            ->groupBy('statut_courant')
            ->pluck('total', 'statut_courant');

        return view('commande.index', compact(
            'commandes',
            'statuts',
            'tables',
            'compteurs'
        ));
    }

    // =========================================================
    // FORMULAIRE NOUVELLE COMMANDE
    // =========================================================

    public function create(Request $request)
    {
        $tables = $this->chargerTablesAvecStatut();

        $categories = Categorie::with(['menus' => function ($q) {
            $q->where('statut', 'Activé')->whereNull('void')->orderBy('intitule');
        }])
        ->where('statut', 'Activé')
        ->whereNull('void')
        ->orderBy('intitule')
        ->get();

        $menus = Menu::with('categorie')
            ->where('statut', 'Activé')
            ->whereNull('void')
            ->orderBy('intitule')
            ->get();

        $tablePreselect = $request->query('table');

        $clients = User::where('role', 'Client')
            ->where('statut', 'Activé')
            ->whereNull('void')
            ->orderBy('nom')
            ->get();

        return view('commande.create', compact(
            'tables',
            'categories',
            'menus',
            'tablePreselect',
            'clients'
        ));
    }

    // =========================================================
    // ENREGISTRER UNE COMMANDE
    // =========================================================

    public function store(Request $request)
    {
        $this->validerRequete($request);

        DB::beginTransaction();

        try {
            $panier = json_decode($request->panier, true);

            if (empty($panier)) {
                return back()->withErrors(['panier' => 'Le panier est vide.']);
            }

            // Vérifier disponibilité de chaque plat
            $this->verifierDisponibilitePlats($panier);

            // Créer ou retrouver le client pour une livraison
            $idclient = $this->gererClientLivraison($request);

            // Créer la commande
            $commande = Commande::create([
                'idclient'       => $idclient,
                'iduser'         => Auth::id(),
                'idtable'        => $request->typecommande === 'Standard' ? $request->idtable : null,
                'typecommande'   => $request->typecommande,
                'reference'      => Commande::genererReference(),
                'montant'        => $request->montant,
                'adresse'        => $request->adresse ?? null,
                'consignes'      => $request->consignes ?? null,
                'mode_paiement'  => $request->mode_paiement ?? 'Espèces',
                'heurecommande'  => now()->format('H:i:s'),
                'datecommande'   => now()->format('Y-m-d'),
                'statut_courant' => 'En attente',
            ]);

            // Créer les lignes
            $this->creerLignes($commande, $panier);

            // Historique initial
            $this->enregistrerHistorique(
                $commande,
                'En attente',
                'Commande créée par ' . Auth::user()->prenom . ' ' . Auth::user()->nom
            );

            DB::commit();

            Log::info('Commande créée', [
                'reference' => $commande->reference,
                'montant'   => $commande->montant,
                'type'      => $commande->typecommande,
                'user'      => Auth::user()->email,
            ]);

            return redirect()->route('commandes.show', $commande->idcommande)
                ->with('success', "Commande {$commande->reference} créée avec succès !");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création commande', [
                'error' => $e->getMessage(),
                'user'  => Auth::user()->email,
            ]);
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la création : ' . $e->getMessage());
        }
    }

    // =========================================================
    // DÉTAIL D'UNE COMMANDE
    // =========================================================

    public function show(Commande $commande)
    {
        if ($commande->void) {
            abort(404);
        }

        $commande->load([
            'client',
            'serveur',
            'table',
            'lignes.menu.categorie',
            'historiques.statut',
        ]);

        // Réponse JSON pour le panneau latéral AJAX
        if (request()->expectsJson()) {
            return response()->json([
                'success'  => true,
                'commande' => $commande,
            ]);
        }

        $statuts        = Statut::orderBy('priorite')->whereNull('void')->get();
        $prochainStatut = $this->determinerProchainStatut(
            $commande->statut_courant,
            $commande->typecommande
        );

        return view('commande.show', compact(
            'commande',
            'statuts',
            'prochainStatut'
        ));
    }

    // =========================================================
    // FORMULAIRE MODIFICATION D'UNE COMMANDE
    // =========================================================

    public function edit(Commande $commande)
    {
        if ($commande->void) {
            abort(404);
        }

        // Seules les commandes encore modifiables peuvent être éditées
        if (!$commande->estModifiable()) {
            return redirect()->route('commandes.show', $commande->idcommande)
                ->with('error', "La commande {$commande->reference} ne peut plus être modifiée (statut : {$commande->statut_courant}).");
        }

        $commande->load(['lignes.menu.categorie', 'client', 'table']);

        $tables = $this->chargerTablesAvecStatut($commande->idtable);

        $categories = Categorie::with(['menus' => function ($q) {
            $q->where('statut', 'Activé')->whereNull('void')->orderBy('intitule');
        }])
        ->where('statut', 'Activé')
        ->whereNull('void')
        ->orderBy('intitule')
        ->get();

        $menus = Menu::with('categorie')
            ->where('statut', 'Activé')
            ->whereNull('void')
            ->orderBy('intitule')
            ->get();

        $clients = User::where('role', 'Client')
            ->where('statut', 'Activé')
            ->whereNull('void')
            ->orderBy('nom')
            ->get();

        return view('commande.edit', compact(
            'commande',
            'tables',
            'categories',
            'menus',
            'clients'
        ));
    }

    // =========================================================
    // METTRE À JOUR UNE COMMANDE
    // =========================================================

    public function update(Request $request, Commande $commande)
    {
        if ($commande->void) {
            abort(404);
        }

        // Vérifier que la commande est encore modifiable
        if (!$commande->estModifiable()) {
            return redirect()->route('commandes.show', $commande->idcommande)
                ->with('error', "La commande {$commande->reference} ne peut plus être modifiée.");
        }

        $this->validerRequete($request);

        DB::beginTransaction();

        try {
            $panier = json_decode($request->panier, true);

            if (empty($panier)) {
                return back()->withErrors(['panier' => 'Le panier est vide.']);
            }

            // Vérifier disponibilité des plats
            $this->verifierDisponibilitePlats($panier);

            // Gérer le client si livraison
            $idclient = $this->gererClientLivraison($request, $commande);

            // Mettre à jour les champs de la commande
            $commande->update([
                'idclient'      => $idclient ?? $commande->idclient,
                'idtable'       => $request->typecommande === 'Standard' ? $request->idtable : null,
                'typecommande'  => $request->typecommande,
                'montant'       => $request->montant,
                'adresse'       => $request->adresse ?? null,
                'consignes'     => $request->consignes ?? null,
                'mode_paiement' => $request->mode_paiement ?? $commande->mode_paiement,
            ]);

            // Supprimer toutes les anciennes lignes et recréer
            Ligne::where('idcommande', $commande->idcommande)->delete();
            $this->creerLignes($commande, $panier);

            // Enregistrer dans l'historique
            $this->enregistrerHistorique(
                $commande,
                $commande->statut_courant,
                'Commande modifiée par ' . Auth::user()->prenom . ' ' . Auth::user()->nom
            );

            DB::commit();

            Log::info('Commande modifiée', [
                'reference' => $commande->reference,
                'montant'   => $commande->montant,
                'user'      => Auth::user()->email,
            ]);

            return redirect()->route('commandes.show', $commande->idcommande)
                ->with('success', "Commande {$commande->reference} modifiée avec succès !");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur modification commande', [
                'error' => $e->getMessage(),
                'user'  => Auth::user()->email,
            ]);
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la modification : ' . $e->getMessage());
        }
    }

    // =========================================================
    // CHANGER LE STATUT D'UNE COMMANDE
    // =========================================================

    public function updateStatut(Request $request, Commande $commande)
    {
        $request->validate([
            'statut'      => 'required|string|max:128',
            'description' => 'nullable|string|max:500',
        ], [
            'statut.required' => 'Le statut est obligatoire.',
        ]);

        // Annulation : Admin/Caissier uniquement avec justification
        if ($request->statut === 'Annulée') {
            if (!in_array(Auth::user()->role, ['Administrateur', 'Caissier'])) {
                return $this->repondre(false,
                    'Seul un Administrateur ou Caissier peut annuler une commande.'
                );
            }
            $request->validate([
                'description' => 'required|string|min:5|max:500',
            ], [
                'description.required' => 'Une justification est obligatoire pour annuler.',
                'description.min'      => 'La justification doit contenir au moins 5 caractères.',
            ]);
        }

        // Commande déjà clôturée
        if (in_array($commande->statut_courant, ['Livrée', 'Servie', 'Annulée'])) {
            return $this->repondre(false,
                'Cette commande est déjà clôturée et ne peut plus être modifiée.'
            );
        }

        $statut = Statut::where('intitule', $request->statut)->first();
        if (!$statut) {
            return $this->repondre(false, 'Statut invalide.');
        }

        DB::beginTransaction();

        try {
            $ancienStatut = $commande->statut_courant;

            $commande->update(['statut_courant' => $request->statut]);

            Historique::create([
                'idcommande'  => $commande->idcommande,
                'idstatut'    => $statut->idstatut,
                'description' => $request->description
                    ?? "Statut changé de « {$ancienStatut} » à « {$request->statut} » par " . Auth::user()->prenom,
            ]);

            DB::commit();

            Log::info('Statut commande modifié', [
                'reference' => $commande->reference,
                'ancien'    => $ancienStatut,
                'nouveau'   => $request->statut,
                'user'      => Auth::user()->email,
            ]);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Statut mis à jour : {$request->statut}",
                    'statut'  => $request->statut,
                ]);
            }

            return back()->with('success',
                "Statut de la commande {$commande->reference} mis à jour : {$request->statut}"
            );

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur mise à jour statut', ['error' => $e->getMessage()]);
            return $this->repondre(false, $e->getMessage(), 500);
        }
    }



    // =========================================================
    // NOTER UNE COMMANDE
    // =========================================================
    
    public function noter(Request $request, Commande $commande)
    {
        $request->validate([
            'note'         => 'required|integer|min:1|max:5',
            'commentaires' => 'nullable|string|max:500',
        ]);

        $commande->update([
            'note'         => $request->note,
            'commentaires' => $request->commentaires,
        ]);

        return back()->with('success', 'Note enregistrée. Merci !');
    }


    
    // =========================================================
    // SUPPRIMER UNE COMMANDE (Soft Delete — Admin uniquement)
    // =========================================================

    public function destroy(Commande $commande)
    {
        // Seul l'Admin peut supprimer une commande
        if (Auth::user()->role !== 'Administrateur') {
            return $this->repondre(false, 'Accès refusé.', 403);
        }

        // Interdire la suppression d'une commande active
        if (!in_array($commande->statut_courant, ['Servie', 'Livrée', 'Annulée'])) {
            return back()->with('error',
                "Impossible de supprimer une commande active (statut : {$commande->statut_courant}). Annulez-la d'abord."
            );
        }

        DB::beginTransaction();

        try {
            $commande->update([
                'void'       => '1',
                'deleted_at' => now(),
            ]);

            DB::commit();

            Log::info('Commande supprimée', [
                'reference' => $commande->reference,
                'user'      => Auth::user()->email,
            ]);

            return redirect()->route('commandes.index')
                ->with('success', "Commande {$commande->reference} supprimée.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur suppression commande', ['error' => $e->getMessage()]);
            return back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    // =========================================================
    // ÉCRAN CUISINE (bons de préparation)
    // =========================================================

    public function cuisine()
    {
        $today = Carbon::today();

        $commandesEnAttente = Commande::where('statut_courant', 'En attente')
            ->whereDate('datecommande', $today)
            ->whereNull('void')
            ->with(['lignes.menu', 'table'])
            ->orderBy('heurecommande')
            ->get();

        $enPreparation = Commande::where('statut_courant', 'En préparation')
            ->whereDate('datecommande', $today)
            ->whereNull('void')
            ->with(['lignes.menu', 'table'])
            ->orderBy('heurecommande')
            ->get();

        $terminees = Commande::whereIn('statut_courant', ['Servie', 'Livrée', 'Expédiée'])
            ->whereDate('datecommande', $today)
            ->whereNull('void')
            ->count();

        $platsTop = DB::table('lignes')
            ->join('menus', 'lignes.idmenu', '=', 'menus.idmenu')
            ->join('commandes', 'lignes.idcommande', '=', 'commandes.idcommande')
            ->whereDate('commandes.datecommande', $today)
            ->whereNull('commandes.void')
            ->select('menus.intitule', DB::raw('SUM(lignes.quantite) as total'))
            ->groupBy('menus.idmenu', 'menus.intitule')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        return view('cuisine.index', compact(
            'commandesEnAttente',
            'enPreparation',
            'terminees',
            'platsTop'
        ));
    }

    // =========================================================
    // PRENDRE EN CHARGE (En attente → En préparation)
    // =========================================================

    public function prendreEnCharge(Commande $commande)
    {
        if ($commande->statut_courant !== 'En attente') {
            return $this->repondre(false, 'Cette commande n\'est plus en attente.', 422);
        }

        DB::beginTransaction();

        try {
            $statut = Statut::where('intitule', 'En préparation')->first();

            $commande->update(['statut_courant' => 'En préparation']);

            Historique::create([
                'idcommande'  => $commande->idcommande,
                'idstatut'    => $statut->idstatut,
                'description' => 'Prise en charge par la cuisine — ' . Auth::user()->prenom,
            ]);

            DB::commit();

            Log::info('Commande prise en charge', [
                'reference' => $commande->reference,
                'cuisinier' => Auth::user()->email,
            ]);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Commande {$commande->reference} en cours de préparation.",
                ]);
            }

            return back()->with('success', "Commande {$commande->reference} prise en charge !");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur prise en charge', ['error' => $e->getMessage()]);
            return $this->repondre(false, $e->getMessage(), 500);
        }
    }

    // =========================================================
    // MARQUER PRÊTE (En préparation → Servie ou Expédiée)
    // =========================================================

    public function marquerPrete(Commande $commande)
    {
        if ($commande->statut_courant !== 'En préparation') {
            return $this->repondre(false, 'Cette commande n\'est pas en préparation.', 422);
        }

        DB::beginTransaction();

        try {
            $prochainStatut = $commande->typecommande === 'Livraison'
                ? 'Expédiée'
                : 'Servie';

            $statut = Statut::where('intitule', $prochainStatut)->first();

            $commande->update(['statut_courant' => $prochainStatut]);

            Historique::create([
                'idcommande'  => $commande->idcommande,
                'idstatut'    => $statut->idstatut,
                'description' => "Commande marquée « {$prochainStatut} » par " . Auth::user()->prenom,
            ]);

            DB::commit();

            Log::info('Commande marquée prête', [
                'reference' => $commande->reference,
                'statut'    => $prochainStatut,
                'user'      => Auth::user()->email,
            ]);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Commande {$commande->reference} → {$prochainStatut}.",
                    'statut'  => $prochainStatut,
                ]);
            }

            return back()->with('success', "Commande {$commande->reference} → {$prochainStatut} !");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur marquer prête', ['error' => $e->getMessage()]);
            return $this->repondre(false, $e->getMessage(), 500);
        }
    }

    // =========================================================
    // TABLES DISPONIBLES (AJAX)
    // =========================================================

    public function tablesDisponibles()
    {
        $tables = TableResto::whereNull('void')
            ->orderBy('intitule')
            ->get()
            ->map(function ($table) {
                $commandeActive = Commande::where('idtable', $table->idtable)
                    ->whereNotIn('statut_courant', ['Servie', 'Livrée', 'Annulée'])
                    ->whereNull('void')
                    ->exists();

                return [
                    'idtable'     => $table->idtable,
                    'intitule'    => $table->intitule,
                    'description' => $table->description,
                    'occupee'     => $commandeActive,
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $tables,
        ]);
    }

    // =========================================================
    // MÉTHODES PRIVÉES
    // =========================================================

    /**
     * Charger toutes les tables avec leur statut en temps réel.
     * $idTableExclure : en mode édition, la table de la commande
     * actuelle ne doit pas être considérée comme "occupée"
     */
    private function chargerTablesAvecStatut(?int $idTableExclure = null)
    {
        return TableResto::whereNull('void')
            ->orderBy('intitule')
            ->get()
            ->map(function ($table) use ($idTableExclure) {
                // Si c'est la table déjà assignée à la commande en cours
                // d'édition, on la considère comme libre pour éviter
                // de bloquer la modification
                if ($idTableExclure && $table->idtable === $idTableExclure) {
                    $table->occupee          = false;
                    $table->commande_active  = null;
                    $table->montant_en_cours = 0;
                    return $table;
                }

                $commandeActive = Commande::where('idtable', $table->idtable)
                    ->whereNotIn('statut_courant', ['Servie', 'Livrée', 'Annulée'])
                    ->whereNull('void')
                    ->with('lignes')
                    ->latest()
                    ->first();

                $table->occupee          = (bool) $commandeActive;
                $table->commande_active  = $commandeActive;
                $table->montant_en_cours = $commandeActive ? $commandeActive->montant : 0;

                return $table;
            });
    }

    /**
     * Valider la requête de création/modification
     */
    private function validerRequete(Request $request): void
    {
        $request->validate([
            'typecommande' => 'required|in:Standard,Livraison',
            'panier'       => 'required|json',
            'montant'      => 'required|numeric|min:1',
        ], [
            'typecommande.required' => 'Le type de commande est obligatoire.',
            'typecommande.in'       => 'Type de commande invalide.',
            'panier.required'       => 'Le panier est vide.',
            'panier.json'           => 'Format du panier invalide.',
            'montant.required'      => 'Le montant est obligatoire.',
            'montant.min'           => 'Le montant doit être supérieur à 0.',
        ]);

        if ($request->typecommande === 'Standard') {
            $request->validate([
                'idtable' => 'required|exists:tables,idtable',
            ], [
                'idtable.required' => 'Veuillez sélectionner une table.',
                'idtable.exists'   => 'Table introuvable.',
            ]);
        }

        if ($request->typecommande === 'Livraison') {
            $request->validate([
                'adresse'    => 'required|string|max:500',
                'nom_client' => 'required|string|max:128',
                'tel_client' => 'required|string|max:20',
            ], [
                'adresse.required'    => 'L\'adresse de livraison est obligatoire.',
                'nom_client.required' => 'Le nom du client est obligatoire.',
                'tel_client.required' => 'Le téléphone du client est obligatoire.',
            ]);
        }
    }

    /**
     * Vérifier que tous les plats du panier sont actifs et disponibles
     */
    private function verifierDisponibilitePlats(array $panier): void
    {
        foreach ($panier as $item) {
            $menu = Menu::where('idmenu', $item['id'])
                ->where('statut', 'Activé')
                ->whereNull('void')
                ->first();

            if (!$menu) {
                throw new \Exception("Le plat « {$item['nom']} » n'est plus disponible.");
            }
        }
    }

    /**
     * Créer ou retrouver le client pour une commande en livraison
     * En mode modification, on conserve le client existant si aucun
     * nouveau n'est fourni
     */
    private function gererClientLivraison(Request $request, ?Commande $commande = null): ?int
    {
        if ($request->typecommande !== 'Livraison') {
            return null;
        }

        // Client existant sélectionné dans la liste
        if ($request->filled('idclient')) {
            return (int) $request->idclient;
        }

        // Nouveau client : créer ou retrouver par téléphone
        if ($request->filled('tel_client')) {
            $parties = explode(' ', trim($request->nom_client), 2);
            $client  = User::firstOrCreate(
                ['telephone' => $request->tel_client],
                [
                    'nom'      => strtoupper($parties[0] ?? $request->nom_client),
                    'prenom'   => ucfirst($parties[1] ?? ''),
                    'role'     => 'Client',
                    'statut'   => 'Activé',
                    'etat'     => 'Déconnecté',
                    'points'   => 0,
                    'password' => bcrypt($request->tel_client),
                ]
            );
            return $client->iduser;
        }

        // Conserver le client existant en mode modification
        return $commande?->idclient;
    }

    /**
     * Créer les lignes de commande à partir du panier
     */
    private function creerLignes(Commande $commande, array $panier): void
    {
        foreach ($panier as $item) {
            $menu   = Menu::find($item['id']);
            $remise = isset($item['remise']) ? (float) $item['remise'] : 0;
            $prix   = Ligne::calculerPrix($menu->pu, (int) $item['qte'], $remise);

            Ligne::create([
                'idcommande' => $commande->idcommande,
                'idmenu'     => $item['id'],
                'quantite'   => (int) $item['qte'],
                'remise'     => $remise,
                'prix'       => $prix,
            ]);
        }
    }

    /**
     * Enregistrer un événement dans l'historique de la commande
     */
    private function enregistrerHistorique(
        Commande $commande,
        string $intituleStatut,
        string $description
    ): void {
        $statut = Statut::where('intitule', $intituleStatut)->first();

        if ($statut) {
            Historique::create([
                'idcommande'  => $commande->idcommande,
                'idstatut'    => $statut->idstatut,
                'description' => $description,
            ]);
        }
    }

    /**
     * Déterminer le prochain statut logique selon le flux métier
     */
    private function determinerProchainStatut(
        string $statutActuel,
        string $typeCommande
    ): ?string {
        $flux = [
            'Standard' => [
                'En attente'     => 'En préparation',
                'En préparation' => 'Servie',
                'Servie'         => null,
                'Annulée'        => null,
            ],
            'Livraison' => [
                'En attente'     => 'En préparation',
                'En préparation' => 'Expédiée',
                'Expédiée'       => 'Livrée',
                'Livrée'         => null,
                'Annulée'        => null,
            ],
        ];

        return $flux[$typeCommande][$statutActuel] ?? null;
    }

    /**
     * Répondre en JSON ou redirect selon le type de requête
     */
    private function repondre(bool $success, string $message, int $code = 200)
    {
        if (request()->expectsJson()) {
            return response()->json([
                'success' => $success,
                'message' => $message,
            ], $code);
        }

        $type = $success ? 'success' : 'error';
        return back()->with($type, $message);
    }
}