<?php $BO = '/gestion_users/view/backoffice/src'; ?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Back Office - Cours Quiz | EduMatch Admin</title>
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
            margin-bottom: 30px;
        }
        .page-header h1 {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
        }
        .btn {
            padding: 12px 24px;
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
        .btn-primary {
            background: #00d4ff;
            color: white;
        }
        .btn-primary:hover {
            background: #00b8d4;
        }
        .btn-secondary {
            background: rgba(79, 107, 255, 0.12);
            color: #22305c;
            border: 1px solid rgba(79, 107, 255, 0.18);
        }
        .btn-secondary:hover {
            background: rgba(79, 107, 255, 0.18);
        }
        .btn-ai {
            background: linear-gradient(135deg, #8b5cf6, #6366f1);
            color: white;
            box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);
            border: none;
        }
        .btn-ai:hover {
            background: linear-gradient(135deg, #7c3aed, #4f46e5);
            box-shadow: 0 6px 20px rgba(139, 92, 246, 0.4);
            transform: translateY(-2px);
            color: white;
        }
        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }
        .course-card {
            background: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }
        .course-card-image {
            width: 100%;
            height: 150px;
            border-radius: 6px;
            object-fit: cover;
            margin-bottom: 15px;
        }
        .course-card-image-placeholder {
            width: 100%;
            height: 150px;
            border-radius: 6px;
            background: linear-gradient(135deg, #525fe1 0%, #1e293b 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 36px;
            margin-bottom: 15px;
        }
        .course-card-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }
        .course-card-title {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
            margin: 0 0 10px 0;
            line-height: 1.4;
        }
        .course-badges {
            display: flex;
            gap: 8px;
            margin-bottom: 15px;
        }
        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-level {
            background: #e3f2fd;
            color: #1976d2;
        }
        .badge-status {
            background: #f3e5f5;
            color: #7b1fa2;
        }
        .badge-published {
            background: #e8f5e9;
            color: #388e3c;
        }
        .badge-draft {
            background: #fff3e0;
            color: #f57c00;
        }
        .course-card-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #e0e0e0;
        }
        .action-btn {
            flex: 1 1 calc(50% - 4px);
            padding: 10px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            text-align: center;
            transition: 0.3s;
            border: none;
            cursor: pointer;
        }
        .action-btn-edit {
            background: #e3f2fd;
            color: #1976d2;
        }
        .action-btn-edit:hover {
            background: #bbdefb;
        }
        .action-btn-quizzes {
            background: #f3e5f5;
            color: #7b1fa2;
        }
        .action-btn-quizzes:hover {
            background: #e1bee7;
        }
        .action-btn-lessons {
            background: #eef2ff;
            color: #4338ca;
        }
        .action-btn-lessons:hover {
            background: #e0e7ff;
        }
        .action-btn-delete {
            background: #ffebee;
            color: #c62828;
        }
        .action-btn-delete:hover {
            background: #ffcdd2;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 8px;
        }
        .empty-state i {
            font-size: 48px;
            color: #ccc;
            margin-bottom: 20px;
        }
        .empty-state h3 {
            font-size: 20px;
            color: #1e293b;
            margin-bottom: 10px;
        }
        .empty-state p {
            color: #666;
            margin-bottom: 20px;
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
                        <h1 class="mb-0">Manage Courses</h1>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <a href="<?= htmlspecialchars(backofficeRoute('courses', 'stats')); ?>#levels" class="btn btn-secondary me-1 mb-1">
                            <i class="fas fa-chart-pie"></i> Level Stats
                        </a>
                        <a href="<?= htmlspecialchars(backofficeRoute('courses', 'stats')); ?>#top-quizzes" class="btn btn-secondary me-1 mb-1">
                            <i class="fas fa-trophy"></i> Top Quizzes
                        </a>
                        <a href="<?= htmlspecialchars(backofficeRoute('courses', 'generate')); ?>" class="btn btn-ai me-1 mb-1">
                            <i class="fas fa-wand-magic-sparkles"></i> Generate AI
                        </a>
                        <a href="<?= htmlspecialchars(backofficeRoute('courses', 'create')); ?>" class="btn btn-primary mb-1">
                            <i class="fas fa-plus"></i> New Course
                        </a>
                    </div>
                </div>

                <?php if (empty($courses)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h3>No Courses Yet</h3>
                        <p>Get started by creating your first course</p>
                        <div style="display:flex; gap:10px; justify-content:center; margin-top:20px;">
                            <a href="<?= htmlspecialchars(backofficeRoute('courses', 'generate')); ?>" class="btn btn-ai">
                                <i class="fas fa-wand-magic-sparkles"></i> Generate AI Course
                            </a>
                            <a href="<?= htmlspecialchars(backofficeRoute('courses', 'create')); ?>" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create Course
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="courses-grid">
                        <?php foreach ($courses as $c): ?>
                            <div class="course-card">
                                <?php if (!empty($c['image'])): ?>
                                    <img src="<?= htmlspecialchars((string) $c['image']) ?>" class="course-card-image" alt="<?= htmlspecialchars((string) $c['title']) ?>">
                                <?php else: ?>
                                    <div class="course-card-image-placeholder">
                                        <i class="fas fa-book"></i>
                                    </div>
                                <?php endif; ?>

                                <div class="course-card-header">
                                    <div>
                                        <h3 class="course-card-title"><?= htmlspecialchars((string) $c['title']) ?></h3>
                                    </div>
                                    <div class="course-badges">
                                        <span class="badge badge-level"><?= htmlspecialchars((string) ($c['level'] ?? 'beginner')) ?></span>
                                    </div>
                                </div>

                                <div class="course-badges">
                                    <?php $status = htmlspecialchars((string) ($c['status'] ?? 'draft')); ?>
                                    <span class="badge <?= $status === 'published' ? 'badge-published' : 'badge-draft' ?>">
                                        <?= $status ?>
                                    </span>
                                </div>

                                <p style="font-size: 14px; color: #666; margin-bottom: 15px; line-height: 1.5;">
                                    <?= htmlspecialchars(substr((string) ($c['description'] ?? 'No description'), 0, 100)) ?>
                                    <?= strlen($c['description'] ?? '') > 100 ? '...' : '' ?>
                                </p>

                                <div class="course-card-actions">
                                    <a href="<?= htmlspecialchars(backofficeRoute('courses', 'edit', ['id' => (int) $c['id']])); ?>" class="action-btn action-btn-edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="<?= htmlspecialchars(backofficeRoute('lessons', 'index', ['course_id' => (int) $c['id']])); ?>" class="action-btn action-btn-lessons">
                                        <i class="fas fa-road"></i> Lessons
                                    </a>
                                    <a href="<?= htmlspecialchars(backofficeRoute('courses', 'quizzes', ['course_id' => (int) $c['id']])); ?>" class="action-btn action-btn-quizzes">
                                        <i class="fas fa-question-circle"></i> Quizzes
                                    </a>
                                    <a href="<?= htmlspecialchars(backofficeRoute('courses', 'delete', ['id' => (int) $c['id']])); ?>" class="action-btn action-btn-delete" onclick="return confirm('Are you sure? This action cannot be undone.')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simplebar@6.2.5/dist/simplebar.min.js"></script>
    <script src="<?= $BO ?>/assets/js/main.js"></script>
    <script src="<?= $BO ?>/assets/js/vendors/sidebarnav.js"></script>
</body>
</html>
