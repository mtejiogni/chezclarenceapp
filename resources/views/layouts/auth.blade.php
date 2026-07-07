<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Connexion') — {{ $restaurantNom ?? 'Chez Clarence' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Tailwind + Alpine.js (compilés par Vite) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        :root {
            --cc-orange:  #ea580c;
            --cc-orange2: #f97316;
            --cc-dark:    #050505;
            --cc-dark2:   #0d0d0d;
            --cc-dark3:   #141414;
            --cc-border:  #1f1f1f;
        }
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--cc-dark);
            margin: 0;
        }
    </style>

    @stack('styles')
</head>
<body class="antialiased">

    @yield('content')

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @stack('scripts')
</body>
</html>