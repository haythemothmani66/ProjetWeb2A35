<?php $BO = '/gestion_users/view/backoffice/src'; ?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Back Office - Quizzes | EduMatch Admin</title>
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
        .course-info {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 20px;
            background: white;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .course-image {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            object-fit: cover;
        }
        .course-image-placeholder {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            background: linear-gradient(135deg, #525fe1 0%, #1e293b 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }
        .course-details h2 {
            font-size: 20px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 5px;
        }
        .course-details p {
            color: #666;
            font-size: 14px;
        }
        .breadcrumb {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
        .breadcrumb a {
            color: #00d4ff;
            text-decoration: none;
        }
        .breadcrumb a:hover {
            text-decoration: underline;
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
        .quizzes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }
        .quiz-card {
            background: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border-left: 4px solid #00d4ff;
        }
        .quiz-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }
        .quiz-card-title {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 15px;
            line-height: 1.4;
        }
        .quiz-meta {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e0e0e0;
        }
        .quiz-meta-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: #666;
        }
        .quiz-meta-item i {
            color: #00d4ff;
            width: 18px;
            text-align: center;
        }
        .quiz-card-actions {
            display: flex;
            gap: 8px;
        }
        .action-btn {
            flex: 1;
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
        .action-btn-questions {
            background: #eef2ff;
            color: #4338ca;
        }
        .action-btn-questions:hover {
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
                        <h1 class="mb-0">Quizzes</h1>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <a href="<?= htmlspecialchars(backofficeRoute('quizzes', 'create')); ?>" class="btn btn-primary"><i class="fas fa-plus me-1"></i> New Quiz</a>
                    </div>
                </div>

            <div class="content">
                <div class="page-header">
                    <div>
                        <h1 style="font-size: 28px;">Quizzes</h1>
                        <div class="breadcrumb">
                            <a href="<?= htmlspecialchars(backofficeRoute('courses', 'index')); ?>">Back to Courses</a>
                        </div>
                    </div>
                    <a href="<?= htmlspecialchars(backofficeRoute('quizzes', 'create', ['course_id' => (int) $course['id']])); ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> New Quiz
                    </a>
                </div>

                <div class="course-info">
                    <?php if (!empty($course['image'])): ?>
                        <img src="<?= htmlspecialchars((string) $course['image']) ?>" class="course-image" alt="<?= htmlspecialchars((string) $course['title']) ?>">
                    <?php else: ?>
                        <div class="course-image-placeholder">
                            <i class="fas fa-book"></i>
                        </div>
                    <?php endif; ?>
                    <div class="course-details">
                        <h2><?= htmlspecialchars((string) $course['title']) ?></h2>
                        <p><?= htmlspecialchars((string) ($course['description'] ?? 'No description')) ?></p>
                    </div>
                </div>

                <?php if (empty($quizzes)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h3>No Quizzes Yet</h3>
                        <p>Create your first quiz for this course</p>
                        <a href="<?= htmlspecialchars(backofficeRoute('quizzes', 'create', ['course_id' => (int) $course['id']])); ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create Quiz
                        </a>
                    </div>
                <?php else: ?>
                    <div class="quizzes-grid">
                        <?php foreach ($quizzes as $q): ?>
                            <div class="quiz-card">
                                <h3 class="quiz-card-title"><?= htmlspecialchars((string) $q['title']) ?></h3>
                                
                                <div class="quiz-meta">
                                    <div class="quiz-meta-item">
                                        <i class="fas fa-clock"></i>
                                        <span><?= (int) ($q['duration_minutes'] ?? 30) ?> minutes</span>
                                    </div>
                                    <div class="quiz-meta-item">
                                        <i class="fas fa-check-circle"></i>
                                        <span><?= htmlspecialchars((string) ($q['passing_score'] ?? 50)) ?>% to pass</span>
                                    </div>
                                    <div class="quiz-meta-item">
                                        <i class="fas fa-repeat"></i>
                                        <span><?= (int) ($q['max_attempts'] ?? 1) ?> attempt<?= ((int) ($q['max_attempts'] ?? 1)) !== 1 ? 's' : '' ?></span>
                                    </div>
                                    <div class="quiz-meta-item">
                                        <i class="fas fa-stopwatch"></i>
                                        <span><?= ((int) ($q['is_timed'] ?? 1)) === 1 ? 'Timed' : 'Untimed' ?></span>
                                    </div>
                                </div>

                                <div class="quiz-card-actions">
                                    <a href="<?= htmlspecialchars(backofficeRoute('quizzes', 'edit', ['id' => (int) $q['id']])); ?>" class="action-btn action-btn-edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="<?= htmlspecialchars(backofficeRoute('questions', 'index', ['quiz_id' => (int) $q['id']])); ?>" class="action-btn action-btn-questions">
                                        <i class="fas fa-list-check"></i> Questions
                                    </a>
                                    <a href="<?= htmlspecialchars(backofficeRoute('quizzes', 'delete', ['id' => (int) $q['id']])); ?>" class="action-btn action-btn-delete" onclick="return confirm('Are you sure? This action cannot be undone.')">
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