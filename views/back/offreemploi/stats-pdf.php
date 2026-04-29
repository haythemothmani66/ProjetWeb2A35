<?php
$stats = $stats ?? [];
$expiringOffers = $expiringOffers ?? [];
$pendingReplies = $pendingReplies ?? [];
$exportDate = $exportDate ?? date('d/m/Y H:i:s');

$html = '
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Statistiques EduMatch</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: white;
            color: #333;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        h1 {
            text-align: center;
            border-bottom: 3px solid #0f6e8b;
            padding-bottom: 12px;
            margin-bottom: 8px;
            font-size: 24px;
        }
        .export-info {
            text-align: center;
            color: #666;
            font-size: 12px;
            margin-bottom: 20px;
        }
        .metrics-row {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .metric-card {
            display: table-cell;
            width: 25%;
            padding: 15px;
            border: 1px solid #ddd;
            background: #f9fafb;
        }
        .metric-card:nth-child(odd) {
            border-right: none;
        }
        .metric-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 8px;
            font-weight: bold;
        }
        .metric-value {
            font-size: 32px;
            font-weight: bold;
            color: #0f6e8b;
            margin-bottom: 6px;
        }
        .metric-detail {
            font-size: 11px;
            color: #999;
        }
        section {
            margin-bottom: 24px;
            page-break-inside: avoid;
        }
        h2 {
            font-size: 16px;
            border-left: 4px solid #0f6e8b;
            padding-left: 10px;
            margin: 16px 0 12px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background: #0f6e8b;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 12px;
            font-weight: bold;
        }
        td {
            padding: 8px 10px;
            border-bottom: 1px solid #eee;
            font-size: 12px;
        }
        tr:nth-child(even) {
            background: #f9fafb;
        }
        .alert {
            padding: 12px;
            margin: 10px 0;
            border-left: 4px solid;
            background: #f5f5f5;
            font-size: 12px;
        }
        .alert-success {
            border-color: #10b981;
            color: #047857;
        }
        .alert-warning {
            border-color: #f59e0b;
            color: #92400e;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }
        .badge-danger {
            background: #fee2e2;
            color: #b91c1c;
        }
        .footer {
            border-top: 1px solid #ddd;
            padding-top: 10px;
            margin-top: 20px;
            font-size: 11px;
            color: #999;
            text-align: center;
        }
        @media print {
            body { margin: 0; padding: 10px; }
            .container { max-width: 100%; }
            section { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>EduMatch - Tableau de bord statistiques</h1>
        <div class="export-info">Généré le ' . htmlspecialchars((string) $exportDate) . '</div>

        <section>
            <div class="metrics-row">
                <div class="metric-card">
                    <div class="metric-label">Offres totales</div>
                    <div class="metric-value">' . (int) $stats['offers']['total'] . '</div>
                    <div class="metric-detail">' . (int) $stats['offers']['open'] . ' ouvertes · ' . (int) $stats['offers']['closed'] . ' fermées</div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Candidatures totales</div>
                    <div class="metric-value">' . (int) $stats['candidatures']['total'] . '</div>
                    <div class="metric-detail">' . (int) $stats['candidatures']['pending'] . ' en attente</div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Réponses traitées</div>
                    <div class="metric-value">' . ((int) $stats['candidatures']['accepted'] + (int) $stats['candidatures']['refused']) . '</div>
                    <div class="metric-detail">' . (int) $stats['candidatures']['accepted'] . ' acceptées · ' . (int) $stats['candidatures']['refused'] . ' refusées</div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Alertes</div>
                    <div class="metric-value">' . ((int) $stats['alerts']['expiring_count'] + (int) $stats['alerts']['pending_count']) . '</div>
                    <div class="metric-detail">' . (int) $stats['alerts']['expiring_count'] . ' offres · ' . (int) $stats['alerts']['pending_count'] . ' réponses</div>
                </div>
            </div>
        </section>

        <section>
            <h2>Offres expirant bientôt</h2>
            ' . (empty($expiringOffers) ? '<div class="alert alert-success">Aucune offre ne s\'approche de l\'échéance immédiate.</div>' : '
            <table>
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Lieu</th>
                        <th>Date limite</th>
                        <th>Jours restants</th>
                    </tr>
                </thead>
                <tbody>
                    ' . implode('', array_map(static function (array $offer): string {
    $daysClass = ((int) $offer['days_left'] <= 3) ? 'badge-danger' : 'badge-warning';
    return '
                    <tr>
                        <td>' . htmlspecialchars((string) $offer['titre']) . '</td>
                        <td>' . htmlspecialchars((string) $offer['lieu']) . '</td>
                        <td>' . htmlspecialchars((string) $offer['datelimite']) . '</td>
                        <td><span class="badge ' . $daysClass . '">' . (int) $offer['days_left'] . ' jour(s)</span></td>
                    </tr>
                    ';
}, $expiringOffers)) . '
                </tbody>
            </table>
            ') . '
        </section>

        <section>
            <h2>Candidatures en attente de réponse</h2>
            ' . (empty($pendingReplies) ? '<div class="alert alert-success">Aucune candidature n\'attend une réponse.</div>' : '
            <table>
                <thead>
                    <tr>
                        <th>Candidat</th>
                        <th>Offre</th>
                        <th>Date de candidature</th>
                    </tr>
                </thead>
                <tbody>
                    ' . implode('', array_map(static function (array $reply): string {
    return '
                    <tr>
                        <td>' . htmlspecialchars(trim((string) $reply['prenom'] . ' ' . (string) $reply['nom'])) . '</td>
                        <td>' . htmlspecialchars((string) ($reply['offre_titre'] ?? 'Offre inconnue')) . '</td>
                        <td>' . htmlspecialchars((string) $reply['datecandidature']) . '</td>
                    </tr>
                    ';
}, $pendingReplies)) . '
                </tbody>
            </table>
            ') . '
        </section>

        <div class="footer">
            <p>Ce rapport a été généré automatiquement par EduMatch.</p>
        </div>
    </div>
</body>
</html>
';

header('Content-Type: text/html; charset=utf-8');
header('Content-Disposition: attachment; filename="stats-recruitment-' . date('Y-m-d') . '.html"');
echo $html;
exit;
