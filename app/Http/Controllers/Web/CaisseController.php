<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Commande;
use App\Models\Parametre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

// ══════════════════════════════════════════════════════════════
// MODULE CAISSE
//
// Tables utilisées :
//   commandes : idcommande, idclient, iduser, idtable, typecommande,
//               montant, mode_paiement, datecommande, heurecommande,
//               statut_courant, void
//   lignes    : idligne, idcommande, idmenu, quantite, remise, prix
//   menus     : idmenu, idcategorie, intitule, pu, statut, void
//
// Convention déjà en place ailleurs dans l'app (Dashboard/Statistique) :
//   - une commande est considérée "encaissée" dès que son statut est
//     'Servie' OU 'Livrée' (le CA encaissé = somme de ces commandes)
//   - 'Servie' seule = en attente de remise/encaissement au comptoir
//   - typecommande : 'Standard' | 'A emporter' | 'Livraison'
//
// NB : si l'app gère un workflow de paiement plus fin (ex. un champ
// "encaisse" dédié), adapter calculerEncaissement() en conséquence —
// ce contrôleur reprend la convention déjà utilisée par
// DashboardController::dashboardCaissier() pour rester cohérent.
// ══════════════════════════════════════════════════════════════

class CaisseController extends Controller
{
    // =========================================================
    // PAGE PRINCIPALE — Caisse du jour (ou d'une date choisie)
    // GET /caisse?date=2026-06-30
    // =========================================================

    public function index(Request $request)
    {
        $date = $request->filled('date')
            ? Carbon::parse($request->date)
            : Carbon::today();

        // ── Commandes encaissées (Servie ou Livrée) ───────────────
        $commandesEncaissees = Commande::whereDate('datecommande', $date)
            ->whereIn('statut_courant', ['Servie', 'Livrée'])
            ->whereNull('void')
            ->with(['lignes.menu', 'table', 'client'])
            ->orderByDesc('updated_at')
            ->get();

        $totalCaisse  = (float) $commandesEncaissees->sum('montant');
        $nbEncaissees = $commandesEncaissees->count();

        $panierMoyen = $nbEncaissees > 0
            ? (int) round($totalCaisse / $nbEncaissees)
            : 0;

        // ── À encaisser (servies, en attente de passage en caisse) ─
        $aEncaisser = Commande::whereDate('datecommande', $date)
            ->where('statut_courant', 'Servie')
            ->whereNull('void')
            ->with(['table', 'lignes'])
            ->orderBy('created_at')
            ->get();

        // ── Commandes encore actives (ni terminées, ni annulées) ──
        $commandesActives = Commande::whereDate('datecommande', $date)
            ->whereNotIn('statut_courant', ['Servie', 'Livrée', 'Annulée'])
            ->whereNull('void')
            ->with(['table', 'lignes'])
            ->orderByDesc('created_at')
            ->get();

        // ── Annulées du jour (contrôle caisse) ────────────────────
        $nbAnnulees = Commande::whereDate('datecommande', $date)
            ->where('statut_courant', 'Annulée')
            ->whereNull('void')
            ->count();

        // ── Répartition par type (Standard / A emporter / Livraison)
        $dataRepartition = $this->repartitionParType($date);

        // ── Répartition par mode de paiement ──────────────────────
        $parModePaiement = $commandesEncaissees
            ->groupBy(fn ($c) => $c->mode_paiement ?? 'Espèces')
            ->map(fn ($groupe) => $groupe->sum('montant'))
            ->sortDesc();

        // ── [AJOUT] Informations du restaurant (table parametres) ─
        $parametres = $this->chargerParametres();

        return view('caisse.index', compact(
            'date',
            'commandesEncaissees',
            'totalCaisse',
            'nbEncaissees',
            'panierMoyen',
            'aEncaisser',
            'commandesActives',
            'nbAnnulees',
            'dataRepartition',
            'parModePaiement',
            'parametres'
        ));
    }

    // =========================================================
    // REÇU PDF — Détail d'une commande encaissée (format ticket)
    // GET /caisse/recu/{commande}
    // =========================================================

    public function genererRecu(Commande $commande)
    {
        if ($commande->void) {
            abort(404);
        }

        $commande->load(['lignes.menu', 'table', 'client', 'serveur']);

        // ── [AJOUT] Informations du restaurant (table parametres) ─
        $parametres = $this->chargerParametres();

        $pdf = Pdf::loadView('caisse.recu-pdf', compact('commande', 'parametres'))
            ->setPaper([0, 0, 226.77, 600], 'portrait'); // ~80mm largeur ticket

        return $pdf->stream("recu-{$commande->reference}.pdf");
    }

    // =========================================================
    // RAPPORT PDF — Rapport de caisse (rapport Z) pour une date
    // GET /caisse/rapport?date=2026-06-30
    // =========================================================

    public function rapport(Request $request)
    {
        $date = $request->filled('date')
            ? Carbon::parse($request->date)
            : Carbon::today();

        $commandesJour = Commande::whereDate('datecommande', $date)
            ->whereNull('void')
            ->with(['lignes.menu', 'table'])
            ->orderBy('heurecommande')
            ->get();

        $encaissees = $commandesJour->whereIn('statut_courant', ['Servie', 'Livrée']);

        $totalCaisse  = (float) $encaissees->sum('montant');
        $nbEncaissees = $encaissees->count();
        $nbAnnulees   = $commandesJour->where('statut_courant', 'Annulée')->count();
        $panierMoyen  = $nbEncaissees > 0 ? (int) round($totalCaisse / $nbEncaissees) : 0;

        $dataRepartition = $this->repartitionParType($date);

        $parModePaiement = $encaissees
            ->groupBy(fn ($c) => $c->mode_paiement ?? 'Espèces')
            ->map(fn ($groupe) => $groupe->sum('montant'))
            ->sortDesc();

        // ── [AJOUT] Informations du restaurant (table parametres) ─
        $parametres = $this->chargerParametres();

        $pdf = Pdf::loadView('caisse.rapport-pdf', compact(
            'date',
            'commandesJour',
            'encaissees',
            'totalCaisse',
            'nbEncaissees',
            'nbAnnulees',
            'panierMoyen',
            'dataRepartition',
            'parModePaiement',
            'parametres'
        ))->setPaper('a4', 'portrait');

        return $pdf->stream('rapport-caisse-' . $date->format('Y-m-d') . '.pdf');
    }

    // =========================================================
    // CLÔTURER LA CAISSE DU JOUR
    // GET /caisse/cloturer
    //
    // NB : le bouton "Clôturer" du dashboard appelle actuellement
    // cette route via window.location.href (GET). Si la route est
    // déclarée en POST côté routes/web.php, il faudra adapter ce
    // bouton pour soumettre un formulaire (recommandé pour une
    // action qui modifie un état). Sinon, déclarer la route en GET
    // pour rester compatible avec le bouton existant.
    // =========================================================

    public function cloturer(Request $request)
    {
        if (!in_array(Auth::user()->role, ['Administrateur', 'Caissier'])) {
            abort(403, 'Seul un Administrateur ou Caissier peut clôturer la caisse.');
        }

        $today = Carbon::today();

        // On refuse la clôture s'il reste des commandes actives non soldées
        $nbActives = Commande::whereDate('datecommande', $today)
            ->whereNotIn('statut_courant', ['Servie', 'Livrée', 'Annulée'])
            ->whereNull('void')
            ->count();

        if ($nbActives > 0) {
            return back()->with('error',
                "Impossible de clôturer : {$nbActives} commande(s) encore active(s) aujourd'hui."
            );
        }

        $nbAEncaisser = Commande::whereDate('datecommande', $today)
            ->where('statut_courant', 'Servie')
            ->whereNull('void')
            ->count();

        if ($nbAEncaisser > 0) {
            return back()->with('error',
                "Impossible de clôturer : {$nbAEncaisser} commande(s) en attente d'encaissement."
            );
        }

        Log::info('Caisse clôturée', [
            'date' => $today->toDateString(),
            'user' => Auth::user()->email,
        ]);

        // Redirige vers le rapport Z du jour (téléchargement immédiat)
        return redirect()
            ->route('caisse.rapport', ['date' => $today->format('Y-m-d')])
            ->with('success', 'Caisse clôturée avec succès. Le rapport Z a été généré.');
    }

    // =========================================================
    // MÉTHODES PRIVÉES
    // =========================================================

    /**
     * Répartition des commandes du jour par type
     * (Standard / A emporter / Livraison), tous statuts confondus
     * hors void.
     */
    private function repartitionParType(Carbon $date): array
    {
        $repartition = Commande::whereDate('datecommande', $date)
            ->whereNull('void')
            ->select('typecommande', DB::raw('COUNT(*) as total'))
            ->groupBy('typecommande')
            ->pluck('total', 'typecommande')
            ->toArray();

        return [
            'Standard'   => $repartition['Standard']   ?? 0,
            'A emporter' => $repartition['A emporter'] ?? 0,
            'Livraison'  => $repartition['Livraison']  ?? 0,
        ];
    }

    /**
     * [AJOUT] Charger les informations du restaurant (table parametres).
     * Repli sur une instance vide si la table n'a pas encore été
     * renseignée, pour éviter toute erreur null dans les vues PDF.
     */
    private function chargerParametres(): Parametre
    {
        return Parametre::first() ?? new Parametre();
    }
}