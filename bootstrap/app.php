<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // On associe l'alias "role" à notre classe CheckRole.
        // On pourra alors écrire dans les routes : ->middleware('role:Administrateur')
        // Enregistrement des alias (noms courts pour les routes)
        $middleware->alias([
            'role'         => \App\Http\Middleware\CheckRole::class,
            'statut'       => \App\Http\Middleware\CheckStatut::class,
            'log.activite' => \App\Http\Middleware\LogActivite::class,
        ]);

        // Appliquer LogActivite sur toutes les requêtes web
        $middleware->appendToGroup('web', [
            \App\Http\Middleware\LogActivite::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
