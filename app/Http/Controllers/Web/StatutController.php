<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Statut;
use App\Models\Commande;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StatutController extends Controller
{
    // =========================================================
    // LISTE DES STATUTS
    // =========================================================

    public function index()
    {
        $statuts = Statut::withCount(['historiques'])
            ->whereNull('void')
            ->orderBy('priorite')
            ->get()
            ->map(function ($statut) {
                // Nombre de commandes actuellement à ce statut
                $statut->nb_commandes_actives = Commande::where('statut_courant', $statut->intitule)
                    ->whereNull('void')
                    ->count();
                return $statut;
            });

        // Mapping couleur / icône pour chaque statut
        $config = $this->getConfig();

        return view('statut.index', compact('statuts', 'config'));
    }

    // =========================================================
    // FORMULAIRE CRÉATION
    // =========================================================

    public function create()
    {
        // Suggérer la prochaine priorité
        $prochainePriorite = (Statut::whereNull('void')->max('priorite') ?? 0) + 1;

        $config = $this->getConfig();

        return view('statut.create', compact('prochainePriorite', 'config'));
    }

    // =========================================================
    // ENREGISTRER UN NOUVEAU STATUT
    // =========================================================

    public function store(Request $request)
    {
        $request->validate([
            'intitule'    => 'required|string|max:128|unique:statuts,intitule',
            'description' => 'nullable|string|max:500',
            'priorite'    => 'required|integer|min:1|max:99',
        ], [
            'intitule.required' => 'Le nom du statut est obligatoire.',
            'intitule.unique'   => 'Un statut avec ce nom existe déjà.',
            'intitule.max'      => 'Le nom ne peut pas dépasser 128 caractères.',
            'priorite.required' => 'La priorité est obligatoire.',
            'priorite.integer'  => 'La priorité doit être un nombre entier.',
            'priorite.min'      => 'La priorité minimale est 1.',
            'priorite.max'      => 'La priorité maximale est 99.',
        ]);

        DB::beginTransaction();

        try {
            $statut = Statut::create([
                'intitule'    => trim($request->intitule),
                'description' => $request->description,
                'priorite'    => $request->priorite,
            ]);

            DB::commit();

            Log::info('Statut créé', [
                'statut' => $statut->intitule,
                'user'   => Auth::user()->email,
            ]);

            return redirect()->route('admin.statuts.index')
                ->with('success', "Le statut « {$statut->intitule} » a été créé avec succès !");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création statut', ['error' => $e->getMessage()]);
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la création : ' . $e->getMessage());
        }
    }

    // =========================================================
    // DÉTAIL D'UN STATUT
    // =========================================================

    public function show(Statut $statut)
    {
        if ($statut->void) {
            abort(404);
        }

        // Commandes actuellement à ce statut
        $commandesActives = Commande::where('statut_courant', $statut->intitule)
            ->whereNull('void')
            ->with(['table', 'client', 'serveur', 'lignes'])
            ->orderByDesc('updated_at')
            ->get();

        // Historique d'utilisation du statut (30 derniers jours)
        $historiqueUsage = DB::table('historiques')
            ->where('idstatut', $statut->idstatut)
            ->whereNull('deleted_at')
            ->where('created_at', '>=', now()->subDays(30))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Statistiques globales du statut
        $stats = [
            'total_utilisation' => DB::table('historiques')
                ->where('idstatut', $statut->idstatut)
                ->whereNull('deleted_at')
                ->count(),

            'utilisation_mois'  => DB::table('historiques')
                ->where('idstatut', $statut->idstatut)
                ->whereNull('deleted_at')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),

            'commandes_actives' => $commandesActives->count(),
        ];

        $config = $this->getConfig();

        return view('statut.show', compact(
            'statut',
            'commandesActives',
            'historiqueUsage',
            'stats',
            'config'
        ));
    }

    // =========================================================
    // FORMULAIRE MODIFICATION
    // =========================================================

    public function edit(Statut $statut)
    {
        if ($statut->void) {
            abort(404);
        }

        // Les statuts du système de base ne peuvent pas être renommés
        $statutsSysteme = [
            'En attente',
            'En préparation',
            'Expédiée',
            'Livrée',
            'Servie',
            'Annulée',
        ];

        $estSysteme = in_array($statut->intitule, $statutsSysteme);
        $config     = $this->getConfig();

        return view('statut.edit', compact('statut', 'estSysteme', 'config'));
    }

    // =========================================================
    // METTRE À JOUR UN STATUT
    // =========================================================

    public function update(Request $request, Statut $statut)
    {
        // Les statuts système ne peuvent pas être renommés
        $statutsSysteme = [
            'En attente',
            'En préparation',
            'Expédiée',
            'Livrée',
            'Servie',
            'Annulée',
        ];

        $estSysteme = in_array($statut->intitule, $statutsSysteme);

        // Règles de validation conditionnelles
        $rules = [
            'description' => 'nullable|string|max:500',
            'priorite'    => 'required|integer|min:1|max:99',
        ];

        $messages = [
            'priorite.required' => 'La priorité est obligatoire.',
            'priorite.integer'  => 'La priorité doit être un nombre entier.',
        ];

        // Autoriser le changement de nom seulement pour les statuts non-système
        if (!$estSysteme) {
            $rules['intitule'] = 'required|string|max:128|unique:statuts,intitule,' . $statut->idstatut . ',idstatut';
            $messages['intitule.required'] = 'Le nom du statut est obligatoire.';
            $messages['intitule.unique']   = 'Un autre statut porte déjà ce nom.';
        }

        $request->validate($rules, $messages);

        DB::beginTransaction();

        try {
            $data = [
                'description' => $request->description,
                'priorite'    => $request->priorite,
            ];

            if (!$estSysteme && $request->filled('intitule')) {
                $data['intitule'] = trim($request->intitule);
            }

            $statut->update($data);

            DB::commit();

            Log::info('Statut modifié', [
                'statut' => $statut->intitule,
                'user'   => Auth::user()->email,
            ]);

            return redirect()->route('admin.statuts.index')
                ->with('success', "Le statut « {$statut->intitule} » a été modifié avec succès !");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur modification statut', ['error' => $e->getMessage()]);
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la modification : ' . $e->getMessage());
        }
    }

    // =========================================================
    // SUPPRIMER UN STATUT (Soft Delete)
    // =========================================================

    public function destroy(Statut $statut)
    {
        // Interdire la suppression des statuts système
        $statutsSysteme = [
            'En attente',
            'En préparation',
            'Expédiée',
            'Livrée',
            'Servie',
            'Annulée',
        ];

        if (in_array($statut->intitule, $statutsSysteme)) {
            return back()->with('error',
                "Impossible de supprimer « {$statut->intitule} » : c'est un statut système indispensable au fonctionnement de l'application."
            );
        }

        // Vérifier s'il y a des commandes actuellement à ce statut
        $commandesActives = Commande::where('statut_courant', $statut->intitule)
            ->whereNull('void')
            ->count();

        if ($commandesActives > 0) {
            return back()->with('error',
                "Impossible de supprimer « {$statut->intitule} » : {$commandesActives} commande(s) ont actuellement ce statut."
            );
        }

        DB::beginTransaction();

        try {
            $statut->update([
                'void'       => '1',
                'deleted_at' => now(),
            ]);

            DB::commit();

            Log::info('Statut supprimé', [
                'statut' => $statut->intitule,
                'user'   => Auth::user()->email,
            ]);

            return redirect()->route('admin.statuts.index')
                ->with('success', "Le statut « {$statut->intitule} » a été supprimé.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur suppression statut', ['error' => $e->getMessage()]);
            return back()->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }

    // =========================================================
    // RÉORGANISER LES PRIORITÉS (AJAX drag & drop)
    // =========================================================

    public function reordonner(Request $request)
    {
        $request->validate([
            'ordre'   => 'required|array',
            'ordre.*' => 'integer|exists:statuts,idstatut',
        ], [
            'ordre.required' => 'L\'ordre est obligatoire.',
            'ordre.array'    => 'Format invalide.',
        ]);

        DB::beginTransaction();

        try {
            foreach ($request->ordre as $position => $idstatut) {
                Statut::where('idstatut', $idstatut)
                    ->update(['priorite' => $position + 1]);
            }

            DB::commit();

            Log::info('Statuts réordonnés', [
                'user' => Auth::user()->email,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ordre des statuts mis à jour.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur réordonnancement statuts', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================
    // LISTE POUR AJAX (sélecteur de statut)
    // =========================================================

    public function liste()
    {
        $statuts = Statut::whereNull('void')
            ->orderBy('priorite')
            ->get()
            ->map(function ($statut) {
                $cfg = $this->getConfig()[$statut->intitule] ?? [
                    'couleur' => 'gray',
                    'icone'   => 'fa-circle',
                ];
                return [
                    'idstatut'    => $statut->idstatut,
                    'intitule'    => $statut->intitule,
                    'description' => $statut->description,
                    'priorite'    => $statut->priorite,
                    'couleur'     => $cfg['couleur'],
                    'icone'       => $cfg['icone'],
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $statuts,
        ]);
    }

    // =========================================================
    // MÉTHODE PRIVÉE : Configuration couleurs/icônes
    // =========================================================

    private function getConfig(): array
    {
        return [
            'En attente' => [
                'couleur'     => 'yellow',
                'icone'       => 'fa-clock',
                'bg'          => 'rgba(234,179,8,0.12)',
                'text'        => '#eab308',
                'badge_class' => 'badge-attente',
            ],
            'En préparation' => [
                'couleur'     => 'blue',
                'icone'       => 'fa-fire-burner',
                'bg'          => 'rgba(59,130,246,0.12)',
                'text'        => '#60a5fa',
                'badge_class' => 'badge-prep',
            ],
            'Expédiée' => [
                'couleur'     => 'orange',
                'icone'       => 'fa-motorcycle',
                'bg'          => 'rgba(234,88,12,0.12)',
                'text'        => '#f97316',
                'badge_class' => 'badge-expediee',
            ],
            'Livrée' => [
                'couleur'     => 'green',
                'icone'       => 'fa-circle-check',
                'bg'          => 'rgba(34,197,94,0.12)',
                'text'        => '#22c55e',
                'badge_class' => 'badge-livree',
            ],
            'Servie' => [
                'couleur'     => 'green',
                'icone'       => 'fa-utensils',
                'bg'          => 'rgba(34,197,94,0.12)',
                'text'        => '#22c55e',
                'badge_class' => 'badge-servie',
            ],
            'Annulée' => [
                'couleur'     => 'red',
                'icone'       => 'fa-circle-xmark',
                'bg'          => 'rgba(239,68,68,0.12)',
                'text'        => '#f87171',
                'badge_class' => 'badge-annulee',
            ],
        ];
    }
}