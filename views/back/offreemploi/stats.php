<?php
$stats = $stats ?? [
    'offers' => ['total' => 0, 'open' => 0, 'closed' => 0],
    'candidatures' => ['total' => 0, 'pending' => 0, 'accepted' => 0, 'refused' => 0],
    'alerts' => ['expiring_count' => 0, 'pending_count' => 0],
];
$chartData = $chartData ?? [
    'offer_labels' => [],
    'offer_counts' => [],
    'status_labels' => ['En attente', 'Acceptées', 'Refusées'],
    'status_counts' => [0, 0, 0],
];
$expiringOffers = $expiringOffers ?? [];
$pendingReplies = $pendingReplies ?? [];
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Back Office - Statistiques</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="../dasher-1.0.0/src/assets/css/theme.css" />
    <style>
        body { background: #f6f8fc; font-family: 'Public Sans', sans-serif; }
        .emploi-shell { min-height: 100vh; }
        .emploi-sidebar { width: 280px; background: #0f172a; color: #fff; }
        .emploi-sidebar a { color: rgba(255,255,255,.8); text-decoration: none; }
        .emploi-sidebar a:hover, .emploi-sidebar .active { color: #fff; }
        .emploi-main { flex: 1; min-width: 0; }
        .emploi-topbar { background: #fff; border-bottom: 1px solid #e5e7eb; }
        .metric-card { border: 0; box-shadow: 0 10px 30px rgba(15, 23, 42, .06); }
        .section-card { border: 0; box-shadow: 0 10px 30px rgba(15, 23, 42, .06); }
        .chart-wrap { position: relative; min-height: 320px; }
        .small-muted { color: #64748b; font-size: .92rem; }
        @media (max-width: 991.98px) { .emploi-sidebar { width: 100%; } }
    </style>
</head>
<body>
    <div class="emploi-shell d-flex">
        <aside class="emploi-sidebar d-none d-lg-flex flex-column">
            <?php include __DIR__ . '/../partials/brand.php'; ?>
            <?php $activeTab = 'stats'; include __DIR__ . '/../partials/nav.php'; ?>
        </aside>

        <div class="emploi-main">
            <header class="emploi-topbar px-4 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <p class="mb-1 text-secondary small">Back Office</p>
                    <h1 class="h4 mb-0">Tableau de bord des statistiques</h1>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a class="btn btn-outline-primary" href="index.php?espace=back&module=offreemploi&action=exportStatsPdf" target="_blank">Exporter en HTML</a>
                    <a class="btn btn-outline-secondary" href="index.php?espace=back&module=offreemploi&action=liste">Retour aux offres</a>
                    <a class="btn btn-primary" href="index.php?espace=back&module=candidature&action=liste">Voir les candidatures</a>
                </div>
            </header>

            <main class="container-fluid p-4 p-lg-5">
                <div class="row g-4 mb-4">
                    <div class="col-md-6 col-xl-3">
                        <div class="card metric-card h-100">
                            <div class="card-body">
                                <p class="small text-uppercase text-secondary mb-2">Offres totales</p>
                                <div class="d-flex align-items-end justify-content-between">
                                    <h2 class="display-6 mb-0"><?= (int) $stats['offers']['total'] ?></h2>
                                    <span class="badge text-bg-primary">+ dashboard</span>
                                </div>
                                <div class="small-muted mt-2"><?= (int) $stats['offers']['open'] ?> ouvertes · <?= (int) $stats['offers']['closed'] ?> fermées</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="card metric-card h-100">
                            <div class="card-body">
                                <p class="small text-uppercase text-secondary mb-2">Candidatures totales</p>
                                <div class="d-flex align-items-end justify-content-between">
                                    <h2 class="display-6 mb-0"><?= (int) $stats['candidatures']['total'] ?></h2>
                                    <span class="badge text-bg-dark">global</span>
                                </div>
                                <div class="small-muted mt-2"><?= (int) $stats['candidatures']['pending'] ?> en attente</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="card metric-card h-100">
                            <div class="card-body">
                                <p class="small text-uppercase text-secondary mb-2">Réponses traitées</p>
                                <div class="d-flex align-items-end justify-content-between">
                                    <h2 class="display-6 mb-0"><?= (int) $stats['candidatures']['accepted'] + (int) $stats['candidatures']['refused'] ?></h2>
                                    <span class="badge text-bg-success">ok</span>
                                </div>
                                <div class="small-muted mt-2"><?= (int) $stats['candidatures']['accepted'] ?> acceptées · <?= (int) $stats['candidatures']['refused'] ?> refusées</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="card metric-card h-100">
                            <div class="card-body">
                                <p class="small text-uppercase text-secondary mb-2">Alertes</p>
                                <div class="d-flex align-items-end justify-content-between">
                                    <h2 class="display-6 mb-0"><?= (int) $stats['alerts']['expiring_count'] + (int) $stats['alerts']['pending_count'] ?></h2>
                                    <span class="badge text-bg-warning">action</span>
                                </div>
                                <div class="small-muted mt-2"><?= (int) $stats['alerts']['expiring_count'] ?> offres proches de l’échéance · <?= (int) $stats['alerts']['pending_count'] ?> réponses en attente</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-xl-8">
                        <div class="card section-card h-100">
                            <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="h5 mb-1">Candidatures par offre</h3>
                                    <p class="small-muted mb-0">Les offres les plus sollicitées sur le back office.</p>
                                </div>
                            </div>
                            <div class="card-body px-4 pb-4">
                                <div class="chart-wrap">
                                    <canvas id="offersChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="card section-card h-100">
                            <div class="card-header bg-white border-0 pt-4 px-4">
                                <h3 class="h5 mb-1">Répartition des candidatures</h3>
                                <p class="small-muted mb-0">En attente, acceptées, refusées.</p>
                            </div>
                            <div class="card-body px-4 pb-4 d-flex align-items-center justify-content-center">
                                <div class="chart-wrap" style="min-height: 280px; max-width: 320px; width: 100%;">
                                    <canvas id="statusChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-xl-6">
                        <div class="card section-card h-100">
                            <div class="card-header bg-white border-0 pt-4 px-4">
                                <h3 class="h5 mb-1">Offres qui expirent bientôt</h3>
                                <p class="small-muted mb-0">Sur les 14 prochains jours.</p>
                            </div>
                            <div class="card-body px-4 pb-4">
                                <?php if (empty($expiringOffers)): ?>
                                    <div class="alert alert-success mb-0">Aucune offre ne s'approche de l'échéance immédiate.</div>
                                <?php else: ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($expiringOffers as $offer): ?>
                                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                                <div>
                                                    <div class="fw-semibold"><?= htmlspecialchars((string) $offer['titre']) ?></div>
                                                    <div class="small text-secondary"><?= htmlspecialchars((string) $offer['lieu']) ?> · limite le <?= htmlspecialchars((string) $offer['datelimite']) ?></div>
                                                </div>
                                                <span class="badge <?= ((int) $offer['days_left'] <= 3) ? 'text-bg-danger' : 'text-bg-warning' ?>"><?= (int) $offer['days_left'] ?> jour(s)</span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="card section-card h-100">
                            <div class="card-header bg-white border-0 pt-4 px-4">
                                <h3 class="h5 mb-1">Réponses en attente</h3>
                                <p class="small-muted mb-0">Candidatures qui attendent une décision.</p>
                            </div>
                            <div class="card-body px-4 pb-4">
                                <?php if (empty($pendingReplies)): ?>
                                    <div class="alert alert-info mb-0">Aucune candidature n'attend une réponse.</div>
                                <?php else: ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($pendingReplies as $reply): ?>
                                            <a class="list-group-item list-group-item-action px-0" href="index.php?espace=back&module=candidature&action=details&id=<?= (int) $reply['id'] ?>">
                                                <div class="d-flex justify-content-between align-items-start gap-3">
                                                    <div>
                                                        <div class="fw-semibold"><?= htmlspecialchars(trim((string) $reply['prenom'] . ' ' . (string) $reply['nom'])) ?></div>
                                                        <div class="small text-secondary"><?= htmlspecialchars((string) ($reply['offre_titre'] ?? 'Offre inconnue')) ?></div>
                                                    </div>
                                                    <span class="badge text-bg-secondary"><?= htmlspecialchars((string) $reply['datecandidature']) ?></span>
                                                </div>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        const offerLabels = <?= json_encode(array_values($chartData['offer_labels']), JSON_UNESCAPED_UNICODE) ?>;
        const offerCounts = <?= json_encode(array_values($chartData['offer_counts']), JSON_UNESCAPED_UNICODE) ?>;
        const statusLabels = <?= json_encode(array_values($chartData['status_labels']), JSON_UNESCAPED_UNICODE) ?>;
        const statusCounts = <?= json_encode(array_values($chartData['status_counts']), JSON_UNESCAPED_UNICODE) ?>;

        const offersCanvas = document.getElementById('offersChart');
        if (offersCanvas) {
            new Chart(offersCanvas, {
                type: 'bar',
                data: {
                    labels: offerLabels,
                    datasets: [{
                        label: 'Candidatures',
                        data: offerCounts,
                        backgroundColor: '#0f6e8b',
                        borderRadius: 10,
                        maxBarThickness: 44,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                    },
                    scales: {
                        x: {
                            ticks: { color: '#475569' },
                            grid: { display: false },
                        },
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0, color: '#475569' },
                            grid: { color: 'rgba(148,163,184,.18)' },
                        },
                    },
                },
            });
        }

        const statusCanvas = document.getElementById('statusChart');
        if (statusCanvas) {
            new Chart(statusCanvas, {
                type: 'doughnut',
                data: {
                    labels: statusLabels,
                    datasets: [{
                        data: statusCounts,
                        backgroundColor: ['#f59e0b', '#10b981', '#ef4444'],
                        borderWidth: 0,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { boxWidth: 14, usePointStyle: true },
                        },
                    },
                },
            });
        }
    </script>
</body>
</html>