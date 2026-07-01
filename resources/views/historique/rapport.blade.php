@extends('layouts.app')

@section('title', 'Rapport d\'activité')
@section('page-title', 'Rapport d\'activité')

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
    .btn-sm { padding: 6px 12px; font-size: 11px; border-radius: 8px; }

    .back-btn {
        width: 36px; height: 36px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        background: var(--cc-dark3); border: 1px solid var(--cc-border); color: #555;
        text-decoration: none; transition: all .18s; flex-shrink: 0;
    }
    .back-btn:hover { color: #ccc; border-color: #333; }

    .sel {
        background: var(--cc-dark2); border: 1px solid var(--cc-border);
        border-radius: 10px; padding: 8px 12px; color: #e5e5e5;
        font-size: 12px; outline: none; font-family: inherit;
        transition: border-color .18s;
    }
    .sel:focus { border-color: var(--cc-orange); }

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

    .chart-wrap { position: relative; height: 220px; }

    .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    @media (max-width: 900px) {
        .two-col { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')

@php
    $couleurs = ['yellow'=>'#eab308','blue'=>'#60a5fa','orange'=>'#f97316','green'=>'#22c55e','red'=>'#f87171','gray'=>'#9ca3af'];
    $configIntitule = fn($i) => match($i) {
        'En attente' => ['couleur'=>'#eab308','icone'=>'fa-clock'],
        'En préparation' => ['couleur'=>'#60a5fa','icone'=>'fa-fire-burner'],
        'Expédiée' => ['couleur'=>'#f97316','icone'=>'fa-motorcycle'],
        'Livrée', 'Servie' => ['couleur'=>'#22c55e','icone'=>'fa-circle-check'],
        'Annulée' => ['couleur'=>'#f87171','icone'=>'fa-circle-xmark'],
        default => ['couleur'=>'#9ca3af','icone'=>'fa-circle'],
    };
@endphp

{{-- ══════════════════════════════════════════════════════════
     EN-TÊTE + FILTRE PÉRIODE
══════════════════════════════════════════════════════════ --}}
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:18px;">
    <div style="display:flex;align-items:center;gap:12px;">
        <a href="{{ route('admin.historiques.index') }}" class="back-btn">
            <i class="fa-solid fa-arrow-left" style="font-size:13px;"></i>
        </a>
        <div>
            <h2 style="font-size:18px;font-weight:700;color:#fff;margin:0;">
                <i class="fa-solid fa-chart-line" style="color:var(--cc-orange);margin-right:8px;"></i>
                Rapport d'activité
            </h2>
            <p style="font-size:12px;color:#444;margin:4px 0 0;">
                Du {{ $debut->format('d/m/Y') }} au {{ $fin->format('d/m/Y') }}
            </p>
        </div>
    </div>
    <form method="GET" action="{{ route('admin.historiques.rapport') }}" style="display:flex;gap:8px;align-items:center;">
        <input type="date" name="debut" value="{{ $debut->format('Y-m-d') }}" class="sel" required>
        <span style="color:#333;font-size:12px;">→</span>
        <input type="date" name="fin" value="{{ $fin->format('Y-m-d') }}" class="sel" required>
        <button type="submit" class="btn btn-ghost btn-sm">
            <i class="fa-solid fa-arrows-rotate"></i> Actualiser
        </button>
    </form>
</div>

{{-- ══════════════════════════════════════════════════════════
     KPIs
══════════════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:16px;">
    <div class="kpi">
        <div class="kpi-val" style="color:var(--cc-orange2);">{{ $transitionsParStatut->sum('total') }}</div>
        <div class="kpi-label">Changements de statut</div>
    </div>
    <div class="kpi">
        <div class="kpi-val" style="color:#fff;">{{ $commandesActives->sum('nb_transitions') }}</div>
        <div class="kpi-label">Transitions (top 10 commandes)</div>
    </div>
    <div class="kpi">
        <div class="kpi-val" style="color:#60a5fa;">
            {{ $dureesMoyennes->avg('duree_moy_minutes') ? round($dureesMoyennes->avg('duree_moy_minutes')) : 0 }} min
        </div>
        <div class="kpi-label">Durée moyenne globale</div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     GRAPHIQUE ACTIVITÉ PAR HEURE
══════════════════════════════════════════════════════════ --}}
<div class="card">
    <div class="card-header">
        <div class="card-header-title">
            <i class="fa-solid fa-clock" style="color:var(--cc-orange);"></i>
            Activité par heure de la journée
        </div>
    </div>
    <div class="card-body">
        <div class="chart-wrap">
            <canvas id="chart-heures"></canvas>
        </div>
    </div>
</div>

<div class="two-col">

    {{-- ══════════════════════════════════════════════════════
         RÉPARTITION PAR STATUT
    ══════════════════════════════════════════════════════════ --}}
    <div class="card">
        <div class="card-header">
            <div class="card-header-title">
                <i class="fa-solid fa-list-check" style="color:var(--cc-orange);"></i>
                Répartition par statut
            </div>
        </div>
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr><th>Statut</th><th class="right">Nb</th><th class="right">Part</th></tr>
                </thead>
                <tbody>
                    @php $totalT = $transitionsParStatut->sum('total'); @endphp
                    @forelse($transitionsParStatut as $t)
                    @php $cfg = $configIntitule($t->intitule); @endphp
                    <tr>
                        <td>
                            <i class="fa-solid {{ $cfg['icone'] }}" style="color:{{ $cfg['couleur'] }};margin-right:6px;"></i>
                            {{ $t->intitule }}
                        </td>
                        <td class="right" style="color:#e5e5e5;font-weight:600;">{{ $t->total }}</td>
                        <td class="right">{{ $totalT > 0 ? round(($t->total / $totalT) * 100) : 0 }}%</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" style="text-align:center;padding:24px;color:#2a2a2a;">Aucune donnée</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
         DURÉES MOYENNES PAR TYPE
    ══════════════════════════════════════════════════════════ --}}
    <div class="card">
        <div class="card-header">
            <div class="card-header-title">
                <i class="fa-solid fa-stopwatch" style="color:var(--cc-orange);"></i>
                Durée moyenne de traitement
            </div>
        </div>
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr><th>Type</th><th class="right">Durée moy.</th><th class="right">Nb</th></tr>
                </thead>
                <tbody>
                    @forelse($dureesMoyennes as $d)
                    @php $labelType = $d->typecommande === 'A emporter' ? 'À emporter' : $d->typecommande; @endphp
                    <tr>
                        <td>{{ $labelType }}</td>
                        <td class="right" style="color:#e5e5e5;font-weight:600;">
                            {{ $d->duree_moy_minutes ? round($d->duree_moy_minutes) : 0 }} min
                        </td>
                        <td class="right">{{ $d->total }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" style="text-align:center;padding:24px;color:#2a2a2a;">Aucune donnée</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════
     TOP 10 COMMANDES LES PLUS ACTIVES
══════════════════════════════════════════════════════════ --}}
<div class="card">
    <div class="card-header">
        <div class="card-header-title">
            <i class="fa-solid fa-fire" style="color:var(--cc-orange);"></i>
            Commandes les plus mouvementées
        </div>
    </div>
    <div style="overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Référence</th>
                    <th>Type</th>
                    <th>Statut actuel</th>
                    <th class="right">Montant</th>
                    <th class="right">Nb changements</th>
                </tr>
            </thead>
            <tbody>
                @forelse($commandesActives as $c)
                @php
                    $iconType = match($c->typecommande) {
                        'Livraison'  => 'fa-motorcycle',
                        'A emporter' => 'fa-bag-shopping',
                        default      => 'fa-chair',
                    };
                    $labelType = $c->typecommande === 'A emporter' ? 'À emporter' : $c->typecommande;
                @endphp
                <tr>
                    <td style="color:#e5e5e5;font-weight:600;">{{ $c->reference }}</td>
                    <td><i class="fa-solid {{ $iconType }}" style="color:#444;margin-right:6px;"></i>{{ $labelType }}</td>
                    <td>{{ $c->statut_courant }}</td>
                    <td class="right">{{ number_format($c->montant, 0, ',', ' ') }} F</td>
                    <td class="right" style="color:var(--cc-orange2);font-weight:700;">{{ $c->nb_transitions }}</td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align:center;padding:30px;color:#2a2a2a;">Aucune donnée sur cette période</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script>
const LABELS = {!! json_encode($heures->pluck('heure')) !!};
const DATA   = {!! json_encode($heures->pluck('total')) !!};

new Chart(document.getElementById('chart-heures'), {
    type: 'bar',
    data: {
        labels: LABELS,
        datasets: [{
            data: DATA,
            backgroundColor: 'rgba(234,88,12,0.75)',
            borderRadius: 4,
            maxBarThickness: 18,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false }, ticks: { color: '#555', font: { size: 9 } } },
            y: { beginAtZero: true, grid: { color: '#1a1a1a' }, ticks: { color: '#555', font: { size: 10 }, precision: 0 } },
        }
    }
});
</script>
@endpush