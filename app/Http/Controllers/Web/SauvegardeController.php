<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Parametre;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use ZipArchive;

// ══════════════════════════════════════════════════════════════
// MODULE SAUVEGARDE ET RESTAURATION (Administrateur uniquement)
//
// ── PRINCIPE DE SÉCURITÉ CENTRAL ─────────────────────────────
// Ce contrôleur peut supprimer des données de façon IRRÉVERSIBLE.
// Règle absolue appliquée partout : la suppression définitive ne
// s'applique JAMAIS à une ligne encore active — uniquement à des
// lignes déjà dans la corbeille (void renseigné). Impossible de
// court-circuiter le soft delete existant de chaque module via cet
// outil : il ne fait que vider une corbeille déjà constituée.
//
// ── REGISTRE DES TABLES [VÉRIFIÉ CONTRE LES VRAIES MIGRATIONS] ──
// Toutes les colonnes (clé primaire, champ image, présence de
// 'void') ont été confirmées contre les fichiers de migration
// réels du projet.
//
// Volontairement EXCLUES du registre : users/password_reset_tokens/
// sessions (infrastructure Laravel), cache/cache_locks/jobs/
// job_batches/failed_jobs (files & cache), et les tables Spatie
// permission (permissions/roles/model_has_*) qui ne semblent pas
// utilisées par l'application — les contrôles de rôle observés
// partout ailleurs comparent directement la colonne users.role,
// pas ces tables de permissions. Aucune de ces tables ne contient
// de donnée métier à sauvegarder/restaurer.
// ══════════════════════════════════════════════════════════════

class SauvegardeController extends Controller
{
    private const TABLES_GEREES = [
        'utilisateurs' => [
            'table'       => 'users',
            'label'       => 'Utilisateurs',
            'pk'          => 'iduser',
            'image'       => 'photo',
            'restaurable' => true,
            'affichage'   => ['nom', 'prenom', 'email', 'role'],
        ],
        'commandes' => [
            'table'       => 'commandes',
            'label'       => 'Commandes',
            'pk'          => 'idcommande',
            'image'       => null,
            'restaurable' => true,
            'affichage'   => ['reference', 'typecommande', 'statut_courant', 'montant'],
        ],
        'lignes' => [
            'table'       => 'lignes',
            'label'       => 'Lignes de commande',
            'pk'          => 'idligne',
            'image'       => null,
            'restaurable' => false, // pas de colonne void sur cette table
            'affichage'   => ['idcommande', 'idmenu', 'quantite', 'prix'],
        ],
        'menus' => [
            'table'       => 'menus',
            'label'       => 'Menus / Plats',
            'pk'          => 'idmenu',
            'image'       => 'photo',
            'restaurable' => true,
            'affichage'   => ['intitule', 'pu', 'statut'],
        ],
        'categories' => [
            'table'       => 'categories',
            'label'       => 'Catégories',
            'pk'          => 'idcategorie',
            'image'       => 'photo',
            'restaurable' => true,
            'affichage'   => ['intitule', 'statut'],
        ],
        'tables' => [
            'table'       => 'tables',
            'label'       => 'Tables du restaurant',
            'pk'          => 'idtable',
            'image'       => null,
            'restaurable' => true,
            'affichage'   => ['intitule', 'description'],
        ],
        'statuts' => [
            'table'       => 'statuts',
            'label'       => 'Statuts',
            'pk'          => 'idstatut',
            'image'       => null,
            'restaurable' => true,
            'affichage'   => ['intitule', 'priorite'],
        ],
        'historiques' => [
            'table'       => 'historiques',
            'label'       => 'Historiques',
            'pk'          => 'idhistorique',
            'image'       => null,
            'restaurable' => true,
            'affichage'   => ['idcommande', 'idstatut', 'description'],
        ],
        'parametres' => [
            'table'       => 'parametres',
            'label'       => 'Paramètres du restaurant',
            'pk'          => 'idparametres',
            'image'       => 'logo',
            'restaurable' => false, // pas de colonne void — export seulement
            'affichage'   => ['nom_restaurant', 'ville'],
        ],
    ];

    // =========================================================
    // PAGE PRINCIPALE — vue d'ensemble + onglets Sauvegarde/Corbeille
    // GET /admin/sauvegarde
    // =========================================================

    public function index()
    {
        $stats = [];

        foreach (self::TABLES_GEREES as $cle => $config) {
            $actifs = DB::table($config['table'])
                ->when($config['restaurable'], fn ($q) => $q->whereNull('void'))
                ->count();

            $corbeille = $config['restaurable']
                ? DB::table($config['table'])->whereNotNull('void')->count()
                : 0;

            $stats[$cle] = array_merge($config, [
                'cle'       => $cle,
                'actifs'    => $actifs,
                'corbeille' => $corbeille,
            ]);
        }

        $totalCorbeille = collect($stats)->sum('corbeille');
        $totalActifs    = collect($stats)->sum('actifs');

        return view('sauvegarde.index', compact('stats', 'totalCorbeille', 'totalActifs'));
    }

    // =========================================================
    // EXPORT / SAUVEGARDE
    // POST /admin/sauvegarde/exporter
    // =========================================================

    public function exporter(Request $request)
    {
        $data = $request->validate([
            'format'         => 'required|in:sql,csv,pdf',
            'scope'          => 'required|in:base_entiere,tables',
            'tables'         => 'required_if:scope,tables|array',
            'tables.*'       => 'string|in:' . implode(',', array_keys(self::TABLES_GEREES)),
            'actifs_seuls'   => 'nullable|boolean',
            'inclure_images' => 'nullable|boolean',
        ], [
            'tables.required_if' => 'Sélectionnez au moins une table.',
        ]);

        $tablesAExporter = $data['scope'] === 'base_entiere'
            ? array_keys(self::TABLES_GEREES)
            : $data['tables'];

        $actifsSeuls   = $request->boolean('actifs_seuls');
        $inclureImages = $request->boolean('inclure_images');

        Log::info('Export de sauvegarde généré', [
            'format' => $data['format'],
            'scope'  => $data['scope'],
            'tables' => $tablesAExporter,
            'admin'  => Auth::user()->email,
        ]);

        $horodatage = now()->format('Ymd_His');

        return match ($data['format']) {
            'sql' => response($this->genererDumpSql($tablesAExporter, $actifsSeuls))
                ->header('Content-Type', 'application/sql; charset=UTF-8')
                ->header('Content-Disposition', "attachment; filename=\"sauvegarde_{$horodatage}.sql\""),

            'csv' => response()->download(
                $this->genererCsvZip($tablesAExporter, $actifsSeuls, $inclureImages),
                "sauvegarde_{$horodatage}.zip"
            )->deleteFileAfterSend(true),

            'pdf' => $this->genererPdf($tablesAExporter, $actifsSeuls)
                ->stream("sauvegarde_{$horodatage}.pdf"),
        };
    }

    // =========================================================
    // CORBEILLE — liste paginée des éléments supprimés (AJAX)
    // GET /admin/sauvegarde/corbeille?table=...&page=...
    // =========================================================

    public function elementsCorbeille(Request $request)
    {
        $config = $this->resoudreConfig($request->get('table'));

        if (!$config['restaurable']) {
            return response()->json([
                'success' => false,
                'message' => 'Cette table ne dispose pas de corbeille.',
            ], 422);
        }

        $lignes = DB::table($config['table'])
            ->whereNotNull('void')
            ->orderByDesc($config['pk'])
            ->paginate(15);

        return response()->json([
            'success'    => true,
            'pk'         => $config['pk'],
            'affichage'  => $config['affichage'],
            'label'      => $config['label'],
            'data'       => $lignes->items(),
            'pagination' => [
                'current_page' => $lignes->currentPage(),
                'last_page'    => $lignes->lastPage(),
                'total'        => $lignes->total(),
            ],
        ]);
    }

    // =========================================================
    // RESTAURER une sélection d'éléments d'une table
    // PATCH /admin/sauvegarde/restaurer
    // =========================================================

    public function restaurer(Request $request)
    {
        $data = $request->validate([
            'table' => 'required|string',
            'ids'   => 'required|array|min:1',
        ]);

        $config = $this->resoudreConfig($data['table']);

        if (!$config['restaurable']) {
            return response()->json(['success' => false, 'message' => 'Table non restaurable.'], 422);
        }

        $nb = DB::table($config['table'])
            ->whereIn($config['pk'], $data['ids'])
            ->whereNotNull('void')
            ->update(['void' => null]);

        Log::info('Restauration de données', [
            'table' => $config['table'],
            'ids'   => $data['ids'],
            'nb'    => $nb,
            'admin' => Auth::user()->email,
        ]);

        return response()->json([
            'success' => true,
            'message' => "{$nb} élément(s) restauré(s) avec succès.",
        ]);
    }

    // =========================================================
    // SUPPRIMER DÉFINITIVEMENT une sélection d'éléments
    // DELETE /admin/sauvegarde/supprimer
    //
    // [SÉCURITÉ] ne cible jamais que des lignes déjà void'ées.
    // =========================================================

    public function supprimerDefinitivement(Request $request)
    {
        $data = $request->validate([
            'table' => 'required|string',
            'ids'   => 'required|array|min:1',
        ]);

        $config = $this->resoudreConfig($data['table']);

        if (!$config['restaurable']) {
            return response()->json(['success' => false, 'message' => 'Table non gérée par la corbeille.'], 422);
        }

        $lignes = DB::table($config['table'])
            ->whereIn($config['pk'], $data['ids'])
            ->whereNotNull('void')
            ->get();

        if ($lignes->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun élément valide à supprimer (déjà purgé ou toujours actif).',
            ], 422);
        }

        // [SÉCURITÉ] categories → menus est en ON DELETE CASCADE côté
        // base de données : purger une catégorie supprime AUTOMATIQUEMENT
        // et silencieusement tous ses plats en base, même actifs. On
        // nettoie donc leurs images avant que la cascade ne s'exécute,
        // sinon elles restent orphelines sur le disque.
        if ($data['table'] === 'categories') {
            $menusAffectes = DB::table('menus')
                ->whereIn('idcategorie', $lignes->pluck('idcategorie'))
                ->get();

            foreach ($menusAffectes as $menu) {
                $this->supprimerImageSiPresente($menu->photo ?? null);
            }
        }

        if ($config['image']) {
            foreach ($lignes as $ligne) {
                $this->supprimerImageSiPresente($ligne->{$config['image']} ?? null);
            }
        }

        // [SÉCURITÉ] lignes.idmenu et historiques.idstatut sont en
        // ON DELETE RESTRICT : purger un menu déjà commandé, ou un
        // statut déjà utilisé dans l'historique, est bloqué par la
        // base de données. On intercepte pour renvoyer un message
        // clair plutôt qu'une erreur SQL brute.
        try {
            $nb = DB::table($config['table'])
                ->whereIn($config['pk'], $lignes->pluck($config['pk']))
                ->delete();
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return response()->json([
                    'success' => false,
                    'message' => "Impossible de supprimer : cet élément est encore référencé par d'autres données (ex. des lignes de commande ou un historique de statut). Ces dépendances doivent être traitées avant toute purge définitive.",
                ], 422);
            }
            throw $e;
        }

        Log::warning('Suppression définitive de données', [
            'table' => $config['table'],
            'ids'   => $data['ids'],
            'nb'    => $nb,
            'admin' => Auth::user()->email,
        ]);

        return response()->json([
            'success' => true,
            'message' => "{$nb} élément(s) supprimé(s) définitivement.",
        ]);
    }

    // =========================================================
    // VIDER TOUTE LA CORBEILLE (toutes tables confondues)
    // POST /admin/sauvegarde/vider-corbeille
    //
    // [SÉCURITÉ RENFORCÉE] mot de passe + saisie du mot
    // "SUPPRIMER" requis avant exécution. Action extrêmement
    // destructive et irréversible.
    // =========================================================

    public function viderCorbeille(Request $request)
    {
        $request->validate([
            'password'     => 'required|string',
            'confirmation' => 'required|in:SUPPRIMER',
        ], [
            'confirmation.in' => 'Vous devez saisir exactement le mot "SUPPRIMER" pour confirmer.',
        ]);

        if (!Hash::check($request->password, Auth::user()->password)) {
            return back()->with('error', 'Mot de passe incorrect. Opération annulée.');
        }

        // [MODIFIÉ] une transaction PAR TABLE plutôt qu'une seule
        // transaction globale : si une table échoue à cause d'une
        // contrainte RESTRICT (menus, historiques), les autres tables
        // sont quand même purgées au lieu de tout annuler.
        $total  = 0;
        $echecs = [];

        foreach (self::TABLES_GEREES as $cle => $config) {
            if (!$config['restaurable']) {
                continue;
            }

            DB::beginTransaction();

            try {
                $lignes = DB::table($config['table'])->whereNotNull('void')->get();

                if ($lignes->isEmpty()) {
                    DB::commit();
                    continue;
                }

                // Même nettoyage cascade categories → menus que dans
                // supprimerDefinitivement().
                if ($cle === 'categories') {
                    $menusAffectes = DB::table('menus')
                        ->whereIn('idcategorie', $lignes->pluck('idcategorie'))
                        ->get();

                    foreach ($menusAffectes as $menu) {
                        $this->supprimerImageSiPresente($menu->photo ?? null);
                    }
                }

                if ($config['image']) {
                    foreach ($lignes as $ligne) {
                        $this->supprimerImageSiPresente($ligne->{$config['image']} ?? null);
                    }
                }

                $total += DB::table($config['table'])->whereNotNull('void')->delete();

                DB::commit();

            } catch (QueryException $e) {
                DB::rollBack();
                $echecs[] = $config['label'];
                Log::error('Échec purge lors du vidage complet de la corbeille', [
                    'table' => $config['table'],
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::warning('Corbeille vidée', [
            'total'  => $total,
            'echecs' => $echecs,
            'admin'  => Auth::user()->email,
        ]);

        if (!empty($echecs)) {
            return back()->with('error',
                "{$total} élément(s) supprimé(s). Échec pour : " . implode(', ', $echecs) .
                ' (encore référencés par d\'autres données — commandes, historique...).'
            );
        }

        return back()->with('success', "Corbeille vidée : {$total} élément(s) supprimé(s) définitivement.");
    }

    // =========================================================
    // MÉTHODES PRIVÉES — GÉNÉRATION DES EXPORTS
    // =========================================================

    private function genererDumpSql(array $tables, bool $actifsSeuls): string
    {
        $pdo = DB::connection()->getPdo();

        $sql  = "-- ══════════════════════════════════════════════════\n";
        $sql .= "-- Sauvegarde générée le " . now()->format('d/m/Y à H:i:s') . "\n";
        $sql .= "-- Application : " . config('app.name') . "\n";
        $sql .= "-- [ATTENTION] Ce script vide (TRUNCATE) chaque table\n";
        $sql .= "-- avant réinsertion. Ne l'exécuter que sur une base\n";
        $sql .= "-- destinée à être restaurée, jamais en production sans\n";
        $sql .= "-- vérification préalable.\n";
        $sql .= "-- ══════════════════════════════════════════════════\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $cle) {
            $config    = $this->resoudreConfig($cle);
            $nomTable  = $config['table'];
            $colonnes  = Schema::getColumnListing($nomTable);

            $query = DB::table($nomTable);
            if ($actifsSeuls && $config['restaurable']) {
                $query->whereNull('void');
            }
            $lignes = $query->get();

            $sql .= "-- ── Table : {$nomTable} ({$lignes->count()} ligne(s)) ──\n";
            $sql .= "TRUNCATE TABLE `{$nomTable}`;\n";

            foreach ($lignes as $ligne) {
                $valeurs = [];
                foreach ($colonnes as $col) {
                    $val       = $ligne->$col;
                    $valeurs[] = is_null($val) ? 'NULL' : $pdo->quote((string) $val);
                }
                $sql .= "INSERT INTO `{$nomTable}` (`" . implode('`,`', $colonnes) . "`) VALUES (" . implode(',', $valeurs) . ");\n";
            }
            $sql .= "\n";
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        return $sql;
    }

    private function genererCsvZip(array $tables, bool $actifsSeuls, bool $inclureImages): string
    {
        $nomZip    = 'sauvegarde_csv_' . now()->format('Ymd_His') . '.zip';
        $dossier   = storage_path('app/temp');
        $cheminZip = $dossier . '/' . $nomZip;

        if (!is_dir($dossier)) {
            mkdir($dossier, 0755, true);
        }

        $zip = new ZipArchive();
        $zip->open($cheminZip, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($tables as $cle) {
            $config   = $this->resoudreConfig($cle);
            $nomTable = $config['table'];
            $colonnes = Schema::getColumnListing($nomTable);

            $query = DB::table($nomTable);
            if ($actifsSeuls && $config['restaurable']) {
                $query->whereNull('void');
            }
            $lignes = $query->get();

            $flux = fopen('php://temp', 'w+');
            // BOM UTF-8 pour un affichage correct des accents dans Excel
            fwrite($flux, "\xEF\xBB\xBF");
            fputcsv($flux, $colonnes);

            foreach ($lignes as $ligne) {
                $ligneCsv = [];
                foreach ($colonnes as $col) {
                    $ligneCsv[] = $ligne->$col;
                }
                fputcsv($flux, $ligneCsv);
            }

            rewind($flux);
            $contenu = stream_get_contents($flux);
            fclose($flux);

            $zip->addFromString($nomTable . '.csv', $contenu);

            if ($inclureImages && $config['image']) {
                $this->copierImagesDansZip($zip, $nomTable, $lignes, $config['image']);
            }
        }

        $zip->close();

        return $cheminZip;
    }

    private function genererPdf(array $tables, bool $actifsSeuls)
    {
        $donnees = [];

        foreach ($tables as $cle) {
            $config   = $this->resoudreConfig($cle);
            $nomTable = $config['table'];
            $colonnes = Schema::getColumnListing($nomTable);

            $query = DB::table($nomTable);
            if ($actifsSeuls && $config['restaurable']) {
                $query->whereNull('void');
            }

            $total  = (clone $query)->count();
            // [LIMITE] 500 lignes/table pour garder un PDF exploitable
            $lignes = $query->limit(500)->get();

            $donnees[] = [
                'label'    => $config['label'],
                'table'    => $nomTable,
                'colonnes' => $colonnes,
                'lignes'   => $lignes,
                'total'    => $total,
                'tronque'  => $total > 500,
            ];
        }

        $parametres = Parametre::first() ?? new Parametre();

        return Pdf::loadView('sauvegarde.export-pdf', compact('donnees', 'parametres'))
            ->setPaper('a4', 'landscape');
    }

    private function copierImagesDansZip(ZipArchive $zip, string $nomTable, $lignes, string $champImage): void
    {
        foreach ($lignes as $ligne) {
            $chemin = $ligne->$champImage ?? null;
            if (!$chemin) {
                continue;
            }

            $cheminComplet = storage_path('app/public/' . $chemin);
            if (file_exists($cheminComplet)) {
                $zip->addFile($cheminComplet, 'images/' . $nomTable . '/' . basename($chemin));
            }
        }
    }

    // =========================================================
    // MÉTHODES PRIVÉES — DIVERS
    // =========================================================

    private function resoudreConfig(?string $cle): array
    {
        if (!$cle || !isset(self::TABLES_GEREES[$cle])) {
            abort(404, 'Table inconnue ou non gérée par ce module.');
        }

        return self::TABLES_GEREES[$cle];
    }

    private function supprimerImageSiPresente(?string $chemin): void
    {
        if ($chemin && Storage::disk('public')->exists($chemin)) {
            Storage::disk('public')->delete($chemin);
        }
    }
}