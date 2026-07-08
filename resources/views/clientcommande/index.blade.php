@extends('layouts.app')

@section('title', 'Mes commandes')
@section('page-title', 'Mes commandes')

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
    .btn-primary { background: var(--cc-orange); color: #fff; }
    .btn-primary:hover { background: #c2410c; }
    .btn-ghost { background: var(--cc-dark3); border: 1px solid var(--cc-border); color: #555; }
    .btn-ghost:hover { color: #ccc; border-color: #333; }
    .btn-sm { padding: 6px 12px; font-size: 11px; border-radius: 8px; }
    .btn-danger-ghost {
        background: rgba(239,68,68,.08); border: 1px solid rgba(239,68,68,.2); color: #f87171;
    }
    .btn-danger-ghost:hover { background: rgba(239,68,68,.15); }

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
    .b-attente  { background: rgba(234,179,8,.12);  color: #eab308; }
    .b-prep     { background: rgba(59,130,246,.12); color: #60a5fa; }
    .b-expediee { background: rgba(234,88,12,.12);  color: #f97316; }
    .b-servie, .b-livree { background: rgba(34,197,94,.12); color: #22c55e; }
    .b-annulee  { background: rgba(239,68,68,.12);  color: #f87171; }

    .cmd-card {
        background: var(--cc-dark2);
        border: 1px solid #1a1a1a;
        border-radius: 12px;
        padding: 14px 16px;
        margin-bottom: 8px;
        transition: border-color .18s;
    }
    .cmd-card:hover { border-color: #252525; }

    .pagination-wrap { padding: 14px 20px; border-top: 1px solid #1a1a1a; }

    @media (max-width: 700px) {
        .kpi-row { grid-template-columns: 1fr 1fr !important; }
    }
</style>
@endpush

@section('content')

{{-- ══════════════════════════════════════════════════════════
     EN-TÊTE
══════════════════════════════════════════════════════════ --}}
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:18px;">
    <div>
        <h2 style="font-size:18px;font-weight:700;color:#fff;margin:0;">
            <i class="fa-solid fa-receipt" style="color:var(--cc-orange);margin-right:8px;"></i>
            Mes commandes
        </h2>
        <p style="font-size:12px;color:#444;margin:4px 0 0;">
            Historique et suivi de vos commandes
        </p>
    </div>
    <a href="{{ route('mes-commandes.create') }}" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Nouvelle commande
    </a>
</div>

{{-- ══════════════════════════════════════════════════════════
     KPI
══════════════════════════════════════════════════════════ --}}
<div class="kpi-row" style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:16px;">
    <div class="kpi">
        <div class="kpi-val" style="color:#fff;">{{ $nbTotal }}</div>
        <div class="kpi-label">Commandes au total</div>
    </div>
    <div class="kpi">
        <div class="kpi-val" style="color:#eab308;">{{ $nbEnAttente }}</div>
        <div class="kpi-label">En attente</div>
    </div>
    <div class="kpi">
        <div class="kpi-val" style="color:var(--cc-orange2);">{{ number_format($totalDepense, 0, ',', ' ') }}</div>
        <div class="kpi-label">Total dépensé (FCFA)</div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     FILTRE STATUT
══════════════════════════════════════════════════════════ --}}
<form method="GET" action="{{ route('mes-commandes.index') }}" style="margin-bottom:16px;">
    <select name="statut" class="sel" onchange="this.form.submit()">
        <option value="">Tous les statuts</option>
        @foreach(['En attente','En préparation','Expédiée','Servie','Livrée','Annulée'] as $s)
        <option value="{{ $s }}" {{ request('statut') === $s ? 'selected' : '' }}>{{ $s }}</option>
        @endforeach
    </select>
</form>

{{-- ══════════════════════════════════════════════════════════
     LISTE
══════════════════════════════════════════════════════════ --}}
<div class="card" style="padding:16px;">
    @forelse($commandes as $cmd)
    @php
        $slug = match($cmd->statut_courant) {
            'En attente'=>'attente','En préparation'=>'prep',
            'Expédiée'=>'expediee','Servie'=>'servie',
            'Livrée'=>'livree','Annulée'=>'annulee', default=>'attente'
        };
        $iconType = match($cmd->typecommande) {
            'Livraison' => 'fa-motorcycle',
            default     => 'fa-bag-shopping',
        };
        $estModifiable = $cmd->statut_courant === 'En attente';
    @endphp
    <div class="cmd-card">
        <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
            <div style="width:40px;height:40px;border-radius:11px;flex-shrink:0;
                        background:#1a1a1a;display:flex;align-items:center;justify-content:center;">
                <i class="fa-solid {{ $iconType }}" style="font-size:15px;color:var(--cc-orange2);"></i>
            </div>
            <div style="flex:1;min-width:0;">
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                    <span style="font-size:13px;font-weight:700;color:#e5e5e5;">{{ $cmd->reference }}</span>
                    <span class="badge b-{{ $slug }}">{{ $cmd->statut_courant }}</span>
                </div>
                <div style="font-size:11px;color:#444;margin-top:3px;">
                    {{ $cmd->datecommande?->format('d/m/Y') }} · {{ $cmd->heurecommande }}
                    · {{ $cmd->lignes->count() }} article(s)
                    · {{ $cmd->typecommande === 'A emporter' ? 'À emporter' : $cmd->typecommande }}
                </div>
            </div>
            <div style="text-align:right;flex-shrink:0;">
                <div style="font-size:14px;font-weight:700;color:#fff;">
                    {{ number_format($cmd->montant, 0, ',', ' ') }} FCFA
                </div>
            </div>
            <div style="display:flex;gap:6px;flex-shrink:0;">
                <a href="{{ route('mes-commandes.show', $cmd->idcommande) }}" class="btn btn-ghost btn-sm" title="Voir le détail">
                    <i class="fa-solid fa-eye"></i>
                </a>
                @if($estModifiable)
                <a href="{{ route('mes-commandes.edit', $cmd->idcommande) }}" class="btn btn-ghost btn-sm" title="Modifier">
                    <i class="fa-solid fa-pen"></i>
                </a>
                <button onclick="annulerCommande({{ $cmd->idcommande }}, '{{ $cmd->reference }}')"
                        class="btn btn-danger-ghost btn-sm" title="Annuler">
                    <i class="fa-solid fa-xmark"></i>
                </button>
                @else
                <a href="{{ route('mes-commandes.recommander', $cmd->idcommande) }}" class="btn btn-ghost btn-sm" title="Recommander">
                    <i class="fa-solid fa-rotate-right"></i>
                </a>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div style="text-align:center;padding:60px 20px;">
        <i class="fa-solid fa-receipt" style="font-size:40px;color:#1f1f1f;display:block;margin-bottom:14px;"></i>
        <p style="font-size:15px;font-weight:600;color:#333;margin-bottom:6px;">Aucune commande pour l'instant</p>
        <p style="font-size:12px;color:#252525;margin-bottom:20px;">Passez votre première commande dès maintenant.</p>
        <a href="{{ route('mes-commandes.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Nouvelle commande
        </a>
    </div>
    @endforelse

    @if($commandes->hasPages())
    <div class="pagination-wrap">{{ $commandes->links() }}</div>
    @endif
</div>

{{-- Formulaire caché annulation --}}
<form method="POST" id="annulerForm" style="display:none;">
    @csrf
    @method('PATCH')
</form>

@endsection

@push('scripts')
<script>
function annulerCommande(id, reference) {
    Swal.fire({
        title: `Annuler ${reference} ?`,
        html: '<div style="color:#666;font-size:13px;">Cette action est irréversible.</div>',
        icon: 'warning', iconColor: '#ef4444',
        background: '#141414', color: '#e5e5e5',
        confirmButtonColor: '#ef4444', confirmButtonText: 'Oui, annuler',
        showCancelButton: true, cancelButtonText: 'Retour', cancelButtonColor: '#1f1f1f',
    }).then(r => {
        if (r.isConfirmed) {
            const form = document.getElementById('annulerForm');
            form.action = `/mes-commandes/${id}/annuler`;
            form.submit();
        }
    });
}
</script>
@endpush