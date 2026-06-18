<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès refusé — 403</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center font-sans p-4">
    <div class="text-center max-w-md">
        <div class="w-24 h-24 bg-red-100 rounded-full
                    flex items-center justify-center mx-auto mb-6">
            <i class="fa-solid fa-lock text-red-500 text-4xl"></i>
        </div>
        <h1 class="text-7xl font-extrabold text-red-500 mb-2">403</h1>
        <h2 class="text-2xl font-bold text-gray-800 mb-3">Accès refusé</h2>
        <p class="text-gray-500 mb-8">
            Vous n'avez pas les droits pour accéder à cette page.
        </p>
        <div class="flex gap-3 justify-center">
            <a href="{{ route('dashboard') }}" class="btn-primary">
                <i class="fa-solid fa-house"></i> Tableau de bord
            </a>
            <button onclick="history.back()" class="btn-secondary">
                <i class="fa-solid fa-arrow-left"></i> Retour
            </button>
        </div>
    </div>
</body>
</html>