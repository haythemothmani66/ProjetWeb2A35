<?php $BO = '/gestion_users/view/backoffice/src'; ?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Back Office - Dashboard Quiz | EduMatch Admin</title>
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
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .stat-icon {
            width: 60px;
            height: 60px;
            background: #f0f4ff;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: #00d4ff;
        }
        .stat-info h3 {
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 5px 0;
        }
        .stat-info p {
            color: #666;
            margin: 0;
            font-size: 14px;
        }
        .quick-actions {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .quick-actions h2 {
            margin: 0 0 20px 0;
            font-size: 20px;
        }
        .action-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        .btn-quiz {
            padding: 12px 20px;
            border-radius: 6px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            transition: 0.3s;
            cursor: pointer;
            border: none;
        }
        .btn-quiz-primary { background: #00d4ff; color: white; }
        .btn-quiz-primary:hover { background: #00b8d4; color: white; }
        .btn-quiz-secondary { background: #0b104a; color: white; }
        .btn-quiz-secondary:hover { background: #0a0a35; color: white; }
        .btn-quiz-outline { background: transparent; color: #0b104a; border: 2px solid #0b104a; }
        .btn-quiz-outline:hover { background: #f0f4ff; color: #0b104a; }
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
                        <h1 class="mb-0">Tableau de bord</h1>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <a class="btn btn-outline-secondary me-2" href="<?= htmlspecialchars(frontofficeRoute('courses', 'index')); ?>">
                            <i class="ti ti-external-link me-1"></i> Voir le front office
                        </a>
                    </div>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= htmlspecialchars($stats['totalCourses'] ?? 0); ?></h3>
                            <p>Total Courses</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= htmlspecialchars($stats['publishedCourses'] ?? 0); ?></h3>
                            <p>Published Courses</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= htmlspecialchars($stats['totalQuizzes'] ?? 0); ?></h3>
                            <p>Total Quizzes</p>
                        </div>
                    </div>
                </div>

                <div class="quick-actions">
                    <h2>Quick Actions</h2>
                    <div class="action-buttons">
                        <a href="<?= htmlspecialchars(backofficeRoute('courses', 'create')); ?>" class="btn-quiz btn-quiz-primary">
                            <i class="fas fa-plus"></i> Add Course
                        </a>
                        <a href="<?= htmlspecialchars(backofficeRoute('quizzes', 'create')); ?>" class="btn-quiz btn-quiz-secondary">
                            <i class="fas fa-plus"></i> Add Quiz
                        </a>
                        <a href="<?= htmlspecialchars(frontofficeRoute('courses', 'index')); ?>" class="btn-quiz btn-quiz-outline">
                            <i class="fas fa-eye"></i> View Frontoffice
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simplebar@6.2.5/dist/simplebar.min.js"></script>
    <script src="<?= $BO ?>/assets/js/main.js"></script>
    <script src="<?= $BO ?>/assets/js/vendors/sidebarnav.js"></script>
</body>
</html>
