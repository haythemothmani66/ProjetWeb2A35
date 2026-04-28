<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Questions - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Jost:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/backoffice.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DM Sans', sans-serif;
            background: #f5f7fc;
            color: #1e293b;
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .page-header h1 {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
        }
        .page-header p {
            color: #666;
            margin-top: 6px;
        }
        .header-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        .btn {
            padding: 12px 20px;
            border-radius: 8px;
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
            background: white;
            color: #525fe1;
            border: 1px solid #d7dcf5;
        }
        .btn-secondary:hover {
            background: #f6f8ff;
        }
        .quiz-info {
            display: grid;
            grid-template-columns: minmax(0, 1.5fr) minmax(280px, 1fr);
            gap: 24px;
            margin-bottom: 30px;
        }
        .info-card,
        .stats-card,
        .question-card,
        .empty-state {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(15, 23, 42, 0.08);
        }
        .info-card,
        .stats-card {
            padding: 24px;
        }
        .info-card h2,
        .stats-card h3 {
            color: #1e293b;
            margin-bottom: 12px;
        }
        .meta-line {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 14px;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 600;
        }
        .badge-course {
            background: #eef2ff;
            color: #525fe1;
        }
        .badge-quiz {
            background: #e6fffb;
            color: #0f766e;
        }
        .badge-question {
            background: #fff7ed;
            color: #c2410c;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }
        .stat-box {
            padding: 16px;
            border-radius: 10px;
            background: #f8fbff;
        }
        .stat-box strong {
            display: block;
            font-size: 26px;
            color: #0b104a;
            margin-bottom: 6px;
        }
        .stat-box span {
            color: #64748b;
            font-size: 14px;
        }
        .questions-grid {
            display: grid;
            gap: 20px;
        }
        .question-card {
            padding: 24px;
            border-left: 4px solid #525fe1;
        }
        .question-top {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }
        .question-top h3 {
            font-size: 20px;
            line-height: 1.4;
        }
        .question-meta {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }
        .question-meta span {
            font-size: 14px;
            color: #475569;
            background: #f8fafc;
            padding: 8px 12px;
            border-radius: 999px;
        }
        .responses-list {
            display: grid;
            gap: 10px;
            margin: 18px 0;
        }
        .response-item {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: center;
            padding: 14px 16px;
            border-radius: 10px;
            background: #f8fafc;
        }
        .response-text {
            color: #1e293b;
            font-size: 14px;
            line-height: 1.5;
        }
        .response-state {
            flex-shrink: 0;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.02em;
        }
        .response-state-correct {
            background: #dcfce7;
            color: #166534;
        }
        .response-state-wrong {
            background: #e2e8f0;
            color: #475569;
        }
        .question-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 20px;
        }
        .action-btn {
            padding: 10px 14px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }
        .action-btn-edit {
            background: #e0f2fe;
            color: #0369a1;
        }
        .action-btn-edit:hover {
            background: #bae6fd;
        }
        .action-btn-delete {
            background: #fee2e2;
            color: #b91c1c;
        }
        .action-btn-delete:hover {
            background: #fecaca;
        }
        .empty-state {
            padding: 60px 24px;
            text-align: center;
        }
        .empty-state i {
            font-size: 48px;
            color: #cbd5e1;
            margin-bottom: 18px;
        }
        .empty-state h3 {
            font-size: 22px;
            color: #1e293b;
            margin-bottom: 10px;
        }
        .empty-state p {
            color: #64748b;
            margin-bottom: 22px;
        }
        @media (max-width: 900px) {
            .quiz-info {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php
        $sidebarSection = 'quizzes';
        $sidebarCourseId = isset($course['id']) ? (int) $course['id'] : null;
        require dirname(__DIR__) . '/partials/sidebar.php';
        ?>

        <div class="main-content">
            <header class="top-bar">
                <h1>Questions</h1>
                <div class="user-profile">
                    <span>Admin User</span>
                </div>
            </header>

            <div class="content">
                <div class="page-header">
                    <div>
                        <h1>Manage Questions</h1>
                        <p>Add questions and correct responses for this quiz.</p>
                    </div>
                    <div class="header-actions">
                        <a href="<?= htmlspecialchars(backofficeRoute('courses', 'quizzes', ['course_id' => (int) $course['id']])); ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Quizzes
                        </a>
                        <a href="<?= htmlspecialchars(backofficeRoute('questions', 'create', ['quiz_id' => (int) $quiz['id']])); ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> New Question
                        </a>
                    </div>
                </div>

                <div class="quiz-info">
                    <div class="info-card">
                        <h2><?= htmlspecialchars((string) $quiz['title']) ?></h2>
                        <p><?= htmlspecialchars((string) ($quiz['description'] ?? 'No quiz description provided.')) ?></p>
                        <div class="meta-line">
                            <span class="badge badge-course"><i class="fas fa-book"></i> <?= htmlspecialchars((string) $course['title']) ?></span>
                            <span class="badge badge-quiz"><i class="fas fa-stopwatch"></i> <?= (int) ($quiz['duration_minutes'] ?? 30) ?> min</span>
                            <span class="badge badge-question"><i class="fas fa-check-circle"></i> <?= htmlspecialchars((string) ($quiz['passing_score'] ?? 50)) ?>% pass</span>
                        </div>
                    </div>

                    <div class="stats-card">
                        <h3>Quiz Snapshot</h3>
                        <div class="stats-grid">
                            <div class="stat-box">
                                <strong><?= count($questions) ?></strong>
                                <span>Questions</span>
                            </div>
                            <div class="stat-box">
                                <strong><?= array_sum(array_map(static fn (array $question): int => count($question['responses'] ?? []), $questions)) ?></strong>
                                <span>Responses</span>
                            </div>
                            <div class="stat-box">
                                <strong><?= (int) ($quiz['max_attempts'] ?? 1) ?></strong>
                                <span>Max Attempts</span>
                            </div>
                            <div class="stat-box">
                                <strong><?= ((int) ($quiz['is_timed'] ?? 1)) === 1 ? 'Yes' : 'No' ?></strong>
                                <span>Timed Quiz</span>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (empty($questions)): ?>
                    <div class="empty-state">
                        <i class="fas fa-list-check"></i>
                        <h3>No Questions Yet</h3>
                        <p>Start by adding the first question and its responses for this quiz.</p>
                        <a href="<?= htmlspecialchars(backofficeRoute('questions', 'create', ['quiz_id' => (int) $quiz['id']])); ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create Question
                        </a>
                    </div>
                <?php else: ?>
                    <div class="questions-grid">
                        <?php foreach ($questions as $question): ?>
                            <?php
                            $responses = $question['responses'] ?? [];
                            $correctResponses = array_filter($responses, static fn (array $response): bool => ((int) ($response['is_correct'] ?? 0)) === 1);
                            ?>
                            <article class="question-card">
                                <div class="question-top">
                                    <div>
                                        <h3><?= htmlspecialchars((string) $question['question_text']) ?></h3>
                                        <?php if (!empty($question['explanation'])): ?>
                                            <p style="color: #64748b; margin-top: 10px; line-height: 1.6;"><?= htmlspecialchars((string) $question['explanation']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <span class="badge badge-course">#<?= (int) ($question['question_order'] ?? 1) ?></span>
                                </div>

                                <div class="question-meta">
                                    <span><i class="fas fa-layer-group"></i> <?= htmlspecialchars($questionTypes[$question['question_type']] ?? (string) $question['question_type']) ?></span>
                                    <span><i class="fas fa-star"></i> <?= htmlspecialchars((string) ($question['points'] ?? '1.00')) ?> pts</span>
                                    <span><i class="fas fa-list"></i> <?= count($responses) ?> responses</span>
                                    <span><i class="fas fa-bullseye"></i> <?= count($correctResponses) ?> correct</span>
                                </div>

                                <div class="responses-list">
                                    <?php foreach ($responses as $response): ?>
                                        <div class="response-item">
                                            <div class="response-text">
                                                <strong><?= (int) ($response['response_order'] ?? 1) ?>.</strong>
                                                <?= htmlspecialchars((string) $response['response_text']) ?>
                                            </div>
                                            <span class="response-state <?= ((int) ($response['is_correct'] ?? 0)) === 1 ? 'response-state-correct' : 'response-state-wrong' ?>">
                                                <?= ((int) ($response['is_correct'] ?? 0)) === 1 ? 'Correct' : 'Response' ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="question-actions">
                                    <a href="<?= htmlspecialchars(backofficeRoute('questions', 'edit', ['id' => (int) $question['id']])); ?>" class="action-btn action-btn-edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="<?= htmlspecialchars(backofficeRoute('questions', 'delete', ['id' => (int) $question['id']])); ?>" class="action-btn action-btn-delete" onclick="return confirm('Delete this question and all its responses?')">
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
