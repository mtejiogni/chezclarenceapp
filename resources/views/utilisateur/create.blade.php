@extends('layouts.app')

@section('title', 'Nouvel utilisateur')
@section('page-title', 'Nouvel utilisateur')

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
        gap: 10px;
    }

    .card-header-title {
        font-size: 13px;
        font-weight: 700;
        color: #e5e5e5;
    }

    .card-body { padding: 22px; }

    /* ── Champs ── */
    .field-group { margin-bottom: 16px; }
    .field-group:last-child { margin-bottom: 0; }

    .field-label {
        display: block;
        font-size: 11px;
        font-weight: 600;
        color: #555;
        letter-spacing: .5px;
        text-transform: uppercase;
        margin-bottom: 7px;
    }

    .field-label .req  { color: var(--cc-orange); margin-left: 2px; }
    .field-label .hint {
        font-weight: 400;
        color: #333;
        text-transform: none;
        letter-spacing: 0;
        margin-left: 4px;
    }

    .field-input {
        width: 100%;
        background: var(--cc-dark2);
        border: 1px solid var(--cc-border);
        border-radius: 10px;
        padding: 10px 14px;
        color: #e5e5e5;
        font-size: 13px;
        outline: none;
        transition: border-color .2s, box-shadow .2s;
        font-family: inherit;
    }

    .field-input::placeholder { color: #2a2a2a; }

    .field-input:focus {
        border-color: var(--cc-orange);
        box-shadow: 0 0 0 3px rgba(234,88,12,.1);
    }

    .field-input.is-error {
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239,68,68,.1);
    }

    .field-input.is-valid { border-color: #22c55e; }

    /* ── Message d'erreur ── */
    .field-error {
        display: none;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        color: #f87171;
        margin-top: 5px;
    }

    .field-error.show { display: flex; }

    /* ── Erreurs serveur ── */
    .server-errors {
        background: rgba(239,68,68,.07);
        border: 1px solid rgba(239,68,68,.2);
        border-radius: 10px;
        padding: 12px 16px;
        margin-bottom: 20px;
        font-size: 12px;
        color: #f87171;
    }

    /* ── Zone avatar ── */
    .avatar-zone {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        border: 2px dashed #1f1f1f;
        background: var(--cc-dark2);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: border-color .2s, background .2s;
        position: relative;
        overflow: hidden;
        margin: 0 auto;
        flex-shrink: 0;
    }

    .avatar-zone:hover {
        border-color: var(--cc-orange);
        background: rgba(234,88,12,.03);
    }

    .avatar-zone img {
        position: absolute; inset: 0;
        width: 100%; height: 100%;
        object-fit: cover;
    }

    /* ── Indicateur force mot de passe ── */
    .password-strength {
        height: 4px;
        border-radius: 2px;
        background: #1a1a1a;
        margin-top: 6px;
        overflow: hidden;
    }

    .password-strength-fill {
        height: 100%;
        border-radius: 2px;
        transition: width .3s, background .3s;
        width: 0%;
    }

    .strength-0 { width: 0%;   background: transparent; }
    .strength-1 { width: 25%;  background: #ef4444; }
    .strength-2 { width: 50%;  background: #eab308; }
    .strength-3 { width: 75%;  background: #3b82f6; }
    .strength-4 { width: 100%; background: #22c55e; }

    .strength-label {
        font-size: 10px;
        margin-top: 3px;
        color: #444;
    }

    /* ── Champ mot de passe avec œil ── */
    .password-wrap { position: relative; }

    .password-wrap .field-input { padding-right: 42px; }

    .password-toggle {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #444;
        cursor: pointer;
        font-size: 13px;
        padding: 2px;
        transition: color .18s;
    }

    .password-toggle:hover { color: #e5e5e5; }

    /* ── Boutons ── */
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 10px 20px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all .18s;
        border: none;
        font-family: inherit;
        text-decoration: none;
    }

    .btn-primary { background: var(--cc-orange); color: #fff; }
    .btn-primary:hover { background: #c2410c; }
    .btn-primary:disabled { opacity: .5; cursor: not-allowed; }

    .btn-ghost {
        background: var(--cc-dark3);
        border: 1px solid var(--cc-border);
        color: #555;
    }
    .btn-ghost:hover { color: #ccc; border-color: #333; }

    /* ── Sélection rôle (cards) ── */
    .role-card {
        border: 1.5px solid var(--cc-border);
        border-radius: 10px;
        padding: 10px 12px;
        cursor: pointer;
        transition: all .18s;
        background: var(--cc-dark2);
        text-align: center;
    }

    .role-card:hover { border-color: #2a2a2a; }

    .role-card.selected {
        border-color: var(--cc-orange);
        background: rgba(234,88,12,.07);
    }

    .role-card input[type="radio"] { display: none; }
</style>
@endpush

@section('content')

{{-- Fil d'Ariane --}}
<div style="display:flex;align-items:center;gap:8px;
            font-size:12px;color:#444;margin-bottom:20px;">
    <a href="{{ route('admin.utilisateurs.index') }}"
       style="color:#555;text-decoration:none;display:flex;align-items:center;gap:5px;"
       onmouseover="this.style.color='#f97316'"
       onmouseout="this.style.color='#555'">
        <i class="fa-solid fa-users"></i>
        Utilisateurs
    </a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;color:#333;"></i>
    <span style="color:#666;">Nouvel utilisateur</span>
</div>

<form method="POST"
      action="{{ route('admin.utilisateurs.store') }}"
      enctype="multipart/form-data"
      id="createForm"
      novalidate>
    @csrf

    <div style="display:grid;grid-template-columns:1fr 300px;gap:16px;align-items:start;">

        {{-- ════════════════════════════════
             COLONNE GAUCHE
        ════════════════════════════════ --}}
        <div>

            {{-- Erreurs serveur --}}
            @if($errors->any())
            <div class="server-errors animate__animated animate__fadeInDown">
                <div style="font-weight:600;margin-bottom:6px;
                            display:flex;align-items:center;gap:6px;">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    Veuillez corriger les erreurs suivantes :
                </div>
                <ul style="padding-left:16px;margin:0;">
                    @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- ── Informations personnelles ── --}}
            <div class="card">
                <div class="card-header">
                    <div style="width:32px;height:32px;border-radius:8px;
                                background:rgba(234,88,12,.15);
                                display:flex;align-items:center;justify-content:center;">
                        <i class="fa-solid fa-user"
                           style="color:var(--cc-orange);font-size:13px;"></i>
                    </div>
                    <div>
                        <div class="card-header-title">Informations personnelles</div>
                        <div style="font-size:11px;color:#444;margin-top:1px;">
                            Les champs marqués <span style="color:var(--cc-orange);">*</span>
                            sont obligatoires
                        </div>
                    </div>
                </div>
                <div class="card-body">

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">

                        {{-- Prénom --}}
                        <div class="field-group">
                            <label class="field-label" for="prenom">
                                Prénom <span class="req">*</span>
                            </label>
                            <input type="text"
                                   name="prenom"
                                   id="prenom"
                                   class="field-input {{ $errors->has('prenom') ? 'is-error' : '' }}"
                                   value="{{ old('prenom') }}"
                                   placeholder="Jean"
                                   maxlength="128"
                                   autocomplete="off"
                                   oninput="validerChamp('prenom','err_prenom','Minimum 2 caractères.')">
                            <div class="field-error {{ $errors->has('prenom') ? 'show' : '' }}"
                                 id="err_prenom">
                                <i class="fa-solid fa-circle-xmark"></i>
                                {{ $errors->first('prenom') }}
                            </div>
                        </div>

                        {{-- Nom --}}
                        <div class="field-group">
                            <label class="field-label" for="nom">
                                Nom <span class="req">*</span>
                            </label>
                            <input type="text"
                                   name="nom"
                                   id="nom"
                                   class="field-input {{ $errors->has('nom') ? 'is-error' : '' }}"
                                   value="{{ old('nom') }}"
                                   placeholder="Dupont"
                                   maxlength="128"
                                   autocomplete="off"
                                   oninput="validerChamp('nom','err_nom','Minimum 2 caractères.')">
                            <div class="field-error {{ $errors->has('nom') ? 'show' : '' }}"
                                 id="err_nom">
                                <i class="fa-solid fa-circle-xmark"></i>
                                {{ $errors->first('nom') }}
                            </div>
                        </div>

                        {{-- Sexe --}}
                        <div class="field-group">
                            <label class="field-label" for="sexe">
                                Sexe <span class="hint">(optionnel)</span>
                            </label>
                            <select name="sexe" id="sexe" class="field-input">
                                <option value="">— Sélectionner —</option>
                                <option value="Masculin"  {{ old('sexe') === 'Masculin'  ? 'selected' : '' }}>
                                    Masculin
                                </option>
                                <option value="Féminin"   {{ old('sexe') === 'Féminin'   ? 'selected' : '' }}>
                                    Féminin
                                </option>
                            </select>
                        </div>

                        {{-- Téléphone --}}
                        <div class="field-group">
                            <label class="field-label" for="telephone">
                                Téléphone <span class="req">*</span>
                            </label>
                            <input type="tel"
                                   name="telephone"
                                   id="telephone"
                                   class="field-input {{ $errors->has('telephone') ? 'is-error' : '' }}"
                                   value="{{ old('telephone') }}"
                                   placeholder="+237 6XX XXX XXX"
                                   maxlength="20"
                                   oninput="validerChamp('telephone','err_telephone','Téléphone obligatoire.')">
                            <div class="field-error {{ $errors->has('telephone') ? 'show' : '' }}"
                                 id="err_telephone">
                                <i class="fa-solid fa-circle-xmark"></i>
                                {{ $errors->first('telephone') }}
                            </div>
                        </div>
                    </div>

                    {{-- Adresse --}}
                    <div class="field-group">
                        <label class="field-label" for="adresse">
                            Adresse <span class="hint">(optionnel)</span>
                        </label>
                        <input type="text"
                               name="adresse"
                               id="adresse"
                               class="field-input"
                               value="{{ old('adresse') }}"
                               placeholder="Quartier, Ville..."
                               maxlength="500">
                    </div>

                </div>
            </div>

            {{-- ── Connexion ── --}}
            <div class="card">
                <div class="card-header">
                    <div style="width:32px;height:32px;border-radius:8px;
                                background:rgba(234,88,12,.15);
                                display:flex;align-items:center;justify-content:center;">
                        <i class="fa-solid fa-lock"
                           style="color:var(--cc-orange);font-size:13px;"></i>
                    </div>
                    <div class="card-header-title">Accès & connexion</div>
                </div>
                <div class="card-body">

                    {{-- Email --}}
                    <div class="field-group">
                        <label class="field-label" for="email">
                            Adresse email <span class="req">*</span>
                        </label>
                        <input type="email"
                               name="email"
                               id="email"
                               class="field-input {{ $errors->has('email') ? 'is-error' : '' }}"
                               value="{{ old('email') }}"
                               placeholder="jean.dupont@email.com"
                               maxlength="128"
                               autocomplete="off"
                               oninput="validerEmail()">
                        <div class="field-error {{ $errors->has('email') ? 'show' : '' }}"
                             id="err_email">
                            <i class="fa-solid fa-circle-xmark"></i>
                            <span id="err_email_txt">{{ $errors->first('email') }}</span>
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">

                        {{-- Mot de passe --}}
                        <div class="field-group">
                            <label class="field-label" for="password">
                                Mot de passe <span class="req">*</span>
                            </label>
                            <div class="password-wrap">
                                <input type="password"
                                       name="password"
                                       id="password"
                                       class="field-input {{ $errors->has('password') ? 'is-error' : '' }}"
                                       placeholder="••••••••"
                                       minlength="8"
                                       autocomplete="new-password"
                                       oninput="analyserMotDePasse()">
                                <button type="button"
                                        class="password-toggle"
                                        onclick="togglePassword('password', this)">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                            {{-- Barre de force --}}
                            <div class="password-strength">
                                <div class="password-strength-fill strength-0"
                                     id="strengthBar"></div>
                            </div>
                            <div class="strength-label" id="strengthLabel">
                                Saisissez un mot de passe
                            </div>
                            <div class="field-error {{ $errors->has('password') ? 'show' : '' }}"
                                 id="err_password">
                                <i class="fa-solid fa-circle-xmark"></i>
                                {{ $errors->first('password') }}
                            </div>
                        </div>

                        {{-- Confirmation --}}
                        <div class="field-group">
                            <label class="field-label" for="password_confirmation">
                                Confirmer <span class="req">*</span>
                            </label>
                            <div class="password-wrap">
                                <input type="password"
                                       name="password_confirmation"
                                       id="password_confirmation"
                                       class="field-input"
                                       placeholder="••••••••"
                                       autocomplete="new-password"
                                       oninput="validerConfirmation()">
                                <button type="button"
                                        class="password-toggle"
                                        onclick="togglePassword('password_confirmation', this)">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                            <div class="field-error" id="err_confirmation">
                                <i class="fa-solid fa-circle-xmark"></i>
                                Les mots de passe ne correspondent pas.
                            </div>
                        </div>
                    </div>

                    {{-- Règles du mot de passe --}}
                    <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:4px;">
                        @foreach([
                            ['id'=>'rule-len', 'label'=>'8 caractères min.'],
                            ['id'=>'rule-maj', 'label'=>'1 majuscule'],
                            ['id'=>'rule-num', 'label'=>'1 chiffre'],
                            ['id'=>'rule-spe', 'label'=>'1 caractère spécial'],
                        ] as $rule)
                        <div id="{{ $rule['id'] }}"
                             style="font-size:10px;padding:2px 8px;border-radius:10px;
                                    background:#1a1a1a;color:#333;transition:all .2s;">
                            <i class="fa-solid fa-circle" style="font-size:6px;margin-right:3px;"></i>
                            {{ $rule['label'] }}
                        </div>
                        @endforeach
                    </div>

                </div>
            </div>

        </div>

        {{-- ════════════════════════════════
             COLONNE DROITE
        ════════════════════════════════ --}}
        <div style="display:flex;flex-direction:column;gap:16px;">

            {{-- ── Photo de profil ── --}}
            <div class="card">
                <div class="card-header">
                    <i class="fa-solid fa-image"
                       style="color:var(--cc-orange);font-size:15px;"></i>
                    <div class="card-header-title">
                        Photo <span style="font-size:11px;font-weight:400;color:#444;">(optionnel)</span>
                    </div>
                </div>
                <div class="card-body" style="text-align:center;">

                    <div class="avatar-zone"
                         id="avatarZone"
                         onclick="document.getElementById('photo').click()"
                         title="Cliquez pour choisir une photo">
                        <img id="avatarPreview" src="" alt="" style="display:none;">
                        <div id="avatarPlaceholder">
                            <i class="fa-solid fa-camera"
                               style="font-size:24px;color:#2a2a2a;display:block;"></i>
                        </div>
                    </div>

                    <input type="file"
                           name="photo"
                           id="photo"
                           accept="image/jpeg,image/png,image/jpg,image/webp"
                           style="display:none;"
                           onchange="handlePhoto(this)">

                    <div id="photoInfo"
                         style="display:none;margin-top:8px;font-size:11px;color:#666;">
                        <span id="photoNom"></span>
                        <button type="button"
                                onclick="supprimerPhoto()"
                                style="background:none;border:none;color:#f87171;
                                       cursor:pointer;margin-left:6px;font-size:11px;">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <p style="font-size:10px;color:#333;margin-top:8px;line-height:1.5;">
                        JPG, PNG, WEBP · Max 2 Mo
                    </p>

                    @error('photo')
                    <div style="font-size:11px;color:#f87171;margin-top:4px;">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
            </div>

            {{-- ── Rôle ── --}}
            <div class="card">
                <div class="card-header">
                    <i class="fa-solid fa-shield-halved"
                       style="color:var(--cc-orange);font-size:15px;"></i>
                    <div>
                        <div class="card-header-title">Rôle <span style="color:var(--cc-orange);">*</span></div>
                        <div style="font-size:11px;color:#444;margin-top:1px;">
                            Détermine les accès dans l'application
                        </div>
                    </div>
                </div>
                <div class="card-body">

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                        @foreach($roles as $role)
                        @php
                            $roleSlug = match($role) {
                                'Administrateur' => 'admin',
                                'Caissier'       => 'caissier',
                                'Serveur'        => 'serveur',
                                'Cuisinier'      => 'cuisinier',
                                'Livreur'        => 'livreur',
                                'Client'         => 'client',
                                default          => 'client',
                            };
                            $roleIco = match($role) {
                                'Administrateur' => 'fa-user-shield',
                                'Caissier'       => 'fa-cash-register',
                                'Serveur'        => 'fa-bell-concierge',
                                'Cuisinier'      => 'fa-hat-chef',
                                'Livreur'        => 'fa-motorcycle',
                                'Client'         => 'fa-user',
                                default          => 'fa-user',
                            };
                            $roleCouleur = match($role) {
                                'Administrateur' => '#f97316',
                                'Caissier'       => '#60a5fa',
                                'Serveur'        => '#22c55e',
                                'Cuisinier'      => '#eab308',
                                'Livreur'        => '#a855f7',
                                'Client'         => '#9ca3af',
                                default          => '#555',
                            };
                            $estSelectionne = old('role') === $role;
                        @endphp
                        <label class="role-card {{ $estSelectionne ? 'selected' : '' }}"
                               onclick="selectionnerRole(this)">
                            <input type="radio"
                                   name="role"
                                   value="{{ $role }}"
                                   {{ $estSelectionne ? 'checked' : '' }}>
                            <i class="fa-solid {{ $roleIco }}"
                               style="font-size:18px;color:{{ $roleCouleur }};
                                      display:block;margin-bottom:5px;"></i>
                            <div style="font-size:11px;font-weight:600;
                                        color:#e5e5e5;">{{ $role }}</div>
                        </label>
                        @endforeach
                    </div>

                    @error('role')
                    <div style="font-size:11px;color:#f87171;margin-top:8px;
                                display:flex;align-items:center;gap:4px;">
                        <i class="fa-solid fa-circle-xmark"></i>
                        {{ $message }}
                    </div>
                    @enderror

                    {{-- Description du rôle sélectionné --}}
                    <div id="roleDescription"
                         style="margin-top:12px;padding:10px 12px;border-radius:8px;
                                font-size:11px;color:#555;background:#0a0a0a;
                                border:1px solid #1a1a1a;line-height:1.5;display:none;">
                    </div>

                </div>
            </div>

            {{-- ── Boutons ── --}}
            <div style="display:flex;flex-direction:column;gap:8px;">
                <button type="submit"
                        id="btnSubmit"
                        class="btn btn-primary"
                        style="justify-content:center;">
                    <i class="fa-solid fa-user-plus" id="btnIcon"></i>
                    <span id="btnText">Créer l'utilisateur</span>
                </button>
                <a href="{{ route('admin.utilisateurs.index') }}"
                   class="btn btn-ghost"
                   style="justify-content:center;">
                    <i class="fa-solid fa-arrow-left"></i>
                    Retour
                </a>
            </div>

        </div>
    </div>

</form>

@endsection

@push('scripts')
<script>
// ════════════════════════════════════════════════════════════
// DESCRIPTIONS DES RÔLES
// ════════════════════════════════════════════════════════════

const ROLE_DESC = {
    'Administrateur': 'Accès complet à toutes les fonctionnalités : gestion des utilisateurs, paramètres, statistiques et toutes les opérations.',
    'Caissier':       'Gère les encaissements, génère les reçus et consulte les commandes du jour.',
    'Serveur':        'Prend les commandes en salle, les enregistre et suit leur statut.',
    'Cuisinier':      'Consulte la liste des commandes à préparer et marque les plats comme prêts.',
    'Livreur':        'Gère les commandes à livrer et met à jour leur statut de livraison.',
    'Client':         'Peut passer des commandes en ligne et consulter l\'historique de ses achats.',
};

// ════════════════════════════════════════════════════════════
// SÉLECTION DU RÔLE (cards cliquables)
// ════════════════════════════════════════════════════════════

function selectionnerRole(label) {
    // Désélectionner toutes les cards
    document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));

    // Activer la card cliquée + cocher le radio
    label.classList.add('selected');
    const radio = label.querySelector('input[type="radio"]');
    if (radio) radio.checked = true;

    // Afficher la description du rôle
    const desc = document.getElementById('roleDescription');
    if (desc && ROLE_DESC[radio.value]) {
        desc.textContent  = ROLE_DESC[radio.value];
        desc.style.display = 'block';
    }
}

// Initialiser la description si un rôle est déjà sélectionné (après erreur de validation)
document.addEventListener('DOMContentLoaded', () => {
    const checked = document.querySelector('input[name="role"]:checked');
    if (checked) {
        const desc = document.getElementById('roleDescription');
        if (desc && ROLE_DESC[checked.value]) {
            desc.textContent   = ROLE_DESC[checked.value];
            desc.style.display = 'block';
        }
    }
});

// ════════════════════════════════════════════════════════════
// VALIDATIONS EN TEMPS RÉEL
// ════════════════════════════════════════════════════════════

/**
 * Valider un champ texte simple (non vide, min 2 chars)
 */
function validerChamp(inputId, errId, message) {
    const input = document.getElementById(inputId);
    const err   = document.getElementById(errId);
    const val   = input.value.trim();

    input.classList.remove('is-error', 'is-valid');

    if (!val || val.length < 2) {
        input.classList.add('is-error');
        if (err) {
            err.innerHTML = `<i class="fa-solid fa-circle-xmark"></i> ${!val ? 'Ce champ est obligatoire.' : message}`;
            err.classList.add('show');
        }
        return false;
    }

    input.classList.add('is-valid');
    if (err) err.classList.remove('show');
    return true;
}

/**
 * Valider l'adresse email
 */
function validerEmail() {
    const input = document.getElementById('email');
    const err   = document.getElementById('err_email');
    const txt   = document.getElementById('err_email_txt');
    const val   = input.value.trim();
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    input.classList.remove('is-error', 'is-valid');

    if (!val) {
        input.classList.add('is-error');
        if (txt) txt.textContent = "L'email est obligatoire.";
        if (err) err.classList.add('show');
        return false;
    }

    if (!regex.test(val)) {
        input.classList.add('is-error');
        if (txt) txt.textContent = "Format d'email invalide.";
        if (err) err.classList.add('show');
        return false;
    }

    input.classList.add('is-valid');
    if (err) err.classList.remove('show');
    return true;
}

/**
 * Valider la confirmation du mot de passe
 */
function validerConfirmation() {
    const pwd    = document.getElementById('password').value;
    const conf   = document.getElementById('password_confirmation');
    const err    = document.getElementById('err_confirmation');

    conf.classList.remove('is-error', 'is-valid');

    if (!conf.value || conf.value !== pwd) {
        conf.classList.add('is-error');
        if (err) err.classList.add('show');
        return false;
    }

    conf.classList.add('is-valid');
    if (err) err.classList.remove('show');
    return true;
}

// ════════════════════════════════════════════════════════════
// ANALYSEUR DE FORCE DU MOT DE PASSE
// ════════════════════════════════════════════════════════════

function analyserMotDePasse() {
    const val   = document.getElementById('password').value;
    const bar   = document.getElementById('strengthBar');
    const label = document.getElementById('strengthLabel');

    const regles = {
        len: val.length >= 8,
        maj: /[A-Z]/.test(val),
        num: /[0-9]/.test(val),
        spe: /[@$!%*#?&]/.test(val),
    };

    // Mettre à jour les indicateurs de règles
    Object.entries(regles).forEach(([cle, ok]) => {
        const el = document.getElementById(`rule-${cle}`);
        if (el) {
            el.style.background = ok ? 'rgba(34,197,94,.12)' : '#1a1a1a';
            el.style.color      = ok ? '#22c55e'             : '#333';
        }
    });

    // Score (0 → 4)
    const score = Object.values(regles).filter(Boolean).length;

    bar.className = `password-strength-fill strength-${score}`;

    const labels = ['', 'Trop faible', 'Faible', 'Moyen', 'Fort'];
    label.textContent = val.length === 0 ? 'Saisissez un mot de passe' : (labels[score] || '');
    label.style.color = ['#333','#ef4444','#eab308','#3b82f6','#22c55e'][score];

    // Ré-vérifier la confirmation si elle est déjà remplie
    if (document.getElementById('password_confirmation').value) {
        validerConfirmation();
    }
}

// ════════════════════════════════════════════════════════════
// AFFICHER / MASQUER LE MOT DE PASSE
// ════════════════════════════════════════════════════════════

function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    const ico   = btn.querySelector('i');
    const estVisible = input.type === 'text';
    input.type  = estVisible ? 'password' : 'text';
    ico.className = estVisible ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash';
}

// ════════════════════════════════════════════════════════════
// PHOTO DE PROFIL
// ════════════════════════════════════════════════════════════

function handlePhoto(input) {
    const file = input.files[0];
    if (!file) return;

    if (!['image/jpeg','image/png','image/jpg','image/webp'].includes(file.type)) {
        Swal.fire({
            toast: true, position: 'bottom-end',
            icon: 'error', title: 'Format non accepté (JPG, PNG ou WEBP).',
            timer: 3000, showConfirmButton: false,
            background: '#141414', color: '#e5e5e5',
        });
        return;
    }

    if (file.size > 2 * 1024 * 1024) {
        Swal.fire({
            toast: true, position: 'bottom-end',
            icon: 'warning', title: 'La photo ne doit pas dépasser 2 Mo.',
            timer: 3000, showConfirmButton: false,
            background: '#141414', color: '#e5e5e5',
        });
        input.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = e => {
        const preview = document.getElementById('avatarPreview');
        const placeholder = document.getElementById('avatarPlaceholder');
        const info = document.getElementById('photoInfo');
        const nom  = document.getElementById('photoNom');

        preview.src            = e.target.result;
        preview.style.display  = 'block';
        placeholder.style.display = 'none';
        nom.textContent        = file.name;
        info.style.display     = 'flex';
        info.style.alignItems  = 'center';
        info.style.justifyContent = 'center';
    };
    reader.readAsDataURL(file);
}

function supprimerPhoto() {
    document.getElementById('photo').value               = '';
    document.getElementById('avatarPreview').style.display    = 'none';
    document.getElementById('avatarPreview').src              = '';
    document.getElementById('avatarPlaceholder').style.display = 'block';
    document.getElementById('photoInfo').style.display        = 'none';
}

// ════════════════════════════════════════════════════════════
// SOUMISSION DU FORMULAIRE
// ════════════════════════════════════════════════════════════

document.getElementById('createForm').addEventListener('submit', function(e) {

    // Valider tous les champs requis
    const okPrenom   = validerChamp('prenom',   'err_prenom',   'Minimum 2 caractères.');
    const okNom      = validerChamp('nom',       'err_nom',      'Minimum 2 caractères.');
    const okTel      = validerChamp('telephone', 'err_telephone','Téléphone obligatoire.');
    const okEmail    = validerEmail();
    const okConf     = validerConfirmation();

    // Vérifier qu'un rôle est sélectionné
    const roleChoisi = document.querySelector('input[name="role"]:checked');

    if (!okPrenom || !okNom || !okTel || !okEmail || !okConf || !roleChoisi) {
        e.preventDefault();

        if (!roleChoisi) {
            Swal.fire({
                toast: true, position: 'bottom-end',
                icon: 'warning', title: 'Veuillez sélectionner un rôle.',
                timer: 3000, showConfirmButton: false,
                background: '#141414', color: '#e5e5e5',
            });
        }

        // Scroller vers la première erreur
        const firstErr = this.querySelector('.is-error');
        if (firstErr) {
            firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstErr.focus();
        }
        return;
    }

    // Loader sur le bouton
    document.getElementById('btnSubmit').disabled = true;
    document.getElementById('btnIcon').className  = 'fa-solid fa-spinner fa-spin';
    document.getElementById('btnText').textContent = 'Création en cours...';
});
</script>
@endpush