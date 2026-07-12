@php
    $parametres = \App\Models\Parametre::first();
    $restaurantNom = $parametres?->nom_restaurant ?? 'Chez Clarence';
    $restaurantSlogan = $parametres?->slogan ?? 'Restaurant • Snack • Grill';
@endphp
@extends('layouts.auth')

@section('title', 'Connexion')

@section('content')
<div class="login-shell">

    {{-- ══════════════════════════════════════════════════════
         PANNEAU BRANDING (desktop uniquement)
    ══════════════════════════════════════════════════════════ --}}
    <div class="login-branding">
        <div class="grill-pattern"></div>

        {{-- Braises animées --}}
        <span class="ember" style="left:12%;animation-delay:0s;"></span>
        <span class="ember" style="left:24%;animation-delay:1.2s;"></span>
        <span class="ember" style="left:38%;animation-delay:2.4s;"></span>
        <span class="ember" style="left:55%;animation-delay:.6s;"></span>
        <span class="ember" style="left:68%;animation-delay:3s;"></span>
        <span class="ember" style="left:80%;animation-delay:1.8s;"></span>
        <span class="ember" style="left:90%;animation-delay:4.2s;"></span>

        <div class="login-branding-glow"></div>

        <div class="login-branding-content animate__animated animate__fadeIn">
            <img src="{{ asset('images/logo-chez-clarence.jpeg') }}" alt="{{ $restaurantNom }}" class="login-branding-logo">
            <h1>{{ $restaurantNom }}</h1>
            <p class="login-branding-tagline">{{ $restaurantSlogan }}</p>

            <div class="login-branding-quote">
                <i class="fa-solid fa-fire"></i>
                <span>La gestion de votre restaurant, brûlante d'efficacité.</span>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
         PANNEAU FORMULAIRE
    ══════════════════════════════════════════════════════════ --}}
    <div class="login-form-panel">

        {{-- Halos décoratifs (visibles seulement quand le panneau branding est masqué, mobile) --}}
        <div class="login-mobile-halo login-mobile-halo-1"></div>
        <div class="login-mobile-halo login-mobile-halo-2"></div>

        <div class="login-form-wrap animate__animated animate__fadeInUp">

            {{-- Logo mobile uniquement --}}
            <div class="login-mobile-logo">
                <img src="{{ asset('images/logo-chez-clarence.jpeg') }}" alt="{{ $restaurantNom }}">
                <h1>{{ $restaurantNom }}</h1>
                <p>{{ $restaurantSlogan }}</p>
            </div>

            <div class="login-card">
                <h2>Connexion à votre espace</h2>
                <p class="login-card-sub">Entrez vos identifiants pour accéder à votre tableau de bord</p>

                {{-- Erreurs de validation --}}
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

                <form method="POST" action="{{ route('login.post') }}" id="loginForm" novalidate>
                    @csrf

                    {{-- Email --}}
                    <div class="field-group">
                        <i class="fa-solid fa-envelope field-icon"></i>
                        <input type="email" name="email" id="emailInput" value="{{ old('email') }}"
                               placeholder=" " required autofocus
                               class="{{ $errors->has('email') ? 'has-error' : '' }}">
                        <label for="emailInput">Adresse email</label>
                        <i class="fa-solid fa-circle-check field-valid" id="emailValid"></i>
                    </div>

                    {{-- Mot de passe --}}
                    <div class="field-group" x-data="{ show: false }">
                        <i class="fa-solid fa-lock field-icon"></i>
                        <input :type="show ? 'text' : 'password'" name="password" id="passwordInput"
                               placeholder=" " required>
                        <label for="passwordInput">Mot de passe</label>
                        <button type="button" class="field-toggle" @click="show = !show" tabindex="-1">
                            <i :class="show ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
                        </button>
                    </div>

                    {{-- Se souvenir de moi --}}
                    <label class="remember-row">
                        <input type="checkbox" name="remember" value="1">
                        <span class="remember-box"><i class="fa-solid fa-check"></i></span>
                        <span class="remember-label">Se souvenir de moi sur cet appareil</span>
                    </label>

                    <button type="submit" id="submitBtn" class="login-submit">
                        <span class="login-submit-shine"></span>
                        <i class="fa-solid fa-right-to-bracket" id="submitIcon"></i>
                        <span id="submitLabel">Se connecter</span>
                    </button>
                </form>
            </div>

            <!--
            <div class="login-register-link">
                <p>
                    Pas encore de compte ?
                    <a href="{{ route('register') }}">Créer un compte client</a>
                </p>
            </div>
            -->
            
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* ══════════════════════════════════════════════════════════
       LAYOUT SPLIT-SCREEN
    ══════════════════════════════════════════════════════════ */
    .login-shell {
        min-height: 100vh;
        display: flex;
    }

    /* ── Panneau branding (desktop) ── */
    .login-branding {
        flex: 1.1;
        position: relative;
        overflow: hidden;
        background: #000;
        display: none;
        align-items: center;
        justify-content: center;
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

    .login-branding-content {
        position: relative; z-index: 1; text-align: center; padding: 40px; max-width: 420px;
    }
    .login-branding-logo {
        width: 120px; height: 120px; border-radius: 26px; object-fit: cover;
        box-shadow: 0 20px 60px rgba(234,88,12,.35);
        margin: 0 auto 24px;
    }
    .login-branding-content h1 {
        font-size: 28px; font-weight: 800; color: #fff; margin: 0; letter-spacing: .5px;
    }
    .login-branding-tagline {
        font-size: 13.5px; color: #888; margin: 8px 0 0; text-transform: uppercase; letter-spacing: 2px;
    }
    .login-branding-quote {
        margin-top: 40px; padding-top: 28px; border-top: 1px solid #1f1f1f;
        display: flex; align-items: center; gap: 10px; justify-content: center;
        font-size: 13px; color: #666; font-style: italic;
    }
    .login-branding-quote i { color: var(--cc-orange); }

    /* ── Panneau formulaire ── */
    .login-form-panel {
        flex: 1;
        position: relative;
        overflow: hidden;
        display: flex; align-items: center; justify-content: center;
        padding: 32px 20px;
        background: var(--cc-dark);
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

    .login-mobile-logo { text-align: center; margin-bottom: 26px; }
    .login-mobile-logo img {
        width: 88px; height: 88px; border-radius: 20px; object-fit: cover;
        box-shadow: 0 8px 30px rgba(234,88,12,.25); margin: 0 auto 14px; display: block;
    }
    .login-mobile-logo h1 { font-size: 19px; font-weight: 700; color: #fff; margin: 0; }
    .login-mobile-logo p { font-size: 12px; color: #666; margin: 4px 0 0; }
    @media (min-width: 1024px) { .login-mobile-logo { display: none; } }

    /* ── Carte ── */
    .login-card {
        background: var(--cc-dark3);
        border: 1px solid var(--cc-border);
        border-radius: 22px;
        padding: 34px 30px;
        box-shadow: 0 25px 60px rgba(0,0,0,.55);
    }
    .login-card h2 { font-size: 18px; font-weight: 700; color: #fff; margin: 0; }
    .login-card-sub { font-size: 12.5px; color: #555; margin: 5px 0 24px; }

    .login-alert {
        margin-bottom: 18px; padding: 12px 14px; border-radius: 10px;
        background: rgba(239,68,68,.08); border: 1px solid rgba(239,68,68,.2);
        display: flex; gap: 9px; align-items: flex-start;
    }
    .login-alert i { color: #f87171; margin-top: 2px; flex-shrink: 0; font-size: 13px; }
    .login-alert ul { margin: 0; padding: 0; list-style: none; font-size: 12.5px; color: #f87171; line-height: 1.6; }

    /* ── Champs à label flottant ── */
    .field-group { position: relative; margin-bottom: 16px; }
    .field-group input {
        width: 100%; background: var(--cc-dark2);
        border: 1.5px solid var(--cc-border); border-radius: 12px;
        padding: 21px 44px 9px 42px;
        color: #e5e5e5; font-size: 15px; outline: none;
        font-family: inherit; transition: border-color .18s, background .18s;
    }
    .field-group input.has-error { border-color: rgba(239,68,68,.5); }
    .field-group input:focus { border-color: var(--cc-orange); background: #0a0a0a; }

    .field-icon {
        position: absolute; left: 15px; top: 21px; color: #444; font-size: 13px;
        transition: color .18s; pointer-events: none;
    }
    .field-group input:focus ~ .field-icon { color: var(--cc-orange2); }

    .field-group label {
        position: absolute; left: 42px; top: 15px;
        font-size: 14.5px; color: #666; pointer-events: none;
        transition: all .16s ease; transform-origin: left;
    }
    .field-group input:focus ~ label,
    .field-group input:not(:placeholder-shown) ~ label {
        top: 6px; font-size: 10px; font-weight: 600; letter-spacing: .3px;
        color: var(--cc-orange2); text-transform: uppercase;
    }

    .field-valid {
        position: absolute; right: 15px; top: 21px; color: #22c55e; font-size: 13px;
        opacity: 0; transform: scale(.5); transition: all .2s;
    }
    .field-valid.show { opacity: 1; transform: scale(1); }

    .field-toggle {
        position: absolute; right: 12px; top: 12px;
        width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;
        background: none; border: none; color: #555; cursor: pointer; border-radius: 8px;
        transition: color .15s;
    }
    .field-toggle:hover { color: #ccc; }

    /* ── Se souvenir de moi ── */
    .remember-row {
        display: flex; align-items: center; gap: 10px;
        margin: 4px 0 22px; cursor: pointer; user-select: none;
    }
    .remember-row input { position: absolute; opacity: 0; width: 0; height: 0; }
    .remember-box {
        width: 18px; height: 18px; border-radius: 5px; flex-shrink: 0;
        border: 1.5px solid var(--cc-border); background: var(--cc-dark2);
        display: flex; align-items: center; justify-content: center;
        transition: all .15s;
    }
    .remember-box i { font-size: 9px; color: #fff; opacity: 0; transform: scale(.5); transition: all .15s; }
    .remember-row input:checked ~ .remember-box {
        background: var(--cc-orange); border-color: var(--cc-orange);
    }
    .remember-row input:checked ~ .remember-box i { opacity: 1; transform: scale(1); }
    .remember-label { font-size: 12.5px; color: #777; }

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
        transform: skewX(-20deg);
        animation: shine 3.2s infinite;
    }
    @keyframes shine {
        0%   { left: -60%; }
        40%  { left: 130%; }
        100% { left: 130%; }
    }

    /* ── Lien inscription ── */
    .login-register-link { text-align: center; margin-top: 22px; }
    .login-register-link p { font-size: 13px; color: #555; margin: 0; }
    .login-register-link a { color: var(--cc-orange2); font-weight: 700; text-decoration: none; }
    .login-register-link a:hover { text-decoration: underline; }

    /* ── Responsive fine-tuning ── */
    @media (max-width: 420px) {
        .login-card { padding: 28px 20px; border-radius: 18px; }
        .field-group input { font-size: 16px; } /* évite le zoom auto iOS */
    }
</style>
@endpush

@push('scripts')
<script>
// ── Validation email en temps réel ───────────────────────────
const emailInput = document.getElementById('emailInput');
const emailValid = document.getElementById('emailValid');
const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

emailInput.addEventListener('input', () => {
    emailValid.classList.toggle('show', emailRegex.test(emailInput.value));
});
if (emailInput.value) emailValid.classList.toggle('show', emailRegex.test(emailInput.value));

// ── État de chargement au submit ─────────────────────────────
document.getElementById('loginForm').addEventListener('submit', function () {
    const btn   = document.getElementById('submitBtn');
    const icon  = document.getElementById('submitIcon');
    const label = document.getElementById('submitLabel');

    btn.disabled = true;
    icon.className = 'fa-solid fa-spinner fa-spin';
    label.textContent = 'Connexion en cours...';
});
</script>
@endpush