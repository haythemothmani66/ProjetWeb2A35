<?php

class BackofficeQuizzesController
{
    private QuizRepository $quizzes;
    private CourseRepository $courses;

    private string $viewsPath;

    public function __construct()
    {
        $this->quizzes = new QuizRepository();
        $this->courses = new CourseRepository();
        $this->viewsPath = dirname(__DIR__) . '/views';
    }

    public function create(array $params = []): void
    {
        $courses = $this->courses->getAll();
        $courseId = isset($params['course_id']) ? (int) $params['course_id'] : null;
        require $this->viewsPath . '/backoffice/quizzes/create.php';
    }

    public function store(array $data = []): void
    {
        $courseId = isset($data['course_id']) ? (int) $data['course_id'] : 0;
        $this->quizzes->create($data);
        header('Location: ' . backofficeRoute('courses', 'quizzes', ['course_id' => $courseId]));
        exit;
    }

    public function edit(array $params = []): void
    {
        $id = isset($params['id']) ? (int) $params['id'] : 0;
        $quiz = $this->quizzes->getById($id);

        if (!$quiz) {
            http_response_code(404);
            echo 'Quiz not found';
            return;
        }

        $courses = $this->courses->getAll();
        require $this->viewsPath . '/backoffice/quizzes/edit.php';
    }

    public function update(array $data = []): void
    {
        $id = isset($data['id']) ? (int) $data['id'] : 0;
        $quiz = $this->quizzes->getById($id);

        if (!$quiz) {
            http_response_code(404);
            echo 'Quiz not found';
            return;
        }

        $this->quizzes->update($id, $data);
        $courseId = isset($data['course_id']) ? (int) $data['course_id'] : (int) $quiz['course_id'];
        header('Location: ' . backofficeRoute('courses', 'quizzes', ['course_id' => $courseId]));
        exit;
    }

    public function delete(array $params = []): void
    {
        $id = isset($params['id']) ? (int) $params['id'] : 0;
        $quiz = $this->quizzes->getById($id);

        if ($quiz) {
            $this->quizzes->delete($id);
            header('Location: ' . backofficeRoute('courses', 'quizzes', ['course_id' => (int) $quiz['course_id']]));
            exit;
        }

        header('Location: ' . backofficeRoute('courses', 'index'));
        exit;
    }
}
