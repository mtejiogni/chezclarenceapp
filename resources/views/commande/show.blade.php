@extends('layouts.app')

@section('title', 'Commande ' . $commande->reference)
@section('page-title', 'Détail commande')

@push('styles')
<style>
    /* ══════════════════════════════════════════════════════════
       VARIABLES & BASE
    ══════════════════════════════════════════════════════════ */
    :root {
        --cc-orange:  #ea580c;
        --cc-orange2: #f97316;
        --cc-dark2:   #0d0d0d;
        --cc-dark3:   #141414;
        --cc-border:  #1f1f1f;
    }

    /* ── Cartes génériques ── */
    .card {
        background: var(--cc-dark3);
        border: 1px solid var(--cc-border);
        border-radius: 14px;
        padding: 1.25rem;
    }

    /* ── Badges de statut ── */
    .badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .b-attente    { background: rgba(234,179,8,.12);  color: #eab308; }
    .b-prep       { background: rgba(59,130,246,.12);  color: #60a5fa; }
    .b-expediee   { background: rgba(234,88,12,.12);   color: #f97316; }
    .b-servie,
    .b-livree     { background: rgba(34,197,94,.12);   color: #22c55e; }
    .b-annulee    { background: rgba(239,68,68,.12);   color: #f87171; }

    /* ── Timeline historique ── */
    .timeline {
        position: relative;
        padding-left: 28px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 11px;
        top: 0;
        bottom: 0;
        width: 1px;
        background: var(--cc-border);
    }

    .timeline-item {
        position: relative;
        padding-bottom: 20px;
    }

    .timeline-item:last-child {
        padding-bottom: 0;
    }

    .timeline-dot {
        position: absolute;
        left: -22px;
        top: 4px;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        border: 1px solid var(--cc-border);
        background: var(--cc-dark3);
        z-index: 1;
    }

    /* ── Boutons ── */
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 18px;
        border-radius: 10px;
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
        background: rgba(239,68,68,.1);
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

    .btn-sm { padding: 6px 12px; font-size: 11px; border-radius: 8px; }

    /* ── Info cell ── */
    .info-cell {
        background: var(--cc-dark2);
        border: 1px solid #1a1a1a;
        border-radius: 9px;
        padding: 10px 12px;
    }

    .info-cell-label {
        font-size: 10px;
        color: #444;
        text-transform: uppercase;
        letter-spacing: .5px;
        margin-bottom: 3px;
    }

    .info-cell-value {
        font-size: 13px;
        font-weight: 600;
        color: #e5e5e5;
    }

    /* ── Ligne article ── */
    .ligne-article {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid #1a1a1a;
    }

    .ligne-article:last-child {
        border-bottom: none;
    }

    /* ── Champ formulaire inline ── */
    .field-input {
        width: 100%;
        background: var(--cc-dark2);
        border: 1px solid var(--cc-border);
        border-radius: 9px;
        padding: 9px 12px;
        color: #e5e5e5;
        font-size: 13px;
        outline: none;
        transition: border-color .18s;
        font-family: inherit;
    }

    .field-input:focus { border-color: var(--cc-orange); }
    .field-input::placeholder { color: #2a2a2a; }

    /* ── Sélecteur statut ── */
    .statut-select {
        background: var(--cc-dark2);
        border: 1px solid var(--cc-border);
        border-radius: 9px;
        padding: 9px 12px;
        color: #e5e5e5;
        font-size: 13px;
        outline: none;
        cursor: pointer;
        font-family: inherit;
        transition: border-color .18s;
    }

    .statut-select:focus { border-color: var(--cc-orange); }

    /* ── Progression visuelle ── */
    .progress-bar {
        display: flex;
        align-items: center;
        gap: 0;
        margin-bottom: 24px;
        overflow-x: auto;
        padding-bottom: 4px;
    }

    .progress-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        flex: 1;
        min-width: 80px;
        position: relative;
    }

    .progress-step:not(:last-child)::after {
        content: '';
        position: absolute;
        left: 50%;
        right: -50%;
        top: 14px;
        height: 2px;
        background: #1f1f1f;
        z-index: 0;
    }

    .progress-step.done:not(:last-child)::after {
        background: #22c55e;
    }

    .progress-step.active:not(:last-child)::after {
        background: linear-gradient(90deg, #22c55e, #1f1f1f);
    }

    .progress-dot {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        border: 2px solid #1f1f1f;
        background: var(--cc-dark3);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        z-index: 1;
        transition: all .3s;
    }

    .progress-step.done .progress-dot {
        border-color: #22c55e;
        background: rgba(34,197,94,.15);
        color: #22c55e;
    }

    .progress-step.active .progress-dot {
        border-color: var(--cc-orange);
        background: rgba(234,88,12,.15);
        color: var(--cc-orange2);
    }

    .progress-label {
        font-size: 10px;
        color: #333;
        white-space: nowrap;
        text-align: center;
    }

    .progress-step.done .progress-label  { color: #22c55e; }
    .progress-step.active .progress-label { color: var(--cc-orange2); }

    /* ── Note étoiles ── */
    .stars { display: flex; gap: 4px; }
    .star {
        font-size: 20px;
        cursor: pointer;
        color: #2a2a2a;
        transition: color .15s;
    }
    .star.active { color: #eab308; }
    .star:hover  { color: #fbbf24; }
</style>
@endpush

@section('content')

{{-- ══════════════════════════════════════════════════════════
     EN-TÊTE : référence + statut + actions rapides
══════════════════════════════════════════════════════════ --}}
<div style="display:flex;align-items:flex-start;justify-content:space-between;
            flex-wrap:wrap;gap:12px;margin-bottom:20px;">

    {{-- Référence & métadonnées --}}
    <div>
        {{-- Fil d'Ariane --}}
        <div style="font-size:11px;color:#444;margin-bottom:6px;display:flex;align-items:center;gap:6px;">
            <a href="{{ route('commandes.index') }}"
               style="color:#555;text-decoration:none;transition:color .18s;"
               onmouseover="this.style.color='#f97316'"
               onmouseout="this.style.color='#555'">
                <i class="fa-solid fa-receipt" style="margin-right:3px;"></i>Commandes
            </a>
            <i class="fa-solid fa-chevron-right" style="font-size:9px;color:#333;"></i>
            <span style="color:#666;">{{ $commande->reference }}</span>
        </div>

        {{-- Titre --}}
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <h1 style="font-size:20px;font-weight:700;color:#fff;margin:0;">
                {{ $commande->reference }}
            </h1>

            {{-- Badge statut --}}
            @php
                $slugStatut = match($commande->statut_courant) {
                    'En attente'     => 'attente',
                    'En préparation' => 'prep',
                    'Expédiée'       => 'expediee',
                    'Servie'         => 'servie',
                    'Livrée'         => 'livree',
                    'Annulée'        => 'annulee',
                    default          => 'attente'
                };
                $iconeStatut = match($commande->statut_courant) {
                    'En attente'     => 'fa-clock',
                    'En préparation' => 'fa-fire-burner',
                    'Expédiée'       => 'fa-motorcycle',
                    'Servie'         => 'fa-utensils',
                    'Livrée'         => 'fa-circle-check',
                    'Annulée'        => 'fa-circle-xmark',
                    default          => 'fa-circle'
                };

                $typeIcone = match($commande->typecommande) {
                    'Livraison'  => 'fa-motorcycle',
                    'A emporter' => 'fa-bag-shopping',
                    default      => 'fa-chair',
                };
                $typeCouleur = match($commande->typecommande) {
                    'Livraison'  => '#f97316',
                    'A emporter' => '#22c55e',
                    default      => '#60a5fa',
                };
                $typeLabel = $commande->typecommande === 'A emporter' ? 'À emporter' : $commande->typecommande;
            @endphp
            <span class="badge b-{{ $slugStatut }}">
                <i class="fa-solid {{ $iconeStatut }}" style="font-size:11px;"></i>
                {{ $commande->statut_courant }}
            </span>

            {{-- Type de commande --}}
            <span style="font-size:11px;padding:3px 10px;border-radius:6px;
                         background:#1a1a1a;color:#555;">
                <i class="fa-solid {{ $typeIcone }}"
                   style="margin-right:4px;color:{{ $typeCouleur }};"></i>
                {{ $typeLabel }}
            </span>
        </div>

        {{-- Sous-titre : date, heure, serveur --}}
        <div style="display:flex;gap:16px;margin-top:6px;flex-wrap:wrap;">
            <span style="font-size:11px;color:#444;">
                <i class="fa-regular fa-calendar" style="margin-right:4px;"></i>
                {{ $commande->datecommande->format('d/m/Y') }}
            </span>
            <span style="font-size:11px;color:#444;">
                <i class="fa-regular fa-clock" style="margin-right:4px;"></i>
                {{ $commande->heurecommande }}
            </span>
            @if($commande->serveur)
            <span style="font-size:11px;color:#444;">
                <i class="fa-solid fa-user-tie" style="margin-right:4px;"></i>
                Enregistrée par {{ $commande->serveur->prenom }} {{ $commande->serveur->nom }}
            </span>
            @endif
        </div>
    </div>

    {{-- Actions rapides --}}
    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">

        {{-- Modifier (si modifiable) --}}
        @if($commande->estModifiable() && in_array(auth()->user()->role, ['Administrateur','Caissier','Serveur']))
        <a href="{{ route('commandes.edit', $commande->idcommande) }}"
           class="btn btn-ghost btn-sm">
            <i class="fa-solid fa-pen-to-square"></i> Modifier
        </a>
        @endif

        {{-- Reçu PDF (si terminée) --}}
        @if(in_array($commande->statut_courant, ['Servie','Livrée']) && in_array(auth()->user()->role, ['Administrateur','Caissier']))
        <a href="{{ route('caisse.recu', $commande->idcommande) }}"
           target="_blank"
           class="btn btn-ghost btn-sm">
            <i class="fa-solid fa-file-pdf" style="color:#f97316;"></i> Reçu PDF
        </a>
        @endif

        {{-- Prochain statut (bouton principal) --}}
        @if($prochainStatut)
        <button onclick="changerStatut('{{ $prochainStatut }}')"
                class="btn btn-success btn-sm">
            <i class="fa-solid fa-arrow-right"></i>
            {{ $prochainStatut }}
        </button>
        @endif

        {{-- Annuler --}}
        @if($commande->estAnnulable() && in_array(auth()->user()->role, ['Administrateur','Caissier']))
        <button onclick="annulerCommande()"
                class="btn btn-danger btn-sm">
            <i class="fa-solid fa-xmark"></i> Annuler
        </button>
        @endif

        {{-- Retour --}}
        <a href="{{ route('commandes.index') }}" class="btn btn-ghost btn-sm">
            <i class="fa-solid fa-arrow-left"></i> Retour
        </a>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     BARRE DE PROGRESSION DU STATUT
══════════════════════════════════════════════════════════ --}}
@php
    // Définir les étapes selon le type de commande
    // [NOTE] "A emporter" suit le même parcours que "Standard"
    //        (pas d'étape Expédiée/Livrée, le client repart directement)
    $etapesStandard  = ['En attente', 'En préparation', 'Servie'];
    $etapesLivraison = ['En attente', 'En préparation', 'Expédiée', 'Livrée'];
    $etapes = $commande->typecommande === 'Livraison' ? $etapesLivraison : $etapesStandard;

    // Si annulée, afficher quand même la progression
    if ($commande->statut_courant === 'Annulée') {
        $etapes[] = 'Annulée';
    }

    // Trouver l'index du statut courant
    $indexActuel = array_search($commande->statut_courant, $etapes);
@endphp

<div class="card" style="margin-bottom:20px;">
    <div class="progress-bar">
        @foreach($etapes as $index => $etape)
        @php
            $estFait   = $index < $indexActuel || ($indexActuel === false && $etape !== 'Annulée');
            $estActuel = $etape === $commande->statut_courant;
            $icone = match($etape) {
                'En attente'     => 'fa-clock',
                'En préparation' => 'fa-fire-burner',
                'Expédiée'       => 'fa-motorcycle',
                'Livrée'         => 'fa-circle-check',
                'Servie'         => 'fa-utensils',
                'Annulée'        => 'fa-circle-xmark',
                default          => 'fa-circle',
            };
        @endphp
        <div class="progress-step {{ $estFait ? 'done' : ($estActuel ? 'active' : '') }}">
            <div class="progress-dot">
                @if($estFait)
                    <i class="fa-solid fa-check" style="font-size:10px;"></i>
                @else
                    <i class="fa-solid {{ $icone }}" style="font-size:10px;"></i>
                @endif
            </div>
            <span class="progress-label">{{ $etape }}</span>
        </div>
        @endforeach
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     CORPS PRINCIPAL : 2 colonnes
══════════════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:1fr 340px;gap:16px;align-items:start;">

    {{-- ════════════════════════════════════
         COLONNE GAUCHE
    ════════════════════════════════════ --}}
    <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- ── 1. Informations générales ── --}}
        <div class="card">
            <h3 style="font-size:13px;font-weight:700;color:#e5e5e5;margin:0 0 14px;
                        display:flex;align-items:center;gap:7px;">
                <i class="fa-solid fa-circle-info" style="color:var(--cc-orange);"></i>
                Informations générales
            </h3>

            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;">

                {{-- Montant --}}
                <div class="info-cell">
                    <div class="info-cell-label">Montant total</div>
                    <div class="info-cell-value" style="color:#f97316;font-size:18px;">
                        {{ number_format($commande->montant, 0, ',', ' ') }}
                        <span style="font-size:12px;color:#555;">FCFA</span>
                    </div>
                </div>

                {{-- Mode paiement --}}
                <div class="info-cell">
                    <div class="info-cell-label">Mode de paiement</div>
                    <div class="info-cell-value">
                        <i class="fa-solid fa-credit-card" style="color:#444;margin-right:5px;font-size:11px;"></i>
                        {{ $commande->mode_paiement ?? 'Espèces' }}
                    </div>
                </div>

                {{-- Table, À emporter, ou Livraison --}}
                @if($commande->table)
                <div class="info-cell">
                    <div class="info-cell-label">Table</div>
                    <div class="info-cell-value">
                        <i class="fa-solid fa-chair" style="color:#60a5fa;margin-right:5px;font-size:11px;"></i>
                        {{ $commande->table->intitule }}
                    </div>
                </div>
                @elseif($commande->typecommande === 'A emporter')
                <div class="info-cell">
                    <div class="info-cell-label">Type</div>
                    <div class="info-cell-value">
                        <i class="fa-solid fa-bag-shopping" style="color:#22c55e;margin-right:5px;font-size:11px;"></i>
                        À emporter
                    </div>
                </div>
                @else
                <div class="info-cell">
                    <div class="info-cell-label">Type</div>
                    <div class="info-cell-value">
                        <i class="fa-solid fa-motorcycle" style="color:#f97316;margin-right:5px;font-size:11px;"></i>
                        Livraison
                    </div>
                </div>
                @endif
            </div>

            {{-- Adresse de livraison --}}
            @if($commande->adresse)
            <div style="margin-top:12px;padding:10px 14px;border-radius:9px;
                        background:var(--cc-dark2);border:1px solid #1a1a1a;">
                <div style="font-size:10px;color:#444;text-transform:uppercase;
                            letter-spacing:.5px;margin-bottom:4px;">
                    <i class="fa-solid fa-location-dot" style="color:#f97316;margin-right:4px;"></i>
                    Adresse de livraison
                </div>
                <div style="font-size:13px;color:#ccc;">{{ $commande->adresse }}</div>
            </div>
            @endif

            {{-- Consignes --}}
            @if($commande->consignes)
            <div style="margin-top:10px;padding:10px 14px;border-radius:9px;
                        background:rgba(234,179,8,.06);border:1px solid rgba(234,179,8,.15);">
                <div style="font-size:10px;color:#eab308;text-transform:uppercase;
                            letter-spacing:.5px;margin-bottom:4px;">
                    <i class="fa-solid fa-note-sticky" style="margin-right:4px;"></i>
                    Consignes
                </div>
                <div style="font-size:13px;color:#d4a807;">{{ $commande->consignes }}</div>
            </div>
            @endif
        </div>

        {{-- ── 2. Informations client ── --}}
        @if($commande->client)
        <div class="card">
            <h3 style="font-size:13px;font-weight:700;color:#e5e5e5;margin:0 0 14px;
                        display:flex;align-items:center;gap:7px;">
                <i class="fa-solid fa-user" style="color:var(--cc-orange);"></i>
                Client
            </h3>
            <div style="display:flex;align-items:center;gap:14px;">

                {{-- Avatar --}}
                <div style="width:44px;height:44px;border-radius:50%;flex-shrink:0;
                            background:rgba(234,88,12,.15);border:1px solid rgba(234,88,12,.3);
                            display:flex;align-items:center;justify-content:center;
                            font-size:15px;font-weight:700;color:#f97316;">
                    {{ strtoupper(substr($commande->client->prenom ?? 'C', 0, 1)) }}
                </div>

                <div style="flex:1;min-width:0;">
                    <div style="font-size:14px;font-weight:600;color:#e5e5e5;">
                        {{ $commande->client->prenom }} {{ $commande->client->nom }}
                    </div>
                    <div style="display:flex;gap:14px;margin-top:3px;flex-wrap:wrap;">
                        @if($commande->client->telephone)
                        <span style="font-size:11px;color:#555;">
                            <i class="fa-solid fa-phone" style="margin-right:3px;color:#444;"></i>
                            {{ $commande->client->telephone }}
                        </span>
                        @endif
                        @if($commande->client->email)
                        <span style="font-size:11px;color:#555;">
                            <i class="fa-solid fa-envelope" style="margin-right:3px;color:#444;"></i>
                            {{ $commande->client->email }}
                        </span>
                        @endif
                        @if($commande->client->points > 0)
                        <span style="font-size:11px;color:#eab308;">
                            <i class="fa-solid fa-star" style="margin-right:3px;"></i>
                            {{ $commande->client->points }} points fidélité
                        </span>
                        @endif
                    </div>
                </div>

                {{-- Lien vers le profil client --}}
                @if(auth()->user()->role === 'Administrateur')
                <a href="{{ route('admin.utilisateurs.show', $commande->client->iduser) }}"
                   class="btn btn-ghost btn-sm">
                    <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:10px;"></i>
                    Profil
                </a>
                @endif
            </div>
        </div>
        @endif

        {{-- ── 3. Articles commandés ── --}}
        <div class="card">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
                <h3 style="font-size:13px;font-weight:700;color:#e5e5e5;margin:0;
                            display:flex;align-items:center;gap:7px;">
                    <i class="fa-solid fa-list" style="color:var(--cc-orange);"></i>
                    Articles ({{ $commande->lignes->count() }})
                </h3>
                {{-- Ajouter un article si commande modifiable --}}
                @if($commande->estModifiable() && in_array(auth()->user()->role, ['Administrateur','Caissier','Serveur']))
                <a href="{{ route('commandes.edit', $commande->idcommande) }}"
                   class="btn btn-ghost btn-sm">
                    <i class="fa-solid fa-plus"></i> Modifier les articles
                </a>
                @endif
            </div>

            {{-- Liste des articles --}}
            @forelse($commande->lignes as $ligne)
            @php
                $prixUnitaire = $ligne->quantite > 0 ? $ligne->prix / $ligne->quantite : 0;
            @endphp
            <div class="ligne-article">

                {{-- Photo du plat (miniature) --}}
                <div style="width:44px;height:44px;border-radius:9px;flex-shrink:0;
                            overflow:hidden;background:#1a1a1a;">
                    @if($ligne->menu && $ligne->menu->photo)
                    <img src="{{ asset('storage/' . $ligne->menu->photo) }}"
                         alt="{{ $ligne->menu->intitule }}"
                         style="width:100%;height:100%;object-fit:cover;">
                    @else
                    <div style="width:100%;height:100%;display:flex;
                                align-items:center;justify-content:center;">
                        <i class="fa-solid fa-utensils" style="color:#2a2a2a;font-size:14px;"></i>
                    </div>
                    @endif
                </div>

                {{-- Détails du plat --}}
                <div style="flex:1;min-width:0;">
                    <div style="font-size:13px;font-weight:600;color:#e5e5e5;
                                white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        {{ $ligne->menu->intitule ?? 'Plat supprimé' }}
                    </div>
                    <div style="font-size:11px;color:#444;margin-top:1px;">
                        {{ $ligne->menu->categorie->intitule ?? '' }}
                        @if($ligne->remise > 0)
                        · <span style="color:#22c55e;">Remise : -{{ number_format($ligne->remise, 0, ',', ' ') }} FCFA</span>
                        @endif
                    </div>
                </div>

                {{-- Quantité --}}
                <div style="text-align:center;min-width:50px;">
                    <div style="font-size:11px;color:#444;margin-bottom:1px;">Qté</div>
                    <div style="font-size:14px;font-weight:700;color:#e5e5e5;">
                        × {{ $ligne->quantite }}
                    </div>
                </div>

                {{-- Prix unitaire --}}
                <div style="text-align:right;min-width:80px;">
                    <div style="font-size:10px;color:#444;margin-bottom:1px;">P.U.</div>
                    <div style="font-size:11px;color:#666;">
                        {{ number_format($ligne->menu->pu ?? 0, 0, ',', ' ') }} F
                    </div>
                </div>

                {{-- Prix total de la ligne --}}
                <div style="text-align:right;min-width:100px;">
                    <div style="font-size:10px;color:#444;margin-bottom:1px;">Sous-total</div>
                    <div style="font-size:14px;font-weight:700;color:#fff;">
                        {{ number_format($ligne->prix, 0, ',', ' ') }} FCFA
                    </div>
                </div>
            </div>
            @empty
            <div style="text-align:center;padding:28px;color:#2a2a2a;">
                <i class="fa-solid fa-list" style="font-size:24px;display:block;margin-bottom:8px;"></i>
                <p style="font-size:13px;">Aucun article</p>
            </div>
            @endforelse

            {{-- Récapitulatif financier --}}
            <div style="margin-top:14px;padding-top:14px;border-top:1px solid #1a1a1a;">

                {{-- Sous-total --}}
                @php
                    $sousTotal = $commande->lignes->sum('prix');
                    $totalRemises = $commande->lignes->sum('remise');
                @endphp

                @if($totalRemises > 0)
                <div style="display:flex;justify-content:space-between;
                            margin-bottom:6px;font-size:12px;">
                    <span style="color:#555;">Sous-total</span>
                    <span style="color:#888;">
                        {{ number_format($sousTotal + $totalRemises, 0, ',', ' ') }} FCFA
                    </span>
                </div>
                <div style="display:flex;justify-content:space-between;
                            margin-bottom:10px;font-size:12px;">
                    <span style="color:#22c55e;">Remises appliquées</span>
                    <span style="color:#22c55e;">
                        -{{ number_format($totalRemises, 0, ',', ' ') }} FCFA
                    </span>
                </div>
                @endif

                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:14px;font-weight:700;color:#e5e5e5;">
                        Total à payer
                    </span>
                    <span style="font-size:20px;font-weight:700;color:#f97316;">
                        {{ number_format($commande->montant, 0, ',', ' ') }} FCFA
                    </span>
                </div>
            </div>
        </div>

        {{-- ── 4. Note & commentaire client (après service) ── --}}
        @if(in_array($commande->statut_courant, ['Servie', 'Livrée']))
        <div class="card">
            <h3 style="font-size:13px;font-weight:700;color:#e5e5e5;margin:0 0 14px;
                        display:flex;align-items:center;gap:7px;">
                <i class="fa-solid fa-star" style="color:#eab308;"></i>
                Évaluation du service
            </h3>

            @if($commande->note)
            {{-- Note déjà enregistrée --}}
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                <div class="stars">
                    @for($i = 1; $i <= 5; $i++)
                    <span class="star {{ $i <= $commande->note ? 'active' : '' }}">★</span>
                    @endfor
                </div>
                <span style="font-size:13px;font-weight:700;color:#eab308;">
                    {{ $commande->note }}/5
                </span>
            </div>
            @if($commande->commentaires)
            <div style="padding:10px 14px;border-radius:9px;background:var(--cc-dark2);
                        border:1px solid #1a1a1a;font-size:13px;color:#888;font-style:italic;">
                "{{ $commande->commentaires }}"
            </div>
            @endif

            @else
            {{-- Formulaire de notation --}}
            <form method="POST"
                  action="{{ route('commandes.noter', $commande->idcommande) }}"
                  id="noteForm">
                @csrf @method('PATCH')

                <div style="margin-bottom:12px;">
                    <div style="font-size:11px;color:#444;margin-bottom:8px;">Note (cliquez sur une étoile)</div>
                    <div class="stars" id="starRating">
                        @for($i = 1; $i <= 5; $i++)
                        <span class="star" data-note="{{ $i }}" onclick="setNote({{ $i }})">★</span>
                        @endfor
                    </div>
                    <input type="hidden" name="note" id="inputNote" value="">
                </div>

                <div style="margin-bottom:12px;">
                    <textarea name="commentaires"
                              class="field-input"
                              rows="2"
                              style="resize:none;"
                              placeholder="Commentaire du client (optionnel)..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-sm"
                        onclick="if(!document.getElementById('inputNote').value){ alert('Sélectionnez une note'); return false; }">
                    <i class="fa-solid fa-check"></i>
                    Enregistrer la note
                </button>
            </form>
            @endif
        </div>
        @endif

    </div>

    {{-- ════════════════════════════════════
         COLONNE DROITE
    ════════════════════════════════════ --}}
    <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- ── 5. Changer le statut manuellement ── --}}
        @if(!in_array($commande->statut_courant, ['Livrée', 'Servie', 'Annulée']))
        @if(in_array(auth()->user()->role, ['Administrateur', 'Caissier', 'Cuisinier', 'Livreur']))
        <div class="card">
            <h3 style="font-size:13px;font-weight:700;color:#e5e5e5;margin:0 0 14px;
                        display:flex;align-items:center;gap:7px;">
                <i class="fa-solid fa-sliders" style="color:var(--cc-orange);"></i>
                Changer le statut
            </h3>

            <form method="POST"
                  action="{{ route('commandes.statut', $commande->idcommande) }}"
                  id="statutForm">
                @csrf @method('PATCH')

                {{-- Sélecteur de statut --}}
                <div style="margin-bottom:10px;">
                    <label style="font-size:10px;color:#444;text-transform:uppercase;
                                  letter-spacing:.5px;display:block;margin-bottom:6px;">
                        Nouveau statut
                    </label>
                    <select name="statut"
                            id="statutSelect"
                            class="statut-select"
                            style="width:100%;"
                            onchange="toggleJustification(this.value)">
                        @foreach($statuts as $s)
                        @if($s->intitule !== $commande->statut_courant)
                        <option value="{{ $s->intitule }}">{{ $s->intitule }}</option>
                        @endif
                        @endforeach
                    </select>
                </div>

                {{-- Justification (obligatoire pour Annulée) --}}
                <div id="justificationBlock" style="display:none;margin-bottom:10px;">
                    <label style="font-size:10px;color:#f87171;text-transform:uppercase;
                                  letter-spacing:.5px;display:block;margin-bottom:6px;">
                        Justification (obligatoire)
                    </label>
                    <textarea name="description"
                              class="field-input"
                              id="justificationText"
                              rows="2"
                              style="resize:none;"
                              placeholder="Raison de l'annulation..."></textarea>
                </div>

                {{-- Bouton --}}
                <button type="button"
                        onclick="soumettreStatut()"
                        class="btn btn-primary"
                        style="width:100%;justify-content:center;">
                    <i class="fa-solid fa-check"></i>
                    Mettre à jour
                </button>
            </form>
        </div>
        @endif
        @endif

        {{-- ── 6. Résumé rapide ── --}}
        <div class="card">
            <h3 style="font-size:13px;font-weight:700;color:#e5e5e5;margin:0 0 14px;
                        display:flex;align-items:center;gap:7px;">
                <i class="fa-solid fa-receipt" style="color:var(--cc-orange);"></i>
                Résumé
            </h3>

            <div style="display:flex;flex-direction:column;gap:8px;">

                <div style="display:flex;justify-content:space-between;font-size:12px;">
                    <span style="color:#555;">Référence</span>
                    <span style="color:#e5e5e5;font-weight:600;font-family:monospace;">
                        {{ $commande->reference }}
                    </span>
                </div>

                <div style="display:flex;justify-content:space-between;font-size:12px;">
                    <span style="color:#555;">Date</span>
                    <span style="color:#888;">
                        {{ $commande->datecommande->format('d/m/Y') }}
                        à {{ $commande->heurecommande }}
                    </span>
                </div>

                <div style="display:flex;justify-content:space-between;font-size:12px;">
                    <span style="color:#555;">Articles</span>
                    <span style="color:#888;">{{ $commande->lignes->count() }} plat(s)</span>
                </div>

                <div style="display:flex;justify-content:space-between;font-size:12px;">
                    <span style="color:#555;">Paiement</span>
                    <span style="color:#888;">{{ $commande->mode_paiement ?? 'Espèces' }}</span>
                </div>

                @if($commande->table)
                <div style="display:flex;justify-content:space-between;font-size:12px;">
                    <span style="color:#555;">Table</span>
                    <span style="color:#60a5fa;">{{ $commande->table->intitule }}</span>
                </div>
                @elseif($commande->typecommande === 'A emporter')
                <div style="display:flex;justify-content:space-between;font-size:12px;">
                    <span style="color:#555;">Type</span>
                    <span style="color:#22c55e;">À emporter</span>
                </div>
                @endif

                <div style="height:1px;background:#1a1a1a;margin:4px 0;"></div>

                <div style="display:flex;justify-content:space-between;">
                    <span style="font-size:13px;font-weight:600;color:#e5e5e5;">Total</span>
                    <span style="font-size:16px;font-weight:700;color:#f97316;">
                        {{ number_format($commande->montant, 0, ',', ' ') }} FCFA
                    </span>
                </div>
            </div>
        </div>

        {{-- ── 7. Historique des statuts ── --}}
        <div class="card">
            <h3 style="font-size:13px;font-weight:700;color:#e5e5e5;margin:0 0 16px;
                        display:flex;align-items:center;gap:7px;">
                <i class="fa-solid fa-timeline" style="color:var(--cc-orange);"></i>
                Historique
            </h3>

            @if($commande->historiques->isNotEmpty())
            <div class="timeline">
                @foreach($commande->historiques as $h)
                @php
                    $slugH = match($h->statut->intitule ?? '') {
                        'En attente'     => 'attente',
                        'En préparation' => 'prep',
                        'Expédiée'       => 'expediee',
                        'Servie'         => 'servie',
                        'Livrée'         => 'livree',
                        'Annulée'        => 'annulee',
                        default          => 'attente'
                    };
                    $iconeH = match($h->statut->intitule ?? '') {
                        'En attente'     => 'fa-clock',
                        'En préparation' => 'fa-fire-burner',
                        'Expédiée'       => 'fa-motorcycle',
                        'Livrée'         => 'fa-circle-check',
                        'Servie'         => 'fa-utensils',
                        'Annulée'        => 'fa-circle-xmark',
                        default          => 'fa-circle',
                    };
                    $couleurH = match($slugH) {
                        'attente'   => '#eab308',
                        'prep'      => '#60a5fa',
                        'expediee'  => '#f97316',
                        'servie','livree' => '#22c55e',
                        'annulee'   => '#f87171',
                        default     => '#555'
                    };
                @endphp
                <div class="timeline-item">
                    <div class="timeline-dot"
                         style="border-color: {{ $couleurH }};
                                background: rgba({{ implode(',', sscanf($couleurH, '#%02x%02x%02x')) }},.1);">
                        <i class="fa-solid {{ $iconeH }}"
                           style="color:{{ $couleurH }};font-size:9px;"></i>
                    </div>

                    <div>
                        {{-- Statut --}}
                        <div style="font-size:12px;font-weight:600;color:#e5e5e5;">
                            {{ $h->statut->intitule ?? 'N/A' }}
                        </div>

                        {{-- Description --}}
                        @if($h->description)
                        <div style="font-size:11px;color:#555;margin-top:2px;line-height:1.4;">
                            {{ $h->description }}
                        </div>
                        @endif

                        {{-- Date & heure --}}
                        <div style="font-size:10px;color:#333;margin-top:3px;">
                            <i class="fa-regular fa-clock" style="margin-right:3px;"></i>
                            {{ $h->created_at->format('d/m/Y à H:i:s') }}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div style="text-align:center;padding:20px;color:#2a2a2a;">
                <i class="fa-solid fa-timeline" style="font-size:22px;display:block;margin-bottom:6px;"></i>
                <p style="font-size:12px;">Aucun historique</p>
            </div>
            @endif
        </div>

        {{-- ── 8. Actions supplémentaires ── --}}
        @if(auth()->user()->role === 'Administrateur')
        <div class="card">
            <h3 style="font-size:13px;font-weight:700;color:#e5e5e5;margin:0 0 14px;
                        display:flex;align-items:center;gap:7px;">
                <i class="fa-solid fa-gear" style="color:var(--cc-orange);"></i>
                Actions administrateur
            </h3>

            <div style="display:flex;flex-direction:column;gap:8px;">

                {{-- Historique complet --}}
                <a href="{{ route('commandes.historique', $commande->idcommande) }}"
                   class="btn btn-ghost btn-sm" style="justify-content:flex-start;">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    Voir l'historique complet
                </a>

                {{-- Reçu PDF --}}
                @if(in_array($commande->statut_courant, ['Servie','Livrée']))
                <a href="{{ route('caisse.recu', $commande->idcommande) }}"
                   target="_blank"
                   class="btn btn-ghost btn-sm" style="justify-content:flex-start;">
                    <i class="fa-solid fa-file-pdf" style="color:#f97316;"></i>
                    Générer le reçu PDF
                </a>
                @endif

                {{-- Supprimer (soft delete - commandes terminées) --}}
                @if(in_array($commande->statut_courant, ['Servie','Livrée','Annulée']))
                <form method="POST"
                      action="{{ route('commandes.destroy', $commande->idcommande) }}"
                      id="deleteForm">
                    @csrf @method('DELETE')
                    <button type="button"
                            onclick="confirmerSuppression()"
                            class="btn btn-danger btn-sm"
                            style="width:100%;justify-content:flex-start;">
                        <i class="fa-solid fa-trash"></i>
                        Supprimer la commande
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endif

    </div>
</div>

@endsection

@push('scripts')
<script>
// ════════════════════════════════════════════════════════════
// GESTION DU CHANGEMENT DE STATUT
// ════════════════════════════════════════════════════════════

/**
 * Affiche ou masque le champ de justification
 * selon le statut sélectionné (obligatoire si Annulée)
 */
function toggleJustification(statut) {
    const block = document.getElementById('justificationBlock');
    block.style.display = statut === 'Annulée' ? 'block' : 'none';
}

/**
 * Soumet le formulaire de changement de statut
 * avec confirmation SweetAlert
 */
function soumettreStatut() {
    const select      = document.getElementById('statutSelect');
    const statut      = select.value;
    const justif      = document.getElementById('justificationText');

    // Validation : justification obligatoire pour annulation
    if (statut === 'Annulée') {
        const texte = justif ? justif.value.trim() : '';
        if (texte.length < 5) {
            Swal.fire({
                title: 'Justification requise',
                text: 'Veuillez saisir une justification d\'au moins 5 caractères pour annuler.',
                icon: 'warning',
                iconColor: '#ea580c',
                background: '#141414',
                color: '#e5e5e5',
                confirmButtonColor: '#ea580c',
            });
            return;
        }
    }

    // Confirmation avant changement
    Swal.fire({
        title: 'Changer le statut ?',
        html: `
            <div style="color:#666;font-size:13px;margin-bottom:4px;">
                Nouveau statut :
            </div>
            <div style="font-size:15px;font-weight:700;color:#f97316;">
                ${statut}
            </div>
        `,
        icon: statut === 'Annulée' ? 'warning' : 'question',
        iconColor: statut === 'Annulée' ? '#ef4444' : '#ea580c',
        background: '#141414',
        color: '#e5e5e5',
        confirmButtonColor: statut === 'Annulée' ? '#ef4444' : '#ea580c',
        confirmButtonText: '<i class="fa-solid fa-check" style="margin-right:6px"></i>Confirmer',
        showCancelButton: true,
        cancelButtonText: 'Annuler',
        cancelButtonColor: '#1f1f1f',
    }).then(result => {
        if (result.isConfirmed) {
            document.getElementById('statutForm').submit();
        }
    });
}

// ════════════════════════════════════════════════════════════
// CHANGEMENT RAPIDE DEPUIS L'EN-TÊTE (bouton prochain statut)
// ════════════════════════════════════════════════════════════

/**
 * Changer le statut directement depuis le bouton d'en-tête
 * sans passer par le sélecteur
 */
function changerStatut(statut) {
    Swal.fire({
        title: `Passer à : ${statut} ?`,
        html: `
            <div style="color:#666;font-size:13px;">
                Commande {{ $commande->reference }}
            </div>
        `,
        icon: 'question',
        iconColor: '#ea580c',
        background: '#141414',
        color: '#e5e5e5',
        confirmButtonColor: '#ea580c',
        confirmButtonText: '<i class="fa-solid fa-check" style="margin-right:6px"></i>Confirmer',
        showCancelButton: true,
        cancelButtonText: 'Annuler',
        cancelButtonColor: '#1f1f1f',
    }).then(result => {
        if (!result.isConfirmed) return;

        // Appel AJAX PATCH
        fetch('{{ route("commandes.statut", $commande->idcommande) }}', {
            method: 'PATCH',
            headers: {
                'Content-Type':     'application/json',
                'Accept':           'application/json',
                'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ statut })
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) throw new Error(data.message);

            Swal.fire({
                toast: true,
                position: 'bottom-end',
                icon: 'success',
                title: `Statut mis à jour : ${statut}`,
                timer: 1800,
                showConfirmButton: false,
                background: '#141414',
                color: '#e5e5e5',
                iconColor: '#22c55e',
            });

            // Recharger la page pour refléter le nouveau statut
            setTimeout(() => location.reload(), 1400);
        })
        .catch(err => {
            Swal.fire({
                toast: true,
                position: 'bottom-end',
                icon: 'error',
                title: err.message || 'Erreur serveur',
                timer: 3000,
                showConfirmButton: false,
                background: '#141414',
                color: '#e5e5e5',
            });
        });
    });
}

// ════════════════════════════════════════════════════════════
// ANNULATION DE LA COMMANDE
// ════════════════════════════════════════════════════════════

/**
 * Ouvre une boîte de dialogue pour annuler la commande
 * avec saisie obligatoire de la justification
 */
function annulerCommande() {
    Swal.fire({
        title: 'Annuler la commande ?',
        html: `
            <div style="color:#666;font-size:13px;margin-bottom:12px;">
                Commande <strong style="color:#f97316;">{{ $commande->reference }}</strong>
                — Cette action est <strong>irréversible</strong>.
            </div>
            <textarea id="justifSwal"
                      placeholder="Justification obligatoire (min. 5 caractères)..."
                      style="width:100%;padding:10px;border-radius:9px;border:1px solid #2a2a2a;
                             background:#0d0d0d;color:#e5e5e5;font-size:12px;
                             resize:none;outline:none;min-height:80px;font-family:inherit;"
                      rows="3"></textarea>
        `,
        icon: 'warning',
        iconColor: '#ef4444',
        background: '#141414',
        color: '#e5e5e5',
        confirmButtonColor: '#ef4444',
        confirmButtonText: '<i class="fa-solid fa-xmark" style="margin-right:6px"></i>Annuler la commande',
        showCancelButton: true,
        cancelButtonText: 'Retour',
        cancelButtonColor: '#1f1f1f',
        preConfirm: () => {
            // Valider la justification avant soumission
            const j = document.getElementById('justifSwal').value.trim();
            if (j.length < 5) {
                Swal.showValidationMessage('La justification doit contenir au moins 5 caractères.');
                return false;
            }
            return j;
        }
    }).then(result => {
        if (!result.isConfirmed) return;

        // Appel AJAX PATCH avec statut Annulée
        fetch('{{ route("commandes.statut", $commande->idcommande) }}', {
            method: 'PATCH',
            headers: {
                'Content-Type':     'application/json',
                'Accept':           'application/json',
                'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                statut:      'Annulée',
                description: result.value,
            })
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) throw new Error(data.message);

            Swal.fire({
                toast: true,
                position: 'bottom-end',
                icon: 'success',
                title: 'Commande annulée',
                timer: 1800,
                showConfirmButton: false,
                background: '#141414',
                color: '#e5e5e5',
                iconColor: '#22c55e',
            });

            setTimeout(() => location.reload(), 1400);
        })
        .catch(err => {
            Swal.fire({
                toast: true,
                position: 'bottom-end',
                icon: 'error',
                title: err.message || 'Erreur serveur',
                timer: 3000,
                showConfirmButton: false,
                background: '#141414',
                color: '#e5e5e5',
            });
        });
    });
}

// ════════════════════════════════════════════════════════════
// SUPPRESSION DE LA COMMANDE (Admin uniquement)
// ════════════════════════════════════════════════════════════

function confirmerSuppression() {
    Swal.fire({
        title: 'Supprimer définitivement ?',
        text: 'La commande {{ $commande->reference }} sera archivée et ne pourra plus être récupérée.',
        icon: 'warning',
        iconColor: '#ef4444',
        background: '#141414',
        color: '#e5e5e5',
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Oui, supprimer',
        showCancelButton: true,
        cancelButtonText: 'Annuler',
        cancelButtonColor: '#1f1f1f',
    }).then(result => {
        if (result.isConfirmed) {
            document.getElementById('deleteForm').submit();
        }
    });
}

// ════════════════════════════════════════════════════════════
// SYSTÈME DE NOTATION PAR ÉTOILES
// ════════════════════════════════════════════════════════════

/**
 * Définit la note sélectionnée et met à jour l'affichage
 * @param {number} note - Note de 1 à 5
 */
function setNote(note) {
    // Enregistrer la valeur dans le champ caché
    document.getElementById('inputNote').value = note;

    // Mettre à jour l'affichage des étoiles
    document.querySelectorAll('.star[data-note]').forEach(star => {
        const starNote = parseInt(star.dataset.note);
        star.classList.toggle('active', starNote <= note);
    });
}

// Survol des étoiles (prévisualisation)
document.querySelectorAll('.star[data-note]').forEach(star => {
    star.addEventListener('mouseover', function() {
        const hoverNote = parseInt(this.dataset.note);
        document.querySelectorAll('.star[data-note]').forEach(s => {
            s.classList.toggle('active', parseInt(s.dataset.note) <= hoverNote);
        });
    });

    // Restaurer la vraie note au départ de la souris
    star.addEventListener('mouseout', function() {
        const noteActuelle = parseInt(document.getElementById('inputNote')?.value || '0');
        document.querySelectorAll('.star[data-note]').forEach(s => {
            s.classList.toggle('active', parseInt(s.dataset.note) <= noteActuelle);
        });
    });
});
</script>
@endpush