<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Rapport de caisse — {{ $date->format('d/m/Y') }}</title>
<style>
    /* ══════════════════════════════════════════════════════════
       DomPDF a un support CSS limité : on reste sur des blocs et
       tables simples (pas de flexbox/grid). Format A4 portrait
       (voir setPaper dans CaisseController::rapport()).
    ══════════════════════════════════════════════════════════ */
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: 'DejaVu Sans', sans-serif;
        font-size: 11px;
        color: #1a1a1a;
        padding: 30px 36px;
    }

    .center { text-align: center; }
    .right  { text-align: right; }
    .bold   { font-weight: bold; }
    .muted  { color: #666; }

    /* ── En-tête ── */
    .header-table { width: 100%; margin-bottom: 18px; }
    .header-table td { vertical-align: top; }

    .restaurant-nom {
        font-size: 18px;
        font-weight: bold;
        color: #1a1a1a;
    }
    .restaurant-info {
        font-size: 9.5px;
        color: #555;
        line-height: 1.5;
        margin-top: 3px;
    }

    .rapport-titre {
        font-size: 15px;
        font-weight: bold;
        color: #c2410c;
        text-transform: uppercase;
        letter-spacing: .5px;
    }
    .rapport-date {
        font-size: 12px;
        color: #333;
        margin-top: 3px;
    }
    .rapport-meta {
        font-size: 9px;
        color: #888;
        margin-top: 6px;
    }

    .separator {
        border-top: 2px solid #1a1a1a;
        margin: 14px 0 18px;
    }

    /* ── Section ── */
    .section-titre {
        font-size: 12px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #1a1a1a;
        padding-bottom: 5px;
        border-bottom: 1px solid #ccc;
        margin: 20px 0 10px;
    }

    /* ── KPI cards (table-based, DomPDF-safe) ── */
    table.kpi-grid { width: 100%; border-collapse: collapse; }
    table.kpi-grid td {
        width: 25%;
        border: 1px solid #ddd;
        padding: 10px 12px;
        text-align: center;
    }
    .kpi-val   { font-size: 18px; font-weight: bold; color: #1a1a1a; }
    .kpi-label { font-size: 8.5px; color: #777; text-transform: uppercase; margin-top: 3px; }

    /* ── Tableau répartition ── */
    table.repartition { width: 100%; border-collapse: collapse; }
    table.repartition th {
        background: #f3f3f3;
        font-size: 9px;
        text-transform: uppercase;
        text-align: left;
        padding: 6px 10px;
        border-bottom: 1px solid #ccc;
    }
    table.repartition td {
        font-size: 10.5px;
        padding: 6px 10px;
        border-bottom: 1px solid #eee;
    }
    table.repartition td.right,
    table.repartition th.right { text-align: right; }

    /* ── Tableau commandes ── */
    table.commandes { width: 100%; border-collapse: collapse; }
    table.commandes th {
        background: #f3f3f3;
        font-size: 8.5px;
        text-transform: uppercase;
        text-align: left;
        padding: 6px 8px;
        border-bottom: 1px solid #ccc;
    }
    table.commandes th.right,
    table.commandes td.right { text-align: right; }
    table.commandes td {
        font-size: 9.5px;
        padding: 5px 8px;
        border-bottom: 1px solid #eee;
    }
    table.commandes tr.annulee td { color: #b91c1c; }
    table.commandes tr.annulee td.statut { text-decoration: line-through; }

    .badge {
        display: inline-block;
        padding: 1px 7px;
        border-radius: 8px;
        font-size: 8px;
        font-weight: bold;
    }
    .badge-encaissee { background: #dcfce7; color: #166534; }
    .badge-annulee   { background: #fee2e2; color: #991b1b; }
    .badge-autre     { background: #e5e7eb; color: #374151; }

    .totaux-finaux { width: 100%; margin-top: 10px; }
    .totaux-finaux td { padding: 4px 0; font-size: 11px; }
    .totaux-finaux .total-final td {
        font-size: 16px;
        font-weight: bold;
        padding-top: 8px;
        border-top: 2px solid #1a1a1a;
        color: #166534;
    }

    .signature-zone {
        margin-top: 40px;
        width: 100%;
    }
    .signature-zone td {
        width: 50%;
        font-size: 10px;
        color: #555;
        padding-top: 30px;
        border-top: 1px solid #999;
    }

    .footer {
        margin-top: 30px;
        text-align: center;
        font-size: 8.5px;
        color: #999;
    }

    .page-break { page-break-after: always; }
</style>
</head>
<body>

    {{-- ══════════════════════════════════════════════════════
         EN-TÊTE
         [AJOUT] données réelles issues de la table `parametres`
         (plus de placeholders en dur).
    ══════════════════════════════════════════════════════════ --}}
    <table class="header-table">
        <tr>
            <td style="width:60%;">
                @if($parametres->logo)
                <img src="{{ public_path('storage/' . $parametres->logo) }}" style="height:38px;margin-bottom:4px;">
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
            </td>
            <td style="width:40%;" class="right">
                <div class="rapport-titre">Rapport de caisse</div>
                <div class="rapport-date">{{ $date->translatedFormat('l d F Y') }}</div>
                <div class="rapport-meta">
                    Généré le {{ now()->format('d/m/Y à H:i') }}<br>
                    Par {{ auth()->user()->prenom ?? '' }} {{ auth()->user()->nom ?? '' }}
                </div>
            </td>
        </tr>
    </table>

    <div class="separator"></div>

    {{-- ══════════════════════════════════════════════════════
         KPIs DU JOUR
    ══════════════════════════════════════════════════════════ --}}
    @php
        $devise = $parametres->devise ?? 'FCFA';
    @endphp
    <table class="kpi-grid">
        <tr>
            <td>
                <div class="kpi-val">{{ number_format($totalCaisse, 0, ',', ' ') }}</div>
                <div class="kpi-label">Total encaissé ({{ $devise }})</div>
            </td>
            <td>
                <div class="kpi-val">{{ $nbEncaissees }}</div>
                <div class="kpi-label">Commandes encaissées</div>
            </td>
            <td>
                <div class="kpi-val">{{ number_format($panierMoyen, 0, ',', ' ') }}</div>
                <div class="kpi-label">Panier moyen ({{ $devise }})</div>
            </td>
            <td>
                <div class="kpi-val">{{ $nbAnnulees }}</div>
                <div class="kpi-label">Annulées</div>
            </td>
        </tr>
    </table>

    {{-- ══════════════════════════════════════════════════════
         RÉPARTITION PAR TYPE DE COMMANDE
         [IMPORTANT] les 3 types sont affichés explicitement
         (Sur place / À emporter / Livraison) — ne pas réduire
         à Standard/Livraison.
    ══════════════════════════════════════════════════════════ --}}
    <div class="section-titre">Répartition par type de commande</div>
    @php
        $totalTypes = array_sum($dataRepartition);
    @endphp
    <table class="repartition">
        <thead>
            <tr>
                <th>Type</th>
                <th class="right">Nb commandes</th>
                <th class="right">Part</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Sur place</td>
                <td class="right">{{ $dataRepartition['Standard'] }}</td>
                <td class="right">{{ $totalTypes > 0 ? round(($dataRepartition['Standard'] / $totalTypes) * 100) : 0 }}%</td>
            </tr>
            <tr>
                <td>À emporter</td>
                <td class="right">{{ $dataRepartition['A emporter'] }}</td>
                <td class="right">{{ $totalTypes > 0 ? round(($dataRepartition['A emporter'] / $totalTypes) * 100) : 0 }}%</td>
            </tr>
            <tr>
                <td>Livraison</td>
                <td class="right">{{ $dataRepartition['Livraison'] }}</td>
                <td class="right">{{ $totalTypes > 0 ? round(($dataRepartition['Livraison'] / $totalTypes) * 100) : 0 }}%</td>
            </tr>
        </tbody>
    </table>

    {{-- ══════════════════════════════════════════════════════
         RÉPARTITION PAR MODE DE PAIEMENT
    ══════════════════════════════════════════════════════════ --}}
    <div class="section-titre">Répartition par mode de paiement</div>
    <table class="repartition">
        <thead>
            <tr>
                <th>Mode</th>
                <th class="right">Montant ({{ $devise }})</th>
                <th class="right">Part</th>
            </tr>
        </thead>
        <tbody>
            @forelse($parModePaiement as $mode => $montant)
            <tr>
                <td>{{ $mode }}</td>
                <td class="right">{{ number_format($montant, 0, ',', ' ') }}</td>
                <td class="right">{{ $totalCaisse > 0 ? round(($montant / $totalCaisse) * 100) : 0 }}%</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="center muted">Aucun encaissement ce jour</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ══════════════════════════════════════════════════════
         DÉTAIL DE TOUTES LES COMMANDES DU JOUR
    ══════════════════════════════════════════════════════════ --}}
    <div class="section-titre">Détail des commandes ({{ $commandesJour->count() }})</div>
    <table class="commandes">
        <thead>
            <tr>
                <th>Référence</th>
                <th>Heure</th>
                <th>Type</th>
                <th>Paiement</th>
                <th>Statut</th>
                <th class="right">Montant</th>
            </tr>
        </thead>
        <tbody>
            @forelse($commandesJour as $cmd)
            @php
                $estAnnulee   = $cmd->statut_courant === 'Annulée';
                $estEncaissee = in_array($cmd->statut_courant, ['Servie', 'Livrée']);
                $typeLabel    = $cmd->typecommande === 'A emporter' ? 'À emporter' : $cmd->typecommande;
                $badgeClass   = $estAnnulee ? 'badge-annulee' : ($estEncaissee ? 'badge-encaissee' : 'badge-autre');
            @endphp
            <tr class="{{ $estAnnulee ? 'annulee' : '' }}">
                <td class="bold">{{ $cmd->reference }}</td>
                <td>{{ $cmd->heurecommande }}</td>
                <td>{{ $typeLabel }}</td>
                <td>{{ $cmd->mode_paiement ?? '—' }}</td>
                <td class="statut">
                    <span class="badge {{ $badgeClass }}">{{ $cmd->statut_courant }}</span>
                </td>
                <td class="right bold">{{ number_format($cmd->montant, 0, ',', ' ') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="center muted">Aucune commande enregistrée ce jour</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ══════════════════════════════════════════════════════
         TOTAL FINAL
    ══════════════════════════════════════════════════════════ --}}
    <table class="totaux-finaux">
        <tr>
            <td class="muted">Commandes encaissées</td>
            <td class="right">{{ $nbEncaissees }}</td>
        </tr>
        <tr>
            <td class="muted">Commandes annulées</td>
            <td class="right">{{ $nbAnnulees }}</td>
        </tr>
        <tr class="total-final">
            <td>TOTAL CAISSE</td>
            <td class="right">{{ number_format($totalCaisse, 0, ',', ' ') }} {{ $devise }}</td>
        </tr>
    </table>

    {{-- ══════════════════════════════════════════════════════
         ZONE DE SIGNATURE
    ══════════════════════════════════════════════════════════ --}}
    <table class="signature-zone">
        <tr>
            <td>Signature du caissier</td>
            <td class="right">Signature du responsable</td>
        </tr>
    </table>

    <div class="footer">
        Rapport généré automatiquement par le système de gestion — {{ $parametres->nom_restaurant ?? config('app.name', 'RESTAURANT') }}
        @if(($parametres->tva ?? 0) > 0)
        <br>TVA appliquée : {{ $parametres->tva }}%
        @endif
    </div>

</body>
</html>