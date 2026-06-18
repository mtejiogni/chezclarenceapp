<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AuthApiController extends Controller
{
    /**
     * Connexion : renvoie un jeton JWT si les identifiants sont valides.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);
 
        // Le guard "api" tente l'authentification et renvoie un jeton, ou false.
        if (! $token = Auth::guard('api')->attempt($credentials)) {
            return response()->json(['message' => 'Identifiants invalides.'], 401);
        }
 
        return $this->respondWithToken($token);
    }
 
    /**
     * Renvoie le profil de l'utilisateur identifié par son jeton.
     */
    public function me(): JsonResponse
    {
        return response()->json(Auth::guard('api')->user());
    }
 
    /**
     * Déconnexion : invalide le jeton courant (mis en liste noire).
     */
    public function logout(): JsonResponse
    {
        Auth::guard('api')->logout();
        return response()->json(['message' => 'Déconnecté avec succès.']);
    }
 
    /**
     * Rafraîchit le jeton avant son expiration (renvoie un nouveau jeton).
     */
    public function refresh(): JsonResponse
    {
        return $this->respondWithToken(Auth::guard('api')->refresh());
    }
 
    /**
     * Met en forme la réponse contenant le jeton et ses métadonnées.
     */
    protected function respondWithToken(string $token): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            // Durée de validité en secondes (TTL exprimé en minutes x 60).
            'expires_in'   => Auth::guard('api')->factory()->getTTL() * 60,
            'user'         => Auth::guard('api')->user(),
        ]);
    }
}

