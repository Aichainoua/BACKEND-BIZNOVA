
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport prévisionnel</title>

    <style>
        @page {
            margin: 30px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #263238;
        }

        h1 {
            font-size: 22px;
            margin-bottom: 5px;
        }

        h2 {
            font-size: 14px;
            margin-top: 25px;
            border-bottom: 1px solid #cccccc;
            padding-bottom: 6px;
        }

        .subtitle {
            color: #666666;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #dddddd;
            padding: 7px;
            text-align: left;
        }

        th {
            background-color: #eeeeee;
        }

        .number {
            text-align: right;
        }

        .warning {
            margin-bottom: 5px;
            color: #9a5b00;
        }

        .footer {
            margin-top: 30px;
            font-size: 9px;
            color: #777777;
        }
    </style>
</head>

<body>

    <h1>Rapport prévisionnel</h1>

    <div class="subtitle">
        Projet : {{ $project->name }}<br>
        Devise : {{ $project->currency }}<br>
        Date de génération : {{ $generatedAt->format('d/m/Y H:i') }}<br>
        Période : {{ \Carbon\Carbon::parse($periodStart)->format('d/m/Y') }}
        au
        {{ \Carbon\Carbon::parse($periodEnd)->format('d/m/Y') }}
    </div>

    <h2>1. Synthèse financière</h2>

    <table>
        <tr>
            <th>Indicateur</th>
            <th class="number">Valeur</th>
        </tr>

        <tr>
            <td>Investissement initial</td>
            <td class="number">{{ number_format($analysis['summary']['initial_investment'], 2, ',', ' ') }} {{ $project->currency }}</td>
        </tr>

        <tr>
            <td>Chiffre d'affaires prévisionnel</td>
            <td class="number">{{ number_format($analysis['summary']['total_revenue'], 2, ',', ' ') }} {{ $project->currency }}</td>
        </tr>

        <tr>
            <td>Total des charges</td>
            <td class="number">{{ number_format($analysis['summary']['total_expenses'], 2, ',', ' ') }} {{ $project->currency }}</td>
        </tr>

        <tr>
            <td>Résultat d'exploitation</td>
            <td class="number">{{ number_format($analysis['summary']['total_operating_result'], 2, ',', ' ') }} {{ $project->devise }}</td>
        </tr>

        <tr>
            <td>Marge d'exploitation</td>
            <td class="number">
                {{ $analysis['summary']['operating_margin_percentage'] !== null
                    ? number_format($analysis['summary']['operating_margin_percentage'], 2, ',', ' ') . ' %'
                    : 'Non calculable' }}
            </td>
        </tr>

        <tr>
            <td>ROI net</td>
            <td class="number">
                {{ $analysis['summary']['roi_percentage'] !== null
                    ? number_format($analysis['summary']['roi_percentage'], 2, ',', ' ') . ' %'
                    : 'Non calculable' }}
            </td>
        </tr>

        <tr>
            <td>Seuil de rentabilité mensuel</td>
            <td class="number">
                {{ $analysis['summary']['break_even_monthly_revenue'] !== null
                    ? number_format($analysis['summary']['break_even_monthly_revenue'], 2, ',', ' ') . ' ' . $project->currency
                    : 'Non calculable' }}
            </td>
        </tr>

        <tr>
            <td>Délai de récupération</td>
            <td class="number">
                {{ $analysis['summary']['payback_month'] !== null
                    ? 'Mois ' . $analysis['summary']['payback_month']
                    : 'Non atteint' }}
            </td>
        </tr>
    </table>

    <h2>2. Prévisions mensuelles</h2>

    <table>
        <thead>
            <tr>
                <th>Mois</th>
                <th class="number">Chiffre d'affaires</th>
                <th class="number">Charges</th>
                <th class="number">Résultat</th>
                <th class="number">Cumul simplifié</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($analysis['monthly_forecast'] as $forecast)
                <tr>
                    <td>{{ $forecast['month'] }}</td>
                    <td class="number">{{ number_format($forecast['revenue'], 2, ',', ' ') }}</td>
                    <td class="number">{{ number_format($forecast['total_expenses'], 2, ',', ' ') }}</td>
                    <td class="number">{{ number_format($forecast['operating_result'], 2, ',', ' ') }}</td>
                    <td class="number">{{ number_format($forecast['cumulative_cash_flow'], 2, ',', ' ') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if (count($analysis['warnings']) > 0)
        <h2>3. Points de vigilance</h2>

        @foreach ($analysis['warnings'] as $warning)
            <p class="warning">• {{ $warning }}</p>
        @endforeach
    @endif

    <h2>4. Méthodologie et limites</h2>

    <p>
        Les prévisions sont calculées à partir des hypothèses saisies
        par l'utilisateur : investissement initial, lignes d'activité,
        recettes, coûts directs et charges.
    </p>

    <p>
        Les résultats présentés sont des estimations. Le calcul actuel
        ne prend pas encore en compte les remboursements d'emprunt,
        les impôts, les amortissements ni le besoin en fonds de roulement.
        La croissance des abonnements suppose l'absence de résiliation.
    </p>

    <div class="footer">
        Rapport généré automatiquement par Biznova.
    </div>

</body>
</html>
