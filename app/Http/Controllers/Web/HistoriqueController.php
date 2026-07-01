<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Historique;
use App\Models\Commande;
use App\Models\Statut;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class HistoriqueController extends Controller
{
    // =========================================================
    // HISTORIQUE GLOBAL DE TOUTES LES COMMANDES
    // Accessible : Administrateur uniquement
    // =========================================================

    public function index(Request $request)
    {
        $query = Historique::with(['commande.table', 'commande.client', 'statut'])
            ->whereNull('void')
            ->orderByDesc('created_at');

        // Filtre par statut
        if ($request->filled('statut')) {
            $query->whereHas('statut', function ($q) use ($request) {
                $q->where('intitule', $request->statut);
            });
        }

        // Filtre par date
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        } else {
            // Par défaut : aujourd'hui
            $query->whereDate('created_at', today());
        }

        // Filtre par type de commande
        if ($request->filled('type')) {
            $query->whereHas('commande', function ($q) use ($request) {
                $q->where('typecommande', $request->type);
            });
        }

        // Recherche par référence de commande
        if ($request->filled('q')) {
            $q = $request->q;
            $query->whereHas('commande', function ($sub) use ($q) {
                $sub->where('reference', 'like', "%{$q}%");
            });
        }

        $historiques = $query->paginate(20)->withQueryString();

        // Statistiques du jour pour la barre de résumé
        $statsJour = $this->getStatsJour();

        // Liste des statuts pour le filtre
        $statuts = Statut::orderBy('priorite')->whereNull('void')->get();

        return view('historique.index', compact(
            'historiques',
            'statsJour',
            'statuts'
        ));
    }

    // =========================================================
    // HISTORIQUE D'UNE COMMANDE SPÉCIFIQUE
    // Accessible : Serveur, Caissier, Admin
    // =========================================================

    public function parCommande(Commande $commande)
    {
        if ($commande->void) {
            abort(404);
        }

        $historiques = Historique::where('idcommande', $commande->idcommande)
            ->with('statut')
            ->whereNull('void')
            ->orderBy('created_at')
            ->get()
            ->map(function ($h) {
                $config = $this->getConfigStatut($h->statut->intitule ?? '');
                $h->couleur      = $config['couleur'];
                $h->icone        = $config['icone'];
                $h->bg           = $config['bg'];
                $h->text_color   = $config['text'];
                $h->date_fmt     = Carbon::parse($h->created_at)->format('d/m/Y à H:i');
                return $h;
            });

        // Si la requête est AJAX, retourner du JSON
        if (request()->expectsJson()) {
            return response()->json([
                'success'     => true,
                'commande'    => $commande->reference,
                'statut'      => $commande->statut_courant,
                'data'        => $historiques->map(fn($h) => [
                    'idhistorique' => $h->idhistorique,
                    'statut'       => $h->statut->intitule ?? 'N/A',
                    'description'  => $h->description,
                    'date'         => $h->date_fmt,
                    'couleur'      => $h->couleur,
                    'icone'        => $h->icone,
                    'bg'           => $h->bg,
                    'text_color'   => $h->text_color,
                ]),
                'total'       => $historiques->count(),
            ]);
        }

        return view('historique.par-commande', compact(
            'commande',
            'historiques'
        ));
    }

    // =========================================================
    // AJOUTER UNE NOTE MANUELLE À L'HISTORIQUE
    // Cas d'usage : annoter une commande (ex: "Client rappelé")
    // Accessible : Administrateur, Caissier
    // =========================================================

    public function store(Request $request, Commande $commande)
    {
        if (!in_array(Auth::user()->role, ['Administrateur', 'Caissier'])) {
            return $this->repondre(false, 'Accès refusé.', 403);
        }

        if ($commande->void) {
            return $this->repondre(false, 'Commande introuvable.', 404);
        }

        $request->validate([
            'description' => 'required|string|min:3|max:500',
        ], [
            'description.required' => 'La description de la note est obligatoire.',
            'description.min'      => 'La note doit contenir au moins 3 caractères.',
            'description.max'      => 'La note ne peut pas dépasser 500 caractères.',
        ]);

        DB::beginTransaction();

        try {
            // Utiliser le statut courant de la commande pour la note
            $statut = Statut::where('intitule', $commande->statut_courant)->first();

            $historique = Historique::create([
                'idcommande'  => $commande->idcommande,
                'idstatut'    => $statut->idstatut,
                'description' => '[NOTE] ' . trim($request->description) . ' — par ' . Auth::user()->prenom,
            ]);

            DB::commit();

            Log::info('Note ajoutée à l\'historique', [
                'commande' => $commande->reference,
                'user'     => Auth::user()->email,
            ]);

            return $this->repondre(true,
                'Note ajoutée à l\'historique de la commande ' . $commande->reference . '.',
            );

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur ajout note historique', ['error' => $e->getMessage()]);
            return $this->repondre(false, 'Erreur : ' . $e->getMessage(), 500);
        }
    }

    // =========================================================
    // SUPPRIMER UNE ENTRÉE D'HISTORIQUE
    // Accessible : Administrateur uniquement
    // =========================================================

    public function destroy(Historique $historique)
    {
        // Interdire la suppression des entrées système (changements de statut)
        if (!str_starts_with($historique->description ?? '', '[NOTE]')) {
            return $this->repondre(false,
                'Seules les notes manuelles peuvent être supprimées. Les changements de statut sont conservés.',
                422
            );
        }

        DB::beginTransaction();

        try {
            $historique->update([
                'void'       => '1',
                'deleted_at' => now(),
            ]);

            DB::commit();

            Log::info('Entrée historique supprimée', [
                'historique' => $historique->idhistorique,
                'user'       => Auth::user()->email,
            ]);

            return $this->repondre(true, 'Note supprimée avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur suppression historique', ['error' => $e->getMessage()]);
            return $this->repondre(false, 'Erreur : ' . $e->getMessage(), 500);
        }
    }

    // =========================================================
    // RAPPORT D'ACTIVITÉ PAR PÉRIODE
    // Accessible : Administrateur uniquement
    // =========================================================

    public function rapport(Request $request)
    {
        $request->validate([
            'debut' => 'required|date',
            'fin'   => 'required|date|after_or_equal:debut',
        ], [
            'debut.required'            => 'La date de début est obligatoire.',
            'fin.required'              => 'La date de fin est obligatoire.',
            'fin.after_or_equal:debut'  => 'La date de fin doit être après la date de début.',
        ]);

        $debut = Carbon::parse($request->debut)->startOfDay();
        $fin   = Carbon::parse($request->fin)->endOfDay();

        // Nombre de transitions par statut sur la période
        $transitionsParStatut = DB::table('historiques')
            ->join('statuts', 'historiques.idstatut', '=', 'statuts.idstatut')
            ->whereNull('historiques.void')
            ->whereBetween('historiques.created_at', [$debut, $fin])
            ->select(
                'statuts.intitule',
                'statuts.priorite',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('statuts.idstatut', 'statuts.intitule', 'statuts.priorite')
            ->orderBy('statuts.priorite')
            ->get();

        // Activité par heure de la journée (tendances)
        $activiteParHeure = DB::table('historiques')
            ->whereNull('void')
            ->whereBetween('created_at', [$debut, $fin])
            ->select(
                DB::raw('HOUR(created_at) as heure'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('heure')
            ->orderBy('heure')
            ->get()
            ->keyBy('heure');

        // Remplir toutes les heures de la journée (0-23)
        $heures = collect(range(0, 23))->map(function ($h) use ($activiteParHeure) {
            return [
                'heure' => str_pad($h, 2, '0', STR_PAD_LEFT) . 'h',
                'total' => $activiteParHeure->get($h)->total ?? 0,
            ];
        });

        // Commandes les plus actives (le plus de changements de statut)
        $commandesActives = DB::table('historiques')
            ->join('commandes', 'historiques.idcommande', '=', 'commandes.idcommande')
            ->whereNull('historiques.void')
            ->whereBetween('historiques.created_at', [$debut, $fin])
            ->select(
                'commandes.reference',
                'commandes.typecommande',
                'commandes.montant',
                'commandes.statut_courant',
                DB::raw('COUNT(historiques.idhistorique) as nb_transitions')
            )
            ->groupBy(
                'commandes.idcommande',
                'commandes.reference',
                'commandes.typecommande',
                'commandes.montant',
                'commandes.statut_courant'
            )
            ->orderByDesc('nb_transitions')
            ->take(10)
            ->get();

        // Durée moyenne entre création et finalisation
        $dureesMoyennes = DB::table('commandes')
            ->whereNull('commandes.void')
            ->whereIn('commandes.statut_courant', ['Servie', 'Livrée'])
            ->whereBetween('commandes.datecommande', [
                $debut->format('Y-m-d'),
                $fin->format('Y-m-d')
            ])
            ->select(
                'commandes.typecommande',
                DB::raw('AVG(TIMESTAMPDIFF(MINUTE, commandes.created_at, commandes.updated_at)) as duree_moy_minutes'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('commandes.typecommande')
            ->get();

        if (request()->expectsJson()) {
            return response()->json([
                'success'              => true,
                'periode'              => [
                    'debut' => $debut->format('d/m/Y'),
                    'fin'   => $fin->format('d/m/Y'),
                ],
                'transitions_statut'   => $transitionsParStatut,
                'activite_par_heure'   => $heures,
                'commandes_actives'    => $commandesActives,
                'durees_moyennes'      => $dureesMoyennes,
            ]);
        }

        return view('historique.rapport', compact(
            'transitionsParStatut',
            'heures',
            'commandesActives',
            'dureesMoyennes',
            'debut',
            'fin'
        ));
    }

    // =========================================================
    // TIMELINE EN TEMPS RÉEL (AJAX - polling toutes les 15s)
    // Accessible : tous les rôles connectés
    // =========================================================

    public function timeline()
    {
        // 20 derniers événements toutes commandes confondues
        $evenements = Historique::with(['commande.table', 'statut'])
            ->whereNull('void')
            ->whereDate('created_at', today())
            ->orderByDesc('created_at')
            ->take(20)
            ->get()
            ->map(function ($h) {
                $config = $this->getConfigStatut($h->statut->intitule ?? '');
                return [
                    'idhistorique' => $h->idhistorique,
                    'reference'    => $h->commande->reference ?? 'N/A',
                    'table'        => $h->commande->table->intitule ?? null,
                    'statut'       => $h->statut->intitule ?? 'N/A',
                    'description'  => $h->description,
                    'heure'        => Carbon::parse($h->created_at)->format('H:i'),
                    'couleur'      => $config['couleur'],
                    'icone'        => $config['icone'],
                    'bg'           => $config['bg'],
                    'text_color'   => $config['text'],
                ];
            });

        return response()->json([
            'success'    => true,
            'data'       => $evenements,
            'timestamp'  => now()->format('H:i:s'),
            'total_jour' => Historique::whereDate('created_at', today())
                ->whereNull('void')->count(),
        ]);
    }

    // =========================================================
    // STATISTIQUES DU JOUR (AJAX)
    // =========================================================

    public function statsJour()
    {
        return response()->json([
            'success' => true,
            'data'    => $this->getStatsJour(),
        ]);
    }

    // =========================================================
    // MÉTHODES PRIVÉES
    // =========================================================

    /**
     * Calcul des statistiques du jour pour la barre de résumé
     */
    private function getStatsJour(): array
    {
        $today = today();

        return [
            'total_evenements' => Historique::whereDate('created_at', $today)
                ->whereNull('void')->count(),

            'commandes_en_attente' => Commande::where('statut_courant', 'En attente')
                ->whereNull('void')->count(),

            'commandes_en_prep' => Commande::where('statut_courant', 'En préparation')
                ->whereNull('void')->count(),

            'commandes_terminees' => Commande::whereIn('statut_courant', ['Servie', 'Livrée'])
                ->whereDate('datecommande', $today)
                ->whereNull('void')->count(),

            'commandes_annulees' => Commande::where('statut_courant', 'Annulée')
                ->whereDate('datecommande', $today)
                ->whereNull('void')->count(),

            'timestamp' => now()->format('H:i:s'),
        ];
    }

    /**
     * Configuration couleur/icône par intitulé de statut
     */
    private function getConfigStatut(string $intitule): array
    {
        $configs = [
            'En attente' => [
                'couleur' => 'yellow',
                'icone'   => 'fa-clock',
                'bg'      => 'rgba(234,179,8,0.12)',
                'text'    => '#eab308',
            ],
            'En préparation' => [
                'couleur' => 'blue',
                'icone'   => 'fa-fire-burner',
                'bg'      => 'rgba(59,130,246,0.12)',
                'text'    => '#60a5fa',
            ],
            'Expédiée' => [
                'couleur' => 'orange',
                'icone'   => 'fa-motorcycle',
                'bg'      => 'rgba(234,88,12,0.12)',
                'text'    => '#f97316',
            ],
            'Livrée' => [
                'couleur' => 'green',
                'icone'   => 'fa-circle-check',
                'bg'      => 'rgba(34,197,94,0.12)',
                'text'    => '#22c55e',
            ],
            'Servie' => [
                'couleur' => 'green',
                'icone'   => 'fa-utensils',
                'bg'      => 'rgba(34,197,94,0.12)',
                'text'    => '#22c55e',
            ],
            'Annulée' => [
                'couleur' => 'red',
                'icone'   => 'fa-circle-xmark',
                'bg'      => 'rgba(239,68,68,0.12)',
                'text'    => '#f87171',
            ],
        ];

        return $configs[$intitule] ?? [
            'couleur' => 'gray',
            'icone'   => 'fa-circle',
            'bg'      => 'rgba(107,114,128,0.12)',
            'text'    => '#6b7280',
        ];
    }

    /**
     * Répondre JSON ou redirect selon le type de requête
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