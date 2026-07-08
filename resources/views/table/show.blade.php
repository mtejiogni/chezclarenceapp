@extends('layouts.app')

@section('title', 'Table : ' . $table->intitule)
@section('page-title', 'Détail table')

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

    .badge-libre   { background: rgba(34,197,94,.12);  color: #22c55e; }
    .badge-occupee { background: rgba(234,88,12,.12);  color: #f97316; }
    .badge-attente { background: rgba(234,179,8,.12);  color: #eab308; }
    .badge-prep    { background: rgba(59,130,246,.12); color: #60a5fa; }
    .badge-servie  { background: rgba(34,197,94,.12);  color: #22c55e; }
    .badge-annulee { background: rgba(239,68,68,.12);  color: #f87171; }

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
    .btn-danger:hover { background: #ef4444; color: #fff; }

    .btn-success {
        background: rgba(34,197,94,.1);
        border: 1px solid rgba(34,197,94,.2);
        color: #22c55e;
    }
    .btn-success:hover { background: #22c55e; color: #fff; }

    .btn-sm { padding: 5px 10px; font-size: 11px; border-radius: 7px; }

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

    /* ── Commande en cours ── */
    .commande-active-box {
        background: rgba(234,88,12,.06);
        border: 1px solid rgba(234,88,12,.2);
        border-radius: 12px;
        padding: 16px;
    }

    /* ── Ligne article ── */
    .ligne-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #1a1a1a;
        font-size: 12px;
    }

    .ligne-row:last-child { border-bottom: none; }

    /* ── Historique ── */
    .hist-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid #1a1a1a;
    }

    .hist-row:last-child { border-bottom: none; }

    /* ── Indicateur statut animé ── */
    @keyframes pulse-green {
        0%,100% { box-shadow: 0 0 0 0 rgba(34,197,94,.4); }
        50%      { box-shadow: 0 0 0 6px rgba(34,197,94,0); }
    }

    @keyframes pulse-orange {
        0%,100% { box-shadow: 0 0 0 0 rgba(234,88,12,.5); }
        50%      { box-shadow: 0 0 0 6px rgba(234,88,12,0); }
    }

    .dot-libre   { background: #22c55e; animation: pulse-green  2s infinite; }
    .dot-occupee { background: var(--cc-orange); animation: pulse-orange 1.5s infinite; }

    /* ── Chart ── */
    .chart-wrap { position: relative; height: 160px; }
</style>
@endpush

@section('content')

{{-- Fil d'Ariane --}}
<div style="display:flex;align-items:center;gap:8px;
            font-size:12px;color:#444;margin-bottom:18px;">
    <a href="{{ route('admin.tables.index') }}"
       style="color:#555;text-decoration:none;
              display:flex;align-items:center;gap:5px;"
       onmouseover="this.style.color='#f97316'"
       onmouseout="this.style.color='#555'">
        <i class="fa-solid fa-chair"></i>
        Tables
    </a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;color:#333;"></i>
    <span style="color:#666;">{{ $table->intitule }}</span>
</div>

{{-- ══════════════════════════════════════════════════════════
     EN-TÊTE
══════════════════════════════════════════════════════════ --}}
<div style="display:flex;align-items:flex-start;justify-content:space-between;
            flex-wrap:wrap;gap:12px;margin-bottom:20px;">

    {{-- Nom + statut --}}
    <div style="display:flex;align-items:center;gap:14px;">

        {{-- Icône centrale --}}
        <div style="width:56px;height:56px;border-radius:14px;flex-shrink:0;
                    display:flex;align-items:center;justify-content:center;
                    background:{{ $commandesActives->isNotEmpty() ? 'rgba(234,88,12,.12)' : 'rgba(34,197,94,.1)' }};
                    border:1px solid {{ $commandesActives->isNotEmpty() ? 'rgba(234,88,12,.25)' : 'rgba(34,197,94,.2)' }};
                    position:relative;">
            <i class="fa-solid fa-chair"
               style="font-size:22px;color:{{ $commandesActives->isNotEmpty() ? '#f97316' : '#22c55e' }};"></i>
            {{-- Point animé --}}
            <div style="width:10px;height:10px;border-radius:50%;
                        position:absolute;top:-3px;right:-3px;
                        border:2px solid #080808;"
                 class="{{ $commandesActives->isNotEmpty() ? 'dot-occupee' : 'dot-libre' }}"></div>
        </div>

        <div>
            <h1 style="font-size:20px;font-weight:700;color:#fff;margin:0 0 6px;">
                {{ $table->intitule }}
            </h1>
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                <span class="badge {{ $commandesActives->isNotEmpty() ? 'badge-occupee' : 'badge-libre' }}">
                    <i class="fa-solid {{ $commandesActives->isNotEmpty() ? 'fa-utensils' : 'fa-circle-check' }}"
                       style="font-size:9px;"></i>
                    {{ $commandesActives->isNotEmpty() ? 'Occupée' : 'Libre' }}
                </span>
                @if($table->description)
                <span style="font-size:11px;color:#444;">{{ $table->description }}</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">

        {{-- Nouvelle commande (toujours possible, même si occupée) --}}
        @if(in_array(auth()->user()->role, ['Administrateur','Caissier','Serveur']))
        <a href="{{ route('commandes.create') }}?table={{ $table->idtable }}"
           class="btn btn-success btn-sm">
            <i class="fa-solid fa-plus"></i>
            Nouvelle commande
        </a>
        @endif

        {{-- Voir les commandes actives --}}
        @if($commandesActives->isNotEmpty())
        <a href="{{ route('commandes.index') }}?table={{ $table->idtable }}"
           class="btn btn-primary btn-sm">
            <i class="fa-solid fa-receipt"></i>
            Voir les commandes ({{ $commandesActives->count() }})
        </a>
        @endif

        {{-- Libérer (Admin) --}}
        @if($commandesActives->isNotEmpty() && auth()->user()->role === 'Administrateur')
        <button onclick="libererTable()"
                class="btn btn-sm"
                style="background:rgba(234,179,8,.1);border:1px solid rgba(234,179,8,.2);color:#eab308;">
            <i class="fa-solid fa-unlock"></i>
            Libérer
        </button>
        @endif

        {{-- Modifier --}}
        <a href="{{ route('admin.tables.edit', $table->idtable) }}"
           class="btn btn-ghost btn-sm">
            <i class="fa-solid fa-pen-to-square"></i>
            Modifier
        </a>

        {{-- Supprimer --}}
        @if($commandesActives->isEmpty())
        <button onclick="confirmerSuppression()"
                class="btn btn-danger btn-sm">
            <i class="fa-solid fa-trash"></i>
            Supprimer
        </button>
        @endif

        {{-- Retour --}}
        <a href="{{ route('admin.tables.index') }}"
           class="btn btn-ghost btn-sm">
            <i class="fa-solid fa-arrow-left"></i>
            Retour
        </a>
    </div>
</div>

{{-- Formulaires cachés --}}
<form method="POST"
      action="{{ route('admin.tables.liberer', $table->idtable) }}"
      id="libererForm" style="display:none;">
    @csrf @method('PATCH')
</form>

<form method="POST"
      action="{{ route('admin.tables.destroy', $table->idtable) }}"
      id="deleteForm" style="display:none;">
    @csrf @method('DELETE')
</form>

{{-- ══════════════════════════════════════════════════════════
     KPIs STATISTIQUES
══════════════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px;">

    <div class="kpi">
        <div class="kpi-val" style="color:#60a5fa;">
            {{ number_format($stats['total_commandes'], 0, ',', ' ') }}
        </div>
        <div class="kpi-label">
            <i class="fa-solid fa-receipt" style="margin-right:3px;"></i>
            Commandes totales
        </div>
    </div>

    <div class="kpi">
        <div class="kpi-val" style="color:#22c55e;">
            {{ number_format($stats['ca_total'], 0, ',', ' ') }}
        </div>
        <div class="kpi-label">
            <i class="fa-solid fa-money-bill-wave" style="margin-right:3px;"></i>
            CA total (FCFA)
        </div>
    </div>

    <div class="kpi">
        <div class="kpi-val" style="color:#f97316;">
            {{ number_format($stats['ca_mois'], 0, ',', ' ') }}
        </div>
        <div class="kpi-label">
            <i class="fa-solid fa-calendar" style="margin-right:3px;"></i>
            CA ce mois (FCFA)
        </div>
    </div>

    <div class="kpi">
        <div class="kpi-val" style="color:#a855f7;">
            {{ number_format($stats['panier_moyen'], 0, ',', ' ') }}
        </div>
        <div class="kpi-label">
            <i class="fa-solid fa-cart-shopping" style="margin-right:3px;"></i>
            Panier moyen (FCFA)
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     CORPS : 2 colonnes
══════════════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:1fr 300px;gap:16px;align-items:start;">

    {{-- ════════════════════════════
         COLONNE GAUCHE
    ════════════════════════════ --}}
    <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- ── Commandes actives en cours ── --}}
        @if($commandesActives->isNotEmpty())
        <div class="card">
            <div class="card-header">
                <div class="card-header-title">
                    <i class="fa-solid fa-fire-burner" style="color:var(--cc-orange);"></i>
                    Commandes en cours
                    <span style="font-size:11px;font-weight:400;color:#444;">
                        ({{ $commandesActives->count() }} · {{ number_format($montantEnCoursTotal, 0, ',', ' ') }} FCFA)
                    </span>
                </div>
            </div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:16px;">

                @foreach($commandesActives as $commandeActive)
                <div class="commande-active-box" style="{{ !$loop->last ? 'padding-bottom:16px;border-bottom:1px solid #1a1a1a;' : '' }}">

                    <div style="display:flex;align-items:center;justify-content:flex-end;margin-bottom:10px;">
                        <a href="{{ route('commandes.show', $commandeActive->idcommande) }}"
                           class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-eye" style="font-size:10px;"></i>
                            Détail complet
                        </a>
                    </div>

                    {{-- Infos commande --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;
                                gap:10px;margin-bottom:14px;">

                        <div>
                            <div style="font-size:10px;color:#444;text-transform:uppercase;
                                        letter-spacing:.5px;margin-bottom:3px;">Référence</div>
                            <div style="font-size:13px;font-weight:700;color:#e5e5e5;
                                        font-family:monospace;">
                                {{ $commandeActive->reference }}
                            </div>
                        </div>

                        <div>
                            <div style="font-size:10px;color:#444;text-transform:uppercase;
                                        letter-spacing:.5px;margin-bottom:3px;">Statut</div>
                            @php
                                $sc = match($commandeActive->statut_courant) {
                                    'En attente'     => 'attente',
                                    'En préparation' => 'prep',
                                    'Servie'         => 'servie',
                                    'Annulée'        => 'annulee',
                                    default          => 'attente',
                                };
                            @endphp
                            <span class="badge badge-{{ $sc }}">
                                {{ $commandeActive->statut_courant }}
                            </span>
                        </div>

                        <div>
                            <div style="font-size:10px;color:#444;text-transform:uppercase;
                                        letter-spacing:.5px;margin-bottom:3px;">Heure</div>
                            <div style="font-size:13px;color:#e5e5e5;">
                                {{ $commandeActive->heurecommande }}
                            </div>
                        </div>

                        <div>
                            <div style="font-size:10px;color:#444;text-transform:uppercase;
                                        letter-spacing:.5px;margin-bottom:3px;">Paiement</div>
                            <div style="font-size:13px;color:#e5e5e5;">
                                {{ $commandeActive->mode_paiement ?? 'Espèces' }}
                            </div>
                        </div>
                    </div>

                    {{-- Articles --}}
                    <div style="margin-bottom:12px;">
                        <div style="font-size:10px;color:#444;text-transform:uppercase;
                                    letter-spacing:.5px;margin-bottom:8px;">
                            Articles ({{ $commandeActive->lignes->count() }})
                        </div>
                        @foreach($commandeActive->lignes as $l)
                        <div class="ligne-row">
                            <div style="display:flex;align-items:center;gap:8px;">
                                <span style="font-size:12px;color:#e5e5e5;">
                                    {{ $l->menu->intitule ?? 'N/A' }}
                                </span>
                                <span style="font-size:11px;color:#f97316;font-weight:600;">
                                    ×{{ $l->quantite }}
                                </span>
                            </div>
                            <span style="font-size:12px;font-weight:700;color:#fff;">
                                {{ number_format($l->prix, 0, ',', ' ') }} FCFA
                            </span>
                        </div>
                        @endforeach
                    </div>

                    {{-- Total --}}
                    <div style="display:flex;justify-content:space-between;
                                align-items:center;padding-top:10px;
                                border-top:1px solid rgba(234,88,12,.2);">
                        <span style="font-size:13px;font-weight:700;color:#e5e5e5;">Total</span>
                        <span style="font-size:18px;font-weight:700;color:#f97316;">
                            {{ number_format($commandeActive->montant, 0, ',', ' ') }} FCFA
                        </span>
                    </div>

                    {{-- Consignes --}}
                    @if($commandeActive->consignes)
                    <div style="margin-top:10px;padding:8px 12px;border-radius:8px;
                                background:rgba(234,179,8,.06);border:1px solid rgba(234,179,8,.15);
                                font-size:11px;color:#eab308;">
                        <i class="fa-solid fa-note-sticky" style="margin-right:4px;"></i>
                        {{ $commandeActive->consignes }}
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        @else

        {{-- Table libre --}}
        <div class="card">
            <div class="card-body"
                 style="text-align:center;padding:36px 20px;">
                <div style="width:64px;height:64px;border-radius:16px;margin:0 auto 14px;
                            background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.2);
                            display:flex;align-items:center;justify-content:center;">
                    <i class="fa-solid fa-chair"
                       style="font-size:26px;color:#22c55e;"></i>
                </div>
                <p style="font-size:15px;font-weight:600;color:#22c55e;margin-bottom:6px;">
                    Table disponible
                </p>
                <p style="font-size:12px;color:#444;margin-bottom:18px;">
                    Aucune commande active sur cette table.
                </p>
                @if(in_array(auth()->user()->role, ['Administrateur','Caissier','Serveur']))
                <a href="{{ route('commandes.create') }}?table={{ $table->idtable }}"
                   class="btn btn-success">
                    <i class="fa-solid fa-plus"></i>
                    Créer une commande
                </a>
                @endif
            </div>
        </div>

        @endif

        {{-- ── Historique 30 derniers jours ── --}}
        <div class="card">
            <div class="card-header">
                <div class="card-header-title">
                    <i class="fa-solid fa-clock-rotate-left" style="color:var(--cc-orange);"></i>
                    Historique — 30 derniers jours
                    <span style="font-size:11px;font-weight:400;color:#444;">
                        ({{ $historique->count() }} commande(s))
                    </span>
                </div>
            </div>
            <div class="card-body">

                @if($historique->isEmpty())
                <div style="text-align:center;padding:28px;color:#2a2a2a;">
                    <i class="fa-solid fa-clock-rotate-left"
                       style="font-size:28px;display:block;margin-bottom:8px;"></i>
                    <p style="font-size:13px;">Aucune commande sur les 30 derniers jours</p>
                </div>
                @else

                @foreach($historique as $cmd)
                @php
                    $sc = match($cmd->statut_courant) {
                        'En attente'     => ['color'=>'#eab308','icone'=>'fa-clock'],
                        'En préparation' => ['color'=>'#60a5fa','icone'=>'fa-fire-burner'],
                        'Servie'         => ['color'=>'#22c55e','icone'=>'fa-utensils'],
                        'Livrée'         => ['color'=>'#22c55e','icone'=>'fa-circle-check'],
                        'Annulée'        => ['color'=>'#f87171','icone'=>'fa-circle-xmark'],
                        default          => ['color'=>'#555',   'icone'=>'fa-circle'],
                    };
                @endphp
                <div class="hist-row">

                    {{-- Icône statut --}}
                    <div style="width:34px;height:34px;border-radius:9px;flex-shrink:0;
                                display:flex;align-items:center;justify-content:center;
                                background:#1a1a1a;border:1px solid #252525;">
                        <i class="fa-solid {{ $sc['icone'] }}"
                           style="font-size:12px;color:{{ $sc['color'] }};"></i>
                    </div>

                    {{-- Infos --}}
                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;align-items:center;gap:6px;">
                            <span style="font-size:12px;font-weight:600;color:#e5e5e5;">
                                {{ $cmd->reference }}
                            </span>
                            <span style="font-size:10px;padding:1px 6px;border-radius:8px;
                                         background:{{ $sc['color'] }}22;color:{{ $sc['color'] }};">
                                {{ $cmd->statut_courant }}
                            </span>
                        </div>
                        <div style="font-size:11px;color:#444;margin-top:1px;">
                            {{ $cmd->datecommande->format('d/m/Y') }}
                            à {{ $cmd->heurecommande }}
                            · {{ $cmd->lignes->count() }} article(s)
                        </div>
                    </div>

                    {{-- Montant --}}
                    <div style="text-align:right;flex-shrink:0;">
                        <div style="font-size:12px;font-weight:700;color:#fff;">
                            {{ number_format($cmd->montant, 0, ',', ' ') }} FCFA
                        </div>
                    </div>

                    {{-- Lien --}}
                    <a href="{{ route('commandes.show', $cmd->idcommande) }}"
                       style="width:28px;height:28px;border-radius:7px;
                              background:#1a1a1a;border:1px solid #252525;flex-shrink:0;
                              display:flex;align-items:center;justify-content:center;
                              color:#444;text-decoration:none;transition:all .18s;"
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

        {{-- ── Informations de la table ── --}}
        <div class="card">
            <div class="card-header">
                <div class="card-header-title">
                    <i class="fa-solid fa-circle-info" style="color:var(--cc-orange);"></i>
                    Informations
                </div>
                <a href="{{ route('admin.tables.edit', $table->idtable) }}"
                   class="btn btn-ghost btn-sm">
                    <i class="fa-solid fa-pen-to-square" style="font-size:10px;"></i>
                    Modifier
                </a>
            </div>
            <div class="card-body">

                {{-- Statut visuel --}}
                <div style="display:flex;align-items:center;gap:12px;
                            padding:12px 14px;border-radius:10px;margin-bottom:14px;
                            background:{{ $commandesActives->isNotEmpty() ? 'rgba(234,88,12,.07)' : 'rgba(34,197,94,.06)' }};
                            border:1px solid {{ $commandesActives->isNotEmpty() ? 'rgba(234,88,12,.2)' : 'rgba(34,197,94,.15)' }};">
                    <div style="width:12px;height:12px;border-radius:50%;flex-shrink:0;"
                         class="{{ $commandesActives->isNotEmpty() ? 'dot-occupee' : 'dot-libre' }}"></div>
                    <div>
                        <div style="font-size:13px;font-weight:700;
                                    color:{{ $commandesActives->isNotEmpty() ? '#f97316' : '#22c55e' }};">
                            {{ $commandesActives->isNotEmpty() ? 'Table occupée' : 'Table libre' }}
                        </div>
                        <div style="font-size:11px;color:#444;margin-top:1px;">
                            @if($commandesActives->isNotEmpty())
                                {{ $commandesActives->count() }} commande{{ $commandesActives->count() > 1 ? 's' : '' }} en cours
                                · {{ number_format($montantEnCoursTotal, 0, ',', ' ') }} FCFA
                            @else
                                Disponible pour une nouvelle commande
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Détails --}}
                <div class="info-cell">
                    <span style="color:#444;">Nom</span>
                    <span style="color:#e5e5e5;font-weight:600;">{{ $table->intitule }}</span>
                </div>

                @if($table->description)
                <div class="info-cell">
                    <span style="color:#444;">Description</span>
                    <span style="color:#888;font-size:11px;">{{ $table->description }}</span>
                </div>
                @endif

                <div class="info-cell">
                    <span style="color:#444;">Créée le</span>
                    <span style="color:#666;">{{ $table->created_at->format('d/m/Y') }}</span>
                </div>

                <div class="info-cell">
                    <span style="color:#444;">Modifiée le</span>
                    <span style="color:#666;">
                        {{ $table->updated_at->format('d/m/Y à H:i') }}
                    </span>
                </div>
            </div>
        </div>

        {{-- ── Statistiques résumé ── --}}
        <div class="card">
            <div class="card-header">
                <div class="card-header-title">
                    <i class="fa-solid fa-chart-bar" style="color:var(--cc-orange);"></i>
                    Statistiques
                </div>
            </div>
            <div class="card-body">
                <div style="display:flex;flex-direction:column;gap:9px;">

                    <div style="display:flex;justify-content:space-between;align-items:center;
                                padding:10px 12px;border-radius:9px;
                                background:var(--cc-dark2);border:1px solid #1a1a1a;">
                        <span style="font-size:12px;color:#555;
                                     display:flex;align-items:center;gap:6px;">
                            <i class="fa-solid fa-receipt" style="color:#444;"></i>
                            Total commandes
                        </span>
                        <span style="font-size:14px;font-weight:700;color:#60a5fa;">
                            {{ $stats['total_commandes'] }}
                        </span>
                    </div>

                    <div style="display:flex;justify-content:space-between;align-items:center;
                                padding:10px 12px;border-radius:9px;
                                background:rgba(34,197,94,.05);
                                border:1px solid rgba(34,197,94,.15);">
                        <span style="font-size:12px;color:#22c55e;
                                     display:flex;align-items:center;gap:6px;">
                            <i class="fa-solid fa-money-bill-wave"></i>
                            CA total
                        </span>
                        <span style="font-size:13px;font-weight:700;color:#22c55e;">
                            {{ number_format($stats['ca_total'], 0, ',', ' ') }} F
                        </span>
                    </div>

                    <div style="display:flex;justify-content:space-between;align-items:center;
                                padding:10px 12px;border-radius:9px;
                                background:rgba(234,88,12,.05);
                                border:1px solid rgba(234,88,12,.15);">
                        <span style="font-size:12px;color:#f97316;
                                     display:flex;align-items:center;gap:6px;">
                            <i class="fa-solid fa-calendar"></i>
                            CA ce mois
                        </span>
                        <span style="font-size:13px;font-weight:700;color:#f97316;">
                            {{ number_format($stats['ca_mois'], 0, ',', ' ') }} F
                        </span>
                    </div>

                    <div style="display:flex;justify-content:space-between;align-items:center;
                                padding:10px 12px;border-radius:9px;
                                background:rgba(168,85,247,.05);
                                border:1px solid rgba(168,85,247,.15);">
                        <span style="font-size:12px;color:#a855f7;
                                     display:flex;align-items:center;gap:6px;">
                            <i class="fa-solid fa-cart-shopping"></i>
                            Panier moyen
                        </span>
                        <span style="font-size:13px;font-weight:700;color:#a855f7;">
                            {{ number_format($stats['panier_moyen'], 0, ',', ' ') }} F
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Actions ── --}}
        <div class="card">
            <div class="card-header">
                <div class="card-header-title">
                    <i class="fa-solid fa-gear" style="color:var(--cc-orange);"></i>
                    Actions
                </div>
            </div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:8px;">

                @if(in_array(auth()->user()->role,['Administrateur','Caissier','Serveur']))
                <a href="{{ route('commandes.create') }}?table={{ $table->idtable }}"
                   class="btn btn-success btn-sm" style="justify-content:flex-start;">
                    <i class="fa-solid fa-plus"></i>
                    Nouvelle commande
                </a>
                @endif

                @if($commandesActives->isNotEmpty())
                <a href="{{ route('commandes.index') }}?table={{ $table->idtable }}"
                   class="btn btn-ghost btn-sm" style="justify-content:flex-start;">
                    <i class="fa-solid fa-receipt"></i>
                    Voir les commandes actives ({{ $commandesActives->count() }})
                </a>
                @endif

                <a href="{{ route('admin.tables.edit', $table->idtable) }}"
                   class="btn btn-ghost btn-sm" style="justify-content:flex-start;">
                    <i class="fa-solid fa-pen-to-square"></i>
                    Modifier cette table
                </a>

                @if($commandesActives->isNotEmpty() && auth()->user()->role === 'Administrateur')
                <div style="height:1px;background:#1a1a1a;"></div>
                <button onclick="libererTable()"
                        class="btn btn-sm"
                        style="justify-content:flex-start;background:rgba(234,179,8,.1);
                               border:1px solid rgba(234,179,8,.2);color:#eab308;">
                    <i class="fa-solid fa-unlock"></i>
                    Libérer la table
                </button>
                @endif

                @if($commandesActives->isEmpty())
                <div style="height:1px;background:#1a1a1a;"></div>
                <button onclick="confirmerSuppression()"
                        class="btn btn-danger btn-sm"
                        style="justify-content:flex-start;">
                    <i class="fa-solid fa-trash"></i>
                    Supprimer la table
                </button>
                @endif

            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
// ── Libérer la table manuellement (Admin) ────────────────────
function libererTable() {
    Swal.fire({
        title: 'Libérer "{{ addslashes($table->intitule) }}" ?',
        html: `
            <div style="color:#666;font-size:13px;margin-bottom:8px;">
                @if($commandesActives->isNotEmpty())
                <strong style="color:#f97316;">
                {{ $commandesActives->count() }} commande{{ $commandesActives->count() > 1 ? 's' : '' }}</strong>
                {{ $commandesActives->count() > 1 ? 'seront marquées' : 'sera marquée' }} comme servie{{ $commandesActives->count() > 1 ? 's' : '' }} :
                {{ $commandesActives->pluck('reference')->implode(', ') }}
                @endif
            </div>
            <div style="color:#f87171;font-size:12px;">
                <i class="fa-solid fa-triangle-exclamation" style="margin-right:4px;"></i>
                Utilisez uniquement en cas de blocage.
            </div>
        `,
        icon: 'warning',
        iconColor: '#eab308',
        background: '#141414',
        color: '#e5e5e5',
        confirmButtonColor: '#eab308',
        confirmButtonText:
            '<i class="fa-solid fa-unlock" style="margin-right:6px"></i>Libérer',
        showCancelButton: true,
        cancelButtonText: 'Annuler',
        cancelButtonColor: '#1f1f1f',
    }).then(r => {
        if (r.isConfirmed) document.getElementById('libererForm').submit();
    });
}

// ── Supprimer la table ───────────────────────────────────────
function confirmerSuppression() {
    Swal.fire({
        title: 'Supprimer "{{ addslashes($table->intitule) }}" ?',
        html: `<div style="color:#666;font-size:13px;">
                   Cette action est <strong>irréversible</strong>.
                   L'historique des commandes sera conservé.
               </div>`,
        icon: 'warning',
        iconColor: '#ef4444',
        background: '#141414',
        color: '#e5e5e5',
        confirmButtonColor: '#ef4444',
        confirmButtonText:
            '<i class="fa-solid fa-trash" style="margin-right:6px"></i>Supprimer',
        showCancelButton: true,
        cancelButtonText: 'Annuler',
        cancelButtonColor: '#1f1f1f',
    }).then(r => {
        if (r.isConfirmed) document.getElementById('deleteForm').submit();
    });
}
</script>
@endpush