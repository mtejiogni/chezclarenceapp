@extends('layouts.app')

@section('title', 'Menus & Plats')
@section('page-title', 'Menus & Plats')

@push('styles')
<style>
    :root {
        --cc-orange:  #ea580c;
        --cc-orange2: #f97316;
        --cc-dark2:   #0d0d0d;
        --cc-dark3:   #141414;
        --cc-border:  #1f1f1f;
    }

    /* ── Carte plat ── */
    .menu-card {
        background: var(--cc-dark3);
        border: 1.5px solid var(--cc-border);
        border-radius: 13px;
        overflow: hidden;
        transition: border-color .2s, transform .18s;
        position: relative;
        display: flex;
        flex-direction: column;
    }

    .menu-card:hover {
        border-color: #2a2a2a;
        transform: translateY(-2px);
    }

    /* ── Photo ── */
    .menu-img {
        height: 130px;
        background: #1a1a1a;
        overflow: hidden;
        position: relative;
        flex-shrink: 0;
    }

    .menu-img img {
        width: 100%; height: 100%;
        object-fit: cover;
        transition: transform .35s;
    }

    .menu-card:hover .menu-img img { transform: scale(1.07); }

    /* ── Badges ── */
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
    .badge-cat      { background: rgba(96,165,250,.12); color: #60a5fa; }

    /* ── Filtres chips ── */
    .filter-chip {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 500;
        cursor: pointer;
        transition: all .18s;
        border: 1px solid var(--cc-border);
        background: var(--cc-dark3);
        color: #555;
        white-space: nowrap;
        font-family: inherit;
    }

    .filter-chip.active,
    .filter-chip:hover {
        background: var(--cc-orange);
        color: #fff;
        border-color: var(--cc-orange);
    }

    /* ── Input recherche ── */
    .search-input {
        background: var(--cc-dark2);
        border: 1px solid var(--cc-border);
        border-radius: 9px;
        padding: 9px 14px 9px 38px;
        color: #e5e5e5;
        font-size: 13px;
        outline: none;
        width: 100%;
        transition: border-color .18s;
        font-family: inherit;
    }

    .search-input::placeholder { color: #333; }
    .search-input:focus { border-color: var(--cc-orange); }

    /* ── Select ── */
    .sel {
        background: var(--cc-dark2);
        border: 1px solid var(--cc-border);
        border-radius: 9px;
        padding: 8px 12px;
        color: #555;
        font-size: 12px;
        outline: none;
        cursor: pointer;
        transition: border-color .18s;
        font-family: inherit;
    }

    .sel:focus { border-color: var(--cc-orange); color: #e5e5e5; }

    /* ── Boutons ── */
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
    .btn-danger:hover { background: #ef4444; color: #fff; border-color: #ef4444; }

    .btn-sm { padding: 5px 10px; font-size: 11px; border-radius: 7px; }

    /* ── Toggle switch ── */
    .toggle {
        width: 34px; height: 19px;
        border-radius: 10px;
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
        top: 2.5px; left: 2.5px;
        width: 14px; height: 14px;
        border-radius: 50%;
        background: #fff;
        transition: left .2s;
    }

    .toggle.on::after { left: 17px; }

    /* ── Compteurs ── */
    .stat-chip {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        border-radius: 9px;
        background: var(--cc-dark3);
        border: 1px solid var(--cc-border);
        font-size: 12px;
        color: #555;
        white-space: nowrap;
    }

    .stat-chip .num {
        font-size: 16px;
        font-weight: 700;
        color: #e5e5e5;
    }

    /* ── Pagination ── */
    .page-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px; height: 32px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 500;
        background: var(--cc-dark3);
        border: 1px solid var(--cc-border);
        color: #555;
        text-decoration: none;
        transition: all .18s;
    }

    .page-link:hover,
    .page-link.active {
        background: var(--cc-orange);
        color: #fff;
        border-color: var(--cc-orange);
    }

    .page-link.disabled { opacity: .35; pointer-events: none; }

    /* ── Vue liste (alternative à la grille) ── */
    .list-row {
        display: flex;
        align-items: center;
        gap: 14px;
        background: var(--cc-dark3);
        border: 1px solid var(--cc-border);
        border-radius: 11px;
        padding: 10px 14px;
        transition: border-color .18s;
    }

    .list-row:hover { border-color: #2a2a2a; }
</style>
@endpush

@section('content')

{{-- ══════════════════════════════════════════════════════════
     EN-TÊTE : compteurs + vue + bouton créer
══════════════════════════════════════════════════════════ --}}
<div style="display:flex;align-items:center;justify-content:space-between;
            flex-wrap:wrap;gap:12px;margin-bottom:20px;">

    {{-- Compteurs --}}
    <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;">
        <div class="stat-chip">
            <i class="fa-solid fa-book-open" style="color:var(--cc-orange);font-size:13px;"></i>
            <span class="num">{{ $totalActifs + $totalInactifs }}</span>
            <span>plat(s) au total</span>
        </div>
        <div class="stat-chip">
            <i class="fa-solid fa-circle-check" style="color:#22c55e;font-size:13px;"></i>
            <span class="num" style="color:#22c55e;">{{ $totalActifs }}</span>
            <span>actif(s)</span>
        </div>
        @if($totalInactifs > 0)
        <div class="stat-chip">
            <i class="fa-solid fa-circle-xmark" style="color:#f87171;font-size:13px;"></i>
            <span class="num" style="color:#f87171;">{{ $totalInactifs }}</span>
            <span>inactif(s)</span>
        </div>
        @endif
    </div>

    <div style="display:flex;align-items:center;gap:8px;">

        {{-- Bouton vue grille / liste --}}
        <button onclick="toggleVue()"
                id="btnVue"
                class="btn btn-ghost btn-sm"
                title="Changer la vue">
            <i class="fa-solid fa-grip" id="icoVue"></i>
        </button>

        {{-- Créer un plat --}}
        <a href="{{ route('admin.menus.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i>
            Nouveau plat
        </a>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     FILTRES
══════════════════════════════════════════════════════════ --}}
<form method="GET"
      action="{{ route('admin.menus.index') }}"
      id="filterForm">

    {{-- Filtres en ligne --}}
    <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px;align-items:center;">

        {{-- Recherche --}}
        <div style="position:relative;flex:1;min-width:200px;">
            <i class="fa-solid fa-magnifying-glass"
               style="position:absolute;left:12px;top:50%;transform:translateY(-50%);
                      color:#333;font-size:13px;pointer-events:none;"></i>
            <input type="text"
                   name="q"
                   value="{{ request('q') }}"
                   class="search-input"
                   placeholder="Rechercher un plat..."
                   oninput="debounceSubmit()">
        </div>

        {{-- Filtre catégorie --}}
        <select name="categorie" class="sel" onchange="this.form.submit()">
            <option value="">Toutes les catégories</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->idcategorie }}"
                {{ request('categorie') == $cat->idcategorie ? 'selected' : '' }}>
                {{ $cat->intitule }}
            </option>
            @endforeach
        </select>

        {{-- Filtre statut --}}
        <select name="statut" class="sel" onchange="this.form.submit()">
            <option value="">Tous les statuts</option>
            <option value="Activé"    {{ request('statut') === 'Activé'    ? 'selected' : '' }}>Activé</option>
            <option value="Désactivé" {{ request('statut') === 'Désactivé' ? 'selected' : '' }}>Désactivé</option>
        </select>

        {{-- Reset --}}
        @if(request()->hasAny(['q','categorie','statut']))
        <a href="{{ route('admin.menus.index') }}" class="btn btn-ghost btn-sm">
            <i class="fa-solid fa-xmark"></i> Réinitialiser
        </a>
        @endif
    </div>

    {{-- Chips catégories rapides --}}
    <div style="display:flex;flex-wrap:nowrap;gap:6px;overflow-x:auto;
                padding-bottom:4px;margin-bottom:18px;">
        <button type="button"
                onclick="filtreCategorie('')"
                class="filter-chip {{ !request('categorie') ? 'active' : '' }}">
            Tous
        </button>
        @foreach($categories as $cat)
        <button type="button"
                onclick="filtreCategorie('{{ $cat->idcategorie }}')"
                class="filter-chip {{ request('categorie') == $cat->idcategorie ? 'active' : '' }}">
            {{ $cat->intitule }}
            <span style="font-size:9px;opacity:.7;">({{ $cat->menus_actifs_count ?? 0 }})</span>
        </button>
        @endforeach
    </div>

    {{-- Champ caché pour la catégorie sélectionnée via chip --}}
    <input type="hidden" name="_cat_chip" id="catChipInput" value="{{ request('categorie') }}">
</form>

{{-- ══════════════════════════════════════════════════════════
     GRILLE DES PLATS
══════════════════════════════════════════════════════════ --}}

@if($menus->isEmpty())

{{-- État vide --}}
<div style="text-align:center;padding:60px 20px;
            background:var(--cc-dark3);border:1px dashed var(--cc-border);
            border-radius:14px;">
    <i class="fa-solid fa-book-open"
       style="font-size:40px;color:#1f1f1f;display:block;margin-bottom:14px;"></i>
    <p style="font-size:15px;font-weight:600;color:#333;margin-bottom:6px;">
        Aucun plat trouvé
    </p>
    <p style="font-size:12px;color:#252525;margin-bottom:20px;">
        @if(request()->hasAny(['q','categorie','statut']))
            Aucun résultat pour ces filtres.
        @else
            Commencez par créer votre premier plat.
        @endif
    </p>
    <a href="{{ route('admin.menus.create') }}" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i>
        Créer un plat
    </a>
</div>

@else

{{-- ── VUE GRILLE ── --}}
<div id="viewGrid"
     style="display:grid;
            grid-template-columns:repeat(auto-fill,minmax(200px,1fr));
            gap:14px;margin-bottom:20px;">

    @foreach($menus as $menu)
    <div class="menu-card animate__animated animate__fadeIn"
         style="animation-delay:{{ $loop->index * 0.03 }}s;">

        {{-- Photo --}}
        <div class="menu-img">
            @if($menu->photo)
            <img src="{{ asset('storage/' . $menu->photo) }}"
                 alt="{{ $menu->intitule }}"
                 loading="lazy">
            @else
            <div style="width:100%;height:100%;
                        display:flex;align-items:center;justify-content:center;
                        background:linear-gradient(135deg,#1a1a1a,#0d0d0d);">
                <i class="fa-solid fa-utensils"
                   style="color:#252525;font-size:28px;"></i>
            </div>
            @endif

            {{-- Badge statut superposé --}}
            <div style="position:absolute;top:7px;left:7px;">
                <span class="badge {{ $menu->statut === 'Activé' ? 'badge-active' : 'badge-inactive' }}">
                    {{ $menu->statut }}
                </span>
            </div>
        </div>

        {{-- Corps --}}
        <div style="padding:12px;flex:1;display:flex;flex-direction:column;gap:6px;">

            {{-- Catégorie --}}
            <div style="font-size:10px;color:#444;display:flex;align-items:center;gap:4px;">
                <i class="fa-solid fa-layer-group" style="font-size:9px;color:#333;"></i>
                {{ $menu->categorie->intitule ?? '—' }}
            </div>

            {{-- Nom --}}
            <h3 style="font-size:13px;font-weight:700;color:#e5e5e5;margin:0;
                        line-height:1.3;display:-webkit-box;
                        -webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                {{ $menu->intitule }}
            </h3>

            {{-- Description --}}
            @if($menu->description)
            <p style="font-size:11px;color:#444;margin:0;line-height:1.5;
                       display:-webkit-box;-webkit-line-clamp:2;
                       -webkit-box-orient:vertical;overflow:hidden;">
                {{ $menu->description }}
            </p>
            @endif

            {{-- Prix --}}
            <div style="font-size:15px;font-weight:700;color:#f97316;margin-top:auto;">
                {{ number_format($menu->pu, 0, ',', ' ') }}
                <span style="font-size:10px;color:#555;">FCFA</span>
            </div>

            {{-- Séparateur --}}
            <div style="height:1px;background:#1a1a1a;"></div>

            {{-- Actions --}}
            <div style="display:flex;align-items:center;gap:6px;">

                {{-- Toggle statut --}}
                <button onclick="toggleStatut(
                            {{ $menu->idmenu }},
                            '{{ addslashes($menu->intitule) }}',
                            '{{ $menu->statut }}'
                        )"
                        title="{{ $menu->statut === 'Activé' ? 'Désactiver' : 'Activer' }}"
                        style="background:none;border:none;cursor:pointer;padding:2px;">
                    <div class="toggle {{ $menu->statut === 'Activé' ? 'on' : 'off' }}"></div>
                </button>

                <div style="flex:1;"></div>

                {{-- Voir --}}
                <a href="{{ route('admin.menus.show', $menu->idmenu) }}"
                   class="btn btn-ghost btn-sm"
                   title="Voir le détail">
                    <i class="fa-solid fa-eye" style="font-size:11px;"></i>
                </a>

                {{-- Modifier --}}
                <a href="{{ route('admin.menus.edit', $menu->idmenu) }}"
                   class="btn btn-ghost btn-sm"
                   title="Modifier">
                    <i class="fa-solid fa-pen-to-square" style="font-size:11px;"></i>
                </a>

                {{-- Supprimer --}}
                <button onclick="confirmerSuppression(
                            {{ $menu->idmenu }},
                            '{{ addslashes($menu->intitule) }}'
                        )"
                        class="btn btn-danger btn-sm"
                        title="Supprimer">
                    <i class="fa-solid fa-trash" style="font-size:11px;"></i>
                </button>
            </div>
        </div>

        {{-- Formulaires cachés --}}
        <form method="POST"
              action="{{ route('admin.menus.toggle-statut', $menu->idmenu) }}"
              id="toggleForm-{{ $menu->idmenu }}"
              style="display:none;">
            @csrf @method('PATCH')
        </form>

        <form method="POST"
              action="{{ route('admin.menus.destroy', $menu->idmenu) }}"
              id="deleteForm-{{ $menu->idmenu }}"
              style="display:none;">
            @csrf @method('DELETE')
        </form>
    </div>
    @endforeach
</div>

{{-- ── VUE LISTE (cachée par défaut) ── --}}
<div id="viewList"
     style="display:none;flex-direction:column;gap:8px;margin-bottom:20px;">

    @foreach($menus as $menu)
    <div class="list-row">

        {{-- Photo miniature --}}
        <div style="width:46px;height:46px;border-radius:9px;overflow:hidden;
                    flex-shrink:0;background:#1a1a1a;border:1px solid #252525;">
            @if($menu->photo)
            <img src="{{ asset('storage/' . $menu->photo) }}"
                 alt="{{ $menu->intitule }}"
                 style="width:100%;height:100%;object-fit:cover;"
                 loading="lazy">
            @else
            <div style="width:100%;height:100%;display:flex;
                        align-items:center;justify-content:center;">
                <i class="fa-solid fa-utensils" style="color:#2a2a2a;font-size:14px;"></i>
            </div>
            @endif
        </div>

        {{-- Infos --}}
        <div style="flex:1;min-width:0;">
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                <span style="font-size:13px;font-weight:600;color:#e5e5e5;">
                    {{ $menu->intitule }}
                </span>
                <span class="badge badge-cat" style="font-size:10px;padding:2px 7px;">
                    {{ $menu->categorie->intitule ?? '—' }}
                </span>
                <span class="badge {{ $menu->statut === 'Activé' ? 'badge-active' : 'badge-inactive' }}"
                      style="font-size:10px;padding:2px 7px;">
                    {{ $menu->statut }}
                </span>
            </div>
            @if($menu->description)
            <div style="font-size:11px;color:#444;margin-top:2px;
                        white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
                        max-width:400px;">
                {{ $menu->description }}
            </div>
            @endif
        </div>

        {{-- Prix --}}
        <div style="font-size:14px;font-weight:700;color:#f97316;
                    flex-shrink:0;min-width:100px;text-align:right;">
            {{ number_format($menu->pu, 0, ',', ' ') }} FCFA
        </div>

        {{-- Actions --}}
        <div style="display:flex;gap:6px;flex-shrink:0;">
            <a href="{{ route('admin.menus.show', $menu->idmenu) }}"
               class="btn btn-ghost btn-sm" title="Voir">
                <i class="fa-solid fa-eye" style="font-size:11px;"></i>
            </a>
            <a href="{{ route('admin.menus.edit', $menu->idmenu) }}"
               class="btn btn-ghost btn-sm" title="Modifier">
                <i class="fa-solid fa-pen-to-square" style="font-size:11px;"></i>
            </a>
            <button onclick="toggleStatut(
                        {{ $menu->idmenu }},
                        '{{ addslashes($menu->intitule) }}',
                        '{{ $menu->statut }}'
                    )"
                    class="btn btn-ghost btn-sm"
                    title="{{ $menu->statut === 'Activé' ? 'Désactiver' : 'Activer' }}">
                <i class="fa-solid {{ $menu->statut === 'Activé' ? 'fa-eye-slash' : 'fa-eye' }}"
                   style="font-size:11px;"></i>
            </button>
            <button onclick="confirmerSuppression(
                        {{ $menu->idmenu }},
                        '{{ addslashes($menu->intitule) }}'
                    )"
                    class="btn btn-danger btn-sm" title="Supprimer">
                <i class="fa-solid fa-trash" style="font-size:11px;"></i>
            </button>
        </div>
    </div>
    @endforeach
</div>

{{-- ── Pagination ── --}}
@if($menus->hasPages())
<div style="display:flex;align-items:center;justify-content:space-between;
            flex-wrap:wrap;gap:10px;margin-top:6px;">

    <div style="font-size:12px;color:#444;">
        {{ $menus->firstItem() }}–{{ $menus->lastItem() }}
        sur {{ $menus->total() }} plat(s)
    </div>

    <div style="display:flex;align-items:center;gap:4px;">

        @if($menus->onFirstPage())
        <span class="page-link disabled">
            <i class="fa-solid fa-chevron-left" style="font-size:10px;"></i>
        </span>
        @else
        <a href="{{ $menus->previousPageUrl() }}" class="page-link">
            <i class="fa-solid fa-chevron-left" style="font-size:10px;"></i>
        </a>
        @endif

        @foreach($menus->getUrlRange(
            max(1, $menus->currentPage() - 2),
            min($menus->lastPage(), $menus->currentPage() + 2)
        ) as $page => $url)
        <a href="{{ $url }}"
           class="page-link {{ $page === $menus->currentPage() ? 'active' : '' }}">
            {{ $page }}
        </a>
        @endforeach

        @if($menus->hasMorePages())
        <a href="{{ $menus->nextPageUrl() }}" class="page-link">
            <i class="fa-solid fa-chevron-right" style="font-size:10px;"></i>
        </a>
        @else
        <span class="page-link disabled">
            <i class="fa-solid fa-chevron-right" style="font-size:10px;"></i>
        </span>
        @endif
    </div>
</div>
@endif

@endif

@endsection

@push('scripts')
<script>
// ════════════════════════════════════════════════════════════
// DEBOUNCE RECHERCHE
// ════════════════════════════════════════════════════════════

let debounceTimer = null;

function debounceSubmit() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        document.getElementById('filterForm').submit();
    }, 500);
}

// ════════════════════════════════════════════════════════════
// FILTRE PAR CATÉGORIE (chips)
// Met à jour le select et le champ caché, puis soumet
// ════════════════════════════════════════════════════════════

function filtreCategorie(idcat) {
    const form   = document.getElementById('filterForm');
    const select = form.querySelector('select[name="categorie"]');
    if (select) select.value = idcat;
    form.submit();
}

// ════════════════════════════════════════════════════════════
// BASCULER VUE GRILLE ↔ LISTE
// ════════════════════════════════════════════════════════════

let vueActuelle = localStorage.getItem('menuVue') || 'grid';

function toggleVue() {
    const grid = document.getElementById('viewGrid');
    const list = document.getElementById('viewList');
    const ico  = document.getElementById('icoVue');

    if (vueActuelle === 'grid') {
        // Passer en liste
        grid.style.display = 'none';
        list.style.display = 'flex';
        ico.className      = 'fa-solid fa-list';
        vueActuelle        = 'list';
    } else {
        // Passer en grille
        grid.style.display = 'grid';
        list.style.display = 'none';
        ico.className      = 'fa-solid fa-grip';
        vueActuelle        = 'grid';
    }

    // Mémoriser le choix
    try { localStorage.setItem('menuVue', vueActuelle); } catch(e) {}
}

// Appliquer la vue mémorisée au chargement
document.addEventListener('DOMContentLoaded', () => {
    if (vueActuelle === 'list') {
        toggleVue();
    }
});

// ════════════════════════════════════════════════════════════
// TOGGLE STATUT (AJAX)
// ════════════════════════════════════════════════════════════

function toggleStatut(id, nom, statutActuel) {
    const desactiver = statutActuel === 'Activé';

    Swal.fire({
        title: desactiver ? `Désactiver "${nom}" ?` : `Activer "${nom}" ?`,
        html: desactiver
            ? `<div style="color:#666;font-size:13px;">
                   Ce plat ne sera plus proposé dans les commandes.
               </div>`
            : `<div style="color:#666;font-size:13px;">
                   Ce plat redeviendra visible dans les commandes.
               </div>`,
        icon: 'question',
        iconColor: desactiver ? '#f97316' : '#22c55e',
        background: '#141414',
        color: '#e5e5e5',
        confirmButtonColor: desactiver ? '#f97316' : '#22c55e',
        confirmButtonText: desactiver ? 'Oui, désactiver' : 'Oui, activer',
        showCancelButton: true,
        cancelButtonText: 'Annuler',
        cancelButtonColor: '#1f1f1f',
    }).then(result => {
        if (result.isConfirmed) {
            document.getElementById(`toggleForm-${id}`).submit();
        }
    });
}

// ════════════════════════════════════════════════════════════
// SUPPRESSION
// ════════════════════════════════════════════════════════════

function confirmerSuppression(id, nom) {
    Swal.fire({
        title: `Supprimer "${nom}" ?`,
        html: `<div style="color:#666;font-size:13px;">
                   Cette action est <strong>irréversible</strong>.
                   Le plat sera archivé.
               </div>`,
        icon: 'warning',
        iconColor: '#ef4444',
        background: '#141414',
        color: '#e5e5e5',
        confirmButtonColor: '#ef4444',
        confirmButtonText: '<i class="fa-solid fa-trash" style="margin-right:6px"></i>Supprimer',
        showCancelButton: true,
        cancelButtonText: 'Annuler',
        cancelButtonColor: '#1f1f1f',
    }).then(result => {
        if (result.isConfirmed) {
            document.getElementById(`deleteForm-${id}`).submit();
        }
    });
}
</script>
@endpush