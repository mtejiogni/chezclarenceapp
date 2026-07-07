@php
    $parametres = \App\Models\Parametre::first();
    $restaurantNom = $parametres?->nom_restaurant ?? 'Chez Clarence';
    $restaurantSlogan = $parametres?->slogan ?? 'Restaurant • Snack • Grill';
@endphp
@extends('layouts.auth')

@section('title', 'Créer un compte')

@section('content')
<div class="login-shell">

    {{-- ══════════════════════════════════════════════════════
         PANNEAU BRANDING (desktop uniquement)
    ══════════════════════════════════════════════════════════ --}}
    <div class="login-branding">
        <div class="grill-pattern"></div>

        <span class="ember" style="left:15%;animation-delay:.4s;"></span>
        <span class="ember" style="left:28%;animation-delay:2s;"></span>
        <span class="ember" style="left:44%;animation-delay:3.4s;"></span>
        <span class="ember" style="left:60%;animation-delay:1s;"></span>
        <span class="ember" style="left:74%;animation-delay:2.8s;"></span>
        <span class="ember" style="left:86%;animation-delay:.2s;"></span>

        <div class="login-branding-glow"></div>

        <div class="login-branding-content animate__animated animate__fadeIn">
            <img src="{{ asset('images/logo-chez-clarence.jpeg') }}" alt="{{ $restaurantNom }}" class="login-branding-logo">
            <h1>{{ $restaurantNom }}</h1>
            <p class="login-branding-tagline">{{ $restaurantSlogan }}</p>

            <div class="login-branding-quote">
                <i class="fa-solid fa-utensils"></i>
                <span>Rejoignez-nous et suivez vos commandes en toute simplicité.</span>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
         PANNEAU FORMULAIRE
    ══════════════════════════════════════════════════════════ --}}
    <div class="login-form-panel">

        <div class="login-mobile-halo login-mobile-halo-1"></div>
        <div class="login-mobile-halo login-mobile-halo-2"></div>

        <div class="login-form-wrap register-wrap animate__animated animate__fadeInUp">

            <div class="login-mobile-logo">
                <img src="{{ asset('images/logo-chez-clarence.jpeg') }}" alt="{{ $restaurantNom }}">
                <h1>{{ $restaurantNom }}</h1>
                <p>{{ $restaurantSlogan }}</p>
            </div>

            <div class="login-card">
                <div class="register-title-row">
                    <h2>Créer un compte</h2>
                    <span class="client-badge"><i class="fa-solid fa-user"></i> Client</span>
                </div>
                <p class="login-card-sub">Réservé aux clients — le personnel est créé par un administrateur.</p>

                @if ($errors->any())
                <div class="login-alert">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form method="POST" action="{{ route('register.post') }}" id="registerForm" novalidate>
                    @csrf

                    {{-- Nom / Prénom --}}
                    <div class="field-row-2">
                        <div class="field-group">
                            <i class="fa-solid fa-id-card field-icon"></i>
                            <input type="text" name="nom" id="nomInput" value="{{ old('nom') }}"
                                   placeholder=" " required maxlength="128">
                            <label for="nomInput">Nom</label>
                        </div>
                        <div class="field-group">
                            <i class="fa-solid fa-id-card field-icon"></i>
                            <input type="text" name="prenom" id="prenomInput" value="{{ old('prenom') }}"
                                   placeholder=" " required maxlength="128">
                            <label for="prenomInput">Prénom</label>
                        </div>
                    </div>

                    {{-- Sexe / Téléphone --}}
                    <div class="field-row-2">
                        <div class="field-group">
                            <i class="fa-solid fa-venus-mars field-icon"></i>
                            <select name="sexe" id="sexeInput" class="field-select">
                                <option value=""></option>
                                <option value="Masculin" {{ old('sexe') === 'Masculin' ? 'selected' : '' }}>Masculin</option>
                                <option value="Féminin" {{ old('sexe') === 'Féminin' ? 'selected' : '' }}>Féminin</option>
                            </select>
                            <label for="sexeInput" class="label-select">Sexe (optionnel)</label>
                        </div>
                        <div class="field-group">
                            <i class="fa-solid fa-phone field-icon"></i>
                            <input type="tel" name="telephone" id="telInput" value="{{ old('telephone') }}"
                                   placeholder=" " maxlength="20">
                            <label for="telInput">Téléphone (optionnel)</label>
                        </div>
                    </div>

                    {{-- Email --}}
                    <div class="field-group">
                        <i class="fa-solid fa-envelope field-icon"></i>
                        <input type="email" name="email" id="emailInput" value="{{ old('email') }}"
                               placeholder=" " required maxlength="128"
                               class="{{ $errors->has('email') ? 'has-error' : '' }}">
                        <label for="emailInput">Adresse email</label>
                        <i class="fa-solid fa-circle-check field-valid" id="emailValid"></i>
                    </div>

                    {{-- Mot de passe --}}
                    <div class="field-group" x-data="{ show: false }" style="margin-bottom:8px;">
                        <i class="fa-solid fa-lock field-icon"></i>
                        <input :type="show ? 'text' : 'password'" name="password" id="passwordInput"
                               placeholder=" " required minlength="8">
                        <label for="passwordInput">Mot de passe</label>
                        <button type="button" class="field-toggle" @click="show = !show" tabindex="-1">
                            <i :class="show ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
                        </button>
                    </div>

                    {{-- Jauge de force --}}
                    <div class="strength-wrap" id="strengthWrap">
                        <div class="strength-track"><div class="strength-fill" id="strengthFill"></div></div>
                        <span class="strength-label" id="strengthLabel">8 caractères minimum</span>
                    </div>

                    {{-- Confirmation mot de passe --}}
                    <div class="field-group" x-data="{ show: false }">
                        <i class="fa-solid fa-lock field-icon"></i>
                        <input :type="show ? 'text' : 'password'" name="password_confirmation" id="passwordConfirmInput"
                               placeholder=" " required minlength="8">
                        <label for="passwordConfirmInput">Confirmer le mot de passe</label>
                        <button type="button" class="field-toggle" @click="show = !show" tabindex="-1" style="right:44px;">
                            <i :class="show ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
                        </button>
                        <i class="fa-solid fa-circle-check field-valid" id="matchValid"></i>
                    </div>

                    <button type="submit" id="submitBtn" class="login-submit" style="margin-top:6px;">
                        <span class="login-submit-shine"></span>
                        <i class="fa-solid fa-user-plus" id="submitIcon"></i>
                        <span id="submitLabel">Créer mon compte</span>
                    </button>
                </form>
            </div>

            <div class="login-register-link">
                <p>
                    Déjà un compte ?
                    <a href="{{ route('login') }}">Se connecter</a>
                </p>
            </div>

        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* ══════════════════════════════════════════════════════════
       LAYOUT SPLIT-SCREEN (identique à login.blade.php)
    ══════════════════════════════════════════════════════════ */
    .login-shell { min-height: 100vh; display: flex; }

    .login-branding {
        flex: 1.1; position: relative; overflow: hidden; background: #000;
        display: none; align-items: center; justify-content: center;
    }
    @media (min-width: 1024px) { .login-branding { display: flex; } }

    .grill-pattern {
        position: absolute; inset: 0;
        background-image: repeating-linear-gradient(
            135deg, rgba(234,88,12,.05) 0px, rgba(234,88,12,.05) 1px,
            transparent 1px, transparent 26px
        );
    }
    .login-branding-glow {
        position: absolute; top: 50%; left: 50%;
        width: 600px; height: 600px; transform: translate(-50%,-50%);
        background: radial-gradient(circle, rgba(234,88,12,.22) 0%, transparent 65%);
        pointer-events: none;
    }
    .ember {
        position: absolute; bottom: 0; width: 5px; height: 5px; border-radius: 50%;
        background: var(--cc-orange2); opacity: 0;
        box-shadow: 0 0 8px 2px rgba(249,115,22,.6);
        animation: ember-rise 6s infinite ease-in;
    }
    @keyframes ember-rise {
        0%   { transform: translateY(0) scale(1); opacity: 0; }
        8%   { opacity: .9; }
        100% { transform: translateY(-520px) scale(.2); opacity: 0; }
    }
    .login-branding-content { position: relative; z-index: 1; text-align: center; padding: 40px; max-width: 420px; }
    .login-branding-logo {
        width: 120px; height: 120px; border-radius: 26px; object-fit: cover;
        box-shadow: 0 20px 60px rgba(234,88,12,.35); margin: 0 auto 24px;
    }
    .login-branding-content h1 { font-size: 28px; font-weight: 800; color: #fff; margin: 0; letter-spacing: .5px; }
    .login-branding-tagline { font-size: 13.5px; color: #888; margin: 8px 0 0; text-transform: uppercase; letter-spacing: 2px; }
    .login-branding-quote {
        margin-top: 40px; padding-top: 28px; border-top: 1px solid #1f1f1f;
        display: flex; align-items: center; gap: 10px; justify-content: center;
        font-size: 13px; color: #666; font-style: italic;
    }
    .login-branding-quote i { color: var(--cc-orange); }

    .login-form-panel {
        flex: 1; position: relative; overflow: hidden;
        display: flex; align-items: center; justify-content: center;
        padding: 32px 20px; background: var(--cc-dark);
    }
    .login-mobile-halo { position: absolute; border-radius: 50%; pointer-events: none; }
    .login-mobile-halo-1 {
        top: -100px; left: -100px; width: 300px; height: 300px;
        background: radial-gradient(circle, rgba(234,88,12,.18) 0%, transparent 70%);
    }
    .login-mobile-halo-2 {
        bottom: -120px; right: -120px; width: 340px; height: 340px;
        background: radial-gradient(circle, rgba(234,88,12,.12) 0%, transparent 70%);
    }
    @media (min-width: 1024px) { .login-mobile-halo { display: none; } }

    .login-form-wrap { width: 100%; max-width: 400px; position: relative; z-index: 1; }
    .register-wrap { max-width: 440px; }

    .login-mobile-logo { text-align: center; margin-bottom: 26px; }
    .login-mobile-logo img {
        width: 88px; height: 88px; border-radius: 20px; object-fit: cover;
        box-shadow: 0 8px 30px rgba(234,88,12,.25); margin: 0 auto 14px; display: block;
    }
    .login-mobile-logo h1 { font-size: 19px; font-weight: 700; color: #fff; margin: 0; }
    .login-mobile-logo p { font-size: 12px; color: #666; margin: 4px 0 0; }
    @media (min-width: 1024px) { .login-mobile-logo { display: none; } }

    .login-card {
        background: var(--cc-dark3); border: 1px solid var(--cc-border);
        border-radius: 22px; padding: 32px 28px;
        box-shadow: 0 25px 60px rgba(0,0,0,.55);
    }
    .login-card h2 { font-size: 18px; font-weight: 700; color: #fff; margin: 0; }
    .login-card-sub { font-size: 12px; color: #555; margin: 5px 0 20px; }

    .register-title-row { display: flex; align-items: center; gap: 10px; }
    .client-badge {
        font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px;
        padding: 3px 9px; border-radius: 20px; background: rgba(34,197,94,.12); color: #22c55e;
        display: inline-flex; align-items: center; gap: 4px;
    }
    .client-badge i { font-size: 8px; }

    .login-alert {
        margin-bottom: 18px; padding: 12px 14px; border-radius: 10px;
        background: rgba(239,68,68,.08); border: 1px solid rgba(239,68,68,.2);
        display: flex; gap: 9px; align-items: flex-start;
    }
    .login-alert i { color: #f87171; margin-top: 2px; flex-shrink: 0; font-size: 13px; }
    .login-alert ul { margin: 0; padding: 0; list-style: none; font-size: 12.5px; color: #f87171; line-height: 1.6; }

    /* ── Champs à label flottant ── */
    .field-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .field-group { position: relative; margin-bottom: 16px; }
    .field-group input, .field-group select {
        width: 100%; background: var(--cc-dark2);
        border: 1.5px solid var(--cc-border); border-radius: 12px;
        padding: 21px 44px 9px 40px;
        color: #e5e5e5; font-size: 14.5px; outline: none;
        font-family: inherit; transition: border-color .18s, background .18s;
        appearance: none;
    }
    .field-group input.has-error { border-color: rgba(239,68,68,.5); }
    .field-group input:focus, .field-group select:focus { border-color: var(--cc-orange); background: #0a0a0a; }
    .field-select { cursor: pointer; }

    .field-icon {
        position: absolute; left: 14px; top: 21px; color: #444; font-size: 12.5px;
        transition: color .18s; pointer-events: none; z-index: 1;
    }
    .field-group input:focus ~ .field-icon,
    .field-group select:focus ~ .field-icon { color: var(--cc-orange2); }

    .field-group label {
        position: absolute; left: 40px; top: 15px;
        font-size: 14px; color: #666; pointer-events: none;
        transition: all .16s ease; transform-origin: left;
    }
    .field-group input:focus ~ label,
    .field-group input:not(:placeholder-shown) ~ label,
    .field-group select:focus ~ label.label-select,
    .field-group select.has-value ~ label.label-select {
        top: 6px; font-size: 9.5px; font-weight: 600; letter-spacing: .3px;
        color: var(--cc-orange2); text-transform: uppercase;
    }
    /* Le select affiche toujours son label en position haute (pas de "placeholder-shown" natif) */
    .label-select { top: 6px; font-size: 9.5px; font-weight: 600; letter-spacing: .3px; color: #555; text-transform: uppercase; }

    .field-valid {
        position: absolute; right: 14px; top: 21px; color: #22c55e; font-size: 12.5px;
        opacity: 0; transform: scale(.5); transition: all .2s; z-index: 1;
    }
    .field-valid.show { opacity: 1; transform: scale(1); }

    .field-toggle {
        position: absolute; right: 10px; top: 11px;
        width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;
        background: none; border: none; color: #555; cursor: pointer; border-radius: 8px;
        transition: color .15s; z-index: 1;
    }
    .field-toggle:hover { color: #ccc; }

    /* ── Jauge de force du mot de passe ── */
    .strength-wrap { margin: -6px 0 16px; }
    .strength-track { height: 4px; border-radius: 4px; background: #1a1a1a; overflow: hidden; }
    .strength-fill { height: 100%; width: 0%; border-radius: 4px; transition: width .25s, background .25s; }
    .strength-label { display: block; font-size: 10.5px; color: #444; margin-top: 5px; }

    /* ── Bouton submit ── */
    .login-submit {
        width: 100%; background: var(--cc-orange); color: #fff; border: none;
        border-radius: 12px; padding: 14px; font-size: 13.5px; font-weight: 700;
        cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 9px;
        transition: background .18s, transform .1s; position: relative; overflow: hidden;
    }
    .login-submit:hover { background: #c2410c; }
    .login-submit:active { transform: scale(.98); }
    .login-submit:disabled { opacity: .75; cursor: not-allowed; }
    .login-submit-shine {
        position: absolute; top: 0; left: -60%; width: 40%; height: 100%;
        background: linear-gradient(120deg, transparent, rgba(255,255,255,.25), transparent);
        transform: skewX(-20deg); animation: shine 3.2s infinite;
    }
    @keyframes shine { 0% { left: -60%; } 40% { left: 130%; } 100% { left: 130%; } }

    .login-register-link { text-align: center; margin-top: 22px; }
    .login-register-link p { font-size: 13px; color: #555; margin: 0; }
    .login-register-link a { color: var(--cc-orange2); font-weight: 700; text-decoration: none; }
    .login-register-link a:hover { text-decoration: underline; }

    /* ── Responsive ── */
    @media (max-width: 480px) {
        .field-row-2 { grid-template-columns: 1fr; gap: 0; }
        .login-card { padding: 26px 18px; border-radius: 18px; }
        .field-group input, .field-group select { font-size: 16px; } /* évite le zoom auto iOS */
    }
</style>
@endpush

@push('scripts')
<script>
// ── Label flottant du select (pas de :placeholder-shown natif) ──
const sexeSelect = document.getElementById('sexeInput');
const toggleSelectValue = () => sexeSelect.classList.toggle('has-value', sexeSelect.value !== '');
sexeSelect.addEventListener('change', toggleSelectValue);
toggleSelectValue();

// ── Validation email en temps réel ───────────────────────────
const emailInput = document.getElementById('emailInput');
const emailValid = document.getElementById('emailValid');
const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
emailInput.addEventListener('input', () => {
    emailValid.classList.toggle('show', emailRegex.test(emailInput.value));
});

// ── Jauge de force du mot de passe ───────────────────────────
const pwdInput   = document.getElementById('passwordInput');
const fillEl     = document.getElementById('strengthFill');
const labelEl    = document.getElementById('strengthLabel');

function evaluerForce(pwd) {
    if (!pwd) return { pct: 0, label: '8 caractères minimum', color: '#333' };
    let score = 0;
    if (pwd.length >= 8) score++;
    if (pwd.length >= 12) score++;
    if (/[A-Z]/.test(pwd) && /[a-z]/.test(pwd)) score++;
    if (/[0-9]/.test(pwd)) score++;
    if (/[^A-Za-z0-9]/.test(pwd)) score++;

    if (score <= 1) return { pct: 25,  label: 'Faible',        color: '#ef4444' };
    if (score <= 2) return { pct: 50,  label: 'Moyen',         color: '#eab308' };
    if (score <= 3) return { pct: 75,  label: 'Bon',           color: '#60a5fa' };
    return               { pct: 100, label: 'Excellent',      color: '#22c55e' };
}

pwdInput.addEventListener('input', () => {
    const r = evaluerForce(pwdInput.value);
    fillEl.style.width = r.pct + '%';
    fillEl.style.background = r.color;
    labelEl.textContent = r.label;
    labelEl.style.color = r.pct > 0 ? r.color : '#444';
    verifierCorrespondance();
});

// ── Correspondance des deux mots de passe ────────────────────
const confirmInput = document.getElementById('passwordConfirmInput');
const matchValid   = document.getElementById('matchValid');

function verifierCorrespondance() {
    const ok = confirmInput.value.length > 0 && confirmInput.value === pwdInput.value;
    matchValid.classList.toggle('show', ok);
}
confirmInput.addEventListener('input', verifierCorrespondance);

// ── État de chargement au submit ─────────────────────────────
document.getElementById('registerForm').addEventListener('submit', function () {
    const btn   = document.getElementById('submitBtn');
    const icon  = document.getElementById('submitIcon');
    const label = document.getElementById('submitLabel');

    btn.disabled = true;
    icon.className = 'fa-solid fa-spinner fa-spin';
    label.textContent = 'Création en cours...';
});
</script>
@endpush