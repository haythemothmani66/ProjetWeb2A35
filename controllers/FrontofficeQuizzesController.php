<?php

class FrontofficeQuizzesController
{
    private QuizRepository $quizzes;
    private CourseRepository $courses;
    private QuestionRepository $questions;
    private ResponseRepository $responses;

    private string $viewsPath;

    public function __construct()
    {
        $this->quizzes = new QuizRepository();
        $this->courses = new CourseRepository();
        $this->questions = new QuestionRepository();
        $this->responses = new ResponseRepository();

        $this->viewsPath = dirname(__DIR__) . '/views';

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public function take(array $params = []): void
    {
        $quizId = isset($params['id']) ? (int) $params['id'] : 0;
        $quiz = $this->quizzes->getById($quizId);

        if (!$quiz) {
            http_response_code(404);
            echo 'Quiz not found';
            return;
        }

        $course = $this->courses->getById((int) $quiz['course_id']);
        if (!$course || ($course['status'] ?? 'draft') !== 'published') {
            http_response_code(404);
            echo 'Course not found';
            return;
        }

        $questions = $this->questions->getByQuizId($quizId);
        foreach ($questions as &$q) {
            $q['responses'] = $this->responses->getByQuestionId((int) $q['id']);
        }
        unset($q);

        require $this->viewsPath . '/frontoffice/quizzes/take.php';
    }

    public function submit(array $data = []): void
    {
        $quizId = isset($data['quiz_id']) ? (int) $data['quiz_id'] : 0;
        $quiz = $this->quizzes->getById($quizId);

        if (!$quiz) {
            http_response_code(404);
            echo 'Quiz not found';
            return;
        }

        $questions = $this->questions->getByQuizId($quizId);

        $totalPoints = 0.0;
        $earnedPoints = 0.0;

        foreach ($questions as $question) {
            $points = (float) ($question['points'] ?? 1);
            $totalPoints += $points;

            $qid = (int) $question['id'];
            $type = $question['question_type'] ?? 'single_choice';

            if ($type === 'multiple_choice') {
                $selected = $data['answers'][$qid] ?? [];
                if (!is_array($selected)) {
                    $selected = [$selected];
                }
                $selected = array_map('intval', $selected);
                sort($selected);

                $correctResponses = $this->responses->getCorrectByQuestionId($qid);
                $correct = array_map(static fn ($r) => (int) $r['id'], $correctResponses);
                sort($correct);

                if ($selected === $correct) {
                    $earnedPoints += $points;
                }
            } else {
                $selectedId = isset($data['answers'][$qid]) ? (int) $data['answers'][$qid] : 0;
                if ($selectedId) {
                    $allResponses = $this->responses->getByQuestionId($qid);
                    foreach ($allResponses as $r) {
                        if ((int) $r['id'] === $selectedId && (int) $r['is_correct'] === 1) {
                            $earnedPoints += $points;
                            break;
                        }
                    }
                }
            }
        }

        $percent = $totalPoints > 0 ? ($earnedPoints / $totalPoints) * 100.0 : 0.0;
        $passed = $percent >= (float) ($quiz['passing_score'] ?? 50);

        $_SESSION['last_quiz_result'] = [
            'quiz_id' => $quizId,
            'earned' => $earnedPoints,
            'total' => $totalPoints,
            'percent' => $percent,
            'passed' => $passed,
        ];

        header('Location: index.php?route=frontoffice/quizzes/result&id=' . $quizId);
        exit;
    }

    public function result(array $params = []): void
    {
        $quizId = isset($params['id']) ? (int) $params['id'] : 0;
        $quiz = $this->quizzes->getById($quizId);

        if (!$quiz) {
            http_response_code(404);
            echo 'Quiz not found';
            return;
        }

        $course = $this->courses->getById((int) $quiz['course_id']);
        $result = $_SESSION['last_quiz_result'] ?? null;

        if (!$result || (int) ($result['quiz_id'] ?? 0) !== $quizId) {
            header('Location: index.php?route=frontoffice/quizzes/take&id=' . $quizId);
            exit;
        }

        require $this->viewsPath . '/frontoffice/quizzes/result.php';
    }
}
