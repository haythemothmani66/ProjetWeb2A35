<?php $BO = '/gestion_users/view/backoffice/src'; ?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Back Office - Statistiques Cours | EduMatch Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800&display=swap" />
    <link rel="stylesheet" href="<?= $BO ?>/assets/css/theme.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simplebar@6.2.5/dist/simplebar.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.47.0/tabler-icons.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <script src="<?= $BO ?>/assets/js/vendors/color-modes.js"></script>
    <script>
        if (localStorage.getItem('sidebarExpanded') === 'false') { document.documentElement.classList.add('collapsed'); document.documentElement.classList.remove('expanded'); }
        else { document.documentElement.classList.remove('collapsed'); document.documentElement.classList.add('expanded'); }
    </script>
    <style>

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 26px;
        }
        .page-header h1 {
            font-size: 28px;
            font-weight: 800;
            margin: 0;
            color: #0f172a;
        }
        .page-header p {
            margin: 6px 0 0;
            color: #64748b;
            font-weight: 600;
        }
        .btn {
            padding: 12px 18px;
            border-radius: 10px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 800;
            transition: 0.2s ease;
            cursor: pointer;
            border: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, #4f6bff, #2dd4bf);
            color: #fff;
            box-shadow: 0 14px 28px rgba(79, 107, 255, 0.22);
        }
        .btn-primary:hover {
            transform: translateY(-1px);
            filter: brightness(1.02);
        }
        .btn-ghost {
            background: rgba(79, 107, 255, 0.1);
            color: #22305c;
        }
        .btn-ghost:hover {
            background: rgba(79, 107, 255, 0.16);
            transform: translateY(-1px);
        }
        .stats-grid {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 18px;
        }
        .card {
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 22px;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
            padding: 20px 22px;
        }
        .card h2 {
            margin: 0 0 14px;
            font-size: 16px;
            color: #0f172a;
            letter-spacing: 0.01em;
        }
        .kpis {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin-top: 14px;
        }
        .kpi {
            border-radius: 18px;
            padding: 14px 14px;
            background: rgba(79, 107, 255, 0.08);
            border: 1px solid rgba(79, 107, 255, 0.12);
        }
        .kpi small {
            display: block;
            color: #64748b;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            font-size: 11px;
        }
        .kpi strong {
            display: block;
            margin-top: 6px;
            font-size: 18px;
            color: #0f172a;
        }
        .kpi span {
            display: block;
            margin-top: 4px;
            color: #334155;
            font-weight: 800;
            font-size: 12px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th,
        .table td {
            padding: 12px 10px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.18);
            text-align: left;
            vertical-align: middle;
        }
        .table th {
            font-size: 12px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #64748b;
        }
        .pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 10px;
            border-radius: 999px;
            font-weight: 900;
            font-size: 12px;
            background: rgba(45, 212, 191, 0.12);
            color: #0f766e;
            border: 1px solid rgba(45, 212, 191, 0.22);
        }
        .pill-muted {
            background: rgba(100, 116, 139, 0.1);
            color: #334155;
            border-color: rgba(100, 116, 139, 0.18);
        }
        .pill-danger {
            background: rgba(239, 68, 68, 0.12);
            color: #b91c1c;
            border-color: rgba(239, 68, 68, 0.18);
        }
        .course-link {
            color: #22305c;
            font-weight: 800;
            text-decoration: none;
        }
        .course-link:hover {
            text-decoration: underline;
        }
        @media (max-width: 1050px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .kpis {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
    
    </style>
</head>
<body>
    <div>
        <?php include __DIR__ . '/../../../../partials_php/sidebar.php'; ?>
        <div id="content" class="position-relative h-100">
            <?php include __DIR__ . '/../../../../partials_php/topbar.php'; ?>
            <div class="custom-container">

                <div class="row mb-6 g-6 align-items-end">
                    <div class="col-lg-8">
                        <p class="text-uppercase text-secondary small mb-2">Module Quiz</p>
                        <h1 class="mb-0">Course Statistics</h1>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <a href="<?= htmlspecialchars(backofficeRoute('courses', 'index')); ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back to Courses</a>
                    </div>
                </div>

            <div class="content">
                <div class="page-header">
                    <div>
                       
                       
                    </div>
                    <div style="display:flex; gap:10px; flex-wrap:wrap;">
                        <a href="<?= htmlspecialchars(backofficeRoute('courses', 'index')); ?>" class="btn btn-ghost">
                            <i class="fa-solid fa-arrow-left"></i> Back to Courses
                        </a>
                        <a href="#top-quizzes" class="btn btn-primary">
                            <i class="fa-solid fa-trophy"></i> Top Quiz Courses
                        </a>
                    </div>
                </div>

                <div class="stats-grid" id="levels">
                    <div class="card">
                        <h2>Courses by Level</h2>
                        <canvas id="levelChart" height="150"></canvas>

                        <div class="kpis">
                            <?php foreach (['beginner','intermediate','advanced','other'] as $key): ?>
                                <?php
                                    $count = (int) ($levelCounts[$key] ?? 0);
                                    $percent = (float) ($levelPercents[$key] ?? 0);
                                ?>
                                <div class="kpi">
                                    <small><?= htmlspecialchars($levelLabels[$key] ?? ucfirst($key)); ?></small>
                                    <strong><?= $count; ?></strong>
                                    <span><?= $percent; ?>%</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="card" id="top-quizzes">
                        <h2>Courses with Most Quizzes</h2>

                        <?php if (empty($topQuizCourses)): ?>
                            <div style="padding: 18px 4px; color: #64748b; font-weight: 700;">
                                No courses found.
                            </div>
                        <?php else: ?>
                            <table class="table" aria-label="Top quiz courses">
                                <thead>
                                    <tr>
                                        <th>Course</th>
                                        <th>Level</th>
                                        <th>Status</th>
                                        <th style="text-align:right;">Quizzes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topQuizCourses as $row): ?>
                                        <?php
                                            $status = strtolower((string) ($row['status'] ?? 'draft'));
                                            $statusClass = $status === 'published' ? 'pill' : 'pill pill-muted';
                                            $quizCount = (int) ($row['quiz_count'] ?? 0);
                                            $levelKey = strtolower((string) ($row['level'] ?? 'beginner'));
                                            $levelText = $levelLabels[$levelKey] ?? ucfirst($levelKey);
                                        ?>
                                        <tr>
                                            <td>
                                                <a class="course-link" href="<?= htmlspecialchars(backofficeRoute('courses', 'edit', ['id' => (int) ($row['id'] ?? 0)])); ?>">
                                                    <?= htmlspecialchars((string) ($row['title'] ?? '')); ?>
                                                </a>
                                            </td>
                                            <td><span class="pill pill-muted"><?= htmlspecialchars($levelText); ?></span></td>
                                            <td><span class="<?= htmlspecialchars($statusClass); ?>"><?= htmlspecialchars($status); ?></span></td>
                                            <td style="text-align:right;">
                                                <span class="<?= $quizCount === 0 ? 'pill pill-danger' : 'pill'; ?>">
                                                    <?= $quizCount; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>

            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simplebar@6.2.5/dist/simplebar.min.js"></script>
    <script src="<?= $BO ?>/assets/js/main.js"></script>
    <script src="<?= $BO ?>/assets/js/vendors/sidebarnav.js"></script>
<script>
        (function () {
            const ctx = document.getElementById('levelChart');
            if (!ctx || typeof Chart === 'undefined') return;

            const data = {
                labels: <?= json_encode([
                    $levelLabels['beginner'],
                    $levelLabels['intermediate'],
                    $levelLabels['advanced'],
                    $levelLabels['other'],
                ]); ?>,
                datasets: [{
                    data: <?= json_encode([
                        (int) ($levelCounts['beginner'] ?? 0),
                        (int) ($levelCounts['intermediate'] ?? 0),
                        (int) ($levelCounts['advanced'] ?? 0),
                        (int) ($levelCounts['other'] ?? 0),
                    ]); ?>,
                    backgroundColor: ['#4f6bff', '#2dd4bf', '#f59e0b', '#94a3b8'],
                    borderWidth: 0
                }]
            };

            new Chart(ctx, {
                type: 'pie',
                data,
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 8,
                                color: '#334155',
                                font: { family: 'DM Sans', weight: '700' }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((sum, val) => sum + val, 0) || 1;
                                    const value = context.parsed || 0;
                                    const percent = Math.round((value / total) * 1000) / 10;
                                    return ` ${context.label}: ${value} (${percent}%)`;
                                }
                            }
                        }
                    }
                }
            });
        })();
    </script>

</body>
</html>