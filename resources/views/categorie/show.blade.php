@extends('layouts.app')

@section('title', 'Catégorie : ' . $categorie->intitule)
@section('page-title', 'Détail catégorie')

@push('styles')
<style>
    :root {
        --cc-orange:  #ea580c;
        --cc-orange2: #f97316;
        --cc-dark2:   #0d0d0d;
        --cc-dark3:   #141414;
        --cc-border:  #1f1f1f;
    }

    .card {
        background: var(--cc-dark3);
        border: 1px solid var(--cc-border);
        border-radius: 14px;
        overflow: hidden;
    }

    .card-header {
        padding: 14px 20px;
        border-bottom: 1px solid #1a1a1a;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .card-body { padding: 20px; }

    .badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }

    .badge-active   { background: rgba(34,197,94,.12);  color: #22c55e; }
    .badge-inactive { background: rgba(239,68,68,.12);  color: #f87171; }
    .badge-orange   { background: rgba(234,88,12,.12);  color: #f97316; }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 9px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all .18s;
        border: none;
        font-family: inherit;
        text-decoration: none;
    }

    .btn-primary { background: var(--cc-orange); color: #fff; }
    .btn-primary:hover { background: #c2410c; }

    .btn-ghost {
        background: var(--cc-dark3);
        border: 1px solid var(--cc-border);
        color: #555;
    }
    .btn-ghost:hover { color: #ccc; border-color: #333; }

    .btn-danger {
        background: rgba(239,68,68,.08);
        border: 1px solid rgba(239,68,68,.2);
        color: #f87171;
    }
    .btn-danger:hover { background: #ef4444; color: #fff; }

    .btn-success {
        background: rgba(34,197,94,.1);
        border: 1px solid rgba(34,197,94,.2);
        color: #22c55e;
    }
    .btn-success:hover { background: #22c55e; color: #fff; }

    .btn-sm { padding: 5px 10px; font-size: 11px; border-radius: 7px; }

    .info-cell {
        background: var(--cc-dark2);
        border: 1px solid #1a1a1a;
        border-radius: 9px;
        padding: 12px 14px;
    }

    .info-label {
        font-size: 10px;
        color: #444;
        text-transform: uppercase;
        letter-spacing: .5px;
        margin-bottom: 4px;
    }

    .info-value {
        font-size: 16px;
        font-weight: 700;
        color: #e5e5e5;
    }

    .menu-card {
        background: var(--cc-dark2);
        border: 1.5px solid #1a1a1a;
        border-radius: 11px;
        overflow: hidden;
        transition: border-color .2s, transform .18s;
        position: relative;
    }

    .menu-card:hover {
        border-color: #2a2a2a;
        transform: translateY(-2px);
    }

    .menu-card-img {
        height: 80px;
        background: #111;
        overflow: hidden;
        position: relative;
    }

    .menu-card-img img {
        width: 100%; height: 100%;
        object-fit: cover;
        transition: transform .3s;
    }

    .menu-card:hover .menu-card-img img { transform: scale(1.07); }

    .prog {
        background: #1a1a1a;
        border-radius: 3px;
        height: 4px;
        overflow: hidden;
        margin-top: 5px;
    }

    .prog-fill {
        height: 100%;
        border-radius: 3px;
        background: var(--cc-orange);
        transition: width 1s ease;
    }

    .toggle {
        width: 38px; height: 22px;
        border-radius: 11px;
        position: relative;
        transition: background .2s;
        flex-shrink: 0;
        cursor: pointer;
    }

    .toggle.on  { background: #22c55e; }
    .toggle.off { background: #2a2a2a; }

    .toggle::after {
        content: '';
        position: absolute;
        top: 3px; left: 3px;
        width: 16px; height: 16px;
        border-radius: 50%;
        background: #fff;
        transition: left .2s;
    }

    .toggle.on::after { left: 19px; }

    .filtre-chip {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 500;
        cursor: pointer;
        transition: all .18s;
        border: 1px solid #1f1f1f;
        background: #141414;
        color: #555;
        font-family: inherit;
    }

    .filtre-chip.active,
    .filtre-chip:hover {
        background: var(--cc-orange);
        color: #fff;
        border-color: var(--cc-orange);
    }

    .top-row {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 0;
        border-bottom: 1px solid #1a1a1a;
    }

    .top-row:last-child { border-bottom: none; }
</style>
@endpush

@section('content')

{{-- Fil d'Ariane --}}
<div style="display:flex;align-items:center;gap:8px;
            font-size:12px;color:#444;margin-bottom:18px;">
    <a href="{{ route('admin.categories.index') }}"
       style="color:#555;text-decoration:none;
              display:flex;align-items:center;gap:5px;"
       onmouseover="this.style.color='#f97316'"
       onmouseout="this.style.color='#555'">
        <i class="fa-solid fa-layer-group"></i>
        Catégories
    </a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;color:#333;"></i>
    <span style="color:#666;">{{ $categorie->intitule }}</span>
</div>

{{-- ══════════════════════════════════════════════════════════
     EN-TÊTE
══════════════════════════════════════════════════════════ --}}
<div style="display:flex;align-items:flex-start;justify-content:space-between;
            flex-wrap:wrap;gap:12px;margin-bottom:20px;">

    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">

        {{-- Photo miniature --}}
        @if($categorie->photo)
        <div style="width:54px;height:54px;border-radius:12px;overflow:hidden;
                    border:1px solid #1f1f1f;flex-shrink:0;">
            <img src="{{ asset('storage/' . $categorie->photo) }}"
                 alt="{{ $categorie->intitule }}"
                 style="width:100%;height:100%;object-fit:cover;">
        </div>
        @else
        <div style="width:54px;height:54px;border-radius:12px;flex-shrink:0;
                    background:#1a1a1a;border:1px solid #1f1f1f;
                    display:flex;align-items:center;justify-content:center;">
            <i class="fa-solid fa-layer-group" style="color:#2a2a2a;font-size:20px;"></i>
        </div>
        @endif

        <div>
            <h1 style="font-size:20px;font-weight:700;color:#fff;margin:0 0 6px;">
                {{ $categorie->intitule }}
            </h1>
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                <span class="badge {{ $categorie->statut === 'Activé' ? 'badge-active' : 'badge-inactive' }}">
                    <i class="fa-solid {{ $categorie->statut === 'Activé' ? 'fa-circle-check' : 'fa-circle-xmark' }}"
                       style="font-size:9px;"></i>
                    {{ $categorie->statut }}
                </span>
                <span class="badge badge-orange">
                    <i class="fa-solid fa-book-open" style="font-size:9px;"></i>
                    {{ $menus->count() }} plat(s)
                </span>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">

        <button onclick="toggleStatut()"
                class="btn {{ $categorie->statut === 'Activé' ? 'btn-ghost' : 'btn-success' }} btn-sm">
            <i class="fa-solid {{ $categorie->statut === 'Activé' ? 'fa-toggle-off' : 'fa-toggle-on' }}"></i>
            {{ $categorie->statut === 'Activé' ? 'Désactiver' : 'Activer' }}
        </button>

        <a href="{{ route('admin.categories.edit', $categorie->idcategorie) }}"
           class="btn btn-ghost btn-sm">
            <i class="fa-solid fa-pen-to-square"></i>
            Modifier
        </a>

        <a href="{{ route('admin.menus.create') }}?categorie={{ $categorie->idcategorie }}"
           class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus"></i>
            Ajouter un plat
        </a>

        @if($menus->where('statut','Activé')->count() === 0 && ($statsVentes->total_vendu ?? 0) == 0)
        <button onclick="confirmerSuppression()" class="btn btn-danger btn-sm">
            <i class="fa-solid fa-trash"></i>
            Supprimer
        </button>
        @endif

        <a href="{{ route('admin.categories.index') }}" class="btn btn-ghost btn-sm">
            <i class="fa-solid fa-arrow-left"></i>
            Retour
        </a>
    </div>
</div>

{{-- Formulaires cachés --}}
<form method="POST"
      action="{{ route('admin.categories.toggle-statut', $categorie->idcategorie) }}"
      id="toggleForm" style="display:none;">
    @csrf @method('PATCH')
</form>

<form method="POST"
      action="{{ route('admin.categories.destroy', $categorie->idcategorie) }}"
      id="deleteForm" style="display:none;">
    @csrf @method('DELETE')
</form>

{{-- ══════════════════════════════════════════════════════════
     CORPS : 2 colonnes
══════════════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:1fr 300px;gap:16px;align-items:start;">

    {{-- ════════════════════════════
         COLONNE GAUCHE
    ════════════════════════════ --}}
    <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- ── Statistiques de ventes ── --}}
        <div class="card">
            <div class="card-header">
                <div style="display:flex;align-items:center;gap:8px;
                            font-size:13px;font-weight:700;color:#e5e5e5;">
                    <i class="fa-solid fa-chart-line" style="color:var(--cc-orange);"></i>
                    Statistiques de ventes
                </div>
            </div>
            <div class="card-body">
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">

                    {{-- Total vendu --}}
                    <div class="info-cell">
                        <div class="info-label">
                            <i class="fa-solid fa-basket-shopping"
                               style="margin-right:4px;"></i>
                            Total vendu
                        </div>
                        <div class="info-value" style="color:#f97316;">
                            {{ number_format($statsVentes->total_vendu ?? 0, 0, ',', ' ') }}
                        </div>
                        <div style="font-size:10px;color:#333;margin-top:2px;">unité(s)</div>
                    </div>

                    {{-- CA total --}}
                    <div class="info-cell">
                        <div class="info-label">
                            <i class="fa-solid fa-money-bill-wave"
                               style="margin-right:4px;"></i>
                            CA généré
                        </div>
                        <div class="info-value" style="color:#22c55e;">
                            {{ number_format($statsVentes->ca_total ?? 0, 0, ',', ' ') }}
                        </div>
                        <div style="font-size:10px;color:#333;margin-top:2px;">FCFA</div>
                    </div>

                    {{-- Nb commandes --}}
                    <div class="info-cell">
                        <div class="info-label">
                            <i class="fa-solid fa-receipt"
                               style="margin-right:4px;"></i>
                            Commandes
                        </div>
                        <div class="info-value" style="color:#60a5fa;">
                            {{ number_format($statsVentes->nb_commandes ?? 0, 0, ',', ' ') }}
                        </div>
                        <div style="font-size:10px;color:#333;margin-top:2px;">commande(s)</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Liste des plats ── --}}
        <div class="card">
            <div class="card-header">
                <div style="display:flex;align-items:center;gap:8px;
                            font-size:13px;font-weight:700;color:#e5e5e5;">
                    <i class="fa-solid fa-book-open" style="color:var(--cc-orange);"></i>
                    Plats
                    <span style="font-size:11px;font-weight:400;color:#444;">
                        ({{ $menus->count() }} total ·
                        {{ $menus->where('statut','Activé')->count() }} actif(s))
                    </span>
                </div>
                <a href="{{ route('admin.menus.create') }}?categorie={{ $categorie->idcategorie }}"
                   class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-plus"></i>
                    Ajouter
                </a>
            </div>

            <div class="card-body">

                @if($menus->isEmpty())
                <div style="text-align:center;padding:40px;color:#2a2a2a;">
                    <i class="fa-solid fa-utensils"
                       style="font-size:36px;display:block;margin-bottom:12px;"></i>
                    <p style="font-size:13px;color:#333;margin-bottom:16px;">
                        Aucun plat dans cette catégorie
                    </p>
                    <a href="{{ route('admin.menus.create') }}?categorie={{ $categorie->idcategorie }}"
                       class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-plus"></i>
                        Créer le premier plat
                    </a>
                </div>

                @else

                {{-- Filtres rapides --}}
                <div style="display:flex;gap:6px;margin-bottom:16px;flex-wrap:wrap;">
                    @foreach(['Tous','Activé','Désactivé'] as $f)
                    <button type="button"
                            onclick="filtrerPlats('{{ $f }}')"
                            data-filtre="{{ $f }}"
                            class="filtre-chip {{ $f === 'Tous' ? 'active' : '' }}">
                        {{ $f }}
                    </button>
                    @endforeach
                </div>

                {{-- Grille plats --}}
                <div style="display:grid;
                            grid-template-columns:repeat(auto-fill,minmax(155px,1fr));
                            gap:10px;"
                     id="menuGrid">

                    @foreach($menus as $menu)
                    <div class="menu-card plat-item"
                         data-statut="{{ $menu->statut }}">

                        {{-- Photo --}}
                        <div class="menu-card-img">
                            @if($menu->photo)
                            <img src="{{ asset('storage/' . $menu->photo) }}"
                                 alt="{{ $menu->intitule }}"
                                 loading="lazy">
                            @else
                            <div style="width:100%;height:100%;
                                        display:flex;align-items:center;justify-content:center;
                                        background:#1a1a1a;">
                                <i class="fa-solid fa-utensils"
                                   style="color:#252525;font-size:18px;"></i>
                            </div>
                            @endif

                            {{-- Badge statut superposé --}}
                            <div style="position:absolute;top:5px;left:5px;">
                                <span style="font-size:9px;font-weight:600;
                                             padding:2px 6px;border-radius:8px;
                                             background:{{ $menu->statut==='Activé'
                                                 ? 'rgba(34,197,94,.15)'
                                                 : 'rgba(239,68,68,.15)' }};
                                             color:{{ $menu->statut==='Activé'
                                                 ? '#22c55e' : '#f87171' }};">
                                    {{ $menu->statut }}
                                </span>
                            </div>
                        </div>

                        {{-- Infos --}}
                        <div style="padding:8px 10px;">
                            <div style="font-size:12px;font-weight:600;color:#e5e5e5;
                                        white-space:nowrap;overflow:hidden;
                                        text-overflow:ellipsis;margin-bottom:3px;">
                                {{ $menu->intitule }}
                            </div>
                            <div style="font-size:13px;font-weight:700;color:#f97316;">
                                {{ number_format($menu->pu, 0, ',', ' ') }}
                                <span style="font-size:10px;color:#555;">FCFA</span>
                            </div>
                            <div style="font-size:10px;color:#333;margin-top:2px;">
                                {{ $menu->lignes_count ?? 0 }} fois commandé
                            </div>

                            {{-- Boutons --}}
                            <div style="display:flex;gap:5px;margin-top:8px;">
                                <a href="{{ route('admin.menus.edit', $menu->idmenu) }}"
                                   class="btn btn-ghost btn-sm"
                                   style="flex:1;justify-content:center;
                                          font-size:10px;padding:4px 6px;"
                                   title="Modifier">
                                    <i class="fa-solid fa-pen-to-square" style="font-size:10px;"></i>
                                </a>
                                <button onclick="toggleMenu(
                                            {{ $menu->idmenu }},
                                            '{{ addslashes($menu->intitule) }}',
                                            '{{ $menu->statut }}'
                                        )"
                                        class="btn btn-ghost btn-sm"
                                        style="flex:1;justify-content:center;
                                               font-size:10px;padding:4px 6px;"
                                        title="{{ $menu->statut === 'Activé' ? 'Désactiver' : 'Activer' }}">
                                    <i class="fa-solid {{ $menu->statut === 'Activé' ? 'fa-eye-slash' : 'fa-eye' }}"
                                       style="font-size:10px;"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Formulaire toggle plat --}}
                        <form method="POST"
                              action="{{ route('admin.menus.toggle-statut', $menu->idmenu) }}"
                              id="toggleMenuForm-{{ $menu->idmenu }}"
                              style="display:none;">
                            @csrf @method('PATCH')
                        </form>
                    </div>
                    @endforeach
                </div>

                @endif
            </div>
        </div>

    </div>

    {{-- ════════════════════════════
         COLONNE DROITE
    ════════════════════════════ --}}
    <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- ── Informations ── --}}
        <div class="card">
            <div class="card-header">
                <div style="display:flex;align-items:center;gap:8px;
                            font-size:13px;font-weight:700;color:#e5e5e5;">
                    <i class="fa-solid fa-circle-info" style="color:var(--cc-orange);"></i>
                    Informations
                </div>
                <a href="{{ route('admin.categories.edit', $categorie->idcategorie) }}"
                   class="btn btn-ghost btn-sm">
                    <i class="fa-solid fa-pen-to-square" style="font-size:10px;"></i>
                    Modifier
                </a>
            </div>
            <div class="card-body">

                {{-- Grande photo --}}
                @if($categorie->photo)
                <div style="width:100%;height:140px;border-radius:10px;
                            overflow:hidden;margin-bottom:16px;
                            border:1px solid #1a1a1a;">
                    <img src="{{ asset('storage/' . $categorie->photo) }}"
                         alt="{{ $categorie->intitule }}"
                         style="width:100%;height:100%;object-fit:cover;">
                </div>
                @endif

                {{-- Nom --}}
                <div style="margin-bottom:12px;">
                    <div class="info-label">Nom</div>
                    <div style="font-size:14px;font-weight:700;color:#e5e5e5;">
                        {{ $categorie->intitule }}
                    </div>
                </div>

                {{-- Description --}}
                @if($categorie->description)
                <div style="margin-bottom:12px;">
                    <div class="info-label">Description</div>
                    <div style="font-size:12px;color:#888;line-height:1.6;">
                        {{ $categorie->description }}
                    </div>
                </div>
                @endif

                {{-- Statut toggle --}}
                <div style="margin-bottom:12px;">
                    <div class="info-label">Statut</div>
                    <div style="display:flex;align-items:center;gap:8px;cursor:pointer;"
                         onclick="toggleStatut()">
                        <div class="toggle {{ $categorie->statut === 'Activé' ? 'on' : 'off' }}"></div>
                        <span style="font-size:12px;font-weight:600;
                                     color:{{ $categorie->statut === 'Activé' ? '#22c55e' : '#f87171' }};">
                            {{ $categorie->statut }}
                        </span>
                    </div>
                </div>

                {{-- Dates --}}
                <div style="display:flex;flex-direction:column;gap:6px;
                            padding-top:12px;border-top:1px solid #1a1a1a;">
                    <div style="display:flex;justify-content:space-between;font-size:11px;">
                        <span style="color:#444;">Créée le</span>
                        <span style="color:#666;">
                            {{ $categorie->created_at->format('d/m/Y') }}
                        </span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:11px;">
                        <span style="color:#444;">Modifiée le</span>
                        <span style="color:#666;">
                            {{ $categorie->updated_at->format('d/m/Y à H:i') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Top 5 plats vendus ── --}}
        @if($topPlats->isNotEmpty())
        <div class="card">
            <div class="card-header">
                <div style="display:flex;align-items:center;gap:8px;
                            font-size:13px;font-weight:700;color:#e5e5e5;">
                    <i class="fa-solid fa-fire" style="color:var(--cc-orange);"></i>
                    Top plats vendus
                </div>
            </div>
            <div class="card-body">
                @php $maxVendu = $topPlats->max('total_vendu') ?: 1; @endphp

                @foreach($topPlats as $i => $plat)
                <div class="top-row">
                    <span style="width:18px;height:18px;border-radius:50%;flex-shrink:0;
                                 background:rgba(234,88,12,.15);color:#f97316;
                                 font-size:10px;font-weight:700;
                                 display:flex;align-items:center;justify-content:center;">
                        {{ $i + 1 }}
                    </span>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:12px;color:#e5e5e5;
                                    white-space:nowrap;overflow:hidden;
                                    text-overflow:ellipsis;margin-bottom:2px;">
                            {{ $plat->intitule }}
                        </div>
                        <div class="prog">
                            <div class="prog-fill"
                                 style="width:{{ round(($plat->total_vendu / $maxVendu) * 100) }}%;"></div>
                        </div>
                    </div>
                    <div style="text-align:right;flex-shrink:0;">
                        <div style="font-size:12px;font-weight:700;color:#f97316;">
                            {{ $plat->total_vendu }}
                        </div>
                        <div style="font-size:10px;color:#333;">vendus</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ── Résumé plats ── --}}
        <div class="card">
            <div class="card-header">
                <div style="display:flex;align-items:center;gap:8px;
                            font-size:13px;font-weight:700;color:#e5e5e5;">
                    <i class="fa-solid fa-chart-pie" style="color:var(--cc-orange);"></i>
                    Résumé des plats
                </div>
            </div>
            <div class="card-body">
                <div style="display:flex;flex-direction:column;gap:9px;">

                    {{-- Total --}}
                    <div style="display:flex;justify-content:space-between;align-items:center;
                                padding:10px 12px;border-radius:9px;
                                background:var(--cc-dark2);border:1px solid #1a1a1a;">
                        <span style="font-size:12px;color:#555;
                                     display:flex;align-items:center;gap:6px;">
                            <i class="fa-solid fa-layer-group" style="color:#444;"></i>
                            Total plats
                        </span>
                        <span style="font-size:14px;font-weight:700;color:#e5e5e5;">
                            {{ $menus->count() }}
                        </span>
                    </div>

                    {{-- Actifs --}}
                    <div style="display:flex;justify-content:space-between;align-items:center;
                                padding:10px 12px;border-radius:9px;
                                background:rgba(34,197,94,.05);
                                border:1px solid rgba(34,197,94,.15);">
                        <span style="font-size:12px;color:#22c55e;
                                     display:flex;align-items:center;gap:6px;">
                            <i class="fa-solid fa-circle-check"></i>
                            Actifs
                        </span>
                        <span style="font-size:14px;font-weight:700;color:#22c55e;">
                            {{ $menus->where('statut','Activé')->count() }}
                        </span>
                    </div>

                    {{-- Désactivés --}}
                    @if($menus->where('statut','Désactivé')->count() > 0)
                    <div style="display:flex;justify-content:space-between;align-items:center;
                                padding:10px 12px;border-radius:9px;
                                background:rgba(239,68,68,.05);
                                border:1px solid rgba(239,68,68,.15);">
                        <span style="font-size:12px;color:#f87171;
                                     display:flex;align-items:center;gap:6px;">
                            <i class="fa-solid fa-circle-xmark"></i>
                            Désactivés
                        </span>
                        <span style="font-size:14px;font-weight:700;color:#f87171;">
                            {{ $menus->where('statut','Désactivé')->count() }}
                        </span>
                    </div>
                    @endif

                    {{-- Prix moyen --}}
                    @if($menus->count() > 0)
                    <div style="display:flex;justify-content:space-between;align-items:center;
                                padding:10px 12px;border-radius:9px;
                                background:rgba(234,88,12,.05);
                                border:1px solid rgba(234,88,12,.15);">
                        <span style="font-size:12px;color:#f97316;
                                     display:flex;align-items:center;gap:6px;">
                            <i class="fa-solid fa-tag"></i>
                            Prix moyen
                        </span>
                        <span style="font-size:13px;font-weight:700;color:#f97316;">
                            {{ number_format($menus->avg('pu'), 0, ',', ' ') }} FCFA
                        </span>
                    </div>
                    @endif

                </div>
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
// ── Toggle statut catégorie ──────────────────────────────────
function toggleStatut() {
    const desactiver = '{{ $categorie->statut }}' === 'Activé';
    const nbActifs   = {{ $menus->where('statut','Activé')->count() }};

    Swal.fire({
        title: desactiver
            ? 'Désactiver {{ addslashes($categorie->intitule) }} ?'
            : 'Activer {{ addslashes($categorie->intitule) }} ?',
        html: desactiver && nbActifs > 0
            ? `<div style="color:#666;font-size:13px;">
                   Les <strong>${nbActifs} plat(s) actif(s)</strong>
                   seront également désactivés.
               </div>`
            : `<div style="color:#666;font-size:13px;">
                   La catégorie redeviendra visible dans les commandes.
               </div>`,
        icon: desactiver ? 'warning' : 'question',
        iconColor: desactiver ? '#f97316' : '#22c55e',
        background: '#141414', color: '#e5e5e5',
        confirmButtonColor: desactiver ? '#f97316' : '#22c55e',
        confirmButtonText: desactiver ? 'Oui, désactiver' : 'Oui, activer',
        showCancelButton: true,
        cancelButtonText: 'Annuler',
        cancelButtonColor: '#1f1f1f',
    }).then(r => {
        if (r.isConfirmed) document.getElementById('toggleForm').submit();
    });
}

// ── Toggle statut plat ───────────────────────────────────────
function toggleMenu(id, nom, statut) {
    Swal.fire({
        title: statut === 'Activé' ? `Désactiver "${nom}" ?` : `Activer "${nom}" ?`,
        html: `<div style="color:#666;font-size:13px;">
                   ${statut === 'Activé'
                       ? 'Ce plat ne sera plus proposé dans les commandes.'
                       : 'Ce plat redeviendra visible dans les commandes.'}
               </div>`,
        icon: 'question', iconColor: '#ea580c',
        background: '#141414', color: '#e5e5e5',
        confirmButtonColor: '#ea580c',
        confirmButtonText: 'Confirmer',
        showCancelButton: true,
        cancelButtonText: 'Annuler',
        cancelButtonColor: '#1f1f1f',
    }).then(r => {
        if (r.isConfirmed) {
            document.getElementById(`toggleMenuForm-${id}`).submit();
        }
    });
}

// ── Filtre rapide des plats ──────────────────────────────────
function filtrerPlats(filtre) {
    document.querySelectorAll('.filtre-chip').forEach(btn => {
        const actif = btn.dataset.filtre === filtre;
        btn.classList.toggle('active', actif);
    });
    document.querySelectorAll('.plat-item').forEach(card => {
        card.style.display = (filtre === 'Tous' || card.dataset.statut === filtre)
            ? '' : 'none';
    });
}

// ── Suppression catégorie ────────────────────────────────────
function confirmerSuppression() {
    Swal.fire({
        title: 'Supprimer cette catégorie ?',
        html: `<div style="color:#666;font-size:13px;">
                   Action <strong>irréversible</strong>.
                   La catégorie et ses plats seront archivés.
               </div>`,
        icon: 'warning', iconColor: '#ef4444',
        background: '#141414', color: '#e5e5e5',
        confirmButtonColor: '#ef4444',
        confirmButtonText: '<i class="fa-solid fa-trash" style="margin-right:6px"></i>Supprimer',
        showCancelButton: true,
        cancelButtonText: 'Annuler',
        cancelButtonColor: '#1f1f1f',
    }).then(r => {
        if (r.isConfirmed) document.getElementById('deleteForm').submit();
    });
}
</script>
@endpush