<?php

class BackofficeQuestionsController
{
    private QuizRepository $quizzes;
    private CourseRepository $courses;
    private QuestionRepository $questions;
    private ResponseRepository $responses;

    private string $viewsPath;
    private array $questionTypes;

    public function __construct()
    {
        $this->quizzes = new QuizRepository();
        $this->courses = new CourseRepository();
        $this->questions = new QuestionRepository();
        $this->responses = new ResponseRepository();
        $this->viewsPath = dirname(__DIR__) . '/views';
        $this->questionTypes = [
            'single_choice' => 'Single Choice',
            'multiple_choice' => 'Multiple Choice',
            'true_false' => 'True / False',
        ];
    }

    public function index(array $params = []): void
    {
        $quizId = isset($params['quiz_id']) ? (int) $params['quiz_id'] : 0;
        $context = $this->loadQuizContext($quizId);

        if ($context === null) {
            return;
        }

        $quiz = $context['quiz'];
        $course = $context['course'];
        $questions = $this->questions->getByQuizId($quizId);

        foreach ($questions as &$question) {
            $question['responses'] = $this->responses->getByQuestionId((int) $question['id']);
        }
        unset($question);

        $questionTypes = $this->questionTypes;
        require $this->viewsPath . '/backoffice/questions/index.php';
    }

    public function create(array $params = []): void
    {
        $quizId = isset($params['quiz_id']) ? (int) $params['quiz_id'] : 0;
        $context = $this->loadQuizContext($quizId);

        if ($context === null) {
            return;
        }

        $quiz = $context['quiz'];
        $course = $context['course'];
        $formData = $this->buildFormData([
            'quiz_id' => $quizId,
            'question_order' => (string) (count($this->questions->getByQuizId($quizId)) + 1),
            'question_type' => 'single_choice',
            'points' => '1.00',
        ]);
        $errors = [];
        $questionTypes = $this->questionTypes;
        $pageTitle = 'New Question';
        $submitLabel = 'Create Question';
        $formAction = backofficeRoute('questions', 'store');

        require $this->viewsPath . '/backoffice/questions/create.php';
    }

    public function store(array $data = []): void
    {
        $quizId = isset($data['quiz_id']) ? (int) $data['quiz_id'] : 0;
        $context = $this->loadQuizContext($quizId);

        if ($context === null) {
            return;
        }

        $quiz = $context['quiz'];
        $course = $context['course'];
        $formData = $this->buildFormData($data);
        $errors = $this->validateFormData($formData);

        if (!empty($errors)) {
            http_response_code(422);
            $questionTypes = $this->questionTypes;
            $pageTitle = 'New Question';
            $submitLabel = 'Create Question';
            $formAction = backofficeRoute('questions', 'store');
            require $this->viewsPath . '/backoffice/questions/create.php';
            return;
        }

        $db = Database::connection();

        try {
            $db->beginTransaction();
            $questionId = (int) $this->questions->create($this->toQuestionPayload($formData, $quizId));
            $this->responses->replaceByQuestionId($questionId, $this->toResponsePayload($formData));
            $db->commit();
        } catch (Throwable $throwable) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            http_response_code(500);
            $errors['form'] = 'Unable to save the question right now.';
            $questionTypes = $this->questionTypes;
            $pageTitle = 'New Question';
            $submitLabel = 'Create Question';
            $formAction = backofficeRoute('questions', 'store');
            require $this->viewsPath . '/backoffice/questions/create.php';
            return;
        }

        header('Location: ' . backofficeRoute('questions', 'index', ['quiz_id' => $quizId]));
        exit;
    }

    public function edit(array $params = []): void
    {
        $id = isset($params['id']) ? (int) $params['id'] : 0;
        $question = $this->questions->getById($id);

        if (!$question) {
            $this->renderNotFound('Question not found');
            return;
        }

        $context = $this->loadQuizContext((int) $question['quiz_id']);

        if ($context === null) {
            return;
        }

        $quiz = $context['quiz'];
        $course = $context['course'];
        $existingResponses = $this->responses->getByQuestionId($id);
        $formData = $this->buildFormData($question, $existingResponses);
        $errors = [];
        $questionTypes = $this->questionTypes;
        $pageTitle = 'Edit Question';
        $submitLabel = 'Save Changes';
        $formAction = backofficeRoute('questions', 'update');

        require $this->viewsPath . '/backoffice/questions/edit.php';
    }

    public function update(array $data = []): void
    {
        $id = isset($data['id']) ? (int) $data['id'] : 0;
        $question = $this->questions->getById($id);

        if (!$question) {
            $this->renderNotFound('Question not found');
            return;
        }

        $quizId = isset($data['quiz_id']) ? (int) $data['quiz_id'] : (int) $question['quiz_id'];
        $context = $this->loadQuizContext($quizId);

        if ($context === null) {
            return;
        }

        $quiz = $context['quiz'];
        $course = $context['course'];
        $formData = $this->buildFormData($data);
        $errors = $this->validateFormData($formData);

        if (!empty($errors)) {
            http_response_code(422);
            $questionTypes = $this->questionTypes;
            $pageTitle = 'Edit Question';
            $submitLabel = 'Save Changes';
            $formAction = backofficeRoute('questions', 'update');
            require $this->viewsPath . '/backoffice/questions/edit.php';
            return;
        }

        $db = Database::connection();

        try {
            $db->beginTransaction();
            $this->questions->update($id, $this->toQuestionPayload($formData, $quizId));
            $this->responses->replaceByQuestionId($id, $this->toResponsePayload($formData));
            $db->commit();
        } catch (Throwable $throwable) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            http_response_code(500);
            $errors['form'] = 'Unable to update the question right now.';
            $questionTypes = $this->questionTypes;
            $pageTitle = 'Edit Question';
            $submitLabel = 'Save Changes';
            $formAction = backofficeRoute('questions', 'update');
            require $this->viewsPath . '/backoffice/questions/edit.php';
            return;
        }

        header('Location: ' . backofficeRoute('questions', 'index', ['quiz_id' => $quizId]));
        exit;
    }

    public function delete(array $params = []): void
    {
        $id = isset($params['id']) ? (int) $params['id'] : 0;
        $question = $this->questions->getById($id);

        if (!$question) {
            header('Location: ' . backofficeRoute('courses', 'index'));
            exit;
        }

        $quizId = (int) $question['quiz_id'];
        $this->questions->delete($id);

        header('Location: ' . backofficeRoute('questions', 'index', ['quiz_id' => $quizId]));
        exit;
    }

    private function loadQuizContext(int $quizId): ?array
    {
        $quiz = $this->quizzes->getById($quizId);

        if (!$quiz) {
            $this->renderNotFound('Quiz not found');
            return null;
        }

        $course = $this->courses->getById((int) $quiz['course_id']);

        if (!$course) {
            $this->renderNotFound('Course not found');
            return null;
        }

        return [
            'quiz' => $quiz,
            'course' => $course,
        ];
    }

    private function renderNotFound(string $message): void
    {
        http_response_code(404);
        echo $message;
    }

    private function buildFormData(array $source, ?array $existingResponses = null): array
    {
        $responses = [];

        if ($existingResponses !== null) {
            foreach ($existingResponses as $index => $response) {
                $responses[] = [
                    'text' => (string) ($response['response_text'] ?? ''),
                    'is_correct' => ((int) ($response['is_correct'] ?? 0)) === 1,
                    'order' => (string) ($response['response_order'] ?? ($index + 1)),
                    'explanation' => (string) ($response['explanation'] ?? ''),
                ];
            }
        } else {
            $rawResponses = $source['responses'] ?? [];

            if (is_array($rawResponses)) {
                foreach ($rawResponses as $index => $response) {
                    if (!is_array($response)) {
                        continue;
                    }

                    $responses[] = [
                        'text' => trim((string) ($response['text'] ?? '')),
                        'is_correct' => !empty($response['is_correct']),
                        'order' => trim((string) ($response['order'] ?? (string) ($index + 1))),
                        'explanation' => trim((string) ($response['explanation'] ?? '')),
                    ];
                }
            }
        }

        if (empty($responses)) {
            $responses = $this->defaultResponses();
        }

        return [
            'quiz_id' => isset($source['quiz_id']) ? (int) $source['quiz_id'] : 0,
            'question_text' => trim((string) ($source['question_text'] ?? '')),
            'question_type' => (string) ($source['question_type'] ?? 'single_choice'),
            'points' => trim((string) ($source['points'] ?? '1.00')),
            'question_order' => trim((string) ($source['question_order'] ?? '1')),
            'explanation' => trim((string) ($source['explanation'] ?? '')),
            'responses' => array_values($responses),
        ];
    }

    private function defaultResponses(): array
    {
        return [
            ['text' => '', 'is_correct' => false, 'order' => '1', 'explanation' => ''],
            ['text' => '', 'is_correct' => false, 'order' => '2', 'explanation' => ''],
            ['text' => '', 'is_correct' => false, 'order' => '3', 'explanation' => ''],
            ['text' => '', 'is_correct' => false, 'order' => '4', 'explanation' => ''],
        ];
    }

    private function validateFormData(array $formData): array
    {
        $errors = [];

        if ($formData['question_text'] === '') {
            $errors['question_text'] = 'Question text is required.';
        } elseif (mb_strlen($formData['question_text']) < 3) {
            $errors['question_text'] = 'Question text must be at least 3 characters.';
        }

        if (!array_key_exists($formData['question_type'], $this->questionTypes)) {
            $errors['question_type'] = 'Please choose a valid question type.';
        }

        if ($formData['points'] === '' || !is_numeric($formData['points']) || (float) $formData['points'] <= 0) {
            $errors['points'] = 'Points must be a number greater than 0.';
        }

        if ($formData['question_order'] === '' || filter_var($formData['question_order'], FILTER_VALIDATE_INT) === false || (int) $formData['question_order'] < 1) {
            $errors['question_order'] = 'Question order must be a whole number starting at 1.';
        }

        $responses = $this->toResponsePayload($formData);

        if (count($responses) < 2) {
            $errors['responses'] = 'Add at least two responses with text.';
            return $errors;
        }

        foreach ($responses as $response) {
            if ($response['response_text'] === '') {
                $errors['responses'] = 'Each response must include text.';
                return $errors;
            }

            if ($response['response_order'] < 1) {
                $errors['responses'] = 'Response order must be a whole number starting at 1.';
                return $errors;
            }
        }

        $correctCount = 0;

        foreach ($responses as $response) {
            if ((int) $response['is_correct'] === 1) {
                $correctCount++;
            }
        }

        if ($formData['question_type'] === 'multiple_choice' && $correctCount < 1) {
            $errors['responses'] = 'Multiple choice questions need at least one correct response.';
        }

        if (in_array($formData['question_type'], ['single_choice', 'true_false'], true) && $correctCount !== 1) {
            $errors['responses'] = 'Single choice and true/false questions need exactly one correct response.';
        }

        if ($formData['question_type'] === 'true_false' && count($responses) !== 2) {
            $errors['responses'] = 'True / false questions must have exactly two responses.';
        }

        return $errors;
    }

    private function toQuestionPayload(array $formData, int $quizId): array
    {
        return [
            'quiz_id' => $quizId,
            'question_text' => $formData['question_text'],
            'question_type' => $formData['question_type'],
            'points' => number_format((float) $formData['points'], 2, '.', ''),
            'question_order' => (int) $formData['question_order'],
            'explanation' => $formData['explanation'] !== '' ? $formData['explanation'] : null,
        ];
    }

    private function toResponsePayload(array $formData): array
    {
        $responses = [];

        foreach ($formData['responses'] as $index => $response) {
            $text = trim((string) ($response['text'] ?? ''));
            $explanation = trim((string) ($response['explanation'] ?? ''));
            $orderRaw = trim((string) ($response['order'] ?? ''));
            $hasContent = $text !== '' || $explanation !== '' || !empty($response['is_correct']);

            if (!$hasContent) {
                continue;
            }

            $responses[] = [
                'response_text' => $text,
                'is_correct' => !empty($response['is_correct']) ? 1 : 0,
                'response_order' => filter_var($orderRaw, FILTER_VALIDATE_INT) !== false ? (int) $orderRaw : 0,
                'explanation' => $explanation !== '' ? $explanation : null,
                '_index' => $index,
            ];
        }

        usort($responses, static function (array $left, array $right): int {
            if ($left['response_order'] === $right['response_order']) {
                return $left['_index'] <=> $right['_index'];
            }

            return $left['response_order'] <=> $right['response_order'];
        });

        foreach ($responses as &$response) {
            unset($response['_index']);
        }
        unset($response);

        return $responses;
    }
}
