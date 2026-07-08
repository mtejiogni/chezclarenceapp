<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Sauvegarde — {{ now()->format('d/m/Y H:i') }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: 'DejaVu Sans', sans-serif;
        font-size: 9px;
        color: #1a1a1a;
        padding: 24px 30px;
    }

    .header-table { width: 100%; margin-bottom: 16px; }
    .restaurant-nom { font-size: 15px; font-weight: bold; }
    .rapport-titre {
        font-size: 13px; font-weight: bold; color: #c2410c;
        text-transform: uppercase; letter-spacing: .5px;
    }
    .rapport-meta { font-size: 8px; color: #888; margin-top: 4px; }

    .separator { border-top: 2px solid #1a1a1a; margin: 10px 0 16px; }

    .section-titre {
        font-size: 11px; font-weight: bold; text-transform: uppercase;
        color: #fff; background: #1a1a1a; padding: 6px 10px;
        margin: 18px 0 8px; border-radius: 3px;
    }
    .section-meta { font-size: 8px; color: #666; margin-bottom: 6px; }

    table.donnees { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
    table.donnees th {
        background: #f3f3f3; font-size: 7.5px; text-transform: uppercase;
        text-align: left; padding: 4px 6px; border: 1px solid #ddd;
    }
    table.donnees td {
        font-size: 7.5px; padding: 4px 6px; border: 1px solid #eee;
        word-break: break-word;
    }
    table.donnees tr:nth-child(even) { background: #fafafa; }

    .tronque-note {
        font-size: 8px; color: #c2410c; font-style: italic; margin-top: 4px;
    }

    .footer {
        margin-top: 20px; text-align: center; font-size: 7.5px; color: #999;
    }
</style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td style="width:60%;">
                <div class="restaurant-nom">{{ $parametres->nom_restaurant ?? config('app.name', 'RESTAURANT') }}</div>
            </td>
            <td style="width:40%;text-align:right;">
                <div class="rapport-titre">Export de sauvegarde</div>
                <div class="rapport-meta">
                    Généré le {{ now()->format('d/m/Y à H:i:s') }}<br>
                    Par {{ auth()->user()->prenom ?? '' }} {{ auth()->user()->nom ?? '' }}
                </div>
            </td>
        </tr>
    </table>

    <div class="separator"></div>

    @foreach($donnees as $bloc)
    <div class="section-titre">{{ $bloc['label'] }} ({{ $bloc['table'] }})</div>
    <div class="section-meta">
        {{ $bloc['total'] }} ligne(s) au total
        @if($bloc['tronque'])
        — affichage limité aux 500 premières
        @endif
    </div>

    <table class="donnees">
        <thead>
            <tr>
                @foreach($bloc['colonnes'] as $col)
                <th>{{ $col }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($bloc['lignes'] as $ligne)
            <tr>
                @foreach($bloc['colonnes'] as $col)
                <td>{{ \Illuminate\Support\Str::limit((string) ($ligne->$col ?? ''), 40) }}</td>
                @endforeach
            </tr>
            @empty
            <tr>
                <td colspan="{{ count($bloc['colonnes']) }}" style="text-align:center;color:#999;">
                    Aucune donnée
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($bloc['tronque'])
    <div class="tronque-note">
        ⚠ Cette table contient plus de lignes que ce PDF n'en affiche. Utilisez un export SQL ou CSV pour une sauvegarde complète.
    </div>
    @endif
    @endforeach

    <div class="footer">
        Export généré automatiquement — {{ $parametres->nom_restaurant ?? config('app.name', 'RESTAURANT') }}
    </div>

</body>
</html>