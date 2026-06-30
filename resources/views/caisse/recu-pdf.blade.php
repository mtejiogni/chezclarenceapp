<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Reçu {{ $commande->reference }}</title>
<style>
    /* ══════════════════════════════════════════════════════════
       DomPDF a un support CSS limité : on reste volontairement
       sur des blocs/tables simples, pas de flexbox/grid.
       Format pensé pour un ticket ~80mm (voir setPaper dans
       CaisseController::recu()).
    ══════════════════════════════════════════════════════════ */
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: 'DejaVu Sans Mono', monospace;
        font-size: 10px;
        color: #000;
        width: 100vw;
        padding: 10px 12px;
    }

    .center { text-align: center; }
    .right  { text-align: right; }
    .bold   { font-weight: bold; }

    .restaurant-nom {
        font-size: 15px;
        font-weight: bold;
        letter-spacing: 1px;
    }

    .restaurant-info {
        font-size: 9px;
        color: #333;
        margin-top: 2px;
        line-height: 1.5;
    }

    .separator {
        border-top: 1px dashed #000;
        margin: 10px 0;
    }

    .separator-double {
        border-top: 2px solid #000;
        margin: 10px 0;
    }

    .ligne-info {
        width: 100%;
        margin-bottom: 2px;
    }
    .ligne-info td { padding: 1px 0; font-size: 9.5px; }
    .ligne-info .label { color: #333; }
    .ligne-info .valeur { font-weight: bold; text-align: right; }

    .badge-type {
        display: inline-block;
        border: 1px solid #000;
        padding: 3px 10px;
        border-radius: 10px;
        font-size: 10px;
        font-weight: bold;
        margin: 6px 0;
    }

    table.articles {
        width: 100%;
        border-collapse: collapse;
        margin-top: 6px;
    }
    table.articles th {
        font-size: 8.5px;
        text-transform: uppercase;
        border-bottom: 1px solid #000;
        padding: 4px 0;
        text-align: left;
    }
    table.articles th.right,
    table.articles td.right { text-align: right; }
    table.articles td {
        font-size: 9.5px;
        padding: 4px 0;
        border-bottom: 1px dotted #999;
        vertical-align: top;
    }
    table.articles .nom-article { font-weight: bold; }
    table.articles .sous-categorie { font-size: 8px; color: #555; }

    .totaux { width: 100%; margin-top: 8px; }
    .totaux td { padding: 2px 0; font-size: 10px; }
    .totaux .total-final td {
        font-size: 14px;
        font-weight: bold;
        padding-top: 6px;
        border-top: 1px solid #000;
    }

    .footer {
        margin-top: 16px;
        text-align: center;
        font-size: 9px;
        color: #333;
        line-height: 1.6;
    }

    .footer .merci {
        font-size: 11px;
        font-weight: bold;
        margin-bottom: 4px;
    }

    .note-box {
        margin-top: 8px;
        padding: 6px;
        border: 1px dashed #000;
        font-size: 9px;
    }
</style>
</head>
<body>

    {{-- ══════════════════════════════════════════════════════
         EN-TÊTE RESTAURANT
         [AJOUT] données réelles issues de la table `parametres`
         (plus de placeholders en dur).
    ══════════════════════════════════════════════════════════ --}}
    <div class="center">
        @if($parametres->logo)
        <img src="{{ public_path('storage/' . $parametres->logo) }}"
             style="height:42px;margin-bottom:6px;">
        @endif
        <div class="restaurant-nom">{{ $parametres->nom_restaurant ?? config('app.name', 'RESTAURANT') }}</div>
        @if($parametres->slogan)
        <div class="restaurant-info" style="font-style:italic;">{{ $parametres->slogan }}</div>
        @endif
        <div class="restaurant-info">
            @if($parametres->adresse){{ $parametres->adresse }}@if($parametres->ville), {{ $parametres->ville }}@endif<br>@endif
            @if($parametres->telephone)Tél : {{ $parametres->telephone }}@if($parametres->telephone2) / {{ $parametres->telephone2 }}@endif<br>@endif
            @if($parametres->email){{ $parametres->email }}<br>@endif
            @if($parametres->mention_legale){{ $parametres->mention_legale }}@endif
        </div>
    </div>

    <div class="separator"></div>

    {{-- ══════════════════════════════════════════════════════
         RÉFÉRENCE DU TICKET
         [AJOUT] préfixe configurable (prefixe_recu)
    ══════════════════════════════════════════════════════════ --}}
    <div class="center" style="font-size:9px;color:#555;margin-bottom:4px;">
        Ticket n° {{ ($parametres->prefixe_recu ?? 'CC') . '-' . $commande->reference }}
    </div>

    {{-- ══════════════════════════════════════════════════════
         INFOS COMMANDE
    ══════════════════════════════════════════════════════════ --}}
    <table class="ligne-info">
        <tr>
            <td class="label">Référence</td>
            <td class="valeur">{{ $commande->reference }}</td>
        </tr>
        <tr>
            <td class="label">Date</td>
            <td class="valeur">{{ $commande->datecommande->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="label">Heure</td>
            <td class="valeur">{{ $commande->heurecommande }}</td>
        </tr>
        @if($commande->serveur)
        <tr>
            <td class="label">Servi par</td>
            <td class="valeur">{{ $commande->serveur->prenom }} {{ $commande->serveur->nom }}</td>
        </tr>
        @endif
    </table>

    {{-- ══════════════════════════════════════════════════════
         TYPE DE COMMANDE
         [IMPORTANT] gestion explicite des 3 types : Standard
         (table), A emporter (aucune table/adresse), Livraison
         (adresse + client). Ne pas réduire à un simple if/else
         binaire Standard/Livraison.
    ══════════════════════════════════════════════════════════ --}}
    <div class="center">
        @if($commande->typecommande === 'Standard')
        <span class="badge-type">SUR PLACE — {{ $commande->table->intitule ?? 'Table N/A' }}</span>
        @elseif($commande->typecommande === 'A emporter')
        <span class="badge-type">À EMPORTER</span>
        @else
        <span class="badge-type">LIVRAISON</span>
        @endif
    </div>

    @if($commande->typecommande === 'Livraison')
    <table class="ligne-info">
        @if($commande->client)
        <tr>
            <td class="label">Client</td>
            <td class="valeur">{{ $commande->client->prenom }} {{ $commande->client->nom }}</td>
        </tr>
        @if($commande->client->telephone)
        <tr>
            <td class="label">Téléphone</td>
            <td class="valeur">{{ $commande->client->telephone }}</td>
        </tr>
        @endif
        @endif
        @if($commande->adresse)
        <tr>
            <td class="label" style="vertical-align:top;">Adresse</td>
            <td class="valeur">{{ $commande->adresse }}</td>
        </tr>
        @endif
    </table>
    @endif

    <div class="separator"></div>

    {{-- ══════════════════════════════════════════════════════
         ARTICLES
    ══════════════════════════════════════════════════════════ --}}
    <table class="articles">
        <thead>
            <tr>
                <th>Article</th>
                <th class="right">Qté</th>
                <th class="right">P.U.</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($commande->lignes as $ligne)
            @php
                $prixUnitaire = $ligne->quantite > 0 ? $ligne->prix / $ligne->quantite : 0;
            @endphp
            <tr>
                <td>
                    <div class="nom-article">{{ $ligne->menu->intitule ?? 'Plat supprimé' }}</div>
                    @if($ligne->remise > 0)
                    <div class="sous-categorie">Remise : -{{ number_format($ligne->remise, 0, ',', ' ') }} F</div>
                    @endif
                </td>
                <td class="right">×{{ $ligne->quantite }}</td>
                <td class="right">{{ number_format($prixUnitaire, 0, ',', ' ') }}</td>
                <td class="right bold">{{ number_format($ligne->prix, 0, ',', ' ') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ══════════════════════════════════════════════════════
         TOTAUX
    ══════════════════════════════════════════════════════════ --}}
    @php
        $sousTotal    = $commande->lignes->sum(fn($l) => ($l->quantite > 0 ? $l->prix + $l->remise : $l->prix));
        $totalRemises = $commande->lignes->sum('remise');
        $devise       = $parametres->devise ?? 'FCFA';
    @endphp

    <table class="totaux">
        @if($totalRemises > 0)
        <tr>
            <td>Sous-total</td>
            <td class="right">{{ number_format($sousTotal, 0, ',', ' ') }} {{ $devise }}</td>
        </tr>
        <tr>
            <td>Remises</td>
            <td class="right">-{{ number_format($totalRemises, 0, ',', ' ') }} {{ $devise }}</td>
        </tr>
        @endif
        <tr class="total-final">
            <td>TOTAL</td>
            <td class="right">{{ number_format($commande->montant, 0, ',', ' ') }} {{ $devise }}</td>
        </tr>
    </table>
    @if(($parametres->tva ?? 0) > 0)
    <div style="font-size:8px;color:#555;text-align:right;margin-top:2px;">
        Dont TVA {{ $parametres->tva }}% (incluse)
    </div>
    @endif

    <div class="separator"></div>

    {{-- ══════════════════════════════════════════════════════
         PAIEMENT & STATUT
    ══════════════════════════════════════════════════════════ --}}
    <table class="ligne-info">
        <tr>
            <td class="label">Mode de paiement</td>
            <td class="valeur">{{ $commande->mode_paiement ?? 'Espèces' }}</td>
        </tr>
        <tr>
            <td class="label">Statut</td>
            <td class="valeur">{{ $commande->statut_courant }}</td>
        </tr>
    </table>

    @if($commande->consignes)
    <div class="note-box">
        <strong>Note :</strong> {{ $commande->consignes }}
    </div>
    @endif

    <div class="separator-double"></div>

    {{-- ══════════════════════════════════════════════════════
         PIED DE PAGE
    ══════════════════════════════════════════════════════════ --}}
    <div class="footer">
        <div class="merci">{{ $parametres->pied_recu ?? 'Merci de votre visite !' }}</div>
        <div>
            Ce reçu fait office de preuve de paiement.<br>
            Document généré le {{ now()->format('d/m/Y à H:i') }}
        </div>
    </div>

</body>
</html>