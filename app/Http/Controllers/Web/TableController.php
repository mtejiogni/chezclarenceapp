<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\TableResto;
use App\Models\Commande;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TableController extends Controller
{
    // =========================================================
    // LISTE DES TABLES
    // =========================================================

    public function index(Request $request)
    {
        $query = TableResto::whereNull('void')
            ->orderBy('intitule');

        // Recherche par nom
        if ($request->filled('q')) {
            $query->where('intitule', 'like', '%' . $request->q . '%');
        }

        // Toutes les tables avec statut en temps réel
        $tables = $query->get()->map(function ($table) {

            // Commande active sur cette table
            $commandeActive = Commande::where('idtable', $table->idtable)
                ->whereNotIn('statut_courant', ['Servie', 'Livrée', 'Annulée'])
                ->whereNull('void')
                ->with(['lignes', 'serveur'])
                ->latest()
                ->first();

            $table->occupee          = (bool) $commandeActive;
            $table->commande_active  = $commandeActive;
            $table->montant_en_cours = $commandeActive ? $commandeActive->montant : 0;
            $table->reference_active = $commandeActive ? $commandeActive->reference : null;
            $table->statut_commande  = $commandeActive ? $commandeActive->statut_courant : null;

            return $table;
        });

        // Compteurs globaux
        $totalTables    = $tables->count();
        $tablesOccupees = $tables->where('occupee', true)->count();
        $tablesLibres   = $totalTables - $tablesOccupees;

        // CA en cours (commandes actives non encore encaissées)
        $caEnCours = Commande::whereNotIn('statut_courant', ['Servie', 'Livrée', 'Annulée'])
            ->whereNotNull('idtable')
            ->whereNull('void')
            ->sum('montant');

        return view('table.index', compact(
            'tables',
            'totalTables',
            'tablesOccupees',
            'tablesLibres',
            'caEnCours'
        ));
    }

    // =========================================================
    // FORMULAIRE CRÉATION
    // =========================================================

    public function create()
    {
        // Suggérer le prochain numéro de table
        $dernierNumero = TableResto::whereNull('void')
            ->where('intitule', 'like', 'Table %')
            ->orderByDesc('intitule')
            ->value('intitule');

        $prochainNumero = 1;
        if ($dernierNumero) {
            preg_match('/(\d+)$/', $dernierNumero, $matches);
            $prochainNumero = isset($matches[1]) ? (int) $matches[1] + 1 : 1;
        }

        $suggestionNom = 'Table ' . str_pad($prochainNumero, 2, '0', STR_PAD_LEFT);

        return view('table.create', compact('suggestionNom'));
    }

    // =========================================================
    // ENREGISTRER UNE TABLE
    // =========================================================

    public function store(Request $request)
    {
        $request->validate([
            'intitule'    => 'required|string|max:128|unique:tables,intitule',
            'description' => 'nullable|string|max:300',
        ], [
            'intitule.required' => 'Le nom de la table est obligatoire.',
            'intitule.max'      => 'Le nom ne peut pas dépasser 128 caractères.',
            'intitule.unique'   => 'Une table avec ce nom existe déjà.',
        ]);

        // Création multiple si demandée (ex: créer Table 01 à Table 10 d'un coup)
        if ($request->boolean('creation_multiple') && $request->filled('nombre')) {
            return $this->storeMultiple($request);
        }

        DB::beginTransaction();

        try {
            $table = TableResto::create([
                'intitule'    => trim($request->intitule),
                'description' => $request->description,
            ]);

            DB::commit();

            Log::info('Table créée', [
                'table' => $table->intitule,
                'user'  => Auth::user()->email,
            ]);

            return redirect()->route('admin.tables.index')
                ->with('success', "La table « {$table->intitule} » a été créée avec succès !");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création table', ['error' => $e->getMessage()]);
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la création : ' . $e->getMessage());
        }
    }

    // =========================================================
    // DÉTAIL D'UNE TABLE
    // =========================================================

    public function show(TableResto $table)
    {
        if ($table->void) {
            abort(404);
        }

        // Commande active en cours sur cette table
        $commandeActive = Commande::where('idtable', $table->idtable)
            ->whereNotIn('statut_courant', ['Servie', 'Livrée', 'Annulée'])
            ->whereNull('void')
            ->with(['lignes.menu', 'serveur'])
            ->latest()
            ->first();

        // Historique des commandes de cette table (30 derniers jours)
        $historique = Commande::where('idtable', $table->idtable)
            ->whereNull('void')
            ->where('datecommande', '>=', now()->subDays(30))
            ->with(['lignes.menu', 'serveur'])
            ->orderByDesc('datecommande')
            ->orderByDesc('heurecommande')
            ->get();

        // Statistiques de la table
        $stats = [
            'total_commandes' => Commande::where('idtable', $table->idtable)
                ->whereNull('void')->count(),

            'ca_total' => Commande::where('idtable', $table->idtable)
                ->whereIn('statut_courant', ['Servie', 'Livrée'])
                ->whereNull('void')->sum('montant'),

            'ca_mois'  => Commande::where('idtable', $table->idtable)
                ->whereIn('statut_courant', ['Servie', 'Livrée'])
                ->whereNull('void')
                ->whereMonth('datecommande', now()->month)
                ->whereYear('datecommande', now()->year)
                ->sum('montant'),

            'panier_moyen' => Commande::where('idtable', $table->idtable)
                ->whereIn('statut_courant', ['Servie', 'Livrée'])
                ->whereNull('void')->avg('montant') ?? 0,
        ];

        return view('table.show', compact(
            'table',
            'commandeActive',
            'historique',
            'stats'
        ));
    }

    // =========================================================
    // FORMULAIRE MODIFICATION
    // =========================================================

    public function edit(TableResto $table)
    {
        if ($table->void) {
            abort(404);
        }

        return view('table.edit', compact('table'));
    }

    // =========================================================
    // METTRE À JOUR UNE TABLE
    // =========================================================

    public function update(Request $request, TableResto $table)
    {
        $request->validate([
            'intitule'    => 'required|string|max:128|unique:tables,intitule,' . $table->idtable . ',idtable',
            'description' => 'nullable|string|max:300',
        ], [
            'intitule.required' => 'Le nom de la table est obligatoire.',
            'intitule.unique'   => 'Une autre table porte déjà ce nom.',
        ]);

        // Vérifier que la table n'est pas occupée avant de la modifier
        $occupee = Commande::where('idtable', $table->idtable)
            ->whereNotIn('statut_courant', ['Servie', 'Livrée', 'Annulée'])
            ->whereNull('void')
            ->exists();

        if ($occupee) {
            return back()->with('error',
                "Impossible de modifier « {$table->intitule} » : une commande est en cours sur cette table."
            );
        }

        DB::beginTransaction();

        try {
            $table->update([
                'intitule'    => trim($request->intitule),
                'description' => $request->description,
            ]);

            DB::commit();

            Log::info('Table modifiée', [
                'table' => $table->intitule,
                'user'  => Auth::user()->email,
            ]);

            return redirect()->route('admin.tables.index')
                ->with('success', "La table « {$table->intitule} » a été modifiée avec succès !");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur modification table', ['error' => $e->getMessage()]);
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la modification : ' . $e->getMessage());
        }
    }

    // =========================================================
    // SUPPRIMER UNE TABLE (Soft Delete)
    // =========================================================

    public function destroy(TableResto $table)
    {
        // Refuser si une commande est active sur cette table
        $commandeActive = Commande::where('idtable', $table->idtable)
            ->whereNotIn('statut_courant', ['Servie', 'Livrée', 'Annulée'])
            ->whereNull('void')
            ->exists();

        if ($commandeActive) {
            return back()->with('error',
                "Impossible de supprimer « {$table->intitule} » : une commande est en cours."
            );
        }

        DB::beginTransaction();

        try {
            $table->update([
                'void'       => '1',
                'deleted_at' => now(),
            ]);

            DB::commit();

            Log::info('Table supprimée', [
                'table' => $table->intitule,
                'user'  => Auth::user()->email,
            ]);

            return redirect()->route('admin.tables.index')
                ->with('success', "La table « {$table->intitule} » a été supprimée.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur suppression table', ['error' => $e->getMessage()]);
            return back()->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }

    // =========================================================
    // STATUT EN TEMPS RÉEL (AJAX)
    // =========================================================

    public function statutTempsReel()
    {
        $tables = TableResto::whereNull('void')
            ->orderBy('intitule')
            ->get()
            ->map(function ($table) {
                $commande = Commande::where('idtable', $table->idtable)
                    ->whereNotIn('statut_courant', ['Servie', 'Livrée', 'Annulée'])
                    ->whereNull('void')
                    ->latest()
                    ->first();

                return [
                    'idtable'         => $table->idtable,
                    'intitule'        => $table->intitule,
                    'description'     => $table->description,
                    'occupee'         => (bool) $commande,
                    'reference'       => $commande?->reference,
                    'statut_commande' => $commande?->statut_courant,
                    'montant'         => $commande ? (float) $commande->montant : 0,
                    'heure'           => $commande?->heurecommande,
                ];
            });

        return response()->json([
            'success'         => true,
            'data'            => $tables,
            'tables_libres'   => $tables->where('occupee', false)->count(),
            'tables_occupees' => $tables->where('occupee', true)->count(),
            'timestamp'       => now()->format('H:i:s'),
        ]);
    }

    // =========================================================
    // LIBÉRER UNE TABLE MANUELLEMENT (Admin uniquement)
    // =========================================================

    public function liberer(TableResto $table)
    {
        // Uniquement accessible aux administrateurs
        if (Auth::user()->role !== 'Administrateur') {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès refusé.',
                ], 403);
            }
            abort(403);
        }

        // Récupérer la commande active
        $commande = Commande::where('idtable', $table->idtable)
            ->whereNotIn('statut_courant', ['Servie', 'Livrée', 'Annulée'])
            ->whereNull('void')
            ->latest()
            ->first();

        if (!$commande) {
            return back()->with('error', 'Aucune commande active sur cette table.');
        }

        DB::beginTransaction();

        try {
            $commande->update([
                'statut_courant' => 'Servie',
                'void'           => null,
            ]);

            Log::warning('Table libérée manuellement par Admin', [
                'table'     => $table->intitule,
                'commande'  => $commande->reference,
                'admin'     => Auth::user()->email,
            ]);

            DB::commit();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Table {$table->intitule} libérée.",
                ]);
            }

            return back()->with('success', "Table « {$table->intitule} » libérée avec succès.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    // =========================================================
    // CRÉATION MULTIPLE (plusieurs tables en une fois)
    // =========================================================

    private function storeMultiple(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'nombre'       => 'required|integer|min:1|max:50',
            'prefixe'      => 'required|string|max:50',
            'debut'        => 'required|integer|min:1',
            'description'  => 'nullable|string|max:300',
        ], [
            'nombre.required'  => 'Le nombre de tables est obligatoire.',
            'nombre.max'       => 'Vous ne pouvez pas créer plus de 50 tables à la fois.',
            'prefixe.required' => 'Le préfixe est obligatoire.',
            'debut.required'   => 'Le numéro de début est obligatoire.',
        ]);

        DB::beginTransaction();

        try {
            $creees  = [];
            $ignores = [];
            $debut   = (int) $request->debut;
            $nombre  = (int) $request->nombre;
            $prefixe = trim($request->prefixe);

            for ($i = $debut; $i < $debut + $nombre; $i++) {
                $intitule = $prefixe . ' ' . str_pad($i, 2, '0', STR_PAD_LEFT);

                // Ne pas créer si une table avec ce nom existe déjà
                $existe = TableResto::where('intitule', $intitule)->exists();

                if ($existe) {
                    $ignores[] = $intitule;
                    continue;
                }

                TableResto::create([
                    'intitule'    => $intitule,
                    'description' => $request->description,
                ]);

                $creees[] = $intitule;
            }

            DB::commit();

            Log::info('Tables créées en masse', [
                'nb_creees'  => count($creees),
                'nb_ignores' => count($ignores),
                'user'       => Auth::user()->email,
            ]);

            $msg = count($creees) . ' table(s) créée(s) avec succès.';
            if (!empty($ignores)) {
                $msg .= ' ' . count($ignores) . ' ignorée(s) car déjà existante(s) : ' . implode(', ', $ignores) . '.';
            }

            return redirect()->route('admin.tables.index')->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création multiple tables', ['error' => $e->getMessage()]);
            return back()
                ->withInput()
                ->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

}