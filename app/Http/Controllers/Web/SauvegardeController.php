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
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use ZipArchive;

// ══════════════════════════════════════════════════════════════
// MODULE SAUVEGARDE ET RESTAURATION (Administrateur et Caissier)
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

    // Ordre d'import respectant les dépendances de clé étrangère
    // (parents avant enfants) : categories avant menus, commandes
    // avant lignes/historiques, etc. FOREIGN_KEY_CHECKS est de
    // toute façon désactivé pendant tout l'import, mais respecter
    // cet ordre reste plus sûr et plus lisible dans les logs.
    private const ORDRE_IMPORT = [
        'utilisateurs', 'categories', 'tables', 'statuts',
        'menus', 'commandes', 'lignes', 'historiques', 'parametres',
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
            // [CORRIGÉ] le SQL ignorait totalement $inclureImages —
            // aucun zip n'était jamais généré, contrairement au CSV.
            // On ne zippe que si des images sont demandées ET qu'au
            // moins une table exportée en possède réellement.
            'sql' => ($inclureImages && $this->desTablesOntDesImages($tablesAExporter))
                ? response()->download(
                    $this->genererSqlZip($tablesAExporter, $actifsSeuls),
                    "sauvegarde_{$horodatage}.zip"
                )->deleteFileAfterSend(true)
                : response($this->genererDumpSql($tablesAExporter, $actifsSeuls))
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
    // IMPORT — ÉTAPE 1 : ANALYSE / APERÇU
    // POST /admin/sauvegarde/import/analyser
    //
    // Reçoit le ZIP, l'extrait dans un dossier temporaire identifié
    // par un token, puis calcule un aperçu (nouveaux/doublons par
    // table, images trouvées/manquantes) SANS RIEN ÉCRIRE en base.
    // Le token est ensuite utilisé par executerImport() pour agir
    // sur ce même contenu déjà extrait, sans re-uploader le fichier.
    // =========================================================

    public function analyserImport(Request $request)
    {
        $request->validate([
            'fichier' => 'required|file|mimes:zip|max:51200', // 50 Mo
        ], [
            'fichier.mimes' => 'Le fichier doit être une archive .zip.',
            'fichier.max'   => 'L\'archive ne doit pas dépasser 50 Mo.',
        ]);

        $token       = (string) Str::uuid();
        $dossierTemp = storage_path('app/temp/imports/' . $token);
        mkdir($dossierTemp, 0755, true);

        $cheminZip = $dossierTemp . '/archive.zip';
        $request->file('fichier')->move($dossierTemp, 'archive.zip');

        $zip = new ZipArchive();
        if ($zip->open($cheminZip) !== true) {
            $this->nettoyerDossierTemp($token);
            return response()->json(['success' => false, 'message' => 'Archive ZIP illisible ou corrompue.'], 422);
        }
        $zip->extractTo($dossierTemp);
        $zip->close();

        // Recherche du fichier .sql (peu importe son nom exact, tant
        // qu'il est à la racine de l'archive)
        $fichierSql = collect(glob($dossierTemp . '/*.sql'))->first();

        if (!$fichierSql) {
            $this->nettoyerDossierTemp($token);
            return response()->json(['success' => false, 'message' => 'Aucun fichier .sql trouvé à la racine de l\'archive.'], 422);
        }

        $contenuSql = file_get_contents($fichierSql);
        $insertionsParTable = $this->extraireInsertionsParTable($contenuSql);

        if (empty($insertionsParTable)) {
            $this->nettoyerDossierTemp($token);
            return response()->json(['success' => false, 'message' => 'Aucune donnée exploitable trouvée dans le fichier SQL.'], 422);
        }

        $dossierImages = $dossierTemp . '/images';
        $apercu = [];
        $tablesInconnues = [];

        foreach ($insertionsParTable as $nomTable => $insertions) {
            $cle = $this->cleParNomTable($nomTable);

            if (!$cle) {
                $tablesInconnues[] = $nomTable;
                continue;
            }

            $config = self::TABLES_GEREES[$cle];
            $pk     = $config['pk'];

            $nomTemp = $this->chargerDansTableTemporaire($nomTable, $insertions);
            $total    = DB::table($nomTemp)->count();
            $doublons = DB::table($nomTemp)->whereIn($pk, function ($q) use ($nomTable, $pk) {
                $q->select($pk)->from($nomTable);
            })->count();

            // Images attendues (lignes dont le champ image n'est pas
            // vide) vs images réellement présentes dans l'archive
            $imagesAttendues  = 0;
            $imagesPresentes  = 0;
            if ($config['image']) {
                $lignesAvecImage = DB::table($nomTemp)->whereNotNull($config['image'])->pluck($config['image']);
                $imagesAttendues = $lignesAvecImage->count();
                foreach ($lignesAvecImage as $chemin) {
                    if (file_exists($dossierImages . '/' . $nomTable . '/' . basename($chemin))) {
                        $imagesPresentes++;
                    }
                }
            }

            DB::unprepared("DROP TEMPORARY TABLE IF EXISTS `{$nomTemp}`");

            $apercu[$cle] = [
                'label'            => $config['label'],
                'table'            => $nomTable,
                'total_fichier'    => $total,
                'nouveaux'         => $total - $doublons,
                'doublons'         => $doublons,
                'images_attendues' => $imagesAttendues,
                'images_presentes' => $imagesPresentes,
            ];
        }

        Log::info('Analyse d\'import de sauvegarde', [
            'token' => $token, 'tables' => array_keys($apercu), 'admin' => Auth::user()->email,
        ]);

        return response()->json([
            'success'          => true,
            'token'            => $token,
            'apercu'           => $apercu,
            'tables_inconnues' => $tablesInconnues,
        ]);
    }

    // =========================================================
    // IMPORT — ÉTAPE 2 : EXÉCUTION CONFIRMÉE
    // POST /admin/sauvegarde/import/executer
    //
    // [SÉCURITÉ] le mode "remplacer" (TRUNCATE + réinsertion) exige
    // le mot de passe administrateur, comme pour vider la corbeille.
    // =========================================================

    public function executerImport(Request $request)
    {
        $data = $request->validate([
            'token'    => 'required|uuid',
            'mode'     => 'required|in:remplacer,fusionner_ignorer,fusionner_ecraser',
            'tables'   => 'required|array|min:1',
            'tables.*' => 'string|in:' . implode(',', array_keys(self::TABLES_GEREES)),
            'password' => 'required_if:mode,remplacer|nullable|string',
        ]);

        if ($data['mode'] === 'remplacer' && !Hash::check($data['password'] ?? '', Auth::user()->password)) {
            return response()->json(['success' => false, 'message' => 'Mot de passe incorrect. Import annulé.'], 422);
        }

        $dossierTemp = storage_path('app/temp/imports/' . $data['token']);
        $dossierImages = $dossierTemp . '/images';
        $fichierSql  = collect(glob($dossierTemp . '/*.sql'))->first();

        if (!$fichierSql) {
            return response()->json(['success' => false, 'message' => 'Session d\'import expirée ou introuvable. Recommencez l\'analyse.'], 422);
        }

        $insertionsParTable = $this->extraireInsertionsParTable(file_get_contents($fichierSql));

        $resultats = [];
        $echecs    = [];

        // On respecte ORDRE_IMPORT plutôt que l'ordre d'apparition
        // dans le fichier, pour insérer les tables parentes avant
        // leurs enfants (categories avant menus, etc.)
        foreach (self::ORDRE_IMPORT as $cle) {
            if (!in_array($cle, $data['tables'], true)) {
                continue;
            }

            $config   = self::TABLES_GEREES[$cle];
            $nomTable = $config['table'];

            if (!isset($insertionsParTable[$nomTable])) {
                continue; // table sélectionnée mais absente du fichier
            }

            DB::beginTransaction();

            try {
                $nomTemp = $this->chargerDansTableTemporaire($nomTable, $insertionsParTable[$nomTable]);
                $pk      = $config['pk'];

                $avant = DB::table($nomTable)->count();

                if ($data['mode'] === 'remplacer') {
                    DB::statement('SET FOREIGN_KEY_CHECKS=0');
                    DB::table($nomTable)->truncate();
                    DB::unprepared("INSERT INTO `{$nomTable}` SELECT * FROM `{$nomTemp}`");
                    DB::statement('SET FOREIGN_KEY_CHECKS=1');

                } elseif ($data['mode'] === 'fusionner_ignorer') {
                    DB::unprepared("INSERT IGNORE INTO `{$nomTable}` SELECT * FROM `{$nomTemp}`");

                } else { // fusionner_ecraser
                    // [IMPORTANT] ON DUPLICATE KEY UPDATE plutôt que
                    // REPLACE INTO : REPLACE fait un DELETE puis un
                    // INSERT, ce qui déclencherait le CASCADE
                    // categories → menus et supprimerait des plats
                    // par effet de bord. ON DUPLICATE KEY UPDATE fait
                    // une vraie mise à jour, sans suppression.
                    $colonnes = array_filter(Schema::getColumnListing($nomTable), fn ($c) => $c !== $pk);
                    $assignations = collect($colonnes)->map(fn ($c) => "`{$c}` = VALUES(`{$c}`)")->implode(', ');
                    DB::unprepared("INSERT INTO `{$nomTable}` SELECT * FROM `{$nomTemp}` ON DUPLICATE KEY UPDATE {$assignations}");
                }

                // Copie des images pour les lignes importées
                $imagesCopiees = 0;
                if ($config['image']) {
                    $chemins = DB::table($nomTemp)->whereNotNull($config['image'])->pluck($config['image']);
                    foreach ($chemins as $chemin) {
                        if ($this->copierImageImportee($dossierImages, $nomTable, $chemin)) {
                            $imagesCopiees++;
                        }
                    }
                }

                DB::unprepared("DROP TEMPORARY TABLE IF EXISTS `{$nomTemp}`");

                $apres = DB::table($nomTable)->count();

                DB::commit();

                $resultats[$cle] = [
                    'label'          => $config['label'],
                    'avant'          => $avant,
                    'apres'          => $apres,
                    'images_copiees' => $imagesCopiees,
                ];

            } catch (QueryException $e) {
                DB::rollBack();
                $echecs[] = $config['label'];
                Log::error('Échec import table', ['table' => $nomTable, 'error' => $e->getMessage()]);
            }
        }

        $this->nettoyerDossierTemp($data['token']);

        Log::warning('Import de sauvegarde exécuté', [
            'mode' => $data['mode'], 'resultats' => $resultats, 'echecs' => $echecs, 'admin' => Auth::user()->email,
        ]);

        return response()->json([
            'success'   => empty($echecs),
            'message'   => empty($echecs)
                ? 'Import terminé avec succès.'
                : 'Import terminé avec des échecs sur : ' . implode(', ', $echecs) . ' (contraintes de clé étrangère).',
            'resultats' => $resultats,
            'echecs'    => $echecs,
        ]);
    }

    // =========================================================
    // MÉTHODES PRIVÉES — GÉNÉRATION DES EXPORTS
    // =========================================================

    /**
     * Vérifie si au moins une des tables sélectionnées possède un
     * champ image — évite de générer un ZIP inutile (ex. export
     * SQL des seules 'commandes', qui n'ont pas d'image).
     */
    private function desTablesOntDesImages(array $tables): bool
    {
        foreach ($tables as $cle) {
            if (!empty(self::TABLES_GEREES[$cle]['image'] ?? null)) {
                return true;
            }
        }

        return false;
    }

    /**
     * [AJOUT] Même principe que genererCsvZip() : le dump .sql est
     * placé à la racine du ZIP, accompagné d'un dossier images/
     * reprenant les fichiers liés aux tables exportées. Corrige le
     * bug où l'export SQL ignorait totalement l'option "Inclure les
     * images" et ne produisait jamais de ZIP.
     */
    private function genererSqlZip(array $tables, bool $actifsSeuls): string
    {
        $nomZip    = 'sauvegarde_sql_' . now()->format('Ymd_His') . '.zip';
        $dossier   = storage_path('app/temp');
        $cheminZip = $dossier . '/' . $nomZip;

        if (!is_dir($dossier)) {
            mkdir($dossier, 0755, true);
        }

        $zip = new ZipArchive();
        $zip->open($cheminZip, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $zip->addFromString('sauvegarde.sql', $this->genererDumpSql($tables, $actifsSeuls));

        foreach ($tables as $cle) {
            $config = $this->resoudreConfig($cle);

            if (!$config['image']) {
                continue;
            }

            $query = DB::table($config['table']);
            if ($actifsSeuls && $config['restaurable']) {
                $query->whereNull('void');
            }
            $lignes = $query->get();

            $this->copierImagesDansZip($zip, $config['table'], $lignes, $config['image']);
        }

        $zip->close();

        return $cheminZip;
    }

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

    // =========================================================
    // MÉTHODES PRIVÉES — IMPORT
    // =========================================================

    /**
     * Découpe un texte SQL en instructions individuelles, en
     * respectant les points-virgules à l'intérieur des chaînes
     * entre guillemets (ex. "Merci ; bonne soirée" dans un champ
     * consignes ne doit pas couper l'instruction en deux).
     *
     * [POURQUOI PAS UN PARSER SQL COMPLET] Plutôt que de parser
     * chaque valeur en PHP (fragile face aux échappements PDO), on
     * ne fait ici que le strict nécessaire : isoler des instructions
     * complètes. Le contenu de chaque instruction est ensuite confié
     * tel quel à MySQL via DB::unprepared(), qui sait parfaitement
     * parser ses propres littéraux — beaucoup plus fiable qu'un
     * parseur SQL maison.
     */
    private function decouperInstructionsSql(string $sql): array
    {
        $instructions     = [];
        $courant          = '';
        $dansChaine       = false;
        $dansCommentaire  = false;
        $longueur         = strlen($sql);

        for ($i = 0; $i < $longueur; $i++) {
            $car = $sql[$i];
            $courant .= $car;

            // Un commentaire SQL en ligne ('-- ...') peut
            // légitimement contenir une apostrophe française non
            // échappée (ex. "Ne l'exécuter que..."). Sans cette
            // détection, une telle apostrophe peu être interprétée comme
            // un début de chaîne SQL, qui peut corrompre le parsing de
            // tout le reste du fichier en aval.
            if (!$dansChaine && !$dansCommentaire && $car === '-' && ($i + 1) < $longueur && $sql[$i + 1] === '-') {
                $dansCommentaire = true;
            }

            if ($dansCommentaire && $car === "\n") {
                $dansCommentaire = false;
            }

            if ($dansCommentaire) {
                continue;
            }

            if ($car === "'" && ($i === 0 || $sql[$i - 1] !== '\\')) {
                $dansChaine = !$dansChaine;
            }

            if ($car === ';' && !$dansChaine) {
                $instructions[] = trim($courant);
                $courant = '';
            }
        }

        if (trim($courant) !== '') {
            $instructions[] = trim($courant);
        }

        return array_values(array_filter($instructions, fn ($i) => $i !== '' && !str_starts_with($i, '--')));
    }

    /**
     * Regroupe les instructions INSERT INTO par nom de table réel
     * (tel qu'écrit dans le fichier), en ignorant TRUNCATE/SET/
     * commentaires — l'import gère lui-même la troncature ou non
     * selon le mode choisi, il ne se fie jamais au TRUNCATE présent
     * dans le fichier original.
     */
    private function extraireInsertionsParTable(string $sql): array
    {
        $parTable = [];

        foreach ($this->decouperInstructionsSql($sql) as $instruction) {
            if (preg_match('/^INSERT INTO `(\w+)`/i', $instruction, $m)) {
                $parTable[$m[1]][] = $instruction;
            }
        }

        return $parTable;
    }

    /**
     * Retrouve la clé du registre TABLES_GEREES à partir du nom réel
     * de la table SQL (ex. 'menus' → 'menus', 'users' → 'utilisateurs').
     */
    private function cleParNomTable(string $nomTable): ?string
    {
        foreach (self::TABLES_GEREES as $cle => $config) {
            if ($config['table'] === $nomTable) {
                return $cle;
            }
        }

        return null;
    }

    /**
     * Crée une table MySQL temporaire de structure identique à la
     * table cible, y insère les instructions fournies (adaptées pour
     * cibler la table temporaire), et la retourne prête à l'emploi.
     * C'est MySQL qui parse les valeurs, pas notre code PHP.
     */
    private function chargerDansTableTemporaire(string $nomTable, array $insertions): string
    {
        $nomTemp = 'tmp_import_' . $nomTable . '_' . substr(str_replace('-', '', (string) Str::uuid()), 0, 10);

        DB::unprepared("DROP TEMPORARY TABLE IF EXISTS `{$nomTemp}`");
        DB::unprepared("CREATE TEMPORARY TABLE `{$nomTemp}` LIKE `{$nomTable}`");

        foreach ($insertions as $insert) {
            $adapte = preg_replace(
                '/^INSERT INTO `' . preg_quote($nomTable, '/') . '`/i',
                "INSERT INTO `{$nomTemp}`",
                $insert
            );
            DB::unprepared($adapte);
        }

        return $nomTemp;
    }

    /**
     * Copie un fichier image depuis le dossier images/{table}/ extrait
     * de l'archive vers son emplacement de destination réel dans
     * storage/app/public, en recréant les sous-dossiers nécessaires.
     */
    private function copierImageImportee(string $dossierImages, string $nomTable, ?string $cheminRelatif): bool
    {
        if (!$cheminRelatif) {
            return false;
        }

        $source = $dossierImages . '/' . $nomTable . '/' . basename($cheminRelatif);

        if (!file_exists($source)) {
            return false;
        }

        $destination         = storage_path('app/public/' . $cheminRelatif);
        $dossierDestination  = dirname($destination);

        if (!is_dir($dossierDestination)) {
            mkdir($dossierDestination, 0755, true);
        }

        return copy($source, $destination);
    }

    /**
     * Supprime le dossier temporaire d'une session d'import (archive
     * extraite + fichier SQL + images), une fois l'import terminé
     * (ou en cas d'échec d'ouverture du ZIP).
     *
     * [NOTE] Les dossiers non nettoyés (ex. utilisateur qui analyse
     * sans jamais confirmer) s'accumulent dans storage/app/temp/imports.
     * Prévoir idéalement une tâche planifiée (php artisan schedule)
     * pour purger les dossiers de plus de quelques heures.
     */
    private function nettoyerDossierTemp(string $token): void
    {
        // [SÉCURITÉ] validation stricte du format avant toute
        // opération sur le système de fichiers, pour empêcher une
        // traversée de chemin via un token trafiqué.
        if (!preg_match('/^[0-9a-f-]{36}$/i', $token)) {
            return;
        }

        $dossier = storage_path('app/temp/imports/' . $token);

        if (is_dir($dossier)) {
            $this->supprimerDossierRecursif($dossier);
        }
    }

    private function supprimerDossierRecursif(string $dossier): void
    {
        $items = scandir($dossier);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $chemin = $dossier . '/' . $item;
            is_dir($chemin) ? $this->supprimerDossierRecursif($chemin) : unlink($chemin);
        }
        rmdir($dossier);
    }
}