<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lessons - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Jost:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/backoffice.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DM Sans', sans-serif; background: #f5f7fc; color: #1e293b; }
        .page-header { display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap; margin-bottom: 30px; }
        .page-header h1 { font-size: 28px; font-weight: 700; color: #1e293b; }
        .page-header p { color: #666; margin-top: 5px; }
        .header-actions { display: flex; gap: 12px; flex-wrap: wrap; }
        .btn { padding: 12px 22px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-weight: 600; transition: 0.3s; cursor: pointer; border: none; }
        .btn-primary { background: #00d4ff; color: white; }
        .btn-primary:hover { background: #00b8d4; }
        .btn-secondary { background: white; color: #525fe1; border: 1px solid #d7dcf5; }
        .btn-secondary:hover { background: #f6f8ff; }
        .course-info { display: grid; grid-template-columns: minmax(0, 1.6fr) minmax(260px, 1fr); gap: 24px; margin-bottom: 30px; }
        .info-card, .stats-card, .lesson-card, .empty-state { background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(15, 23, 42, 0.08); }
        .info-card, .stats-card { padding: 24px; }
        .info-card h2, .stats-card h3 { color: #1e293b; margin-bottom: 12px; }
        .info-card p { color: #64748b; line-height: 1.7; }
        .meta-badges { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 18px; }
        .badge { display: inline-flex; align-items: center; gap: 6px; padding: 8px 12px; border-radius: 999px; font-size: 13px; font-weight: 700; }
        .badge-level { background: #eef2ff; color: #4338ca; }
        .badge-status { background: #ecfdf5; color: #047857; }
        .badge-count { background: #fff7ed; color: #c2410c; }
        .stats-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .stat-box { padding: 16px; border-radius: 10px; background: #f8fbff; }
        .stat-box strong { display: block; font-size: 26px; color: #0b104a; margin-bottom: 6px; }
        .stat-box span { color: #64748b; font-size: 14px; }
        .lessons-grid { display: grid; gap: 20px; }
        .lesson-card { padding: 24px; border-left: 4px solid #525fe1; }
        .lesson-top { display: flex; justify-content: space-between; align-items: start; gap: 18px; flex-wrap: wrap; margin-bottom: 16px; }
        .lesson-title { font-size: 21px; font-weight: 700; color: #1e293b; margin-bottom: 8px; }
        .lesson-summary { color: #64748b; line-height: 1.7; }
        .lesson-order { display: inline-flex; align-items: center; justify-content: center; min-width: 44px; height: 44px; border-radius: 12px; background: #eef2ff; color: #4338ca; font-weight: 800; }
        .lesson-meta { display: flex; gap: 12px; flex-wrap: wrap; margin: 16px 0 20px; }
        .lesson-meta span { display: inline-flex; align-items: center; gap: 6px; padding: 8px 12px; border-radius: 999px; font-size: 13px; background: #f8fafc; color: #475569; }
        .lesson-actions { display: flex; gap: 10px; flex-wrap: wrap; }
        .action-btn { padding: 10px 14px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 600; }
        .action-btn-edit { background: #e0f2fe; color: #0369a1; }
        .action-btn-edit:hover { background: #bae6fd; }
        .action-btn-delete { background: #fee2e2; color: #b91c1c; }
        .action-btn-delete:hover { background: #fecaca; }
        .action-btn-view { background: #ecfdf5; color: #047857; }
        .action-btn-view:hover { background: #d1fae5; }
        .empty-state { padding: 60px 24px; text-align: center; }
        .empty-state i { font-size: 48px; color: #cbd5e1; margin-bottom: 18px; }
        .empty-state h3 { font-size: 22px; color: #1e293b; margin-bottom: 10px; }
        .empty-state p { color: #64748b; margin-bottom: 22px; }
        @media (max-width: 920px) { .course-info { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php
        $sidebarSection = 'courses';
        $sidebarCourseId = isset($course['id']) ? (int) $course['id'] : null;
        require dirname(__DIR__) . '/partials/sidebar.php';
        ?>

        <div class="main-content">
            <header class="top-bar">
                <h1>Lessons</h1>
                <div class="user-profile">
                    <span>Admin User</span>
                </div>
            </header>

            <div class="content">
                <div class="page-header">
                    <div>
                        <h1>Learning Path Lessons</h1>
                        <p>Arrange the lessons in the order learners should follow.</p>
                    </div>
                    <div class="header-actions">
                        <a href="<?= htmlspecialchars(backofficeRoute('courses', 'index')); ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Courses
                        </a>
                        <a href="<?= htmlspecialchars(backofficeRoute('lessons', 'create', ['course_id' => (int) $course['id']])); ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> New Lesson
                        </a>
                    </div>
                </div>

                <div class="course-info">
                    <div class="info-card">
                        <h2><?= htmlspecialchars((string) $course['title']) ?></h2>
                        <p><?= htmlspecialchars((string) ($course['description'] ?? 'No course description available.')) ?></p>
                        <div class="meta-badges">
                            <span class="badge badge-level"><i class="fas fa-layer-group"></i> <?= htmlspecialchars((string) ($course['level'] ?? 'beginner')) ?></span>
                            <span class="badge badge-status"><i class="fas fa-circle-check"></i> <?= htmlspecialchars((string) ($course['status'] ?? 'draft')) ?></span>
                            <span class="badge badge-count"><i class="fas fa-list-ol"></i> <?= count($lessons) ?> lesson<?= count($lessons) !== 1 ? 's' : '' ?></span>
                        </div>
                    </div>

                    <div class="stats-card">
                        <h3>Course Snapshot</h3>
                        <div class="stats-grid">
                            <div class="stat-box">
                                <strong><?= count($lessons) ?></strong>
                                <span>Total Lessons</span>
                            </div>
                            <div class="stat-box">
                                <strong><?= count(array_filter($lessons, static fn (array $lesson): bool => ($lesson['status'] ?? 'draft') === 'published')) ?></strong>
                                <span>Published</span>
                            </div>
                            <div class="stat-box">
                                <strong><?= array_sum(array_map(static fn (array $lesson): int => (int) ($lesson['duration_minutes'] ?? 0), $lessons)) ?></strong>
                                <span>Total Minutes</span>
                            </div>
                            <div class="stat-box">
                                <strong><?= empty($lessons) ? 0 : max(array_map(static fn (array $lesson): int => (int) ($lesson['lesson_order'] ?? 0), $lessons)) ?></strong>
                                <span>Last Step</span>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (empty($lessons)): ?>
                    <div class="empty-state">
                        <i class="fas fa-road"></i>
                        <h3>No Lessons Yet</h3>
                        <p>Create the first lesson to start building this course learning path.</p>
                        <a href="<?= htmlspecialchars(backofficeRoute('lessons', 'create', ['course_id' => (int) $course['id']])); ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create Lesson
                        </a>
                    </div>
                <?php else: ?>
                    <div class="lessons-grid">
                        <?php foreach ($lessons as $lesson): ?>
                            <?php $lessonStatus = (string) ($lesson['status'] ?? 'draft'); ?>
                            <article class="lesson-card">
                                <div class="lesson-top">
                                    <div>
                                        <div class="lesson-title"><?= htmlspecialchars((string) $lesson['title']) ?></div>
                                        <p class="lesson-summary"><?= htmlspecialchars((string) ($lesson['summary'] ?? 'No lesson summary yet.')) ?></p>
                                    </div>
                                    <div class="lesson-order"><?= (int) ($lesson['lesson_order'] ?? 1) ?></div>
                                </div>

                                <div class="lesson-meta">
                                    <span><i class="fas fa-clock"></i> <?= (int) ($lesson['duration_minutes'] ?? 10) ?> min</span>
                                    <span><i class="fas fa-circle-dot"></i> <?= htmlspecialchars($lessonStatus) ?></span>
                                    <span><i class="fas fa-align-left"></i> <?= strlen(trim((string) ($lesson['content'] ?? ''))) > 0 ? 'Content ready' : 'Needs content' ?></span>
                                </div>

                                <div class="lesson-actions">
                                    <?php if ($lessonStatus === 'published'): ?>
                                        <a href="<?= htmlspecialchars(frontofficeRoute('lessons', 'show', ['id' => (int) $lesson['id']])); ?>" class="action-btn action-btn-view">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?= htmlspecialchars(backofficeRoute('lessons', 'edit', ['id' => (int) $lesson['id']])); ?>" class="action-btn action-btn-edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="<?= htmlspecialchars(backofficeRoute('lessons', 'delete', ['id' => (int) $lesson['id']])); ?>" class="action-btn action-btn-delete" onclick="return confirm('Delete this lesson from the learning path?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
