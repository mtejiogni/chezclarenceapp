@extends('layouts.app')

@section('title', 'Caisse')
@section('page-title', 'Caisse')

@push('styles')
<style>
    :root {
        --cc-orange:  #ea580c;
        --cc-orange2: #f97316;
        --cc-dark2:   #0d0d0d;
        --cc-dark3:   #141414;
        --cc-border:  #1f1f1f;
    }

    /* ── Carte générique ── */
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

    /* ── KPI ── */
    .kpi {
        background: var(--cc-dark3);
        border: 1px solid var(--cc-border);
        border-radius: 13px;
        padding: 1.1rem;
        text-align: center;
    }
    .kpi-val   { font-size: 24px; font-weight: 700; margin-bottom: 4px; }
    .kpi-label { font-size: 11px; color: #444; }

    /* ── Boutons ── */
    .btn {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 9px 16px; border-radius: 10px;
        font-size: 12px; font-weight: 600; cursor: pointer;
        transition: all .18s; border: none; font-family: inherit;
        text-decoration: none;
    }
    .btn-primary { background: var(--cc-orange); color: #fff; }
    .btn-primary:hover { background: #c2410c; }
    .btn-ghost {
        background: var(--cc-dark3); border: 1px solid var(--cc-border); color: #555;
    }
    .btn-ghost:hover { color: #ccc; border-color: #333; }
    .btn-success {
        background: rgba(34,197,94,.1); border: 1px solid rgba(34,197,94,.25); color: #22c55e;
    }
    .btn-success:hover { background: #22c55e; color: #fff; }
    .btn-sm { padding: 6px 12px; font-size: 11px; border-radius: 8px; }

    /* ── Input date ── */
    .sel {
        background: var(--cc-dark2); border: 1px solid var(--cc-border);
        border-radius: 10px; padding: 8px 12px; color: #e5e5e5;
        font-size: 12px; outline: none; cursor: pointer; font-family: inherit;
        transition: border-color .18s;
    }
    .sel:focus { border-color: var(--cc-orange); }

    /* ── Badges statut ── */
    .badge {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;
    }
    .b-attente  { background: rgba(234,179,8,.12);  color: #eab308; }
    .b-prep     { background: rgba(59,130,246,.12);  color: #60a5fa; }
    .b-expediee { background: rgba(234,88,12,.12);   color: #f97316; }
    .b-servie,
    .b-livree   { background: rgba(34,197,94,.12);   color: #22c55e; }
    .b-annulee  { background: rgba(239,68,68,.12);   color: #f87171; }

    /* ── Lignes commande ── */
    .cmd-row {
        display: flex; align-items: center; gap: 12px;
        padding: 10px 12px; border-radius: 10px;
        background: var(--cc-dark2); border: 1px solid #1a1a1a;
        transition: border-color .18s;
    }
    .cmd-row:hover { border-color: #252525; }

    /* ── Mode paiement bar ── */
    .pay-row { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
    .pay-bar-track {
        flex: 1; height: 7px; border-radius: 4px; background: #1a1a1a; overflow: hidden;
    }
    .pay-bar-fill { height: 100%; background: var(--cc-orange); border-radius: 4px; }

    /* ── Table ── */
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table th {
        text-align: left; font-size: 10px; font-weight: 600; color: #444;
        text-transform: uppercase; letter-spacing: .5px;
        padding: 10px 16px; border-bottom: 1px solid #1a1a1a;
    }
    .data-table td {
        padding: 10px 16px; font-size: 12px; color: #888;
        border-bottom: 1px solid #141414;
    }
    .data-table tr:last-child td { border-bottom: none; }

    .chart-wrap { position: relative; height: 220px; }

    @media (max-width: 900px) {
        .caisse-grid { grid-template-columns: 1fr !important; }
    }
</style>
@endpush

@section('content')

{{-- ══════════════════════════════════════════════════════════
     EN-TÊTE
══════════════════════════════════════════════════════════ --}}
<div style="display:flex;align-items:center;justify-content:space-between;
            flex-wrap:wrap;gap:12px;margin-bottom:18px;">
    <div>
        <h2 style="font-size:18px;font-weight:700;color:#fff;margin:0;">
            <i class="fa-solid fa-cash-register" style="color:var(--cc-orange);margin-right:8px;"></i>
            Caisse — {{ $date->translatedFormat('l d F Y') }}
        </h2>
        <p style="font-size:12px;color:#444;margin:4px 0 0;">
            Suivi des encaissements et clôture journalière
        </p>
    </div>

    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
        {{-- Filtre date --}}
        <form method="GET" action="{{ route('caisse.index') }}" id="dateForm">
            <input type="date" name="date" value="{{ $date->format('Y-m-d') }}"
                   class="sel" onchange="this.form.submit()">
        </form>

        <a href="{{ route('caisse.rapport', ['date' => $date->format('Y-m-d')]) }}"
           target="_blank" class="btn btn-ghost">
            <i class="fa-solid fa-file-pdf" style="color:#f97316;"></i> Rapport PDF
        </a>

        @if(in_array(auth()->user()->role, ['Administrateur','Caissier']))
        <button onclick="confirmerCloture()" class="btn btn-primary">
            <i class="fa-solid fa-lock"></i> Clôturer
        </button>
        @endif
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     MESSAGES FLASH
══════════════════════════════════════════════════════════ --}}
@if(session('success'))
<div style="margin-bottom:16px;padding:12px 16px;border-radius:10px;font-size:12px;
            background:rgba(34,197,94,.07);border:1px solid rgba(34,197,94,.2);color:#22c55e;">
    <i class="fa-solid fa-circle-check" style="margin-right:6px;"></i>
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div style="margin-bottom:16px;padding:12px 16px;border-radius:10px;font-size:12px;
            background:rgba(239,68,68,.07);border:1px solid rgba(239,68,68,.2);color:#f87171;">
    <i class="fa-solid fa-circle-exclamation" style="margin-right:6px;"></i>
    {{ session('error') }}
</div>
@endif

{{-- ══════════════════════════════════════════════════════════
     KPIs
══════════════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:16px;">
    <div class="kpi">
        <div class="kpi-val" style="color:#22c55e;">
            {{ number_format($totalCaisse, 0, ',', ' ') }}
        </div>
        <div class="kpi-label">Total encaissé (FCFA)</div>
    </div>
    <div class="kpi">
        <div class="kpi-val" style="color:#60a5fa;">{{ $nbEncaissees }}</div>
        <div class="kpi-label">Commandes encaissées</div>
    </div>
    <div class="kpi">
        <div class="kpi-val" style="color:#f97316;">
            {{ number_format($panierMoyen, 0, ',', ' ') }}
        </div>
        <div class="kpi-label">Panier moyen (FCFA)</div>
    </div>
    <div class="kpi">
        <div class="kpi-val" style="color:#eab308;">{{ $aEncaisser->count() }}</div>
        <div class="kpi-label">À encaisser</div>
    </div>
    <div class="kpi">
        <div class="kpi-val" style="color:#f87171;">{{ $nbAnnulees }}</div>
        <div class="kpi-label">Annulées</div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     CORPS : 2 colonnes
══════════════════════════════════════════════════════════ --}}
<div class="caisse-grid" style="display:grid;grid-template-columns:1fr 320px;gap:16px;align-items:start;">

    {{-- ════════════════════════════════
         COLONNE GAUCHE
    ════════════════════════════════ --}}
    <div>

        {{-- À encaisser
             [CORRECTION] une commande est "à encaisser" tant qu'elle
             n'est NI 'Servie' NI 'Livrée' (et non annulée) — donc
             'En attente', 'En préparation' ou 'Expédiée'. Ce panel
             remplace les anciens panels distincts "À encaisser" et
             "Commandes actives", qui pointaient désormais vers le
             même ensemble de commandes. --}}
        <div class="card">
            <div class="card-header">
                <div class="card-header-title">
                    <i class="fa-solid fa-hourglass-half" style="color:#eab308;"></i>
                    À encaisser ({{ $aEncaisser->count() }})
                </div>
            </div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:8px;">
                @forelse($aEncaisser as $cmd)
                @php
                    $slug = match($cmd->statut_courant) {
                        'En attente'     => 'attente',
                        'En préparation' => 'prep',
                        'Expédiée'       => 'expediee',
                        default          => 'attente',
                    };
                    $iconType = match($cmd->typecommande) {
                        'Livraison'  => 'fa-motorcycle',
                        'A emporter' => 'fa-bag-shopping',
                        default      => 'fa-chair',
                    };
                @endphp
                <div class="cmd-row">
                    <i class="fa-solid {{ $iconType }}" style="color:#555;font-size:13px;"></i>
                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                            <span style="font-size:13px;font-weight:700;color:#e5e5e5;">{{ $cmd->reference }}</span>
                            @if($cmd->table)
                            <span style="font-size:10px;padding:2px 7px;border-radius:5px;background:#1a1a1a;color:#444;">
                                {{ $cmd->table->intitule }}
                            </span>
                            @endif
                            <span class="badge b-{{ $slug }}">{{ $cmd->statut_courant }}</span>
                        </div>
                        <div style="font-size:11px;color:#444;margin-top:2px;">
                            {{ $cmd->heurecommande }} · {{ $cmd->lignes->count() }} article(s)
                        </div>
                    </div>
                    <span style="font-size:13px;font-weight:700;color:#fff;">
                        {{ number_format($cmd->montant, 0, ',', ' ') }} F
                    </span>
                    <a href="{{ route('caisse.recu', $cmd->idcommande) }}" target="_blank"
                       class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-print"></i> Reçu
                    </a>
                </div>
                @empty
                <div style="text-align:center;padding:28px;color:#2a2a2a;">
                    <i class="fa-solid fa-circle-check" style="font-size:26px;display:block;margin-bottom:8px;color:#22c55e;"></i>
                    <p style="font-size:13px;">Rien à encaisser pour le moment</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Commandes encaissées (table détaillée) --}}
        <div class="card">
            <div class="card-header">
                <div class="card-header-title">
                    <i class="fa-solid fa-receipt" style="color:var(--cc-orange);"></i>
                    Commandes encaissées
                </div>
            </div>
            <div class="card-body" style="padding:0;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Référence</th>
                            <th>Type</th>
                            <th>Heure</th>
                            <th>Paiement</th>
                            <th>Montant</th>
                            <th>Statut</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($commandesEncaissees as $cmd)
                        @php
                            $slug = $cmd->statut_courant === 'Livrée' ? 'livree' : 'servie';
                            $typeLabel = $cmd->typecommande === 'A emporter' ? 'À emporter' : $cmd->typecommande;
                        @endphp
                        <tr>
                            <td style="color:#e5e5e5;font-weight:600;">{{ $cmd->reference }}</td>
                            <td>{{ $typeLabel }}</td>
                            <td>{{ $cmd->heurecommande }}</td>
                            <td>{{ $cmd->mode_paiement ?? 'Espèces' }}</td>
                            <td style="color:#fff;font-weight:700;">
                                {{ number_format($cmd->montant, 0, ',', ' ') }} F
                            </td>
                            <td><span class="badge b-{{ $slug }}">{{ $cmd->statut_courant }}</span></td>
                            <td>
                                <a href="{{ route('caisse.recu', $cmd->idcommande) }}" target="_blank"
                                   class="btn btn-ghost btn-sm">
                                    <i class="fa-solid fa-print"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="text-align:center;padding:30px;color:#2a2a2a;">
                                Aucune commande encaissée pour cette date
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- ════════════════════════════════
         COLONNE DROITE
    ════════════════════════════════ --}}
    <div>

        {{-- Répartition par type --}}
        <div class="card">
            <div class="card-header">
                <div class="card-header-title">
                    <i class="fa-solid fa-chart-pie" style="color:var(--cc-orange);"></i>
                    Répartition
                </div>
            </div>
            <div class="card-body">
                <div class="chart-wrap" style="height:160px;">
                    <canvas id="c-repartition"></canvas>
                </div>
                <div style="display:flex;flex-direction:column;gap:8px;margin-top:14px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;font-size:12px;">
                        <span style="display:flex;align-items:center;gap:7px;color:#888;">
                            <span style="width:10px;height:10px;border-radius:50%;background:#ea580c;display:inline-block;"></span>
                            Sur place
                        </span>
                        <span style="font-weight:700;color:#e5e5e5;">{{ $dataRepartition['Standard'] }}</span>
                    </div>
                    <div style="display:flex;align-items:center;justify-content:space-between;font-size:12px;">
                        <span style="display:flex;align-items:center;gap:7px;color:#888;">
                            <span style="width:10px;height:10px;border-radius:50%;background:#22c55e;display:inline-block;"></span>
                            À emporter
                        </span>
                        <span style="font-weight:700;color:#e5e5e5;">{{ $dataRepartition['A emporter'] }}</span>
                    </div>
                    <div style="display:flex;align-items:center;justify-content:space-between;font-size:12px;">
                        <span style="display:flex;align-items:center;gap:7px;color:#888;">
                            <span style="width:10px;height:10px;border-radius:50%;background:#60a5fa;display:inline-block;"></span>
                            Livraison
                        </span>
                        <span style="font-weight:700;color:#e5e5e5;">{{ $dataRepartition['Livraison'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Répartition par mode de paiement --}}
        <div class="card">
            <div class="card-header">
                <div class="card-header-title">
                    <i class="fa-solid fa-credit-card" style="color:var(--cc-orange);"></i>
                    Modes de paiement
                </div>
            </div>
            <div class="card-body">
                @forelse($parModePaiement as $mode => $montant)
                @php
                    $pct = $totalCaisse > 0 ? round(($montant / $totalCaisse) * 100) : 0;
                @endphp
                <div class="pay-row">
                    <span style="font-size:11px;color:#888;min-width:90px;">{{ $mode }}</span>
                    <div class="pay-bar-track">
                        <div class="pay-bar-fill" style="width:{{ $pct }}%;"></div>
                    </div>
                    <span style="font-size:11px;font-weight:700;color:#e5e5e5;min-width:50px;text-align:right;">
                        {{ $pct }}%
                    </span>
                </div>
                @empty
                <p style="font-size:12px;color:#333;text-align:center;padding:10px 0;">Aucune donnée</p>
                @endforelse
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
// ── Confirmation de clôture ────────────────────────────────────
function confirmerCloture() {
    Swal.fire({
        title: 'Clôturer la caisse ?',
        html: '<div style="color:#666;font-size:13px;">Un rapport Z sera généré. Vérifiez que toutes les commandes sont encaissées et qu\'aucune n\'est encore active.</div>',
        icon: 'warning',
        iconColor: '#ea580c',
        background: '#141414',
        color: '#e5e5e5',
        confirmButtonColor: '#ea580c',
        confirmButtonText: 'Oui, clôturer',
        showCancelButton: true,
        cancelButtonText: 'Annuler',
        cancelButtonColor: '#1f1f1f',
    }).then(r => {
        if (r.isConfirmed) {
            window.open('{{ route("caisse.cloturer") }}', '_blank');
        }
    });
}

// ── Graphique répartition par type ───────────────────────────────
(function() {
    const el = document.getElementById('c-repartition');
    if (!el) return;

    const data = {!! json_encode($dataRepartition) !!};

    new Chart(el, {
        type: 'doughnut',
        data: {
            labels: ['Sur place', 'À emporter', 'Livraison'],
            datasets: [{
                data: [data['Standard'], data['A emporter'], data['Livraison']],
                backgroundColor: ['rgba(234,88,12,0.8)', 'rgba(34,197,94,0.8)', 'rgba(96,165,250,0.8)'],
                borderColor: '#0d0d0d',
                borderWidth: 3,
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: { legend: { display: false } }
        }
    });
})();
</script>
@endpush