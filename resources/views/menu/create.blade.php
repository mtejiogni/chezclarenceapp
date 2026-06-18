@extends('layouts.app')

@section('title', 'Nouveau plat')
@section('page-title', 'Nouveau plat')

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
        gap: 10px;
    }

    .card-body { padding: 22px; }

    /* ── Champs ── */
    .field-group { margin-bottom: 18px; }

    .field-label {
        display: block;
        font-size: 11px;
        font-weight: 600;
        color: #555;
        letter-spacing: .5px;
        text-transform: uppercase;
        margin-bottom: 7px;
    }

    .field-label .req { color: var(--cc-orange); margin-left: 2px; }
    .field-label .hint {
        font-weight: 400; color: #333;
        text-transform: none; letter-spacing: 0; margin-left: 4px;
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

    /* ── Erreurs ── */
    .field-error {
        display: none;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        color: #f87171;
        margin-top: 5px;
    }

    .field-error.show { display: flex; }

    .server-errors {
        background: rgba(239,68,68,.07);
        border: 1px solid rgba(239,68,68,.2);
        border-radius: 10px;
        padding: 12px 16px;
        margin-bottom: 20px;
        font-size: 12px;
        color: #f87171;
    }

    /* ── Champ prix avec préfixe ── */
    .prix-wrap {
        position: relative;
    }

    .prix-wrap .prix-suffix {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 11px;
        color: #444;
        pointer-events: none;
        font-weight: 500;
    }

    .prix-wrap .field-input {
        padding-right: 56px;
    }

    /* ── Zone photo ── */
    .photo-zone {
        border: 2px dashed #1f1f1f;
        border-radius: 12px;
        background: var(--cc-dark2);
        min-height: 180px;
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
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .photo-zone-placeholder {
        text-align: center;
        padding: 20px;
        pointer-events: none;
    }

    /* ── Toggle statut ── */
    .toggle-field {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 14px;
        background: var(--cc-dark2);
        border: 1px solid var(--cc-border);
        border-radius: 10px;
        cursor: pointer;
        user-select: none;
    }

    .toggle-switch {
        width: 44px; height: 24px;
        border-radius: 12px;
        position: relative;
        transition: background .2s;
        flex-shrink: 0;
    }

    .toggle-switch.on  { background: #22c55e; }
    .toggle-switch.off { background: #2a2a2a; }

    .toggle-switch::after {
        content: '';
        position: absolute;
        top: 3px; left: 3px;
        width: 18px; height: 18px;
        border-radius: 50%;
        background: #fff;
        transition: left .2s;
        box-shadow: 0 1px 3px rgba(0,0,0,.3);
    }

    .toggle-switch.on::after { left: 23px; }

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

    /* ── Aperçu prix ── */
    .prix-preview {
        text-align: center;
        padding: 16px;
        background: rgba(234,88,12,.06);
        border: 1px solid rgba(234,88,12,.15);
        border-radius: 10px;
        margin-top: 10px;
    }

    /* ── Compteur chars ── */
    .char-count {
        font-size: 10px;
        color: #333;
        text-align: right;
        margin-top: 3px;
        transition: color .18s;
    }

    .char-count.warn   { color: #eab308; }
    .char-count.danger { color: #ef4444; }
</style>
@endpush

@section('content')

{{-- Fil d'Ariane --}}
<div style="display:flex;align-items:center;gap:8px;
            font-size:12px;color:#444;margin-bottom:20px;">
    <a href="{{ route('admin.menus.index') }}"
       style="color:#555;text-decoration:none;
              display:flex;align-items:center;gap:5px;"
       onmouseover="this.style.color='#f97316'"
       onmouseout="this.style.color='#555'">
        <i class="fa-solid fa-book-open"></i>
        Menus & Plats
    </a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;color:#333;"></i>
    <span style="color:#666;">Nouveau plat</span>
</div>

{{-- ══════════════════════════════════════════════════════════
     FORMULAIRE
══════════════════════════════════════════════════════════ --}}
<form method="POST"
      action="{{ route('admin.menus.store') }}"
      enctype="multipart/form-data"
      id="menuForm"
      novalidate>
    @csrf

    <div style="display:grid;grid-template-columns:1fr 320px;gap:16px;align-items:start;">

        {{-- ════════════════════════════════
             COLONNE GAUCHE
        ════════════════════════════════ --}}
        <div style="display:flex;flex-direction:column;gap:16px;">

            <div class="card">
                <div class="card-header">
                    <div style="width:34px;height:34px;border-radius:9px;
                                background:rgba(234,88,12,.15);
                                border:1px solid rgba(234,88,12,.2);
                                display:flex;align-items:center;justify-content:center;">
                        <i class="fa-solid fa-utensils"
                           style="color:var(--cc-orange);font-size:14px;"></i>
                    </div>
                    <div>
                        <h2 style="font-size:14px;font-weight:700;color:#e5e5e5;margin:0;">
                            Informations du plat
                        </h2>
                        <p style="font-size:11px;color:#444;margin:2px 0 0;">
                            Les champs marqués
                            <span style="color:var(--cc-orange);">*</span>
                            sont obligatoires
                        </p>
                    </div>
                </div>

                <div class="card-body">

                    {{-- Erreurs serveur --}}
                    @if($errors->any())
                    <div class="server-errors animate__animated animate__fadeInDown">
                        <div style="font-weight:600;margin-bottom:6px;
                                    display:flex;align-items:center;gap:6px;">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            Veuillez corriger les erreurs :
                        </div>
                        <ul style="padding-left:16px;margin:0;">
                            @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    {{-- ── Catégorie ── --}}
                    <div class="field-group">
                        <label class="field-label" for="idcategorie">
                            Catégorie <span class="req">*</span>
                        </label>
                        <select name="idcategorie"
                                id="idcategorie"
                                class="field-input {{ $errors->has('idcategorie') ? 'is-error' : '' }}"
                                onchange="validerCategorie()">
                            <option value="">— Sélectionner une catégorie —</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->idcategorie }}"
                                {{ old('idcategorie', request('categorie')) == $cat->idcategorie ? 'selected' : '' }}>
                                {{ $cat->intitule }}
                            </option>
                            @endforeach
                        </select>
                        <div class="field-error {{ $errors->has('idcategorie') ? 'show' : '' }}"
                             id="err_cat">
                            <i class="fa-solid fa-circle-xmark"></i>
                            <span id="err_cat_txt">{{ $errors->first('idcategorie') }}</span>
                        </div>
                    </div>

                    {{-- ── Nom du plat ── --}}
                    <div class="field-group">
                        <label class="field-label" for="intitule">
                            Nom du plat <span class="req">*</span>
                        </label>
                        <input type="text"
                               name="intitule"
                               id="intitule"
                               class="field-input {{ $errors->has('intitule') ? 'is-error' : '' }}"
                               value="{{ old('intitule') }}"
                               placeholder="Ex: Poulet braisé, Ndolé, Jus de gingembre..."
                               maxlength="128"
                               autocomplete="off"
                               oninput="
                                   validerIntitule();
                                   compterChars('intitule','cnt_intitule',128);
                               ">
                        <div class="char-count" id="cnt_intitule">0 / 128</div>
                        <div class="field-error {{ $errors->has('intitule') ? 'show' : '' }}"
                             id="err_intitule">
                            <i class="fa-solid fa-circle-xmark"></i>
                            <span id="err_intitule_txt">{{ $errors->first('intitule') }}</span>
                        </div>
                    </div>

                    {{-- ── Description ── --}}
                    <div class="field-group">
                        <label class="field-label" for="description">
                            Description <span class="hint">(optionnel)</span>
                        </label>
                        <textarea name="description"
                                  id="description"
                                  class="field-input {{ $errors->has('description') ? 'is-error' : '' }}"
                                  rows="3"
                                  style="resize:vertical;min-height:80px;"
                                  maxlength="1000"
                                  placeholder="Ingrédients, mode de préparation, allergènes..."
                                  oninput="compterChars('description','cnt_description',1000)">{{ old('description') }}</textarea>
                        <div class="char-count" id="cnt_description">0 / 1000</div>
                        @error('description')
                        <div class="field-error show">
                            <i class="fa-solid fa-circle-xmark"></i>
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    {{-- ── Prix unitaire ── --}}
                    <div class="field-group">
                        <label class="field-label" for="pu">
                            Prix unitaire <span class="req">*</span>
                        </label>
                        <div class="prix-wrap">
                            <input type="number"
                                   name="pu"
                                   id="pu"
                                   class="field-input {{ $errors->has('pu') ? 'is-error' : '' }}"
                                   value="{{ old('pu') }}"
                                   placeholder="Ex: 2500"
                                   min="1"
                                   max="9999999"
                                   step="50"
                                   oninput="validerPrix(); majApercu();">
                            <span class="prix-suffix">FCFA</span>
                        </div>
                        <div class="field-error {{ $errors->has('pu') ? 'show' : '' }}"
                             id="err_pu">
                            <i class="fa-solid fa-circle-xmark"></i>
                            <span id="err_pu_txt">{{ $errors->first('pu') }}</span>
                        </div>

                        {{-- Aperçu prix formaté --}}
                        <div class="prix-preview" id="prixPreview" style="display:none;">
                            <div style="font-size:10px;color:#444;margin-bottom:4px;
                                        text-transform:uppercase;letter-spacing:.5px;">
                                Aperçu du prix
                            </div>
                            <div style="font-size:22px;font-weight:700;color:#f97316;"
                                 id="prixFormate">—</div>
                            <div style="font-size:10px;color:#555;margin-top:2px;">FCFA</div>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        {{-- ════════════════════════════════
             COLONNE DROITE
        ════════════════════════════════ --}}
        <div style="display:flex;flex-direction:column;gap:16px;">

            {{-- ── Photo ── --}}
            <div class="card">
                <div class="card-header">
                    <i class="fa-solid fa-image"
                       style="color:var(--cc-orange);font-size:15px;"></i>
                    <h3 style="font-size:13px;font-weight:600;color:#e5e5e5;margin:0;">
                        Photo du plat
                        <span style="font-size:11px;font-weight:400;color:#444;">
                            (optionnel)
                        </span>
                    </h3>
                </div>
                <div class="card-body">

                    {{-- Zone drag & drop --}}
                    <div class="photo-zone"
                         id="photoZone"
                         onclick="document.getElementById('photo').click()"
                         ondragover="photoDragOver(event)"
                         ondragleave="photoDragLeave(event)"
                         ondrop="photoDrop(event)">

                        <img id="photoPreview" src="" alt="" style="display:none;">

                        <div class="photo-zone-placeholder" id="photoPlaceholder">
                            <i class="fa-solid fa-cloud-arrow-up"
                               style="font-size:30px;color:#2a2a2a;
                                      display:block;margin-bottom:8px;"></i>
                            <div style="font-size:12px;color:#444;font-weight:500;">
                                Cliquez ou glissez une image
                            </div>
                            <div style="font-size:10px;color:#333;margin-top:4px;">
                                JPG, PNG, WEBP · Max 2 Mo
                            </div>
                        </div>
                    </div>

                    <input type="file"
                           name="photo"
                           id="photo"
                           accept="image/jpeg,image/png,image/jpg,image/webp"
                           style="display:none;"
                           onchange="photoChanged(this)">

                    {{-- Info fichier --}}
                    <div id="photoInfo"
                         style="display:none;margin-top:8px;padding:8px 10px;
                                border-radius:8px;background:#0a0a0a;
                                border:1px solid #1a1a1a;
                                display:flex;align-items:center;
                                justify-content:space-between;gap:8px;">
                        <span style="font-size:11px;color:#888;overflow:hidden;
                                     text-overflow:ellipsis;white-space:nowrap;"
                              id="photoNom"></span>
                        <button type="button"
                                onclick="supprimerPhoto()"
                                style="background:none;border:none;color:#f87171;
                                       cursor:pointer;flex-shrink:0;font-size:13px;
                                       transition:color .18s;"
                                onmouseover="this.style.color='#ef4444'"
                                onmouseout="this.style.color='#f87171'">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    @error('photo')
                    <div class="field-error show" style="margin-top:6px;">
                        <i class="fa-solid fa-circle-xmark"></i>
                        {{ $message }}
                    </div>
                    @enderror
                </div>
            </div>

            {{-- ── Statut ── --}}
            <div class="card">
                <div class="card-header">
                    <i class="fa-solid fa-toggle-on"
                       style="color:var(--cc-orange);font-size:15px;"></i>
                    <h3 style="font-size:13px;font-weight:600;color:#e5e5e5;margin:0;">
                        Disponibilité
                    </h3>
                </div>
                <div class="card-body">

                    <div class="toggle-field" onclick="basculerStatut()">
                        <div>
                            <div style="font-size:13px;font-weight:600;color:#e5e5e5;"
                                 id="statutLabel">
                                Activé
                            </div>
                            <div style="font-size:11px;color:#444;margin-top:2px;"
                                 id="statutDesc">
                                Visible dans les commandes
                            </div>
                        </div>
                        <div class="toggle-switch on" id="toggleSwitch"></div>
                    </div>

                    <input type="hidden"
                           name="statut"
                           id="inputStatut"
                           value="{{ old('statut', 'Activé') }}">

                    @error('statut')
                    <div class="field-error show" style="margin-top:6px;">
                        <i class="fa-solid fa-circle-xmark"></i>
                        {{ $message }}
                    </div>
                    @enderror

                    {{-- Info contextuelle --}}
                    <div style="margin-top:12px;padding:10px 12px;border-radius:8px;
                                font-size:11px;color:#555;background:#0a0a0a;
                                border:1px solid #1a1a1a;line-height:1.5;"
                         id="statutInfo">
                        <i class="fa-solid fa-circle-info"
                           style="color:#444;margin-right:5px;"></i>
                        Un plat <strong style="color:#22c55e;">Activé</strong>
                        apparaîtra dans le formulaire de prise de commande.
                    </div>
                </div>
            </div>

            {{-- ── Boutons ── --}}
            <div style="display:flex;flex-direction:column;gap:8px;">
                <button type="submit"
                        id="btnSubmit"
                        class="btn btn-primary"
                        style="justify-content:center;">
                    <i class="fa-solid fa-floppy-disk" id="btnIcon"></i>
                    <span id="btnText">Enregistrer le plat</span>
                </button>
                <a href="{{ route('admin.menus.index') }}"
                   class="btn btn-ghost"
                   style="justify-content:center;">
                    <i class="fa-solid fa-arrow-left"></i>
                    Retour aux plats
                </a>
            </div>

            {{-- ── Conseils ── --}}
            <div style="background:#0a0a0a;border:1px solid #1a1a1a;
                        border-radius:10px;padding:14px;font-size:11px;
                        color:#444;line-height:1.7;">
                <div style="font-weight:600;color:#555;margin-bottom:6px;
                            display:flex;align-items:center;gap:5px;">
                    <i class="fa-solid fa-lightbulb" style="color:#eab308;"></i>
                    Conseils
                </div>
                <ul style="padding-left:14px;margin:0;">
                    <li>Choisissez un nom <strong style="color:#666;">précis</strong>
                        (ex: "Poulet braisé épicé" plutôt que "Poulet")</li>
                    <li>Ajoutez une <strong style="color:#666;">belle photo</strong>
                        pour stimuler les commandes</li>
                    <li>La description peut inclure
                        <strong style="color:#666;">les ingrédients ou allergènes</strong></li>
                    <li>Prix arrondi au <strong style="color:#666;">multiple de 50 FCFA</strong>
                        conseillé</li>
                </ul>
            </div>

        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
// ════════════════════════════════════════════════════════════
// INITIALISATION
// ════════════════════════════════════════════════════════════

document.addEventListener('DOMContentLoaded', () => {
    // Initialiser les compteurs avec les valeurs old()
    compterChars('intitule',    'cnt_intitule',    128);
    compterChars('description', 'cnt_description', 1000);

    // Initialiser l'aperçu prix
    majApercu();

    // Statut depuis old()
    const statut = document.getElementById('inputStatut').value;
    if (statut === 'Désactivé') appliquerStatut('Désactivé');
});

// ════════════════════════════════════════════════════════════
// COMPTEUR DE CARACTÈRES
// ════════════════════════════════════════════════════════════

function compterChars(inputId, counterId, max) {
    const input   = document.getElementById(inputId);
    const counter = document.getElementById(counterId);
    if (!input || !counter) return;

    const len = input.value.length;
    counter.textContent = `${len} / ${max}`;
    counter.className   = 'char-count';

    if (len >= max * 0.9)      counter.classList.add('danger');
    else if (len >= max * 0.7) counter.classList.add('warn');
}

// ════════════════════════════════════════════════════════════
// VALIDATIONS EN TEMPS RÉEL
// ════════════════════════════════════════════════════════════

function validerCategorie() {
    const sel = document.getElementById('idcategorie');
    const err = document.getElementById('err_cat');
    const txt = document.getElementById('err_cat_txt');

    sel.classList.remove('is-error','is-valid');

    if (!sel.value) {
        sel.classList.add('is-error');
        txt.textContent = 'Veuillez sélectionner une catégorie.';
        err.classList.add('show');
        return false;
    }

    sel.classList.add('is-valid');
    err.classList.remove('show');
    return true;
}

function validerIntitule() {
    const input = document.getElementById('intitule');
    const err   = document.getElementById('err_intitule');
    const txt   = document.getElementById('err_intitule_txt');
    const val   = input.value.trim();

    input.classList.remove('is-error','is-valid');

    if (!val) {
        input.classList.add('is-error');
        txt.textContent = 'Le nom du plat est obligatoire.';
        err.classList.add('show');
        return false;
    }

    if (val.length < 2) {
        input.classList.add('is-error');
        txt.textContent = 'Minimum 2 caractères.';
        err.classList.add('show');
        return false;
    }

    input.classList.add('is-valid');
    err.classList.remove('show');
    return true;
}

function validerPrix() {
    const input = document.getElementById('pu');
    const err   = document.getElementById('err_pu');
    const txt   = document.getElementById('err_pu_txt');
    const val   = parseFloat(input.value);

    input.classList.remove('is-error','is-valid');

    if (!input.value || isNaN(val)) {
        input.classList.add('is-error');
        txt.textContent = 'Le prix unitaire est obligatoire.';
        err.classList.add('show');
        return false;
    }

    if (val < 1) {
        input.classList.add('is-error');
        txt.textContent = 'Le prix doit être supérieur à 0.';
        err.classList.add('show');
        return false;
    }

    if (val > 9999999) {
        input.classList.add('is-error');
        txt.textContent = 'Le prix semble trop élevé.';
        err.classList.add('show');
        return false;
    }

    input.classList.add('is-valid');
    err.classList.remove('show');
    return true;
}

// ════════════════════════════════════════════════════════════
// APERÇU DU PRIX FORMATÉ
// ════════════════════════════════════════════════════════════

function majApercu() {
    const val      = parseFloat(document.getElementById('pu').value);
    const preview  = document.getElementById('prixPreview');
    const formate  = document.getElementById('prixFormate');

    if (!isNaN(val) && val > 0) {
        formate.textContent   = new Intl.NumberFormat('fr-FR').format(val);
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
}

// ════════════════════════════════════════════════════════════
// TOGGLE STATUT
// ════════════════════════════════════════════════════════════

function basculerStatut() {
    const actuel  = document.getElementById('inputStatut').value;
    const nouveau = actuel === 'Activé' ? 'Désactivé' : 'Activé';
    appliquerStatut(nouveau);
}

function appliquerStatut(statut) {
    const toggle = document.getElementById('toggleSwitch');
    const label  = document.getElementById('statutLabel');
    const desc   = document.getElementById('statutDesc');
    const info   = document.getElementById('statutInfo');
    const input  = document.getElementById('inputStatut');

    input.value = statut;

    if (statut === 'Activé') {
        toggle.className  = 'toggle-switch on';
        label.textContent = 'Activé';
        label.style.color = '#22c55e';
        desc.textContent  = 'Visible dans les commandes';
        info.innerHTML    = `<i class="fa-solid fa-circle-info"
                                style="color:#444;margin-right:5px;"></i>
            Un plat <strong style="color:#22c55e;">Activé</strong>
            apparaîtra dans le formulaire de prise de commande.`;
    } else {
        toggle.className  = 'toggle-switch off';
        label.textContent = 'Désactivé';
        label.style.color = '#f87171';
        desc.textContent  = 'Masqué dans les commandes';
        info.innerHTML    = `<i class="fa-solid fa-circle-info"
                                style="color:#444;margin-right:5px;"></i>
            Un plat <strong style="color:#f87171;">Désactivé</strong>
            ne sera pas proposé lors de la prise de commande.`;
    }
}

// ════════════════════════════════════════════════════════════
// GESTION PHOTO
// ════════════════════════════════════════════════════════════

function photoChanged(input) {
    const file = input.files[0];
    if (file) traiterPhoto(file);
}

function traiterPhoto(file) {
    // Validation type
    const types = ['image/jpeg','image/png','image/jpg','image/webp'];
    if (!types.includes(file.type)) {
        Swal.fire({
            toast: true, position: 'bottom-end',
            icon: 'error',
            title: 'Format non accepté (JPG, PNG, WEBP uniquement).',
            timer: 3000, showConfirmButton: false,
            background: '#141414', color: '#e5e5e5',
        });
        return;
    }

    // Validation taille (max 2 Mo)
    if (file.size > 2 * 1024 * 1024) {
        Swal.fire({
            toast: true, position: 'bottom-end',
            icon: 'warning',
            title: 'La photo ne doit pas dépasser 2 Mo.',
            timer: 3000, showConfirmButton: false,
            background: '#141414', color: '#e5e5e5',
        });
        document.getElementById('photo').value = '';
        return;
    }

    // Lire et afficher l'aperçu
    const reader = new FileReader();
    reader.onload = e => {
        const img  = document.getElementById('photoPreview');
        const phld = document.getElementById('photoPlaceholder');
        const info = document.getElementById('photoInfo');
        const nom  = document.getElementById('photoNom');

        img.src            = e.target.result;
        img.style.display  = 'block';
        phld.style.display = 'none';
        nom.textContent    = file.name;
        info.style.display = 'flex';
    };
    reader.readAsDataURL(file);
}

function supprimerPhoto() {
    document.getElementById('photo').value          = '';
    document.getElementById('photoPreview').src     = '';
    document.getElementById('photoPreview').style.display  = 'none';
    document.getElementById('photoPlaceholder').style.display = 'block';
    document.getElementById('photoInfo').style.display     = 'none';
}

// Drag & Drop
function photoDragOver(e) {
    e.preventDefault();
    document.getElementById('photoZone').classList.add('dragover');
}

function photoDragLeave(e) {
    document.getElementById('photoZone').classList.remove('dragover');
}

function photoDrop(e) {
    e.preventDefault();
    document.getElementById('photoZone').classList.remove('dragover');
    const files = e.dataTransfer.files;
    if (!files.length) return;

    // Injecter le fichier dans l'input
    const input = document.getElementById('photo');
    const dt    = new DataTransfer();
    dt.items.add(files[0]);
    input.files = dt.files;

    traiterPhoto(files[0]);
}

// ════════════════════════════════════════════════════════════
// SOUMISSION FORMULAIRE
// ════════════════════════════════════════════════════════════

document.getElementById('menuForm').addEventListener('submit', function(e) {

    // Valider tous les champs requis
    const okCat   = validerCategorie();
    const okNom   = validerIntitule();
    const okPrix  = validerPrix();

    if (!okCat || !okNom || !okPrix) {
        e.preventDefault();

        // Scroller vers la première erreur
        const firstErr = this.querySelector('.is-error');
        if (firstErr) {
            firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstErr.focus();
        }
        return;
    }

    // Loader sur le bouton
    const btn  = document.getElementById('btnSubmit');
    const icon = document.getElementById('btnIcon');
    const txt  = document.getElementById('btnText');

    btn.disabled    = true;
    icon.className  = 'fa-solid fa-spinner fa-spin';
    txt.textContent = 'Enregistrement...';
});
</script>
@endpush