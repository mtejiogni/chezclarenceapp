<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Commande;
use App\Models\Ligne;
use App\Models\Menu;
use App\Models\Categorie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// ══════════════════════════════════════════════════════════════
// MODULE "MES COMMANDES" — self-service Client
//
// [DÉCISIONS DE CONCEPTION — à valider]
//   - typecommande limité à 'A emporter' | 'Livraison'. Pas de
//     'Standard' : la sélection de table reste un geste du
//     personnel en salle (staff physiquement présent), pas un
//     acte que le client accomplit lui-même à distance.
//   - idclient toujours forcé à Auth::user()->iduser, jamais lu
//     depuis la requête (même principe de sécurité que pour
//     l'inscription publique).
//   - iduser (personnel ayant enregistré) laissé à null : aucun
//     membre du personnel n'intervient dans l'auto-enregistrement.
//   - idtable toujours null (cohérent avec la restriction ci-dessus).
//   - Modification et annulation strictement réservées au statut
//     'En attente' + vérification de propriété (idclient) à
//     chaque action.
//   - Référence générée via Commande::genererReference() (méthode
//     statique centralisée sur le modèle, format 'CMD-XXXXXX'),
//     la même que celle utilisée par CommandeController pour les
//     commandes enregistrées par le personnel.
// ══════════════════════════════════════════════════════════════

class ClientCommandeController extends Controller
{
    private const TYPES_AUTORISES = ['A emporter', 'Livraison'];

    // =========================================================
    // LISTE DE MES COMMANDES
    // GET /mes-commandes
    // =========================================================

    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Commande::where('idclient', $user->iduser)
            ->whereNull('void')
            ->with(['lignes.menu'])
            ->orderByDesc('created_at');

        if ($request->filled('statut')) {
            $query->where('statut_courant', $request->statut);
        }

        $commandes = $query->paginate(10)->withQueryString();

        $nbEnAttente = Commande::where('idclient', $user->iduser)
            ->where('statut_courant', 'En attente')
            ->whereNull('void')
            ->count();

        $totalDepense = Commande::where('idclient', $user->iduser)
            ->whereIn('statut_courant', ['Servie', 'Livrée'])
            ->whereNull('void')
            ->sum('montant');

        $nbTotal = Commande::where('idclient', $user->iduser)->whereNull('void')->count();

        return view('clientcommande.index', compact(
            'commandes',
            'nbEnAttente',
            'totalDepense',
            'nbTotal'
        ));
    }

    // =========================================================
    // FORMULAIRE NOUVELLE COMMANDE
    // GET /mes-commandes/nouvelle
    // =========================================================

    public function create()
    {
        $categories = Categorie::where('statut', 'Activé')
            ->whereNull('void')
            ->orderBy('intitule')
            ->get();

        return view('clientcommande.create', compact('categories'));
    }

    // =========================================================
    // ENREGISTRER UNE NOUVELLE COMMANDE
    // POST /mes-commandes
    // =========================================================

    public function store(Request $request)
    {
        $data = $this->validerRequete($request);

        DB::beginTransaction();

        try {
            $user = Auth::user();

            [$montant, $lignes] = $this->calculerLignes($data['items']);

            $commande = Commande::create([
                'reference'      => Commande::genererReference(),
                'idclient'       => $user->iduser,
                'iduser'         => null,
                'idtable'        => null,
                'typecommande'   => $data['typecommande'],
                'adresse'        => $data['typecommande'] === 'Livraison' ? $data['adresse'] : null,
                'consignes'      => $data['consignes'] ?? null,
                'montant'        => $montant,
                'statut_courant' => 'En attente',
                'datecommande'   => now()->toDateString(),
                'heurecommande'  => now()->format('H:i'),
            ]);

            foreach ($lignes as $ligne) {
                Ligne::create(array_merge($ligne, ['idcommande' => $commande->idcommande]));
            }

            DB::commit();

            Log::info('Commande client auto-enregistrée', [
                'reference' => $commande->reference,
                'client'    => $user->email,
            ]);

            return redirect()->route('mes-commandes.show', $commande->idcommande)
                ->with('success', "Votre commande {$commande->reference} a été enregistrée !");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création commande client', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    // =========================================================
    // DÉTAIL D'UNE DE MES COMMANDES
    // GET /mes-commandes/{commande}
    // =========================================================

    public function show(Commande $commande)
    {
        $this->verifierProprietaire($commande);

        $commande->load(['lignes.menu']);

        return view('clientcommande.show', compact('commande'));
    }

    // =========================================================
    // FORMULAIRE MODIFICATION (uniquement si 'En attente')
    // GET /mes-commandes/{commande}/modifier
    // =========================================================

    public function edit(Commande $commande)
    {
        $this->verifierProprietaire($commande);
        $this->verifierModifiable($commande);

        $commande->load('lignes.menu');

        $categories = Categorie::where('statut', 'Activé')
            ->whereNull('void')
            ->orderBy('intitule')
            ->get();

        return view('clientcommande.edit', compact('commande', 'categories'));
    }

    // =========================================================
    // METTRE À JOUR MA COMMANDE (uniquement si 'En attente')
    // PUT /mes-commandes/{commande}
    // =========================================================

    public function update(Request $request, Commande $commande)
    {
        $this->verifierProprietaire($commande);
        $this->verifierModifiable($commande);

        $data = $this->validerRequete($request);

        DB::beginTransaction();

        try {
            [$montant, $lignes] = $this->calculerLignes($data['items']);

            // Repartir d'un panier propre à chaque modification, plus
            // simple et plus sûr que de tenter un diff ligne à ligne.
            $commande->lignes()->delete();

            foreach ($lignes as $ligne) {
                Ligne::create(array_merge($ligne, ['idcommande' => $commande->idcommande]));
            }

            $commande->update([
                'typecommande' => $data['typecommande'],
                'adresse'      => $data['typecommande'] === 'Livraison' ? $data['adresse'] : null,
                'consignes'    => $data['consignes'] ?? null,
                'montant'      => $montant,
            ]);

            DB::commit();

            Log::info('Commande client modifiée', [
                'reference' => $commande->reference,
                'client'    => Auth::user()->email,
            ]);

            return redirect()->route('mes-commandes.show', $commande->idcommande)
                ->with('success', "Commande {$commande->reference} modifiée avec succès.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur modification commande client', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    // =========================================================
    // ANNULER MA COMMANDE (uniquement si 'En attente')
    // PATCH /mes-commandes/{commande}/annuler
    // =========================================================

    public function annuler(Commande $commande)
    {
        $this->verifierProprietaire($commande);
        $this->verifierModifiable($commande);

        $commande->update(['statut_courant' => 'Annulée']);

        Log::info('Commande client annulée', [
            'reference' => $commande->reference,
            'client'    => Auth::user()->email,
        ]);

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Commande annulée.']);
        }

        return redirect()->route('mes-commandes.index')
            ->with('success', "Commande {$commande->reference} annulée.");
    }

    // =========================================================
    // RECOMMANDER (pré-remplit une nouvelle commande à partir
    // d'une commande passée — bonus UX)
    // GET /mes-commandes/{commande}/recommander
    // =========================================================

    public function recommander(Commande $commande)
    {
        $this->verifierProprietaire($commande);

        $commande->load('lignes.menu');

        $categories = Categorie::where('statut', 'Activé')
            ->whereNull('void')
            ->orderBy('intitule')
            ->get();

        // Ne pré-remplir qu'avec les plats encore actifs aujourd'hui
        $panierInitial = $commande->lignes
            ->filter(fn ($l) => $l->menu && $l->menu->statut === 'Activé' && !$l->menu->void)
            ->map(fn ($l) => [
                'idmenu'   => $l->menu->idmenu,
                'intitule' => $l->menu->intitule,
                'pu'       => $l->menu->pu,
                'quantite' => $l->quantite,
            ])
            ->values();

        return view('clientcommande.create', compact('categories', 'panierInitial'));
    }

    // =========================================================
    // LISTE DES PLATS DISPONIBLES (AJAX, pour le sélecteur)
    // GET /mes-commandes/menu/liste
    //
    // [NOTE] Auto-suffisant plutôt que de réutiliser
    // MenuController::listeActive() : cette dernière est déclarée
    // dans le groupe de routes 'admin' (role:Administrateur) et ne
    // serait donc pas accessible à un Client sans modifier ce
    // groupe. Ce doublon évite d'exposer une route admin au public.
    // =========================================================

    public function menuListe(Request $request)
    {
        $query = Menu::with('categorie')
            ->where('statut', 'Activé')
            ->whereNull('void');

        if ($request->filled('idcategorie')) {
            $query->where('idcategorie', $request->idcategorie);
        }

        if ($request->filled('q')) {
            $query->where('intitule', 'like', '%' . $request->q . '%');
        }

        $menus = $query->orderBy('intitule')->get()->map(fn ($menu) => [
            'idmenu'      => $menu->idmenu,
            'intitule'    => $menu->intitule,
            'description' => $menu->description,
            'pu'          => (float) $menu->pu,
            'photo_url'   => $menu->photo_url ?? null,
            'categorie'   => $menu->categorie->intitule ?? null,
        ]);

        return response()->json([
            'success' => true,
            'data'    => $menus,
        ]);
    }

    // =========================================================
    // MÉTHODES PRIVÉES
    // =========================================================

    private function validerRequete(Request $request): array
    {
        return $request->validate([
            'typecommande'      => 'required|in:' . implode(',', self::TYPES_AUTORISES),
            'adresse'           => 'required_if:typecommande,Livraison|nullable|string|max:300',
            'consignes'         => 'nullable|string|max:500',
            'items'             => 'required|array|min:1',
            'items.*.idmenu'    => 'required|integer|exists:menus,idmenu',
            'items.*.quantite'  => 'required|integer|min:1|max:20',
        ], [
            'typecommande.required' => 'Choisissez un type de commande.',
            'typecommande.in'       => 'Type de commande invalide.',
            'adresse.required_if'   => 'L\'adresse de livraison est obligatoire.',
            'items.required'        => 'Votre panier est vide.',
            'items.min'             => 'Votre panier est vide.',
            'items.*.idmenu.exists' => 'Un des plats sélectionnés n\'existe plus.',
        ]);
    }

    /**
     * Calcule le montant total et prépare les lignes à créer,
     * en revérifiant côté serveur que chaque plat est toujours
     * actif (jamais confiance au prix envoyé par le client).
     */
    private function calculerLignes(array $items): array
    {
        $montant = 0;
        $lignes  = [];

        foreach ($items as $item) {
            $menu = Menu::where('idmenu', $item['idmenu'])
                ->where('statut', 'Activé')
                ->whereNull('void')
                ->first();

            if (!$menu) {
                throw new \Exception("Un des plats sélectionnés n'est plus disponible.");
            }

            $prixLigne = $menu->pu * $item['quantite'];
            $montant  += $prixLigne;

            $lignes[] = [
                'idmenu'   => $menu->idmenu,
                'quantite' => $item['quantite'],
                'remise'   => 0,
                'prix'     => $prixLigne,
            ];
        }

        return [$montant, $lignes];
    }

    private function verifierProprietaire(Commande $commande): void
    {
        if ($commande->idclient !== Auth::user()->iduser) {
            abort(403, 'Cette commande ne vous appartient pas.');
        }
    }

    private function verifierModifiable(Commande $commande): void
    {
        if ($commande->statut_courant !== 'En attente') {
            abort(403, "Cette commande ne peut plus être modifiée ou annulée (statut actuel : {$commande->statut_courant}).");
        }
    }
}