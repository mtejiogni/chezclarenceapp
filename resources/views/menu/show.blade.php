@extends('layouts.app')

@section('title', 'Plat : ' . $menu->intitule)
@section('page-title', 'Détail du plat')

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

    .card-header-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        font-weight: 700;
        color: #e5e5e5;
    }

    .card-body { padding: 20px; }

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

    .btn-success {
        background: rgba(34,197,94,.1);
        border: 1px solid rgba(34,197,94,.2);
        color: #22c55e;
    }
    .btn-success:hover { background: #22c55e; color: #fff; }

    .btn-sm { padding: 5px 10px; font-size: 11px; border-radius: 7px; }

    /* ── KPI ── */
    .kpi {
        background: var(--cc-dark2);
        border: 1px solid #1a1a1a;
        border-radius: 11px;
        padding: 14px;
        text-align: center;
    }

    .kpi-val {
        font-size: 22px;
        font-weight: 700;
        line-height: 1.2;
    }

    .kpi-label {
        font-size: 10px;
        color: #444;
        text-transform: uppercase;
        letter-spacing: .5px;
        margin-top: 4px;
    }

    /* ── Info cell ── */
    .info-cell {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 9px 0;
        border-bottom: 1px solid #1a1a1a;
        font-size: 12px;
    }

    .info-cell:last-child { border-bottom: none; }
    .info-cell-label { color: #444; }
    .info-cell-val   { color: #e5e5e5; font-weight: 600; }

    /* ── Graphique ventes ── */
    .chart-wrap {
        position: relative;
        height: 180px;
    }

    /* ── Toggle switch ── */
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

    /* ── Commandes récentes ── */
    .cmd-row {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 9px 0;
        border-bottom: 1px solid #1a1a1a;
    }

    .cmd-row:last-child { border-bottom: none; }
</style>
@endpush

@section('content')

{{-- Fil d'Ariane --}}
<div style="display:flex;align-items:center;gap:8px;
            font-size:12px;color:#444;margin-bottom:18px;">
    <a href="{{ route('admin.menus.index') }}"
       style="color:#555;text-decoration:none;
              display:flex;align-items:center;gap:5px;"
       onmouseover="this.style.color='#f97316'"
       onmouseout="this.style.color='#555'">
        <i class="fa-solid fa-book-open"></i>
        Menus & Plats
    </a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;color:#333;"></i>
    <span style="color:#666;">{{ $menu->intitule }}</span>
</div>

{{-- ══════════════════════════════════════════════════════════
     EN-TÊTE
══════════════════════════════════════════════════════════ --}}
<div style="display:flex;align-items:flex-start;justify-content:space-between;
            flex-wrap:wrap;gap:12px;margin-bottom:20px;">

    {{-- Nom + badges --}}
    <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">

        {{-- Photo miniature --}}
        <div style="width:56px;height:56px;border-radius:13px;overflow:hidden;
                    flex-shrink:0;border:1px solid #1f1f1f;background:#1a1a1a;">
            @if($menu->photo)
            <img src="{{ asset('storage/' . $menu->photo) }}"
                 alt="{{ $menu->intitule }}"
                 style="width:100%;height:100%;object-fit:cover;">
            @else
            <div style="width:100%;height:100%;display:flex;
                        align-items:center;justify-content:center;">
                <i class="fa-solid fa-utensils" style="color:#2a2a2a;font-size:20px;"></i>
            </div>
            @endif
        </div>

        <div>
            <h1 style="font-size:20px;font-weight:700;color:#fff;margin:0 0 6px;">
                {{ $menu->intitule }}
            </h1>
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                <span class="badge {{ $menu->statut === 'Activé' ? 'badge-active' : 'badge-inactive' }}">
                    <i class="fa-solid {{ $menu->statut === 'Activé' ? 'fa-circle-check' : 'fa-circle-xmark' }}"
                       style="font-size:9px;"></i>
                    {{ $menu->statut }}
                </span>
                @if($menu->categorie)
                <span class="badge badge-cat">
                    <i class="fa-solid fa-layer-group" style="font-size:9px;"></i>
                    {{ $menu->categorie->intitule }}
                </span>
                @endif
                <span style="font-size:16px;font-weight:700;color:#f97316;">
                    {{ number_format($menu->pu, 0, ',', ' ') }} FCFA
                </span>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">

        {{-- Toggle statut --}}
        <button onclick="toggleStatut()"
                class="btn {{ $menu->statut === 'Activé' ? 'btn-ghost' : 'btn-success' }} btn-sm">
            <i class="fa-solid {{ $menu->statut === 'Activé' ? 'fa-toggle-off' : 'fa-toggle-on' }}"></i>
            {{ $menu->statut === 'Activé' ? 'Désactiver' : 'Activer' }}
        </button>

        {{-- Modifier --}}
        <a href="{{ route('admin.menus.edit', $menu->idmenu) }}"
           class="btn btn-ghost btn-sm">
            <i class="fa-solid fa-pen-to-square"></i>
            Modifier
        </a>

        {{-- Supprimer --}}
        <button onclick="confirmerSuppression()" class="btn btn-danger btn-sm">
            <i class="fa-solid fa-trash"></i>
            Supprimer
        </button>

        {{-- Retour --}}
        <a href="{{ route('admin.menus.index') }}" class="btn btn-ghost btn-sm">
            <i class="fa-solid fa-arrow-left"></i>
            Retour
        </a>
    </div>
</div>

{{-- Formulaires cachés --}}
<form method="POST"
      action="{{ route('admin.menus.toggle-statut', $menu->idmenu) }}"
      id="toggleForm" style="display:none;">
    @csrf @method('PATCH')
</form>

<form method="POST"
      action="{{ route('admin.menus.destroy', $menu->idmenu) }}"
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

        {{-- ── KPIs ventes ── --}}
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">

            <div class="kpi">
                <div class="kpi-val" style="color:#f97316;">
                    {{ number_format($totalVendu, 0, ',', ' ') }}
                </div>
                <div class="kpi-label">
                    <i class="fa-solid fa-basket-shopping" style="margin-right:3px;"></i>
                    Total vendu
                </div>
            </div>

            <div class="kpi">
                <div class="kpi-val" style="color:#22c55e;">
                    {{ number_format($caTotal, 0, ',', ' ') }}
                </div>
                <div class="kpi-label">
                    <i class="fa-solid fa-money-bill-wave" style="margin-right:3px;"></i>
                    CA généré (FCFA)
                </div>
            </div>

            <div class="kpi">
                <div class="kpi-val" style="color:#60a5fa;">
                    {{ $ventesMois->sum('total') }}
                </div>
                <div class="kpi-label">
                    <i class="fa-solid fa-calendar" style="margin-right:3px;"></i>
                    Vendus ce mois
                </div>
            </div>
        </div>

        {{-- ── Graphique ventes 30 jours ── --}}
        <div class="card">
            <div class="card-header">
                <div class="card-header-title">
                    <i class="fa-solid fa-chart-area" style="color:var(--cc-orange);"></i>
                    Ventes — 30 derniers jours
                </div>
                <span style="font-size:10px;color:#444;padding:3px 8px;
                             border-radius:6px;background:#1a1a1a;">
                    unités vendues
                </span>
            </div>
            <div class="card-body">
                @if($ventesMois->isNotEmpty())
                <div class="chart-wrap">
                    <canvas id="chartVentes"></canvas>
                </div>
                @else
                <div style="text-align:center;padding:32px;color:#2a2a2a;">
                    <i class="fa-solid fa-chart-area"
                       style="font-size:32px;display:block;margin-bottom:8px;"></i>
                    <p style="font-size:13px;">Aucune vente sur les 30 derniers jours</p>
                </div>
                @endif
            </div>
        </div>

        {{-- ── Commandes récentes incluant ce plat ── --}}
        <div class="card">
            <div class="card-header">
                <div class="card-header-title">
                    <i class="fa-solid fa-receipt" style="color:var(--cc-orange);"></i>
                    Dernières commandes
                    <span style="font-size:11px;font-weight:400;color:#444;">
                        (incluant ce plat)
                    </span>
                </div>
            </div>
            <div class="card-body">

                @php
                    // Récupérer les 10 dernières commandes contenant ce plat
                    $dernieresCommandes = \App\Models\Commande::whereHas('lignes', function($q) use ($menu) {
                        $q->where('idmenu', $menu->idmenu);
                    })
                    ->whereNull('void')
                    ->with(['table', 'lignes' => function($q) use ($menu) {
                        $q->where('idmenu', $menu->idmenu);
                    }])
                    ->orderByDesc('datecommande')
                    ->orderByDesc('heurecommande')
                    ->take(10)
                    ->get();
                @endphp

                @if($dernieresCommandes->isEmpty())
                <div style="text-align:center;padding:28px;color:#2a2a2a;">
                    <i class="fa-solid fa-receipt"
                       style="font-size:28px;display:block;margin-bottom:8px;"></i>
                    <p style="font-size:13px;">Aucune commande pour ce plat</p>
                </div>
                @else
                @foreach($dernieresCommandes as $cmd)
                @php
                    $lignePlat = $cmd->lignes->first();
                    $slugStatut = match($cmd->statut_courant) {
                        'En attente'     => ['color'=>'#eab308','icone'=>'fa-clock'],
                        'En préparation' => ['color'=>'#60a5fa','icone'=>'fa-fire-burner'],
                        'Expédiée'       => ['color'=>'#f97316','icone'=>'fa-motorcycle'],
                        'Servie','Livrée'=> ['color'=>'#22c55e','icone'=>'fa-circle-check'],
                        'Annulée'        => ['color'=>'#f87171','icone'=>'fa-circle-xmark'],
                        default          => ['color'=>'#555',   'icone'=>'fa-circle'],
                    };
                @endphp
                <div class="cmd-row">
                    {{-- Icône type commande --}}
                    <div style="width:36px;height:36px;border-radius:9px;flex-shrink:0;
                                background:#1a1a1a;border:1px solid #252525;
                                display:flex;align-items:center;justify-content:center;">
                        <i class="fa-solid {{ $cmd->typecommande === 'Livraison' ? 'fa-motorcycle' : 'fa-chair' }}"
                           style="font-size:13px;
                                  color:{{ $cmd->typecommande === 'Livraison' ? '#f97316' : '#60a5fa' }};"></i>
                    </div>

                    {{-- Infos commande --}}
                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                            <span style="font-size:12px;font-weight:700;color:#e5e5e5;">
                                {{ $cmd->reference }}
                            </span>
                            @if($cmd->table)
                            <span style="font-size:10px;color:#444;background:#1a1a1a;
                                         padding:1px 6px;border-radius:5px;">
                                {{ $cmd->table->intitule }}
                            </span>
                            @endif
                        </div>
                        <div style="font-size:11px;color:#444;margin-top:1px;">
                            {{ $cmd->datecommande->format('d/m/Y') }}
                            à {{ $cmd->heurecommande }}
                            @if($lignePlat)
                            · <span style="color:#f97316;">
                                ×{{ $lignePlat->quantite }}
                            </span>
                            @endif
                        </div>
                    </div>

                    {{-- Statut --}}
                    <div style="flex-shrink:0;">
                        <span style="font-size:11px;font-weight:600;
                                     padding:3px 9px;border-radius:12px;
                                     background:{{ $slugStatut['color'] }}22;
                                     color:{{ $slugStatut['color'] }};">
                            <i class="fa-solid {{ $slugStatut['icone'] }}"
                               style="font-size:9px;margin-right:3px;"></i>
                            {{ $cmd->statut_courant }}
                        </span>
                    </div>

                    {{-- Lien vers commande --}}
                    <a href="{{ route('commandes.show', $cmd->idcommande) }}"
                       style="width:28px;height:28px;border-radius:7px;
                              background:#1a1a1a;border:1px solid #252525;
                              display:flex;align-items:center;justify-content:center;
                              color:#444;transition:all .18s;text-decoration:none;flex-shrink:0;"
                       onmouseover="this.style.color='#f97316';this.style.borderColor='rgba(234,88,12,.3)'"
                       onmouseout="this.style.color='#444';this.style.borderColor='#252525'">
                        <i class="fa-solid fa-eye" style="font-size:10px;"></i>
                    </a>
                </div>
                @endforeach
                @endif
            </div>
        </div>

    </div>

    {{-- ════════════════════════════
         COLONNE DROITE
    ════════════════════════════ --}}
    <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- ── Photo du plat ── --}}
        <div class="card">
            @if($menu->photo)
            <div style="height:200px;overflow:hidden;">
                <img src="{{ asset('storage/' . $menu->photo) }}"
                     alt="{{ $menu->intitule }}"
                     style="width:100%;height:100%;object-fit:cover;">
            </div>
            @else
            <div style="height:160px;background:#1a1a1a;
                        display:flex;flex-direction:column;
                        align-items:center;justify-content:center;gap:8px;">
                <i class="fa-solid fa-utensils" style="font-size:36px;color:#252525;"></i>
                <span style="font-size:11px;color:#2a2a2a;">Aucune photo</span>
            </div>
            @endif
            <div class="card-body" style="padding:14px 16px;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <div style="font-size:11px;color:#444;">
                        @if($menu->photo)
                            <i class="fa-solid fa-circle-check"
                               style="color:#22c55e;margin-right:4px;"></i>
                            Photo enregistrée
                        @else
                            <i class="fa-solid fa-circle-xmark"
                               style="color:#555;margin-right:4px;"></i>
                            Aucune photo
                        @endif
                    </div>
                    <a href="{{ route('admin.menus.edit', $menu->idmenu) }}"
                       class="btn btn-ghost btn-sm">
                        <i class="fa-solid fa-pen-to-square" style="font-size:10px;"></i>
                        {{ $menu->photo ? 'Changer' : 'Ajouter' }}
                    </a>
                </div>
            </div>
        </div>

        {{-- ── Informations du plat ── --}}
        <div class="card">
            <div class="card-header">
                <div class="card-header-title">
                    <i class="fa-solid fa-circle-info" style="color:var(--cc-orange);"></i>
                    Informations
                </div>
                <a href="{{ route('admin.menus.edit', $menu->idmenu) }}"
                   class="btn btn-ghost btn-sm">
                    <i class="fa-solid fa-pen-to-square" style="font-size:10px;"></i>
                    Modifier
                </a>
            </div>
            <div class="card-body">

                {{-- Statut avec toggle --}}
                <div class="info-cell" style="padding:10px 0;">
                    <span class="info-cell-label">Statut</span>
                    <div style="display:flex;align-items:center;gap:8px;cursor:pointer;"
                         onclick="toggleStatut()">
                        <div class="toggle {{ $menu->statut === 'Activé' ? 'on' : 'off' }}"></div>
                        <span style="font-size:12px;font-weight:600;
                                     color:{{ $menu->statut === 'Activé' ? '#22c55e' : '#f87171' }};">
                            {{ $menu->statut }}
                        </span>
                    </div>
                </div>

                <div class="info-cell">
                    <span class="info-cell-label">Catégorie</span>
                    @if($menu->categorie)
                    <a href="{{ route('admin.categories.show', $menu->categorie->idcategorie) }}"
                       style="font-size:12px;font-weight:600;color:#60a5fa;text-decoration:none;">
                        {{ $menu->categorie->intitule }}
                    </a>
                    @else
                    <span class="info-cell-val" style="color:#555;">—</span>
                    @endif
                </div>

                <div class="info-cell">
                    <span class="info-cell-label">Prix unitaire</span>
                    <span class="info-cell-val" style="color:#f97316;font-size:14px;">
                        {{ number_format($menu->pu, 0, ',', ' ') }} FCFA
                    </span>
                </div>

                @if($menu->description)
                <div style="padding:10px 0;border-bottom:1px solid #1a1a1a;">
                    <div style="font-size:10px;color:#444;text-transform:uppercase;
                                letter-spacing:.5px;margin-bottom:5px;">Description</div>
                    <div style="font-size:12px;color:#888;line-height:1.6;">
                        {{ $menu->description }}
                    </div>
                </div>
                @endif

                <div class="info-cell">
                    <span class="info-cell-label">Créé le</span>
                    <span class="info-cell-val" style="color:#555;">
                        {{ $menu->created_at->format('d/m/Y') }}
                    </span>
                </div>

                <div class="info-cell">
                    <span class="info-cell-label">Modifié le</span>
                    <span class="info-cell-val" style="color:#555;">
                        {{ $menu->updated_at->format('d/m/Y à H:i') }}
                    </span>
                </div>

            </div>
        </div>

        {{-- ── Actions administrateur ── --}}
        <div class="card">
            <div class="card-header">
                <div class="card-header-title">
                    <i class="fa-solid fa-gear" style="color:var(--cc-orange);"></i>
                    Actions
                </div>
            </div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:8px;">

                <a href="{{ route('admin.menus.edit', $menu->idmenu) }}"
                   class="btn btn-ghost btn-sm"
                   style="justify-content:flex-start;">
                    <i class="fa-solid fa-pen-to-square"></i>
                    Modifier ce plat
                </a>

                <button onclick="toggleStatut()"
                        class="btn btn-ghost btn-sm"
                        style="justify-content:flex-start;">
                    <i class="fa-solid {{ $menu->statut === 'Activé' ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                    {{ $menu->statut === 'Activé' ? 'Désactiver' : 'Activer' }}
                </button>

                @if($menu->categorie)
                <a href="{{ route('admin.categories.show', $menu->categorie->idcategorie) }}"
                   class="btn btn-ghost btn-sm"
                   style="justify-content:flex-start;">
                    <i class="fa-solid fa-layer-group"></i>
                    Voir la catégorie
                </a>
                @endif

                <div style="height:1px;background:#1a1a1a;margin:4px 0;"></div>

                <button onclick="confirmerSuppression()"
                        class="btn btn-danger btn-sm"
                        style="justify-content:flex-start;">
                    <i class="fa-solid fa-trash"></i>
                    Supprimer ce plat
                </button>

            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
// ── Données pour le graphique (préparées en PHP) ─────────────
@php
    // Générer les 30 derniers jours
    $labels = [];
    $data   = [];
    for ($i = 29; $i >= 0; $i--) {
        $date     = now()->subDays($i)->format('Y-m-d');
        $labels[] = now()->subDays($i)->format('d/m');
        $vente    = $ventesMois->firstWhere('date', $date);
        $data[]   = $vente ? (int) $vente->total : 0;
    }
@endphp

const LABELS_VENTES = {!! json_encode($labels) !!};
const DATA_VENTES   = {!! json_encode($data) !!};

// ── Graphique Chart.js ───────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('chartVentes');
    if (!canvas) return;

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: LABELS_VENTES,
            datasets: [{
                label: 'Unités vendues',
                data: DATA_VENTES,
                backgroundColor: 'rgba(234,88,12,0.7)',
                borderRadius: 5,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#141414',
                    borderColor: '#1f1f1f',
                    borderWidth: 1,
                    titleColor: '#e5e5e5',
                    bodyColor: '#888',
                    callbacks: {
                        label: ctx => ` ${ctx.parsed.y} unité(s)`
                    }
                }
            },
            scales: {
                x: {
                    grid:  { color: '#141414' },
                    ticks: { color: '#333', font: { size: 9 },
                             maxTicksLimit: 10 }
                },
                y: {
                    grid:  { color: '#141414' },
                    ticks: { color: '#333', font: { size: 10 },
                             stepSize: 1,
                             callback: v => Number.isInteger(v) ? v : '' }
                }
            }
        }
    });
});

// ── Toggle statut ────────────────────────────────────────────
function toggleStatut() {
    const desactiver = '{{ $menu->statut }}' === 'Activé';

    Swal.fire({
        title: desactiver
            ? 'Désactiver "{{ addslashes($menu->intitule) }}" ?'
            : 'Activer "{{ addslashes($menu->intitule) }}" ?',
        html: desactiver
            ? `<div style="color:#666;font-size:13px;">
                   Ce plat ne sera plus proposé dans les commandes.
               </div>`
            : `<div style="color:#666;font-size:13px;">
                   Ce plat redeviendra visible dans les commandes.
               </div>`,
        icon: 'question',
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

// ── Suppression ──────────────────────────────────────────────
function confirmerSuppression() {
    Swal.fire({
        title: 'Supprimer "{{ addslashes($menu->intitule) }}" ?',
        html: `<div style="color:#666;font-size:13px;">
                   Cette action est <strong>irréversible</strong>.
                   Le plat sera archivé.
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
        if (r.isConfirmed) document.getElementById('deleteForm').submit();
    });
}
</script>
@endpush