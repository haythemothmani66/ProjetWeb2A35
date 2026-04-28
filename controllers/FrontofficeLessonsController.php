<?php

class FrontofficeLessonsController
{
    private LessonRepository $lessons;
    private CourseRepository $courses;
    private QuizRepository $quizzes;

    private string $viewsPath;

    public function __construct()
    {
        $this->lessons = new LessonRepository();
        $this->courses = new CourseRepository();
        $this->quizzes = new QuizRepository();
        $this->viewsPath = dirname(__DIR__) . '/views';
    }

    public function show(array $params = []): void
    {
        $id = isset($params['id']) ? (int) $params['id'] : 0;
        $lesson = $this->lessons->getById($id);

        if (!$lesson || ($lesson['status'] ?? 'draft') !== 'published') {
            http_response_code(404);
            echo 'Lesson not found';
            return;
        }

        $course = $this->courses->getById((int) $lesson['course_id']);

        if (!$course || ($course['status'] ?? 'draft') !== 'published') {
            http_response_code(404);
            echo 'Course not found';
            return;
        }

        $lessons = $this->lessons->getPublishedByCourseId((int) $course['id']);
        $quizzes = $this->quizzes->getByCourseId((int) $course['id']);
        $previousLesson = null;
        $nextLesson = null;

        foreach ($lessons as $index => $courseLesson) {
            if ((int) $courseLesson['id'] !== (int) $lesson['id']) {
                continue;
            }

            $previousLesson = $index > 0 ? $lessons[$index - 1] : null;
            $nextLesson = isset($lessons[$index + 1]) ? $lessons[$index + 1] : null;
            break;
        }

        require $this->viewsPath . '/frontoffice/lessons/show.php';
    }
}
