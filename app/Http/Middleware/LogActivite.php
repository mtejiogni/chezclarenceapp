<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogActivite
{
    public function handle(Request $request, Closure $next): mixed
    {
        $response = $next($request);

        // Logger uniquement les actions qui modifient des données
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            if (Auth::check()) {
                Log::channel('activites')->info('Action utilisateur', [
                    'user'    => Auth::user()->email,
                    'role'    => Auth::user()->role,
                    'methode' => $request->method(),
                    'url'     => $request->fullUrl(),
                    'ip'      => $request->ip(),
                    'statut'  => $response->getStatusCode(),
                ]);
            }
        }

        return $response;
    }
}
