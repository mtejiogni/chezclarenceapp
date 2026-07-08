<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\CommandeController;
use App\Http\Controllers\Web\CaisseController;
use App\Http\Controllers\Web\StatutController;
use App\Http\Controllers\Web\MenuController;
use App\Http\Controllers\Web\TableController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\LivraisonController;
use App\Http\Controllers\Web\LigneController;
use App\Http\Controllers\Web\HistoriqueController;
use App\Http\Controllers\Web\StatistiqueController;
use App\Http\Controllers\Web\ParametreController;
use App\Http\Controllers\Web\CategorieController;
use App\Http\Controllers\Web\ClientCommandeController;
use App\Http\Controllers\Web\SauvegardeController;
use Illuminate\Support\Facades\Route;


// =========================================================
// PAGE PUBLIQUE
// =========================================================
Route::get('/', function () {
    return view('welcome');
})->name('front');





// =========================================================
// AUTHENTIFICATION
// =========================================================
Route::middleware('guest')->group(function () {

    Route::get('/connexion', 
        [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/connexion', 
        [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.post');
    
    Route::get('/inscription', 
        [AuthController::class, 'showRegister'])
        ->name('register');
    
    Route::post('/inscription', 
        [AuthController::class, 'register'])
        ->middleware('throttle:5,1')
        ->name('register.post');
});

Route::post('/deconnexion', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');










// =========================================================
// ESPACE CONNECTÉ
// =========================================================
Route::middleware(['auth', 'statut'])->group(function () {

    Route::get('/dashboard', 
        [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/dashboard/refresh', 
        [DashboardController::class, 'refresh'])
        ->name('dashboard.refresh');

    Route::get('/profil', 
        [AuthController::class, 'profil'])
        ->name('profil');

    Route::put('/profil', 
        [AuthController::class, 'updateProfil'])
        ->name('profil.update');

    Route::put('/profil/password', 
        [AuthController::class, 'updatePassword'])
        ->name('profil.password');

    // -- les notifications pour les commandes en attente
    Route::get('/notifications/commandes-en-attente',
        [CommandeController::class, 'notificationsEnAttente'])
        ->name('notifications.commandes-en-attente');


    // =========================================================
    // ADMINISTRATEUR
    // Toutes les routes ont le préfixe URL 'admin/' et le
    // préfixe de nom 'admin.' → ex: 'admin.tables.statut'
    // RÈGLE : charger les routes statiques AVANT les Route::resource()
    // =========================================================
    Route::middleware('role:Administrateur')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {

        // ── Utilisateurs ──────────────────────────────────────
        Route::patch('/utilisateurs/{user}/toggle-statut',
            [UserController::class, 'toggleStatut'])
            ->name('utilisateurs.toggle-statut');

        Route::post('/utilisateurs/{user}/reset-password',
            [UserController::class, 'resetPassword'])
            ->name('utilisateurs.reset-password');

        Route::delete('/utilisateurs/{user}/photo',
            [UserController::class, 'supprimerPhoto'])
            ->name('utilisateurs.supprimer-photo');

        Route::resource('utilisateurs', UserController::class)
            ->parameters(['utilisateurs' => 'user']);


        // ── Catégories ────────────────────────────────────────
        Route::patch('/categories/{categorie}/toggle-statut',
            [CategorieController::class, 'toggleStatut'])
            ->name('categories.toggle-statut');

        Route::delete('/categories/{categorie}/photo',
            [CategorieController::class, 'supprimerPhotoCat'])
            ->name('categories.supprimer-photo');

        Route::resource('categories', CategorieController::class)
            ->parameters(['categories' => 'categorie']);


        // ── Menus / Plats ─────────────────────────────────────
        Route::patch('/menus/{menu}/toggle-statut',
            [MenuController::class, 'toggleStatut'])
            ->name('menus.toggle-statut');

        Route::resource('menus', MenuController::class)
            ->parameters(['menus' => 'menu']);


        // ── Tables ────────────────────────────────────────────
        Route::get('/tables/statut-temps-reel',
            [TableController::class, 'statutTempsReel'])
            ->name('tables.statut');
        
        Route::patch('/tables/{table}/liberer',
            [TableController::class, 'liberer'])
            ->name('tables.liberer');

        // Resource APRÈS les routes statiques
        Route::resource('tables', TableController::class)
            ->parameters(['tables' => 'table']);


        // ── Statuts ───────────────────────────────────────────
        Route::get('/statuts/liste',
            [StatutController::class, 'liste'])
            ->name('statuts.liste');

        Route::post('/statuts/reordonner',
            [StatutController::class, 'reordonner'])
            ->name('statuts.reordonner');

        Route::resource('statuts', StatutController::class)
            ->parameters(['statuts' => 'statut']);


        // ── Statistiques ──────────────────────────────────────
        Route::get('/statistiques/export',
            [StatistiqueController::class, 'export'])
            ->name('statistiques.export');
        
        Route::get('/statistiques/refresh',
            [StatistiqueController::class, 'refresh'])
            ->name('statistiques.refresh');

        Route::get('/statistiques',
            [StatistiqueController::class, 'index'])
            ->name('statistiques');


        // ── Paramètres ────────────────────────────────────────
        Route::get('/parametres',
            [ParametreController::class, 'index'])
            ->name('parametres.index');

        Route::put('/parametres',
            [ParametreController::class, 'update'])
            ->name('parametres.update');
        
        Route::get('/api/parametres',
            [ParametreController::class, 'show'])
            ->name('parametres.show');
        
        Route::delete('/parametres/logo',
            [ParametreController::class, 'supprimerLogo'])
            ->name('parametres.supprimer-logo');
        
        Route::post('/parametres/vider-cache', 
            [ParametreController::class, 'viderCache'])
         ->name('parametres.vider-cache');



        // ── Historiques ────────────────────────────────────────
        Route::get('/historiques', 
            [HistoriqueController::class, 'index'])
            ->name('historiques.index');

        Route::get('/historiques/rapport', 
            [HistoriqueController::class, 'rapport'])
            ->name('historiques.rapport');

        Route::get('/historiques/timeline', 
            [HistoriqueController::class, 'timeline'])
            ->name('historiques.timeline');

        Route::get('/historiques/stats-jour', 
            [HistoriqueController::class, 'statsJour'])
            ->name('historiques.stats-jour');

        Route::delete('/historiques/{historique}', 
            [HistoriqueController::class, 'destroy'])
            ->name('historiques.destroy');
        

        // ── Sauvegarde et restauration ───────────────────────────────────
        Route::get('/sauvegarde', 
            [SauvegardeController::class, 'index'])
            ->name('sauvegarde.index');
        
        Route::post('/sauvegarde/exporter', 
            [SauvegardeController::class, 'exporter'])
            ->name('sauvegarde.exporter');
        
        Route::get('/sauvegarde/corbeille', 
            [SauvegardeController::class, 'elementsCorbeille'])
            ->name('sauvegarde.corbeille');
        
        Route::patch('/sauvegarde/restaurer', 
            [SauvegardeController::class, 'restaurer'])
            ->name('sauvegarde.restaurer');
        
        Route::delete('/sauvegarde/supprimer', 
            [SauvegardeController::class, 'supprimerDefinitivement'])
            ->name('sauvegarde.supprimer');
        
        Route::post('/sauvegarde/vider-corbeille', 
            [SauvegardeController::class, 'viderCorbeille'])
            ->name('sauvegarde.vider-corbeille');

    });







    // =========================================================
    // CAISSE
    // =========================================================
    Route::middleware('role:Caissier,Administrateur')
        ->prefix('caisse')
        ->name('caisse.')
        ->group(function () {

        Route::get('/', 
            [CaisseController::class, 'index'])
            ->name('index');

        Route::get('/recu/{commande}', 
            [CaisseController::class, 'genererRecu'])
            ->name('recu');

        Route::get('/cloturer', 
            [CaisseController::class, 'cloturer'])
            ->name('cloturer');

        Route::get('/rapport/{date?}', 
            [CaisseController::class, 'rapport'])
            ->name('rapport');

        Route::patch('/annuler/{commande}', 
            [CaisseController::class, 'annuler'])
            ->name('annuler');
    });




    // =========================================================
    // COMMANDES
    // =========================================================
    Route::middleware('role:Serveur,Caissier,Administrateur')
        ->prefix('commandes')
        ->name('commandes.')
        ->group(function () {

        Route::get('/', [CommandeController::class, 'index'])
            ->name('index');

        Route::get('/nouvelle', [CommandeController::class, 'create'])
            ->name('create');

        Route::get('/tables/disponibles',
            [CommandeController::class, 'tablesDisponibles'])
            ->name('tables.disponibles');

        Route::post('/', 
            [CommandeController::class, 'store'])
            ->name('store');

        Route::get('/{commande}', 
            [CommandeController::class, 'show'])
            ->name('show');

        Route::get('/{commande}/modifier',
            [CommandeController::class, 'edit'])
            ->name('edit');

        Route::put('/{commande}', 
            [CommandeController::class, 'update'])
            ->name('update');

        Route::patch('/{commande}/statut',
            [CommandeController::class, 'updateStatut'])
            ->name('statut');

        Route::patch('/{commande}/noter',
            [CommandeController::class, 'noter'])
            ->name('noter');

        Route::delete('/{commande}', 
            [CommandeController::class, 'destroy'])
            ->name('destroy')
            ->middleware('role:Administrateur');
        
        // ── Historiques par commande (tous rôles connectés) ───────
        Route::get('/{commande}/historique',
            [HistoriqueController::class, 'parCommande'])
            ->name('historique');

        Route::post('/{commande}/historique',
            [HistoriqueController::class, 'store'])
            ->name('historique.store');
    });




    // =========================================================
    // LIVRAISONS
    // =========================================================
    Route::middleware('role:Livreur,Administrateur')
        ->prefix('livraisons')
        ->name('livraisons.')
        ->group(function () {

        Route::get('/', 
            [LivraisonController::class, 'index'])
            ->name('index');

        Route::get('/historique', 
            [LivraisonController::class, 'historique'])
            ->name('historique');

        Route::patch('/{commande}/statut',
            [LivraisonController::class, 'updateStatut'])
            ->name('statut');
        
        Route::get('/statut-temps-reel', 
            [LivraisonController::class, 'statutTempsReel'])
            ->name('statut-temps-reel');
    });



    // =========================================================
    // CUISINE
    // =========================================================
    Route::middleware('role:Cuisinier,Administrateur')
        ->prefix('cuisine')
        ->name('cuisine.')
        ->group(function () {

        Route::get('/', 
            [CommandeController::class, 'cuisine'])
            ->name('index');

        Route::patch('/{commande}/prendre-en-charge',
            [CommandeController::class, 'prendreEnCharge'])
            ->name('prendre-en-charge');

        Route::patch('/{commande}/prete',
            [CommandeController::class, 'marquerPrete'])
            ->name('prete');
    });


    // =========================================================
    // MES COMMANDES - CLIENT
    // =========================================================
    Route::middleware('role:Client')
        ->prefix('mes-commandes')
        ->name('mes-commandes.')
        ->group(function () {

        Route::get('/', 
            [ClientCommandeController::class, 'index'])
            ->name('index');
        
        Route::get('/nouvelle', 
            [ClientCommandeController::class, 'create'])
            ->name('create');
        
        Route::post('/', 
            [ClientCommandeController::class, 'store'])
            ->name('store');
        
        Route::get('/menu/liste', 
            [ClientCommandeController::class, 'menuListe'])
            ->name('menu.liste');
        
        Route::get('/{commande}', 
            [ClientCommandeController::class, 'show'])
            ->name('show');
        
        Route::get('/{commande}/modifier', 
            [ClientCommandeController::class, 'edit'])
            ->name('edit');
        
        Route::put('/{commande}', 
            [ClientCommandeController::class, 'update'])
            ->name('update');
        
        Route::patch('/{commande}/annuler', 
            [ClientCommandeController::class, 'annuler'])
            ->name('annuler');
        
        Route::get('/{commande}/recommander', 
            [ClientCommandeController::class, 'recommander'])
            ->name('recommander');
    });

});









// =========================================================
// LIGNES DE COMMANDES
// (hors groupe auth — à sécuriser si nécessaire)
// =========================================================
Route::prefix('commandes/{commande}/lignes')
    ->name('commandes.lignes.')
    ->group(function () {

    Route::post('/', [LigneController::class, 'store'])
        ->name('store');
    
    Route::get('/liste', [LigneController::class, 'liste'])
        ->name('liste');

    Route::put('/{ligne}', [LigneController::class, 'update'])
        ->name('update');

    Route::delete('/{ligne}', [LigneController::class, 'destroy'])
        ->name('destroy');

    Route::patch('/{ligne}/remise',
        [LigneController::class, 'appliquerRemise'])
        ->name('remise');
});

