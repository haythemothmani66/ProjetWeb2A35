<?php

class BackofficeLessonsController
{
    private CourseRepository $courses;
    private LessonRepository $lessons;

    private string $viewsPath;

    public function __construct()
    {
        $this->courses = new CourseRepository();
        $this->lessons = new LessonRepository();
        $this->viewsPath = dirname(dirname(__DIR__)) . '/view/backoffice/src/pages/backoffice/quiz';
    }

    public function index(array $params = []): void
    {
        $courseId = isset($params['course_id']) ? (int) $params['course_id'] : 0;
        $course = $this->courses->getById($courseId);

        if (!$course) {
            http_response_code(404);
            echo 'Course not found';
            return;
        }

        $lessons = $this->lessons->getByCourseId($courseId);
        require $this->viewsPath . '/lessons/index.php';
    }

    public function create(array $params = []): void
    {
        $courseId = isset($params['course_id']) ? (int) $params['course_id'] : 0;
        $course = $this->courses->getById($courseId);

        if (!$course) {
            http_response_code(404);
            echo 'Course not found';
            return;
        }

        $lesson = [
            'course_id' => $courseId,
            'title' => '',
            'summary' => '',
            'content' => '',
            'duration_minutes' => 10,
            'lesson_order' => count($this->lessons->getByCourseId($courseId)) + 1,
            'status' => 'draft',
        ];

        require $this->viewsPath . '/lessons/create.php';
    }

    public function store(array $data = []): void
    {
        $courseId = isset($data['course_id']) ? (int) $data['course_id'] : 0;
        $course = $this->courses->getById($courseId);

        if (!$course) {
            http_response_code(404);
            echo 'Course not found';
            return;
        }

        $this->lessons->create($this->normalizeLessonData($data, $courseId));
        header('Location: ' . backofficeRoute('lessons', 'index', ['course_id' => $courseId]));
        exit;
    }

    public function edit(array $params = []): void
    {
        $id = isset($params['id']) ? (int) $params['id'] : 0;
        $lesson = $this->lessons->getById($id);

        if (!$lesson) {
            http_response_code(404);
            echo 'Lesson not found';
            return;
        }

        $course = $this->courses->getById((int) $lesson['course_id']);

        if (!$course) {
            http_response_code(404);
            echo 'Course not found';
            return;
        }

        require $this->viewsPath . '/lessons/edit.php';
    }

    public function update(array $data = []): void
    {
        $id = isset($data['id']) ? (int) $data['id'] : 0;
        $lesson = $this->lessons->getById($id);

        if (!$lesson) {
            http_response_code(404);
            echo 'Lesson not found';
            return;
        }

        $courseId = (int) $lesson['course_id'];
        $course = $this->courses->getById($courseId);

        if (!$course) {
            http_response_code(404);
            echo 'Course not found';
            return;
        }

        $this->lessons->update($id, $this->normalizeLessonData($data, $courseId));
        header('Location: ' . backofficeRoute('lessons', 'index', ['course_id' => $courseId]));
        exit;
    }

    public function delete(array $params = []): void
    {
        $id = isset($params['id']) ? (int) $params['id'] : 0;
        $lesson = $this->lessons->getById($id);

        if ($lesson) {
            $courseId = (int) $lesson['course_id'];
            $this->lessons->delete($id);
            header('Location: ' . backofficeRoute('lessons', 'index', ['course_id' => $courseId]));
            exit;
        }

        header('Location: ' . backofficeRoute('courses', 'index'));
        exit;
    }

    private function normalizeLessonData(array $data, int $courseId): array
    {
        $duration = isset($data['duration_minutes']) ? (int) $data['duration_minutes'] : 10;
        $order = isset($data['lesson_order']) ? (int) $data['lesson_order'] : 1;
        $status = (string) ($data['status'] ?? 'draft');

        if (!in_array($status, ['draft', 'published'], true)) {
            $status = 'draft';
        }

        return [
            'course_id' => $courseId,
            'title' => trim((string) ($data['title'] ?? '')),
            'summary' => trim((string) ($data['summary'] ?? '')),
            'content' => trim((string) ($data['content'] ?? '')),
            'duration_minutes' => $duration > 0 ? $duration : 10,
            'lesson_order' => $order > 0 ? $order : 1,
            'status' => $status,
        ];
    }
}
