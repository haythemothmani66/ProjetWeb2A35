<?php

class FrontofficeCertificatesController
{
    private CertificateRepository $certificates;
    private QuizRepository $quizzes;
    private CourseRepository $courses;
    private string $viewsPath;

    public function __construct()
    {
        $this->certificates = new CertificateRepository();
        $this->quizzes = new QuizRepository();
        $this->courses = new CourseRepository();
        $this->viewsPath = dirname(dirname(__DIR__)) . '/view/frontoffice/quiz';

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public function generate(array $data = []): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            return;
        }

        $quizId = isset($data['quiz_id']) ? (int) $data['quiz_id'] : 0;
        $studentName = trim($data['student_name'] ?? '');

        if (!$quizId || empty($studentName)) {
            http_response_code(400);
            echo 'Missing required fields';
            return;
        }

        $quiz = $this->quizzes->getById($quizId);
        if (!$quiz) {
            http_response_code(404);
            echo 'Quiz not found';
            return;
        }

        $result = $_SESSION['last_quiz_result'] ?? null;
        if (!$result || (int) ($result['quiz_id'] ?? 0) !== $quizId || !$result['passed']) {
            http_response_code(403);
            echo 'You have not passed this quiz yet.';
            return;
        }

        $existing = $this->certificates->getByQuizAndName($quizId, $studentName);
        if ($existing) {
            $certId = $existing['id'];
        } else {
            $certId = $this->certificates->create([
                'course_id' => $quiz['course_id'],
                'quiz_id' => $quizId,
                'student_name' => $studentName
            ]);
        }

        header('Location: ' . frontofficeRoute('certificates', 'show', ['id' => $certId]));
        exit;
    }

    public function show(array $params = []): void
    {
        $certId = isset($params['id']) ? (int) $params['id'] : 0;
        $certificate = $this->certificates->getById($certId);

        if (!$certificate) {
            http_response_code(404);
            echo 'Certificate not found';
            return;
        }

        require $this->viewsPath . '/certificates/show.php';
    }
}
