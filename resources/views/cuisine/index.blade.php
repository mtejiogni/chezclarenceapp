@extends('layouts.app')

@section('title', 'Cuisine')
@section('page-title', 'Écran Cuisine')

@push('styles')
<style>
    :root {
        --cc-orange:  #ea580c;
        --cc-orange2: #f97316;
        --cc-dark2:   #0d0d0d;
        --cc-dark3:   #141414;
        --cc-border:  #1f1f1f;
    }

    .kds-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 260px;
        gap: 18px;
        align-items: start;
    }
    @media (max-width: 1100px) {
        .kds-grid { grid-template-columns: 1fr 1fr; }
        .kds-side { grid-column: 1 / -1; }
    }
    @media (max-width: 700px) {
        .kds-grid { grid-template-columns: 1fr; }
    }

    .kds-col {
        background: var(--cc-dark3);
        border: 1px solid var(--cc-border);
        border-radius: 14px;
        overflow: hidden;
    }
    .kds-col-header {
        padding: 14px 18px;
        display: flex; align-items: center; justify-content: space-between;
        border-bottom: 1px solid #1a1a1a;
    }
    .kds-col-title {
        display: flex; align-items: center; gap: 8px;
        font-size: 13px; font-weight: 700; letter-spacing: .5px;
        text-transform: uppercase;
    }
    .kds-col-count {
        font-size: 11px; font-weight: 700; padding: 2px 9px; border-radius: 10px;
    }
    .kds-col-body {
        padding: 14px; display: flex; flex-direction: column; gap: 10px;
    }

    .bon-card {
        background: var(--cc-dark2);
        border: 1px solid #1a1a1a;
        border-left: 3px solid #333;
        border-radius: 10px;
        padding: 12px 14px;
        transition: border-color .2s, opacity .2s;
    }
    .bon-card.urgent { border-left-color: #ef4444; }

    .bon-head {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 8px;
    }
    .bon-ref { font-size: 14px; font-weight: 700; color: #e5e5e5; }
    .bon-time { font-size: 11px; color: #666; }
    .bon-time.urgent { color: #ef4444; font-weight: 700; }

    .bon-meta {
        display: flex; align-items: center; gap: 6px; flex-wrap: wrap;
        margin-bottom: 8px;
    }
    .bon-type {
        display: inline-flex; align-items: center; gap: 4px;
        font-size: 10px; padding: 2px 8px; border-radius: 6px;
        background: #1a1a1a;
    }
    .bon-table {
        font-size: 10px; padding: 2px 8px; border-radius: 6px;
        background: #1a1a1a; color: #666;
    }

    .bon-lignes { margin-bottom: 10px; }
    .bon-ligne {
        display: flex; justify-content: space-between; gap: 8px;
        font-size: 12px; color: #ccc; padding: 3px 0;
    }
    .bon-ligne .qte {
        font-weight: 700; color: var(--cc-orange2); flex-shrink: 0;
    }

    .btn-bon {
        width: 100%; padding: 9px; border-radius: 9px; border: none;
        font-size: 12px; font-weight: 700; cursor: pointer;
        display: flex; align-items: center; justify-content: center; gap: 7px;
        transition: opacity .15s; font-family: inherit;
    }
    .btn-bon:hover { opacity: .85; }
    .btn-bon:disabled { opacity: .5; cursor: not-allowed; }
    .btn-demarrer { background: rgba(96,165,250,.15); color: #60a5fa; border: 1px solid rgba(96,165,250,.3); }
    .btn-pret     { background: #22c55e; color: #fff; }

    .kds-empty { text-align: center; padding: 36px 16px; color: #2a2a2a; }
    .kds-empty i { font-size: 30px; display: block; margin-bottom: 10px; }

    .top-plat-row {
        display: flex; align-items: center; justify-content: space-between;
        padding: 8px 0; border-bottom: 1px solid #1a1a1a; font-size: 12px;
    }
    .top-plat-row:last-child { border-bottom: none; }
</style>
@endpush

@section('content')

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:18px;">
    <div>
        <h2 style="font-size:18px;font-weight:700;color:#fff;margin:0;">
            <i class="fa-solid fa-fire-burner" style="color:var(--cc-orange);margin-right:8px;"></i>
            Écran Cuisine
        </h2>
        <p style="font-size:12px;color:#444;margin:4px 0 0;">
            <span style="color:#22c55e;">{{ $terminees }}</span> commande(s) terminée(s) aujourd'hui
        </p>
    </div>
    <div style="display:flex;align-items:center;gap:8px;font-size:11px;color:#444;">
        <span style="width:8px;height:8px;border-radius:50%;background:#22c55e;display:inline-block;
                     animation:pulse 1.6s ease-in-out infinite;"></span>
        Actualisation automatique
    </div>
</div>

<div class="kds-grid">

    {{-- ═══════════════════════════════
         COLONNE EN ATTENTE
    ═══════════════════════════════ --}}
    <div class="kds-col">
        <div class="kds-col-header">
            <div class="kds-col-title" style="color:#eab308;">
                <i class="fa-solid fa-clock"></i> En attente
            </div>
            <span class="kds-col-count" style="background:rgba(234,179,8,.15);color:#eab308;">
                {{ $commandesEnAttente->count() }}
            </span>
        </div>
        <div class="kds-col-body">
            @forelse($commandesEnAttente as $cmd)
            @php
                $minutes = $cmd->heurecommande
                    ? now()->diffInMinutes(\Carbon\Carbon::parse($cmd->datecommande->format('Y-m-d') . ' ' . $cmd->heurecommande))
                    : 0;
                $urgent = $minutes >= 10;
                $iconType = match($cmd->typecommande) {
                    'Livraison'  => 'fa-motorcycle',
                    'A emporter' => 'fa-bag-shopping',
                    default      => 'fa-chair',
                };
                $couleurType = match($cmd->typecommande) {
                    'Livraison'  => '#f97316',
                    'A emporter' => '#22c55e',
                    default      => '#60a5fa',
                };
                $labelType = $cmd->typecommande === 'A emporter' ? 'À emporter' : $cmd->typecommande;
            @endphp
            <div class="bon-card {{ $urgent ? 'urgent' : '' }}" data-id="{{ $cmd->idcommande }}">
                <div class="bon-head">
                    <span class="bon-ref">{{ $cmd->reference }}</span>
                    <span class="bon-time {{ $urgent ? 'urgent' : '' }}">
                        <i class="fa-solid fa-stopwatch"></i> {{ $minutes }} min
                    </span>
                </div>
                <div class="bon-meta">
                    <span class="bon-type" style="color:{{ $couleurType }};">
                        <i class="fa-solid {{ $iconType }}"></i> {{ $labelType }}
                    </span>
                    @if($cmd->table)
                    <span class="bon-table"><i class="fa-solid fa-chair"></i> {{ $cmd->table->intitule }}</span>
                    @endif
                </div>
                <div class="bon-lignes">
                    @foreach($cmd->lignes as $ligne)
                    <div class="bon-ligne">
                        <span>{{ $ligne->menu->intitule ?? 'Plat supprimé' }}</span>
                        <span class="qte">×{{ $ligne->quantite }}</span>
                    </div>
                    @endforeach
                </div>
                <button class="btn-bon btn-demarrer"
                        data-url="{{ route('cuisine.prendre-en-charge', $cmd->idcommande) }}"
                        onclick="agirSurBon(this)">
                    <i class="fa-solid fa-play"></i> Démarrer la préparation
                </button>
            </div>
            @empty
            <div class="kds-empty">
                <i class="fa-solid fa-circle-check" style="color:#22c55e;"></i>
                <p style="font-size:13px;">Rien en attente</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- ═══════════════════════════════
         COLONNE EN PRÉPARATION
    ═══════════════════════════════ --}}
    <div class="kds-col">
        <div class="kds-col-header">
            <div class="kds-col-title" style="color:#60a5fa;">
                <i class="fa-solid fa-fire-burner"></i> En préparation
            </div>
            <span class="kds-col-count" style="background:rgba(96,165,250,.15);color:#60a5fa;">
                {{ $enPreparation->count() }}
            </span>
        </div>
        <div class="kds-col-body">
            @forelse($enPreparation as $cmd)
            @php
                $minutes = $cmd->heurecommande
                    ? now()->diffInMinutes(\Carbon\Carbon::parse($cmd->datecommande->format('Y-m-d') . ' ' . $cmd->heurecommande))
                    : 0;
                $urgent = $minutes >= 15;
                $iconType = match($cmd->typecommande) {
                    'Livraison'  => 'fa-motorcycle',
                    'A emporter' => 'fa-bag-shopping',
                    default      => 'fa-chair',
                };
                $couleurType = match($cmd->typecommande) {
                    'Livraison'  => '#f97316',
                    'A emporter' => '#22c55e',
                    default      => '#60a5fa',
                };
                $labelType = $cmd->typecommande === 'A emporter' ? 'À emporter' : $cmd->typecommande;
            @endphp
            <div class="bon-card {{ $urgent ? 'urgent' : '' }}" data-id="{{ $cmd->idcommande }}">
                <div class="bon-head">
                    <span class="bon-ref">{{ $cmd->reference }}</span>
                    <span class="bon-time {{ $urgent ? 'urgent' : '' }}">
                        <i class="fa-solid fa-stopwatch"></i> {{ $minutes }} min
                    </span>
                </div>
                <div class="bon-meta">
                    <span class="bon-type" style="color:{{ $couleurType }};">
                        <i class="fa-solid {{ $iconType }}"></i> {{ $labelType }}
                    </span>
                    @if($cmd->table)
                    <span class="bon-table"><i class="fa-solid fa-chair"></i> {{ $cmd->table->intitule }}</span>
                    @endif
                </div>
                <div class="bon-lignes">
                    @foreach($cmd->lignes as $ligne)
                    <div class="bon-ligne">
                        <span>{{ $ligne->menu->intitule ?? 'Plat supprimé' }}</span>
                        <span class="qte">×{{ $ligne->quantite }}</span>
                    </div>
                    @endforeach
                </div>
                <button class="btn-bon btn-pret"
                        data-url="{{ route('cuisine.prete', $cmd->idcommande) }}"
                        onclick="agirSurBon(this)">
                    <i class="fa-solid fa-check"></i> Marquer prêt
                </button>
            </div>
            @empty
            <div class="kds-empty">
                <i class="fa-solid fa-circle-check" style="color:#22c55e;"></i>
                <p style="font-size:13px;">Rien en préparation</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- ═══════════════════════════════
         COLONNE LATÉRALE — TOP PLATS
    ═══════════════════════════════ --}}
    <div class="kds-col kds-side">
        <div class="kds-col-header">
            <div class="kds-col-title" style="color:var(--cc-orange);">
                <i class="fa-solid fa-ranking-star"></i> Top plats du jour
            </div>
        </div>
        <div class="kds-col-body">
            @forelse($platsTop as $plat)
            <div class="top-plat-row">
                <span style="color:#ccc;">{{ $plat->intitule }}</span>
                <span style="font-weight:700;color:var(--cc-orange2);">{{ $plat->total }}</span>
            </div>
            @empty
            <div class="kds-empty">
                <p style="font-size:12px;">Aucune vente aujourd'hui</p>
            </div>
            @endforelse
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
// ══════════════════════════════════════════════════════════════
// ÉCRAN CUISINE — s'appuie sur les routes réelles fournies :
//   GET   cuisine.index              → cette page
//   PATCH cuisine.prendre-en-charge  → En attente → En préparation
//   PATCH cuisine.prete              → En préparation → Servie/Expédiée
// L'URL complète de chaque action est injectée côté serveur via
// data-url (route() Blade), plus besoin de reconstruire l'URL en JS.
// ══════════════════════════════════════════════════════════════

const CSRF_TOKEN  = document.querySelector('meta[name="csrf-token"]')?.content;
const REFRESH_MS  = 20000; // 20s

async function agirSurBon(btn) {
    const url = btn.dataset.url;
    btn.disabled = true;
    const original = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> ...';

    try {
        const res = await fetch(url, {
            method: 'PATCH',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
            },
        });
        const data = await res.json();

        if (!data.success) {
            Swal.fire({
                toast: true, position: 'bottom-end', icon: 'error',
                title: data.message, timer: 2500, showConfirmButton: false,
                background: '#141414', color: '#e5e5e5', iconColor: '#ef4444',
            });
            btn.disabled = false;
            btn.innerHTML = original;
            return;
        }

        // Petit effet de sortie avant rechargement pour un ressenti fluide
        const card = btn.closest('.bon-card');
        if (card) card.style.opacity = '0.3';

        setTimeout(() => window.location.reload(), 300);

    } catch (e) {
        console.error('Erreur action bon :', e);
        btn.disabled = false;
        btn.innerHTML = original;
    }
}

// ── Rafraîchissement automatique de la page ────────────────────
setInterval(() => window.location.reload(), REFRESH_MS);
</script>
@endpush