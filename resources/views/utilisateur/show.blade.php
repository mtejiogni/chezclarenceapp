@extends('layouts.app')

@section('title', 'Profil : ' . $user->prenom . ' ' . $user->nom)
@section('page-title', 'Profil utilisateur')

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

    /* ── Avatar grand ── */
    .avatar-lg {
        width: 80px; height: 80px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        font-weight: 700;
        text-transform: uppercase;
        overflow: hidden;
        flex-shrink: 0;
    }

    .avatar-lg img { width: 100%; height: 100%; object-fit: cover; }

    /* ── Couleurs par rôle ── */
    .role-admin     { background: rgba(234,88,12,.15);  color: #f97316; }
    .role-caissier  { background: rgba(96,165,250,.15); color: #60a5fa; }
    .role-serveur   { background: rgba(34,197,94,.15);  color: #22c55e; }
    .role-cuisinier { background: rgba(234,179,8,.15);  color: #eab308; }
    .role-livreur   { background: rgba(168,85,247,.15); color: #a855f7; }
    .role-client    { background: rgba(156,163,175,.15);color: #9ca3af; }

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

    .badge-role {
        font-size: 11px;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 8px;
    }

    .badge-role.admin     { background: rgba(234,88,12,.12);  color: #f97316; }
    .badge-role.caissier  { background: rgba(96,165,250,.12); color: #60a5fa; }
    .badge-role.serveur   { background: rgba(34,197,94,.12);  color: #22c55e; }
    .badge-role.cuisinier { background: rgba(234,179,8,.12);  color: #eab308; }
    .badge-role.livreur   { background: rgba(168,85,247,.12); color: #a855f7; }
    .badge-role.client    { background: rgba(156,163,175,.12);color: #9ca3af; }

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
    .info-cell-val   { color: #e5e5e5; font-weight: 500; }

    /* ── Commandes ── */
    .cmd-row {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 9px 0;
        border-bottom: 1px solid #1a1a1a;
    }

    .cmd-row:last-child { border-bottom: none; }

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

    /* ── Point connexion ── */
    .etat-dot {
        width: 8px; height: 8px;
        border-radius: 50%;
        display: inline-block;
        flex-shrink: 0;
    }
    .etat-dot.connecte   { background: #22c55e; }
    .etat-dot.deconnecte { background: #2a2a2a; }
</style>
@endpush

@section('content')

@php
    $estMoi   = auth()->id() === $user->iduser;
    $estActif = $user->statut === 'Activé';
    $estConnecte = $user->etat === 'Connecté';
    $roleSlug = match($user->role) {
        'Administrateur' => 'admin',
        'Caissier'       => 'caissier',
        'Serveur'        => 'serveur',
        'Cuisinier'      => 'cuisinier',
        'Livreur'        => 'livreur',
        'Client'         => 'client',
        default          => 'client',
    };
    $initiales = strtoupper(
        substr($user->prenom ?? '?', 0, 1) .
        substr($user->nom    ?? '',  0, 1)
    );
@endphp

{{-- Fil d'Ariane --}}
<div style="display:flex;align-items:center;gap:8px;
            font-size:12px;color:#444;margin-bottom:18px;">
    <a href="{{ route('admin.utilisateurs.index') }}"
       style="color:#555;text-decoration:none;display:flex;align-items:center;gap:5px;"
       onmouseover="this.style.color='#f97316'"
       onmouseout="this.style.color='#555'">
        <i class="fa-solid fa-users"></i>
        Utilisateurs
    </a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;color:#333;"></i>
    <span style="color:#666;">{{ $user->prenom }} {{ $user->nom }}</span>
</div>

{{-- Alertes flash --}}
@if(session('success'))
<div style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:10px;
            margin-bottom:18px;font-size:13px;
            background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2);color:#22c55e;">
    <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
</div>
@endif

@if(session('error'))
<div style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:10px;
            margin-bottom:18px;font-size:13px;
            background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);color:#f87171;">
    <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
</div>
@endif

{{-- ══════════════════════════════════════════════════════════
     EN-TÊTE : avatar + nom + actions
══════════════════════════════════════════════════════════ --}}
<div style="display:flex;align-items:flex-start;justify-content:space-between;
            flex-wrap:wrap;gap:12px;margin-bottom:20px;">

    <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">

        {{-- Avatar --}}
        <div class="avatar-lg role-{{ $roleSlug }}">
            @if($user->photo)
                <img src="{{ asset('storage/' . $user->photo) }}"
                     alt="{{ $user->prenom }}">
            @else
                {{ $initiales }}
            @endif
        </div>

        <div>
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:6px;">
                <h1 style="font-size:20px;font-weight:700;color:#fff;margin:0;">
                    {{ $user->prenom }} {{ $user->nom }}
                </h1>
                @if($estMoi)
                <span style="font-size:11px;padding:2px 8px;border-radius:6px;
                             background:rgba(234,88,12,.12);color:#f97316;">
                    Vous
                </span>
                @endif
            </div>
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                <span class="badge-role {{ $roleSlug }}">{{ $user->role }}</span>
                <span class="badge {{ $estActif ? 'badge-active' : 'badge-inactive' }}">
                    {{ $user->statut }}
                </span>
                <span style="display:flex;align-items:center;gap:5px;font-size:11px;
                             color:{{ $estConnecte ? '#22c55e' : '#444' }};">
                    <span class="etat-dot {{ $estConnecte ? 'connecte' : 'deconnecte' }}"></span>
                    {{ $user->etat }}
                </span>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">

        <a href="{{ route('admin.utilisateurs.edit', $user->iduser) }}"
           class="btn btn-ghost btn-sm">
            <i class="fa-solid fa-pen-to-square"></i>
            Modifier
        </a>

        @if(!$estMoi)
        <button onclick="toggleStatut()"
                class="btn {{ $estActif ? 'btn-ghost' : 'btn-success' }} btn-sm">
            <i class="fa-solid {{ $estActif ? 'fa-user-slash' : 'fa-user-check' }}"></i>
            {{ $estActif ? 'Désactiver' : 'Activer' }}
        </button>
        @endif

        <button onclick="resetPassword()"
                class="btn btn-ghost btn-sm">
            <i class="fa-solid fa-key" style="color:#eab308;"></i>
            Réinitialiser MDP
        </button>

        @if(!$estMoi)
        <button onclick="confirmerSuppression()"
                class="btn btn-danger btn-sm">
            <i class="fa-solid fa-trash"></i>
            Supprimer
        </button>
        @endif

        <a href="{{ route('admin.utilisateurs.index') }}"
           class="btn btn-ghost btn-sm">
            <i class="fa-solid fa-arrow-left"></i>
            Retour
        </a>
    </div>
</div>

{{-- Formulaires cachés --}}
<form method="POST"
      action="{{ route('admin.utilisateurs.toggle-statut', $user->iduser) }}"
      id="toggleForm" style="display:none;">
    @csrf @method('PATCH')
</form>

<form method="POST"
      action="{{ route('admin.utilisateurs.reset-password', $user->iduser) }}"
      id="resetForm" style="display:none;">
    @csrf @method('PATCH')
</form>

<form method="POST"
      action="{{ route('admin.utilisateurs.destroy', $user->iduser) }}"
      id="deleteForm" style="display:none;">
    @csrf @method('DELETE')
</form>

{{-- ══════════════════════════════════════════════════════════
     KPIs selon le rôle
══════════════════════════════════════════════════════════ --}}
@if(!empty($stats))
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:16px;">

    @if(isset($stats['commandes_enregistrees']))
    <div class="kpi">
        <div class="kpi-val" style="color:#60a5fa;">
            {{ number_format($stats['commandes_enregistrees'], 0, ',', ' ') }}
        </div>
        <div class="kpi-label">
            <i class="fa-solid fa-receipt" style="margin-right:3px;"></i>
            Commandes enregistrées
        </div>
    </div>

    <div class="kpi">
        <div class="kpi-val" style="color:#22c55e;">
            {{ number_format($stats['ca_genere'] ?? 0, 0, ',', ' ') }}
        </div>
        <div class="kpi-label">
            <i class="fa-solid fa-money-bill-wave" style="margin-right:3px;"></i>
            CA généré (FCFA)
        </div>
    </div>

    <div class="kpi">
        <div class="kpi-val" style="color:#f97316;">
            {{ $stats['commandes_du_mois'] ?? 0 }}
        </div>
        <div class="kpi-label">
            <i class="fa-solid fa-calendar" style="margin-right:3px;"></i>
            Ce mois
        </div>
    </div>
    @endif

    @if(isset($stats['commandes_passees']))
    <div class="kpi">
        <div class="kpi-val" style="color:#60a5fa;">
            {{ number_format($stats['commandes_passees'], 0, ',', ' ') }}
        </div>
        <div class="kpi-label">
            <i class="fa-solid fa-bag-shopping" style="margin-right:3px;"></i>
            Commandes passées
        </div>
    </div>

    <div class="kpi">
        <div class="kpi-val" style="color:#22c55e;">
            {{ number_format($stats['montant_total'] ?? 0, 0, ',', ' ') }}
        </div>
        <div class="kpi-label">
            <i class="fa-solid fa-money-bill-wave" style="margin-right:3px;"></i>
            Total dépensé (FCFA)
        </div>
    </div>

    <div class="kpi">
        <div class="kpi-val" style="color:#f97316;">
            {{ $stats['commandes_en_cours'] ?? 0 }}
        </div>
        <div class="kpi-label">
            <i class="fa-solid fa-spinner" style="margin-right:3px;"></i>
            En cours
        </div>
    </div>
    @endif

</div>
@endif

{{-- ══════════════════════════════════════════════════════════
     CORPS : 2 colonnes
══════════════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:1fr 300px;gap:16px;align-items:start;">

    {{-- ════════════════════════════
         COLONNE GAUCHE
    ════════════════════════════ --}}
    <div>

        {{-- ── Dernières commandes ── --}}
        <div class="card">
            <div class="card-header">
                <div class="card-header-title">
                    <i class="fa-solid fa-clock-rotate-left"
                       style="color:var(--cc-orange);"></i>
                    Dernières commandes
                    <span style="font-size:11px;font-weight:400;color:#444;">
                        ({{ $dernieresCommandes->count() }})
                    </span>
                </div>
            </div>
            <div class="card-body">

                @if($dernieresCommandes->isEmpty())
                <div style="text-align:center;padding:28px;color:#2a2a2a;">
                    <i class="fa-solid fa-receipt"
                       style="font-size:28px;display:block;margin-bottom:8px;"></i>
                    <p style="font-size:13px;">Aucune commande associée</p>
                </div>
                @else

                @foreach($dernieresCommandes as $cmd)
                @php
                    $sc = match($cmd->statut_courant ?? '') {
                        'En attente'     => ['color'=>'#eab308','icone'=>'fa-clock'],
                        'En préparation' => ['color'=>'#60a5fa','icone'=>'fa-fire-burner'],
                        'Expédiée'       => ['color'=>'#f97316','icone'=>'fa-motorcycle'],
                        'Servie',
                        'Livrée'         => ['color'=>'#22c55e','icone'=>'fa-circle-check'],
                        'Annulée'        => ['color'=>'#f87171','icone'=>'fa-circle-xmark'],
                        default          => ['color'=>'#555',   'icone'=>'fa-circle'],
                    };
                @endphp
                <div class="cmd-row">

                    {{-- Icône statut --}}
                    <div style="width:34px;height:34px;border-radius:9px;flex-shrink:0;
                                display:flex;align-items:center;justify-content:center;
                                background:#1a1a1a;border:1px solid #252525;">
                        <i class="fa-solid {{ $sc['icone'] }}"
                           style="font-size:12px;color:{{ $sc['color'] }};"></i>
                    </div>

                    {{-- Infos --}}
                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                            <span style="font-size:12px;font-weight:600;color:#e5e5e5;">
                                {{ $cmd->reference }}
                            </span>
                            @if($cmd->table)
                            <span style="font-size:10px;color:#444;background:#1a1a1a;
                                         padding:1px 6px;border-radius:5px;">
                                {{ $cmd->table->intitule }}
                            </span>
                            @endif
                            <span style="font-size:10px;padding:1px 7px;border-radius:8px;
                                         background:{{ $sc['color'] }}22;color:{{ $sc['color'] }};">
                                {{ $cmd->statut_courant }}
                            </span>
                        </div>
                        <div style="font-size:11px;color:#444;margin-top:1px;">
                            {{ $cmd->datecommande?->format('d/m/Y') }}
                            · {{ $cmd->lignes->count() }} article(s)
                        </div>
                    </div>

                    {{-- Montant --}}
                    <div style="font-size:12px;font-weight:700;color:#fff;flex-shrink:0;">
                        {{ number_format($cmd->montant ?? 0, 0, ',', ' ') }} FCFA
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

        {{-- ── Informations du profil ── --}}
        <div class="card">
            <div class="card-header">
                <div class="card-header-title">
                    <i class="fa-solid fa-circle-info" style="color:var(--cc-orange);"></i>
                    Informations
                </div>
                <a href="{{ route('admin.utilisateurs.edit', $user->iduser) }}"
                   class="btn btn-ghost btn-sm">
                    <i class="fa-solid fa-pen-to-square" style="font-size:10px;"></i>
                    Modifier
                </a>
            </div>
            <div class="card-body">

                <div class="info-cell">
                    <span class="info-cell-label">Prénom</span>
                    <span class="info-cell-val">{{ $user->prenom }}</span>
                </div>
                <div class="info-cell">
                    <span class="info-cell-label">Nom</span>
                    <span class="info-cell-val">{{ $user->nom }}</span>
                </div>
                @if($user->sexe)
                <div class="info-cell">
                    <span class="info-cell-label">Sexe</span>
                    <span class="info-cell-val">{{ $user->sexe }}</span>
                </div>
                @endif
                <div class="info-cell">
                    <span class="info-cell-label">Email</span>
                    <span class="info-cell-val"
                          style="font-size:11px;word-break:break-all;">
                        {{ $user->email }}
                    </span>
                </div>
                @if($user->telephone)
                <div class="info-cell">
                    <span class="info-cell-label">Téléphone</span>
                    <span class="info-cell-val">{{ $user->telephone }}</span>
                </div>
                @endif
                @if($user->adresse)
                <div class="info-cell">
                    <span class="info-cell-label">Adresse</span>
                    <span class="info-cell-val"
                          style="font-size:11px;text-align:right;max-width:180px;">
                        {{ $user->adresse }}
                    </span>
                </div>
                @endif
                <div class="info-cell">
                    <span class="info-cell-label">Rôle</span>
                    <span class="badge-role {{ $roleSlug }}"
                          style="font-size:10px;">
                        {{ $user->role }}
                    </span>
                </div>
                <div class="info-cell">
                    <span class="info-cell-label">Statut</span>
                    <span class="badge {{ $estActif ? 'badge-active' : 'badge-inactive' }}">
                        {{ $user->statut }}
                    </span>
                </div>
                <div class="info-cell">
                    <span class="info-cell-label">État</span>
                    <span style="display:flex;align-items:center;gap:5px;font-size:12px;
                                 color:{{ $estConnecte ? '#22c55e' : '#555' }};">
                        <span class="etat-dot {{ $estConnecte ? 'connecte' : 'deconnecte' }}"></span>
                        {{ $user->etat }}
                    </span>
                </div>
                <div class="info-cell">
                    <span class="info-cell-label">Créé le</span>
                    <span class="info-cell-val" style="color:#555;">
                        {{ $user->created_at->format('d/m/Y') }}
                    </span>
                </div>
                <div class="info-cell">
                    <span class="info-cell-label">Modifié le</span>
                    <span class="info-cell-val" style="color:#555;">
                        {{ $user->updated_at->format('d/m/Y à H:i') }}
                    </span>
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

                <a href="{{ route('admin.utilisateurs.edit', $user->iduser) }}"
                   class="btn btn-ghost btn-sm" style="justify-content:flex-start;">
                    <i class="fa-solid fa-pen-to-square"></i>
                    Modifier le profil
                </a>

                @if(!$estMoi)
                <button onclick="toggleStatut()"
                        class="btn btn-ghost btn-sm"
                        style="justify-content:flex-start;">
                    <i class="fa-solid {{ $estActif ? 'fa-user-slash' : 'fa-user-check' }}"
                       style="color:{{ $estActif ? '#f87171' : '#22c55e' }};"></i>
                    {{ $estActif ? 'Désactiver le compte' : 'Activer le compte' }}
                </button>
                @endif

                <button onclick="resetPassword()"
                        class="btn btn-ghost btn-sm"
                        style="justify-content:flex-start;">
                    <i class="fa-solid fa-key" style="color:#eab308;"></i>
                    Réinitialiser le mot de passe
                </button>

                @if(!$estMoi)
                <div style="height:1px;background:#1a1a1a;"></div>
                <button onclick="confirmerSuppression()"
                        class="btn btn-danger btn-sm"
                        style="justify-content:flex-start;">
                    <i class="fa-solid fa-trash"></i>
                    Supprimer ce compte
                </button>
                @endif

            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
// ── Toggle statut ────────────────────────────────────────────
function toggleStatut() {
    const estActif = '{{ $user->statut }}' === 'Activé';
    Swal.fire({
        title: estActif
            ? 'Désactiver "{{ addslashes($user->prenom . " " . $user->nom) }}" ?'
            : 'Activer "{{ addslashes($user->prenom . " " . $user->nom) }}" ?',
        html: estActif
            ? `<div style="color:#666;font-size:13px;">
                   Cet utilisateur ne pourra plus se connecter.
               </div>`
            : `<div style="color:#666;font-size:13px;">
                   Cet utilisateur pourra de nouveau se connecter.
               </div>`,
        icon: 'question',
        iconColor: estActif ? '#f87171' : '#22c55e',
        background: '#141414', color: '#e5e5e5',
        confirmButtonColor: estActif ? '#ef4444' : '#22c55e',
        confirmButtonText: estActif ? 'Oui, désactiver' : 'Oui, activer',
        showCancelButton: true,
        cancelButtonText: 'Annuler',
        cancelButtonColor: '#1f1f1f',
    }).then(r => {
        if (r.isConfirmed) document.getElementById('toggleForm').submit();
    });
}

// ── Reset mot de passe ───────────────────────────────────────
function resetPassword() {
    Swal.fire({
        title: 'Réinitialiser le mot de passe ?',
        html: `<div style="color:#666;font-size:13px;">
                   Le mot de passe de
                   <strong style="color:#e5e5e5;">
                       {{ addslashes($user->prenom . ' ' . $user->nom) }}
                   </strong>
                   sera remis à
                   <strong style="color:#eab308;">password123</strong>.
                   <br><br>
                   L'utilisateur devra le changer à sa prochaine connexion.
               </div>`,
        icon: 'warning', iconColor: '#eab308',
        background: '#141414', color: '#e5e5e5',
        confirmButtonColor: '#eab308',
        confirmButtonText: '<i class="fa-solid fa-key" style="margin-right:6px"></i>Réinitialiser',
        showCancelButton: true,
        cancelButtonText: 'Annuler',
        cancelButtonColor: '#1f1f1f',
    }).then(r => {
        if (r.isConfirmed) document.getElementById('resetForm').submit();
    });
}

// ── Suppression ──────────────────────────────────────────────
function confirmerSuppression() {
    Swal.fire({
        title: 'Supprimer "{{ addslashes($user->prenom . " " . $user->nom) }}" ?',
        html: `<div style="color:#666;font-size:13px;">
                   Cette action est <strong>irréversible</strong>.
                   Le compte sera définitivement supprimé.
               </div>`,
        icon: 'warning', iconColor: '#ef4444',
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