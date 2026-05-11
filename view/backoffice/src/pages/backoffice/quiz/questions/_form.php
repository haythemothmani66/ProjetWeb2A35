<?php $BO = '/gestion_users/view/backoffice/src'; ?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Back Office - Question Form | EduMatch Admin</title>
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

        .stack { display: grid; gap: 24px; }
        .pill { display: inline-flex; align-items: center; gap: 8px; padding: 10px 14px; border-radius: 999px; background: #eef2ff; color: #4338ca; font-weight: 700; }
        .card { background: white; border-radius: 16px; padding: 24px; box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08); }
        .summary { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
        .summary-box { padding: 16px; border-radius: 12px; background: #f8fbff; }
        .summary-box strong { display: block; margin-bottom: 6px; color: #0b104a; }
        .summary-box span { color: #64748b; font-size: 14px; }
        .grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
        .full { grid-column: 1 / -1; }
        label { display: block; margin-bottom: 8px; font-weight: 700; color: #0f172a; }
        input, textarea, select { width: 100%; padding: 12px 14px; border: 1px solid #d7dce7; border-radius: 10px; font-size: 14px; font-family: inherit; color: #1e293b; background: white; }
        input:focus, textarea:focus, select:focus { outline: none; border-color: #525fe1; box-shadow: 0 0 0 3px rgba(82, 95, 225, 0.12); }
        textarea { min-height: 110px; resize: vertical; }
        .hint { margin-top: 8px; color: #64748b; font-size: 13px; line-height: 1.5; }
        .error { margin-top: 8px; color: #dc2626; font-size: 13px; display: block; }
        .alert { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; border-radius: 12px; padding: 14px 16px; line-height: 1.5; }
        .responses-head { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 18px; }
        .responses-head h2 { font-size: 22px; color: #0b104a; }
        .responses-head p { color: #64748b; font-size: 14px; }
        .responses { display: grid; gap: 16px; }
        .response-card { border: 1px solid #e2e8f0; border-radius: 14px; padding: 18px; background: #fcfdff; }
        .response-top { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 16px; flex-wrap: wrap; }
        .response-top h3 { font-size: 17px; color: #1e293b; }
        .response-actions { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
        .toggle { display: inline-flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 600; color: #0f172a; }
        .toggle input { width: 18px; height: 18px; accent-color: #525fe1; }
        .btn { padding: 11px 16px; border-radius: 10px; border: 1px solid transparent; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 8px; font-weight: 700; cursor: pointer; transition: 0.25s ease; font-size: 14px; }
        .btn-primary { background: #525fe1; color: white; }
        .btn-primary:hover { background: #4452d4; }
        .btn-secondary { background: white; color: #475569; border-color: #d7dce7; }
        .btn-secondary:hover { background: #f8fafc; }
        .btn-danger { background: #fff1f2; color: #be123c; border-color: #fecdd3; }
        .btn-danger:hover { background: #ffe4e6; }
        .btn-add { background: #ecfeff; color: #0f766e; border-color: #a5f3fc; }
        .btn-add:hover { background: #cffafe; }
        .form-actions { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 24px; }
        @media (max-width: 900px) { .grid, .summary { grid-template-columns: 1fr; } }
    
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
                        <h1 class="mb-0">Question</h1>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <a href="<?= htmlspecialchars(backofficeRoute('questions', 'index', ['quiz_id' => (int) $quiz['id']])); ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back to Questions</a>
                    </div>
                </div>



            <div class="content">
                <div class="stack">
                    <div class="card">
                        <div class="summary">
                            <div class="summary-box">
                                <strong>Quiz</strong>
                                <span><?= htmlspecialchars((string) $quiz['title']) ?></span>
                            </div>
                            <div class="summary-box">
                                <strong>Passing Score</strong>
                                <span><?= htmlspecialchars((string) ($quiz['passing_score'] ?? 50)) ?>%</span>
                            </div>
                            <div class="summary-box">
                                <strong>Duration</strong>
                                <span><?= (int) ($quiz['duration_minutes'] ?? 30) ?> minutes</span>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <?php if (!empty($errors['form'])): ?>
                            <div class="alert" style="margin-bottom: 20px;"><?= htmlspecialchars((string) $errors['form']) ?></div>
                        <?php endif; ?>

                        <form id="questionForm" method="post" action="<?= htmlspecialchars($formAction) ?>" novalidate>
                            <?php if (isset($question['id'])): ?>
                                <input type="hidden" name="id" value="<?= (int) $question['id'] ?>">
                            <?php endif; ?>
                            <input type="hidden" name="quiz_id" value="<?= (int) $formData['quiz_id'] ?>">

                            <div class="grid">
                                <div class="full">
                                    <label for="questionText">Question Text</label>
                                    <textarea id="questionText" name="question_text"><?= htmlspecialchars((string) $formData['question_text']) ?></textarea>
                                    <span class="error" id="questionTextError"<?= empty($errors['question_text']) ? ' style="display: none;"' : '' ?>><?= htmlspecialchars((string) ($errors['question_text'] ?? '')) ?></span>
                                </div>

                                <div>
                                    <label for="questionType">Question Type</label>
                                    <select id="questionType" name="question_type">
                                        <?php foreach ($questionTypes as $typeKey => $typeLabel): ?>
                                            <option value="<?= htmlspecialchars($typeKey) ?>" <?= $formData['question_type'] === $typeKey ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($typeLabel) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span class="error" id="questionTypeError"<?= empty($errors['question_type']) ? ' style="display: none;"' : '' ?>><?= htmlspecialchars((string) ($errors['question_type'] ?? '')) ?></span>
                                </div>

                                <div>
                                    <label for="points">Points</label>
                                    <input id="points" type="number" min="0.01" step="0.01" name="points" value="<?= htmlspecialchars((string) $formData['points']) ?>">
                                    <span class="error" id="pointsError"<?= empty($errors['points']) ? ' style="display: none;"' : '' ?>><?= htmlspecialchars((string) ($errors['points'] ?? '')) ?></span>
                                </div>

                                <div>
                                    <label for="questionOrder">Question Order</label>
                                    <input id="questionOrder" type="number" min="1" step="1" name="question_order" value="<?= htmlspecialchars((string) $formData['question_order']) ?>">
                                    <span class="error" id="questionOrderError"<?= empty($errors['question_order']) ? ' style="display: none;"' : '' ?>><?= htmlspecialchars((string) ($errors['question_order'] ?? '')) ?></span>
                                </div>

                                <div class="full">
                                    <label for="explanation">Explanation</label>
                                    <textarea id="explanation" name="explanation"><?= htmlspecialchars((string) $formData['explanation']) ?></textarea>
                                    <div class="hint">Optional extra explanation for the question.</div>
                                </div>
                            </div>

                            <div style="margin-top: 28px;">
                                <div class="responses-head">
                                    <div>
                                        <h2>Responses</h2>
                                        <p id="responsesHint"></p>
                                    </div>
                                    <button type="button" class="btn btn-add" id="addResponseBtn">
                                        <i class="fas fa-plus"></i> Add Response
                                    </button>
                                </div>

                                <div class="alert" id="responsesError"<?= empty($errors['responses']) ? ' style="display: none; margin-bottom: 18px;"' : ' style="margin-bottom: 18px;"' ?>><?= htmlspecialchars((string) ($errors['responses'] ?? '')) ?></div>

                                <div class="responses" id="responseList">
                                    <?php foreach ($formData['responses'] as $index => $response): ?>
                                        <div class="response-card" data-response-index="<?= (int) $index ?>">
                                            <div class="response-top">
                                                <h3>Response <span class="response-number"><?= (int) $index + 1 ?></span></h3>
                                                <div class="response-actions">
                                                    <label class="toggle">
                                                        <input type="checkbox" data-field="is_correct" name="responses[<?= (int) $index ?>][is_correct]" value="1" <?= !empty($response['is_correct']) ? 'checked' : '' ?>>
                                                        Correct response
                                                    </label>
                                                    <button type="button" class="btn btn-danger remove-response-btn">
                                                        <i class="fas fa-trash"></i> Remove
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="grid">
                                                <div class="full">
                                                    <label>Response Text</label>
                                                    <input type="text" data-field="text" name="responses[<?= (int) $index ?>][text]" value="<?= htmlspecialchars((string) ($response['text'] ?? '')) ?>">
                                                </div>
                                                <div>
                                                    <label>Display Order</label>
                                                    <input type="number" min="1" step="1" data-field="order" name="responses[<?= (int) $index ?>][order]" value="<?= htmlspecialchars((string) ($response['order'] ?? (string) ($index + 1))) ?>">
                                                </div>
                                                <div class="full">
                                                    <label>Explanation</label>
                                                    <textarea data-field="explanation" name="responses[<?= (int) $index ?>][explanation]"><?= htmlspecialchars((string) ($response['explanation'] ?? '')) ?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button class="btn btn-primary" type="submit"><?= htmlspecialchars($submitLabel) ?></button>
                                <a class="btn btn-secondary" href="<?= htmlspecialchars(backofficeRoute('questions', 'index', ['quiz_id' => (int) $quiz['id']])); ?>">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <template id="responseTemplate">
        <div class="response-card" data-response-index="__INDEX__">
            <div class="response-top">
                <h3>Response <span class="response-number">__NUMBER__</span></h3>
                <div class="response-actions">
                    <label class="toggle">
                        <input type="checkbox" data-field="is_correct" name="responses[__INDEX__][is_correct]" value="1">
                        Correct response
                    </label>
                    <button type="button" class="btn btn-danger remove-response-btn">
                        <i class="fas fa-trash"></i> Remove
                    </button>
                </div>
            </div>

            <div class="grid">
                <div class="full">
                    <label>Response Text</label>
                    <input type="text" data-field="text" name="responses[__INDEX__][text]">
                </div>
                <div>
                    <label>Display Order</label>
                    <input type="number" min="1" step="1" data-field="order" name="responses[__INDEX__][order]" value="__ORDER__">
                </div>
                <div class="full">
                    <label>Explanation</label>
                    <textarea data-field="explanation" name="responses[__INDEX__][explanation]"></textarea>
                </div>
            </div>
        </div>
    </template>

            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simplebar@6.2.5/dist/simplebar.min.js"></script>
    <script src="<?= $BO ?>/assets/js/main.js"></script>
    <script src="<?= $BO ?>/assets/js/vendors/sidebarnav.js"></script>
<script>
        const questionForm = document.getElementById('questionForm');
        const questionText = document.getElementById('questionText');
        const questionType = document.getElementById('questionType');
        const points = document.getElementById('points');
        const questionOrder = document.getElementById('questionOrder');
        const responseList = document.getElementById('responseList');
        const responseTemplate = document.getElementById('responseTemplate');
        const responsesError = document.getElementById('responsesError');
        const responsesHint = document.getElementById('responsesHint');
        const addResponseBtn = document.getElementById('addResponseBtn');
        const responseNotes = {
            single_choice: 'Choose exactly one correct response.',
            multiple_choice: 'You can mark more than one correct response.',
            true_false: 'Keep exactly two responses and mark one as correct.'
        };

        function setHint() {
            responsesHint.textContent = responseNotes[questionType.value] || '';
        }

        function setFieldError(element, errorId, message) {
            element.style.borderColor = message ? '#dc2626' : '#d7dce7';
            const errorNode = document.getElementById(errorId);
            errorNode.textContent = message || '';
            errorNode.style.display = message ? 'block' : 'none';
        }

        function setResponsesError(message) {
            responsesError.textContent = message || '';
            responsesError.style.display = message ? 'block' : 'none';
        }

        function syncResponses() {
            [...responseList.querySelectorAll('.response-card')].forEach((card, index) => {
                card.dataset.responseIndex = String(index);
                card.querySelector('.response-number').textContent = String(index + 1);
                card.querySelectorAll('[data-field]').forEach((field) => {
                    field.name = `responses[${index}][${field.dataset.field}]`;
                    if (field.dataset.field === 'order' && !field.value) {
                        field.value = String(index + 1);
                    }
                });
            });
        }

        function addResponse(data = {}) {
            const index = responseList.querySelectorAll('.response-card').length;
            let html = responseTemplate.innerHTML
                .replaceAll('__INDEX__', String(index))
                .replaceAll('__NUMBER__', String(index + 1))
                .replaceAll('__ORDER__', String(data.order || index + 1));
            responseList.insertAdjacentHTML('beforeend', html);
            const card = responseList.lastElementChild;
            card.querySelector('[data-field="text"]').value = data.text || '';
            card.querySelector('[data-field="explanation"]').value = data.explanation || '';
            card.querySelector('[data-field="is_correct"]').checked = Boolean(data.is_correct);
            syncResponses();
        }

        function enforceSingleCorrect(target) {
            if ((questionType.value === 'single_choice' || questionType.value === 'true_false') && target.checked) {
                responseList.querySelectorAll('input[data-field="is_correct"]').forEach((checkbox) => {
                    if (checkbox !== target) {
                        checkbox.checked = false;
                    }
                });
            }
        }

        function activeResponses() {
            return [...responseList.querySelectorAll('.response-card')].map((card) => ({
                text: card.querySelector('[data-field="text"]').value.trim(),
                explanation: card.querySelector('[data-field="explanation"]').value.trim(),
                order: card.querySelector('[data-field="order"]').value.trim(),
                isCorrect: card.querySelector('[data-field="is_correct"]').checked
            })).filter((response) => response.text !== '' || response.explanation !== '' || response.isCorrect);
        }

        function validateResponses() {
            const responses = activeResponses();

            if (responses.length < 2) {
                setResponsesError('Add at least two responses with text.');
                return false;
            }

            for (const response of responses) {
                if (response.text === '') {
                    setResponsesError('Each response must include text.');
                    return false;
                }

                if (!Number.isInteger(Number(response.order)) || Number(response.order) < 1) {
                    setResponsesError('Response order must be a whole number starting at 1.');
                    return false;
                }
            }

            const correctCount = responses.filter((response) => response.isCorrect).length;

            if (questionType.value === 'multiple_choice' && correctCount < 1) {
                setResponsesError('Multiple choice questions need at least one correct response.');
                return false;
            }

            if ((questionType.value === 'single_choice' || questionType.value === 'true_false') && correctCount !== 1) {
                setResponsesError('Single choice and true/false questions need exactly one correct response.');
                return false;
            }

            if (questionType.value === 'true_false' && responses.length !== 2) {
                setResponsesError('True / false questions must have exactly two responses.');
                return false;
            }

            setResponsesError('');
            return true;
        }

        addResponseBtn.addEventListener('click', () => addResponse());

        responseList.addEventListener('click', (event) => {
            const removeButton = event.target.closest('.remove-response-btn');
            if (!removeButton) {
                return;
            }

            if (responseList.querySelectorAll('.response-card').length <= 2) {
                setResponsesError('At least two responses are required.');
                return;
            }

            removeButton.closest('.response-card').remove();
            syncResponses();
            setResponsesError('');
        });

        responseList.addEventListener('change', (event) => {
            if (event.target.matches('input[data-field="is_correct"]')) {
                enforceSingleCorrect(event.target);
            }
        });

        questionType.addEventListener('change', () => {
            setHint();
            const checked = responseList.querySelectorAll('input[data-field="is_correct"]:checked');
            if ((questionType.value === 'single_choice' || questionType.value === 'true_false') && checked.length > 1) {
                checked.forEach((checkbox, index) => {
                    checkbox.checked = index === 0;
                });
            }
            setResponsesError('');
        });

        questionForm.addEventListener('submit', (event) => {
            let valid = true;

            setFieldError(questionText, 'questionTextError', '');
            setFieldError(questionType, 'questionTypeError', '');
            setFieldError(points, 'pointsError', '');
            setFieldError(questionOrder, 'questionOrderError', '');
            setResponsesError('');

            if (questionText.value.trim() === '') {
                setFieldError(questionText, 'questionTextError', 'Question text is required.');
                valid = false;
            } else if (questionText.value.trim().length < 3) {
                setFieldError(questionText, 'questionTextError', 'Question text must be at least 3 characters.');
                valid = false;
            }

            if (!['single_choice', 'multiple_choice', 'true_false'].includes(questionType.value)) {
                setFieldError(questionType, 'questionTypeError', 'Please choose a valid question type.');
                valid = false;
            }

            if (points.value.trim() === '' || Number.isNaN(Number(points.value)) || Number(points.value) <= 0) {
                setFieldError(points, 'pointsError', 'Points must be a number greater than 0.');
                valid = false;
            }

            if (!Number.isInteger(Number(questionOrder.value)) || Number(questionOrder.value) < 1) {
                setFieldError(questionOrder, 'questionOrderError', 'Question order must be a whole number starting at 1.');
                valid = false;
            }

            if (!validateResponses()) {
                valid = false;
            }

            if (!valid) {
                event.preventDefault();
            }
        });

        setHint();
        syncResponses();
    </script>

</body>
</html>