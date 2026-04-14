<?php

class FrontofficeCoursesController
{
    private CourseRepository $courses;
    private QuizRepository $quizzes;

    private string $viewsPath;

    public function __construct()
    {
        $this->courses = new CourseRepository();
        $this->quizzes = new QuizRepository();
        $this->viewsPath = dirname(__DIR__) . '/views';
    }

    public function index(array $params = []): void
    {
        $courses = $this->courses->getAll();
        $courses = array_values(array_filter($courses, static fn ($c) => ($c['status'] ?? 'draft') === 'published'));
        require $this->viewsPath . '/frontoffice/courses/index.php';
    }

    public function show(array $params = []): void
    {
        $id = isset($params['id']) ? (int) $params['id'] : 0;
        $course = $this->courses->getById($id);

        if (!$course || ($course['status'] ?? 'draft') !== 'published') {
            http_response_code(404);
            echo 'Course not found';
            return;
        }

        $quizzes = $this->quizzes->getByCourseId($id);
        require $this->viewsPath . '/frontoffice/courses/show.php';
    }
}
