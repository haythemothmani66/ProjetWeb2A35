<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backoffice | <?= htmlspecialchars(isset($lesson['id']) ? 'Edit Lesson' : 'New Lesson') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Jost:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/backoffice.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DM Sans', sans-serif; background: #f5f7fc; color: #1e293b; overflow-x: hidden; }
        .page-title h1 { font-size: 26px; font-weight: 700; color: #0b104a; }
        .page-title p { color: #64748b; margin-top: 6px; }
        .admin-badge { background: #525fe1; color: white; padding: 8px 18px; border-radius: 40px; font-weight: 600; font-size: 14px; }
        .container { max-width: 840px; margin: 0 auto; background: white; padding: 36px; border-radius: 16px; box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08); }
        .summary { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; margin-bottom: 28px; }
        .summary-box { background: #f8fbff; border-radius: 12px; padding: 16px; }
        .summary-box strong { display: block; color: #0b104a; margin-bottom: 6px; }
        .summary-box span { color: #64748b; font-size: 14px; line-height: 1.5; }
        .form-group { margin-bottom: 22px; }
        .row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        label { display: block; margin-bottom: 8px; font-weight: 700; color: #0f172a; }
        input[type="text"], input[type="number"], textarea, select { width: 100%; padding: 12px; border: 1px solid #e0e0e0; border-radius: 10px; font-family: inherit; font-size: 14px; transition: 0.3s; }
        input[type="text"]:focus, input[type="number"]:focus, textarea:focus, select:focus { outline: none; border-color: #525fe1; box-shadow: 0 0 0 3px rgba(82, 95, 225, 0.1); }
        textarea { resize: vertical; min-height: 120px; }
        .editor { min-height: 240px; }
        .helper { color: #64748b; font-size: 13px; line-height: 1.5; margin-top: 8px; }
        .form-actions { display: flex; gap: 12px; margin-top: 30px; flex-wrap: wrap; }
        .btn { padding: 12px 24px; border-radius: 8px; border: none; font-weight: 700; cursor: pointer; transition: 0.3s; font-size: 14px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
        .btn-primary { background: #525fe1; color: white; }
        .btn-primary:hover { background: #3d4bc7; }
        .btn-secondary { background: #f0f4ff; color: #525fe1; border: 1px solid #525fe1; }
        .btn-secondary:hover { background: #e3f2fd; }
        .error-message { color: #ef4444; font-size: 13px; margin-top: 8px; display: none; }
        .invalid { border-color: #ef4444 !important; }
        @media (max-width: 760px) { .row, .summary { grid-template-columns: 1fr; } .container { padding: 24px; } }
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
            <div class="top-bar">
                <div class="page-title">
                    <h1><?= isset($lesson['id']) ? 'Edit Lesson' : 'Create Lesson' ?></h1>
                    <p><?= htmlspecialchars((string) $course['title']) ?></p>
                </div>
                <div class="admin-badge">Learning Path</div>
            </div>

            <div class="container">
                <div class="summary">
                    <div class="summary-box">
                        <strong>Course</strong>
                        <span><?= htmlspecialchars((string) $course['title']) ?></span>
                    </div>
                    <div class="summary-box">
                        <strong>Level</strong>
                        <span><?= htmlspecialchars((string) ($course['level'] ?? 'beginner')) ?></span>
                    </div>
                    <div class="summary-box">
                        <strong>Goal</strong>
                        <span>Use ordered lessons to guide learners step by step.</span>
                    </div>
                </div>

                <form id="lessonForm" method="post" action="<?= htmlspecialchars(isset($lesson['id']) ? backofficeRoute('lessons', 'update') : backofficeRoute('lessons', 'store')) ?>" novalidate>
                    <?php if (isset($lesson['id'])): ?>
                        <input type="hidden" name="id" value="<?= (int) $lesson['id'] ?>">
                    <?php endif; ?>
                    <input type="hidden" name="course_id" value="<?= (int) $course['id'] ?>">

                    <div class="form-group">
                        <label for="title">Lesson Title</label>
                        <input type="text" id="title" name="title" value="<?= htmlspecialchars((string) ($lesson['title'] ?? '')) ?>" placeholder="Introduction to the course">
                        <div class="error-message" id="titleError"></div>
                    </div>

                    <div class="form-group">
                        <label for="summary">Lesson Summary</label>
                        <textarea id="summary" name="summary" placeholder="Briefly describe what learners will cover in this step."><?= htmlspecialchars((string) ($lesson['summary'] ?? '')) ?></textarea>
                        <div class="error-message" id="summaryError"></div>
                    </div>

                    <div class="form-group">
                        <label for="content">Lesson Content</label>
                        <textarea id="content" class="editor" name="content" placeholder="Write the full lesson content here. Use line breaks to separate sections."><?= htmlspecialchars((string) ($lesson['content'] ?? '')) ?></textarea>
                        <div class="helper">Learners will read this as the lesson page content.</div>
                        <div class="error-message" id="contentError"></div>
                    </div>

                    <div class="row">
                        <div class="form-group">
                            <label for="durationMinutes">Duration Minutes</label>
                            <input type="number" id="durationMinutes" min="1" name="duration_minutes" value="<?= (int) ($lesson['duration_minutes'] ?? 10) ?>">
                            <div class="error-message" id="durationMinutesError"></div>
                        </div>
                        <div class="form-group">
                            <label for="lessonOrder">Lesson Order</label>
                            <input type="number" id="lessonOrder" min="1" name="lesson_order" value="<?= (int) ($lesson['lesson_order'] ?? 1) ?>">
                            <div class="error-message" id="lessonOrderError"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <?php $lessonStatus = (string) ($lesson['status'] ?? 'draft'); ?>
                        <select id="status" name="status">
                            <option value="draft" <?= $lessonStatus === 'draft' ? 'selected' : '' ?>>Draft</option>
                            <option value="published" <?= $lessonStatus === 'published' ? 'selected' : '' ?>>Published</option>
                        </select>
                        <div class="helper">Only published lessons appear to learners in the frontoffice.</div>
                        <div class="error-message" id="statusError"></div>
                    </div>

                    <div class="form-actions">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-save"></i> <?= isset($lesson['id']) ? 'Save Lesson' : 'Create Lesson' ?>
                        </button>
                        <a href="<?= htmlspecialchars(backofficeRoute('lessons', 'index', ['course_id' => (int) $course['id']])); ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('lessonForm').addEventListener('submit', function(event) {
            let isValid = true;
            const title = document.getElementById('title');
            const summary = document.getElementById('summary');
            const content = document.getElementById('content');
            const duration = document.getElementById('durationMinutes');
            const order = document.getElementById('lessonOrder');
            const status = document.getElementById('status');

            const clearError = (element) => {
                element.classList.remove('invalid');
                const message = document.getElementById(`${element.id}Error`);
                if (message) {
                    message.textContent = '';
                    message.style.display = 'none';
                }
            };

            const showError = (element, text) => {
                element.classList.add('invalid');
                const message = document.getElementById(`${element.id}Error`);
                if (message) {
                    message.textContent = text;
                    message.style.display = 'block';
                }
            };

            [title, summary, content, duration, order, status].forEach(clearError);

            if (!title.value.trim()) {
                showError(title, 'Lesson title is required.');
                isValid = false;
            } else if (title.value.trim().length < 3) {
                showError(title, 'Lesson title must have at least 3 characters.');
                isValid = false;
            }

            if (!summary.value.trim()) {
                showError(summary, 'Summary is required.');
                isValid = false;
            } else if (summary.value.trim().length < 10) {
                showError(summary, 'Summary must have at least 10 characters.');
                isValid = false;
            }

            if (!content.value.trim()) {
                showError(content, 'Lesson content is required.');
                isValid = false;
            } else if (content.value.trim().length < 30) {
                showError(content, 'Lesson content must have at least 30 characters.');
                isValid = false;
            }

            if (duration.value === '' || Number.isNaN(Number(duration.value)) || !Number.isInteger(Number(duration.value)) || Number(duration.value) < 1) {
                showError(duration, 'Duration must be a whole number of at least 1 minute.');
                isValid = false;
            }

            if (order.value === '' || Number.isNaN(Number(order.value)) || !Number.isInteger(Number(order.value)) || Number(order.value) < 1) {
                showError(order, 'Lesson order must be a whole number starting at 1.');
                isValid = false;
            }

            if (!['draft', 'published'].includes(status.value)) {
                showError(status, 'Please select a valid status.');
                isValid = false;
            }

            if (!isValid) {
                event.preventDefault();
            }
        });
    </script>
</body>
</html>
