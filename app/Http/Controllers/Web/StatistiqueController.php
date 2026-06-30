<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Commande;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatistiqueController extends Controller
{
    // ══════════════════════════════════════════════════════════════
    // INDEX — page principale des statistiques
    // GET /admin/statistiques?periode=semaine
    //
    // Tables utilisées :
    //   commandes  : idcommande, idclient, iduser, idtable,
    //                typecommande, montant, datecommande,
    //                heurecommande, statut_courant, void
    //   lignes     : idligne, idcommande, idmenu, quantite,
    //                remise, prix  (← pas prix_unitaire)
    //   menus      : idmenu, idcategorie, intitule, pu, statut, void
    //   categories : idcategorie, intitule, statut, void
    //   users      : iduser, nom, prenom, role, statut, void
    // ══════════════════════════════════════════════════════════════

    public function index(Request $request)
    {
        $periode = $request->get('periode', 'semaine');

        [$debut, $fin]         = $this->plage($periode);
        [$debutPrec, $finPrec] = $this->plagePrecedente($periode);

        $stats              = $this->calculerStats($debut, $fin, $debutPrec, $finPrec);
        $topPlats           = $this->topPlats($debut, $fin, 10);
        $meilleuresJournees = $this->meilleuresJournees($debut, $fin);

        [$labelsEvolution, $dataCA, $dataNbCommandes] =
            $this->dataEvolution($debut, $fin, $periode);

        return view('statistique.index', compact(
            'stats',
            'topPlats',
            'meilleuresJournees',
            'labelsEvolution',
            'dataCA',
            'dataNbCommandes',
        ));
    }

    // ══════════════════════════════════════════════════════════════
    // EXPORT CSV
    // GET /admin/statistiques/export?periode=semaine
    // ══════════════════════════════════════════════════════════════

    public function export(Request $request)
    {
        $periode    = $request->get('periode', 'semaine');
        [$debut, $fin] = $this->plage($periode);

        $journees   = $this->meilleuresJournees($debut, $fin);
        $nomFichier = 'statistiques_' . $periode . '_' . now()->format('Ymd') . '.csv';

        $entetes = [
            'Date', 'CA (FCFA)', 'Commandes',
            'Panier moyen', 'Sur place', 'À emporter', 'Livraisons', 'Annulées',
        ];

        $lignes = $journees->map(fn($j) => [
            Carbon::parse($j->date)->format('d/m/Y'),
            number_format((float) $j->ca, 0, ',', ' '),
            $j->nb_commandes,
            number_format((float) $j->panier_moyen, 0, ',', ' '),
            $j->sur_place   ?? 0,
            $j->a_emporter  ?? 0,
            $j->livraisons  ?? 0,
            $j->annulees    ?? 0,
        ]);

        return response()->streamDownload(function () use ($entetes, $lignes) {
            $out = fopen('php://output', 'w');
            // BOM UTF-8 pour compatibilité Excel
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, $entetes, ';');
            foreach ($lignes as $ligne) {
                fputcsv($out, $ligne, ';');
            }
            fclose($out);
        }, $nomFichier, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // AJAX — rafraîchissement partiel des KPIs
    // GET /admin/statistiques/refresh?periode=semaine
    // ══════════════════════════════════════════════════════════════

    public function refresh(Request $request)
    {
        $periode = $request->get('periode', 'semaine');

        [$debut, $fin]         = $this->plage($periode);
        [$debutPrec, $finPrec] = $this->plagePrecedente($periode);

        $stats = $this->calculerStats($debut, $fin, $debutPrec, $finPrec);

        return response()->json([
            'success'   => true,
            'stats'     => $stats,
            'timestamp' => now()->format('H:i:s'),
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // MÉTHODES PRIVÉES — calculs
    // ══════════════════════════════════════════════════════════════

    /**
     * Calculer tous les indicateurs pour une plage de dates.
     *
     * Colonnes de référence :
     *   commandes.statut_courant : 'Servie' | 'Livrée' → chiffre d'affaires encaissé
     *   commandes.typecommande   : 'Standard' | 'A emporter' | 'Livraison'
     *   commandes.void           : null = active, '1' = supprimée (soft delete logique)
     *   commandes.montant        : total de la commande en FCFA
     *   commandes.datecommande   : date (pas datetime)
     */
    private function calculerStats(
        Carbon $debut,
        Carbon $fin,
        Carbon $debutPrec,
        Carbon $finPrec
    ): array {

        // ── Helper : base query commandes actives sur une plage ──
        $q = fn(Carbon $d, Carbon $f) => DB::table('commandes')
            ->whereNull('void')
            ->whereNull('deleted_at')
            ->whereBetween('datecommande', [
                $d->toDateString(),
                $f->toDateString(),
            ]);

        // ── CA (commandes Servies ou Livrées = encaissées) ───────
        $caTotal    = (float) $q($debut, $fin)
            ->whereIn('statut_courant', ['Servie', 'Livrée'])
            ->sum('montant');

        $caPrecedent = (float) $q($debutPrec, $finPrec)
            ->whereIn('statut_courant', ['Servie', 'Livrée'])
            ->sum('montant');

        // ── Commandes (hors Annulées) ────────────────────────────
        $nbCommandes = (int) $q($debut, $fin)
            ->where('statut_courant', '!=', 'Annulée')
            ->count();

        $nbCommandesPrec = (int) $q($debutPrec, $finPrec)
            ->where('statut_courant', '!=', 'Annulée')
            ->count();

        // ── Livraisons ───────────────────────────────────────────
        $nbLivraisons = (int) $q($debut, $fin)
            ->where('typecommande', 'Livraison')
            ->where('statut_courant', '!=', 'Annulée')
            ->count();

        // ── Sur place ────────────────────────────────────────────
        $nbSurPlace = (int) $q($debut, $fin)
            ->where('typecommande', 'Standard')
            ->where('statut_courant', '!=', 'Annulée')
            ->count();

        // ── [AJOUT] À emporter ───────────────────────────────────
        $nbAEmporter = (int) $q($debut, $fin)
            ->where('typecommande', 'A emporter')
            ->where('statut_courant', '!=', 'Annulée')
            ->count();

        // ── Annulées ─────────────────────────────────────────────
        $nbAnnulees = (int) $q($debut, $fin)
            ->where('statut_courant', 'Annulée')
            ->count();

        $nbTotal = (int) $q($debut, $fin)->count();

        $tauxAnnulation = $nbTotal > 0
            ? round(($nbAnnulees / $nbTotal) * 100, 1)
            : 0;

        // ── Panier moyen ─────────────────────────────────────────
        $panierMoyen = $nbCommandes > 0
            ? (int) round($caTotal / $nbCommandes)
            : 0;

        // ── Clients distincts servis ─────────────────────────────
        // idclient = FK vers users (nullable pour commandes sans client)
        $clientsServis = (int) $q($debut, $fin)
            ->whereIn('statut_courant', ['Servie', 'Livrée'])
            ->whereNotNull('idclient')
            ->distinct()
            ->count('idclient');

        // ── Évolutions (%) ───────────────────────────────────────
        $evolutionCA         = $this->evolution($caTotal, $caPrecedent);
        $evolutionCommandes  = $this->evolution($nbCommandes, $nbCommandesPrec);

        // ── Répartition par statut_courant ────────────────────────
        $parStatut = $q($debut, $fin)
            ->select('statut_courant', DB::raw('COUNT(*) as total'))
            ->groupBy('statut_courant')
            ->orderBy('total', 'desc')
            ->pluck('total', 'statut_courant')
            ->toArray();

        // ── Répartition Standard / A emporter / Livraison ─────────
        $repartition = [
            'Sur place'  => $nbSurPlace,
            'A emporter' => $nbAEmporter,
            'Livraison'  => $nbLivraisons,
        ];

        // ── CA par catégorie ──────────────────────────────────────
        // lignes.prix = prix total de la ligne (pu × quantite - remise)
        // On peut aussi recalculer via menus.pu × lignes.quantite
        // mais on utilise lignes.prix qui est la donnée stockée.
        $caParCategorie = DB::table('lignes as l')
            ->join('commandes as c',    'c.idcommande',  '=', 'l.idcommande')
            ->join('menus as m',        'm.idmenu',      '=', 'l.idmenu')
            ->join('categories as cat', 'cat.idcategorie','=', 'm.idcategorie')
            ->whereNull('c.void')
            ->whereNull('c.deleted_at')
            ->whereBetween('c.datecommande', [
                $debut->toDateString(),
                $fin->toDateString(),
            ])
            ->whereIn('c.statut_courant', ['Servie', 'Livrée'])
            ->whereNull('cat.void')
            ->select(
                'cat.idcategorie',
                'cat.intitule as nom_categorie',
                DB::raw('SUM(l.prix) as ca'),
                DB::raw('SUM(l.quantite) as nb_ventes')
            )
            ->groupBy('cat.idcategorie', 'cat.intitule')
            ->orderByDesc('ca')
            ->get();

        // ── Performances par serveur (role = Serveur ou Caissier) ─
        // iduser = le serveur/caissier qui a enregistré la commande
        $parServeur = DB::table('commandes as c')
            ->join('users as u', 'u.iduser', '=', 'c.iduser')
            ->whereNull('c.void')
            ->whereNull('c.deleted_at')
            ->whereBetween('c.datecommande', [
                $debut->toDateString(),
                $fin->toDateString(),
            ])
            ->whereIn('c.statut_courant', ['Servie', 'Livrée'])
            ->whereNull('u.void')
            ->whereNull('u.deleted_at')
            ->whereIn('u.role', ['Serveur', 'Caissier', 'Administrateur'])
            ->select(
                'u.iduser',
                'u.prenom',
                'u.nom',
                DB::raw('COUNT(c.idcommande) as nb_commandes'),
                DB::raw('SUM(c.montant) as ca')
            )
            ->groupBy('u.iduser', 'u.prenom', 'u.nom')
            ->orderByDesc('ca')
            ->limit(10)
            ->get();

        return [
            'ca_total'            => $caTotal,
            'evolution_ca'        => $evolutionCA,
            'nb_commandes'        => $nbCommandes,
            'evolution_commandes' => $evolutionCommandes,
            'panier_moyen'        => $panierMoyen,
            'nb_livraisons'       => $nbLivraisons,
            'nb_sur_place'        => $nbSurPlace,
            'nb_a_emporter'       => $nbAEmporter,
            'taux_annulation'     => $tauxAnnulation,
            'clients_servis'      => $clientsServis,
            'par_statut'          => $parStatut,
            'repartition'         => $repartition,
            'ca_par_categorie'    => $caParCategorie,
            'par_serveur'         => $parServeur,
        ];
    }

    /**
     * Top N plats les plus vendus sur une plage.
     *
     * Table lignes : idligne, idcommande, idmenu, quantite, remise, prix
     * Table menus  : idmenu, idcategorie, intitule, pu, statut, void
     */
    private function topPlats(Carbon $debut, Carbon $fin, int $limite = 10)
    {
        return DB::table('lignes as l')
            ->join('commandes as c', 'c.idcommande', '=', 'l.idcommande')
            ->join('menus as m',     'm.idmenu',     '=', 'l.idmenu')
            ->whereNull('c.void')
            ->whereNull('c.deleted_at')
            ->whereBetween('c.datecommande', [
                $debut->toDateString(),
                $fin->toDateString(),
            ])
            ->where('c.statut_courant', '!=', 'Annulée')
            ->whereNull('m.void')
            ->select(
                'm.idmenu',
                'm.intitule',
                // total_vendu = somme des quantités commandées
                DB::raw('SUM(l.quantite) as total_vendu'),
                // ca_genere = somme des prix de lignes (prix = total ligne stocké)
                DB::raw('SUM(l.prix) as ca_genere')
            )
            ->groupBy('m.idmenu', 'm.intitule')
            ->orderByDesc('total_vendu')
            ->limit($limite)
            ->get();
    }

    /**
     * Meilleures journées classées par CA (max 30 résultats).
     *
     * datecommande est une colonne DATE (pas DATETIME),
     * donc on group by directement dessus.
     */
    private function meilleuresJournees(Carbon $debut, Carbon $fin)
    {
        return DB::table('commandes')
            ->whereNull('void')
            ->whereNull('deleted_at')
            ->whereBetween('datecommande', [
                $debut->toDateString(),
                $fin->toDateString(),
            ])
            ->select(
                'datecommande as date',
                // CA = somme des commandes encaissées uniquement
                DB::raw('SUM(CASE WHEN statut_courant IN ("Servie","Livrée")
                              THEN montant ELSE 0 END) as ca'),
                // Toutes les commandes non annulées
                DB::raw('COUNT(CASE WHEN statut_courant != "Annulée"
                              THEN 1 END) as nb_commandes'),
                // Panier moyen sur les encaissées
                DB::raw('ROUND(AVG(CASE WHEN statut_courant IN ("Servie","Livrée")
                              THEN montant END), 0) as panier_moyen'),
                // Répartition par type (typecommande : Standard | A emporter | Livraison)
                DB::raw('SUM(CASE WHEN typecommande = "Standard"
                              AND statut_courant != "Annulée"
                              THEN 1 ELSE 0 END) as sur_place'),
                DB::raw('SUM(CASE WHEN typecommande = "A emporter"
                              AND statut_courant != "Annulée"
                              THEN 1 ELSE 0 END) as a_emporter'),
                DB::raw('SUM(CASE WHEN typecommande = "Livraison"
                              AND statut_courant != "Annulée"
                              THEN 1 ELSE 0 END) as livraisons'),
                DB::raw('SUM(CASE WHEN statut_courant = "Annulée"
                              THEN 1 ELSE 0 END) as annulees')
            )
            ->groupBy('datecommande')
            ->orderByDesc('ca')
            ->limit(30)
            ->get();
    }

    /**
     * Données pour le graphique d'évolution des ventes.
     * Retourne [labels[], dataCA[], dataNbCommandes[]].
     *
     * Granularité automatique :
     *   jour    → par heure    (format HH:00)
     *   semaine → par jour     (format DD/MM)
     *   mois    → par jour     (format DD/MM)
     *   annee   → par mois     (format MM/YYYY)
     *
     * Note : datecommande est de type DATE, heurecommande est TIME.
     * Pour la granularité "jour", on utilise heurecommande.
     */
    private function dataEvolution(Carbon $debut, Carbon $fin, string $periode): array
    {
        if ($periode === 'jour') {
            // Granularité horaire : on group by heure de la commande
            $rows = DB::table('commandes')
                ->whereNull('void')
                ->whereNull('deleted_at')
                ->whereBetween('datecommande', [
                    $debut->toDateString(),
                    $fin->toDateString(),
                ])
                ->whereIn('statut_courant', ['Servie', 'Livrée'])
                ->select(
                    DB::raw("HOUR(heurecommande) as heure"),
                    DB::raw('SUM(montant) as ca'),
                    DB::raw('COUNT(*) as nb')
                )
                ->groupBy('heure')
                ->orderBy('heure')
                ->get();

            return [
                $rows->map(fn($r) => str_pad($r->heure, 2, '0', STR_PAD_LEFT) . 'h')->toArray(),
                $rows->map(fn($r) => (int) $r->ca)->toArray(),
                $rows->map(fn($r) => (int) $r->nb)->toArray(),
            ];
        }

        // Granularité journalière ou mensuelle
        $formatSQL = match($periode) {
            'annee'  => '%m/%Y',  // par mois
            default  => '%d/%m',  // par jour (semaine, mois)
        };

        $rows = DB::table('commandes')
            ->whereNull('void')
            ->whereNull('deleted_at')
            ->whereBetween('datecommande', [
                $debut->toDateString(),
                $fin->toDateString(),
            ])
            ->whereIn('statut_courant', ['Servie', 'Livrée'])
            ->select(
                DB::raw("DATE_FORMAT(datecommande, '{$formatSQL}') as label"),
                DB::raw('SUM(montant) as ca'),
                DB::raw('COUNT(*) as nb')
            )
            ->groupBy('label')
            ->orderBy(DB::raw("MIN(datecommande)"))
            ->get();

        return [
            $rows->pluck('label')->toArray(),
            $rows->map(fn($r) => (int) $r->ca)->toArray(),
            $rows->map(fn($r) => (int) $r->nb)->toArray(),
        ];
    }

    // ══════════════════════════════════════════════════════════════
    // HELPERS PRIVÉS
    // ══════════════════════════════════════════════════════════════

    /**
     * Calcule l'évolution en % entre valeur actuelle et précédente.
     */
    private function evolution(float $actuel, float $precedent): float
    {
        if ($precedent == 0) {
            return $actuel > 0 ? 100.0 : 0.0;
        }

        return round((($actuel - $precedent) / $precedent) * 100, 1);
    }

    /**
     * Retourne [Carbon $debut, Carbon $fin] pour la période demandée.
     * datecommande étant un DATE, on travaille en startOfDay/endOfDay.
     */
    private function plage(string $periode): array
    {
        return match($periode) {
            'jour'    => [
                Carbon::today(),
                Carbon::today(),
            ],
            'semaine' => [
                Carbon::now()->subDays(6)->startOfDay(),
                Carbon::today(),
            ],
            'mois'    => [
                Carbon::now()->startOfMonth(),
                Carbon::today(),
            ],
            'annee'   => [
                Carbon::now()->startOfYear(),
                Carbon::today(),
            ],
            default   => [
                Carbon::now()->subDays(6)->startOfDay(),
                Carbon::today(),
            ],
        };
    }

    /**
     * Retourne [Carbon $debut, Carbon $fin] de la période précédente
     * (pour calculer les évolutions).
     */
    private function plagePrecedente(string $periode): array
    {
        return match($periode) {
            'jour'    => [
                Carbon::yesterday(),
                Carbon::yesterday(),
            ],
            'semaine' => [
                Carbon::now()->subDays(13)->startOfDay(),
                Carbon::now()->subDays(7),
            ],
            'mois'    => [
                Carbon::now()->subMonth()->startOfMonth(),
                Carbon::now()->subMonth()->endOfMonth(),
            ],
            'annee'   => [
                Carbon::now()->subYear()->startOfYear(),
                Carbon::now()->subYear()->endOfYear(),
            ],
            default   => [
                Carbon::now()->subDays(13)->startOfDay(),
                Carbon::now()->subDays(7),
            ],
        };
    }
}