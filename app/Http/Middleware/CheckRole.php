<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Filtre l'accès selon le rôle de l'utilisateur connecté.
     *
     * @param  Request  $request   La requête HTTP entrante
     * @param  Closure  $next      Le maillon suivant de la chaîne de middlewares
     * @param  string   ...$roles  Les rôles autorisés, passés depuis la route
     *                             ex : ->middleware('role:Caissier,Administrateur')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // ---- 1) L'utilisateur est-il authentifié ? ----
        // Auth::check() renvoie false si aucune session valide n'existe.
        if (! Auth::check()) {
            return redirect()
                ->route('login')
                ->with('error', 'Veuillez vous connecter pour continuer.');
        }
 
        $user = Auth::user(); // L'utilisateur connecté (modèle App\Models\User)
 
        // ---- 2) Le compte est-il toujours actif ? ----
        // Un compte désactivé par l'administrateur est immédiatement déconnecté,
        // même si sa session était encore valide.
        if ($user->statut !== 'Activé') {
            Auth::logout();
            return redirect()
                ->route('login')
                ->with('error', 'Votre compte a été désactivé. Contactez l\'administrateur.');
        }
 
        // ---- 3) Le rôle est-il autorisé pour cette page ? ----
        // Le 3e argument "true" de in_array force une comparaison stricte (===).
        if (! in_array($user->role, $roles, true)) {
            // 403 = "Interdit". L'utilisateur est connecté mais n'a pas les droits.
            abort(403, 'Accès refusé : vous n\'avez pas les droits nécessaires.');
        }
 
        // ---- 4) Tout est bon : on laisse la requête poursuivre sa route ----
        return $next($request);
    }
}
