<?php
 
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\MenuApiController;
use Illuminate\Support\Facades\Route;
 
// ===== ROUTES PUBLIQUES (sans jeton) =====
Route::post('/login', [AuthApiController::class, 'login']);
 
// ===== ROUTES PROTÉGÉES (jeton JWT obligatoire) =====
// Le middleware "auth:api" rejette toute requête sans jeton valide (401).
Route::middleware('auth:api')->group(function () {
    Route::get('/me',      [AuthApiController::class, 'me']);
    Route::post('/logout', [AuthApiController::class, 'logout']);
    Route::post('/refresh',[AuthApiController::class, 'refresh']);
 
    //Route::get('/menus',   [MenuApiController::class, 'index']);
});
