<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') — Chez Clarence</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        :root {
            --cc-orange:  #ea580c;
            --cc-orange2: #f97316;
            --cc-dark:    #080808;
            --cc-dark2:   #0d0d0d;
            --cc-dark3:   #141414;
            --cc-dark4:   #1a1a1a;
            --cc-border:  #1f1f1f;
            --cc-text:    #e5e5e5;
            --cc-muted:   #6b7280;
        }

        * { box-sizing: border-box; }

        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: #111; }
        ::-webkit-scrollbar-thumb { background: #2a2a2a; border-radius: 2px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--cc-orange); }

        body {
            background: var(--cc-dark);
            color: var(--cc-text);
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
            margin: 0;
            overflow: hidden;
            height: 100vh;
        }

        /* ── Sidebar ── */
        #app-sidebar {
            width: 240px;
            background: var(--cc-dark2);
            border-right: 1px solid var(--cc-dark4);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            transition: width .28s cubic-bezier(.4,0,.2,1);
            overflow: hidden;
        }

        #app-sidebar.collapsed { width: 64px; }

        #app-sidebar.collapsed .sb-label,
        #app-sidebar.collapsed .sb-logo-text,
        #app-sidebar.collapsed .sb-user-info,
        #app-sidebar.collapsed .sb-section,
        #app-sidebar.collapsed .sb-badge { display: none !important; }

        #app-sidebar.collapsed .sb-link {
            justify-content: center;
            padding: 11px 0;
            border-radius: 10px;
        }

        #app-sidebar.collapsed .sb-link i { margin: 0; }

        /* ── Nav links ── */
        .sb-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 13px;
            border-radius: 10px;
            color: #555;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: all .18s;
            position: relative;
            white-space: nowrap;
        }

        .sb-link:hover { background: var(--cc-dark4); color: #ccc; }

        .sb-link.active {
            background: rgba(234,88,12,.13);
            color: var(--cc-orange2);
            border-left: 3px solid var(--cc-orange);
        }

        .sb-link i { font-size: 15px; min-width: 18px; text-align: center; flex-shrink: 0; }

        /* ── KPI Card ── */
        .card {
            background: var(--cc-dark3);
            border: 1px solid var(--cc-border);
            border-radius: 14px;
            padding: 1.25rem;
            transition: border-color .2s, transform .2s;
        }

        .card:hover { border-color: #2a2a2a; }

        /* ── Badge statut ── */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 9px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-attente    { background:rgba(234,179,8,.12);  color:#eab308; }
        .badge-prep       { background:rgba(59,130,246,.12); color:#60a5fa; }
        .badge-expediee   { background:rgba(234,88,12,.12);  color:#f97316; }
        .badge-servie,
        .badge-livree     { background:rgba(34,197,94,.12);  color:#22c55e; }
        .badge-annulee    { background:rgba(239,68,68,.12);  color:#f87171; }

        /* ── Tabs ── */
        .tab-pane { display: none; }
        .tab-pane.active { display: block; }

        /* ── Table plan ── */
        .table-cell {
            aspect-ratio: 1;
            border-radius: 12px;
            border: 1.5px solid var(--cc-border);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all .2s;
            background: var(--cc-dark3);
            gap: 3px;
            min-height: 70px;
        }

        .table-cell.libre:hover { border-color: #22c55e; background: rgba(34,197,94,.05); }
        .table-cell.occupee { border-color: var(--cc-orange); background: rgba(234,88,12,.06); }

        /* ── Bon cuisine ── */
        .bon {
            background: var(--cc-dark2);
            border: 1px solid var(--cc-border);
            border-left: 3px solid var(--cc-orange);
            border-radius: 12px;
            padding: 1rem;
            transition: all .2s;
        }
        .bon:hover { background: #111; }
        .bon.blue { border-left-color: #3b82f6; }

        /* ── Notif dot ── */
        .notif-dot {
            position: absolute; top: -2px; right: -2px;
            width: 7px; height: 7px;
            background: var(--cc-orange); border-radius: 50%;
            border: 2px solid var(--cc-dark2);
        }

        @keyframes pulse-o {
            0%,100% { box-shadow: 0 0 0 0 rgba(234,88,12,.5); }
            50%      { box-shadow: 0 0 0 6px rgba(234,88,12,0); }
        }
        .pulse-o { animation: pulse-o 2s infinite; }

        /* ── Progress bar ── */
        .prog { background: var(--cc-dark4); border-radius: 2px; height: 3px; overflow: hidden; }
        .prog-bar { height: 3px; border-radius: 2px; background: var(--cc-orange); transition: width 1s ease; }

        /* ── Btn utilitaires ── */
        .btn-cc {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 16px; border-radius: 10px;
            font-size: 12px; font-weight: 600;
            cursor: pointer; transition: all .18s; border: none;
            text-decoration: none;
        }
        .btn-cc-primary  { background: var(--cc-orange); color: #fff; }
        .btn-cc-primary:hover { background: #c2410c; }
        .btn-cc-ghost {
            background: var(--cc-dark3);
            border: 1px solid var(--cc-border);
            color: #555;
        }
        .btn-cc-ghost:hover { color: #ccc; border-color: #333; }
        .btn-cc-danger { background: rgba(239,68,68,.12); border: 1px solid rgba(239,68,68,.2); color: #f87171; }
        .btn-cc-danger:hover { background: #ef4444; color: #fff; }

        /* ── Chart wrap ── */
        .chart-wrap { position: relative; height: 200px; }

        /* ── Responsive mobile ── */
        @media(max-width:768px) {
            #app-sidebar { position: fixed; z-index: 50; height: 100vh; transform: translateX(-100%); transition: transform .28s; width: 240px !important; }
            #app-sidebar.mobile-open { transform: translateX(0); }
            #mobile-overlay { display: block; }
        }
    </style>

    @stack('styles')
</head>

<body>

{{-- Mobile overlay --}}
<div id="mobile-overlay"
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.6); z-index:40;"
     onclick="closeMobileSidebar()"></div>

<div style="display:flex; height:100vh; overflow:hidden;">

    {{-- ══════════════════════════════════════════ --}}
    {{-- SIDEBAR                                    --}}
    {{-- ══════════════════════════════════════════ --}}
    <aside id="app-sidebar">

        {{-- Logo --}}
        <div style="display:flex; align-items:center; justify-content:space-between;
                    padding:14px 16px; border-bottom:1px solid #1a1a1a; min-height:60px; flex-shrink:0;">
            <div class="sb-logo-text" style="display:flex; align-items:center; gap:10px;">
                <div style="width:34px; height:34px; border-radius:10px; flex-shrink:0;
                            background:rgba(234,88,12,.15); border:1px solid rgba(234,88,12,.3);
                            display:flex; align-items:center; justify-content:center;">
                    <i class="fa-solid fa-fire-flame-curved" style="color:#ea580c; font-size:15px;"></i>
                </div>
                <div>
                    <div style="font-size:13px; font-weight:700; color:#fff; line-height:1.2;">Chez Clarence</div>
                    <div style="font-size:9px; color:#333; letter-spacing:2px;">GESTION</div>
                </div>
            </div>
            <button id="sb-toggle"
                    onclick="toggleSidebar()"
                    style="background:none; border:none; color:#333; cursor:pointer; padding:6px;
                           border-radius:8px; transition:all .18s; flex-shrink:0;"
                    onmouseover="this.style.background='#1a1a1a'; this.style.color='#ccc';"
                    onmouseout="this.style.background='none'; this.style.color='#333';">
                <i class="fa-solid fa-bars" style="font-size:14px;"></i>
            </button>
        </div>

        {{-- Nav --}}
        <nav style="flex:1; padding:12px; overflow-y:auto; display:flex; flex-direction:column; gap:2px;">

            {{-- Dashboard --}}
            <a href="{{ route('dashboard') }}"
               class="sb-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-gauge-high"></i>
                <span class="sb-label">Tableau de bord</span>
            </a>



            @if(in_array(auth()->user()->role, ['Client']))
            @php
                $nbAttenteClient = \App\Models\Commande::where('statut_courant', 'En attente')
                    ->where('idclient', auth()->user()->iduser)
                    ->whereNull('void')
                    ->count();
            @endphp
            <a href="{{ route('mes-commandes.index') }}"
               class="sb-link {{ request()->routeIs('mes-commandes.*') ? 'active' : '' }}">
                <i class="fa-solid fa-receipt"></i>
                <span class="sb-label">Mes Commandes</span>
                @if($nbAttenteClient > 0)
                <span class="sb-badge" style="margin-left:auto; background:rgba(234,88,12,.2);
                      color:#f97316; font-size:10px; padding:1px 7px; border-radius:10px; font-weight:700;">
                    {{ $nbAttenteClient }}
                </span>
                @endif
            </a>
            @endif



            @if(in_array(auth()->user()->role, ['Administrateur','Caissier','Serveur']))
            <a href="{{ route('commandes.index') }}"
               class="sb-link {{ request()->routeIs('commandes.*') ? 'active' : '' }}">
                <i class="fa-solid fa-receipt"></i>
                <span class="sb-label">Commandes</span>
                @php $nbAttente = \App\Models\Commande::where('statut_courant','En attente')->whereNull('void')->count(); @endphp
                @if($nbAttente > 0)
                <span class="sb-badge" style="margin-left:auto; background:rgba(234,88,12,.2);
                      color:#f97316; font-size:10px; padding:1px 7px; border-radius:10px; font-weight:700;">
                    {{ $nbAttente }}
                </span>
                @endif
            </a>
            @endif

            @if(in_array(auth()->user()->role, ['Administrateur','Caissier']))
            <a href="{{ route('caisse.index') }}"
               class="sb-link {{ request()->routeIs('caisse.*') ? 'active' : '' }}">
                <i class="fa-solid fa-cash-register"></i>
                <span class="sb-label">Caisse</span>
            </a>
            @endif

            @if(in_array(auth()->user()->role, ['Administrateur','Cuisinier']))
            <a href="{{ route('cuisine.index') }}"
               class="sb-link {{ request()->routeIs('cuisine.*') ? 'active' : '' }}">
                <i class="fa-solid fa-fire-burner"></i>
                <span class="sb-label">Cuisine</span>
            </a>
            @endif

            @if(in_array(auth()->user()->role, ['Administrateur','Livreur']))
            <a href="{{ route('livraisons.index') }}"
               class="sb-link {{ request()->routeIs('livraisons.*') ? 'active' : '' }}">
                <i class="fa-solid fa-motorcycle"></i>
                <span class="sb-label">Livraisons</span>
                @php $nbLiv = \App\Models\Commande::where('typecommande','Livraison')->whereIn('statut_courant',['En attente','En préparation','Expédiée'])->whereNull('void')->count(); @endphp
                @if($nbLiv > 0)
                <span class="sb-badge" style="margin-left:auto; background:rgba(234,88,12,.2);
                      color:#f97316; font-size:10px; padding:1px 7px; border-radius:10px; font-weight:700;">
                    {{ $nbLiv }}
                </span>
                @endif
            </a>
            @endif







            @if(in_array(auth()->user()->role, ['Administrateur','Caissier']))
            <div class="sb-section" style="padding:12px 8px 4px; font-size:9px; color:#2a2a2a;
                         letter-spacing:2px; text-transform:uppercase; font-weight:600;">
                Administration
            </div>
            @endif

            @if(in_array(auth()->user()->role, ['Administrateur']))
            <a href="{{ route('admin.statistiques') }}"
               class="sb-link {{ request()->routeIs('admin.statistiques') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-line"></i>
                <span class="sb-label">Statistiques</span>
            </a>

            <a href="{{ route('admin.menus.index') }}"
               class="sb-link {{ request()->routeIs('admin.menus.*') ? 'active' : '' }}">
                <i class="fa-solid fa-book-open"></i>
                <span class="sb-label">Menus & Plats</span>
            </a>

            <a href="{{ route('admin.tables.index') }}"
               class="sb-link {{ request()->routeIs('admin.tables.*') ? 'active' : '' }}">
                <i class="fa-solid fa-chair"></i>
                <span class="sb-label">Tables & Salles</span>
            </a>

            <a href="{{ route('admin.utilisateurs.index') }}"
               class="sb-link {{ request()->routeIs('admin.utilisateurs.*') ? 'active' : '' }}">
                <i class="fa-solid fa-users"></i>
                <span class="sb-label">Utilisateurs</span>
            </a>

            <a href="{{ route('admin.parametres.index') }}"
               class="sb-link {{ request()->routeIs('admin.parametres.index') ? 'active' : '' }}">
                <i class="fa-solid fa-sliders"></i>
                <span class="sb-label">Paramètres</span>
            </a>
            @endif


            @if(in_array(auth()->user()->role, ['Administrateur','Caissier']))
            <a href="{{ route('admin.sauvegarde.index') }}"
               class="sb-link {{ request()->routeIs('admin.sauvegarde.index') ? 'active' : '' }}">
                <i class="fa-solid fa-database"></i>
                <span class="sb-label">Sauvegarde & <br />Restauration</span>
            </a>
            @endif
        </nav>



        {{-- Profil bas --}}
        <div style="padding:12px; border-top:1px solid #1a1a1a; flex-shrink:0;">
            <div style="display:flex; align-items:center; gap:10px; padding:8px; border-radius:10px;
                        cursor:pointer; transition:background .18s;"
                 onmouseover="this.style.background='#1a1a1a'"
                 onmouseout="this.style.background='transparent'">
                <div style="width:32px; height:32px; border-radius:50%; flex-shrink:0;
                            background:rgba(234,88,12,.2); border:1px solid rgba(234,88,12,.3);
                            display:flex; align-items:center; justify-content:center;
                            font-size:12px; font-weight:700; color:#f97316;">
                    {{ strtoupper(substr(auth()->user()->prenom ?? 'U', 0, 1)) }}{{ strtoupper(substr(auth()->user()->nom ?? '', 0, 1)) }}
                </div>
                <div class="sb-user-info" style="flex:1; min-width:0;">
                    <div style="font-size:12px; font-weight:600; color:#e5e5e5; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                        {{ auth()->user()->prenom }} {{ auth()->user()->nom }}
                    </div>
                    <div style="font-size:10px; color:#444;">{{ auth()->user()->role }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" id="logout-form">
                @csrf
                <button type="button"
                        onclick="confirmLogout()"
                        style="width:100%; display:flex; align-items:center; gap:8px; padding:7px 10px;
                               border-radius:8px; border:none; background:none; color:#333; cursor:pointer;
                               font-size:12px; transition:all .18s; margin-top:4px;"
                        onmouseover="this.style.color='#ef4444'; this.style.background='rgba(239,68,68,.08)'"
                        onmouseout="this.style.color='#333'; this.style.background='none'">
                    <i class="fa-solid fa-right-from-bracket" style="font-size:12px;"></i>
                    <span class="sb-user-info">Déconnexion</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- ══════════════════════════════════════════ --}}
    {{-- CONTENU PRINCIPAL                          --}}
    {{-- ══════════════════════════════════════════ --}}
    <div style="flex:1; display:flex; flex-direction:column; overflow:hidden; min-width:0;">

        {{-- TOPBAR --}}
        <header style="flex-shrink:0; display:flex; align-items:center; justify-content:space-between;
                       padding:0 24px; height:60px; background:#0a0a0a; border-bottom:1px solid #1a1a1a;">
            <div style="display:flex; align-items:center; gap:12px;">
                {{-- Burger mobile --}}
                <button class="mobile-burger" onclick="openMobileSidebar()"
                        style="display:none; background:none; border:none; color:#555; cursor:pointer; padding:4px;">
                    <i class="fa-solid fa-bars" style="font-size:16px;"></i>
                </button>
                <div>
                    <h1 style="font-size:15px; font-weight:600; color:#e5e5e5; margin:0;" id="page-title">
                        @yield('page-title', 'Tableau de bord')
                    </h1>
                    <div style="font-size:11px; color:#333; margin-top:1px;">
                        {{ now()->isoFormat('dddd DD MMMM YYYY') }}
                    </div>
                </div>
            </div>

            <div style="display:flex; align-items:center; gap:12px;">

                {{-- Horloge --}}
                <div style="font-size:12px; color:#444; font-variant-numeric:tabular-nums;" id="clock"></div>

                {{-- Badge rôle --}}
                <span style="font-size:10px; font-weight:600; padding:5px 12px; border-radius:20px;
                             background:rgba(234,88,12,.1); color:#f97316;
                             border:1px solid rgba(234,88,12,.2);">
                    <i class="fa-solid fa-circle-user" style="margin-right:4px;"></i>
                    {{ auth()->user()->role }}
                </span>

                {{-- Notif --}}
                <div style="position:relative;" id="notif-wrap">
                    <button onclick="toggleNotifPanel()" id="notif-btn"
                            style="width:36px; height:36px; border-radius:10px; border:1px solid #1f1f1f;
                                   background:#141414; color:#555; cursor:pointer; transition:all .18s;
                                   display:flex; align-items:center; justify-content:center; position:relative;"
                            onmouseover="this.style.color='#f97316'; this.style.borderColor='rgba(234,88,12,.3)'"
                            onmouseout="this.style.color='#555'; this.style.borderColor='#1f1f1f'">
                        <i class="fa-solid fa-bell" style="font-size:13px;"></i>
                    </button>

                    {{-- Badge : valeur initiale au chargement de la page,
                         puis tenue à jour par le polling JS ci-dessous. --}}
                    @php
                        $nbNotifAttente = \App\Models\Commande::where('statut_courant', 'En attente')
                            ->whereNull('void')
                            ->when(auth()->user()->role === 'Client', fn ($q) => $q->where('idclient', auth()->user()->iduser))
                            ->when(auth()->user()->role === 'Livreur', fn ($q) => $q->where('typecommande', 'Livraison'))
                            ->count();
                    @endphp
                    <span id="notif-badge"
                          class="absolute -top-1.5 -right-1.5 z-10 flex h-6 min-w-[26px] items-center justify-center
                                 rounded-full border-2 border-[#0d0d0d] bg-orange-600 px-1
                                 text-[14px] font-bold leading-none text-white pulse-o"
                          style="display:{{ $nbNotifAttente > 0 ? 'flex' : 'none' }};">
                        {{ $nbNotifAttente > 9 ? '9+' : $nbNotifAttente }}
                    </span>

                    {{-- Panneau déroulant --}}
                    <div id="notif-panel"
                         style="display:none; position:absolute; top:46px; right:0; width:340px; max-width:90vw;
                                background:#141414; border:1px solid #1f1f1f; border-radius:14px;
                                box-shadow:0 20px 50px rgba(0,0,0,.5); z-index:100; overflow:hidden;">

                        <div style="padding:14px 16px; border-bottom:1px solid #1f1f1f;
                                    display:flex; align-items:center; justify-content:space-between;">
                            <span style="font-size:13px; font-weight:700; color:#e5e5e5;">
                                <i class="fa-solid fa-clock" style="color:#eab308; margin-right:7px;"></i>
                                Commandes en attente
                            </span>
                            <span id="notif-timestamp" style="font-size:10px; color:#444;"></span>
                        </div>

                        <div id="notif-list" style="max-height:360px; overflow-y:auto;">
                            <div style="text-align:center; padding:24px; color:#333; font-size:12px;">
                                Chargement...
                            </div>
                        </div>

                        @php $roleNotif = auth()->user()->role; @endphp

                        @if(in_array($roleNotif, ['Administrateur','Caissier','Serveur']))
                        <div style="padding:10px 16px; border-top:1px solid #1f1f1f; text-align:center;">
                            <a href="{{ route('commandes.index') }}?statut=En+attente"
                               style="font-size:11.5px; color:#f97316; text-decoration:none; font-weight:600;">
                                Voir toutes les commandes en attente
                            </a>
                        </div>
                        @elseif($roleNotif === 'Cuisinier')
                        <div style="padding:10px 16px; border-top:1px solid #1f1f1f; text-align:center;">
                            <a href="{{ route('cuisine.index') }}"
                               style="font-size:11.5px; color:#f97316; text-decoration:none; font-weight:600;">
                                Aller à l'écran Cuisine
                            </a>
                        </div>
                        @elseif($roleNotif === 'Livreur')
                        <div style="padding:10px 16px; border-top:1px solid #1f1f1f; text-align:center;">
                            <a href="{{ route('livraisons.index') }}"
                               style="font-size:11.5px; color:#f97316; text-decoration:none; font-weight:600;">
                                Aller au suivi des livraisons
                            </a>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Déconnexion --}}
                <button type="button"
                        onclick="confirmLogout()"
                        style="display:flex; align-items:center; gap:6px; padding:7px 14px; border-radius:10px;
                               border:1px solid #1f1f1f; background:#141414; color:#555; cursor:pointer;
                               font-size:11px; font-weight:500; transition:all .18s;"
                        onmouseover="this.style.color='#ef4444'; this.style.borderColor='rgba(239,68,68,.3)'"
                        onmouseout="this.style.color='#555'; this.style.borderColor='#1f1f1f'">
                    <i class="fa-solid fa-right-from-bracket" style="font-size:12px;"></i>
                    <span>Déconnexion</span>
                </button>
            </div>
        </header>

        {{-- CONTENU --}}
        <main style="flex:1; overflow-y:auto; padding:20px; background:#080808;">

            @if(session('success'))
            <div class="animate__animated animate__fadeInDown"
                 style="margin-bottom:16px; display:flex; align-items:center; gap:10px; padding:12px 16px;
                        border-radius:10px; font-size:13px; background:rgba(34,197,94,.07);
                        border:1px solid rgba(34,197,94,.2); color:#4ade80;">
                <i class="fa-solid fa-circle-check"></i>
                {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div class="animate__animated animate__fadeInDown"
                 style="margin-bottom:16px; display:flex; align-items:center; gap:10px; padding:12px 16px;
                        border-radius:10px; font-size:13px; background:rgba(239,68,68,.07);
                        border:1px solid rgba(239,68,68,.2); color:#f87171;">
                <i class="fa-solid fa-circle-xmark"></i>
                {{ session('error') }}
            </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>





{{-- Scripts globaux --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// ── Horloge ──────────────────────────────────────────────────
(function clock() {
    const el = document.getElementById('clock');
    if (el) el.textContent = new Date().toLocaleTimeString('fr-FR');
    setTimeout(clock, 1000);
})();

// ── Sidebar toggle ───────────────────────────────────────────
function toggleSidebar() {
    document.getElementById('app-sidebar').classList.toggle('collapsed');
}

function openMobileSidebar() {
    const sb = document.getElementById('app-sidebar');
    sb.classList.add('mobile-open');
    document.getElementById('mobile-overlay').style.display = 'block';
}

function closeMobileSidebar() {
    const sb = document.getElementById('app-sidebar');
    sb.classList.remove('mobile-open');
    document.getElementById('mobile-overlay').style.display = 'none';
}

// Burger visible sur mobile
if (window.innerWidth < 768) {
    const burger = document.querySelector('.mobile-burger');
    if (burger) burger.style.display = 'block';
}

// ── Déconnexion confirmée ────────────────────────────────────
function confirmLogout() {
    Swal.fire({
        title: 'Se déconnecter ?',
        text: 'Vous allez quitter votre session.',
        icon: 'question',
        iconColor: '#ea580c',
        background: '#141414',
        color: '#e5e5e5',
        confirmButtonColor: '#ea580c',
        confirmButtonText: '<i class="fa-solid fa-right-from-bracket" style="margin-right:6px"></i>Déconnexion',
        showCancelButton: true,
        cancelButtonText: 'Annuler',
        cancelButtonColor: '#1f1f1f',
    }).then(r => { if (r.isConfirmed) document.getElementById('logout-form').submit(); });
}

// ── Notifications ────────────────────────────────────────────
// ══════════════════════════════════════════════════════════════
// NOTIFICATIONS — panneau déroulant asynchrone (commandes en attente)
// ══════════════════════════════════════════════════════════════

const userRole          = '{{ auth()->user()->role }}';
const NOTIF_ROUTE        = '{{ route("notifications.commandes-en-attente") }}';
const ROUTE_SHOW_TPL     = '{{ route("commandes.show", "__ID__") }}';
const ROUTE_PRENDRE_TPL  = '{{ route("cuisine.prendre-en-charge", "__ID__") }}';
const NOTIF_REFRESH_MS   = 30000; // 30s

let notifPanelOuvert = false;
let notifChargeUneFois = false;

function toggleNotifPanel() {
    const panel = document.getElementById('notif-panel');
    notifPanelOuvert = !notifPanelOuvert;
    panel.style.display = notifPanelOuvert ? 'block' : 'none';

    if (notifPanelOuvert) {
        chargerNotifications();
    }
}

// Ferme le panneau au clic en dehors
document.addEventListener('click', function (e) {
    const wrap = document.getElementById('notif-wrap');
    if (notifPanelOuvert && wrap && !wrap.contains(e.target)) {
        notifPanelOuvert = false;
        document.getElementById('notif-panel').style.display = 'none';
    }
});

function iconeType(type) {
    if (type === 'Livraison')  return 'fa-motorcycle';
    if (type === 'A emporter') return 'fa-bag-shopping';
    return 'fa-chair';
}

function ligneNotif(cmd) {
    const urgent = cmd.minutes !== null && cmd.minutes >= 10;

    const localisation = cmd.table
        ? cmd.table
        : (cmd.client ?? cmd.type_label);

    // Action selon le rôle : Cuisinier/Administrateur peuvent prendre
    // en charge directement depuis la notification ; les rôles ayant
    // accès au module Commandes voient un lien "Voir".
    let action = '';
    if (['Cuisinier', 'Administrateur'].includes(userRole)) {
        action = `<button onclick="prendreEnChargeDepuisNotif(${cmd.idcommande}, this)"
                          style="font-size:10.5px;font-weight:700;color:#22c55e;background:rgba(34,197,94,.1);
                                 border:1px solid rgba(34,197,94,.25);border-radius:7px;padding:4px 9px;
                                 cursor:pointer;font-family:inherit;white-space:nowrap;">
                      <i class="fa-solid fa-play" style="font-size:9px;"></i> Prendre en charge
                  </button>`;
    } else if (['Administrateur', 'Caissier', 'Serveur'].includes(userRole)) {
        const url = ROUTE_SHOW_TPL.replace('__ID__', cmd.idcommande);
        action = `<a href="${url}" style="font-size:10.5px;font-weight:700;color:#60a5fa;text-decoration:none;
                                            white-space:nowrap;">
                      <i class="fa-solid fa-eye"></i> Voir
                  </a>`;
    }

    return `
        <div style="display:flex; align-items:flex-start; gap:10px; padding:12px 16px;
                    border-bottom:1px solid #1a1a1a;" data-idcommande="${cmd.idcommande}">
            <div style="width:34px; height:34px; border-radius:9px; flex-shrink:0; background:#1a1a1a;
                        display:flex; align-items:center; justify-content:center; margin-top:1px;">
                <i class="fa-solid ${iconeType(cmd.typecommande)}" style="font-size:13px; color:#eab308;"></i>
            </div>
            <div style="flex:1; min-width:0;">
                <div style="font-size:12.5px; font-weight:700; color:#e5e5e5;">
                    ${cmd.reference}
                    <span style="font-size:10px; font-weight:400; color:#555;">· ${localisation ?? ''}</span>
                </div>
                <div style="font-size:10.5px; color:${urgent ? '#f87171' : '#555'}; margin-top:2px;">
                    <i class="fa-regular fa-calendar"></i> ${cmd.date ?? ''}
                    <span style="margin:0 3px;">·</span>
                    <i class="fa-regular fa-clock"></i> ${cmd.heure ?? ''}${cmd.minutes !== null ? ' (depuis ' + cmd.minutes + ' min)' : ''}
                </div>
                <div style="font-size:12px; font-weight:700; color:#f97316; margin-top:5px;">
                    ${Math.round(cmd.montant).toLocaleString('fr-FR')} FCFA
                </div>
            </div>
            ${action}
        </div>
    `;
}

function chargerNotifications() {
    fetch(NOTIF_ROUTE, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) return;
        notifChargeUneFois = true;

        majBadge(data.total);
        document.getElementById('notif-timestamp').textContent = 'mise à jour à ' + data.timestamp;

        const liste = document.getElementById('notif-list');
        if (!data.commandes.length) {
            liste.innerHTML = `
                <div style="text-align:center; padding:28px 16px; color:#2a2a2a;">
                    <i class="fa-solid fa-circle-check" style="font-size:24px; display:block; margin-bottom:8px; color:#22c55e;"></i>
                    <p style="font-size:12.5px;">Aucune commande en attente</p>
                </div>`;
            return;
        }
        liste.innerHTML = data.commandes.map(ligneNotif).join('');
    })
    .catch(() => {
        document.getElementById('notif-list').innerHTML =
            '<div style="text-align:center; padding:20px; color:#f87171; font-size:12px;">Erreur de chargement</div>';
    });
}

function majBadge(total) {
    const badge = document.getElementById('notif-badge');
    badge.style.display = total > 0 ? 'flex' : 'none';
    badge.textContent = total > 9 ? '9+' : total;
}

// ── Prendre en charge directement depuis la notification ────────
function prendreEnChargeDepuisNotif(idcommande, btn) {
    btn.disabled = true;
    const original = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

    fetch(ROUTE_PRENDRE_TPL.replace('__ID__', idcommande), {
        method: 'PATCH',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            Swal.fire({
                toast: true, position: 'bottom-end', icon: 'error',
                title: data.message || 'Action impossible', timer: 2500, showConfirmButton: false,
                background: '#141414', color: '#e5e5e5', iconColor: '#ef4444',
            });
            btn.disabled = false;
            btn.innerHTML = original;
            return;
        }

        Swal.fire({
            toast: true, position: 'bottom-end', icon: 'success',
            title: 'Commande prise en charge', timer: 1800, showConfirmButton: false,
            background: '#141414', color: '#e5e5e5', iconColor: '#22c55e',
        });

        chargerNotifications();
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = original;
    });
}

// ── Rafraîchissement périodique du badge (même panneau fermé) ──
setInterval(() => {
    // Si le panneau est ouvert, on recharge tout (liste + badge).
    // Sinon on se contente d'une requête légère pour tenir le badge à jour.
    if (notifPanelOuvert) {
        chargerNotifications();
    } else {
        fetch(NOTIF_ROUTE, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        })
        .then(r => r.json())
        .then(data => { if (data.success) majBadge(data.total); })
        .catch(() => {});
    }
}, NOTIF_REFRESH_MS);

// ── Confirmation suppression globale ────────────────────────
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.btn-delete');
    if (!btn) return;
    e.preventDefault();
    const form = btn.closest('form');
    Swal.fire({
        title: 'Confirmer la suppression',
        text: 'Cette action est irréversible !',
        icon: 'warning',
        iconColor: '#ea580c',
        background: '#141414',
        color: '#e5e5e5',
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Oui, supprimer',
        showCancelButton: true,
        cancelButtonText: 'Annuler',
        cancelButtonColor: '#1f1f1f',
    }).then(r => { if (r.isConfirmed && form) form.submit(); });
});
</script>

@stack('scripts')
</body>
</html>