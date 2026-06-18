<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    description: "API REST pour la plateforme de gestion du restaurant",
    title: "CHEZCLARENCE API",
    contact: new OA\Contact(
        name: "HI-TECH Vision SARL",
        email: "contact@hi-techvisioncm.com"
    )
)]
#[OA\Server(
    url: "http://127.0.0.1:8000",
    description: "Serveur de développement local"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT"
)]
#[OA\PathItem(path: "/api")]
class SwaggerInfo {}