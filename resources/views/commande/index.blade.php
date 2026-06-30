@extends('layouts.app')

@section('title', 'Commandes')
@section('page-title', 'Gestion des Commandes')

@push('styles')
<style>
    /* ── Variables ── */
    :root {
        --cc-orange:  #ea580c;
        --cc-orange2: #f97316;
        --cc-dark:    #080808;
        --cc-dark2:   #0d0d0d;
        --cc-dark3:   #141414;
        --cc-border:  #1f1f1f;
    }

    /* ── Cartes commande ── */
    .cmd-card {
        background: #141414;
        border: 1px solid #1f1f1f;
        border-radius: 13px;
        padding: 1rem 1.25rem;
        transition: border-color .2s, transform .18s;
        cursor: pointer;
    }
    .cmd-card:hover {
        border-color: #2a2a2a;
        transform: translateY(-1px);
    }
    .cmd-card.selected {
        border-color: var(--cc-orange);
        background: rgba(234,88,12,.05);
    }

    /* ── Badges statut ── */
    .badge {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 3px 10px; border-radius: 20px;
        font-size: 11px; font-weight: 600;
    }
    .b-attente    { background:rgba(234,179,8,.12);  color:#eab308; }
    .b-prep       { background:rgba(59,130,246,.12);  color:#60a5fa; }
    .b-expediee   { background:rgba(234,88,12,.12);   color:#f97316; }
    .b-servie,
    .b-livree     { background:rgba(34,197,94,.12);   color:#22c55e; }
    .b-annulee    { background:rgba(239,68,68,.12);   color:#f87171; }

    /* ── Filtres ── */
    .filter-chip {
        padding: 6px 14px; border-radius: 20px;
        font-size: 11px; font-weight: 500;
        cursor: pointer; transition: all .18s;
        border: 1px solid #1f1f1f;
        background: #141414; color: #555;
        white-space: nowrap;
    }
    .filter-chip.active,
    .filter-chip:hover {
        background: var(--cc-orange);
        color: #fff; border-color: var(--cc-orange);
    }

    /* ── Compteurs statut ── */
    .stat-pill {
        display: flex; align-items: center; gap: 8px;
        padding: 8px 14px; border-radius: 10px;
        background: #141414; border: 1px solid #1f1f1f;
        font-size: 12px; font-weight: 500; color: #555;
        white-space: nowrap;
    }
    .stat-pill .num {
        font-size: 16px; font-weight: 700; color: #e5e5e5;
    }

    /* ── Input recherche ── */
    .search-input {
        background: #0d0d0d; border: 1px solid #1f1f1f;
        border-radius: 10px; padding: 9px 14px 9px 38px;
        color: #e5e5e5; font-size: 13px; outline: none;
        width: 100%; transition: border-color .18s;
        font-family: inherit;
    }
    .search-input::placeholder { color: #333; }
    .search-input:focus { border-color: var(--cc-orange); }

    /* ── Select filtre ── */
    .sel {
        background: #0d0d0d; border: 1px solid #1f1f1f;
        border-radius: 10px; padding: 8px 12px;
        color: #555; font-size: 12px; outline: none;
        cursor: pointer; transition: border-color .18s;
        font-family: inherit;
    }
    .sel:focus { border-color: var(--cc-orange); color: #e5e5e5; }

    /* ── Boutons action ── */
    .btn {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 8px 16px; border-radius: 10px;
        font-size: 12px; font-weight: 600; cursor: pointer;
        transition: all .18s; border: none;
        text-decoration: none; font-family: inherit;
    }
    .btn-primary  { background: var(--cc-orange); color: #fff; }
    .btn-primary:hover { background: #c2410c; }
    .btn-ghost {
        background: #141414; border: 1px solid #1f1f1f; color: #555;
    }
    .btn-ghost:hover { color: #ccc; border-color: #333; }
    .btn-danger-ghost {
        background: rgba(239,68,68,.08);
        border: 1px solid rgba(239,68,68,.2); color: #f87171;
    }
    .btn-danger-ghost:hover { background:#ef4444; color:#fff; }
    .btn-sm { padding: 5px 10px; font-size: 11px; border-radius: 7px; }

    /* ── Pagination ── */
    .page-link {
        display: inline-flex; align-items: center; justify-content: center;
        width: 32px; height: 32px; border-radius: 8px;
        font-size: 12px; font-weight: 500;
        background: #141414; border: 1px solid #1f1f1f; color: #555;
        text-decoration: none; transition: all .18s;
    }
    .page-link:hover,
    .page-link.active { background: var(--cc-orange); color:#fff; border-color: var(--cc-orange); }
    .page-link.disabled { opacity:.35; pointer-events: none; }

    /* ── Détail panel (slide depuis la droite) ── */
    #detail-panel {
        position: fixed; top: 0; right: -480px; width: 460px; height: 100vh;
        background: #0d0d0d; border-left: 1px solid #1f1f1f;
        z-index: 100; transition: right .3s cubic-bezier(.4,0,.2,1);
        display: flex; flex-direction: column; overflow: hidden;
    }
    #detail-panel.open { right: 0; }

    #panel-overlay {
        position: fixed; inset: 0; background: rgba(0,0,0,.5);
        z-index: 99; display: none; backdrop-filter: blur(2px);
    }
    #panel-overlay.open { display: block; }

    /* ── Timeline historique ── */
    .timeline-item {
        display: flex; gap: 12px; position: relative;
        padding-bottom: 14px;
    }
    .timeline-item:not(:last-child)::before {
        content: ''; position: absolute;
        left: 15px; top: 32px; bottom: 0;
        width: 1px; background: #1f1f1f;
    }
    .timeline-dot {
        width: 32px; height: 32px; border-radius: 50%;
        flex-shrink: 0; display: flex; align-items: center; justify-content: center;
        font-size: 13px;
    }

    /* ── Responsive ── */
    @media(max-width: 768px) {
        #detail-panel { width: 100%; right: -100%; }
    }
</style>
@endpush

@section('content')

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- BARRE STATISTIQUES                                        --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:18px;align-items:center;">

    {{-- Compteurs --}}
    @php
        $configStatuts = [
            'En attente'     => ['icone'=>'fa-clock',       'color'=>'#eab308'],
            'En préparation' => ['icone'=>'fa-fire-burner', 'color'=>'#60a5fa'],
            'Expédiée'       => ['icone'=>'fa-motorcycle',  'color'=>'#f97316'],
            'Servie'         => ['icone'=>'fa-utensils',    'color'=>'#22c55e'],
            'Livrée'         => ['icone'=>'fa-circle-check','color'=>'#22c55e'],
            'Annulée'        => ['icone'=>'fa-circle-xmark','color'=>'#f87171'],
        ];
    @endphp

    @foreach($configStatuts as $label => $cfg)
    @php $nb = $compteurs[$label] ?? 0; @endphp
    <div class="stat-pill">
        <i class="fa-solid {{ $cfg['icone'] }}" style="color:{{ $cfg['color'] }};font-size:13px;"></i>
        <span class="num" id="cnt-{{ Str::slug($label) }}">{{ $nb }}</span>
        <span>{{ $label }}</span>
    </div>
    @endforeach

    {{-- Bouton nouvelle commande --}}
    <div style="margin-left:auto;">
        <a href="{{ route('commandes.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i>
            Nouvelle commande
        </a>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- FILTRES                                                   --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<form method="GET" action="{{ route('commandes.index') }}" id="filterForm">

    <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:14px;align-items:center;">

        {{-- Recherche --}}
        <div style="position:relative;min-width:220px;flex:1;">
            <i class="fa-solid fa-magnifying-glass"
               style="position:absolute;left:12px;top:50%;transform:translateY(-50%);
                      color:#333;font-size:13px;pointer-events:none;"></i>
            <input type="text" name="q" value="{{ request('q') }}"
                   class="search-input"
                   placeholder="Référence, nom client..."
                   oninput="debounceSubmit()">
        </div>

        {{-- Filtre statut --}}
        <select name="statut" class="sel" onchange="this.form.submit()">
            <option value="">Tous les statuts</option>
            @foreach($statuts as $s)
            <option value="{{ $s->intitule }}"
                {{ request('statut') === $s->intitule ? 'selected' : '' }}>
                {{ $s->intitule }}
            </option>
            @endforeach
        </select>

        {{-- Filtre type --}}
        <select name="type" class="sel" onchange="this.form.submit()">
            <option value="">Tous les types</option>
            <option value="Standard"   {{ request('type')==='Standard'   ? 'selected':'' }}>Standard (Salle)</option>
            <option value="A emporter" {{ request('type')==='A emporter' ? 'selected':'' }}>À emporter</option>
            <option value="Livraison"  {{ request('type')==='Livraison'  ? 'selected':'' }}>Livraison</option>
        </select>

        {{-- Filtre date --}}
        <input type="date" name="date" value="{{ request('date', today()->format('Y-m-d')) }}"
               class="sel" onchange="this.form.submit()" style="color:#e5e5e5;">

        {{-- Filtre table --}}
        <select name="table" class="sel" onchange="this.form.submit()">
            <option value="">Toutes les tables</option>
            @foreach($tables as $t)
            <option value="{{ $t->idtable }}"
                {{ request('table') == $t->idtable ? 'selected' : '' }}>
                {{ $t->intitule }}
            </option>
            @endforeach
        </select>

        {{-- Reset --}}
        @if(request()->hasAny(['q','statut','type','date','table']))
        <a href="{{ route('commandes.index') }}" class="btn btn-ghost btn-sm">
            <i class="fa-solid fa-xmark"></i> Réinitialiser
        </a>
        @endif

    </div>

    {{-- Chips statut rapide --}}
    <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:18px;">
        @php
            $chips = ['Toutes', 'En attente', 'En préparation', 'Expédiée', 'Servie', 'Livrée', 'Annulée'];
            $statActif = request('statut', '');
        @endphp
        @foreach($chips as $chip)
        <button type="button"
                class="filter-chip {{ ($chip === 'Toutes' && !$statActif) || $chip === $statActif ? 'active' : '' }}"
                onclick="quickFilter('{{ $chip === 'Toutes' ? '' : $chip }}')">
            {{ $chip }}
        </button>
        @endforeach
    </div>

</form>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- LISTE DES COMMANDES                                       --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<div style="display:flex;flex-direction:column;gap:8px;" id="commandes-list">

    @forelse($commandes as $cmd)
    @php
        $slugStatut = match($cmd->statut_courant) {
            'En attente'     => 'attente',
            'En préparation' => 'prep',
            'Expédiée'       => 'expediee',
            'Servie'         => 'servie',
            'Livrée'         => 'livree',
            'Annulée'        => 'annulee',
            default          => 'attente'
        };
        $cfg = $configStatuts[$cmd->statut_courant] ?? ['icone'=>'fa-circle','color'=>'#555'];

        $typeIcone = match($cmd->typecommande) {
            'Livraison'  => 'fa-motorcycle',
            'A emporter' => 'fa-bag-shopping',
            default      => 'fa-chair',
        };
        $typeCouleur = match($cmd->typecommande) {
            'Livraison'  => '#f97316',
            'A emporter' => '#22c55e',
            default      => '#60a5fa',
        };
    @endphp

    <div class="cmd-card" onclick="ouvrirDetail({{ $cmd->idcommande }})">
        <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">

            {{-- Icône type --}}
            <div style="width:42px;height:42px;border-radius:11px;flex-shrink:0;
                        background:#1a1a1a;border:1px solid #252525;
                        display:flex;align-items:center;justify-content:center;">
                <i class="fa-solid {{ $typeIcone }}"
                   style="font-size:15px;color:{{ $typeCouleur }};"></i>
            </div>

            {{-- Infos principales --}}
            <div style="flex:1;min-width:0;">
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                    <span style="font-size:13px;font-weight:700;color:#e5e5e5;">
                        {{ $cmd->reference }}
                    </span>
                    @if($cmd->table)
                    <span style="font-size:10px;padding:2px 8px;border-radius:5px;
                                 background:#1a1a1a;color:#555;">
                        {{ $cmd->table->intitule }}
                    </span>
                    @endif
                    <span style="font-size:10px;color:#333;">{{ $cmd->typecommande === 'A emporter' ? 'À emporter' : $cmd->typecommande }}</span>
                </div>
                <div style="display:flex;align-items:center;gap:12px;margin-top:4px;flex-wrap:wrap;">
                    <span style="font-size:11px;color:#444;">
                        <i class="fa-regular fa-clock" style="margin-right:3px;"></i>
                        {{ $cmd->heurecommande }}
                    </span>
                    <span style="font-size:11px;color:#444;">
                        <i class="fa-solid fa-list" style="margin-right:3px;"></i>
                        {{ $cmd->lignes->count() }} article(s)
                    </span>
                    @if($cmd->client)
                    <span style="font-size:11px;color:#444;">
                        <i class="fa-solid fa-user" style="margin-right:3px;"></i>
                        {{ $cmd->client->prenom }} {{ $cmd->client->nom }}
                    </span>
                    @endif
                    @if($cmd->consignes)
                    <span style="font-size:11px;color:#555;" title="{{ $cmd->consignes }}">
                        <i class="fa-solid fa-note-sticky" style="color:#eab308;margin-right:2px;"></i>
                        Note
                    </span>
                    @endif
                </div>
            </div>

            {{-- Montant --}}
            <div style="text-align:right;flex-shrink:0;">
                <div style="font-size:14px;font-weight:700;color:#fff;margin-bottom:4px;">
                    {{ number_format($cmd->montant, 0, ',', ' ') }} FCFA
                </div>
                <span class="badge b-{{ $slugStatut }}">
                    <i class="fa-solid {{ $cfg['icone'] }}" style="font-size:10px;"></i>
                    {{ $cmd->statut_courant }}
                </span>
            </div>

            {{-- Actions rapides --}}
            <div style="display:flex;gap:6px;flex-shrink:0;" onclick="event.stopPropagation()">

                {{-- Voir détail --}}
                <a href="{{ route('commandes.show', $cmd->idcommande) }}"
                   class="btn btn-ghost btn-sm" title="Voir détail">
                    <i class="fa-solid fa-eye" style="font-size:12px;"></i>
                </a>

                {{-- Changer statut rapidement --}}
                @if($cmd->estModifiable())
                @php
                    $prochainStatut = match(true) {
                        $cmd->statut_courant === 'En attente'     => 'En préparation',
                        $cmd->statut_courant === 'En préparation' && $cmd->typecommande === 'Standard'   => 'Servie',
                        $cmd->statut_courant === 'En préparation' && $cmd->typecommande === 'A emporter'  => 'Servie',
                        $cmd->statut_courant === 'En préparation' && $cmd->typecommande === 'Livraison'  => 'Expédiée',
                        $cmd->statut_courant === 'Expédiée'       => 'Livrée',
                        default => null
                    };
                @endphp
                @if($prochainStatut)
                <button onclick="changerStatutRapide({{ $cmd->idcommande }}, '{{ $prochainStatut }}', '{{ $cmd->reference }}')"
                        class="btn btn-sm"
                        style="background:rgba(34,197,94,.12);border:1px solid rgba(34,197,94,.2);color:#22c55e;"
                        title="Passer à : {{ $prochainStatut }}">
                    <i class="fa-solid fa-arrow-right" style="font-size:11px;"></i>
                    <span style="font-size:10px;">{{ $prochainStatut }}</span>
                </button>
                @endif
                @endif

                {{-- Annuler (Admin/Caissier) --}}
                @if(in_array(auth()->user()->role, ['Administrateur','Caissier']) && $cmd->estAnnulable())
                <button onclick="annulerCommande({{ $cmd->idcommande }}, '{{ $cmd->reference }}')"
                        class="btn btn-danger-ghost btn-sm" title="Annuler la commande">
                    <i class="fa-solid fa-xmark" style="font-size:11px;"></i>
                </button>
                @endif

                {{-- Reçu PDF (Caissier/Admin - commandes terminées) --}}
                @if(in_array(auth()->user()->role, ['Administrateur','Caissier']) && in_array($cmd->statut_courant, ['Servie','Livrée']))
                <a href="{{ route('caisse.recu', $cmd->idcommande) }}"
                   class="btn btn-ghost btn-sm" title="Générer le reçu PDF" target="_blank">
                    <i class="fa-solid fa-file-pdf" style="font-size:11px;color:#f97316;"></i>
                </a>
                @endif
            </div>

        </div>
    </div>

    @empty
    {{-- État vide --}}
    <div style="text-align:center;padding:60px 20px;background:#141414;
                border:1px dashed #1f1f1f;border-radius:13px;">
        <i class="fa-solid fa-receipt" style="font-size:40px;color:#1f1f1f;display:block;margin-bottom:14px;"></i>
        <p style="font-size:15px;font-weight:600;color:#333;margin-bottom:6px;">Aucune commande trouvée</p>
        <p style="font-size:12px;color:#252525;margin-bottom:20px;">
            @if(request()->hasAny(['q','statut','type','table']))
                Aucun résultat pour les filtres sélectionnés.
            @else
                Aucune commande pour cette journée.
            @endif
        </p>
        <a href="{{ route('commandes.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i>
            Créer une commande
        </a>
    </div>
    @endforelse
</div>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- PAGINATION                                                --}}
{{-- ══════════════════════════════════════════════════════════ --}}
@if($commandes->hasPages())
<div style="display:flex;align-items:center;justify-content:space-between;
            margin-top:20px;flex-wrap:wrap;gap:10px;">

    <div style="font-size:12px;color:#444;">
        {{ $commandes->firstItem() }}–{{ $commandes->lastItem() }}
        sur {{ $commandes->total() }} commande(s)
    </div>

    <div style="display:flex;align-items:center;gap:4px;">
        {{-- Précédent --}}
        @if($commandes->onFirstPage())
        <span class="page-link disabled"><i class="fa-solid fa-chevron-left" style="font-size:10px;"></i></span>
        @else
        <a href="{{ $commandes->previousPageUrl() }}" class="page-link">
            <i class="fa-solid fa-chevron-left" style="font-size:10px;"></i>
        </a>
        @endif

        {{-- Pages --}}
        @foreach($commandes->getUrlRange(
            max(1, $commandes->currentPage() - 2),
            min($commandes->lastPage(), $commandes->currentPage() + 2)
        ) as $page => $url)
        <a href="{{ $url }}" class="page-link {{ $page === $commandes->currentPage() ? 'active' : '' }}">
            {{ $page }}
        </a>
        @endforeach

        {{-- Suivant --}}
        @if($commandes->hasMorePages())
        <a href="{{ $commandes->nextPageUrl() }}" class="page-link">
            <i class="fa-solid fa-chevron-right" style="font-size:10px;"></i>
        </a>
        @else
        <span class="page-link disabled"><i class="fa-solid fa-chevron-right" style="font-size:10px;"></i></span>
        @endif
    </div>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- PANNEAU DÉTAIL (slide depuis la droite)                   --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<div id="panel-overlay" onclick="fermerDetail()"></div>

<div id="detail-panel">

    {{-- Header du panel --}}
    <div style="display:flex;align-items:center;justify-content:space-between;
                padding:16px 20px;border-bottom:1px solid #1a1a1a;flex-shrink:0;">
        <div>
            <div style="font-size:14px;font-weight:700;color:#e5e5e5;" id="panel-ref">—</div>
            <div style="font-size:11px;color:#444;margin-top:2px;" id="panel-type">—</div>
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
            <a id="panel-link" href="#"
               style="font-size:11px;color:#f97316;text-decoration:none;
                      display:flex;align-items:center;gap:4px;">
                <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:10px;"></i>
                Détail complet
            </a>
            <button onclick="fermerDetail()"
                    style="width:28px;height:28px;border-radius:7px;border:1px solid #1f1f1f;
                           background:#141414;color:#555;cursor:pointer;font-size:12px;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    </div>

    {{-- Contenu du panel --}}
    <div style="flex:1;overflow-y:auto;padding:16px 20px;" id="panel-content">
        <div style="text-align:center;padding:40px;color:#333;">
            <i class="fa-solid fa-spinner fa-spin" style="font-size:24px;"></i>
            <p style="font-size:12px;margin-top:10px;">Chargement...</p>
        </div>
    </div>

    {{-- Footer du panel --}}
    <div style="padding:12px 20px;border-top:1px solid #1a1a1a;flex-shrink:0;" id="panel-footer">
    </div>
</div>

@endsection

@push('scripts')
<script>
// ── Variables globales ──────────────────────────────────────
let debounceTimer   = null;
let cmdIdCourant    = null;
const csrfToken     = document.querySelector('meta[name="csrf-token"]').content;

// ── Debounce pour la recherche ───────────────────────────────
function debounceSubmit() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        document.getElementById('filterForm').submit();
    }, 500);
}

// ── Filtre rapide par chip de statut ────────────────────────
function quickFilter(statut) {
    const form    = document.getElementById('filterForm');
    const select  = form.querySelector('select[name="statut"]');
    if (select) select.value = statut;
    form.submit();
}

// ── Ouvrir le panneau détail ─────────────────────────────────
function ouvrirDetail(id) {
    cmdIdCourant = id;

    // Afficher le panel
    document.getElementById('detail-panel').classList.add('open');
    document.getElementById('panel-overlay').classList.add('open');
    document.body.style.overflow = 'hidden';

    // Réinitialiser le contenu
    document.getElementById('panel-ref').textContent  = 'Chargement...';
    document.getElementById('panel-type').textContent = '';
    document.getElementById('panel-footer').innerHTML = '';
    document.getElementById('panel-content').innerHTML = `
        <div style="text-align:center;padding:40px;color:#333;">
            <i class="fa-solid fa-spinner fa-spin" style="font-size:24px;"></i>
            <p style="font-size:12px;margin-top:10px;">Chargement...</p>
        </div>
    `;

    // Charger les données via AJAX
    fetch(`/commandes/${id}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept':           'application/json',
            'X-CSRF-TOKEN':     csrfToken,
        }
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) throw new Error(data.message || 'Erreur inconnue');
        renderPanel(data.commande);
    })
    .catch(err => {
        document.getElementById('panel-content').innerHTML = `
            <div style="text-align:center;padding:30px;color:#f87171;">
                <i class="fa-solid fa-circle-exclamation" style="font-size:24px;display:block;margin-bottom:8px;"></i>
                <p style="font-size:12px;">${err.message}</p>
            </div>
        `;
    });
}

// ── Fermer le panneau détail ─────────────────────────────────
function fermerDetail() {
    document.getElementById('detail-panel').classList.remove('open');
    document.getElementById('panel-overlay').classList.remove('open');
    document.body.style.overflow = '';
    cmdIdCourant = null;
}

// Fermer avec Echap
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') fermerDetail();
});

// ── Rendre le contenu du panneau ─────────────────────────────
function renderPanel(cmd) {
    const slugStatut = {
        'En attente':     'attente',
        'En préparation': 'prep',
        'Expédiée':       'expediee',
        'Servie':         'servie',
        'Livrée':         'livree',
        'Annulée':        'annulee',
    }[cmd.statut_courant] || 'attente';

    const iconeStatut = {
        'En attente':     'fa-clock',
        'En préparation': 'fa-fire-burner',
        'Expédiée':       'fa-motorcycle',
        'Servie':         'fa-utensils',
        'Livrée':         'fa-circle-check',
        'Annulée':        'fa-circle-xmark',
    }[cmd.statut_courant] || 'fa-circle';

    // Header
    document.getElementById('panel-ref').textContent  = cmd.reference;
    document.getElementById('panel-type').textContent = (cmd.typecommande === 'A emporter' ? 'À emporter' : cmd.typecommande) + (cmd.table ? ' — ' + cmd.table.intitule : '');
    document.getElementById('panel-link').href        = `/commandes/${cmd.idcommande}`;

    // Lignes de commande
    let lignesHtml = '';
    if (cmd.lignes && cmd.lignes.length) {
        lignesHtml = cmd.lignes.map(l => `
            <div style="display:flex;justify-content:space-between;align-items:center;
                        padding:8px 0;border-bottom:1px solid #1a1a1a;">
                <div>
                    <div style="font-size:12px;color:#e5e5e5;">${l.menu?.intitule ?? 'N/A'}</div>
                    <div style="font-size:10px;color:#444;">×${l.quantite} × ${fmt(l.prix / l.quantite)} FCFA</div>
                </div>
                <span style="font-size:12px;font-weight:700;color:#fff;">${fmt(l.prix)} FCFA</span>
            </div>
        `).join('');
    } else {
        lignesHtml = '<p style="font-size:12px;color:#333;padding:8px 0;">Aucun article</p>';
    }

    // Historique
    let histHtml = '';
    if (cmd.historiques && cmd.historiques.length) {
        histHtml = cmd.historiques.map(h => {
            const slug2 = {
                'En attente':'attente','En préparation':'prep','Expédiée':'expediee',
                'Servie':'servie','Livrée':'livree','Annulée':'annulee'
            }[h.statut?.intitule] || 'attente';
            const couleurs = {
                attente:'#eab308', prep:'#60a5fa', expediee:'#f97316',
                servie:'#22c55e', livree:'#22c55e', annulee:'#f87171',
            };
            const icones = {
                attente:'fa-clock', prep:'fa-fire-burner', expediee:'fa-motorcycle',
                servie:'fa-utensils', livree:'fa-circle-check', annulee:'fa-circle-xmark',
            };
            const c = couleurs[slug2] || '#555';
            const ic = icones[slug2]  || 'fa-circle';
            return `
                <div class="timeline-item">
                    <div class="timeline-dot"
                         style="background:rgba(255,255,255,.05);border:1px solid #1f1f1f;">
                        <i class="fa-solid ${ic}" style="color:${c};font-size:12px;"></i>
                    </div>
                    <div style="flex:1;padding-top:4px;">
                        <div style="font-size:12px;font-weight:600;color:#e5e5e5;">
                            ${h.statut?.intitule ?? 'N/A'}
                        </div>
                        <div style="font-size:11px;color:#444;margin-top:1px;">
                            ${h.description ?? ''}
                        </div>
                        <div style="font-size:10px;color:#333;margin-top:2px;">
                            ${h.created_at ? new Date(h.created_at).toLocaleString('fr-FR') : ''}
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    } else {
        histHtml = '<p style="font-size:12px;color:#333;">Aucun historique</p>';
    }

    // Contenu complet du panel
    document.getElementById('panel-content').innerHTML = `

        {{-- Statut + montant --}}
        <div style="display:flex;justify-content:space-between;align-items:center;
                    margin-bottom:16px;padding:12px 14px;border-radius:10px;
                    background:#0d0d0d;border:1px solid #1a1a1a;">
            <span class="badge b-${slugStatut}">
                <i class="fa-solid ${iconeStatut}" style="font-size:10px;"></i>
                ${cmd.statut_courant}
            </span>
            <span style="font-size:18px;font-weight:700;color:#fff;">
                ${fmt(cmd.montant)} FCFA
            </span>
        </div>

        {{-- Infos --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:16px;">
            ${infoCell('fa-calendar', 'Date', cmd.datecommande ?? '—')}
            ${infoCell('fa-clock', 'Heure', cmd.heurecommande ?? '—')}
            ${infoCell('fa-credit-card', 'Paiement', cmd.mode_paiement ?? '—')}
            ${infoCell('fa-chair', 'Table', cmd.table?.intitule ?? (cmd.typecommande === 'Livraison' ? 'Livraison' : (cmd.typecommande === 'A emporter' ? 'À emporter' : '—')))}
            ${cmd.client ? infoCell('fa-user', 'Client', cmd.client.prenom + ' ' + cmd.client.nom) : ''}
            ${cmd.serveur ? infoCell('fa-user-tie', 'Serveur', cmd.serveur.prenom) : ''}
        </div>

        ${cmd.adresse ? `
        <div style="padding:8px 12px;border-radius:8px;background:#0d0d0d;
                    border:1px solid #1a1a1a;margin-bottom:14px;font-size:11px;color:#555;">
            <i class="fa-solid fa-location-dot" style="color:#f97316;margin-right:6px;"></i>
            ${cmd.adresse}
        </div>` : ''}

        ${cmd.consignes ? `
        <div style="padding:8px 12px;border-radius:8px;background:rgba(234,179,8,.06);
                    border:1px solid rgba(234,179,8,.15);margin-bottom:14px;font-size:11px;color:#eab308;">
            <i class="fa-solid fa-note-sticky" style="margin-right:6px;"></i>
            ${cmd.consignes}
        </div>` : ''}

        {{-- Articles --}}
        <div style="margin-bottom:16px;">
            <div style="font-size:11px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;
                        color:#444;margin-bottom:8px;">
                Articles (${cmd.lignes?.length ?? 0})
            </div>
            ${lignesHtml}
        </div>

        {{-- Historique --}}
        <div>
            <div style="font-size:11px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;
                        color:#444;margin-bottom:10px;">
                Historique
            </div>
            ${histHtml}
        </div>
    `;

    // Footer actions
    renderPanelFooter(cmd);
}

// ── Cellule info ─────────────────────────────────────────────
function infoCell(icone, label, valeur) {
    return `
        <div style="background:#0d0d0d;border:1px solid #1a1a1a;
                    border-radius:8px;padding:8px 10px;">
            <div style="font-size:10px;color:#444;margin-bottom:2px;">
                <i class="fa-solid ${icone}" style="margin-right:4px;font-size:10px;"></i>
                ${label}
            </div>
            <div style="font-size:12px;font-weight:600;color:#e5e5e5;">${valeur}</div>
        </div>
    `;
}

// ── Footer du panel avec boutons d'action ────────────────────
function renderPanelFooter(cmd) {
    const modifiable = ['En attente', 'En préparation'].includes(cmd.statut_courant);
    const annulable  = !['Livrée', 'Servie', 'Annulée'].includes(cmd.statut_courant);

    const prochains = {
        'Standard':   { 'En attente':'En préparation', 'En préparation':'Servie' },
        'A emporter': { 'En attente':'En préparation', 'En préparation':'Servie' },
        'Livraison':  { 'En attente':'En préparation', 'En préparation':'Expédiée', 'Expédiée':'Livrée' },
    };
    const prochain = prochains[cmd.typecommande]?.[cmd.statut_courant] ?? null;

    let btns = `
        <a href="/commandes/${cmd.idcommande}"
           style="flex:1;text-align:center;padding:9px;border-radius:9px;
                  border:1px solid #1f1f1f;background:#141414;color:#555;
                  font-size:12px;font-weight:600;text-decoration:none;display:flex;
                  align-items:center;justify-content:center;gap:6px;transition:all .18s;"
           onmouseover="this.style.color='#ccc'" onmouseout="this.style.color='#555'">
            <i class="fa-solid fa-eye" style="font-size:12px;"></i> Détail
        </a>
    `;

    if (prochain) {
        btns += `
            <button onclick="changerStatutRapide(${cmd.idcommande}, '${prochain}', '${cmd.reference}')"
                    style="flex:1;padding:9px;border-radius:9px;background:rgba(34,197,94,.12);
                           border:1px solid rgba(34,197,94,.2);color:#22c55e;font-size:12px;
                           font-weight:600;cursor:pointer;display:flex;align-items:center;
                           justify-content:center;gap:6px;transition:all .18s;"
                    onmouseover="this.style.background='#22c55e';this.style.color='#fff'"
                    onmouseout="this.style.background='rgba(34,197,94,.12)';this.style.color='#22c55e'">
                <i class="fa-solid fa-arrow-right" style="font-size:11px;"></i>
                ${prochain}
            </button>
        `;
    }

    document.getElementById('panel-footer').innerHTML = `
        <div style="display:flex;gap:7px;">${btns}</div>
    `;
}

// ── Changer statut rapide ────────────────────────────────────
function changerStatutRapide(id, statut, ref) {
    Swal.fire({
        title: `Passer à : ${statut} ?`,
        html: `<div style="color:#666;font-size:13px;">Commande ${ref}</div>`,
        icon: 'question',
        iconColor: '#ea580c',
        background: '#141414',
        color: '#e5e5e5',
        confirmButtonColor: '#ea580c',
        confirmButtonText: 'Confirmer',
        showCancelButton: true,
        cancelButtonText: 'Annuler',
        cancelButtonColor: '#1f1f1f',
    }).then(r => {
        if (!r.isConfirmed) return;

        fetch(`/commandes/${id}/statut`, {
            method: 'PATCH',
            headers: {
                'Content-Type':     'application/json',
                'Accept':           'application/json',
                'X-CSRF-TOKEN':     csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ statut })
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) throw new Error(data.message);

            Swal.fire({
                toast: true, position: 'bottom-end',
                icon: 'success', title: `Statut mis à jour : ${statut}`,
                timer: 2000, showConfirmButton: false,
                background: '#141414', color: '#e5e5e5', iconColor: '#22c55e',
            });

            // Recharger la page pour mettre à jour la liste
            setTimeout(() => location.reload(), 1200);
        })
        .catch(err => {
            Swal.fire({
                toast: true, position: 'bottom-end',
                icon: 'error', title: err.message || 'Erreur serveur',
                timer: 3000, showConfirmButton: false,
                background: '#141414', color: '#e5e5e5',
            });
        });
    });
}

// ── Annuler une commande ─────────────────────────────────────
function annulerCommande(id, ref) {
    Swal.fire({
        title: 'Annuler la commande ?',
        html: `
            <div style="color:#666;font-size:13px;margin-bottom:12px;">
                Commande ${ref} — Cette action est irréversible.
            </div>
            <textarea id="justification" placeholder="Justification obligatoire..."
                      style="width:100%;padding:10px;border-radius:8px;border:1px solid #2a2a2a;
                             background:#0d0d0d;color:#e5e5e5;font-size:12px;resize:none;
                             outline:none;min-height:80px;font-family:inherit;"
                      rows="3"></textarea>
        `,
        icon: 'warning',
        iconColor: '#ef4444',
        background: '#141414',
        color: '#e5e5e5',
        confirmButtonColor: '#ef4444',
        confirmButtonText: '<i class="fa-solid fa-xmark" style="margin-right:6px"></i>Annuler la commande',
        showCancelButton: true,
        cancelButtonText: 'Retour',
        cancelButtonColor: '#1f1f1f',
        preConfirm: () => {
            const j = document.getElementById('justification').value.trim();
            if (j.length < 5) {
                Swal.showValidationMessage('La justification doit contenir au moins 5 caractères.');
                return false;
            }
            return j;
        }
    }).then(r => {
        if (!r.isConfirmed) return;

        fetch(`/commandes/${id}/statut`, {
            method: 'PATCH',
            headers: {
                'Content-Type':     'application/json',
                'Accept':           'application/json',
                'X-CSRF-TOKEN':     csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ statut: 'Annulée', description: r.value })
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) throw new Error(data.message);
            Swal.fire({
                toast: true, position: 'bottom-end',
                icon: 'success', title: `Commande ${ref} annulée`,
                timer: 2000, showConfirmButton: false,
                background: '#141414', color: '#e5e5e5', iconColor: '#22c55e',
            });
            setTimeout(() => location.reload(), 1200);
        })
        .catch(err => {
            Swal.fire({
                toast: true, position: 'bottom-end',
                icon: 'error', title: err.message || 'Erreur serveur',
                timer: 3000, showConfirmButton: false,
                background: '#141414', color: '#e5e5e5',
            });
        });
    });
}

// ── Formater les montants ────────────────────────────────────
function fmt(n) {
    return new Intl.NumberFormat('fr-FR').format(Math.round(n ?? 0));
}

// ── Refresh automatique des compteurs ────────────────────────
function refreshCompteurs() {
    fetch('{{ route("dashboard.refresh") }}', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
        }
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) return;
        const d = data.data;
        const m = {
            'en-attente':     d.commandes_en_attente,
            'en-preparation': d.commandes_en_preparation,
        };
        Object.entries(m).forEach(([key, val]) => {
            const el = document.getElementById('cnt-' + key);
            if (el && val !== undefined) el.textContent = val;
        });
    })
    .catch(() => {});
}

// Refresh toutes les 30 secondes
setInterval(refreshCompteurs, 30000);
</script>
@endpush