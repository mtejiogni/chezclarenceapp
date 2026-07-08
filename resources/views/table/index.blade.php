@extends('layouts.app')

@section('title', 'Tables')
@section('page-title', 'Gestion des Tables')

@push('styles')
<style>
    :root {
        --cc-orange:  #ea580c;
        --cc-orange2: #f97316;
        --cc-dark2:   #0d0d0d;
        --cc-dark3:   #141414;
        --cc-border:  #1f1f1f;
    }

    /* ── Carte table ── */
    .table-card {
        background: var(--cc-dark3);
        border: 1.5px solid var(--cc-border);
        border-radius: 14px;
        padding: 1.1rem;
        transition: border-color .2s, transform .18s;
        position: relative;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .table-card:hover {
        border-color: #2a2a2a;
        transform: translateY(-2px);
    }

    .table-card.occupee {
        border-color: rgba(234,88,12,.35);
        background: rgba(234,88,12,.03);
    }

    .table-card.libre {
        border-color: rgba(34,197,94,.2);
    }

    /* ── Icône centrale ── */
    .table-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        margin: 0 auto 4px;
    }

    /* ── Indicateur statut (point clignotant) ── */
    .statut-dot {
        width: 8px; height: 8px;
        border-radius: 50%;
        position: absolute;
        top: 10px; right: 10px;
    }

    @keyframes pulse-green {
        0%, 100% { box-shadow: 0 0 0 0 rgba(34,197,94,.4); }
        50%       { box-shadow: 0 0 0 5px rgba(34,197,94,0); }
    }

    @keyframes pulse-orange {
        0%, 100% { box-shadow: 0 0 0 0 rgba(234,88,12,.5); }
        50%       { box-shadow: 0 0 0 5px rgba(234,88,12,0); }
    }

    .dot-libre   { background: #22c55e; animation: pulse-green  2s infinite; }
    .dot-occupee { background: var(--cc-orange); animation: pulse-orange 1.5s infinite; }

    /* ── Plan de salle (vue grille) ── */
    .plan-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 12px;
    }

    /* ── Vue liste ── */
    .list-row {
        display: flex;
        align-items: center;
        gap: 12px;
        background: var(--cc-dark3);
        border: 1px solid var(--cc-border);
        border-radius: 11px;
        padding: 12px 16px;
        transition: border-color .18s;
    }

    .list-row:hover { border-color: #2a2a2a; }
    .list-row.occupee { border-color: rgba(234,88,12,.3); }

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

    .badge-libre   { background: rgba(34,197,94,.12);  color: #22c55e; }
    .badge-occupee { background: rgba(234,88,12,.12);  color: #f97316; }

    /* ── Boutons ── */
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 14px;
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

    .btn-success {
        background: rgba(34,197,94,.1);
        border: 1px solid rgba(34,197,94,.2);
        color: #22c55e;
    }
    .btn-success:hover { background: #22c55e; color: #fff; }

    .btn-sm { padding: 5px 10px; font-size: 11px; border-radius: 7px; }

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

    /* ── Modal création/edition ── */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,.75);
        z-index: 200;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
        backdrop-filter: blur(3px);
    }

    .modal-box {
        background: #0d0d0d;
        border: 1px solid #1f1f1f;
        border-radius: 16px;
        width: 100%;
        max-width: 460px;
        animation: modalIn .22s ease;
    }

    @keyframes modalIn {
        from { opacity:0; transform: translateY(-16px) scale(.97); }
        to   { opacity:1; transform: translateY(0) scale(1); }
    }

    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        border-bottom: 1px solid #1a1a1a;
    }

    .modal-body    { padding: 20px; }
    .modal-footer  {
        padding: 14px 20px;
        border-top: 1px solid #1a1a1a;
        display: flex;
        justify-content: flex-end;
        gap: 8px;
    }

    /* ── Champs formulaire ── */
    .field-group { margin-bottom: 14px; }

    .field-label {
        display: block;
        font-size: 10px;
        font-weight: 600;
        color: #555;
        letter-spacing: .5px;
        text-transform: uppercase;
        margin-bottom: 6px;
    }

    .field-input {
        width: 100%;
        background: #0a0a0a;
        border: 1px solid #1f1f1f;
        border-radius: 9px;
        padding: 9px 12px;
        color: #e5e5e5;
        font-size: 13px;
        outline: none;
        transition: border-color .18s;
        font-family: inherit;
    }

    .field-input:focus { border-color: var(--cc-orange); }
    .field-input::placeholder { color: #2a2a2a; }
    .field-input.is-error { border-color: #ef4444; }

    .field-error {
        font-size: 11px;
        color: #f87171;
        margin-top: 4px;
        display: none;
        align-items: center;
        gap: 4px;
    }

    .field-error.show { display: flex; }

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

    /* ── Refresh indicator ── */
    @keyframes spin { to { transform: rotate(360deg); } }
    .spin { animation: spin .6s linear infinite; }
</style>
@endpush

@section('content')

{{-- ══════════════════════════════════════════════════════════
     EN-TÊTE : compteurs + légende + actions
══════════════════════════════════════════════════════════ --}}
<div style="display:flex;align-items:center;justify-content:space-between;
            flex-wrap:wrap;gap:12px;margin-bottom:20px;">

    {{-- Compteurs --}}
    <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;">

        <div class="stat-chip">
            <i class="fa-solid fa-chair" style="color:var(--cc-orange);font-size:13px;"></i>
            <span class="num">{{ $totalTables }}</span>
            <span>table(s)</span>
        </div>

        <div class="stat-chip">
            <span class="statut-dot dot-libre" style="position:static;"></span>
            <span class="num" style="color:#22c55e;"
                  id="cnt-libres">{{ $tablesLibres }}</span>
            <span>libre(s)</span>
        </div>

        <div class="stat-chip">
            <span class="statut-dot dot-occupee" style="position:static;"></span>
            <span class="num" style="color:#f97316;"
                  id="cnt-occupees">{{ $tablesOccupees }}</span>
            <span>occupée(s)</span>
        </div>

        @if($caEnCours > 0)
        <div class="stat-chip">
            <i class="fa-solid fa-coins" style="color:#22c55e;font-size:13px;"></i>
            <span class="num" style="color:#22c55e;">
                {{ number_format($caEnCours, 0, ',', ' ') }}
            </span>
            <span>FCFA en cours</span>
        </div>
        @endif
    </div>

    {{-- Actions --}}
    <div style="display:flex;align-items:center;gap:8px;">

        {{-- Rafraîchir le plan --}}
        <button onclick="rafraichirPlan()"
                id="btnRefresh"
                class="btn btn-ghost btn-sm"
                title="Rafraîchir le plan de salle">
            <i class="fa-solid fa-rotate-right" id="icoRefresh"></i>
        </button>

        {{-- Bascule vue --}}
        <button onclick="toggleVue()"
                id="btnVue"
                class="btn btn-ghost btn-sm"
                title="Changer la vue">
            <i class="fa-solid fa-grip" id="icoVue"></i>
        </button>

        {{-- Nouvelle table --}}
        <button onclick="ouvrirModalCreate()"
                class="btn btn-primary">
            <i class="fa-solid fa-plus"></i>
            Nouvelle table
        </button>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     FILTRES
══════════════════════════════════════════════════════════ --}}
<form method="GET"
      action="{{ route('admin.tables.index') }}"
      id="filterForm"
      style="margin-bottom:18px;">

    <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;">

        {{-- Recherche --}}
        <div style="position:relative;flex:1;min-width:180px;">
            <i class="fa-solid fa-magnifying-glass"
               style="position:absolute;left:12px;top:50%;transform:translateY(-50%);
                      color:#333;font-size:13px;pointer-events:none;"></i>
            <input type="text"
                   name="q"
                   value="{{ request('q') }}"
                   class="search-input"
                   placeholder="Rechercher une table..."
                   oninput="debounceSubmit()">
        </div>

        {{-- Filtre statut --}}
        @foreach(['','libre','occupee'] as $s)
        <button type="button"
                onclick="filtreStatut('{{ $s }}')"
                data-s="{{ $s }}"
                class="filtre-s"
                style="padding:7px 14px;border-radius:20px;font-size:11px;
                       font-weight:500;cursor:pointer;transition:all .18s;
                       border:1px solid #1f1f1f;font-family:inherit;
                       background:{{ request('statut', '') === $s ? '#ea580c' : '#141414' }};
                       color:{{ request('statut', '') === $s ? '#fff' : '#555' }};">
            @if($s === '') Toutes
            @elseif($s === 'libre') 🟢 Libres
            @else 🟠 Occupées
            @endif
        </button>
        @endforeach

        {{-- Champ caché statut --}}
        <input type="hidden" name="statut" id="inputStatut" value="{{ request('statut') }}">

        @if(request()->hasAny(['q','statut']))
        <a href="{{ route('admin.tables.index') }}" class="btn btn-ghost btn-sm">
            <i class="fa-solid fa-xmark"></i> Réinitialiser
        </a>
        @endif
    </div>
</form>

{{-- ══════════════════════════════════════════════════════════
     PLAN DE SALLE / VUE GRILLE
══════════════════════════════════════════════════════════ --}}

@if($tables->isEmpty())

<div style="text-align:center;padding:60px 20px;
            background:var(--cc-dark3);border:1px dashed var(--cc-border);
            border-radius:14px;">
    <i class="fa-solid fa-chair"
       style="font-size:40px;color:#1f1f1f;display:block;margin-bottom:14px;"></i>
    <p style="font-size:15px;font-weight:600;color:#333;margin-bottom:6px;">
        Aucune table configurée
    </p>
    <p style="font-size:12px;color:#252525;margin-bottom:20px;">
        Commencez par créer vos premières tables.
    </p>
    <button onclick="ouvrirModalCreate()" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i>
        Créer une table
    </button>
</div>

@else

{{-- ── VUE PLAN (grille) ── --}}
<div id="viewGrid" class="plan-grid" style="margin-bottom:20px;">

    @foreach($tables as $table)
    <div class="table-card {{ $table->occupee ? 'occupee' : 'libre' }} animate__animated animate__fadeIn"
         style="animation-delay:{{ $loop->index * 0.04 }}s;">

        {{-- Indicateur statut --}}
        <div class="statut-dot {{ $table->occupee ? 'dot-occupee' : 'dot-libre' }}"></div>

        {{-- Icône + nom --}}
        <div style="text-align:center;">
            <div class="table-icon"
                 style="background:{{ $table->occupee ? 'rgba(234,88,12,.12)' : 'rgba(34,197,94,.1)' }};
                        border:1px solid {{ $table->occupee ? 'rgba(234,88,12,.25)' : 'rgba(34,197,94,.2)' }};">
                <i class="fa-solid fa-chair"
                   style="color:{{ $table->occupee ? '#f97316' : '#22c55e' }};"></i>
            </div>
            <h3 style="font-size:14px;font-weight:700;color:#e5e5e5;
                        margin:6px 0 4px;">
                {{ $table->intitule }}
            </h3>
            @if($table->description)
            <p style="font-size:10px;color:#444;margin:0;
                       white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                {{ $table->description }}
            </p>
            @endif
        </div>

        {{-- Statut + montant --}}
        <div style="text-align:center;">
            @if($table->occupee)
            <div style="font-size:14px;font-weight:700;color:#f97316;margin-bottom:3px;">
                {{ number_format($table->montant_total, 0, ',', ' ') }} FCFA
            </div>
            <span class="badge badge-occupee" style="font-size:10px;">
                <i class="fa-solid fa-circle" style="font-size:6px;"></i>
                {{ $table->nb_commandes_actives }} commande{{ $table->nb_commandes_actives > 1 ? 's' : '' }}
            </span>
            @else
            <span class="badge badge-libre">
                <i class="fa-solid fa-circle-check" style="font-size:9px;"></i>
                Libre
            </span>
            @endif
        </div>

        {{-- Actions --}}
        <div style="display:flex;gap:6px;justify-content:center;flex-wrap:wrap;">

            {{-- Voir détail --}}
            <a href="{{ route('admin.tables.show', $table->idtable) }}"
               class="btn btn-ghost btn-sm"
               title="Voir le détail">
                <i class="fa-solid fa-eye" style="font-size:10px;"></i>
            </a>

            {{-- Modifier --}}
            <button onclick="ouvrirModalEdit(
                        {{ $table->idtable }},
                        '{{ addslashes($table->intitule) }}',
                        '{{ addslashes($table->description ?? '') }}'
                    )"
                    class="btn btn-ghost btn-sm"
                    title="Modifier">
                <i class="fa-solid fa-pen-to-square" style="font-size:10px;"></i>
            </button>

            {{-- Nouvelle commande (toujours possible, même si occupée) --}}
            @if(in_array(auth()->user()->role, ['Administrateur','Caissier','Serveur']))
            <a href="{{ route('commandes.create') }}?table={{ $table->idtable }}"
               class="btn btn-success btn-sm"
               title="Nouvelle commande sur cette table">
                <i class="fa-solid fa-plus" style="font-size:10px;"></i>
            </a>
            @endif

            {{-- Libérer (Admin, table occupée) --}}
            @if($table->occupee && auth()->user()->role === 'Administrateur')
            <button onclick="libererTable(
                        {{ $table->idtable }},
                        '{{ addslashes($table->intitule) }}',
                        {{ $table->nb_commandes_actives ?? 0 }}
                    )"
                    class="btn btn-sm"
                    style="background:rgba(234,179,8,.1);border:1px solid rgba(234,179,8,.2);
                           color:#eab308;font-size:11px;"
                    title="Libérer manuellement la table">
                <i class="fa-solid fa-unlock" style="font-size:10px;"></i>
            </button>
            @endif

            {{-- Supprimer --}}
            @if(!$table->occupee)
            <button onclick="confirmerSuppression(
                        {{ $table->idtable }},
                        '{{ addslashes($table->intitule) }}'
                    )"
                    class="btn btn-danger btn-sm"
                    title="Supprimer">
                <i class="fa-solid fa-trash" style="font-size:10px;"></i>
            </button>
            @endif

        </div>

        {{-- Formulaires cachés --}}
        <form method="POST"
              action="{{ route('admin.tables.destroy', $table->idtable) }}"
              id="deleteForm-{{ $table->idtable }}"
              style="display:none;">
            @csrf @method('DELETE')
        </form>

        <form method="POST"
              action="{{ route('admin.tables.liberer', $table->idtable) }}"
              id="libererForm-{{ $table->idtable }}"
              style="display:none;">
            @csrf @method('PATCH')
        </form>
    </div>
    @endforeach
</div>

{{-- ── VUE LISTE (cachée par défaut) ── --}}
<div id="viewList"
     style="display:none;flex-direction:column;gap:8px;margin-bottom:20px;">

    @foreach($tables as $table)
    <div class="list-row {{ $table->occupee ? 'occupee' : '' }}">

        {{-- Icône statut --}}
        <div style="width:40px;height:40px;border-radius:10px;flex-shrink:0;
                    display:flex;align-items:center;justify-content:center;
                    background:{{ $table->occupee ? 'rgba(234,88,12,.12)' : 'rgba(34,197,94,.1)' }};
                    border:1px solid {{ $table->occupee ? 'rgba(234,88,12,.25)' : 'rgba(34,197,94,.2)' }};">
            <i class="fa-solid fa-chair"
               style="font-size:14px;color:{{ $table->occupee ? '#f97316' : '#22c55e' }};"></i>
        </div>

        {{-- Infos --}}
        <div style="flex:1;min-width:0;">
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                <span style="font-size:13px;font-weight:600;color:#e5e5e5;">
                    {{ $table->intitule }}
                </span>
                @if($table->description)
                <span style="font-size:11px;color:#444;">{{ $table->description }}</span>
                @endif
            </div>
            @if($table->occupee && $table->nb_commandes_actives > 0)
            <div style="font-size:11px;color:#f97316;margin-top:1px;">
                <i class="fa-solid fa-receipt" style="margin-right:3px;"></i>
                {{ $table->nb_commandes_actives }} commande{{ $table->nb_commandes_actives > 1 ? 's' : '' }} en cours
                @if($table->reference_active)
                · dernière : {{ $table->reference_active }}
                @endif
            </div>
            @endif
        </div>

        {{-- Montant (si occupée) --}}
        @if($table->occupee && $table->montant_total > 0)
        <div style="font-size:13px;font-weight:700;color:#f97316;
                    flex-shrink:0;text-align:right;min-width:100px;">
            {{ number_format($table->montant_total, 0, ',', ' ') }} FCFA
        </div>
        @endif

        {{-- Badge --}}
        <div style="flex-shrink:0;">
            <span class="badge {{ $table->occupee ? 'badge-occupee' : 'badge-libre' }}">
                {{ $table->occupee ? 'Occupée' : 'Libre' }}
            </span>
        </div>

        {{-- Actions --}}
        <div style="display:flex;gap:6px;flex-shrink:0;">
            <a href="{{ route('admin.tables.show', $table->idtable) }}"
               class="btn btn-ghost btn-sm" title="Voir">
                <i class="fa-solid fa-eye" style="font-size:10px;"></i>
            </a>
            <button onclick="ouvrirModalEdit(
                        {{ $table->idtable }},
                        '{{ addslashes($table->intitule) }}',
                        '{{ addslashes($table->description ?? '') }}'
                    )"
                    class="btn btn-ghost btn-sm" title="Modifier">
                <i class="fa-solid fa-pen-to-square" style="font-size:10px;"></i>
            </button>
            @if(in_array(auth()->user()->role,['Administrateur','Caissier','Serveur']))
            <a href="{{ route('commandes.create') }}?table={{ $table->idtable }}"
               class="btn btn-success btn-sm" title="Nouvelle commande">
                <i class="fa-solid fa-plus" style="font-size:10px;"></i>
            </a>
            @endif
            @if($table->occupee && auth()->user()->role === 'Administrateur')
            <button onclick="libererTable(
                        {{ $table->idtable }},
                        '{{ addslashes($table->intitule) }}',
                        {{ $table->nb_commandes_actives ?? 0 }}
                    )"
                    class="btn btn-sm"
                    style="background:rgba(234,179,8,.1);border:1px solid rgba(234,179,8,.2);color:#eab308;">
                <i class="fa-solid fa-unlock" style="font-size:10px;"></i>
            </button>
            @endif
            @if(!$table->occupee)
            <button onclick="confirmerSuppression(
                        {{ $table->idtable }},
                        '{{ addslashes($table->intitule) }}'
                    )"
                    class="btn btn-danger btn-sm" title="Supprimer">
                <i class="fa-solid fa-trash" style="font-size:10px;"></i>
            </button>
            @endif
        </div>
    </div>
    @endforeach
</div>

@endif

{{-- ══════════════════════════════════════════════════════════
     MODAL : CRÉER UNE TABLE
══════════════════════════════════════════════════════════ --}}
<div id="modalCreate"
     class="modal-overlay"
     style="display:none;"
     onclick="fermerModalSiOverlay(event,'modalCreate')">

    <div class="modal-box">
        <div class="modal-header">
            <div style="display:flex;align-items:center;gap:8px;">
                <div style="width:32px;height:32px;border-radius:8px;
                            background:rgba(234,88,12,.15);
                            display:flex;align-items:center;justify-content:center;">
                    <i class="fa-solid fa-chair"
                       style="color:var(--cc-orange);font-size:13px;"></i>
                </div>
                <h3 style="font-size:14px;font-weight:700;color:#e5e5e5;margin:0;">
                    Nouvelle table
                </h3>
            </div>
            <button onclick="fermerModal('modalCreate')"
                    style="background:none;border:none;color:#444;cursor:pointer;
                           font-size:16px;padding:4px;"
                    onmouseover="this.style.color='#e5e5e5'"
                    onmouseout="this.style.color='#444'">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST"
              action="{{ route('admin.tables.store') }}"
              id="createForm"
              onsubmit="return validerFormCreate()">
            @csrf

            <div class="modal-body">

                {{-- Nom de la table --}}
                <div class="field-group">
                    <label class="field-label" for="c_intitule">
                        Nom de la table *
                    </label>
                    <input type="text"
                           name="intitule"
                           id="c_intitule"
                           class="field-input"
                           placeholder="{{ $suggestionNom ?? 'Ex: Table 01, VIP 01, Bar...' }}"
                           value="{{ $suggestionNom ?? '' }}"
                           maxlength="128"
                           autocomplete="off"
                           oninput="validerChamp('c_intitule','err_c_intitule')">
                    <div class="field-error" id="err_c_intitule">
                        <i class="fa-solid fa-circle-xmark"></i>
                        <span id="err_c_intitule_txt"></span>
                    </div>
                </div>

                {{-- Description --}}
                <div class="field-group">
                    <label class="field-label" for="c_description">
                        Description
                        <span style="font-weight:400;color:#333;">(optionnel)</span>
                    </label>
                    <input type="text"
                           name="description"
                           id="c_description"
                           class="field-input"
                           placeholder="Ex: Terrasse, VIP, Proche de la fenêtre..."
                           maxlength="300">
                </div>

                {{-- Création multiple --}}
                <div style="padding:12px;background:#0a0a0a;border:1px solid #1a1a1a;
                            border-radius:9px;">
                    <div style="display:flex;align-items:center;gap:8px;
                                margin-bottom:10px;cursor:pointer;"
                         onclick="toggleMultiple()">
                        <div style="width:16px;height:16px;border-radius:4px;
                                    border:1px solid #2a2a2a;background:#141414;
                                    display:flex;align-items:center;justify-content:center;"
                             id="checkMultiple">
                        </div>
                        <span style="font-size:12px;color:#555;">
                            Créer plusieurs tables d'un coup
                        </span>
                    </div>
                    <div id="blockMultiple" style="display:none;">
                        <div style="display:grid;grid-template-columns:1fr 80px;gap:8px;">
                            <div class="field-group" style="margin-bottom:0;">
                                <label class="field-label">Préfixe</label>
                                <input type="text" name="prefixe" id="c_prefixe"
                                       class="field-input" value="Table"
                                       placeholder="Table, VIP, Bar...">
                            </div>
                            <div class="field-group" style="margin-bottom:0;">
                                <label class="field-label">Quantité</label>
                                <input type="number" name="nombre" id="c_nombre"
                                       class="field-input" value="5"
                                       min="1" max="50" placeholder="5">
                            </div>
                        </div>
                        <div style="margin-top:8px;">
                            <label class="field-label">Numéro de départ</label>
                            <input type="number" name="debut" id="c_debut"
                                   class="field-input" value="1"
                                   min="1" placeholder="1">
                        </div>
                        <input type="hidden" name="creation_multiple" id="inputMultiple" value="0">
                        <div style="margin-top:8px;font-size:11px;color:#444;"
                             id="previewMultiple">
                            Prévisualisation : Table 01, Table 02...
                        </div>
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button"
                        onclick="fermerModal('modalCreate')"
                        class="btn btn-ghost btn-sm">
                    Annuler
                </button>
                <button type="submit"
                        id="btnCreate"
                        class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     MODAL : MODIFIER UNE TABLE
══════════════════════════════════════════════════════════ --}}
<div id="modalEdit"
     class="modal-overlay"
     style="display:none;"
     onclick="fermerModalSiOverlay(event,'modalEdit')">

    <div class="modal-box">
        <div class="modal-header">
            <div style="display:flex;align-items:center;gap:8px;">
                <div style="width:32px;height:32px;border-radius:8px;
                            background:rgba(234,88,12,.15);
                            display:flex;align-items:center;justify-content:center;">
                    <i class="fa-solid fa-pen-to-square"
                       style="color:var(--cc-orange);font-size:12px;"></i>
                </div>
                <h3 style="font-size:14px;font-weight:700;color:#e5e5e5;margin:0;"
                    id="editTitle">Modifier la table</h3>
            </div>
            <button onclick="fermerModal('modalEdit')"
                    style="background:none;border:none;color:#444;cursor:pointer;font-size:16px;padding:4px;"
                    onmouseover="this.style.color='#e5e5e5'"
                    onmouseout="this.style.color='#444'">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" id="editForm" onsubmit="return validerFormEdit()">
            @csrf @method('PUT')
            <div class="modal-body">

                <div class="field-group">
                    <label class="field-label" for="e_intitule">Nom *</label>
                    <input type="text" name="intitule" id="e_intitule"
                           class="field-input" maxlength="128"
                           oninput="validerChamp('e_intitule','err_e_intitule')">
                    <div class="field-error" id="err_e_intitule">
                        <i class="fa-solid fa-circle-xmark"></i>
                        <span id="err_e_intitule_txt"></span>
                    </div>
                </div>

                <div class="field-group" style="margin-bottom:0;">
                    <label class="field-label" for="e_description">
                        Description <span style="font-weight:400;color:#333;">(optionnel)</span>
                    </label>
                    <input type="text" name="description" id="e_description"
                           class="field-input" maxlength="300"
                           placeholder="Ex: Terrasse, VIP...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button"
                        onclick="fermerModal('modalEdit')"
                        class="btn btn-ghost btn-sm">Annuler</button>
                <button type="submit" class="btn btn-primary btn-sm">
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
    debounceTimer = setTimeout(() => document.getElementById('filterForm').submit(), 500);
}

// ════════════════════════════════════════════════════════════
// FILTRE STATUT (chips)
// ════════════════════════════════════════════════════════════

function filtreStatut(s) {
    document.getElementById('inputStatut').value = s;
    document.getElementById('filterForm').submit();
}

// ════════════════════════════════════════════════════════════
// VUE GRILLE / LISTE
// ════════════════════════════════════════════════════════════

let vueActuelle = localStorage.getItem('tableVue') || 'grid';

function toggleVue() {
    const grid = document.getElementById('viewGrid');
    const list = document.getElementById('viewList');
    const ico  = document.getElementById('icoVue');

    if (vueActuelle === 'grid') {
        grid.style.display = 'none';
        list.style.display = 'flex';
        ico.className      = 'fa-solid fa-list';
        vueActuelle        = 'list';
    } else {
        grid.style.display = 'grid';
        list.style.display = 'none';
        ico.className      = 'fa-solid fa-grip';
        vueActuelle        = 'grid';
    }
    try { localStorage.setItem('tableVue', vueActuelle); } catch(e) {}
}

document.addEventListener('DOMContentLoaded', () => {
    if (vueActuelle === 'list') toggleVue();
});

// ════════════════════════════════════════════════════════════
// RAFRAÎCHIR LE PLAN (AJAX)
// ════════════════════════════════════════════════════════════

function rafraichirPlan() {
    const ico = document.getElementById('icoRefresh');
    ico.classList.add('spin');

    fetch('{{ route("admin.tables.statut") }}', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        }
    })
    .then(response => {
        return response.json();
    })
    .then(data => {
        if (!data.success) throw new Error('Erreur serveur');

        // Mettre à jour les compteurs
        const cntL = document.getElementById('cnt-libres');
        const cntO = document.getElementById('cnt-occupees');
        if (cntL) cntL.textContent = data.tables_libres;
        if (cntO) cntO.textContent = data.tables_occupees;

        Swal.fire({
            toast: true, position: 'bottom-end',
            icon: 'success',
            title: `Plan mis à jour — ${data.timestamp}`,
            timer: 1800, showConfirmButton: false,
            background: '#141414', color: '#e5e5e5', iconColor: '#22c55e',
        });
    })
    .catch((err) => {
        console.error('AJAX Error :', err.message);
        Swal.fire({
            toast: true, position: 'bottom-end',
            icon: 'info', title: 'Rechargement de la page...',
            timer: 1000, showConfirmButton: false,
            background: '#141414', color: '#e5e5e5',
        });
        setTimeout(() => location.reload(), 1100);
    })
    .finally(() => ico.classList.remove('spin'));
}

// Rafraîchir automatiquement toutes les 30 secondes
setInterval(() => rafraichirPlan(), 30000);


// ════════════════════════════════════════════════════════════
// MODALS
// ════════════════════════════════════════════════════════════

function ouvrirModalCreate() {
    document.getElementById('createForm').reset();
    document.getElementById('inputMultiple').value = '0';
    document.getElementById('blockMultiple').style.display = 'none';
    document.getElementById('checkMultiple').innerHTML = '';
    document.getElementById('modalCreate').style.display = 'flex';
    document.body.style.overflow = 'hidden';
    setTimeout(() => document.getElementById('c_intitule').focus(), 100);
}

function ouvrirModalEdit(id, intitule, description) {
    const form = document.getElementById('editForm');
    form.action = `/admin/tables/${id}`;
    document.getElementById('e_intitule').value    = intitule;
    document.getElementById('e_description').value = description;
    document.getElementById('editTitle').textContent = `Modifier : ${intitule}`;
    document.getElementById('modalEdit').style.display = 'flex';
    document.body.style.overflow = 'hidden';
    setTimeout(() => document.getElementById('e_intitule').focus(), 100);
}

function fermerModal(id) {
    document.getElementById(id).style.display = 'none';
    document.body.style.overflow = '';
}

function fermerModalSiOverlay(event, id) {
    if (event.target.id === id) fermerModal(id);
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        fermerModal('modalCreate');
        fermerModal('modalEdit');
    }
});

// ════════════════════════════════════════════════════════════
// CRÉATION MULTIPLE
// ════════════════════════════════════════════════════════════

let multipleActif = false;

function toggleMultiple() {
    multipleActif = !multipleActif;
    const check = document.getElementById('checkMultiple');
    const block = document.getElementById('blockMultiple');
    const input = document.getElementById('inputMultiple');

    check.innerHTML    = multipleActif
        ? '<i class="fa-solid fa-check" style="color:#ea580c;font-size:10px;"></i>'
        : '';
    block.style.display = multipleActif ? 'block' : 'none';
    input.value         = multipleActif ? '1' : '0';

    if (multipleActif) majPreviewMultiple();
}

function majPreviewMultiple() {
    const prefixe = document.getElementById('c_prefixe').value || 'Table';
    const debut   = parseInt(document.getElementById('c_debut').value) || 1;
    const nombre  = parseInt(document.getElementById('c_nombre').value) || 5;
    const max     = Math.min(nombre, 4);
    let noms = [];
    for (let i = debut; i < debut + max; i++) {
        noms.push(`${prefixe} ${String(i).padStart(2,'0')}`);
    }
    if (nombre > 4) noms.push('...');
    document.getElementById('previewMultiple').textContent =
        `Prévisualisation : ${noms.join(', ')}`;
}

['c_prefixe','c_debut','c_nombre'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('input', majPreviewMultiple);
});

// ════════════════════════════════════════════════════════════
// VALIDATIONS FORMULAIRES
// ════════════════════════════════════════════════════════════

function validerChamp(inputId, errId) {
    const input = document.getElementById(inputId);
    const err   = document.getElementById(errId);
    const txt   = document.getElementById(errId + '_txt');
    const val   = input.value.trim();

    input.classList.remove('is-error');

    if (!val || val.length < 2) {
        input.classList.add('is-error');
        if (txt) txt.textContent = !val
            ? 'Ce champ est obligatoire.'
            : 'Minimum 2 caractères.';
        err.classList.add('show');
        return false;
    }

    err.classList.remove('show');
    return true;
}

function validerFormCreate() {
    const ok = validerChamp('c_intitule','err_c_intitule');
    if (!ok) return false;

    const btn = document.getElementById('btnCreate');
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Enregistrement...';
    btn.disabled  = true;
    return true;
}

function validerFormEdit() {
    return validerChamp('e_intitule','err_e_intitule');
}

// ════════════════════════════════════════════════════════════
// LIBÉRER UNE TABLE (Admin)
// ════════════════════════════════════════════════════════════

function libererTable(id, nom, nbCommandes) {
    Swal.fire({
        title: `Libérer "${nom}" ?`,
        html: `<div style="color:#666;font-size:13px;">
                   ${nbCommandes > 0
                       ? `<strong style="color:#f97316;">${nbCommandes} commande${nbCommandes > 1 ? 's' : ''}</strong> en cours sur cette table ${nbCommandes > 1 ? 'seront marquées' : 'sera marquée'} comme servie${nbCommandes > 1 ? 's' : ''}.`
                       : 'La commande en cours sera marquée comme servie.'}
                   <br><br>
                   <strong style="color:#f87171;">
                   Utilisez uniquement en cas de blocage.</strong>
               </div>`,
        icon: 'warning',
        iconColor: '#eab308',
        background: '#141414', color: '#e5e5e5',
        confirmButtonColor: '#eab308',
        confirmButtonText: '<i class="fa-solid fa-unlock" style="margin-right:6px"></i>Libérer',
        showCancelButton: true,
        cancelButtonText: 'Annuler',
        cancelButtonColor: '#1f1f1f',
    }).then(r => {
        if (r.isConfirmed) document.getElementById(`libererForm-${id}`).submit();
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
               </div>`,
        icon: 'warning',
        iconColor: '#ef4444',
        background: '#141414', color: '#e5e5e5',
        confirmButtonColor: '#ef4444',
        confirmButtonText: '<i class="fa-solid fa-trash" style="margin-right:6px"></i>Supprimer',
        showCancelButton: true,
        cancelButtonText: 'Annuler',
        cancelButtonColor: '#1f1f1f',
    }).then(r => {
        if (r.isConfirmed) document.getElementById(`deleteForm-${id}`).submit();
    });
}
</script>
@endpush