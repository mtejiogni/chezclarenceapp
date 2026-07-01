@extends('layouts.app')

@section('title', 'Statut : ' . $statut->intitule)
@section('page-title', 'Détail du statut')

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
        margin-bottom: 16px;
    }
    .card-header {
        padding: 14px 20px;
        border-bottom: 1px solid #1a1a1a;
        display: flex; align-items: center; justify-content: space-between; gap: 10px;
    }
    .card-header-title {
        display: flex; align-items: center; gap: 8px;
        font-size: 13px; font-weight: 700; color: #e5e5e5;
    }
    .card-body { padding: 20px; }

    .kpi {
        background: var(--cc-dark3);
        border: 1px solid var(--cc-border);
        border-radius: 13px;
        padding: 1.1rem;
        text-align: center;
    }
    .kpi-val   { font-size: 24px; font-weight: 700; margin-bottom: 4px; }
    .kpi-label { font-size: 11px; color: #444; }

    .btn {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 9px 16px; border-radius: 10px;
        font-size: 12px; font-weight: 600; cursor: pointer;
        transition: all .18s; border: none; font-family: inherit;
        text-decoration: none;
    }
    .btn-ghost { background: var(--cc-dark3); border: 1px solid var(--cc-border); color: #555; }
    .btn-ghost:hover { color: #ccc; border-color: #333; }

    .back-btn {
        width: 36px; height: 36px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        background: var(--cc-dark3); border: 1px solid var(--cc-border); color: #555;
        text-decoration: none; transition: all .18s; flex-shrink: 0;
    }
    .back-btn:hover { color: #ccc; border-color: #333; }

    .data-table { width: 100%; border-collapse: collapse; }
    .data-table th {
        text-align: left; font-size: 10px; font-weight: 600; color: #444;
        text-transform: uppercase; letter-spacing: .5px;
        padding: 10px 16px; border-bottom: 1px solid #1a1a1a;
    }
    .data-table th.right, .data-table td.right { text-align: right; }
    .data-table td {
        padding: 10px 16px; font-size: 12px; color: #888;
        border-bottom: 1px solid #141414;
    }
    .data-table tr:last-child td { border-bottom: none; }
    .data-table tr:hover td { background: #171717; }

    .chart-wrap { position: relative; height: 210px; }

    @media (max-width: 700px) {
        .kpi-grid { grid-template-columns: 1fr !important; }
    }
</style>
@endpush

@section('content')

@php
    $cfg = $config[$statut->intitule] ?? ['text' => '#9ca3af', 'bg' => 'rgba(156,163,175,.12)', 'icone' => 'fa-circle'];
@endphp

{{-- ══════════════════════════════════════════════════════════
     EN-TÊTE
══════════════════════════════════════════════════════════ --}}
<div style="display:flex;align-items:center;gap:14px;margin-bottom:18px;flex-wrap:wrap;">
    <a href="{{ route('admin.statuts.index') }}" class="back-btn">
        <i class="fa-solid fa-arrow-left" style="font-size:13px;"></i>
    </a>
    <div style="width:44px;height:44px;border-radius:12px;flex-shrink:0;
                display:flex;align-items:center;justify-content:center;
                background:{{ $cfg['bg'] }};">
        <i class="fa-solid {{ $cfg['icone'] }}" style="color:{{ $cfg['text'] }};font-size:17px;"></i>
    </div>
    <div style="flex:1;min-width:0;">
        <h2 style="font-size:18px;font-weight:700;color:#fff;margin:0;">{{ $statut->intitule }}</h2>
        @if($statut->description)
        <p style="font-size:12px;color:#555;margin:4px 0 0;">{{ $statut->description }}</p>
        @endif
    </div>
    <a href="{{ route('admin.statuts.edit', $statut->idstatut) }}" class="btn btn-ghost">
        <i class="fa-solid fa-pen"></i> Modifier
    </a>
</div>

{{-- ══════════════════════════════════════════════════════════
     KPIs
══════════════════════════════════════════════════════════ --}}
<div class="kpi-grid" style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:16px;">
    <div class="kpi">
        <div class="kpi-val" style="color:#fff;">{{ $stats['commandes_actives'] }}</div>
        <div class="kpi-label">Commandes actuellement à ce statut</div>
    </div>
    <div class="kpi">
        <div class="kpi-val" style="color:var(--cc-orange2);">{{ $stats['utilisation_mois'] }}</div>
        <div class="kpi-label">Utilisations ce mois-ci</div>
    </div>
    <div class="kpi">
        <div class="kpi-val" style="color:#ccc;">{{ $stats['total_utilisation'] }}</div>
        <div class="kpi-label">Utilisations au total</div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     GRAPHIQUE 30 DERNIERS JOURS
══════════════════════════════════════════════════════════ --}}
<div class="card">
    <div class="card-header">
        <div class="card-header-title">
            <i class="fa-solid fa-chart-line" style="color:{{ $cfg['text'] }};"></i>
            Utilisation — 30 derniers jours
        </div>
    </div>
    <div class="card-body">
        <div class="chart-wrap">
            <canvas id="chart-historique"></canvas>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     COMMANDES ACTUELLEMENT À CE STATUT
══════════════════════════════════════════════════════════ --}}
<div class="card">
    <div class="card-header">
        <div class="card-header-title">
            <i class="fa-solid fa-receipt" style="color:var(--cc-orange);"></i>
            Commandes à ce statut ({{ $commandesActives->count() }})
        </div>
    </div>
    <div class="card-body" style="padding:0;">
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Référence</th>
                        <th>Type</th>
                        <th>Client / Table</th>
                        <th>Serveur</th>
                        <th class="right">Montant</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($commandesActives as $cmd)
                    @php
                        $iconType = match($cmd->typecommande) {
                            'Livraison'  => 'fa-motorcycle',
                            'A emporter' => 'fa-bag-shopping',
                            default      => 'fa-chair',
                        };
                        $labelType = $cmd->typecommande === 'A emporter' ? 'À emporter' : $cmd->typecommande;
                    @endphp
                    <tr>
                        <td style="color:#e5e5e5;font-weight:600;">{{ $cmd->reference }}</td>
                        <td>
                            <i class="fa-solid {{ $iconType }}" style="margin-right:6px;color:#444;"></i>{{ $labelType }}
                        </td>
                        <td>
                            @if($cmd->table)
                                {{ $cmd->table->intitule }}
                            @elseif($cmd->client)
                                {{ $cmd->client->prenom }} {{ $cmd->client->nom }}
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $cmd->serveur ? $cmd->serveur->prenom . ' ' . $cmd->serveur->nom : '—' }}</td>
                        <td class="right" style="color:#fff;font-weight:700;">
                            {{ number_format($cmd->montant, 0, ',', ' ') }} F
                        </td>
                        <td class="right">
                            <a href="{{ route('commandes.show', $cmd->idcommande) }}"
                               style="color:#555;text-decoration:none;">
                                <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:11px;"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center;padding:30px;color:#2a2a2a;">
                            Aucune commande n'est actuellement à ce statut
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const LABELS = {!! json_encode($historiqueUsage->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m'))) !!};
const DATA   = {!! json_encode($historiqueUsage->pluck('total')) !!};

new Chart(document.getElementById('chart-historique'), {
    type: 'bar',
    data: {
        labels: LABELS,
        datasets: [{
            data: DATA,
            backgroundColor: '{{ $cfg["text"] }}',
            borderRadius: 4,
            maxBarThickness: 22,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false }, ticks: { color: '#555', font: { size: 10 } } },
            y: { beginAtZero: true, grid: { color: '#1a1a1a' }, ticks: { color: '#555', font: { size: 10 }, precision: 0 } },
        }
    }
});
</script>
@endpush