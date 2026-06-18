@extends('layouts.app')

@section('title', 'Utilisateurs')
@section('page-title', 'Gestion des utilisateurs')

@push('styles')
<style>
    :root {
        --cc-orange:  #ea580c;
        --cc-orange2: #f97316;
        --cc-dark2:   #0d0d0d;
        --cc-dark3:   #141414;
        --cc-border:  #1f1f1f;
    }

    .user-card {
        background: var(--cc-dark3);
        border: 1px solid var(--cc-border);
        border-radius: 13px;
        padding: 16px;
        display: flex;
        align-items: center;
        gap: 14px;
        transition: border-color .18s;
    }

    .user-card:hover { border-color: #2a2a2a; }
    .user-card.desactive { opacity: .6; }

    .avatar {
        width: 46px; height: 46px;
        border-radius: 12px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        font-weight: 700;
        text-transform: uppercase;
        overflow: hidden;
    }

    .avatar img { width: 100%; height: 100%; object-fit: cover; }

    .role-admin     { background: rgba(234,88,12,.15);  color: #f97316; }
    .role-caissier  { background: rgba(96,165,250,.15); color: #60a5fa; }
    .role-serveur   { background: rgba(34,197,94,.15);  color: #22c55e; }
    .role-cuisinier { background: rgba(234,179,8,.15);  color: #eab308; }
    .role-livreur   { background: rgba(168,85,247,.15); color: #a855f7; }
    .role-client    { background: rgba(156,163,175,.15);color: #9ca3af; }

    .badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 9px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 600;
    }

    .badge-active   { background: rgba(34,197,94,.12);  color: #22c55e; }
    .badge-inactive { background: rgba(239,68,68,.12);  color: #f87171; }

    .badge-role {
        font-size: 10px;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 6px;
    }

    .badge-role.admin     { background: rgba(234,88,12,.12);  color: #f97316; }
    .badge-role.caissier  { background: rgba(96,165,250,.12); color: #60a5fa; }
    .badge-role.serveur   { background: rgba(34,197,94,.12);  color: #22c55e; }
    .badge-role.cuisinier { background: rgba(234,179,8,.12);  color: #eab308; }
    .badge-role.livreur   { background: rgba(168,85,247,.12); color: #a855f7; }
    .badge-role.client    { background: rgba(156,163,175,.12);color: #9ca3af; }

    /* ── Indicateur en ligne (point connecté) ── */
    .etat-dot {
        width: 7px; height: 7px;
        border-radius: 50%;
        display: inline-block;
        flex-shrink: 0;
    }

    .etat-dot.connecte    { background: #22c55e; }
    .etat-dot.deconnecte  { background: #2a2a2a; }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 14px;
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

    .btn-sm { padding: 5px 10px; font-size: 11px; border-radius: 7px; }

    .stat-chip {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        border-radius: 9px;
        background: var(--cc-dark3);
        border: 1px solid var(--cc-border);
        font-size: 12px;
        color: #555;
        white-space: nowrap;
    }

    .stat-chip .num { font-size: 16px; font-weight: 700; }

    .search-input {
        background: var(--cc-dark2);
        border: 1px solid var(--cc-border);
        border-radius: 9px;
        padding: 9px 14px 9px 38px;
        color: #e5e5e5;
        font-size: 13px;
        outline: none;
        width: 100%;
        transition: border-color .18s;
        font-family: inherit;
    }

    .search-input::placeholder { color: #333; }
    .search-input:focus { border-color: var(--cc-orange); }

    .filtre-chip {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 500;
        cursor: pointer;
        border: 1px solid #1f1f1f;
        background: #141414;
        color: #555;
        font-family: inherit;
        transition: all .18s;
        white-space: nowrap;
    }

    .filtre-chip.actif { background: var(--cc-orange); color: #fff; border-color: var(--cc-orange); }
    .filtre-chip:hover:not(.actif) { border-color: #2a2a2a; color: #ccc; }

    .result-count {
        font-size: 11px;
        color: #444;
        padding: 4px 10px;
        border-radius: 6px;
        background: #141414;
        border: 1px solid #1f1f1f;
    }

    #emptyFilter {
        display: none;
        text-align: center;
        padding: 40px;
        background: var(--cc-dark3);
        border: 1px dashed var(--cc-border);
        border-radius: 14px;
        color: #2a2a2a;
    }
</style>
@endpush

@section('content')

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
     EN-TÊTE
══════════════════════════════════════════════════════════ --}}
<div style="display:flex;align-items:center;justify-content:space-between;
            flex-wrap:wrap;gap:12px;margin-bottom:20px;">

    <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;">

        <div class="stat-chip">
            <i class="fa-solid fa-users" style="color:var(--cc-orange);font-size:13px;"></i>
            <span class="num">{{ $totalUtilisateurs }}</span>
            <span>utilisateur(s)</span>
        </div>

        <div class="stat-chip">
            <i class="fa-solid fa-circle-check" style="color:#22c55e;font-size:13px;"></i>
            <span class="num" style="color:#22c55e;">{{ $totalActifs }}</span>
            <span>activé(s)</span>
        </div>

        @if($totalInactifs > 0)
        <div class="stat-chip">
            <i class="fa-solid fa-circle-xmark" style="color:#f87171;font-size:13px;"></i>
            <span class="num" style="color:#f87171;">{{ $totalInactifs }}</span>
            <span>désactivé(s)</span>
        </div>
        @endif

        @if($connectes > 0)
        <div class="stat-chip">
            <span class="etat-dot connecte"></span>
            <span class="num" style="color:#22c55e;">{{ $connectes }}</span>
            <span>connecté(s)</span>
        </div>
        @endif

        @if($totalAdmins >= 3)
        <div style="display:flex;align-items:center;gap:6px;padding:7px 12px;
                    border-radius:9px;font-size:11px;
                    background:rgba(234,179,8,.08);border:1px solid rgba(234,179,8,.2);
                    color:#eab308;">
            <i class="fa-solid fa-triangle-exclamation"></i>
            Limite de 3 administrateurs atteinte
        </div>
        @endif
    </div>

    <a href="{{ route('admin.utilisateurs.create') }}" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i>
        Nouvel utilisateur
    </a>
</div>

{{-- ══════════════════════════════════════════════════════════
     FILTRES DYNAMIQUES
══════════════════════════════════════════════════════════ --}}
<div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px;align-items:center;">

    <div style="position:relative;flex:1;min-width:200px;">
        <i class="fa-solid fa-magnifying-glass"
           style="position:absolute;left:12px;top:50%;transform:translateY(-50%);
                  color:#333;font-size:13px;pointer-events:none;"></i>
        <input type="text"
               id="searchInput"
               class="search-input"
               placeholder="Rechercher un utilisateur..."
               oninput="filtrerDOM()">
        <button id="btnClear"
                onclick="viderFiltres()"
                style="display:none;position:absolute;right:10px;top:50%;
                       transform:translateY(-50%);background:none;border:none;
                       color:#444;cursor:pointer;font-size:13px;"
                onmouseover="this.style.color='#f97316'"
                onmouseout="this.style.color='#444'">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <div class="result-count" id="resultCount">
        {{ $totalUtilisateurs }} résultat(s)
    </div>
</div>

{{-- Chips filtre par rôle + statut --}}
<div style="display:flex;gap:6px;margin-bottom:18px;flex-wrap:wrap;align-items:center;">

    {{-- Rôles --}}
    <button class="filtre-chip actif" data-filtre-role="tous"
            onclick="setRole('tous', this)">Tous</button>

    @foreach($roles as $r)
    <button class="filtre-chip" data-filtre-role="{{ $r }}"
            onclick="setRole('{{ $r }}', this)">
        {{ $r }}
    </button>
    @endforeach

    {{-- Séparateur --}}
    <div style="width:1px;background:#1f1f1f;margin:0 4px;height:20px;"></div>

    {{-- Statuts — les valeurs data-filtre-statut correspondent aux valeurs réelles de la BDD --}}
    <button class="filtre-chip" data-filtre-statut="Activé"
            onclick="setStatut('Activé', this)">
        <span style="width:7px;height:7px;border-radius:50%;
                     background:#22c55e;display:inline-block;margin-right:3px;"></span>
        Activés
    </button>
    <button class="filtre-chip" data-filtre-statut="Désactivé"
            onclick="setStatut('Désactivé', this)">
        <span style="width:7px;height:7px;border-radius:50%;
                     background:#f87171;display:inline-block;margin-right:3px;"></span>
        Désactivés
    </button>

</div>

{{-- ══════════════════════════════════════════════════════════
     LISTE
══════════════════════════════════════════════════════════ --}}
@if($users->isEmpty())

<div style="text-align:center;padding:60px 20px;
            background:var(--cc-dark3);border:1px dashed var(--cc-border);
            border-radius:14px;">
    <i class="fa-solid fa-users"
       style="font-size:40px;color:#1f1f1f;display:block;margin-bottom:14px;"></i>
    <p style="font-size:15px;font-weight:600;color:#333;margin-bottom:6px;">
        Aucun utilisateur
    </p>
    <a href="{{ route('admin.utilisateurs.create') }}" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Créer le premier utilisateur
    </a>
</div>

@else

<div style="display:flex;flex-direction:column;gap:8px;" id="userList">

    @foreach($users as $user)
    @php
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
        $estMoi      = auth()->id() === $user->iduser;
        $estActif    = $user->statut === 'Activé';
        $estConnecte = $user->etat   === 'Connecté';
    @endphp

    {{--
        data-statut stocke la valeur BDD exacte : "Activé" ou "Désactivé"
        Le filtre JS compare directement avec ces valeurs
    --}}
    <div class="user-card user-item {{ !$estActif ? 'desactive' : '' }}"
         data-nom="{{ strtolower(($user->prenom ?? '') . ' ' . ($user->nom ?? '') . ' ' . $user->email) }}"
         data-role="{{ $user->role }}"
         data-statut="{{ $user->statut }}">

        {{-- Avatar --}}
        <div class="avatar role-{{ $roleSlug }}">
            @if($user->photo)
                <img src="{{ asset('storage/' . $user->photo) }}" alt="{{ $user->prenom }}">
            @else
                {{ $initiales }}
            @endif
        </div>

        {{-- Infos --}}
        <div style="flex:1;min-width:0;">
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">

                {{-- Nom --}}
                <span style="font-size:13px;font-weight:700;color:#e5e5e5;">
                    {{ $user->prenom }} {{ $user->nom }}
                </span>

                {{-- Badge "Vous" --}}
                @if($estMoi)
                <span style="font-size:10px;padding:1px 7px;border-radius:6px;
                             background:rgba(234,88,12,.12);color:#f97316;">
                    Vous
                </span>
                @endif

                {{-- Badge rôle --}}
                <span class="badge-role {{ $roleSlug }}">{{ $user->role }}</span>

                {{-- Badge statut : compare avec 'Activé' / 'Désactivé' --}}
                <span class="badge {{ $estActif ? 'badge-active' : 'badge-inactive' }}">
                    {{ $user->statut }}
                </span>

                {{-- Point de connexion --}}
                <span style="display:flex;align-items:center;gap:4px;font-size:10px;
                             color:{{ $estConnecte ? '#22c55e' : '#333' }};">
                    <span class="etat-dot {{ $estConnecte ? 'connecte' : 'deconnecte' }}"></span>
                    {{ $user->etat }}
                </span>
            </div>

            <div style="font-size:11px;color:#444;margin-top:3px;
                        display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                <span>
                    <i class="fa-solid fa-envelope" style="margin-right:4px;color:#333;"></i>
                    {{ $user->email }}
                </span>
                @if($user->telephone)
                <span>
                    <i class="fa-solid fa-phone" style="margin-right:4px;color:#333;"></i>
                    {{ $user->telephone }}
                </span>
                @endif
                <span style="color:#333;">
                    <i class="fa-solid fa-clock" style="margin-right:4px;"></i>
                    Créé {{ $user->created_at->diffForHumans() }}
                </span>
            </div>
        </div>

        {{-- Actions --}}
        <div style="display:flex;gap:6px;flex-shrink:0;align-items:center;">

            <a href="{{ route('admin.utilisateurs.show', $user->iduser) }}"
               class="btn btn-ghost btn-sm" title="Voir le profil">
                <i class="fa-solid fa-eye" style="font-size:11px;"></i>
            </a>

            <a href="{{ route('admin.utilisateurs.edit', $user->iduser) }}"
               class="btn btn-ghost btn-sm" title="Modifier">
                <i class="fa-solid fa-pen-to-square" style="font-size:11px;"></i>
            </a>

            {{-- Toggle statut — pas sur soi-même --}}
            @if(!$estMoi)
            <button onclick="toggleStatut(
                        {{ $user->iduser }},
                        '{{ addslashes($user->prenom . ' ' . $user->nom) }}',
                        '{{ $user->statut }}'
                    )"
                    class="btn btn-ghost btn-sm"
                    title="{{ $estActif ? 'Désactiver' : 'Activer' }}">
                <i class="fa-solid {{ $estActif ? 'fa-user-slash' : 'fa-user-check' }}"
                   style="font-size:11px;color:{{ $estActif ? '#f87171' : '#22c55e' }};"></i>
            </button>
            @endif

            {{-- Reset mot de passe --}}
            <button onclick="resetPassword(
                        {{ $user->iduser }},
                        '{{ addslashes($user->prenom . ' ' . $user->nom) }}'
                    )"
                    class="btn btn-ghost btn-sm"
                    title="Réinitialiser le mot de passe">
                <i class="fa-solid fa-key" style="font-size:11px;color:#eab308;"></i>
            </button>

            {{-- Supprimer — pas sur soi-même --}}
            @if(!$estMoi)
            <button onclick="confirmerSuppression(
                        {{ $user->iduser }},
                        '{{ addslashes($user->prenom . ' ' . $user->nom) }}'
                    )"
                    class="btn btn-danger btn-sm" title="Supprimer">
                <i class="fa-solid fa-trash" style="font-size:11px;"></i>
            </button>
            @endif

        </div>

        {{-- Formulaires cachés --}}
        <form method="POST"
              action="{{ route('admin.utilisateurs.destroy', $user->iduser) }}"
              id="deleteForm-{{ $user->iduser }}" style="display:none;">
            @csrf @method('DELETE')
        </form>

        <form method="POST"
              action="{{ route('admin.utilisateurs.toggle-statut', $user->iduser) }}"
              id="toggleForm-{{ $user->iduser }}" style="display:none;">
            @csrf @method('PATCH')
        </form>

        <form method="POST"
              action="{{ route('admin.utilisateurs.reset-password', $user->iduser) }}"
              id="resetForm-{{ $user->iduser }}" style="display:none;">
            @csrf @method('PATCH')
        </form>
    </div>
    @endforeach

</div>

{{-- Bloc vide après filtre --}}
<div id="emptyFilter">
    <i class="fa-solid fa-magnifying-glass"
       style="font-size:28px;display:block;margin-bottom:10px;"></i>
    <p style="font-size:14px;font-weight:600;color:#333;margin-bottom:4px;"
       id="emptyMsg">Aucun utilisateur trouvé</p>
    <button onclick="viderFiltres()" class="btn btn-ghost btn-sm" style="margin-top:10px;">
        <i class="fa-solid fa-xmark"></i> Effacer les filtres
    </button>
</div>

@endif

@endsection

@push('scripts')
<script>
// ════════════════════════════════════════════════════════════
// ÉTAT DES FILTRES
// Les valeurs correspondent aux valeurs réelles stockées en BDD :
//   statut → 'Activé' | 'Désactivé'
//   etat   → 'Connecté' | 'Déconnecté'  (non filtré ici)
// ════════════════════════════════════════════════════════════

let roleActuel   = 'tous';
let statutActuel = 'tous'; // 'tous' | 'Activé' | 'Désactivé'

// ════════════════════════════════════════════════════════════
// FILTRE DOM
// ════════════════════════════════════════════════════════════

function filtrerDOM() {
    const q = document.getElementById('searchInput').value.trim().toLowerCase();

    document.getElementById('btnClear').style.display =
        (q || roleActuel !== 'tous' || statutActuel !== 'tous') ? 'block' : 'none';

    const items = document.querySelectorAll('.user-item');
    let visibles = 0;

    items.forEach(item => {
        const nom    = item.dataset.nom    || '';
        const role   = item.dataset.role   || '';
        // data-statut contient "Activé" ou "Désactivé" (valeur BDD exacte)
        const statut = item.dataset.statut || '';

        const okNom    = !q || nom.includes(q);
        const okRole   = roleActuel   === 'tous' || role   === roleActuel;
        const okStatut = statutActuel === 'tous' || statut === statutActuel;

        const visible = okNom && okRole && okStatut;
        item.style.display = visible ? '' : 'none';
        if (visible) visibles++;
    });

    const rc = document.getElementById('resultCount');
    if (rc) {
        rc.textContent = `${visibles} résultat(s)`;
        rc.style.color = visibles === 0 ? '#f87171' : '#444';
    }

    const empty = document.getElementById('emptyFilter');
    const list  = document.getElementById('userList');

    if (visibles === 0 && empty) {
        empty.style.display = 'block';
        if (list) list.style.display = 'none';
        const msg = document.getElementById('emptyMsg');
        if (msg) msg.textContent = q
            ? `Aucun utilisateur contenant "${q}".`
            : 'Aucun utilisateur pour ces critères.';
    } else if (empty) {
        empty.style.display = 'none';
        if (list) list.style.display = 'flex';
    }
}

// ════════════════════════════════════════════════════════════
// CHIP RÔLE
// ════════════════════════════════════════════════════════════

function setRole(role, btn) {
    roleActuel = role;
    document.querySelectorAll('[data-filtre-role]').forEach(b => b.classList.remove('actif'));
    btn.classList.add('actif');
    filtrerDOM();
}

// ════════════════════════════════════════════════════════════
// CHIP STATUT
// Toggle : cliquer sur la même chip la désactive
// ════════════════════════════════════════════════════════════

function setStatut(statut, btn) {
    if (statutActuel === statut) {
        statutActuel = 'tous';
        btn.classList.remove('actif');
    } else {
        statutActuel = statut;
        document.querySelectorAll('[data-filtre-statut]').forEach(b => b.classList.remove('actif'));
        btn.classList.add('actif');
    }
    filtrerDOM();
}

// ════════════════════════════════════════════════════════════
// VIDER FILTRES
// ════════════════════════════════════════════════════════════

function viderFiltres() {
    document.getElementById('searchInput').value = '';
    document.getElementById('btnClear').style.display = 'none';
    roleActuel   = 'tous';
    statutActuel = 'tous';
    document.querySelectorAll('[data-filtre-role]').forEach((b, i) => {
        b.classList.toggle('actif', i === 0);
    });
    document.querySelectorAll('[data-filtre-statut]').forEach(b => b.classList.remove('actif'));
    filtrerDOM();
    document.getElementById('searchInput').focus();
}

document.addEventListener('DOMContentLoaded', () => filtrerDOM());

// ════════════════════════════════════════════════════════════
// TOGGLE STATUT
// Le paramètre "statut" reçoit 'Activé' ou 'Désactivé'
// ════════════════════════════════════════════════════════════

function toggleStatut(id, nom, statut) {
    const desactiver = statut === 'Activé';
    Swal.fire({
        title: desactiver ? `Désactiver "${nom}" ?` : `Activer "${nom}" ?`,
        html: desactiver
            ? `<div style="color:#666;font-size:13px;">
                   Cet utilisateur ne pourra plus se connecter.
               </div>`
            : `<div style="color:#666;font-size:13px;">
                   Cet utilisateur pourra de nouveau se connecter.
               </div>`,
        icon: 'question',
        iconColor: desactiver ? '#f87171' : '#22c55e',
        background: '#141414', color: '#e5e5e5',
        confirmButtonColor: desactiver ? '#ef4444' : '#22c55e',
        confirmButtonText: desactiver ? 'Oui, désactiver' : 'Oui, activer',
        showCancelButton: true,
        cancelButtonText: 'Annuler',
        cancelButtonColor: '#1f1f1f',
    }).then(r => {
        if (r.isConfirmed) document.getElementById(`toggleForm-${id}`).submit();
    });
}

// ════════════════════════════════════════════════════════════
// RESET MOT DE PASSE
// ════════════════════════════════════════════════════════════

function resetPassword(id, nom) {
    Swal.fire({
        title: 'Réinitialiser le mot de passe ?',
        html: `<div style="color:#666;font-size:13px;">
                   Le mot de passe de <strong style="color:#e5e5e5;">${nom}</strong>
                   sera remis à <strong style="color:#eab308;">password123</strong>.
                   <br><br>
                   L'utilisateur devra le changer à sa prochaine connexion.
               </div>`,
        icon: 'warning', iconColor: '#eab308',
        background: '#141414', color: '#e5e5e5',
        confirmButtonColor: '#eab308',
        confirmButtonText: '<i class="fa-solid fa-key" style="margin-right:6px"></i>Réinitialiser',
        showCancelButton: true,
        cancelButtonText: 'Annuler', cancelButtonColor: '#1f1f1f',
    }).then(r => {
        if (r.isConfirmed) document.getElementById(`resetForm-${id}`).submit();
    });
}

// ════════════════════════════════════════════════════════════
// SUPPRESSION
// ════════════════════════════════════════════════════════════

function confirmerSuppression(id, nom) {
    Swal.fire({
        title: `Supprimer "${nom}" ?`,
        html: `<div style="color:#666;font-size:13px;">
                   Cette action est <strong>irréversible</strong>.
                   L'utilisateur sera définitivement supprimé.
               </div>`,
        icon: 'warning', iconColor: '#ef4444',
        background: '#141414', color: '#e5e5e5',
        confirmButtonColor: '#ef4444',
        confirmButtonText: '<i class="fa-solid fa-trash" style="margin-right:6px"></i>Supprimer',
        showCancelButton: true,
        cancelButtonText: 'Annuler', cancelButtonColor: '#1f1f1f',
    }).then(r => {
        if (r.isConfirmed) document.getElementById(`deleteForm-${id}`).submit();
    });
}
</script>
@endpush