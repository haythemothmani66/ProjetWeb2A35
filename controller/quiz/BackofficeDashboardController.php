<?php

/**
 * Backoffice Dashboard Controller
 * Handles the admin dashboard and main backoffice landing page
 */
class BackofficeDashboardController
{
    private CourseRepository $courses;
    private QuizRepository $quizzes;
    private string $viewsPath;

    public function __construct()
    {
        $this->courses = new CourseRepository();
        $this->quizzes = new QuizRepository();
        $this->viewsPath = dirname(dirname(__DIR__)) . '/view/backoffice/src/pages/backoffice/quiz';
    }

    /**
     * Display the dashboard
     */
    public function index(array $params = []): void
    {
        $stats = [
            'totalCourses' => count($this->courses->getAll()),
            'totalQuizzes' => count($this->quizzes->getAll()),
            'publishedCourses' => count(array_filter(
                $this->courses->getAll(),
                static fn ($c) => ($c['status'] ?? 'draft') === 'published'
            )),
        ];

        require $this->viewsPath . '/dashboard/index.php';
    }
}
