@extends('layouts.app')

@section('title', 'Historiques')
@section('page-title', 'Journal des Statuts')

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
        display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap;
    }
    .card-header-title {
        display: flex; align-items: center; gap: 8px;
        font-size: 13px; font-weight: 700; color: #e5e5e5;
    }

    .kpi {
        background: var(--cc-dark3);
        border: 1px solid var(--cc-border);
        border-radius: 13px;
        padding: 1rem;
        text-align: center;
    }
    .kpi-val   { font-size: 22px; font-weight: 700; margin-bottom: 4px; }
    .kpi-label { font-size: 10.5px; color: #444; }

    .btn {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 9px 16px; border-radius: 10px;
        font-size: 12px; font-weight: 600; cursor: pointer;
        transition: all .18s; border: none; font-family: inherit;
        text-decoration: none;
    }
    .btn-primary { background: var(--cc-orange); color: #fff; }
    .btn-primary:hover { background: #c2410c; }
    .btn-ghost { background: var(--cc-dark3); border: 1px solid var(--cc-border); color: #555; }
    .btn-ghost:hover { color: #ccc; border-color: #333; }
    .btn-sm { padding: 6px 12px; font-size: 11px; border-radius: 8px; }

    .sel {
        background: var(--cc-dark2); border: 1px solid var(--cc-border);
        border-radius: 10px; padding: 8px 12px; color: #e5e5e5;
        font-size: 12px; outline: none; font-family: inherit;
        transition: border-color .18s;
    }
    .sel:focus { border-color: var(--cc-orange); }

    .badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;
    }

    .data-table { width: 100%; border-collapse: collapse; }
    .data-table th {
        text-align: left; font-size: 10px; font-weight: 600; color: #444;
        text-transform: uppercase; letter-spacing: .5px;
        padding: 10px 16px; border-bottom: 1px solid #1a1a1a;
    }
    .data-table td {
        padding: 10px 16px; font-size: 12px; color: #888;
        border-bottom: 1px solid #141414; vertical-align: top;
    }
    .data-table tr:last-child td { border-bottom: none; }
    .data-table tr:hover td { background: #171717; }

    .icon-btn {
        width: 28px; height: 28px; border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        background: #1a1a1a; border: 1px solid var(--cc-border); color: #555;
        transition: all .15s; cursor: pointer;
    }
    .icon-btn.danger:hover {
        color: #f87171; border-color: rgba(239,68,68,.3); background: rgba(239,68,68,.08);
    }
    .icon-btn.disabled { color: #2a2a2a; cursor: not-allowed; }

    .pagination-wrap { padding: 14px 20px; border-top: 1px solid #1a1a1a; }

    /* Flux en direct */
    .live-item {
        display: flex; gap: 10px; padding: 10px 0; border-bottom: 1px solid #1a1a1a;
    }
    .live-item:last-child { border-bottom: none; }
    .live-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; margin-top: 5px; }
    .live-pulse { animation: pulse-live 1.8s infinite; }
    @keyframes pulse-live { 0%,100% { opacity: 1; } 50% { opacity: .3; } }

    .layout-grid { display: grid; grid-template-columns: 1fr 300px; gap: 16px; align-items: start; }

    @media (max-width: 1000px) {
        .layout-grid { grid-template-columns: 1fr; }
        .kpi-row { grid-template-columns: repeat(2,1fr) !important; }
        .filters-row { flex-direction: column; align-items: stretch !important; }
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
            <i class="fa-solid fa-clock-rotate-left" style="color:var(--cc-orange);margin-right:8px;"></i>
            Journal des Statuts
        </h2>
        <p style="font-size:12px;color:#444;margin:4px 0 0;">
            Historique de tous les changements de statut de commande
        </p>
    </div>
    <form method="GET" action="{{ route('admin.historiques.rapport') }}" style="display:flex;gap:8px;align-items:center;">
        <input type="date" name="debut" value="{{ now()->startOfMonth()->format('Y-m-d') }}" class="sel" required>
        <span style="color:#333;font-size:12px;">→</span>
        <input type="date" name="fin" value="{{ now()->format('Y-m-d') }}" class="sel" required>
        <button type="submit" class="btn btn-ghost btn-sm">
            <i class="fa-solid fa-chart-line"></i> Rapport
        </button>
    </form>
</div>

{{-- ══════════════════════════════════════════════════════════
     MESSAGES FLASH
══════════════════════════════════════════════════════════ --}}
@if(session('success'))
<div style="margin-bottom:16px;padding:12px 16px;border-radius:10px;font-size:12px;
            background:rgba(34,197,94,.07);border:1px solid rgba(34,197,94,.2);color:#22c55e;">
    <i class="fa-solid fa-circle-check" style="margin-right:6px;"></i>{{ session('success') }}
</div>
@endif
@if(session('error'))
<div style="margin-bottom:16px;padding:12px 16px;border-radius:10px;font-size:12px;
            background:rgba(239,68,68,.07);border:1px solid rgba(239,68,68,.2);color:#f87171;">
    <i class="fa-solid fa-circle-exclamation" style="margin-right:6px;"></i>{{ session('error') }}
</div>
@endif

{{-- ══════════════════════════════════════════════════════════
     STATS DU JOUR
══════════════════════════════════════════════════════════ --}}
<div class="kpi-row" style="display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:16px;">
    <div class="kpi">
        <div class="kpi-val" style="color:var(--cc-orange2);">{{ $statsJour['total_evenements'] }}</div>
        <div class="kpi-label">Événements aujourd'hui</div>
    </div>
    <div class="kpi">
        <div class="kpi-val" style="color:#eab308;">{{ $statsJour['commandes_en_attente'] }}</div>
        <div class="kpi-label">En attente</div>
    </div>
    <div class="kpi">
        <div class="kpi-val" style="color:#60a5fa;">{{ $statsJour['commandes_en_prep'] }}</div>
        <div class="kpi-label">En préparation</div>
    </div>
    <div class="kpi">
        <div class="kpi-val" style="color:#22c55e;">{{ $statsJour['commandes_terminees'] }}</div>
        <div class="kpi-label">Terminées aujourd'hui</div>
    </div>
    <div class="kpi">
        <div class="kpi-val" style="color:#f87171;">{{ $statsJour['commandes_annulees'] }}</div>
        <div class="kpi-label">Annulées aujourd'hui</div>
    </div>
</div>

<div class="layout-grid">

    {{-- ════════════════════════════════
         COLONNE PRINCIPALE
    ════════════════════════════════ --}}
    <div>
        {{-- Filtres --}}
        <div class="card">
            <div class="card-header">
                <form method="GET" action="{{ route('admin.historiques.index') }}"
                      class="filters-row" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;width:100%;">
                    <input type="text" name="q" value="{{ request('q') }}"
                           placeholder="Rechercher une référence..."
                           class="sel" style="flex:1;min-width:180px;">

                    <select name="statut" class="sel" onchange="this.form.submit()">
                        <option value="">Tous les statuts</option>
                        @foreach($statuts as $statut)
                        <option value="{{ $statut->intitule }}" {{ request('statut') === $statut->intitule ? 'selected' : '' }}>
                            {{ $statut->intitule }}
                        </option>
                        @endforeach
                    </select>

                    <select name="type" class="sel" onchange="this.form.submit()">
                        <option value="">Tous les types</option>
                        <option value="Standard" {{ request('type') === 'Standard' ? 'selected' : '' }}>Standard</option>
                        <option value="A emporter" {{ request('type') === 'A emporter' ? 'selected' : '' }}>À emporter</option>
                        <option value="Livraison" {{ request('type') === 'Livraison' ? 'selected' : '' }}>Livraison</option>
                    </select>

                    <input type="date" name="date" value="{{ request('date', today()->format('Y-m-d')) }}"
                           class="sel" onchange="this.form.submit()">

                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                    @if(request()->anyFilled(['q','statut','type']) || request('date') !== today()->format('Y-m-d'))
                    <a href="{{ route('admin.historiques.index') }}" class="btn btn-ghost btn-sm">Réinitialiser</a>
                    @endif
                </form>
            </div>

            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Heure</th>
                            <th>Commande</th>
                            <th>Type</th>
                            <th>Statut</th>
                            <th>Description</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($historiques as $h)
                        @php
                            $couleurs = ['yellow'=>'#eab308','blue'=>'#60a5fa','orange'=>'#f97316','green'=>'#22c55e','red'=>'#f87171','gray'=>'#9ca3af'];
                            $hex = $couleurs[$h->statut->couleur ?? 'gray'] ?? '#9ca3af';
                            $estNote = str_starts_with($h->description ?? '', '[NOTE]');
                            $iconType = match($h->commande->typecommande ?? null) {
                                'Livraison'  => 'fa-motorcycle',
                                'A emporter' => 'fa-bag-shopping',
                                default      => 'fa-chair',
                            };
                        @endphp
                        <tr>
                            <td style="white-space:nowrap;">{{ $h->created_at->format('H:i') }}</td>
                            <td>
                                @if($h->commande)
                                <a href="{{ route('commandes.historique', $h->commande->idcommande) }}"
                                   style="color:#e5e5e5;font-weight:600;text-decoration:none;">
                                    {{ $h->commande->reference }}
                                </a>
                                @if($h->commande->table)
                                <div style="font-size:10px;color:#444;">{{ $h->commande->table->intitule }}</div>
                                @elseif($h->commande->client)
                                <div style="font-size:10px;color:#444;">{{ $h->commande->client->prenom }} {{ $h->commande->client->nom }}</div>
                                @endif
                                @else
                                <span style="color:#333;">Commande supprimée</span>
                                @endif
                            </td>
                            <td><i class="fa-solid {{ $iconType }}" style="color:#444;"></i></td>
                            <td>
                                <span class="badge" style="background:{{ $hex }}22;color:{{ $hex }};">
                                    <i class="fa-solid {{ $h->statut->icone ?? 'fa-circle' }}" style="font-size:10px;"></i>
                                    {{ $h->statut->intitule ?? '—' }}
                                </span>
                            </td>
                            <td style="max-width:280px;">
                                @if($estNote)
                                <span style="color:#a78bfa;font-weight:600;">Note :</span>
                                {{ str_replace('[NOTE] ', '', $h->description) }}
                                @else
                                {{ $h->description }}
                                @endif
                            </td>
                            <td class="right">
                                @if($estNote)
                                <button onclick="confirmerSuppression({{ $h->idhistorique }})" class="icon-btn danger" title="Supprimer cette note">
                                    <i class="fa-solid fa-trash" style="font-size:11px;"></i>
                                </button>
                                @else
                                <span class="icon-btn disabled" title="Changement de statut — conservé">
                                    <i class="fa-solid fa-lock" style="font-size:10px;"></i>
                                </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align:center;padding:40px;color:#2a2a2a;">
                                Aucune entrée pour ces critères
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($historiques->hasPages())
            <div class="pagination-wrap">{{ $historiques->links() }}</div>
            @endif
        </div>
    </div>

    {{-- ════════════════════════════════
         COLONNE LATÉRALE — FLUX EN DIRECT
    ════════════════════════════════ --}}
    <div class="card">
        <div class="card-header">
            <div class="card-header-title">
                <span class="live-dot live-pulse" style="background:#22c55e;width:8px;height:8px;border-radius:50%;display:inline-block;"></span>
                Flux en direct
            </div>
            <span id="live-total" style="font-size:11px;color:#444;"></span>
        </div>
        <div style="padding:16px 20px;max-height:600px;overflow-y:auto;" id="live-feed">
            <div style="text-align:center;padding:20px;color:#333;font-size:12px;">Chargement...</div>
        </div>
    </div>

</div>

{{-- Formulaire caché suppression --}}
<form method="POST" id="deleteForm" style="display:none;">
    @csrf
    @method('DELETE')
</form>

@endsection

@push('scripts')
<script>
function confirmerSuppression(id) {
    Swal.fire({
        title: 'Supprimer cette note ?',
        html: '<div style="color:#666;font-size:13px;">Seules les notes manuelles peuvent être supprimées.</div>',
        icon: 'warning', iconColor: '#ef4444',
        background: '#141414', color: '#e5e5e5',
        confirmButtonColor: '#ef4444', confirmButtonText: 'Oui, supprimer',
        showCancelButton: true, cancelButtonText: 'Annuler', cancelButtonColor: '#1f1f1f',
    }).then(r => {
        if (r.isConfirmed) {
            const form = document.getElementById('deleteForm');
            form.action = `/admin/historiques/${id}`;
            form.submit();
        }
    });
}

// ── Flux en direct (polling 15s, cohérent avec le commentaire du contrôleur) ──
async function chargerFluxDirect() {
    try {
        const res  = await fetch('{{ route("admin.historiques.timeline") }}', { headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        if (!data.success) return;

        Swal.fire({
            toast: true, position: 'bottom-end',
            icon: 'success',
            title: `Timeline mis à jour — ${data.timestamp}`,
            timer: 1800, showConfirmButton: false,
            background: '#141414', color: '#e5e5e5', iconColor: '#22c55e',
        });

        document.getElementById('live-total').textContent = data.total_jour + " aujourd'hui";

        const feed = document.getElementById('live-feed');
        if (data.data.length === 0) {
            feed.innerHTML = '<div style="text-align:center;padding:20px;color:#2a2a2a;font-size:12px;">Aucun événement aujourd\'hui</div>';
            return;
        }

        feed.innerHTML = data.data.map(ev => `
            <div class="live-item">
                <span class="live-dot" style="background:${ev.text_color};"></span>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:11px;color:#333;">${ev.heure} · ${ev.reference}${ev.table ? ' · ' + ev.table : ''}</div>
                    <div style="font-size:12px;color:#ccc;margin-top:1px;">
                        <i class="fa-solid ${ev.icone}" style="color:${ev.text_color};margin-right:4px;font-size:10px;"></i>
                        ${ev.statut}
                    </div>
                </div>
            </div>
        `).join('');
    } catch (e) {
        console.error('Erreur flux en direct :', e);
    }
}

chargerFluxDirect();
setInterval(chargerFluxDirect, 15000);
</script>
@endpush