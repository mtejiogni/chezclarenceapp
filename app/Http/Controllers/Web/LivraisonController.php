<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Commande;
use App\Models\Statut;
use App\Models\Historique;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

// ══════════════════════════════════════════════════════════════
// MODULE LIVRAISON — suivi des commandes typecommande = 'Livraison'
//
// Flux :
//   'En attente' → 'En préparation' → 'Expédiée' → 'Livrée'
//
// Les deux premières étapes sont gérées par la Cuisine (voir
// CommandeController::cuisine()/prendreEnCharge()/marquerPrete()) —
// marquerPrete() bascule déjà une commande Livraison en 'Expédiée'
// dès qu'elle est prête, exactement le point d'entrée de cet écran.
//
// Ce module gère donc la suite : une fois 'Expédiée' (récupérée
// par le livreur), updateStatut() permet de la faire avancer vers
// 'Livrée' (remise au client) ou 'Annulée' (livraison échouée).
// ══════════════════════════════════════════════════════════════

class LivraisonController extends Controller
{
    /**
     * Transitions de statut autorisées pour un Livreur, par statut
     * de départ. Toute autre transition est rejetée.
     */
    private const TRANSITIONS_AUTORISEES = [
        'Expédiée' => ['Livrée', 'Annulée'],
    ];

    // =========================================================
    // ÉCRAN PRINCIPAL
    // GET /livraisons
    // =========================================================

    public function index()
    {
        $today = Carbon::today();

        $base = fn (string $statut) => Commande::where('typecommande', 'Livraison')
            ->where('statut_courant', $statut)
            ->whereDate('datecommande', $today)
            ->whereNull('void')
            ->with(['lignes.menu', 'client'])
            ->orderBy('heurecommande');

        // En attente / en préparation : informatif, géré par la cuisine
        $enAttente     = $base('En attente')->get();
        $enPreparation = $base('En préparation')->get();

        // En route : l'étape actionnable pour le livreur
        $enRoute = $base('Expédiée')->orderByDesc('updated_at')->get();

        $livreesAujourdhui = Commande::where('typecommande', 'Livraison')
            ->where('statut_courant', 'Livrée')
            ->whereDate('datecommande', $today)
            ->whereNull('void')
            ->count();

        return view('livraison.index', compact(
            'enAttente',
            'enPreparation',
            'enRoute',
            'livreesAujourdhui'
        ));
    }

    // =========================================================
    // HISTORIQUE DES LIVRAISONS
    // GET /livraisons/historique?date=AAAA-MM-JJ
    // =========================================================

    public function historique(Request $request)
    {
        $date = $request->filled('date')
            ? Carbon::parse($request->date)
            : Carbon::today();

        $livraisons = Commande::where('typecommande', 'Livraison')
            ->whereDate('datecommande', $date)
            ->whereIn('statut_courant', ['Livrée', 'Annulée'])
            ->whereNull('void')
            ->with(['lignes.menu', 'client'])
            ->orderByDesc('updated_at')
            ->get();

        $nbLivrees    = $livraisons->where('statut_courant', 'Livrée')->count();
        $nbAnnulees   = $livraisons->where('statut_courant', 'Annulée')->count();
        $totalMontant = $livraisons->where('statut_courant', 'Livrée')->sum('montant');

        return view('livraison.historique', compact(
            'date',
            'livraisons',
            'nbLivrees',
            'nbAnnulees',
            'totalMontant'
        ));
    }

    // =========================================================
    // METTRE À JOUR LE STATUT D'UNE LIVRAISON
    // PATCH /livraisons/{commande}/statut
    // Body attendu : { "statut": "Livrée" | "Annulée", "description"?: string }
    // =========================================================

    public function updateStatut(Request $request, Commande $commande)
    {
        $request->validate([
            'statut'      => 'required|string|in:Livrée,Annulée',
            'description' => 'nullable|string|max:500',
        ], [
            'statut.required' => 'Le statut cible est obligatoire.',
            'statut.in'       => 'Statut cible invalide pour une livraison.',
        ]);

        if ($commande->typecommande !== 'Livraison') {
            return $this->reponse($commande, false,
                'Cette commande n\'est pas une livraison.'
            );
        }

        $transitionsPossibles = self::TRANSITIONS_AUTORISEES[$commande->statut_courant] ?? [];

        if (!in_array($request->statut, $transitionsPossibles, true)) {
            return $this->reponse($commande, false,
                "Transition non autorisée : « {$commande->statut_courant} » → « {$request->statut} »."
            );
        }

        // Annulation d'une livraison : justification obligatoire
        if ($request->statut === 'Annulée') {
            $request->validate([
                'description' => 'required|string|min:5|max:500',
            ], [
                'description.required' => 'Un motif est obligatoire pour annuler une livraison.',
                'description.min'      => 'Le motif doit contenir au moins 5 caractères.',
            ]);
        }

        DB::beginTransaction();

        try {
            $ancienStatut = $commande->statut_courant;
            $statut       = Statut::where('intitule', $request->statut)->first();

            if (!$statut) {
                DB::rollBack();
                return $this->reponse($commande, false, 'Statut introuvable en base.');
            }

            $commande->update(['statut_courant' => $request->statut]);

            Historique::create([
                'idcommande'  => $commande->idcommande,
                'idstatut'    => $statut->idstatut,
                'description' => $request->description ?? (
                    $request->statut === 'Livrée'
                        ? 'Livraison remise au client par ' . Auth::user()->prenom
                        : "Livraison annulée par " . Auth::user()->prenom
                ),
            ]);

            DB::commit();

            Log::info('Statut livraison modifié', [
                'reference' => $commande->reference,
                'ancien'    => $ancienStatut,
                'nouveau'   => $request->statut,
                'livreur'   => Auth::user()->email,
            ]);

            $message = $request->statut === 'Livrée'
                ? "Commande {$commande->reference} livrée avec succès !"
                : "Livraison {$commande->reference} annulée.";

            return $this->reponse($commande, true, $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur mise à jour statut livraison', ['error' => $e->getMessage()]);
            return $this->reponse($commande, false, $e->getMessage());
        }
    }

    // =========================================================
    // STATUT TEMPS RÉEL (AJAX) — pour rafraîchissement sans reload
    // GET /livraisons/statut-temps-reel
    // =========================================================

    public function statutTempsReel()
    {
        $today = Carbon::today();

        $base = fn (string $statut) => Commande::where('typecommande', 'Livraison')
            ->where('statut_courant', $statut)
            ->whereDate('datecommande', $today)
            ->whereNull('void')
            ->with(['lignes.menu', 'client']);

        $formatter = fn ($cmd) => [
            'idcommande'     => $cmd->idcommande,
            'reference'      => $cmd->reference,
            'heurecommande'  => $cmd->heurecommande,
            'montant'        => (float) $cmd->montant,
            'adresse'        => $cmd->adresse,
            'statut_courant' => $cmd->statut_courant,
            'client'         => $cmd->client ? [
                'nom'       => trim($cmd->client->prenom . ' ' . $cmd->client->nom),
                'telephone' => $cmd->client->telephone,
            ] : null,
            'lignes' => $cmd->lignes->map(fn ($l) => [
                'intitule' => $l->menu->intitule ?? 'Plat supprimé',
                'quantite' => $l->quantite,
            ]),
        ];

        $enAttente     = $base('En attente')->orderBy('heurecommande')->get()->map($formatter);
        $enPreparation = $base('En préparation')->orderBy('heurecommande')->get()->map($formatter);
        $enRoute       = $base('Expédiée')->orderByDesc('updated_at')->get()->map($formatter);

        $livreesAujourdhui = Commande::where('typecommande', 'Livraison')
            ->where('statut_courant', 'Livrée')
            ->whereDate('datecommande', $today)
            ->whereNull('void')
            ->count();

        return response()->json([
            'success'            => true,
            'en_attente'         => $enAttente,
            'en_preparation'     => $enPreparation,
            'en_route'           => $enRoute,
            'livrees_aujourdhui' => $livreesAujourdhui,
            'timestamp'          => now()->format('H:i:s'),
        ]);
    }

    // =========================================================
    // MÉTHODE PRIVÉE
    // =========================================================

    private function reponse(Commande $commande, bool $success, string $message)
    {
        if (request()->expectsJson()) {
            return response()->json([
                'success'    => $success,
                'message'    => $message,
                'idcommande' => $commande->idcommande,
                'statut'     => $commande->statut_courant,
            ], $success ? 200 : 422);
        }

        return back()->with($success ? 'success' : 'error', $message);
    }
}