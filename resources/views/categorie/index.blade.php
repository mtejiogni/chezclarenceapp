@extends('layouts.app')

@section('title', 'Catégories')
@section('page-title', 'Gestion des Catégories')

@push('styles')
<style>
    /* ══════════════════════════════════════════
       VARIABLES
    ══════════════════════════════════════════ */
    :root {
        --cc-orange:  #ea580c;
        --cc-orange2: #f97316;
        --cc-dark2:   #0d0d0d;
        --cc-dark3:   #141414;
        --cc-border:  #1f1f1f;
    }

    /* ── Cartes catégorie ── */
    .cat-card {
        background: var(--cc-dark3);
        border: 1px solid var(--cc-border);
        border-radius: 14px;
        overflow: hidden;
        transition: border-color .2s, transform .18s;
        position: relative;
    }

    .cat-card:hover {
        border-color: #2a2a2a;
        transform: translateY(-2px);
    }

    /* ── Photo de catégorie ── */
    .cat-img {
        height: 120px;
        background: #1a1a1a;
        overflow: hidden;
        position: relative;
    }

    .cat-img img {
        width: 100%; height: 100%;
        object-fit: cover;
        transition: transform .3s;
    }

    .cat-card:hover .cat-img img { transform: scale(1.06); }

    /* ── Placeholder sans photo ── */
    .cat-img-placeholder {
        width: 100%; height: 100%;
        display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #1a1a1a, #0d0d0d);
    }

    /* ── Badges ── */
    .badge {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 3px 10px; border-radius: 20px;
        font-size: 11px; font-weight: 600;
    }

    .badge-active   { background: rgba(34,197,94,.12);  color: #22c55e; }
    .badge-inactive { background: rgba(239,68,68,.12);  color: #f87171; }

    /* ── Toggle switch ── */
    .toggle-wrap {
        display: inline-flex; align-items: center; gap: 8px;
        cursor: pointer;
    }

    .toggle {
        width: 36px; height: 20px;
        border-radius: 10px;
        position: relative;
        transition: background .2s;
        flex-shrink: 0;
    }

    .toggle.on  { background: #22c55e; }
    .toggle.off { background: #2a2a2a; }

    .toggle::after {
        content: '';
        position: absolute;
        top: 3px; left: 3px;
        width: 14px; height: 14px;
        border-radius: 50%;
        background: #fff;
        transition: left .2s;
    }

    .toggle.on::after { left: 19px; }

    /* ── Boutons ── */
    .btn {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 8px 16px; border-radius: 9px;
        font-size: 12px; font-weight: 600;
        cursor: pointer; transition: all .18s;
        border: none; font-family: inherit;
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

    /* ── Input recherche ── */
    .search-input {
        background: var(--cc-dark2);
        border: 1px solid var(--cc-border);
        border-radius: 9px;
        padding: 9px 14px 9px 38px;
        color: #e5e5e5; font-size: 13px;
        outline: none; width: 100%;
        transition: border-color .18s;
        font-family: inherit;
    }

    .search-input::placeholder { color: #333; }
    .search-input:focus { border-color: var(--cc-orange); }

    /* ── Select filtre ── */
    .sel {
        background: var(--cc-dark2);
        border: 1px solid var(--cc-border);
        border-radius: 9px; padding: 8px 12px;
        color: #555; font-size: 12px;
        outline: none; cursor: pointer;
        transition: border-color .18s;
        font-family: inherit;
    }

    .sel:focus { border-color: var(--cc-orange); color: #e5e5e5; }

    /* ── Pagination ── */
    .page-link {
        display: inline-flex; align-items: center; justify-content: center;
        width: 32px; height: 32px; border-radius: 8px;
        font-size: 12px; font-weight: 500;
        background: var(--cc-dark3);
        border: 1px solid var(--cc-border);
        color: #555; text-decoration: none;
        transition: all .18s;
    }

    .page-link:hover,
    .page-link.active {
        background: var(--cc-orange);
        color: #fff; border-color: var(--cc-orange);
    }

    .page-link.disabled { opacity: .35; pointer-events: none; }

    /* ── Modal création/édition ── */
    .modal-overlay {
        position: fixed; inset: 0;
        background: rgba(0,0,0,.7);
        z-index: 200;
        display: flex; align-items: center; justify-content: center;
        padding: 16px;
        backdrop-filter: blur(3px);
    }

    .modal-box {
        background: #0d0d0d;
        border: 1px solid #1f1f1f;
        border-radius: 16px;
        width: 100%; max-width: 500px;
        max-height: 90vh; overflow-y: auto;
        animation: modalIn .25s ease;
    }

    @keyframes modalIn {
        from { opacity: 0; transform: translateY(-20px) scale(.97); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    .modal-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 16px 20px;
        border-bottom: 1px solid #1a1a1a;
    }

    .modal-body { padding: 20px; }

    .modal-footer {
        padding: 14px 20px;
        border-top: 1px solid #1a1a1a;
        display: flex; justify-content: flex-end; gap: 8px;
    }

    /* ── Champs formulaire ── */
    .field-group { margin-bottom: 14px; }

    .field-label {
        display: block; font-size: 10px; font-weight: 600;
        color: #555; letter-spacing: .5px;
        text-transform: uppercase; margin-bottom: 6px;
    }

    .field-input {
        width: 100%; background: #0a0a0a;
        border: 1px solid #1f1f1f; border-radius: 9px;
        padding: 9px 12px; color: #e5e5e5; font-size: 13px;
        outline: none; transition: border-color .18s;
        font-family: inherit;
    }

    .field-input:focus { border-color: var(--cc-orange); }
    .field-input::placeholder { color: #2a2a2a; }
    .field-input.error { border-color: #ef4444; }

    .field-error {
        font-size: 11px; color: #f87171;
        margin-top: 4px; display: none;
        align-items: center; gap: 4px;
    }

    .field-error.show { display: flex; }

    /* ── Aperçu photo ── */
    .photo-preview {
        width: 100%; height: 120px;
        border-radius: 9px; overflow: hidden;
        border: 2px dashed #1f1f1f;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; transition: border-color .18s;
        background: #0a0a0a;
        position: relative;
    }

    .photo-preview:hover { border-color: var(--cc-orange); }

    .photo-preview img {
        width: 100%; height: 100%;
        object-fit: cover;
    }

    /* ── Compteurs résumé ── */
    .stat-chip {
        display: flex; align-items: center; gap: 8px;
        padding: 8px 14px; border-radius: 9px;
        background: var(--cc-dark3);
        border: 1px solid var(--cc-border);
        font-size: 12px; color: #555;
        white-space: nowrap;
    }

    .stat-chip .num {
        font-size: 15px; font-weight: 700;
        color: #e5e5e5;
    }

    /* ── Indicateur plats ── */
    .plats-bar {
        height: 3px; border-radius: 2px;
        background: #1a1a1a; overflow: hidden;
        margin-top: 6px;
    }

    .plats-bar-fill {
        height: 100%; border-radius: 2px;
        background: var(--cc-orange);
        transition: width 1s ease;
    }
</style>
@endpush





@section('content')

{{-- ══════════════════════════════════════════════════════════
     EN-TÊTE : titre + compteurs + bouton créer
══════════════════════════════════════════════════════════ --}}
<div style="display:flex;align-items:flex-start;justify-content:space-between;
            flex-wrap:wrap;gap:12px;margin-bottom:20px;">

    {{-- Compteurs --}}
    <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;">
        <div class="stat-chip">
            <i class="fa-solid fa-layer-group" style="color:var(--cc-orange);font-size:13px;"></i>
            <span class="num">{{ $totalActives + $totalInactives }}</span>
            <span>catégorie(s) au total</span>
        </div>
        <div class="stat-chip">
            <i class="fa-solid fa-circle-check" style="color:#22c55e;font-size:13px;"></i>
            <span class="num" style="color:#22c55e;">{{ $totalActives }}</span>
            <span>active(s)</span>
        </div>
        @if($totalInactives > 0)
        <div class="stat-chip">
            <i class="fa-solid fa-circle-xmark" style="color:#f87171;font-size:13px;"></i>
            <span class="num" style="color:#f87171;">{{ $totalInactives }}</span>
            <span>inactive(s)</span>
        </div>
        @endif
    </div>

    {{-- Bouton créer --}}
    <button onclick="ouvrirModal()"
            class="btn btn-primary">
        <i class="fa-solid fa-plus"></i>
        Nouvelle catégorie
    </button>
</div>



{{-- ══════════════════════════════════════════════════════════
     FILTRES
══════════════════════════════════════════════════════════ --}}
<form method="GET"
      action="{{ route('admin.categories.index') }}"
      id="filterForm"
      style="margin-bottom:20px;">

    <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;">

        {{-- Recherche --}}
        <div style="position:relative;flex:1;min-width:200px;">
            <i class="fa-solid fa-magnifying-glass"
               style="position:absolute;left:12px;top:50%;transform:translateY(-50%);
                      color:#333;font-size:13px;pointer-events:none;"></i>
            <input type="text"
                   name="q"
                   value="{{ request('q') }}"
                   class="search-input"
                   placeholder="Rechercher une catégorie..."
                   oninput="debounceSubmit()">
        </div>

        {{-- Filtre statut --}}
        <select name="statut" class="sel" onchange="this.form.submit()">
            <option value="">Tous les statuts</option>
            <option value="Activé"    {{ request('statut') === 'Activé'    ? 'selected' : '' }}>
                Activé
            </option>
            <option value="Désactivé" {{ request('statut') === 'Désactivé' ? 'selected' : '' }}>
                Désactivé
            </option>
        </select>

        {{-- Reset --}}
        @if(request()->hasAny(['q', 'statut']))
        <a href="{{ route('admin.categories.index') }}" class="btn btn-ghost btn-sm">
            <i class="fa-solid fa-xmark"></i> Réinitialiser
        </a>
        @endif

    </div>
</form>

{{-- ══════════════════════════════════════════════════════════
     GRILLE DES CATÉGORIES
══════════════════════════════════════════════════════════ --}}

@if($categories->isEmpty())

{{-- État vide --}}
<div style="text-align:center;padding:60px 20px;background:var(--cc-dark3);
            border:1px dashed var(--cc-border);border-radius:14px;">
    <i class="fa-solid fa-layer-group"
       style="font-size:40px;color:#1f1f1f;display:block;margin-bottom:14px;"></i>
    <p style="font-size:15px;font-weight:600;color:#333;margin-bottom:6px;">
        Aucune catégorie trouvée
    </p>
    <p style="font-size:12px;color:#252525;margin-bottom:20px;">
        @if(request()->hasAny(['q', 'statut']))
            Aucun résultat pour ces filtres.
        @else
            Commencez par créer votre première catégorie.
        @endif
    </p>
    <button onclick="ouvrirModal()" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i>
        Créer une catégorie
    </button>
</div>

@else

{{-- Grille --}}
@php
    // Max de plats dans une catégorie (pour la barre de progression)
    $maxPlats = $categories->max('menus_count') ?: 1;
@endphp

<div style="display:grid;
            grid-template-columns:repeat(auto-fill,minmax(240px,1fr));
            gap:14px;margin-bottom:20px;">

    @foreach($categories as $cat)
    <div class="cat-card animate__animated animate__fadeIn"
         style="animation-delay:{{ $loop->index * 0.04 }}s;">

        {{-- Photo ou placeholder --}}
        <div class="cat-img">
            @if($cat->photo)
            <img src="{{ asset('storage/' . $cat->photo) }}"
                 alt="{{ $cat->intitule }}"
                 loading="lazy">
            @else
            <div class="cat-img-placeholder">
                <i class="fa-solid fa-utensils"
                   style="font-size:32px;color:#252525;"></i>
            </div>
            @endif

            {{-- Badge statut superposé --}}
            <div style="position:absolute;top:8px;left:8px;">
                <span class="badge {{ $cat->statut === 'Activé' ? 'badge-active' : 'badge-inactive' }}">
                    <i class="fa-solid {{ $cat->statut === 'Activé' ? 'fa-circle-check' : 'fa-circle-xmark' }}"
                       style="font-size:9px;"></i>
                    {{ $cat->statut }}
                </span>
            </div>
        </div>

        {{-- Corps de la carte --}}
        <div style="padding:14px;">

            {{-- Nom de la catégorie --}}
            <div style="display:flex;align-items:center;
                        justify-content:space-between;margin-bottom:8px;">
                <h3 style="font-size:14px;font-weight:700;color:#e5e5e5;
                            margin:0;white-space:nowrap;overflow:hidden;
                            text-overflow:ellipsis;flex:1;min-width:0;">
                    {{ $cat->intitule }}
                </h3>

                {{-- Toggle activer/désactiver --}}
                <button onclick="toggleStatut({{ $cat->idcategorie }}, '{{ $cat->intitule }}', '{{ $cat->statut }}')"
                        title="{{ $cat->statut === 'Activé' ? 'Désactiver' : 'Activer' }}"
                        style="background:none;border:none;cursor:pointer;padding:4px;
                               flex-shrink:0;margin-left:8px;">
                    <div class="toggle {{ $cat->statut === 'Activé' ? 'on' : 'off' }}"></div>
                </button>
            </div>

            {{-- Description --}}
            @if($cat->description)
            <p style="font-size:11px;color:#444;margin:0 0 10px;line-height:1.5;
                       display:-webkit-box;-webkit-line-clamp:2;
                       -webkit-box-orient:vertical;overflow:hidden;">
                {{ $cat->description }}
            </p>
            @endif

            {{-- Nombre de plats + barre de progression --}}
            <div style="margin-bottom:12px;">
                <div style="display:flex;justify-content:space-between;
                            align-items:center;margin-bottom:4px;">
                    <span style="font-size:11px;color:#444;">
                        <i class="fa-solid fa-book-open"
                           style="margin-right:3px;color:#333;"></i>
                        {{ $cat->menus_actifs_count ?? 0 }} plat(s) actif(s)
                    </span>
                    <span style="font-size:10px;color:#333;">
                        / {{ $cat->menus_count ?? 0 }} total
                    </span>
                </div>
                <div class="plats-bar">
                    <div class="plats-bar-fill"
                         style="width:{{ $maxPlats > 0 ? round(($cat->menus_count / $maxPlats) * 100) : 0 }}%;"></div>
                </div>
            </div>

            {{-- Actions --}}
            <div style="display:flex;gap:6px;flex-wrap:wrap;">

                {{-- Voir les plats --}}
                <a href="{{ route('admin.categories.show', $cat->idcategorie) }}"
                   class="btn btn-ghost btn-sm"
                   style="flex:1;justify-content:center;"
                   title="Voir les plats de cette catégorie">
                    <i class="fa-solid fa-eye" style="font-size:11px;"></i>
                    Voir
                </a>

                {{-- Modifier --}}
                <button onclick="ouvrirModalEdit(
                            {{ $cat->idcategorie }},
                            '{{ addslashes($cat->intitule) }}',
                            '{{ addslashes($cat->description ?? '') }}',
                            '{{ $cat->statut }}',
                            '{{ $cat->photo ? asset("storage/" . $cat->photo) : "" }}'
                        )"
                        class="btn btn-ghost btn-sm"
                        title="Modifier la catégorie">
                    <i class="fa-solid fa-pen-to-square" style="font-size:11px;"></i>
                    Modifier
                </button>

                {{-- Supprimer --}}
                <button onclick="confirmerSuppression(
                            {{ $cat->idcategorie }},
                            '{{ addslashes($cat->intitule) }}',
                            {{ $cat->menus_actifs_count ?? 0 }}
                        )"
                        class="btn btn-danger btn-sm"
                        title="Supprimer la catégorie">
                    <i class="fa-solid fa-trash" style="font-size:11px;"></i>
                </button>

            </div>
        </div>

        {{-- Formulaires cachés (suppression & toggle) --}}

        {{-- Formulaire suppression --}}
        <form method="POST"
              action="{{ route('admin.categories.destroy', $cat->idcategorie) }}"
              id="deleteForm-{{ $cat->idcategorie }}"
              style="display:none;">
            @csrf @method('DELETE')
        </form>

        {{-- Formulaire toggle statut --}}
        <form method="POST"
              action="{{ route('admin.categories.toggle-statut', $cat->idcategorie) }}"
              id="toggleForm-{{ $cat->idcategorie }}"
              style="display:none;">
            @csrf @method('PATCH')
        </form>

    </div>
    @endforeach
</div>

{{-- ── Pagination ── --}}
@if($categories->hasPages())
<div style="display:flex;align-items:center;justify-content:space-between;
            flex-wrap:wrap;gap:10px;">

    <div style="font-size:12px;color:#444;">
        {{ $categories->firstItem() }}–{{ $categories->lastItem() }}
        sur {{ $categories->total() }} catégorie(s)
    </div>

    <div style="display:flex;align-items:center;gap:4px;">

        {{-- Précédent --}}
        @if($categories->onFirstPage())
        <span class="page-link disabled">
            <i class="fa-solid fa-chevron-left" style="font-size:10px;"></i>
        </span>
        @else
        <a href="{{ $categories->previousPageUrl() }}" class="page-link">
            <i class="fa-solid fa-chevron-left" style="font-size:10px;"></i>
        </a>
        @endif

        {{-- Pages ── --}}
        @foreach($categories->getUrlRange(
            max(1, $categories->currentPage() - 2),
            min($categories->lastPage(), $categories->currentPage() + 2)
        ) as $page => $url)
        <a href="{{ $url }}"
           class="page-link {{ $page === $categories->currentPage() ? 'active' : '' }}">
            {{ $page }}
        </a>
        @endforeach

        {{-- Suivant --}}
        @if($categories->hasMorePages())
        <a href="{{ $categories->nextPageUrl() }}" class="page-link">
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

{{-- ══════════════════════════════════════════════════════════
     MODAL : CRÉER UNE CATÉGORIE
══════════════════════════════════════════════════════════ --}}
<div id="modalCreate"
     class="modal-overlay"
     style="display:none;"
     onclick="fermerModalSiOverlay(event, 'modalCreate')">

    <div class="modal-box">

        <div class="modal-header">
            <div style="display:flex;align-items:center;gap:8px;">
                <div style="width:32px;height:32px;border-radius:9px;
                            background:rgba(234,88,12,.15);border:1px solid rgba(234,88,12,.2);
                            display:flex;align-items:center;justify-content:center;">
                    <i class="fa-solid fa-plus" style="color:var(--cc-orange);font-size:13px;"></i>
                </div>
                <h3 style="font-size:15px;font-weight:700;color:#e5e5e5;margin:0;">
                    Nouvelle catégorie
                </h3>
            </div>
            <button onclick="fermerModal('modalCreate')"
                    style="background:none;border:none;color:#444;cursor:pointer;
                           font-size:16px;padding:4px;transition:color .18s;"
                    onmouseover="this.style.color='#e5e5e5'"
                    onmouseout="this.style.color='#444'">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST"
              action="{{ route('admin.categories.store') }}"
              enctype="multipart/form-data"
              id="createForm"
              onsubmit="return validerFormCreate()">
            @csrf

            <div class="modal-body">

                {{-- Intitulé --}}
                <div class="field-group">
                    <label class="field-label" for="create_intitule">
                        Nom de la catégorie *
                    </label>
                    <input type="text"
                           name="intitule"
                           id="create_intitule"
                           class="field-input"
                           placeholder="Ex: Grillades, Boissons, Desserts..."
                           oninput="validerChamp('create_intitule', 'err_intitule', 2)"
                           value="{{ old('intitule') }}">
                    <div class="field-error" id="err_intitule">
                        <i class="fa-solid fa-circle-xmark"></i>
                        <span id="err_intitule_txt"></span>
                    </div>
                </div>

                {{-- Description --}}
                <div class="field-group">
                    <label class="field-label" for="create_description">
                        Description
                        <span style="font-weight:400;color:#333;">(optionnel)</span>
                    </label>
                    <textarea name="description"
                              id="create_description"
                              class="field-input"
                              rows="2"
                              style="resize:none;"
                              placeholder="Décrivez brièvement cette catégorie...">{{ old('description') }}</textarea>
                </div>

                {{-- Statut --}}
                <div class="field-group">
                    <label class="field-label" for="create_statut">Statut *</label>
                    <select name="statut"
                            id="create_statut"
                            class="field-input">
                        <option value="Activé"    {{ old('statut','Activé') === 'Activé'    ? 'selected' : '' }}>
                            ✅ Activé
                        </option>
                        <option value="Désactivé" {{ old('statut') === 'Désactivé' ? 'selected' : '' }}>
                            ❌ Désactivé
                        </option>
                    </select>
                </div>

                {{-- Photo --}}
                <div class="field-group">
                    <label class="field-label">
                        Photo
                        <span style="font-weight:400;color:#333;">(optionnel · max 2 Mo)</span>
                    </label>

                    {{-- Zone de clic pour uploader --}}
                    <div class="photo-preview"
                         id="previewCreate"
                         onclick="document.getElementById('create_photo').click()">
                        <div id="previewCreatePlaceholder"
                             style="text-align:center;color:#333;">
                            <i class="fa-solid fa-image"
                               style="font-size:28px;display:block;margin-bottom:6px;"></i>
                            <span style="font-size:11px;">Cliquez pour choisir une image</span>
                        </div>
                        <img id="previewCreateImg"
                             src="" alt=""
                             style="display:none;width:100%;height:100%;object-fit:cover;">
                    </div>

                    <input type="file"
                           name="photo"
                           id="create_photo"
                           accept="image/jpeg,image/png,image/jpg,image/webp"
                           style="display:none;"
                           onchange="previewPhoto(this, 'previewCreateImg', 'previewCreatePlaceholder')">
                </div>

            </div>

            <div class="modal-footer">
                <button type="button"
                        onclick="fermerModal('modalCreate')"
                        class="btn btn-ghost">
                    Annuler
                </button>
                <button type="submit" class="btn btn-primary" id="btnCreate">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     MODAL : MODIFIER UNE CATÉGORIE
══════════════════════════════════════════════════════════ --}}
<div id="modalEdit"
     class="modal-overlay"
     style="display:none;"
     onclick="fermerModalSiOverlay(event, 'modalEdit')">

    <div class="modal-box">

        <div class="modal-header">
            <div style="display:flex;align-items:center;gap:8px;">
                <div style="width:32px;height:32px;border-radius:9px;
                            background:rgba(234,88,12,.15);border:1px solid rgba(234,88,12,.2);
                            display:flex;align-items:center;justify-content:center;">
                    <i class="fa-solid fa-pen-to-square"
                       style="color:var(--cc-orange);font-size:12px;"></i>
                </div>
                <h3 style="font-size:15px;font-weight:700;color:#e5e5e5;margin:0;"
                    id="editModalTitle">
                    Modifier la catégorie
                </h3>
            </div>
            <button onclick="fermerModal('modalEdit')"
                    style="background:none;border:none;color:#444;cursor:pointer;
                           font-size:16px;padding:4px;transition:color .18s;"
                    onmouseover="this.style.color='#e5e5e5'"
                    onmouseout="this.style.color='#444'">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        {{-- Le formulaire est mis à jour dynamiquement par JS --}}
        <form method="POST"
              id="editForm"
              enctype="multipart/form-data"
              onsubmit="return validerFormEdit()">
            @csrf @method('PUT')

            <div class="modal-body">

                {{-- Intitulé --}}
                <div class="field-group">
                    <label class="field-label" for="edit_intitule">
                        Nom de la catégorie *
                    </label>
                    <input type="text"
                           name="intitule"
                           id="edit_intitule"
                           class="field-input"
                           placeholder="Ex: Grillades, Boissons..."
                           oninput="validerChamp('edit_intitule', 'err_edit_intitule', 2)">
                    <div class="field-error" id="err_edit_intitule">
                        <i class="fa-solid fa-circle-xmark"></i>
                        <span id="err_edit_intitule_txt"></span>
                    </div>
                </div>

                {{-- Description --}}
                <div class="field-group">
                    <label class="field-label" for="edit_description">
                        Description
                        <span style="font-weight:400;color:#333;">(optionnel)</span>
                    </label>
                    <textarea name="description"
                              id="edit_description"
                              class="field-input"
                              rows="2"
                              style="resize:none;"
                              placeholder="Décrivez brièvement cette catégorie..."></textarea>
                </div>

                {{-- Statut --}}
                <div class="field-group">
                    <label class="field-label" for="edit_statut">Statut *</label>
                    <select name="statut" id="edit_statut" class="field-input">
                        <option value="Activé">✅ Activé</option>
                        <option value="Désactivé">❌ Désactivé</option>
                    </select>
                </div>

                {{-- Photo --}}
                <div class="field-group">
                    <label class="field-label">
                        Photo
                        <span style="font-weight:400;color:#333;">(laisser vide pour conserver)</span>
                    </label>

                    <div class="photo-preview"
                         id="previewEdit"
                         onclick="document.getElementById('edit_photo').click()">
                        <div id="previewEditPlaceholder"
                             style="text-align:center;color:#333;">
                            <i class="fa-solid fa-image"
                               style="font-size:28px;display:block;margin-bottom:6px;"></i>
                            <span style="font-size:11px;">Cliquez pour changer la photo</span>
                        </div>
                        <img id="previewEditImg"
                             src="" alt=""
                             style="display:none;width:100%;height:100%;object-fit:cover;">
                    </div>

                    <input type="file"
                           name="photo"
                           id="edit_photo"
                           accept="image/jpeg,image/png,image/jpg,image/webp"
                           style="display:none;"
                           onchange="previewPhoto(this, 'previewEditImg', 'previewEditPlaceholder')">
                </div>

            </div>

            <div class="modal-footer">
                <button type="button"
                        onclick="fermerModal('modalEdit')"
                        class="btn btn-ghost">
                    Annuler
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

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
// GESTION DES MODALS
// ════════════════════════════════════════════════════════════

/**
 * Ouvrir le modal de création
 */
function ouvrirModal() {
    // Réinitialiser le formulaire
    document.getElementById('createForm').reset();

    // Réinitialiser l'aperçu photo
    const img  = document.getElementById('previewCreateImg');
    const phld = document.getElementById('previewCreatePlaceholder');
    img.style.display  = 'none';
    img.src = '';
    phld.style.display = 'block';

    // Afficher le modal
    document.getElementById('modalCreate').style.display = 'flex';
    document.body.style.overflow = 'hidden';

    // Focus sur le champ nom
    setTimeout(() => document.getElementById('create_intitule').focus(), 100);
}

/**
 * Ouvrir le modal de modification avec les données existantes
 * @param {number} id         - ID de la catégorie
 * @param {string} intitule   - Nom actuel
 * @param {string} description - Description actuelle
 * @param {string} statut     - Statut actuel
 * @param {string} photoUrl   - URL de la photo actuelle (vide si aucune)
 */
function ouvrirModalEdit(id, intitule, description, statut, photoUrl) {

    // Mettre à jour l'action du formulaire
    const form = document.getElementById('editForm');
    form.action = `/admin/categories/${id}`;

    // Pré-remplir les champs
    document.getElementById('edit_intitule').value    = intitule;
    document.getElementById('edit_description').value = description;
    document.getElementById('edit_statut').value      = statut;

    // Mettre à jour le titre du modal
    document.getElementById('editModalTitle').textContent = `Modifier : ${intitule}`;

    // Gérer l'aperçu photo
    const img  = document.getElementById('previewEditImg');
    const phld = document.getElementById('previewEditPlaceholder');

    if (photoUrl) {
        img.src = photoUrl;
        img.style.display  = 'block';
        phld.style.display = 'none';
    } else {
        img.style.display  = 'none';
        img.src = '';
        phld.style.display = 'block';
    }

    // Réinitialiser l'input photo
    document.getElementById('edit_photo').value = '';

    // Afficher le modal
    document.getElementById('modalEdit').style.display = 'flex';
    document.body.style.overflow = 'hidden';

    setTimeout(() => document.getElementById('edit_intitule').focus(), 100);
}

/**
 * Fermer un modal par son ID
 */
function fermerModal(id) {
    document.getElementById(id).style.display = 'none';
    document.body.style.overflow = '';
}

/**
 * Fermer le modal si on clique sur l'overlay (pas sur la boîte)
 */
function fermerModalSiOverlay(event, id) {
    if (event.target.id === id) {
        fermerModal(id);
    }
}

// Fermer avec Echap
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        fermerModal('modalCreate');
        fermerModal('modalEdit');
    }
});

// ════════════════════════════════════════════════════════════
// APERÇU PHOTO EN TEMPS RÉEL
// ════════════════════════════════════════════════════════════

/**
 * Afficher un aperçu de l'image sélectionnée
 * @param {HTMLInputElement} input       - L'input file
 * @param {string}           imgId       - ID de la balise <img>
 * @param {string}           placeholderId - ID du placeholder
 */
function previewPhoto(input, imgId, placeholderId) {
    const file = input.files[0];
    if (!file) return;

    // Vérifier la taille (max 2 Mo)
    if (file.size > 2 * 1024 * 1024) {
        Swal.fire({
            toast: true,
            position: 'bottom-end',
            icon: 'warning',
            title: 'La photo ne doit pas dépasser 2 Mo.',
            timer: 3000,
            showConfirmButton: false,
            background: '#141414',
            color: '#e5e5e5',
        });
        input.value = '';
        return;
    }

    // Lire et afficher le fichier
    const reader = new FileReader();
    reader.onload = function(e) {
        const img  = document.getElementById(imgId);
        const phld = document.getElementById(placeholderId);
        img.src = e.target.result;
        img.style.display  = 'block';
        phld.style.display = 'none';
    };
    reader.readAsDataURL(file);
}

// ════════════════════════════════════════════════════════════
// VALIDATION DES FORMULAIRES EN TEMPS RÉEL
// ════════════════════════════════════════════════════════════

/**
 * Valider un champ et afficher/masquer l'erreur
 * @param {string} inputId   - ID de l'input
 * @param {string} errId     - ID du bloc d'erreur
 * @param {number} minLength - Longueur minimale requise
 * @returns {boolean}
 */
function validerChamp(inputId, errId, minLength = 1) {
    const input = document.getElementById(inputId);
    const err   = document.getElementById(errId);
    const txt   = document.getElementById(errId + '_txt');
    const val   = input.value.trim();
    let message = '';

    if (!val) {
        message = 'Ce champ est obligatoire.';
    } else if (val.length < minLength) {
        message = `Minimum ${minLength} caractères requis.`;
    }

    if (message) {
        input.classList.add('error');
        if (txt) txt.textContent = message;
        err.classList.add('show');
        return false;
    } else {
        input.classList.remove('error');
        err.classList.remove('show');
        return true;
    }
}

/**
 * Valider le formulaire de création avant soumission
 */
function validerFormCreate() {
    const ok = validerChamp('create_intitule', 'err_intitule', 2);
    if (!ok) {
        document.getElementById('create_intitule').focus();
        return false;
    }
    // Montrer le loader sur le bouton
    const btn = document.getElementById('btnCreate');
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Enregistrement...';
    btn.disabled = true;
    return true;
}

/**
 * Valider le formulaire de modification avant soumission
 */
function validerFormEdit() {
    return validerChamp('edit_intitule', 'err_edit_intitule', 2);
}

// ════════════════════════════════════════════════════════════
// TOGGLE STATUT (AJAX)
// ════════════════════════════════════════════════════════════

/**
 * Activer ou désactiver une catégorie avec confirmation
 * Si désactivation, avertir que les plats seront aussi désactivés
 */
function toggleStatut(id, nom, statutActuel) {
    const desactiver = statutActuel === 'Activé';

    Swal.fire({
        title: desactiver ? `Désactiver "${nom}" ?` : `Activer "${nom}" ?`,
        html: desactiver
            ? `<div style="color:#666;font-size:13px;">
                   Tous les plats de cette catégorie seront également désactivés.
               </div>`
            : `<div style="color:#666;font-size:13px;">
                   La catégorie redeviendra visible dans les commandes.
               </div>`,
        icon: desactiver ? 'warning' : 'question',
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
            // Soumettre le formulaire hidden
            document.getElementById(`toggleForm-${id}`).submit();
        }
    });
}

// ════════════════════════════════════════════════════════════
// SUPPRESSION
// ════════════════════════════════════════════════════════════

/**
 * Confirmer et supprimer une catégorie
 * @param {number} id        - ID de la catégorie
 * @param {string} nom       - Nom affiché dans la confirmation
 * @param {number} nbPlats   - Nombre de plats actifs (bloque si > 0)
 */
function confirmerSuppression(id, nom, nbPlats) {

    // Bloquer si des plats actifs existent
    if (nbPlats > 0) {
        Swal.fire({
            title: 'Suppression impossible',
            html: `
                <div style="color:#666;font-size:13px;">
                    La catégorie <strong style="color:#f97316;">${nom}</strong>
                    contient <strong>${nbPlats} plat(s) actif(s)</strong>.
                    <br><br>
                    Désactivez ou supprimez les plats avant de supprimer la catégorie.
                </div>
            `,
            icon: 'error',
            iconColor: '#ef4444',
            background: '#141414',
            color: '#e5e5e5',
            confirmButtonColor: '#ea580c',
            confirmButtonText: 'Compris',
        });
        return;
    }

    // Confirmation avant suppression
    Swal.fire({
        title: `Supprimer "${nom}" ?`,
        html: `
            <div style="color:#666;font-size:13px;">
                Cette action est <strong>irréversible</strong>.
                La catégorie et tous ses plats seront archivés.
            </div>
        `,
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

// ════════════════════════════════════════════════════════════
// OUVERTURE AUTO DU MODAL SI ERREURS SERVEUR
// ════════════════════════════════════════════════════════════

{{-- Si des erreurs sont présentes après soumission du formulaire
     de création, rouvrir le modal automatiquement --}}
@if($errors->any() && old('_method') === null)
document.addEventListener('DOMContentLoaded', () => {
    ouvrirModal();
});
@endif
</script>
@endpush