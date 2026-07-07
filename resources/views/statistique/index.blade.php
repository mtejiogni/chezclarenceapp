@extends('layouts.app')

@section('title', 'Statistiques')
@section('page-title', 'Statistiques')

@push('styles')
<style>
    :root {
        --cc-orange:  #ea580c;
        --cc-orange2: #f97316;
        --cc-dark2:   #0d0d0d;
        --cc-dark3:   #141414;
        --cc-border:  #1f1f1f;
    }

    /* ── Carte ── */
    .card {
        background: var(--cc-dark3);
        border: 1px solid var(--cc-border);
        border-radius: 14px;
        overflow: hidden;
        margin-bottom: 16px;
        transition: border-color .2s;
    }

    .card:hover { border-color: #2a2a2a; }

    .card-header {
        padding: 14px 20px;
        border-bottom: 1px solid #1a1a1a;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
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

    /* ── KPI ── */
    .kpi {
        background: var(--cc-dark2);
        border: 1px solid #1a1a1a;
        border-radius: 12px;
        padding: 16px;
        position: relative;
        overflow: hidden;
        transition: border-color .2s, transform .2s;
    }

    .kpi::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 2px;
        background: linear-gradient(90deg, var(--cc-orange), transparent);
        opacity: 0;
        transition: opacity .3s;
    }

    .kpi:hover { border-color: #2a2a2a; transform: translateY(-2px); }
    .kpi:hover::before { opacity: 1; }

    .kpi-val {
        font-size: 26px;
        font-weight: 700;
        line-height: 1.2;
        margin-bottom: 4px;
    }

    .kpi-label {
        font-size: 11px;
        color: #444;
        text-transform: uppercase;
        letter-spacing: .5px;
    }

    .kpi-evo {
        font-size: 11px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 3px;
        margin-top: 6px;
    }

    /* ── Chips période ── */
    .periode-chip {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
        border: 1px solid #1f1f1f;
        background: #141414;
        color: #555;
        text-decoration: none;
        transition: all .18s;
        display: inline-block;
    }

    .periode-chip.active,
    .periode-chip:hover {
        background: var(--cc-orange);
        color: #fff;
        border-color: var(--cc-orange);
    }

    /* ── Barre de progression ── */
    .prog {
        background: #1a1a1a;
        border-radius: 3px;
        height: 4px;
        overflow: hidden;
        margin-top: 4px;
    }

    .prog-bar {
        height: 4px;
        border-radius: 3px;
        background: var(--cc-orange);
        transition: width 1s ease;
    }

    /* ── Tableau ── */
    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table th {
        font-size: 10px;
        font-weight: 600;
        color: #333;
        text-transform: uppercase;
        letter-spacing: .5px;
        padding: 8px 12px;
        text-align: left;
        border-bottom: 1px solid #1a1a1a;
    }

    .data-table td {
        font-size: 12px;
        color: #888;
        padding: 10px 12px;
        border-bottom: 1px solid #111;
        transition: background .15s;
    }

    .data-table tbody tr:last-child td { border-bottom: none; }
    .data-table tbody tr:hover td { background: rgba(255,255,255,.02); }

    /* ── Bouton ── */
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

    .btn-ghost {
        background: var(--cc-dark3);
        border: 1px solid var(--cc-border);
        color: #555;
    }

    .btn-ghost:hover { color: #ccc; border-color: #333; }

    .btn-primary { background: var(--cc-orange); color: #fff; }
    .btn-primary:hover { background: #c2410c; }

    /* ── Chart ── */
    .chart-wrap {
        position: relative;
        height: 220px;
    }

    /* ── Row commandes ── */
    .cmd-row {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 0;
        border-bottom: 1px solid #1a1a1a;
        font-size: 12px;
    }

    .cmd-row:last-child { border-bottom: none; }

    /* ── Badge ── */
    .badge {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: 600;
    }
</style>
@endpush

@section('content')

@php
    $periodeActive = request('periode', 'semaine');
@endphp

{{-- ══════════════════════════════════════════════════════════
     EN-TÊTE
══════════════════════════════════════════════════════════ --}}
<div style="display:flex;align-items:center;justify-content:space-between;
            flex-wrap:wrap;gap:12px;margin-bottom:20px;">

    <div>
        <h2 style="font-size:18px;font-weight:700;color:#fff;margin:0;">
            <i class="fa-solid fa-chart-line" style="color:var(--cc-orange);margin-right:8px;"></i>
            Statistiques
        </h2>
        <p style="font-size:12px;color:#444;margin:3px 0 0;">
            Vue d'ensemble des performances de Chez Clarence
        </p>
    </div>

    {{-- Sélecteur de période --}}
    <div style="display:flex;gap:6px;align-items:center;">
        @foreach(['jour' => 'Aujourd\'hui', 'semaine' => '7 jours', 'mois' => 'Ce mois', 'annee' => 'Cette année'] as $val => $label)
        <a href="{{ request()->fullUrlWithQuery(['periode' => $val]) }}"
           class="periode-chip {{ $periodeActive === $val ? 'active' : '' }}">
            {{ $label }}
        </a>
        @endforeach

        {{-- Export --}}
        <a href="{{ route('admin.statistiques.export', ['periode' => $periodeActive]) }}"
           class="btn btn-ghost"
           style="margin-left:8px;">
            <i class="fa-solid fa-file-csv"></i>
            Exporter
        </a>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     KPIs PRINCIPAUX
══════════════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
            gap:12px;margin-bottom:20px;">

    {{-- CA Total --}}
    <div class="kpi">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <div style="width:38px;height:38px;border-radius:10px;
                        background:rgba(34,197,94,.12);
                        display:flex;align-items:center;justify-content:center;">
                <i class="fa-solid fa-money-bill-wave" style="color:#22c55e;font-size:15px;"></i>
            </div>
            @if(($stats['evolution_ca'] ?? 0) >= 0)
            <span style="font-size:11px;font-weight:600;color:#22c55e;">
                <i class="fa-solid fa-arrow-trend-up"></i>
                +{{ $stats['evolution_ca'] ?? 0 }}%
            </span>
            @else
            <span style="font-size:11px;font-weight:600;color:#ef4444;">
                <i class="fa-solid fa-arrow-trend-down"></i>
                {{ $stats['evolution_ca'] ?? 0 }}%
            </span>
            @endif
        </div>
        <div class="kpi-val" style="color:#22c55e;">
            {{ number_format($stats['ca_total'] ?? 0, 0, ',', ' ') }}
        </div>
        <div class="kpi-label">CA Total (FCFA)</div>
    </div>

    {{-- Nombre de commandes --}}
    <div class="kpi">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <div style="width:38px;height:38px;border-radius:10px;
                        background:rgba(96,165,250,.12);
                        display:flex;align-items:center;justify-content:center;">
                <i class="fa-solid fa-receipt" style="color:#60a5fa;font-size:15px;"></i>
            </div>
            @if(($stats['evolution_commandes'] ?? 0) >= 0)
            <span style="font-size:11px;font-weight:600;color:#22c55e;">
                <i class="fa-solid fa-arrow-trend-up"></i>
                +{{ $stats['evolution_commandes'] ?? 0 }}%
            </span>
            @else
            <span style="font-size:11px;font-weight:600;color:#ef4444;">
                {{ $stats['evolution_commandes'] ?? 0 }}%
            </span>
            @endif
        </div>
        <div class="kpi-val" style="color:#60a5fa;">
            {{ number_format($stats['nb_commandes'] ?? 0, 0, ',', ' ') }}
        </div>
        <div class="kpi-label">Commandes</div>
    </div>

    {{-- Panier moyen --}}
    <div class="kpi">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <div style="width:38px;height:38px;border-radius:10px;
                        background:rgba(234,88,12,.12);
                        display:flex;align-items:center;justify-content:center;">
                <i class="fa-solid fa-basket-shopping" style="color:#f97316;font-size:15px;"></i>
            </div>
        </div>
        <div class="kpi-val" style="color:#f97316;">
            {{ number_format($stats['panier_moyen'] ?? 0, 0, ',', ' ') }}
        </div>
        <div class="kpi-label">Panier moyen (FCFA)</div>
    </div>

    {{-- Livraisons --}}
    <div class="kpi">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <div style="width:38px;height:38px;border-radius:10px;
                        background:rgba(168,85,247,.12);
                        display:flex;align-items:center;justify-content:center;">
                <i class="fa-solid fa-motorcycle" style="color:#a855f7;font-size:15px;"></i>
            </div>
        </div>
        <div class="kpi-val" style="color:#a855f7;">
            {{ number_format($stats['nb_livraisons'] ?? 0, 0, ',', ' ') }}
        </div>
        <div class="kpi-label">Livraisons</div>
    </div>

    {{-- [AJOUT] À emporter --}}
    <div class="kpi">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <div style="width:38px;height:38px;border-radius:10px;
                        background:rgba(34,197,94,.12);
                        display:flex;align-items:center;justify-content:center;">
                <i class="fa-solid fa-bag-shopping" style="color:#22c55e;font-size:15px;"></i>
            </div>
        </div>
        <div class="kpi-val" style="color:#22c55e;">
            {{ number_format($stats['nb_a_emporter'] ?? 0, 0, ',', ' ') }}
        </div>
        <div class="kpi-label">À emporter</div>
    </div>

    {{-- Taux d'annulation --}}
    <div class="kpi">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <div style="width:38px;height:38px;border-radius:10px;
                        background:rgba(239,68,68,.12);
                        display:flex;align-items:center;justify-content:center;">
                <i class="fa-solid fa-circle-xmark" style="color:#f87171;font-size:15px;"></i>
            </div>
        </div>
        <div class="kpi-val" style="color:#f87171;">
            {{ $stats['taux_annulation'] ?? 0 }}%
        </div>
        <div class="kpi-label">Taux d'annulation</div>
    </div>

    {{-- Clients servis --}}
    <div class="kpi">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <div style="width:38px;height:38px;border-radius:10px;
                        background:rgba(234,179,8,.12);
                        display:flex;align-items:center;justify-content:center;">
                <i class="fa-solid fa-users" style="color:#eab308;font-size:15px;"></i>
            </div>
        </div>
        <div class="kpi-val" style="color:#eab308;">
            {{ number_format($stats['clients_servis'] ?? 0, 0, ',', ' ') }}
        </div>
        <div class="kpi-label">Clients servis</div>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════
     GRAPHIQUES : Évolution + Répartition
══════════════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:1fr 340px;gap:16px;margin-bottom:16px;">

    {{-- Graphique évolution des ventes --}}
    <div class="card">
        <div class="card-header">
            <div class="card-header-title">
                <i class="fa-solid fa-chart-area" style="color:var(--cc-orange);"></i>
                Évolution des ventes
                <span style="font-size:11px;font-weight:400;color:#444;">
                    (FCFA · {{ ucfirst($periodeActive) }})
                </span>
            </div>
            <div style="display:flex;gap:10px;align-items:center;">
                <div style="display:flex;align-items:center;gap:5px;font-size:11px;color:#444;">
                    <span style="width:12px;height:3px;background:#ea580c;border-radius:2px;display:inline-block;"></span>
                    CA
                </div>
                <div style="display:flex;align-items:center;gap:5px;font-size:11px;color:#444;">
                    <span style="width:12px;height:3px;background:#60a5fa;border-radius:2px;display:inline-block;"></span>
                    Commandes
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="chart-wrap">
                <canvas id="chartEvolution"></canvas>
            </div>
        </div>
    </div>

    {{-- Répartition Standard / A emporter / Livraison --}}
    <div class="card">
        <div class="card-header">
            <div class="card-header-title">
                <i class="fa-solid fa-chart-pie" style="color:var(--cc-orange);"></i>
                Répartition
            </div>
        </div>
        <div class="card-body">
            <div class="chart-wrap" style="height:160px;">
                <canvas id="chartRepartition"></canvas>
            </div>
            {{-- Légende --}}
            <div style="display:flex;flex-direction:column;gap:8px;margin-top:14px;">
                <div style="display:flex;align-items:center;justify-content:space-between;font-size:12px;">
                    <span style="display:flex;align-items:center;gap:7px;color:#888;">
                        <span style="width:10px;height:10px;border-radius:50%;background:#ea580c;display:inline-block;"></span>
                        Sur place
                    </span>
                    <span style="font-weight:700;color:#e5e5e5;">
                        {{ $stats['nb_sur_place'] ?? 0 }} commandes
                    </span>
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;font-size:12px;">
                    <span style="display:flex;align-items:center;gap:7px;color:#888;">
                        <span style="width:10px;height:10px;border-radius:50%;background:#22c55e;display:inline-block;"></span>
                        À emporter
                    </span>
                    <span style="font-weight:700;color:#e5e5e5;">
                        {{ $stats['nb_a_emporter'] ?? 0 }} commandes
                    </span>
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;font-size:12px;">
                    <span style="display:flex;align-items:center;gap:7px;color:#888;">
                        <span style="width:10px;height:10px;border-radius:50%;background:#60a5fa;display:inline-block;"></span>
                        Livraison
                    </span>
                    <span style="font-weight:700;color:#e5e5e5;">
                        {{ $stats['nb_livraisons'] ?? 0 }} commandes
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     TOP PLATS + RÉPARTITION STATUTS
══════════════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">

    {{-- Top 10 plats --}}
    <div class="card">
        <div class="card-header">
            <div class="card-header-title">
                <i class="fa-solid fa-ranking-star" style="color:var(--cc-orange);"></i>
                Top 10 plats vendus
            </div>
        </div>
        <div class="card-body">
            @forelse($topPlats ?? [] as $i => $plat)
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
                <span style="width:22px;height:22px;border-radius:50%;flex-shrink:0;
                             background:{{ $i < 3 ? 'rgba(234,88,12,.2)' : '#1a1a1a' }};
                             color:{{ $i < 3 ? '#f97316' : '#444' }};
                             font-size:10px;font-weight:700;
                             display:flex;align-items:center;justify-content:center;">
                    {{ $i + 1 }}
                </span>
                <div style="flex:1;min-width:0;">
                    <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                        <span style="font-size:12px;color:#e5e5e5;white-space:nowrap;
                                     overflow:hidden;text-overflow:ellipsis;max-width:180px;">
                            {{ $plat->intitule }}
                        </span>
                        <span style="font-size:12px;font-weight:700;color:#f97316;flex-shrink:0;margin-left:8px;">
                            {{ $plat->total_vendu }}
                        </span>
                    </div>
                    <div class="prog">
                        <div class="prog-bar"
                             style="width:{{ $i === 0 ? 100 : max(10, round(($plat->total_vendu / ($topPlats[0]->total_vendu ?: 1)) * 100)) }}%;
                                    background:{{ $i < 3 ? '#ea580c' : '#2a2a2a' }};"></div>
                    </div>
                </div>
            </div>
            @empty
            <div style="text-align:center;padding:30px 0;color:#2a2a2a;">
                <i class="fa-solid fa-chart-bar" style="font-size:28px;display:block;margin-bottom:8px;"></i>
                <p style="font-size:12px;">Aucune donnée pour cette période</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Répartition par statut --}}
    <div class="card">
        <div class="card-header">
            <div class="card-header-title">
                <i class="fa-solid fa-layer-group" style="color:var(--cc-orange);"></i>
                Répartition par statut
            </div>
        </div>
        <div class="card-body">
            <div class="chart-wrap" style="height:180px;">
                <canvas id="chartStatuts"></canvas>
            </div>
            {{-- Légende statuts --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-top:12px;">
                @foreach($stats['par_statut'] ?? [] as $statut => $nb)
                @php
                    $couleur = match($statut) {
                        'En attente'     => '#eab308',
                        'En préparation' => '#60a5fa',
                        'Expédiée'       => '#f97316',
                        'Servie'         => '#22c55e',
                        'Livrée'         => '#22c55e',
                        'Annulée'        => '#f87171',
                        default          => '#555',
                    };
                @endphp
                <div style="display:flex;align-items:center;gap:6px;font-size:11px;color:#666;">
                    <span style="width:8px;height:8px;border-radius:50%;
                                 background:{{ $couleur }};flex-shrink:0;display:inline-block;"></span>
                    {{ $statut }} ({{ $nb }})
                </div>
                @endforeach
            </div>
        </div>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════
     PERFORMANCES PAR CATÉGORIE
══════════════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">

    {{-- CA par catégorie --}}
    <div class="card">
        <div class="card-header">
            <div class="card-header-title">
                <i class="fa-solid fa-tags" style="color:var(--cc-orange);"></i>
                CA par catégorie
            </div>
        </div>
        <div class="card-body">
            @forelse($stats['ca_par_categorie'] ?? [] as $cat)
            <div style="display:flex;align-items:center;justify-content:space-between;
                        padding:8px 0;border-bottom:1px solid #111;">
                <span style="font-size:12px;color:#888;">{{ $cat->nom_categorie }}</span>
                <div style="text-align:right;">
                    <div style="font-size:12px;font-weight:700;color:#e5e5e5;">
                        {{ number_format($cat->ca, 0, ',', ' ') }} FCFA
                    </div>
                    <div style="font-size:10px;color:#444;">
                        {{ $cat->nb_ventes }} ventes
                    </div>
                </div>
            </div>
            @empty
            <p style="font-size:12px;color:#333;text-align:center;padding:20px 0;">
                Aucune donnée
            </p>
            @endforelse
        </div>
    </div>

    {{-- Performances par serveur --}}
    <div class="card">
        <div class="card-header">
            <div class="card-header-title">
                <i class="fa-solid fa-bell-concierge" style="color:var(--cc-orange);"></i>
                Performances par serveur
            </div>
        </div>
        <div class="card-body">
            @forelse($stats['par_serveur'] ?? [] as $i => $s)
            <div style="display:flex;align-items:center;gap:10px;
                        padding:8px 0;border-bottom:1px solid #111;">
                {{-- Avatar --}}
                <div style="width:34px;height:34px;border-radius:9px;flex-shrink:0;
                            background:rgba(234,88,12,.12);
                            display:flex;align-items:center;justify-content:center;
                            font-size:12px;font-weight:700;color:#f97316;">
                    {{ strtoupper(substr($s->prenom ?? '?', 0, 1) . substr($s->nom ?? '', 0, 1)) }}
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:12px;font-weight:600;color:#e5e5e5;">
                        {{ $s->prenom }} {{ $s->nom }}
                    </div>
                    <div style="font-size:10px;color:#444;margin-top:1px;">
                        {{ $s->nb_commandes }} commandes
                    </div>
                </div>
                <div style="text-align:right;flex-shrink:0;">
                    <div style="font-size:12px;font-weight:700;color:#22c55e;">
                        {{ number_format($s->ca, 0, ',', ' ') }} F
                    </div>
                </div>
            </div>
            @empty
            <p style="font-size:12px;color:#333;text-align:center;padding:20px 0;">
                Aucune donnée
            </p>
            @endforelse
        </div>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════
     TABLEAU DES MEILLEURES JOURNÉES
══════════════════════════════════════════════════════════ --}}
<div class="card">
    <div class="card-header">
        <div class="card-header-title">
            <i class="fa-solid fa-calendar-check" style="color:var(--cc-orange);"></i>
            Meilleures journées
            <span style="font-size:11px;font-weight:400;color:#444;">({{ ucfirst($periodeActive) }})</span>
        </div>
        <a href="{{ route('admin.statistiques.export', ['periode' => $periodeActive]) }}"
           class="btn btn-ghost"
           style="font-size:11px;padding:5px 12px;">
            <i class="fa-solid fa-download"></i>
            CSV
        </a>
    </div>
    <div class="card-body" style="padding:0;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>CA (FCFA)</th>
                    <th>Commandes</th>
                    <th>Panier moyen</th>
                    <th>Sur place</th>
                    <th>À emporter</th>
                    <th>Livraisons</th>
                    <th>Annulées</th>
                </tr>
            </thead>
            <tbody>
                @forelse($meilleuresJournees ?? [] as $j)
                <tr>
                    <td style="color:#e5e5e5;font-weight:500;">
                        {{ \Carbon\Carbon::parse($j->date)->isoFormat('ddd DD MMM') }}
                    </td>
                    <td style="color:#22c55e;font-weight:700;">
                        {{ number_format($j->ca, 0, ',', ' ') }}
                    </td>
                    <td style="color:#60a5fa;">{{ $j->nb_commandes }}</td>
                    <td>{{ number_format($j->panier_moyen, 0, ',', ' ') }}</td>
                    <td>{{ $j->sur_place ?? 0 }}</td>
                    <td>{{ $j->a_emporter ?? 0 }}</td>
                    <td>{{ $j->livraisons ?? 0 }}</td>
                    <td style="color:{{ ($j->annulees ?? 0) > 0 ? '#f87171' : '#333' }};">
                        {{ $j->annulees ?? 0 }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center;padding:30px;color:#2a2a2a;">
                        <i class="fa-solid fa-calendar-xmark"
                           style="font-size:24px;display:block;margin-bottom:8px;"></i>
                        Aucune donnée pour cette période
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ════════════════════════════════════════════════════════════
// DONNÉES INJECTÉES PAR BLADE
// ════════════════════════════════════════════════════════════

const LABELS_EVO   = {!! json_encode($labelsEvolution  ?? []) !!};
const DATA_CA      = {!! json_encode($dataCA           ?? []) !!};
const DATA_NB      = {!! json_encode($dataNbCommandes  ?? []) !!};
const DATA_REP     = {!! json_encode(array_values($stats['repartition'] ?? ['Sur place' => 0, 'A emporter' => 0, 'Livraison' => 0])) !!};
const LABELS_REP   = {!! json_encode(array_keys($stats['repartition']   ?? ['Sur place' => 0, 'A emporter' => 0, 'Livraison' => 0])) !!};

@php
    $statutsLabels = array_keys($stats['par_statut'] ?? []);
    $statutsData   = array_values($stats['par_statut'] ?? []);
    $statutsCouleurs = array_map(fn($s) => match($s) {
        'En attente'     => 'rgba(234,179,8,.7)',
        'En préparation' => 'rgba(59,130,246,.7)',
        'Expédiée'       => 'rgba(234,88,12,.7)',
        'Servie'         => 'rgba(34,197,94,.7)',
        'Livrée'         => 'rgba(34,197,94,.7)',
        'Annulée'        => 'rgba(239,68,68,.7)',
        default          => 'rgba(85,85,85,.7)',
    }, $statutsLabels);
@endphp

const STATUTS_LABELS   = {!! json_encode($statutsLabels)   !!};
const STATUTS_DATA     = {!! json_encode($statutsData)     !!};
const STATUTS_COULEURS = {!! json_encode($statutsCouleurs) !!};

// ════════════════════════════════════════════════════════════
// OPTIONS COMMUNES
// ════════════════════════════════════════════════════════════

const CHART_DEFAULTS = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: '#141414',
            borderColor: '#2a2a2a',
            borderWidth: 1,
            titleColor: '#e5e5e5',
            bodyColor: '#888',
            padding: 10,
        }
    }
};

const SCALE_X = {
    grid: { color: '#111' },
    ticks: { color: '#333', font: { size: 10 } }
};

const SCALE_Y = (fmt) => ({
    grid: { color: '#111' },
    ticks: {
        color: '#333',
        font: { size: 10 },
        callback: v => fmt ? new Intl.NumberFormat('fr-FR').format(v) : v
    }
});

// ════════════════════════════════════════════════════════════
// GRAPHIQUE 1 : ÉVOLUTION DES VENTES (line)
// ════════════════════════════════════════════════════════════

const ctxEvo = document.getElementById('chartEvolution');
if (ctxEvo) {
    new Chart(ctxEvo, {
        type: 'line',
        data: {
            labels: LABELS_EVO,
            datasets: [
                {
                    label: 'CA (FCFA)',
                    data: DATA_CA,
                    borderColor: '#ea580c',
                    backgroundColor: 'rgba(234,88,12,.06)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#ea580c',
                    pointBorderColor: '#0d0d0d',
                    pointBorderWidth: 2,
                },
                {
                    label: 'Nb commandes',
                    data: DATA_NB,
                    borderColor: '#60a5fa',
                    backgroundColor: 'transparent',
                    tension: 0.4,
                    pointRadius: 3,
                    pointBackgroundColor: '#60a5fa',
                    yAxisID: 'y1',
                }
            ]
        },
        options: {
            ...CHART_DEFAULTS,
            interaction: { intersect: false, mode: 'index' },
            plugins: {
                ...CHART_DEFAULTS.plugins,
                legend: { display: false },
                tooltip: {
                    ...CHART_DEFAULTS.plugins.tooltip,
                    callbacks: {
                        label: ctx => ctx.dataset.label === 'CA (FCFA)'
                            ? ' ' + new Intl.NumberFormat('fr-FR').format(ctx.parsed.y) + ' FCFA'
                            : ' ' + ctx.parsed.y + ' commandes'
                    }
                }
            },
            scales: {
                x: SCALE_X,
                y: {
                    ...SCALE_Y(true),
                    position: 'left',
                },
                y1: {
                    position: 'right',
                    grid: { display: false },
                    ticks: { color: '#333', font: { size: 10 } }
                }
            }
        }
    });
}

// ════════════════════════════════════════════════════════════
// GRAPHIQUE 2 : RÉPARTITION (donut)
// ════════════════════════════════════════════════════════════

const ctxRep = document.getElementById('chartRepartition');
if (ctxRep) {
    new Chart(ctxRep, {
        type: 'doughnut',
        data: {
            labels: LABELS_REP,
            datasets: [{
                data: DATA_REP,
                backgroundColor: ['rgba(234,88,12,.8)', 'rgba(34,197,94,.8)', 'rgba(96,165,250,.8)'],
                borderColor: '#0d0d0d',
                borderWidth: 3,
                hoverOffset: 6,
            }]
        },
        options: {
            ...CHART_DEFAULTS,
            cutout: '68%',
            plugins: {
                ...CHART_DEFAULTS.plugins,
                tooltip: {
                    ...CHART_DEFAULTS.plugins.tooltip,
                    callbacks: {
                        label: ctx => ` ${ctx.label} : ${ctx.parsed} commandes`
                    }
                }
            }
        }
    });
}

// ════════════════════════════════════════════════════════════
// GRAPHIQUE 3 : STATUTS (bar horizontale)
// ════════════════════════════════════════════════════════════

const ctxSt = document.getElementById('chartStatuts');
if (ctxSt) {
    new Chart(ctxSt, {
        type: 'bar',
        data: {
            labels: STATUTS_LABELS,
            datasets: [{
                data: STATUTS_DATA,
                backgroundColor: STATUTS_COULEURS,
                borderRadius: 5,
                borderSkipped: false,
            }]
        },
        options: {
            ...CHART_DEFAULTS,
            indexAxis: 'y',
            scales: {
                x: {
                    grid: { color: '#111' },
                    ticks: { color: '#333', font: { size: 10 } }
                },
                y: {
                    grid: { display: false },
                    ticks: { color: '#444', font: { size: 10 } }
                }
            }
        }
    });
}
</script>
@endpush