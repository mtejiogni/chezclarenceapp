@extends('layouts.app')

@section('title', isset($commande) ? 'Modifier la commande ' . $commande->reference : 'Nouvelle commande')
@section('page-title', isset($commande) ? 'Modifier ' . $commande->reference : 'Nouvelle commande')

@push('styles')
<style>
    :root {
        --cc-orange:  #ea580c;
        --cc-orange2: #f97316;
        --cc-dark2:   #0d0d0d;
        --cc-dark3:   #141414;
        --cc-border:  #1f1f1f;
    }

    /* ── Étapes wizard ── */
    .step-bar {
        display: flex;
        align-items: center;
        gap: 0;
        margin-bottom: 28px;
    }

    .step {
        display: flex;
        align-items: center;
        gap: 8px;
        flex: 1;
        position: relative;
    }

    .step:not(:last-child)::after {
        content: '';
        position: absolute;
        left: 44px;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
        height: 1px;
        background: #1f1f1f;
        transition: background .3s;
    }

    .step.done:not(:last-child)::after { background: var(--cc-orange); }

    .step-num {
        width: 32px; height: 32px;
        border-radius: 50%; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; font-weight: 700;
        border: 2px solid #1f1f1f;
        background: #141414; color: #444;
        transition: all .3s; z-index: 1;
    }

    .step.active .step-num {
        border-color: var(--cc-orange);
        background: rgba(234,88,12,.15);
        color: var(--cc-orange2);
    }

    .step.done .step-num {
        border-color: #22c55e;
        background: rgba(34,197,94,.15);
        color: #22c55e;
    }

    .step-label {
        font-size: 11px; font-weight: 500;
        color: #333; white-space: nowrap;
        transition: color .3s;
    }

    .step.active .step-label { color: #e5e5e5; }
    .step.done .step-label   { color: #555; }

    /* ── Panneaux étapes ── */
    .step-panel { display: none; }
    .step-panel.active {
        display: block;
        animation: fadeUp .25s ease;
    }

    @keyframes fadeUp {
        from { opacity:0; transform:translateY(8px); }
        to   { opacity:1; transform:translateY(0); }
    }

    /* ── Grille tables ── */
    .table-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
        gap: 10px;
    }

    .table-btn {
        aspect-ratio: 1;
        border-radius: 13px;
        border: 1.5px solid #1f1f1f;
        background: #141414;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        gap: 4px; cursor: pointer;
        transition: all .2s; min-height: 80px;
        padding: 8px;
    }

    .table-btn:hover:not(.occupee) {
        border-color: var(--cc-orange);
        background: rgba(234,88,12,.06);
    }

    .table-btn.occupee {
        border-color: #ef4444;
        background: rgba(239,68,68,.05);
        cursor: not-allowed; opacity: .6;
    }

    .table-btn.selected {
        border-color: var(--cc-orange);
        background: rgba(234,88,12,.12);
        box-shadow: 0 0 0 3px rgba(234,88,12,.2);
    }

    .table-dot {
        width: 7px; height: 7px; border-radius: 50%;
        position: absolute; top: 6px; right: 6px;
    }

    /* ── Grille menus ── */
    .menu-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 10px;
    }

    .menu-card {
        background: #141414;
        border: 1.5px solid #1f1f1f;
        border-radius: 12px;
        overflow: hidden;
        cursor: pointer;
        transition: all .2s;
        position: relative;
    }

    .menu-card:hover {
        border-color: var(--cc-orange);
        transform: translateY(-2px);
    }

    .menu-card.in-cart {
        border-color: var(--cc-orange);
        background: rgba(234,88,12,.05);
    }

    .menu-card-img {
        height: 90px;
        background: #1a1a1a;
        overflow: hidden;
    }

    .menu-card-img img {
        width: 100%; height: 100%;
        object-fit: cover;
        transition: transform .3s;
    }

    .menu-card:hover .menu-card-img img { transform: scale(1.06); }

    .menu-card-body {
        padding: 8px 10px;
    }

    .qty-badge {
        position: absolute; top: 6px; right: 6px;
        width: 22px; height: 22px; border-radius: 50%;
        background: var(--cc-orange); color: #fff;
        font-size: 11px; font-weight: 700;
        display: flex; align-items: center; justify-content: center;
    }

    /* ── Filtre catégories ── */
    .cat-tab {
        padding: 6px 14px; border-radius: 20px;
        font-size: 11px; font-weight: 500;
        cursor: pointer; transition: all .18s;
        border: 1px solid #1f1f1f;
        background: #141414; color: #555;
        white-space: nowrap; flex-shrink: 0;
    }
    .cat-tab.active,
    .cat-tab:hover {
        background: var(--cc-orange);
        color: #fff; border-color: var(--cc-orange);
    }

    /* ── Panier ── */
    .cart-item {
        display: flex; align-items: center; gap: 10px;
        padding: 10px 12px;
        background: #0d0d0d; border: 1px solid #1a1a1a;
        border-radius: 10px;
        transition: border-color .18s;
    }

    .cart-item:hover { border-color: #252525; }

    .qty-ctrl {
        display: flex; align-items: center; gap: 6px;
    }

    .qty-btn {
        width: 26px; height: 26px; border-radius: 6px;
        border: 1px solid #252525; background: #141414;
        color: #e5e5e5; font-size: 13px; font-weight: 700;
        cursor: pointer; transition: all .18s;
        display: flex; align-items: center; justify-content: center;
    }

    .qty-btn:hover { background: var(--cc-orange); border-color: var(--cc-orange); color: #fff; }

    /* ── Champs formulaire ── */
    .field-group { margin-bottom: 14px; }

    .field-label {
        display: block; font-size: 11px; font-weight: 600;
        color: #555; letter-spacing: .5px; text-transform: uppercase;
        margin-bottom: 6px;
    }

    .field-input {
        width: 100%; background: #0d0d0d;
        border: 1px solid #1f1f1f; border-radius: 10px;
        padding: 10px 14px; color: #e5e5e5; font-size: 13px;
        outline: none; transition: border-color .18s;
        font-family: inherit;
    }

    .field-input::placeholder { color: #2a2a2a; }
    .field-input:focus { border-color: var(--cc-orange); }
    .field-input.error { border-color: #ef4444; }

    .field-error {
        font-size: 11px; color: #f87171;
        margin-top: 4px; display: flex; align-items: center; gap: 4px;
    }

    /* ── Boutons principaux ── */
    .btn {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 10px 20px; border-radius: 10px;
        font-size: 13px; font-weight: 600; cursor: pointer;
        transition: all .18s; border: none; font-family: inherit;
        text-decoration: none;
    }

    .btn-primary { background: var(--cc-orange); color: #fff; }
    .btn-primary:hover { background: #c2410c; }
    .btn-primary:disabled { opacity: .5; cursor: not-allowed; }

    .btn-ghost {
        background: #141414; border: 1px solid #1f1f1f; color: #555;
    }
    .btn-ghost:hover { color: #ccc; border-color: #333; }

    .btn-danger {
        background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.2); color: #f87171;
    }
    .btn-danger:hover { background: #ef4444; color: #fff; }

    /* ── Résumé commande ── */
    .summary-card {
        background: #141414; border: 1px solid #1f1f1f;
        border-radius: 13px; padding: 1.25rem;
        position: sticky; top: 80px;
    }

    /* ── Search menus ── */
    .menu-search {
        background: #0d0d0d; border: 1px solid #1f1f1f;
        border-radius: 10px; padding: 9px 14px 9px 38px;
        color: #e5e5e5; font-size: 13px; outline: none;
        width: 100%; transition: border-color .18s;
        font-family: inherit;
    }
    .menu-search::placeholder { color: #333; }
    .menu-search:focus { border-color: var(--cc-orange); }
</style>
@endpush















@section('content')

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- FORMULAIRE PRINCIPAL                                      --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<form method="POST"
      action="{{ isset($commande) ? route('commandes.update', $commande->idcommande) : route('commandes.store') }}"
      id="commandeForm"
      x-data="commandeApp()"
      x-init="init()">
    @csrf
    @if(isset($commande)) @method('PUT') @endif

    {{-- Champs cachés --}}
    <input type="hidden" name="panier"       id="input-panier"    :value="JSON.stringify(panier)">
    <input type="hidden" name="montant"      id="input-montant"   :value="total">
    <input type="hidden" name="typecommande" id="input-type"      :value="type">
    <input type="hidden" name="idtable"      id="input-table"     :value="tableId">

    {{-- ══ BARRE D'ÉTAPES ══ --}}
    <div class="step-bar">
        <div class="step" :class="{ active: etape >= 1, done: etape > 1 }">
            <div class="step-num">
                <span x-show="etape <= 1">1</span>
                <i class="fa-solid fa-check" x-show="etape > 1" style="font-size:11px;"></i>
            </div>
            <span class="step-label">Type</span>
        </div>
        <div class="step" :class="{ active: etape >= 2, done: etape > 2 }">
            <div class="step-num">
                <span x-show="etape <= 2">2</span>
                <i class="fa-solid fa-check" x-show="etape > 2" style="font-size:11px;"></i>
            </div>
            <span class="step-label" x-text="type === 'Livraison' ? 'Client' : 'Table'"></span>
        </div>
        <div class="step" :class="{ active: etape >= 3, done: etape > 3 }">
            <div class="step-num">
                <span x-show="etape <= 3">3</span>
                <i class="fa-solid fa-check" x-show="etape > 3" style="font-size:11px;"></i>
            </div>
            <span class="step-label">Articles</span>
        </div>
        <div class="step" :class="{ active: etape >= 4 }">
            <div class="step-num">4</div>
            <span class="step-label">Validation</span>
        </div>
    </div>

    {{-- Erreurs serveur --}}
    @if($errors->any())
    <div style="margin-bottom:16px;padding:12px 16px;border-radius:10px;font-size:12px;
                background:rgba(239,68,68,.07);border:1px solid rgba(239,68,68,.2);color:#f87171;"
         class="animate__animated animate__fadeInDown">
        <div style="font-weight:600;margin-bottom:6px;">
            <i class="fa-solid fa-circle-exclamation" style="margin-right:6px;"></i>
            Veuillez corriger les erreurs suivantes :
        </div>
        <ul style="padding-left:16px;">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- ════════════════════════════════════════════════════ --}}
    {{-- ÉTAPE 1 : TYPE DE COMMANDE                         --}}
    {{-- ════════════════════════════════════════════════════ --}}
    <div class="step-panel" :class="{ active: etape === 1 }">

        <div style="max-width:500px;margin:0 auto;">
            <h3 style="font-size:15px;font-weight:600;color:#e5e5e5;margin-bottom:6px;text-align:center;">
                Quel type de commande ?
            </h3>
            <p style="font-size:12px;color:#444;text-align:center;margin-bottom:24px;">
                Sélectionnez le mode de service
            </p>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">

                {{-- Standard --}}
                <button type="button"
                        @click="choisirType('Standard')"
                        :class="type === 'Standard' ? 'selected' : ''"
                        class="table-btn"
                        style="height:160px;border-radius:16px;padding:24px;">
                    <div style="width:56px;height:56px;border-radius:16px;
                                background:rgba(96,165,250,.12);border:1px solid rgba(96,165,250,.2);
                                display:flex;align-items:center;justify-content:center;margin-bottom:10px;">
                        <i class="fa-solid fa-chair" style="font-size:22px;color:#60a5fa;"></i>
                    </div>
                    <div style="font-size:14px;font-weight:700;color:#e5e5e5;">En salle</div>
                    <div style="font-size:11px;color:#444;margin-top:3px;">Commande sur table</div>
                </button>

                {{-- Livraison --}}
                <button type="button"
                        @click="choisirType('Livraison')"
                        :class="type === 'Livraison' ? 'selected' : ''"
                        class="table-btn"
                        style="height:160px;border-radius:16px;padding:24px;">
                    <div style="width:56px;height:56px;border-radius:16px;
                                background:rgba(234,88,12,.12);border:1px solid rgba(234,88,12,.2);
                                display:flex;align-items:center;justify-content:center;margin-bottom:10px;">
                        <i class="fa-solid fa-motorcycle" style="font-size:22px;color:#f97316;"></i>
                    </div>
                    <div style="font-size:14px;font-weight:700;color:#e5e5e5;">Livraison</div>
                    <div style="font-size:11px;color:#444;margin-top:3px;">Commande à domicile</div>
                </button>

            </div>

            {{-- Erreur si pas de type --}}
            <p x-show="erreurs.type" x-text="erreurs.type"
               style="font-size:11px;color:#f87171;text-align:center;margin-top:12px;">
            </p>
        </div>

        <div style="display:flex;justify-content:flex-end;margin-top:24px;">
            <button type="button" @click="etapeSuivante()"
                    class="btn btn-primary">
                Continuer <i class="fa-solid fa-arrow-right"></i>
            </button>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════ --}}
    {{-- ÉTAPE 2A : SÉLECTION DE TABLE (Standard)           --}}
    {{-- ════════════════════════════════════════════════════ --}}
    <div class="step-panel" :class="{ active: etape === 2 && type === 'Standard' }">

        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:8px;">
            <div>
                <h3 style="font-size:15px;font-weight:600;color:#e5e5e5;margin:0;">
                    Choisir une table
                </h3>
                <p style="font-size:12px;color:#444;margin:3px 0 0;">
                    Cliquez sur une table disponible
                </p>
            </div>
            <div style="display:flex;gap:10px;font-size:11px;color:#444;align-items:center;">
                <span style="display:flex;align-items:center;gap:5px;">
                    <span style="width:10px;height:10px;border-radius:3px;
                                 border:1px solid #22c55e;background:rgba(34,197,94,.1);"></span>
                    Libre ({{ $tables->where('occupee', false)->count() }})
                </span>
                <span style="display:flex;align-items:center;gap:5px;">
                    <span style="width:10px;height:10px;border-radius:3px;
                                 border:1px solid #ef4444;background:rgba(239,68,68,.1);"></span>
                    Occupée ({{ $tables->where('occupee', true)->count() }})
                </span>
            </div>
        </div>

        <div class="table-grid">
            @foreach($tables as $table)
            <button type="button"
                    @click="{{ !$table->occupee ? "choisirTable({$table->idtable}, '{$table->intitule}')" : '' }}"
                    :class="{
                        'selected': tableId === {{ $table->idtable }},
                        'occupee':  {{ $table->occupee ? 'true' : 'false' }}
                    }"
                    class="table-btn"
                    {{ $table->occupee && !isset($commande) ? 'disabled' : '' }}
                    style="position:relative;">

                {{-- Indicateur statut --}}
                <span class="table-dot"
                      style="background:{{ $table->occupee ? '#ef4444' : '#22c55e' }};
                             box-shadow:0 0 6px {{ $table->occupee ? 'rgba(239,68,68,.4)' : 'rgba(34,197,94,.4)' }};">
                </span>

                <i class="fa-solid fa-chair"
                   style="font-size:22px;color:{{ $table->occupee ? '#ef4444' : '#22c55e' }};"></i>

                <span style="font-size:11px;font-weight:600;
                             color:{{ $table->occupee ? '#555' : '#e5e5e5' }};">
                    {{ $table->intitule }}
                </span>

                @if($table->occupee)
                <span style="font-size:9px;color:#ef4444;">Occupée</span>
                @elseif($table->description)
                <span style="font-size:9px;color:#333;">{{ Str::limit($table->description, 15) }}</span>
                @else
                <span style="font-size:9px;color:#22c55e;">Libre</span>
                @endif
            </button>
            @endforeach
        </div>

        <p x-show="erreurs.table" x-text="erreurs.table"
           style="font-size:11px;color:#f87171;margin-top:10px;"></p>

        <div style="display:flex;justify-content:space-between;margin-top:24px;">
            <button type="button" @click="etapePrecedente()" class="btn btn-ghost">
                <i class="fa-solid fa-arrow-left"></i> Retour
            </button>
            <button type="button" @click="etapeSuivante()" class="btn btn-primary">
                Continuer <i class="fa-solid fa-arrow-right"></i>
            </button>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════ --}}
    {{-- ÉTAPE 2B : INFOS CLIENT (Livraison)                --}}
    {{-- ════════════════════════════════════════════════════ --}}
    <div class="step-panel" :class="{ active: etape === 2 && type === 'Livraison' }">

        <h3 style="font-size:15px;font-weight:600;color:#e5e5e5;margin-bottom:20px;">
            Informations du client
        </h3>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">
            <div class="field-group">
                <label class="field-label">Nom du client *</label>
                <input type="text" x-model="nomClient"
                       @input="valider('nomClient')"
                       :class="erreurs.nomClient ? 'error' : ''"
                       class="field-input" name="nom_client"
                       placeholder="Ex: Jean Mbarga">
                <p x-show="erreurs.nomClient" x-text="erreurs.nomClient" class="field-error">
                    <i class="fa-solid fa-circle-xmark"></i>
                </p>
            </div>
            <div class="field-group">
                <label class="field-label">Téléphone *</label>
                <div style="position:relative;">
                    <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);
                                 font-size:12px;color:#444;">+237</span>
                    <input type="tel" x-model="telClient"
                           @input="valider('telClient')"
                           :class="erreurs.telClient ? 'error' : ''"
                           class="field-input" name="tel_client"
                           placeholder="6XX XXX XXX"
                           style="padding-left:52px;">
                </div>
                <p x-show="erreurs.telClient" x-text="erreurs.telClient" class="field-error">
                    <i class="fa-solid fa-circle-xmark"></i>
                </p>
            </div>
        </div>

        <div class="field-group">
            <label class="field-label">Adresse de livraison *</label>
            <input type="text" x-model="adresse"
                   @input="valider('adresse')"
                   :class="erreurs.adresse ? 'error' : ''"
                   class="field-input" name="adresse"
                   placeholder="Ex: Akwa, Rue de la Joie, Immeuble ABC, 3ème étage">
            <p x-show="erreurs.adresse" x-text="erreurs.adresse" class="field-error">
                <i class="fa-solid fa-circle-xmark"></i>
            </p>
        </div>

        <div class="field-group">
            <label class="field-label">
                Consignes spéciales
                <span style="font-weight:400;color:#333;">(optionnel)</span>
            </label>
            <textarea x-model="consignes" name="consignes"
                      class="field-input" rows="2"
                      style="resize:none;"
                      placeholder="Ex: Sonner au portail, pas de piment..."></textarea>
        </div>

        <div class="field-group">
            <label class="field-label">Mode de paiement</label>
            <select x-model="modePaiement" name="mode_paiement" class="field-input">
                <option value="Espèces">Espèces</option>
                <option value="Mobile Money">Mobile Money</option>
                <option value="Orange Money">Orange Money</option>
                <option value="MTN MoMo">MTN MoMo</option>
            </select>
        </div>

        <div style="display:flex;justify-content:space-between;margin-top:24px;">
            <button type="button" @click="etapePrecedente()" class="btn btn-ghost">
                <i class="fa-solid fa-arrow-left"></i> Retour
            </button>
            <button type="button" @click="etapeSuivante()" class="btn btn-primary">
                Continuer <i class="fa-solid fa-arrow-right"></i>
            </button>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════ --}}
    {{-- ÉTAPE 3 : SÉLECTION DES ARTICLES                   --}}
    {{-- ════════════════════════════════════════════════════ --}}
    <div class="step-panel" :class="{ active: etape === 3 }">

        <div style="display:grid;grid-template-columns:1fr 300px;gap:16px;align-items:start;">

            {{-- Colonne gauche : catalogue ── --}}
            <div>

                {{-- Recherche + catégories --}}
                <div style="margin-bottom:14px;">
                    <div style="position:relative;margin-bottom:10px;">
                        <i class="fa-solid fa-magnifying-glass"
                           style="position:absolute;left:12px;top:50%;transform:translateY(-50%);
                                  color:#333;font-size:13px;pointer-events:none;"></i>
                        <input type="text" x-model="recherche"
                               class="menu-search"
                               placeholder="Rechercher un plat...">
                    </div>
                    <div style="display:flex;gap:6px;overflow-x:auto;padding-bottom:4px;">
                        <button type="button"
                                @click="catActive = null"
                                :class="!catActive ? 'active' : ''"
                                class="cat-tab">
                            Tout
                        </button>
                        @foreach($categories as $cat)
                        <button type="button"
                                @click="catActive = {{ $cat->idcategorie }}"
                                :class="catActive === {{ $cat->idcategorie }} ? 'active' : ''"
                                class="cat-tab">
                            {{ $cat->intitule }}
                            <span style="font-size:9px;opacity:.7;">({{ $cat->menus->count() }})</span>
                        </button>
                        @endforeach
                    </div>
                </div>

                {{-- Grille des plats --}}
                <div class="menu-grid" id="menu-grid">
                    @foreach($menus as $menu)
                    <div class="menu-card"
                         x-show="filtrerMenu({{ $menu->idcategorie }}, '{{ addslashes($menu->intitule) }}')"
                         @click="ajouterAuPanier({{ $menu->idmenu }}, '{{ addslashes($menu->intitule) }}', {{ $menu->pu }})"
                         :class="qteEnPanier({{ $menu->idmenu }}) > 0 ? 'in-cart' : ''">

                        {{-- Badge quantité --}}
                        <div x-show="qteEnPanier({{ $menu->idmenu }}) > 0" class="qty-badge">
                            <span x-text="qteEnPanier({{ $menu->idmenu }})"></span>
                        </div>

                        {{-- Photo --}}
                        <div class="menu-card-img">
                            @if($menu->photo)
                            <img src="{{ asset('storage/' . $menu->photo) }}"
                                 alt="{{ $menu->intitule }}"
                                 loading="lazy">
                            @else
                            <div style="width:100%;height:100%;display:flex;align-items:center;
                                        justify-content:center;background:#1a1a1a;">
                                <i class="fa-solid fa-utensils" style="color:#252525;font-size:24px;"></i>
                            </div>
                            @endif
                        </div>

                        {{-- Infos --}}
                        <div class="menu-card-body">
                            <div style="font-size:12px;font-weight:600;color:#e5e5e5;
                                        white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                {{ $menu->intitule }}
                            </div>
                            <div style="font-size:10px;color:#444;margin-top:1px;">
                                {{ $menu->categorie->intitule ?? '' }}
                            </div>
                            <div style="font-size:13px;font-weight:700;color:#f97316;margin-top:4px;">
                                {{ number_format($menu->pu, 0, ',', ' ') }} FCFA
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Message vide --}}
                <div x-show="menusVisibles === 0"
                     style="text-align:center;padding:40px;color:#333;">
                    <i class="fa-solid fa-magnifying-glass" style="font-size:28px;display:block;margin-bottom:8px;"></i>
                    <p style="font-size:13px;">Aucun plat trouvé</p>
                </div>

                <p x-show="erreurs.panier" x-text="erreurs.panier"
                   style="font-size:11px;color:#f87171;margin-top:10px;text-align:center;"></p>
            </div>

            {{-- Colonne droite : panier ── --}}
            <div class="summary-card">

                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
                    <h4 style="font-size:13px;font-weight:700;color:#e5e5e5;margin:0;">
                        <i class="fa-solid fa-cart-shopping" style="color:var(--cc-orange);margin-right:6px;"></i>
                        Panier
                    </h4>
                    <span style="font-size:11px;color:#555;"
                          x-text="panier.length + ' article(s)'"></span>
                </div>

                {{-- Articles du panier --}}
                <div style="display:flex;flex-direction:column;gap:7px;
                            max-height:300px;overflow-y:auto;margin-bottom:14px;">

                    <template x-for="item in panier" :key="item.id">
                        <div class="cart-item">
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:12px;font-weight:600;color:#e5e5e5;
                                            white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"
                                     x-text="item.nom"></div>
                                <div style="font-size:11px;color:#f97316;margin-top:1px;"
                                     x-text="fmt(item.pu * item.qte) + ' FCFA'"></div>
                            </div>
                            <div class="qty-ctrl">
                                <button type="button" @click="diminuer(item.id)" class="qty-btn">−</button>
                                <span style="font-size:13px;font-weight:700;color:#e5e5e5;
                                             min-width:20px;text-align:center;"
                                      x-text="item.qte"></span>
                                <button type="button" @click="augmenter(item.id)" class="qty-btn">+</button>
                            </div>
                            <button type="button" @click="supprimer(item.id)"
                                    style="width:24px;height:24px;border-radius:6px;border:1px solid #252525;
                                           background:rgba(239,68,68,.08);color:#f87171;cursor:pointer;
                                           font-size:12px;display:flex;align-items:center;justify-content:center;
                                           transition:all .18s;flex-shrink:0;"
                                    onmouseover="this.style.background='#ef4444';this.style.color='#fff'"
                                    onmouseout="this.style.background='rgba(239,68,68,.08)';this.style.color='#f87171'">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </template>

                    {{-- Panier vide --}}
                    <div x-show="panier.length === 0"
                         style="text-align:center;padding:24px;color:#2a2a2a;">
                        <i class="fa-solid fa-cart-shopping" style="font-size:28px;display:block;margin-bottom:8px;"></i>
                        <p style="font-size:12px;">Cliquez sur un plat pour l'ajouter</p>
                    </div>
                </div>

                {{-- Consignes (mode Standard) --}}
                <div x-show="type === 'Standard'" style="margin-bottom:12px;">
                    <label class="field-label">Consignes / Notes</label>
                    <textarea x-model="consignes" name="consignes"
                              class="field-input" rows="2"
                              style="resize:none;font-size:12px;"
                              placeholder="Sans piment, bien cuit..."></textarea>
                </div>

                {{-- Mode paiement (Standard) --}}
                <div x-show="type === 'Standard'" class="field-group">
                    <label class="field-label">Mode de paiement</label>
                    <select x-model="modePaiement" name="mode_paiement" class="field-input">
                        <option value="Espèces">Espèces</option>
                        <option value="Mobile Money">Mobile Money</option>
                        <option value="Orange Money">Orange Money</option>
                        <option value="MTN MoMo">MTN MoMo</option>
                    </select>
                </div>

                {{-- Séparateur --}}
                <div style="height:1px;background:#1a1a1a;margin:12px 0;"></div>

                {{-- Total --}}
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:13px;font-weight:600;color:#555;">Total</span>
                    <span style="font-size:18px;font-weight:700;color:#fff;"
                          x-text="fmt(total) + ' FCFA'"></span>
                </div>

                {{-- Vider le panier --}}
                <button type="button" x-show="panier.length > 0"
                        @click="viderPanier()"
                        style="width:100%;margin-top:10px;padding:7px;border-radius:8px;
                               border:1px solid rgba(239,68,68,.2);background:rgba(239,68,68,.07);
                               color:#f87171;font-size:11px;font-weight:600;cursor:pointer;
                               transition:all .18s;display:flex;align-items:center;
                               justify-content:center;gap:6px;">
                    <i class="fa-solid fa-trash" style="font-size:11px;"></i>
                    Vider le panier
                </button>

            </div>
        </div>

        <div style="display:flex;justify-content:space-between;margin-top:20px;">
            <button type="button" @click="etapePrecedente()" class="btn btn-ghost">
                <i class="fa-solid fa-arrow-left"></i> Retour
            </button>
            <button type="button" @click="etapeSuivante()" class="btn btn-primary">
                Continuer <i class="fa-solid fa-arrow-right"></i>
            </button>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════ --}}
    {{-- ÉTAPE 4 : RÉCAPITULATIF ET VALIDATION              --}}
    {{-- ════════════════════════════════════════════════════ --}}
    <div class="step-panel" :class="{ active: etape === 4 }">

        <div style="max-width:600px;margin:0 auto;">

            <h3 style="font-size:15px;font-weight:600;color:#e5e5e5;margin-bottom:20px;text-align:center;">
                Récapitulatif de la commande
            </h3>

            {{-- Infos générales --}}
            <div style="background:#141414;border:1px solid #1f1f1f;border-radius:13px;
                        padding:1.25rem;margin-bottom:14px;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">

                    <div>
                        <div style="font-size:10px;color:#444;text-transform:uppercase;
                                    letter-spacing:1px;margin-bottom:3px;">Type</div>
                        <div style="font-size:13px;font-weight:600;color:#e5e5e5;
                                    display:flex;align-items:center;gap:6px;">
                            <i :class="type === 'Livraison' ? 'fa-motorcycle' : 'fa-chair'"
                               class="fa-solid"
                               :style="'color:' + (type === 'Livraison' ? '#f97316' : '#60a5fa')"></i>
                            <span x-text="type"></span>
                        </div>
                    </div>

                    <div x-show="type === 'Standard' && tableNom">
                        <div style="font-size:10px;color:#444;text-transform:uppercase;
                                    letter-spacing:1px;margin-bottom:3px;">Table</div>
                        <div style="font-size:13px;font-weight:600;color:#e5e5e5;" x-text="tableNom"></div>
                    </div>

                    <div x-show="type === 'Livraison' && nomClient">
                        <div style="font-size:10px;color:#444;text-transform:uppercase;
                                    letter-spacing:1px;margin-bottom:3px;">Client</div>
                        <div style="font-size:13px;font-weight:600;color:#e5e5e5;" x-text="nomClient"></div>
                    </div>

                    <div x-show="type === 'Livraison' && telClient">
                        <div style="font-size:10px;color:#444;text-transform:uppercase;
                                    letter-spacing:1px;margin-bottom:3px;">Téléphone</div>
                        <div style="font-size:13px;font-weight:600;color:#e5e5e5;" x-text="'+237 ' + telClient"></div>
                    </div>

                    <div>
                        <div style="font-size:10px;color:#444;text-transform:uppercase;
                                    letter-spacing:1px;margin-bottom:3px;">Paiement</div>
                        <div style="font-size:13px;font-weight:600;color:#e5e5e5;" x-text="modePaiement"></div>
                    </div>

                </div>

                <div x-show="type === 'Livraison' && adresse"
                     style="margin-top:10px;padding:8px 12px;border-radius:8px;
                            background:#0d0d0d;border:1px solid #1a1a1a;
                            font-size:12px;color:#888;">
                    <i class="fa-solid fa-location-dot" style="color:#f97316;margin-right:6px;"></i>
                    <span x-text="adresse"></span>
                </div>

                <div x-show="consignes"
                     style="margin-top:8px;padding:8px 12px;border-radius:8px;
                            background:rgba(234,179,8,.06);border:1px solid rgba(234,179,8,.15);
                            font-size:12px;color:#eab308;">
                    <i class="fa-solid fa-note-sticky" style="margin-right:6px;"></i>
                    <span x-text="consignes"></span>
                </div>
            </div>

            {{-- Articles récapitulatif --}}
            <div style="background:#141414;border:1px solid #1f1f1f;border-radius:13px;
                        padding:1.25rem;margin-bottom:14px;">
                <div style="font-size:12px;font-weight:700;color:#555;
                            text-transform:uppercase;letter-spacing:1px;margin-bottom:12px;">
                    Articles (<span x-text="panier.length"></span>)
                </div>

                <template x-for="item in panier" :key="item.id">
                    <div style="display:flex;justify-content:space-between;align-items:center;
                                padding:8px 0;border-bottom:1px solid #1a1a1a;">
                        <div>
                            <span style="font-size:13px;color:#e5e5e5;" x-text="item.nom"></span>
                            <span style="font-size:11px;color:#444;margin-left:8px;"
                                  x-text="'×' + item.qte"></span>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-size:11px;color:#444;"
                                 x-text="fmt(item.pu) + ' × ' + item.qte"></div>
                            <div style="font-size:13px;font-weight:700;color:#fff;"
                                 x-text="fmt(item.pu * item.qte) + ' FCFA'"></div>
                        </div>
                    </div>
                </template>

                {{-- Total --}}
                <div style="display:flex;justify-content:space-between;align-items:center;
                            margin-top:12px;padding-top:12px;border-top:1px solid #252525;">
                    <span style="font-size:14px;font-weight:700;color:#e5e5e5;">Total à payer</span>
                    <span style="font-size:20px;font-weight:700;color:#f97316;"
                          x-text="fmt(total) + ' FCFA'"></span>
                </div>
            </div>

            {{-- Boutons finaux --}}
            <div style="display:flex;gap:10px;justify-content:space-between;">
                <button type="button" @click="etapePrecedente()" class="btn btn-ghost">
                    <i class="fa-solid fa-arrow-left"></i> Modifier
                </button>
                <button type="button"
                        @click="soumettre()"
                        :disabled="chargement"
                        :class="chargement ? 'opacity-60' : ''"
                        class="btn btn-primary"
                        style="flex:1;">
                    <span x-show="!chargement" style="display:flex;align-items:center;gap:7px;">
                        <i class="fa-solid fa-check"></i>
                        {{ isset($commande) ? 'Enregistrer les modifications' : 'Valider la commande' }}
                    </span>
                    <span x-show="chargement" style="display:flex;align-items:center;gap:7px;">
                        <i class="fa-solid fa-spinner fa-spin"></i>
                        Enregistrement...
                    </span>
                </button>
            </div>

        </div>
    </div>

</form>

@php
    $panierInitial = [];
    if (isset($commande)) {
        foreach ($commande->lignes as $l) {
            $panierInitial[] = [
                'id'  => (int) $l->idmenu,
                'nom' => $l->menu->intitule ?? 'N/A',
                'pu'  => (float) ($l->menu->pu ?? 0),
                'qte' => (int) $l->quantite,
            ];
        }
    }
@endphp

@endsection







@push('scripts')
<script>
// Enregistrement du composant Alpine AVANT Alpine.start()
document.addEventListener('alpine:init', () => {
    Alpine.data('commandeApp', () => ({

        // ── État wizard ──
        etape: 1,

        // ── Type & table ──
        type:     '{{ isset($commande) ? $commande->typecommande : old("typecommande", "") }}',
        tableId:  {{ isset($commande) && $commande->idtable ? $commande->idtable : 'null' }},
        tableNom: '{{ isset($commande) && $commande->table ? $commande->table->intitule : "" }}',

        // ── Client (livraison) ──
        nomClient:   '{{ old("nom_client", isset($commande) && $commande->client ? addslashes($commande->client->prenom . " " . $commande->client->nom) : "") }}',
        telClient:   '{{ old("tel_client", isset($commande) && $commande->client ? $commande->client->telephone : "") }}',
        adresse:     '{{ old("adresse", isset($commande) ? addslashes($commande->adresse ?? "") : "") }}',

        // ── Panier ── (données PHP injectées via variable JS)
        panier: {!! json_encode($panierInitial) !!},

        // ── Options commande ──
        consignes:    '{{ old("consignes", isset($commande) ? addslashes($commande->consignes ?? "") : "") }}',
        modePaiement: '{{ old("mode_paiement", isset($commande) ? $commande->mode_paiement : "Espèces") }}',

        // ── Filtres catalogue ──
        catActive:  null,
        recherche:  '',

        // ── UI ──
        chargement: false,
        erreurs:    {},

        // ── Computed ──
        get total() {
            return this.panier.reduce((s, i) => s + i.pu * i.qte, 0);
        },

        get menusVisibles() {
            return document.querySelectorAll('.menu-card:not([style*="display: none"])').length;
        },

        // ── Init ──
        init() {
            @if(isset($commande))
            this.etape = 4;
            @endif
        },

        // ── Navigation wizard ──
        choisirType(t) {
            this.type     = t;
            this.tableId  = null;
            this.tableNom = '';
            delete this.erreurs.type;
        },

        choisirTable(id, nom) {
            this.tableId  = id;
            this.tableNom = nom;
            delete this.erreurs.table;
        },

        etapeSuivante() {
            if (!this.validerEtape()) return;
            this.etape++;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        etapePrecedente() {
            this.etape--;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        // ── Validation par étape ──
        validerEtape() {
            this.erreurs = {};

            if (this.etape === 1) {
                if (!this.type) {
                    this.erreurs.type = 'Veuillez sélectionner un type de commande.';
                    return false;
                }
            }

            if (this.etape === 2 && this.type === 'Standard') {
                if (!this.tableId) {
                    this.erreurs.table = 'Veuillez sélectionner une table.';
                    return false;
                }
            }

            if (this.etape === 2 && this.type === 'Livraison') {
                if (!this.nomClient.trim()) {
                    this.erreurs.nomClient = 'Le nom du client est obligatoire.';
                    return false;
                }
                if (!this.telClient.trim() || this.telClient.length < 8) {
                    this.erreurs.telClient = 'Numéro de téléphone invalide.';
                    return false;
                }
                if (!this.adresse.trim()) {
                    this.erreurs.adresse = "L'adresse de livraison est obligatoire.";
                    return false;
                }
            }

            if (this.etape === 3) {
                if (this.panier.length === 0) {
                    this.erreurs.panier = 'Le panier est vide. Ajoutez au moins un article.';
                    return false;
                }
            }

            return true;
        },

        // ── Validation temps réel ──
        valider(champ) {
            delete this.erreurs[champ];
            if (champ === 'nomClient' && !this.nomClient.trim()) {
                this.erreurs.nomClient = 'Le nom est obligatoire.';
            }
            if (champ === 'telClient' && this.telClient.length < 8) {
                this.erreurs.telClient = 'Numéro invalide (min. 8 chiffres).';
            }
            if (champ === 'adresse' && !this.adresse.trim()) {
                this.erreurs.adresse = "L'adresse est obligatoire.";
            }
        },

        // ── Gestion du panier ──
        ajouterAuPanier(id, nom, pu) {
            const item = this.panier.find(i => i.id === id);
            if (item) {
                item.qte++;
            } else {
                this.panier.push({ id, nom, pu, qte: 1 });
            }
            delete this.erreurs.panier;
        },

        augmenter(id) {
            const item = this.panier.find(i => i.id === id);
            if (item && item.qte < 99) item.qte++;
        },

        diminuer(id) {
            const item = this.panier.find(i => i.id === id);
            if (!item) return;
            if (item.qte > 1) { item.qte--; }
            else { this.supprimer(id); }
        },

        supprimer(id) {
            this.panier = this.panier.filter(i => i.id !== id);
        },

        viderPanier() {
            Swal.fire({
                title: 'Vider le panier ?',
                text: 'Tous les articles seront retirés.',
                icon: 'warning',
                iconColor: '#ea580c',
                background: '#141414',
                color: '#e5e5e5',
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Oui, vider',
                showCancelButton: true,
                cancelButtonText: 'Annuler',
                cancelButtonColor: '#1f1f1f',
            }).then(r => { if (r.isConfirmed) this.panier = []; });
        },

        qteEnPanier(id) {
            return this.panier.find(i => i.id === id)?.qte ?? 0;
        },

        // ── Filtre catalogue ──
        filtrerMenu(idcat, nom) {
            const matchCat = !this.catActive || this.catActive === idcat;
            const matchNom = !this.recherche
                || nom.toLowerCase().includes(this.recherche.toLowerCase());
            return matchCat && matchNom;
        },

        // ── Formatage ──
        fmt(n) {
            return new Intl.NumberFormat('fr-FR').format(Math.round(n ?? 0));
        },

        // ── Soumission ──
        soumettre() {
            if (!this.validerEtape()) return;

            Swal.fire({
                title: '{{ isset($commande) ? "Enregistrer les modifications ?" : "Valider la commande ?" }}',
                html: `
                    <div style="color:#666;font-size:13px;margin-bottom:8px;">
                        ${this.panier.length} article(s) —
                        <strong style="color:#f97316;">${this.fmt(this.total)} FCFA</strong>
                    </div>
                    <div style="font-size:12px;color:#444;">
                        ${this.type === 'Livraison'
                            ? '🛵 Livraison pour ' + this.nomClient
                            : '🪑 ' + this.tableNom}
                    </div>
                `,
                icon: 'question',
                iconColor: '#ea580c',
                background: '#141414',
                color: '#e5e5e5',
                confirmButtonColor: '#ea580c',
                confirmButtonText: '<i class="fa-solid fa-check" style="margin-right:6px"></i>Confirmer',
                showCancelButton: true,
                cancelButtonText: 'Vérifier encore',
                cancelButtonColor: '#1f1f1f',
            }).then(r => {
                if (!r.isConfirmed) return;
                this.chargement = true;
                document.getElementById('commandeForm').submit();
            });
        },

    }));
});
</script>
@endpush