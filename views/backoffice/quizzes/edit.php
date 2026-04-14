<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backoffice | Edit Quiz</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Jost:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/backoffice.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f7fc; overflow-x: hidden; }

        .page-title h1 {
            font-size: 26px;
            font-weight: 700;
            color: #0b104a;
        }

        .admin-badge {
            background: #525fe1;
            color: white;
            padding: 8px 18px;
            border-radius: 40px;
            font-weight: 600;
            font-size: 14px;
        }

        label { display:block; margin-top: 12px; font-weight: 600; }
        input, textarea, select { width:100%; padding: 10px; margin-top:6px; border-radius: 10px; border: 1px solid #ddd; }
        .row { display:flex; gap: 12px; flex-wrap: wrap; }
        .row > div { flex: 1; min-width: 220px; }
        .actions { margin-top: 16px; display:flex; gap: 10px; flex-wrap: wrap; }
        .btn { padding: 10px 14px; border-radius: 10px; border: 1px solid #525fe1; color:#525fe1; background: #fff; font-weight: 600; cursor:pointer; text-decoration: none; display: inline-block; }
        .btn-primary { background:#525fe1; color:#fff; }
        .error-message { color: #ef4444; font-size: 13px; margin-top: 6px; display: none; }
        .invalid { border-color: #ef4444 !important; }
    </style>
</head>
<body>
    <div class="admin-wrapper" id="adminWrapper">
        <?php
        $sidebarSection = 'quizzes';
        $sidebarCourseId = isset($quiz['course_id']) ? (int) $quiz['course_id'] : null;
        require dirname(__DIR__) . '/partials/sidebar.php';
        ?>

        <div class="main-content">
            <div class="top-bar">
                <div class="page-title">
                    <h1>Edit Quiz</h1>
                </div>
                <div class="admin-badge">Admin</div>
            </div>
<form id="quizForm" method="post" action="<?= htmlspecialchars(backofficeRoute('quizzes', 'update')); ?>" novalidate>
    <input type="hidden" name="id" value="<?= (int) $quiz['id'] ?>">

    <label for="courseId">Course</label>
    <select id="courseId" name="course_id">
        <?php foreach ($courses as $c): ?>
            <option value="<?= (int) $c['id'] ?>" <?= ((int) $c['id'] === (int) $quiz['course_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars((string) $c['title']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <div class="error-message" id="courseIdError"></div>

    <label for="title">Title</label>
    <input id="title" name="title" value="<?= htmlspecialchars((string) $quiz['title']) ?>">
    <div class="error-message" id="titleError"></div>

    <label for="description">Description</label>
    <textarea id="description" name="description" rows="4"><?= htmlspecialchars((string) ($quiz['description'] ?? '')) ?></textarea>
    <div class="error-message" id="descriptionError"></div>

    <div class="row">
        <div>
            <label for="durationMinutes">Duration Minutes</label>
            <input id="durationMinutes" type="number" min="1" name="duration_minutes" value="<?= (int) ($quiz['duration_minutes'] ?? 30) ?>">
            <div class="error-message" id="durationMinutesError"></div>
        </div>
        <div>
            <label for="passingScore">Passing Score (%)</label>
            <input id="passingScore" type="number" min="0" max="100" step="0.01" name="passing_score" value="<?= htmlspecialchars((string) ($quiz['passing_score'] ?? 50.00)) ?>">
            <div class="error-message" id="passingScoreError"></div>
        </div>
    </div>

    <div class="row">
        <div>
            <label for="maxAttempts">Max Attempts</label>
            <input id="maxAttempts" type="number" min="1" name="max_attempts" value="<?= (int) ($quiz['max_attempts'] ?? 1) ?>">
            <div class="error-message" id="maxAttemptsError"></div>
        </div>
        <div>
            <label for="isTimed">Is Timed</label>
            <?php $isTimed = ((int) ($quiz['is_timed'] ?? 1)) === 1; ?>
            <select id="isTimed" name="is_timed">
                <option value="1" <?= $isTimed ? 'selected' : '' ?>>Yes</option>
                <option value="0" <?= !$isTimed ? 'selected' : '' ?>>No</option>
            </select>
            <div class="error-message" id="isTimedError"></div>
        </div>
    </div>

    <div class="actions">
        <button class="btn btn-primary" type="submit">Save</button>
        <a class="btn" href="<?= htmlspecialchars(backofficeRoute('courses', 'quizzes', ['course_id' => (int) $quiz['course_id']])); ?>">Cancel</a>
    </div>
</form>

<script>
    document.getElementById('quizForm').addEventListener('submit', function(event) {
        let isValid = true;
        const courseSelect = document.getElementById('courseId');
        const title = document.getElementById('title');
        const description = document.getElementById('description');
        const duration = document.getElementById('durationMinutes');
        const passingScore = document.getElementById('passingScore');
        const maxAttempts = document.getElementById('maxAttempts');
        const isTimed = document.getElementById('isTimed');

        const clearError = (field) => {
            field.classList.remove('invalid');
            const error = document.getElementById(`${field.id}Error`);
            if (error) {
                error.textContent = '';
                error.style.display = 'none';
            }
        };

        const showError = (field, message) => {
            field.classList.add('invalid');
            const error = document.getElementById(`${field.id}Error`);
            if (error) {
                error.textContent = message;
                error.style.display = 'block';
            }
        };

        [courseSelect, title, description, duration, passingScore, maxAttempts, isTimed].forEach(clearError);

        if (!courseSelect.value) {
            showError(courseSelect, 'Please select a course.');
            isValid = false;
        }

        if (!title.value.trim()) {
            showError(title, 'Quiz title is required.');
            isValid = false;
        } else if (title.value.trim().length < 3) {
            showError(title, 'Quiz title must be at least 3 characters.');
            isValid = false;
        }

        if (!description.value.trim()) {
            showError(description, 'Description is required.');
            isValid = false;
        } else if (description.value.trim().length < 10) {
            showError(description, 'Description must be at least 10 characters.');
            isValid = false;
        }

        if (duration.value === '' || Number.isNaN(Number(duration.value))) {
            showError(duration, 'Duration is required.');
            isValid = false;
        } else if (!Number.isInteger(Number(duration.value)) || Number(duration.value) < 1) {
            showError(duration, 'Duration must be a whole number of minutes and at least 1.');
            isValid = false;
        }

        if (passingScore.value === '' || Number.isNaN(Number(passingScore.value))) {
            showError(passingScore, 'Passing score is required.');
            isValid = false;
        } else if (Number(passingScore.value) < 0 || Number(passingScore.value) > 100) {
            showError(passingScore, 'Passing score must be between 0 and 100.');
            isValid = false;
        }

        if (maxAttempts.value === '' || Number.isNaN(Number(maxAttempts.value))) {
            showError(maxAttempts, 'Max attempts is required.');
            isValid = false;
        } else if (!Number.isInteger(Number(maxAttempts.value)) || Number(maxAttempts.value) < 1) {
            showError(maxAttempts, 'Max attempts must be a whole number and at least 1.');
            isValid = false;
        }

        if (isTimed.value !== '0' && isTimed.value !== '1') {
            showError(isTimed, 'Please choose if the quiz is timed.');
            isValid = false;
        }

        if (!isValid) {
            event.preventDefault();
        }
    });
</script>
        </div>
    </div>
</body>
</html>
