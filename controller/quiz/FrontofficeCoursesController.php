<?php

class FrontofficeCoursesController
{
    private CourseRepository $courses;
    private LessonRepository $lessons;
    private QuizRepository $quizzes;

    private string $viewsPath;

    private const LEVEL_ORDER = [
        'beginner' => 1,
        'intermediate' => 2,
        'advanced' => 3,
    ];

    public function __construct()
    {
        $this->courses = new CourseRepository();
        $this->lessons = new LessonRepository();
        $this->quizzes = new QuizRepository();
        $this->viewsPath = dirname(dirname(__DIR__)) . '/view/frontoffice/quiz';
    }

    public function index(array $params = []): void
    {
        $courses = $this->courses->getAll();
        $courses = array_values(array_filter($courses, static fn ($c) => ($c['status'] ?? 'draft') === 'published'));

        $q = is_string($params['q'] ?? null) ? trim((string) $params['q']) : '';
        if ($q !== '') {
            $courses = array_values(array_filter($courses, static function (array $c) use ($q): bool {
                $title = (string) ($c['title'] ?? '');
                return stripos($title, $q) !== false;
            }));
        }

        $sort = is_string($params['sort'] ?? null) ? trim((string) $params['sort']) : '';
        $sort = in_array($sort, ['level', 'name'], true) ? $sort : 'default';

        if ($sort === 'name') {
            usort($courses, static function (array $a, array $b): int {
                return strcasecmp((string) ($a['title'] ?? ''), (string) ($b['title'] ?? ''));
            });
        } elseif ($sort === 'level') {
            $order = self::LEVEL_ORDER;
            usort($courses, static function (array $a, array $b) use ($order): int {
                $aLevel = strtolower((string) ($a['level'] ?? ''));
                $bLevel = strtolower((string) ($b['level'] ?? ''));

                $aWeight = $order[$aLevel] ?? 99;
                $bWeight = $order[$bLevel] ?? 99;

                if ($aWeight !== $bWeight) {
                    return $aWeight <=> $bWeight;
                }

                return strcasecmp((string) ($a['title'] ?? ''), (string) ($b['title'] ?? ''));
            });
        }

        require $this->viewsPath . '/courses/index.php';
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

        $lessons = $this->lessons->getPublishedByCourseId($id);
        $quizzes = $this->quizzes->getByCourseId($id);
        require $this->viewsPath . '/courses/show.php';
    }

    public function pdf(array $params = []): void
    {
        $id = isset($params['id']) ? (int) $params['id'] : 0;
        $course = $this->courses->getById($id);

        if (!$course || ($course['status'] ?? 'draft') !== 'published') {
            http_response_code(404);
            echo 'Course not found';
            return;
        }

        $pdf = CoursePdfGenerator::build($course);
        $title = (string) ($course['title'] ?? 'course');
        $safeTitle = preg_replace('/[^a-z0-9]+/i', '-', $title);
        $safeTitle = trim((string) $safeTitle, '-');
        $filename = ($safeTitle !== '' ? $safeTitle : 'course') . "-{$id}.pdf";

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($pdf));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        echo $pdf;
        exit;
    }
}
