<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckStatut
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (Auth::check() && Auth::user()->statut !== 'Activé') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                             ->with('error', 'Votre compte a été désactivé. Contactez l\'administrateur.');
        }

        return $next($request);
    }
}
