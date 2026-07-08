<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Commande;
use App\Models\Menu;
use App\Models\User;
use App\Models\TableResto;
use App\Models\Categorie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Rediriger vers le bon dashboard selon le rôle
        return match($user->role) {
            'Administrateur' => $this->dashboardAdmin($request),
            'Caissier'       => $this->dashboardCaissier(),
            'Serveur'        => $this->dashboardServeur(),
            'Cuisinier'      => $this->dashboardCuisinier(),
            'Livreur'        => $this->dashboardLivreur(),
            default          => $this->dashboardDefault(),
        };
    }

    // =========================================================
    // DASHBOARD ADMINISTRATEUR
    // =========================================================

    private function dashboardAdmin(Request $request)
    {
        $today     = Carbon::today();
        $debutMois = Carbon::now()->startOfMonth();
        $hier      = Carbon::yesterday();

        // ── Statistiques du jour ──────────────────────────────

        $commandesDuJour = Commande::whereDate('datecommande', $today)
            ->whereNull('void')
            ->count();

        $commandesHier = Commande::whereDate('datecommande', $hier)
            ->whereNull('void')
            ->count();

        $caJour = Commande::whereDate('datecommande', $today)
            ->whereIn('statut_courant', ['Servie', 'Livrée'])
            ->whereNull('void')
            ->sum('montant') ?? 0;

        $caHier = Commande::whereDate('datecommande', $hier)
            ->whereIn('statut_courant', ['Servie', 'Livrée'])
            ->whereNull('void')
            ->sum('montant') ?? 0;

        $caMois = Commande::whereBetween('datecommande', [$debutMois, Carbon::now()])
            ->whereIn('statut_courant', ['Servie', 'Livrée'])
            ->whereNull('void')
            ->sum('montant') ?? 0;

        // ── Commandes actives ─────────────────────────────────

        $commandesEnAttente = Commande::where('statut_courant', 'En attente')
            ->whereNull('void')
            ->count();

        $commandesEnPreparation = Commande::where('statut_courant', 'En préparation')
            ->whereNull('void')
            ->count();

        $livraisonsEnCours = Commande::where('typecommande', 'Livraison')
            ->whereIn('statut_courant', ['En attente', 'En préparation', 'Expédiée'])
            ->whereNull('void')
            ->count();

        // ── Tables ────────────────────────────────────────────

        $totalTables = TableResto::whereNull('void')->count();

        $tablesOccupees = TableResto::whereNull('void')
            ->whereHas('commandesActives')
            ->count();

        $tablesLibres = $totalTables - $tablesOccupees;

        // ── Utilisateurs ──────────────────────────────────────

        $totalUsers    = User::whereNull('void')->where('statut', 'Activé')->count();
        $usersConnectes = User::where('etat', 'Connecté')->whereNull('void')->count();

        // ── Graphique : ventes des 7 derniers jours ───────────

        $ventesSet = Commande::whereBetween('datecommande', [
                Carbon::now()->subDays(6)->startOfDay(),
                Carbon::now()->endOfDay()
            ])
            ->whereIn('statut_courant', ['Servie', 'Livrée'])
            ->whereNull('void')
            ->select(
                DB::raw('DATE(datecommande) as date'),
                DB::raw('SUM(montant) as total'),
                DB::raw('COUNT(*) as nb')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Remplir les jours sans ventes avec 0
        $labelsVentes = [];
        $dataVentes   = [];
        $dataNb       = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $labelsVentes[] = Carbon::now()->subDays($i)->translatedFormat('D d/m');
            $dataVentes[]   = isset($ventesSet[$date])
                ? (float) $ventesSet[$date]->total
                : 0;
            $dataNb[]       = isset($ventesSet[$date])
                ? (int) $ventesSet[$date]->nb
                : 0;
        }

        // ── Graphique : répartition par type de commande ──────

        $repartition = Commande::whereDate('datecommande', $today)
            ->whereNull('void')
            ->select('typecommande', DB::raw('COUNT(*) as total'))
            ->groupBy('typecommande')
            ->pluck('total', 'typecommande')
            ->toArray();

        $dataRepartition = [
            'Standard'   => $repartition['Standard']   ?? 0,
            'A emporter' => $repartition['A emporter'] ?? 0,
            'Livraison'  => $repartition['Livraison']  ?? 0,
        ];

        // ── Top 5 plats les plus vendus (mois en cours) ───────

        $topPlats = DB::table('lignes')
            ->join('menus', 'lignes.idmenu', '=', 'menus.idmenu')
            ->join('commandes', 'lignes.idcommande', '=', 'commandes.idcommande')
            ->whereBetween('commandes.datecommande', [
                $debutMois->format('Y-m-d'),
                Carbon::now()->format('Y-m-d')
            ])
            ->whereNull('commandes.void')
            ->select(
                'menus.intitule',
                DB::raw('SUM(lignes.quantite) as total_vendu'),
                DB::raw('SUM(lignes.prix) as ca_total')
            )
            ->groupBy('menus.idmenu', 'menus.intitule')
            ->orderByDesc('total_vendu')
            ->take(5)
            ->get();

        // ── Données pour l'onglet Commandes ──────────────────
        // ── Les 08 Dernières commandes peu importe le jour

        $dernieresCommandes = Commande::with(['client', 'table', 'serveur'])
            ->whereNull('void')
            ->orderByDesc('created_at')
            ->take(8)
            ->get();

        // ── Évolution vs hier ─────────────────────────────────

        $evolutionCommandes = $commandesHier > 0
            ? round((($commandesDuJour - $commandesHier) / $commandesHier) * 100, 1)
            : ($commandesDuJour > 0 ? 100 : 0);

        $evolutionCA = $caHier > 0
            ? round((($caJour - $caHier) / $caHier) * 100, 1)
            : ($caJour > 0 ? 100 : 0);

        // ── Données pour l'onglet Caisse ──────────────────
        $commandesEncaissees = Commande::whereDate('datecommande', $today)
            ->whereIn('statut_courant', ['Servie', 'Livrée'])
            ->whereNull('void')
            ->with(['lignes.menu', 'table'])
            ->orderByDesc('updated_at')
            ->get();

        $totalCaisse  = $commandesEncaissees->sum('montant');
        $nbEncaissees = $commandesEncaissees->count();

        // Une commande est "à encaisser" tant qu'elle
        // n'est NI 'Servie' NI 'Livrée' (et non annulée) — pas
        // seulement celles au statut 'Servie'.
        $aEncaisser = Commande::whereDate('datecommande', $today)
            ->whereNotIn('statut_courant', ['Servie', 'Livrée', 'Annulée'])
            ->whereNull('void')
            ->with(['table', 'lignes'])
            ->orderBy('created_at')
            ->get();

        $panierMoyen = $nbEncaissees > 0
            ? round($totalCaisse / $nbEncaissees, 0)
            : 0;

        // ── Données pour l'onglet Cuisine ─────────────────
        // $commandesEnAttente plus haut est un ENTIER (compteur KPI/badge),
        // on a donc besoin d'une variable distincte avec les vraies
        // commandes pour pouvoir afficher les bons de préparation.
        $cuisineEnAttente = Commande::where('statut_courant', 'En attente')
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

        // ── Données pour l'onglet Livraisons ──────────────
        $livraisonsAttente = Commande::where('typecommande', 'Livraison')
            ->where('statut_courant', 'En attente')
            ->whereDate('datecommande', $today)
            ->whereNull('void')
            ->with(['client', 'lignes.menu'])
            ->orderBy('heurecommande')
            ->get();

        $livraisonsPrepa = Commande::where('typecommande', 'Livraison')
            ->where('statut_courant', 'En préparation')
            ->whereDate('datecommande', $today)
            ->whereNull('void')
            ->with(['client', 'lignes.menu'])
            ->orderBy('heurecommande')
            ->get();

        $livraisonsEnRoute = Commande::where('typecommande', 'Livraison')
            ->where('statut_courant', 'Expédiée')
            ->whereDate('datecommande', $today)
            ->whereNull('void')
            ->with(['client', 'lignes.menu'])
            ->orderByDesc('updated_at')
            ->get();

        // ── Données pour l'onglet Tables ──────────────────
        $tables = $this->chargerTablesPourDashboard();

        // ── Données pour l'onglet Statistiques ────────────
        $periode = $request->get('periode', 'semaine');

        [$debutStats, $finStats] = match ($periode) {
            'jour' => [Carbon::today(), Carbon::today()],
            'mois' => [Carbon::now()->startOfMonth(), Carbon::today()],
            default => [Carbon::now()->subDays(6)->startOfDay(), Carbon::today()],
        };

        $statsQuery = fn() => Commande::whereNull('void')
            ->whereBetween('datecommande', [
                $debutStats->toDateString(),
                $finStats->toDateString(),
            ]);

        $statsNbCommandes = (int) $statsQuery()
            ->where('statut_courant', '!=', 'Annulée')
            ->count();

        $statsCaTotal = (float) $statsQuery()
            ->whereIn('statut_courant', ['Servie', 'Livrée'])
            ->sum('montant');

        $stats = [
            'ca_total'      => $statsCaTotal,
            'nb_commandes'  => $statsNbCommandes,
            'nb_livraisons' => (int) $statsQuery()
                ->where('typecommande', 'Livraison')
                ->where('statut_courant', '!=', 'Annulée')
                ->count(),
            'panier_moyen'  => $statsNbCommandes > 0
                ? (int) round($statsCaTotal / $statsNbCommandes)
                : 0,
        ];

        return view('dashboard.index', compact(
            'commandesDuJour',
            'caJour',
            'caMois',
            'commandesEnAttente',
            'commandesEnPreparation',
            'livraisonsEnCours',
            'totalTables',
            'tablesOccupees',
            'tablesLibres',
            'totalUsers',
            'usersConnectes',
            'labelsVentes',
            'dataVentes',
            'dataNb',
            'dataRepartition',
            'topPlats',
            'dernieresCommandes',
            'evolutionCommandes',
            'evolutionCA',
            'today',
            'commandesEncaissees',
            'totalCaisse',
            'nbEncaissees',
            'aEncaisser',
            'panierMoyen',
            'cuisineEnAttente',
            'enPreparation',
            'livraisonsAttente',
            'livraisonsPrepa',
            'livraisonsEnRoute',
            'tables',
            'stats',
            'periode'
        ));
    }

    // =========================================================
    // DASHBOARD CAISSIER
    // =========================================================

    private function dashboardCaissier()
    {
        $today = Carbon::today();

        // Commandes du jour encaissées
        $commandesEncaissees = Commande::whereDate('datecommande', $today)
            ->whereIn('statut_courant', ['Servie', 'Livrée'])
            ->whereNull('void')
            ->with(['lignes.menu', 'table'])
            ->orderByDesc('updated_at')
            ->get();

        $totalCaisse   = $commandesEncaissees->sum('montant');
        $nbEncaissees  = $commandesEncaissees->count();

        // Commandes en attente d'encaissement
        // ni Servie ni Livrée ni Annulée
        $aEncaisser = Commande::whereDate('datecommande', $today)
            ->whereNotIn('statut_courant', ['Servie', 'Livrée', 'Annulée'])
            ->whereNull('void')
            ->with(['table', 'lignes'])
            ->orderBy('created_at')
            ->get();

        // Panier moyen du jour
        $panierMoyen = $nbEncaissees > 0
            ? round($totalCaisse / $nbEncaissees, 0)
            : 0;

        // ── Répartition par type pour le graphique de la pane Caisse
        // Manquait ici alors que la vue l'utilise pour TOUT rôle voyant
        // cette pane (Administrateur ET Caissier).
        $repartition = Commande::whereDate('datecommande', $today)
            ->whereNull('void')
            ->select('typecommande', DB::raw('COUNT(*) as total'))
            ->groupBy('typecommande')
            ->pluck('total', 'typecommande')
            ->toArray();

        $dataRepartition = [
            'Standard'   => $repartition['Standard']   ?? 0,
            'A emporter' => $repartition['A emporter'] ?? 0,
            'Livraison'  => $repartition['Livraison']  ?? 0,
        ];

        return view('dashboard.index', compact(
            'commandesEncaissees',
            'totalCaisse',
            'nbEncaissees',
            'aEncaisser',
            'panierMoyen',
            'dataRepartition',
            'today'
        ));
    }

    // =========================================================
    // DASHBOARD SERVEUR
    // =========================================================

    private function dashboardServeur()
    {
        $today   = Carbon::today();
        $serveur = Auth::user();

        // Tables avec leur statut en temps réel
        $tables = $this->chargerTablesPourDashboard();

        // Commandes du serveur connecté aujourd'hui
        $mesCommandes = Commande::where('iduser', $serveur->iduser)
            ->whereDate('datecommande', $today)
            ->whereNull('void')
            ->with(['table', 'lignes.menu'])
            ->orderByDesc('created_at')
            ->get();

        // Commandes en attente de service (toutes tables)
        $commandesEnAttente = Commande::where('statut_courant', 'En attente')
            ->whereDate('datecommande', $today)
            ->whereNull('void')
            ->with(['table', 'lignes.menu'])
            ->orderBy('created_at')
            ->get();

        $commandesPrêtes = Commande::where('statut_courant', 'En préparation')
            ->whereDate('datecommande', $today)
            ->whereNull('void')
            ->with(['table', 'lignes.menu'])
            ->orderBy('created_at')
            ->get();

        return view('dashboard.index', compact(
            'tables',
            'mesCommandes',
            'commandesEnAttente',
            'commandesPrêtes',
            'today'
        ));
    }

    // =========================================================
    // DASHBOARD CUISINIER
    // =========================================================

    private function dashboardCuisinier()
    {
        $today = Carbon::today();

        // Bons de préparation en attente (priorité : heure de commande)
        $commandesEnAttente = Commande::where('statut_courant', 'En attente')
            ->whereDate('datecommande', $today)
            ->whereNull('void')
            ->with(['lignes.menu', 'table'])
            ->orderBy('heurecommande')
            ->get();

        // Commandes en cours de préparation
        $enPreparation = Commande::where('statut_courant', 'En préparation')
            ->whereDate('datecommande', $today)
            ->whereNull('void')
            ->with(['lignes.menu', 'table'])
            ->orderBy('heurecommande')
            ->get();

        // Commandes terminées aujourd'hui par la cuisine
        $terminees = Commande::whereIn('statut_courant', ['Servie', 'Livrée', 'Expédiée'])
            ->whereDate('datecommande', $today)
            ->whereNull('void')
            ->count();

        // Plats les plus commandés aujourd'hui
        $platsTop = DB::table('lignes')
            ->join('menus', 'lignes.idmenu', '=', 'menus.idmenu')
            ->join('commandes', 'lignes.idcommande', '=', 'commandes.idcommande')
            ->whereDate('commandes.datecommande', $today)
            ->whereNull('commandes.void')
            ->select(
                'menus.intitule',
                DB::raw('SUM(lignes.quantite) as total')
            )
            ->groupBy('menus.idmenu', 'menus.intitule')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        return view('dashboard.index', compact(
            'commandesEnAttente',
            'enPreparation',
            'terminees',
            'platsTop',
            'today'
        ));
    }

    // =========================================================
    // DASHBOARD LIVREUR
    // =========================================================

    private function dashboardLivreur()
    {
        $today   = Carbon::today();
        $livreur = Auth::user();

        // Livraisons en attente (non encore assignées)
        $livraisonsAttente = Commande::where('typecommande', 'Livraison')
            ->where('statut_courant', 'En attente')
            ->whereDate('datecommande', $today)
            ->whereNull('void')
            ->with(['client', 'lignes.menu'])
            ->orderBy('heurecommande')
            ->get();

        // Livraisons en préparation
        $livraisonsPrepa = Commande::where('typecommande', 'Livraison')
            ->where('statut_courant', 'En préparation')
            ->whereDate('datecommande', $today)
            ->whereNull('void')
            ->with(['client', 'lignes.menu'])
            ->orderBy('heurecommande')
            ->get();

        // Livraisons expédiées (en route)
        $livraisonsEnRoute = Commande::where('typecommande', 'Livraison')
            ->where('statut_courant', 'Expédiée')
            ->whereDate('datecommande', $today)
            ->whereNull('void')
            ->with(['client', 'lignes.menu'])
            ->orderByDesc('updated_at')
            ->get();

        // Livraisons terminées aujourd'hui
        $livraisonsTerminees = Commande::where('typecommande', 'Livraison')
            ->where('statut_courant', 'Livrée')
            ->whereDate('datecommande', $today)
            ->whereNull('void')
            ->count();

        return view('dashboard.index', compact(
            'livraisonsAttente',
            'livraisonsPrepa',
            'livraisonsEnRoute',
            'livraisonsTerminees',
            'today'
        ));
    }

    // =========================================================
    // HELPER PARTAGÉ — TABLES AVEC STATUT TEMPS RÉEL
    // Utilisé par dashboardAdmin() et dashboardServeur()
    // =========================================================

    private function chargerTablesPourDashboard()
    {
        return TableResto::whereNull('void')
            ->orderBy('intitule')
            ->get()
            ->map(function ($table) {
                // [MODIFIÉ] une table peut désormais accueillir plusieurs
                // commandes actives simultanément (plusieurs convives sur
                // la même table) — on ne prend donc plus seulement la
                // dernière commande, mais on agrège toutes les commandes
                // actives de cette table.
                $commandesActives = Commande::where('idtable', $table->idtable)
                    ->whereNotIn('statut_courant', ['Servie', 'Livrée', 'Annulée'])
                    ->whereNull('void')
                    ->with('lignes')
                    ->orderByDesc('created_at')
                    ->get();

                $table->occupee              = $commandesActives->isNotEmpty();
                $table->commandes_actives    = $commandesActives;
                $table->nb_commandes_actives = $commandesActives->count();
                $table->montant_total        = $commandesActives->sum('montant');

                return $table;
            });
    }

    // =========================================================
    // DASHBOARD PAR DEFAUT (Client ou rôle inconnu)
    // =========================================================

    private function dashboardDefault()
    {
        return view('dashboard.index', [
            'user' => Auth::user(),
        ]);
    }

    // =========================================================
    // DONNEES EN TEMPS REEL (appelé via AJAX toutes les 30s)
    // =========================================================

    public function refresh()
    {
        $today = Carbon::today();

        $data = [
            'commandes_en_attente'    => Commande::where('statut_courant', 'En attente')
                ->whereNull('void')->count(),

            'commandes_en_preparation' => Commande::where('statut_courant', 'En préparation')
                ->whereNull('void')->count(),

            'nb_commandes_jour'       => Commande::whereDate('datecommande', $today)
                ->whereNull('void')->count(),

            'livraisons_en_cours'     => Commande::where('typecommande', 'Livraison')
                ->whereIn('statut_courant', ['En attente', 'En préparation', 'Expédiée'])
                ->whereNull('void')->count(),

            'ca_jour'                 => Commande::whereDate('datecommande', $today)
                ->whereIn('statut_courant', ['Servie', 'Livrée'])
                ->whereNull('void')->sum('montant'),

            'tables_occupees'         => TableResto::whereNull('void')
                ->whereHas('commandesActives')->count(),

            'nb_encaissees'           => Commande::whereDate('datecommande', $today)
                ->whereIn('statut_courant', ['Servie', 'Livrée'])
                ->whereNull('void')->count(),

            'total_caisse'            => Commande::whereDate('datecommande', $today)
                ->whereIn('statut_courant', ['Servie', 'Livrée'])
                ->whereNull('void')->sum('montant'),

            'timestamp'               => now()->format('H:i:s'),
        ];

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }
}