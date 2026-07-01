@extends('layouts.app')

@section('title', 'Paramètres')
@section('page-title', 'Paramètres du restaurant')

@push('styles')
<style>
    :root {
        --cc-orange:  #ea580c;
        --cc-orange2: #f97316;
        --cc-dark2:   #0d0d0d;
        --cc-dark3:   #141414;
        --cc-border:  #1f1f1f;
    }

    .param-nav {
        display: flex;
        flex-direction: column;
        gap: 2px;
        width: 220px;
        flex-shrink: 0;
    }

    .param-nav-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 500;
        color: #555;
        cursor: pointer;
        transition: all .18s;
        border: none;
        background: none;
        font-family: inherit;
        text-align: left;
        width: 100%;
    }

    .param-nav-item:hover { background: #141414; color: #ccc; }

    .param-nav-item.active {
        background: rgba(234,88,12,.12);
        color: #f97316;
        border: 1px solid rgba(234,88,12,.2);
    }

    .param-nav-item .nav-ico {
        width: 28px; height: 28px;
        border-radius: 7px;
        display: flex; align-items: center; justify-content: center;
        font-size: 12px;
        flex-shrink: 0;
        background: #1a1a1a;
    }

    .param-nav-item.active .nav-ico {
        background: rgba(234,88,12,.15);
        color: var(--cc-orange);
    }

    .param-section { display: none; }
    .param-section.active { display: block; }

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

    .field-group { margin-bottom: 18px; }
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

    .field-input.is-error { border-color: #ef4444; }

    .photo-zone {
        border: 2px dashed #1f1f1f;
        border-radius: 12px;
        background: var(--cc-dark2);
        min-height: 140px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: border-color .2s, background .2s;
        position: relative;
        overflow: hidden;
    }

    .photo-zone:hover {
        border-color: var(--cc-orange);
        background: rgba(234,88,12,.03);
    }

    .photo-zone.dragover {
        border-color: var(--cc-orange);
        background: rgba(234,88,12,.06);
    }

    .photo-zone img {
        position: absolute; inset: 0;
        width: 100%; height: 100%;
        object-fit: contain;
        padding: 12px;
    }

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

    .btn-danger {
        background: rgba(239,68,68,.08);
        border: 1px solid rgba(239,68,68,.2);
        color: #f87171;
    }
    .btn-danger:hover { background: #ef4444; color: #fff; }

    .btn-sm { padding: 6px 12px; font-size: 11px; border-radius: 8px; }

    .wa-preview {
        background: rgba(37,211,102,.07);
        border: 1px solid rgba(37,211,102,.2);
        border-radius: 10px;
        padding: 12px 16px;
        font-size: 12px;
        color: #4ade80;
        word-break: break-all;
    }

    .sys-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #1a1a1a;
        font-size: 12px;
    }

    .sys-row:last-child { border-bottom: none; }
    .sys-row-label { color: #444; }
    .sys-row-val   { color: #888; font-weight: 500; }

    .server-errors {
        background: rgba(239,68,68,.07);
        border: 1px solid rgba(239,68,68,.2);
        border-radius: 10px;
        padding: 12px 16px;
        margin-bottom: 20px;
        font-size: 12px;
        color: #f87171;
    }

    .char-count {
        font-size: 10px;
        color: #333;
        text-align: right;
        margin-top: 3px;
    }

    .char-count.warn   { color: #eab308; }
    .char-count.danger { color: #ef4444; }

    .section-divider {
        height: 1px;
        background: #1a1a1a;
        margin: 20px 0;
    }
</style>
@endpush

@section('content')

{{-- Alertes flash --}}
@if(session('success'))
<div style="display:flex;align-items:center;gap:10px;padding:12px 16px;
            border-radius:10px;margin-bottom:18px;font-size:13px;
            background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2);color:#22c55e;">
    <i class="fa-solid fa-circle-check"></i>
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div style="display:flex;align-items:center;gap:10px;padding:12px 16px;
            border-radius:10px;margin-bottom:18px;font-size:13px;
            background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);color:#f87171;">
    <i class="fa-solid fa-circle-exclamation"></i>
    {{ session('error') }}
</div>
@endif

<div style="display:flex;gap:20px;align-items:flex-start;">

    {{-- ── Navigation verticale ── --}}
    <nav class="param-nav">

        <div style="font-size:10px;color:#333;text-transform:uppercase;
                    letter-spacing:.5px;padding:4px 14px;margin-bottom:4px;">
            Paramètres
        </div>

        <button class="param-nav-item active"
                onclick="afficherSection('identite', this)">
            <div class="nav-ico"><i class="fa-solid fa-store"></i></div>
            Identité
        </button>

        <button class="param-nav-item"
                onclick="afficherSection('coordonnees', this)">
            <div class="nav-ico"><i class="fa-solid fa-location-dot"></i></div>
            Coordonnées
        </button>

        <button class="param-nav-item"
                onclick="afficherSection('whatsapp', this)">
            <div class="nav-ico"><i class="fa-brands fa-whatsapp"></i></div>
            WhatsApp
        </button>

        <button class="param-nav-item"
                onclick="afficherSection('caisse', this)">
            <div class="nav-ico"><i class="fa-solid fa-cash-register"></i></div>
            Caisse & reçus
        </button>

        <button class="param-nav-item"
                onclick="afficherSection('systeme', this)">
            <div class="nav-ico"><i class="fa-solid fa-circle-info"></i></div>
            Système
        </button>

        <div style="height:1px;background:#1a1a1a;margin:8px 0;"></div>

        <div style="font-size:10px;color:#333;text-transform:uppercase;
                    letter-spacing:.5px;padding:4px 14px;margin-bottom:4px;">
            Raccourcis
        </div>

        <a href="{{ route('admin.categories.index') }}"
           class="param-nav-item" style="text-decoration:none;">
            <div class="nav-ico"><i class="fa-solid fa-layer-group"></i></div>
            Catégories
        </a>


        <a href="{{ route('admin.statuts.index') }}"
           class="param-nav-item" style="text-decoration:none;">
            <div class="nav-ico"><i class="fa-solid fa-list-check"></i></div>
            Status
        </a>


        <a href="{{ route('admin.historiques.index') }}"
           class="param-nav-item" style="text-decoration:none;">
            <div class="nav-ico"><i class="fa-solid fa-clock-rotate-left"></i></div>
            Historiques
        </a>

    </nav>

    {{-- ── Contenu principal ── --}}
    <div style="flex:1;min-width:0;">

        @if($errors->any())
        <div class="server-errors">
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

        {{-- ════════════════════════════════════════════════
             SECTION 1 : IDENTITÉ
        ════════════════════════════════════════════════ --}}
        <div id="section-identite" class="param-section active">

            <form method="POST"
                  action="{{ route('admin.parametres.update') }}"
                  enctype="multipart/form-data"
                  id="formIdentite">
                @csrf @method('PUT')
                <input type="hidden" name="section" value="identite">

                <div class="card">
                    <div class="card-header">
                        <div style="width:32px;height:32px;border-radius:8px;
                                    background:rgba(234,88,12,.15);
                                    display:flex;align-items:center;justify-content:center;">
                            <i class="fa-solid fa-store"
                               style="color:var(--cc-orange);font-size:13px;"></i>
                        </div>
                        <div>
                            <div class="card-header-title">Identité du restaurant</div>
                            <div style="font-size:11px;color:#444;margin-top:1px;">
                                Ces informations apparaissent sur les reçus et la page publique.
                            </div>
                        </div>
                    </div>
                    <div class="card-body">

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">

                            <div class="field-group">
                                <label class="field-label" for="nom_restaurant">
                                    Nom du restaurant *
                                </label>
                                <input type="text"
                                       name="nom_restaurant"
                                       id="nom_restaurant"
                                       class="field-input {{ $errors->has('nom_restaurant') ? 'is-error' : '' }}"
                                       value="{{ old('nom_restaurant', $parametre->nom_restaurant) }}"
                                       placeholder="Chez Clarence"
                                       maxlength="128"
                                       required
                                       oninput="compterChars('nom_restaurant','cnt_nom',128)">
                                <div class="char-count" id="cnt_nom">0 / 128</div>
                            </div>

                            <div class="field-group">
                                <label class="field-label" for="slogan">
                                    Slogan <span class="hint">(optionnel)</span>
                                </label>
                                <input type="text"
                                       name="slogan"
                                       id="slogan"
                                       class="field-input"
                                       value="{{ old('slogan', $parametre->slogan) }}"
                                       placeholder="Le goût de chez nous..."
                                       maxlength="200"
                                       oninput="compterChars('slogan','cnt_slogan',200)">
                                <div class="char-count" id="cnt_slogan">0 / 200</div>
                            </div>
                        </div>

                        <div class="field-group">
                            <label class="field-label" for="description">
                                Description <span class="hint">(affichée sur la page publique)</span>
                            </label>
                            <textarea name="description"
                                      id="description"
                                      class="field-input"
                                      rows="3"
                                      style="resize:vertical;min-height:70px;"
                                      maxlength="600"
                                      placeholder="Quelques mots sur votre restaurant..."
                                      oninput="compterChars('description','cnt_desc',600)">{{ old('description', $parametre->description) }}</textarea>
                            <div class="char-count" id="cnt_desc">0 / 600</div>
                        </div>

                    </div>
                </div>

                {{-- Logo --}}
                <div class="card">
                    <div class="card-header">
                        <i class="fa-solid fa-image"
                           style="color:var(--cc-orange);font-size:15px;"></i>
                        <div>
                            <div class="card-header-title">Logo</div>
                            <div style="font-size:11px;color:#444;margin-top:1px;">
                                Apparaît en haut des reçus et sur la page WhatsApp.
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div style="display:grid;grid-template-columns:200px 1fr;gap:20px;align-items:start;">

                            <div>
                                <div class="photo-zone"
                                     id="logoZone"
                                     onclick="document.getElementById('logo').click()"
                                     ondragover="logoDragOver(event)"
                                     ondragleave="logoDragLeave(event)"
                                     ondrop="logoDrop(event)">

                                    <img id="logoPreview"
                                         src="{{ $parametre->logo ? asset('storage/' . $parametre->logo) : '' }}"
                                         alt="Logo"
                                         style="{{ $parametre->logo ? '' : 'display:none;' }}">

                                    <div id="logoPlaceholder"
                                         style="{{ $parametre->logo ? 'display:none;' : '' }}
                                                text-align:center;padding:16px;pointer-events:none;">
                                        <i class="fa-solid fa-cloud-arrow-up"
                                           style="font-size:28px;color:#2a2a2a;display:block;margin-bottom:6px;"></i>
                                        <div style="font-size:11px;color:#444;">Cliquez ou glissez</div>
                                        <div style="font-size:10px;color:#333;margin-top:2px;">
                                            PNG, JPG · Max 2 Mo
                                        </div>
                                    </div>
                                </div>

                                <input type="file"
                                       name="logo"
                                       id="logo"
                                       accept="image/jpeg,image/png,image/jpg,image/webp"
                                       style="display:none;"
                                       onchange="handleLogo(this)">
                            </div>

                            <div>
                                @if($parametre->logo)
                                <div style="display:flex;align-items:center;gap:8px;
                                            padding:10px 12px;border-radius:9px;margin-bottom:12px;
                                            background:rgba(34,197,94,.06);
                                            border:1px solid rgba(34,197,94,.15);
                                            font-size:11px;color:#22c55e;">
                                    <i class="fa-solid fa-circle-check"></i>
                                    Logo actuel enregistré
                                </div>
                                @endif

                                <div id="logoInfo"
                                     style="display:none;padding:8px 10px;border-radius:8px;
                                            background:#0a0a0a;border:1px solid #1a1a1a;
                                            font-size:11px;color:#888;margin-bottom:12px;">
                                    <i class="fa-solid fa-file-image" style="margin-right:4px;"></i>
                                    <span id="logoNom"></span>
                                </div>

                                <p style="font-size:11px;color:#444;line-height:1.6;margin-bottom:12px;">
                                    Le logo idéal est carré ou rectangulaire, sur fond blanc ou transparent
                                    (PNG recommandé). Il sera redimensionné automatiquement à 200×200 px maximum.
                                </p>

                                @if($parametre->logo)
                                <button type="button"
                                        onclick="supprimerLogo()"
                                        class="btn btn-danger btn-sm">
                                    <i class="fa-solid fa-trash"></i>
                                    Supprimer le logo
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div style="display:flex;justify-content:flex-end;gap:8px;">
                    <button type="submit" id="btnIdentite" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk" id="icoIdentite"></i>
                        <span id="txtIdentite">Enregistrer l'identité</span>
                    </button>
                </div>

            </form>

            @if($parametre->logo)
            <form method="POST"
                  action="{{ route('admin.parametres.supprimer-logo') }}"
                  id="deleteLogoForm" style="display:none;">
                @csrf @method('DELETE')
            </form>
            @endif

        </div>

        {{-- ════════════════════════════════════════════════
             SECTION 2 : COORDONNÉES
        ════════════════════════════════════════════════ --}}
        <div id="section-coordonnees" class="param-section">

            <form method="POST"
                  action="{{ route('admin.parametres.update') }}"
                  id="formCoordonnees">
                @csrf @method('PUT')
                <input type="hidden" name="section" value="coordonnees">

                <div class="card">
                    <div class="card-header">
                        <div style="width:32px;height:32px;border-radius:8px;
                                    background:rgba(234,88,12,.15);
                                    display:flex;align-items:center;justify-content:center;">
                            <i class="fa-solid fa-location-dot"
                               style="color:var(--cc-orange);font-size:13px;"></i>
                        </div>
                        <div>
                            <div class="card-header-title">Coordonnées du restaurant</div>
                            <div style="font-size:11px;color:#444;margin-top:1px;">
                                Affichées sur les reçus et la page publique.
                            </div>
                        </div>
                    </div>
                    <div class="card-body">

                        <div class="field-group">
                            <label class="field-label" for="adresse">Adresse complète</label>
                            <textarea name="adresse"
                                      id="adresse"
                                      class="field-input"
                                      rows="2"
                                      style="resize:vertical;"
                                      maxlength="300"
                                      placeholder="Quartier, Rue, Numéro — Douala, Cameroun">{{ old('adresse', $parametre->adresse) }}</textarea>
                        </div>

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">

                            <div class="field-group">
                                <label class="field-label" for="telephone">Téléphone principal</label>
                                <input type="tel"
                                       name="telephone"
                                       id="telephone"
                                       class="field-input"
                                       value="{{ old('telephone', $parametre->telephone) }}"
                                       placeholder="+237 6XX XXX XXX"
                                       maxlength="20">
                            </div>

                            <div class="field-group">
                                <label class="field-label" for="telephone2">
                                    Téléphone secondaire <span class="hint">(optionnel)</span>
                                </label>
                                <input type="tel"
                                       name="telephone2"
                                       id="telephone2"
                                       class="field-input"
                                       value="{{ old('telephone2', $parametre->telephone2) }}"
                                       placeholder="+237 6XX XXX XXX"
                                       maxlength="20">
                            </div>

                            <div class="field-group">
                                <label class="field-label" for="email">
                                    Email de contact <span class="hint">(optionnel)</span>
                                </label>
                                <input type="email"
                                       name="email"
                                       id="email"
                                       class="field-input {{ $errors->has('email') ? 'is-error' : '' }}"
                                       value="{{ old('email', $parametre->email) }}"
                                       placeholder="contact@chezclarence.cm"
                                       maxlength="150">
                            </div>

                            <div class="field-group">
                                <label class="field-label" for="ville">Ville</label>
                                <input type="text"
                                       name="ville"
                                       id="ville"
                                       class="field-input"
                                       value="{{ old('ville', $parametre->ville ?? 'Douala') }}"
                                       placeholder="Douala"
                                       maxlength="100">
                            </div>
                        </div>

                        <div class="field-group">
                            <label class="field-label" for="horaires">
                                Horaires d'ouverture <span class="hint">(affiché sur la page publique)</span>
                            </label>
                            <input type="text"
                                   name="horaires"
                                   id="horaires"
                                   class="field-input"
                                   value="{{ old('horaires', $parametre->horaires) }}"
                                   placeholder="Lun–Sam : 7h–23h · Dim : 9h–21h"
                                   maxlength="200">
                        </div>

                    </div>
                </div>

                <div style="display:flex;justify-content:flex-end;">
                    <button type="submit" class="btn btn-primary"
                            onclick="loaderBtn(this,'Enregistrement...')">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Enregistrer les coordonnées
                    </button>
                </div>
            </form>

        </div>

        {{-- ════════════════════════════════════════════════
             SECTION 3 : WHATSAPP
             Supprimé : toggle page_publique et son champ caché
        ════════════════════════════════════════════════ --}}
        <div id="section-whatsapp" class="param-section">

            <form method="POST"
                  action="{{ route('admin.parametres.update') }}"
                  id="formWhatsapp">
                @csrf @method('PUT')
                <input type="hidden" name="section" value="whatsapp">

                <div class="card">
                    <div class="card-header">
                        <div style="width:32px;height:32px;border-radius:8px;
                                    background:rgba(37,211,102,.12);
                                    display:flex;align-items:center;justify-content:center;">
                            <i class="fa-brands fa-whatsapp"
                               style="color:#25d366;font-size:16px;"></i>
                        </div>
                        <div>
                            <div class="card-header-title">WhatsApp & commandes</div>
                            <div style="font-size:11px;color:#444;margin-top:1px;">
                                Configurez le lien de commande WhatsApp affiché sur la page publique.
                            </div>
                        </div>
                    </div>
                    <div class="card-body">

                        <div class="field-group">
                            <label class="field-label" for="whatsapp">Numéro WhatsApp *</label>
                            <input type="text"
                                   name="whatsapp"
                                   id="whatsapp"
                                   class="field-input {{ $errors->has('whatsapp') ? 'is-error' : '' }}"
                                   value="{{ old('whatsapp', $parametre->whatsapp) }}"
                                   placeholder="+237 6XX XXX XXX"
                                   maxlength="20"
                                   oninput="majAperçuWA()">
                            <div style="font-size:11px;color:#444;margin-top:5px;">
                                Format international sans espaces :
                                <code style="color:#666;">+237699000000</code>
                            </div>
                        </div>

                        <div class="field-group">
                            <label class="field-label" for="message_whatsapp">
                                Message d'accueil pré-rempli
                                <span class="hint">(envoyé automatiquement au client)</span>
                            </label>
                            <textarea name="message_whatsapp"
                                      id="message_whatsapp"
                                      class="field-input"
                                      rows="3"
                                      style="resize:vertical;"
                                      maxlength="500"
                                      placeholder="Bonjour ! Je souhaite passer une commande..."
                                      oninput="compterChars('message_whatsapp','cnt_wa',500); majAperçuWA()">{{ old('message_whatsapp', $parametre->message_whatsapp) }}</textarea>
                            <div class="char-count" id="cnt_wa">0 / 500</div>
                        </div>

                        {{-- Aperçu du lien généré --}}
                        <div class="field-group">
                            <label class="field-label">Aperçu du lien généré</label>
                            <div class="wa-preview">
                                <i class="fa-brands fa-whatsapp" style="margin-right:5px;"></i>
                                <span id="waLien">
                                    @php
                                        $tel = preg_replace('/\D/', '', $parametre->whatsapp ?? '');
                                        $msg = urlencode($parametre->message_whatsapp ?? '');
                                        $url_whatsapp= 'https://wa.me/' . $tel . ($msg ? '?text=' . $msg : '');
                                    @endphp
                                    {{ $tel ? $url_whatsapp : '—' }}
                                </span>
                                @if($tel)
                                <div class="text-right">
                                    <a target="_blank" href="{{ $tel ? $url_whatsapp : '#' }}" class="btn btn-success">
                                        {{ $tel ? 'Tester le lien' : '' }}
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>

                <div style="display:flex;justify-content:flex-end;">
                    <button type="submit" class="btn btn-primary"
                            onclick="loaderBtn(this,'Enregistrement...')">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Enregistrer WhatsApp
                    </button>
                </div>
            </form>

        </div>

        {{-- ════════════════════════════════════════════════
             SECTION 4 : CAISSE & REÇUS
             Supprimé : champ impression_auto
        ════════════════════════════════════════════════ --}}
        <div id="section-caisse" class="param-section">

            <form method="POST"
                  action="{{ route('admin.parametres.update') }}"
                  id="formCaisse">
                @csrf @method('PUT')
                <input type="hidden" name="section" value="caisse">

                <div class="card">
                    <div class="card-header">
                        <div style="width:32px;height:32px;border-radius:8px;
                                    background:rgba(234,88,12,.15);
                                    display:flex;align-items:center;justify-content:center;">
                            <i class="fa-solid fa-cash-register"
                               style="color:var(--cc-orange);font-size:13px;"></i>
                        </div>
                        <div>
                            <div class="card-header-title">Caisse & reçus</div>
                            <div style="font-size:11px;color:#444;margin-top:1px;">
                                Paramètres utilisés lors de la génération des reçus PDF.
                            </div>
                        </div>
                    </div>
                    <div class="card-body">

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">

                            <div class="field-group">
                                <label class="field-label" for="devise">Devise</label>
                                <select name="devise" id="devise" class="field-input">
                                    <option value="FCFA" {{ old('devise', $parametre->devise ?? 'FCFA') === 'FCFA' ? 'selected' : '' }}>
                                        FCFA (Franc CFA)
                                    </option>
                                    <option value="EUR" {{ old('devise', $parametre->devise ?? '') === 'EUR' ? 'selected' : '' }}>
                                        EUR (Euro)
                                    </option>
                                    <option value="USD" {{ old('devise', $parametre->devise ?? '') === 'USD' ? 'selected' : '' }}>
                                        USD (Dollar)
                                    </option>
                                    <option value="XOF" {{ old('devise', $parametre->devise ?? '') === 'XOF' ? 'selected' : '' }}>
                                        XOF (Franc Ouest-Africain)
                                    </option>
                                </select>
                            </div>

                            <div class="field-group">
                                <label class="field-label" for="tva">
                                    TVA applicable (%)
                                    <span class="hint">(0 = non applicable)</span>
                                </label>
                                <input type="number"
                                       name="tva"
                                       id="tva"
                                       class="field-input"
                                       value="{{ old('tva', $parametre->tva ?? 0) }}"
                                       min="0" max="100" step="0.1"
                                       placeholder="0">
                            </div>

                            <div class="field-group">
                                <label class="field-label" for="prefixe_recu">
                                    Préfixe des numéros de reçu
                                </label>
                                <input type="text"
                                       name="prefixe_recu"
                                       id="prefixe_recu"
                                       class="field-input"
                                       value="{{ old('prefixe_recu', $parametre->prefixe_recu ?? 'CC') }}"
                                       placeholder="CC"
                                       maxlength="5">
                                <div style="font-size:10px;color:#333;margin-top:4px;">
                                    Ex: <code style="color:#555;">CC-2026-00123</code>
                                </div>
                            </div>

                        </div>

                        <div class="section-divider"></div>

                        <div class="field-group">
                            <label class="field-label" for="pied_recu">
                                Message de bas de reçu
                                <span class="hint">(remerciements, slogan, promo...)</span>
                            </label>
                            <textarea name="pied_recu"
                                      id="pied_recu"
                                      class="field-input"
                                      rows="2"
                                      style="resize:vertical;"
                                      maxlength="300"
                                      placeholder="Merci pour votre visite ! Revenez nous voir bientôt."
                                      oninput="compterChars('pied_recu','cnt_pied',300)">{{ old('pied_recu', $parametre->pied_recu) }}</textarea>
                            <div class="char-count" id="cnt_pied">0 / 300</div>
                        </div>

                        <div class="field-group">
                            <label class="field-label" for="mention_legale">
                                Mention légale / NINEA
                                <span class="hint">(optionnel — affiché en bas du reçu)</span>
                            </label>
                            <input type="text"
                                   name="mention_legale"
                                   id="mention_legale"
                                   class="field-input"
                                   value="{{ old('mention_legale', $parametre->mention_legale) }}"
                                   placeholder="RC/DLA/2018/B/1234 · TVA non applicable (Art. 236 CGI)"
                                   maxlength="200">
                        </div>

                    </div>
                </div>

                <div style="display:flex;justify-content:flex-end;">
                    <button type="submit" class="btn btn-primary"
                            onclick="loaderBtn(this,'Enregistrement...')">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Enregistrer la caisse
                    </button>
                </div>
            </form>

        </div>

        {{-- ════════════════════════════════════════════════
             SECTION 5 : SYSTÈME (identique)
        ════════════════════════════════════════════════ --}}
        <div id="section-systeme" class="param-section">

            <div class="card">
                <div class="card-header">
                    <div style="width:32px;height:32px;border-radius:8px;background:#1a1a1a;
                                display:flex;align-items:center;justify-content:center;">
                        <i class="fa-solid fa-circle-info" style="color:#555;font-size:13px;"></i>
                    </div>
                    <div>
                        <div class="card-header-title">Informations système</div>
                        <div style="font-size:11px;color:#444;margin-top:1px;">
                            Lecture seule — informations techniques de l'installation.
                        </div>
                    </div>
                </div>
                <div class="card-body">

                    <div class="sys-row">
                        <span class="sys-row-label">Version de l'application</span>
                        <span class="sys-row-val">
                            {{ config('app.name') }} {{ config('app.version') }}
                        </span>
                    </div>
                    <div class="sys-row">
                        <span class="sys-row-label">Framework</span>
                        <span class="sys-row-val">Laravel {{ app()->version() }}</span>
                    </div>
                    <div class="sys-row">
                        <span class="sys-row-label">PHP</span>
                        <span class="sys-row-val">{{ phpversion() }}</span>
                    </div>
                    <div class="sys-row">
                        <span class="sys-row-label">Base de données</span>
                        <span class="sys-row-val">
                            MySQL {{ DB::select('SELECT VERSION() as v')[0]->v ?? '—' }}
                        </span>
                    </div>
                    <div class="sys-row">
                        <span class="sys-row-label">Environnement</span>
                        <span class="sys-row-val"
                              style="color:{{ app()->environment('production') ? '#22c55e' : '#eab308' }};">
                            {{ app()->environment() }}
                        </span>
                    </div>
                    <div class="sys-row">
                        <span class="sys-row-label">Debug mode</span>
                        <span class="sys-row-val"
                              style="color:{{ config('app.debug') ? '#f87171' : '#22c55e' }};">
                            {{ config('app.debug') ? '⚠ Activé (désactiver en production)' : 'Désactivé' }}
                        </span>
                    </div>
                    <div class="sys-row">
                        <span class="sys-row-label">URL de l'application</span>
                        <span class="sys-row-val">{{ config('app.url') }}</span>
                    </div>
                    <div class="sys-row">
                        <span class="sys-row-label">Stockage (lien symbolique)</span>
                        @php $lienOk = file_exists(public_path('storage')); @endphp
                        <span class="sys-row-val" style="color:{{ $lienOk ? '#22c55e' : '#f87171' }};">
                            {{ $lienOk ? '✓ Fonctionnel' : '✗ Absent — exécuter php artisan storage:link' }}
                        </span>
                    </div>
                    <div class="sys-row">
                        <span class="sys-row-label">Paramètres créés le</span>
                        <span class="sys-row-val">{{ $parametre->created_at->format('d/m/Y à H:i') }}</span>
                    </div>
                    <div class="sys-row">
                        <span class="sys-row-label">Dernière modification</span>
                        <span class="sys-row-val">{{ $parametre->updated_at->format('d/m/Y à H:i') }}</span>
                    </div>

                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <i class="fa-solid fa-wrench" style="color:#555;font-size:14px;"></i>
                    <div class="card-header-title">Actions de maintenance</div>
                </div>
                <div class="card-body">
                    <div style="display:flex;flex-direction:column;gap:10px;">
                        <div style="display:flex;align-items:center;justify-content:space-between;
                                    padding:12px 14px;border-radius:9px;
                                    background:var(--cc-dark2);border:1px solid #1a1a1a;">
                            <div>
                                <div style="font-size:13px;font-weight:600;color:#e5e5e5;">
                                    Vider le cache
                                </div>
                                <div style="font-size:11px;color:#444;margin-top:1px;">
                                    Efface le cache de configuration, de vues et de routes.
                                </div>
                            </div>
                            <form method="POST" action="{{ route('admin.parametres.vider-cache') }}">
                                @csrf
                                <button type="submit" class="btn btn-ghost btn-sm">
                                    <i class="fa-solid fa-broom"></i>
                                    Vider
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
// ════════════════════════════════════════════════════════════
// NAVIGATION PAR ONGLETS
// ════════════════════════════════════════════════════════════

function afficherSection(id, btn) {
    document.querySelectorAll('.param-section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.param-nav-item').forEach(b => b.classList.remove('active'));
    document.getElementById('section-' + id).classList.add('active');
    if (btn) btn.classList.add('active');
}

document.addEventListener('DOMContentLoaded', () => {
    @if($errors->any())
    const section = '{{ old("section", "identite") }}';
    const btn = document.querySelector(`[onclick*="${section}"]`);
    afficherSection(section, btn);
    @endif

    initCompteurs();
    majAperçuWA();
});

function initCompteurs() {
    [
        ['nom_restaurant',   'cnt_nom',    128],
        ['slogan',           'cnt_slogan', 200],
        ['description',      'cnt_desc',   600],
        ['message_whatsapp', 'cnt_wa',     500],
        ['pied_recu',        'cnt_pied',   300],
    ].forEach(([id, cnt, max]) => compterChars(id, cnt, max));
}

// ════════════════════════════════════════════════════════════
// COMPTEUR DE CARACTÈRES
// ════════════════════════════════════════════════════════════

function compterChars(inputId, counterId, max) {
    const el  = document.getElementById(inputId);
    const cnt = document.getElementById(counterId);
    if (!el || !cnt) return;
    const len = el.value.length;
    cnt.textContent = `${len} / ${max}`;
    cnt.className   = 'char-count';
    if (len >= max * 0.9)      cnt.classList.add('danger');
    else if (len >= max * 0.7) cnt.classList.add('warn');
}

// ════════════════════════════════════════════════════════════
// APERÇU DU LIEN WHATSAPP
// ════════════════════════════════════════════════════════════

function majAperçuWA() {
    const tel = (document.getElementById('whatsapp')?.value || '').replace(/\D/g, '');
    const msg = encodeURIComponent(document.getElementById('message_whatsapp')?.value || '');
    const lienEl = document.getElementById('waLien');
    if (!lienEl) return;
    lienEl.textContent = tel
        ? `https://wa.me/${tel}${msg ? '?text=' + msg : ''}`
        : '— (numéro non renseigné)';
}

// ════════════════════════════════════════════════════════════
// LOADER BOUTON
// ════════════════════════════════════════════════════════════

function loaderBtn(btn, texte) {
    btn.disabled  = true;
    btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> ${texte}`;
}

// ════════════════════════════════════════════════════════════
// LOGO
// ════════════════════════════════════════════════════════════

function handleLogo(input) {
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
            icon: 'warning', title: 'Le logo ne doit pas dépasser 2 Mo.',
            timer: 3000, showConfirmButton: false,
            background: '#141414', color: '#e5e5e5',
        });
        input.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('logoPreview').src           = e.target.result;
        document.getElementById('logoPreview').style.display = 'block';
        document.getElementById('logoPlaceholder').style.display = 'none';
        document.getElementById('logoNom').textContent       = file.name;
        document.getElementById('logoInfo').style.display    = 'block';
    };
    reader.readAsDataURL(file);
}

function logoDragOver(e) {
    e.preventDefault();
    document.getElementById('logoZone').classList.add('dragover');
}

function logoDragLeave(e) {
    document.getElementById('logoZone').classList.remove('dragover');
}

function logoDrop(e) {
    e.preventDefault();
    document.getElementById('logoZone').classList.remove('dragover');
    const files = e.dataTransfer.files;
    if (!files.length) return;
    const input = document.getElementById('logo');
    const dt = new DataTransfer();
    dt.items.add(files[0]);
    input.files = dt.files;
    handleLogo(input);
}

function supprimerLogo() {
    Swal.fire({
        title: 'Supprimer le logo ?',
        text: 'Le logo actuel sera définitivement supprimé.',
        icon: 'warning', iconColor: '#ef4444',
        background: '#141414', color: '#e5e5e5',
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Oui, supprimer',
        showCancelButton: true,
        cancelButtonText: 'Annuler', cancelButtonColor: '#1f1f1f',
    }).then(r => {
        if (r.isConfirmed) document.getElementById('deleteLogoForm')?.submit();
    });
}

document.getElementById('formIdentite')?.addEventListener('submit', function() {
    const btn = document.getElementById('btnIdentite');
    const ico = document.getElementById('icoIdentite');
    const txt = document.getElementById('txtIdentite');
    if (btn) btn.disabled = true;
    if (ico) ico.className = 'fa-solid fa-spinner fa-spin';
    if (txt) txt.textContent = 'Enregistrement...';
});
</script>
@endpush