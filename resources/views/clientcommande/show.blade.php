@extends('layouts.app')

@section('title', $commande->reference)
@section('page-title', 'Détail de ma commande')

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
        padding: 14px 20px; border-bottom: 1px solid #1a1a1a;
        display: flex; align-items: center; justify-content: space-between;
    }
    .card-header-title {
        display: flex; align-items: center; gap: 8px;
        font-size: 13px; font-weight: 700; color: #e5e5e5;
    }
    .card-body { padding: 20px; }

    .btn {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 10px 16px; border-radius: 10px;
        font-size: 12.5px; font-weight: 700; cursor: pointer;
        transition: all .18s; border: none; font-family: inherit;
        text-decoration: none;
    }
    .btn-primary { background: var(--cc-orange); color: #fff; }
    .btn-primary:hover { background: #c2410c; }
    .btn-ghost { background: var(--cc-dark3); border: 1px solid var(--cc-border); color: #666; }
    .btn-ghost:hover { color: #ccc; border-color: #333; }
    .btn-danger { background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.25); color: #f87171; }
    .btn-danger:hover { background: rgba(239,68,68,.18); }

    .badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700;
    }
    .b-attente  { background: rgba(234,179,8,.12);  color: #eab308; }
    .b-prep     { background: rgba(59,130,246,.12); color: #60a5fa; }
    .b-expediee { background: rgba(234,88,12,.12);  color: #f97316; }
    .b-servie, .b-livree { background: rgba(34,197,94,.12); color: #22c55e; }
    .b-annulee  { background: rgba(239,68,68,.12);  color: #f87171; }

    .info-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 10px 0; border-bottom: 1px solid #1a1a1a; font-size: 12.5px;
    }
    .info-row:last-child { border-bottom: none; }
    .info-label { color: #555; }
    .info-value { color: #e5e5e5; font-weight: 600; text-align: right; }

    .ligne-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 10px 0; border-bottom: 1px solid #1a1a1a; font-size: 12.5px;
    }
    .ligne-row:last-child { border-bottom: none; }

    .timeline-status {
        display: flex; align-items: center; gap: 10px; margin-bottom: 4px;
    }
</style>
@endpush

@section('content')

@php
    $slug = match($commande->statut_courant) {
        'En attente'=>'attente','En préparation'=>'prep',
        'Expédiée'=>'expediee','Servie'=>'servie',
        'Livrée'=>'livree','Annulée'=>'annulee', default=>'attente'
    };
    $estModifiable = $commande->statut_courant === 'En attente';
@endphp

<div style="display:flex;align-items:center;gap:12px;margin-bottom:18px;flex-wrap:wrap;">
    <a href="{{ route('mes-commandes.index') }}"
       style="width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;
              background:var(--cc-dark3);border:1px solid var(--cc-border);color:#555;text-decoration:none;flex-shrink:0;">
        <i class="fa-solid fa-arrow-left" style="font-size:13px;"></i>
    </a>
    <div style="flex:1;min-width:0;">
        <h2 style="font-size:18px;font-weight:700;color:#fff;margin:0;">{{ $commande->reference }}</h2>
        <p style="font-size:12px;color:#444;margin:4px 0 0;">
            {{ $commande->datecommande?->format('d/m/Y') }} à {{ $commande->heurecommande }}
        </p>
    </div>
    <span class="badge b-{{ $slug }}">{{ $commande->statut_courant }}</span>
</div>

{{-- Actions --}}
<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;">
    @if($estModifiable)
    <a href="{{ route('mes-commandes.edit', $commande->idcommande) }}" class="btn btn-primary">
        <i class="fa-solid fa-pen"></i> Modifier
    </a>
    <button onclick="confirmerAnnulation()" class="btn btn-danger">
        <i class="fa-solid fa-xmark"></i> Annuler la commande
    </button>
    @else
    <a href="{{ route('mes-commandes.recommander', $commande->idcommande) }}" class="btn btn-primary">
        <i class="fa-solid fa-rotate-right"></i> Recommander la même chose
    </a>
    @endif
</div>

{{-- Infos générales --}}
<div class="card">
    <div class="card-header">
        <div class="card-header-title">
            <i class="fa-solid fa-circle-info" style="color:var(--cc-orange);"></i>
            Informations
        </div>
    </div>
    <div class="card-body" style="padding:8px 20px;">
        <div class="info-row">
            <span class="info-label">Type</span>
            <span class="info-value">
                <i class="fa-solid {{ $commande->typecommande === 'Livraison' ? 'fa-motorcycle' : 'fa-bag-shopping' }}" style="margin-right:5px;color:var(--cc-orange2);"></i>
                {{ $commande->typecommande === 'A emporter' ? 'À emporter' : $commande->typecommande }}
            </span>
        </div>
        @if($commande->typecommande === 'Livraison' && $commande->adresse)
        <div class="info-row">
            <span class="info-label">Adresse de livraison</span>
            <span class="info-value">{{ $commande->adresse }}</span>
        </div>
        @endif
        @if($commande->consignes)
        <div class="info-row">
            <span class="info-label">Consignes</span>
            <span class="info-value">{{ $commande->consignes }}</span>
        </div>
        @endif
        <div class="info-row">
            <span class="info-label">Mode de paiement</span>
            <span class="info-value">{{ $commande->mode_paiement ?? 'À définir' }}</span>
        </div>
    </div>
</div>

{{-- Articles --}}
<div class="card">
    <div class="card-header">
        <div class="card-header-title">
            <i class="fa-solid fa-list" style="color:var(--cc-orange);"></i>
            Articles ({{ $commande->lignes->count() }})
        </div>
    </div>
    <div class="card-body" style="padding:8px 20px;">
        @foreach($commande->lignes as $ligne)
        <div class="ligne-row">
            <div>
                <div style="color:#e5e5e5;font-weight:600;">{{ $ligne->menu->intitule ?? 'Plat supprimé' }}</div>
                <div style="color:#555;font-size:11px;margin-top:2px;">
                    {{ $ligne->quantite }} × {{ number_format($ligne->menu->pu ?? 0, 0, ',', ' ') }} FCFA
                </div>
            </div>
            <span style="color:#fff;font-weight:700;">{{ number_format($ligne->prix, 0, ',', ' ') }} FCFA</span>
        </div>
        @endforeach

        <div style="display:flex;justify-content:space-between;padding-top:14px;margin-top:6px;border-top:1px solid #252525;">
            <span style="font-size:14px;font-weight:700;color:#e5e5e5;">Total</span>
            <span style="font-size:18px;font-weight:700;color:var(--cc-orange2);">
                {{ number_format($commande->montant, 0, ',', ' ') }} FCFA
            </span>
        </div>
    </div>
</div>

{{-- Formulaire caché annulation --}}
<form method="POST" action="{{ route('mes-commandes.annuler', $commande->idcommande) }}" id="annulerForm" style="display:none;">
    @csrf
    @method('PATCH')
</form>

@endsection

@push('scripts')
<script>
function confirmerAnnulation() {
    Swal.fire({
        title: 'Annuler cette commande ?',
        html: '<div style="color:#666;font-size:13px;">Cette action est irréversible.</div>',
        icon: 'warning', iconColor: '#ef4444',
        background: '#141414', color: '#e5e5e5',
        confirmButtonColor: '#ef4444', confirmButtonText: 'Oui, annuler',
        showCancelButton: true, cancelButtonText: 'Retour', cancelButtonColor: '#1f1f1f',
    }).then(r => {
        if (r.isConfirmed) {
            document.getElementById('annulerForm').submit();
        }
    });
}
</script>
@endpush