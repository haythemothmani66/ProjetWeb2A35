<?php

class BackofficeCoursesController
{
    private CourseRepository $courses;
    private QuizRepository $quizzes;
    private LessonRepository $lessons;
    private QuestionRepository $questions;
    private ResponseRepository $responses;

    private string $viewsPath;
    private string $uploadsDir;

    private const LEVEL_LABELS = [
        'beginner' => 'Beginner',
        'intermediate' => 'Intermediate',
        'advanced' => 'Advanced',
    ];

    public function __construct()
    {
        $this->courses = new CourseRepository();
        $this->quizzes = new QuizRepository();
        $this->lessons = new LessonRepository();
        $this->questions = new QuestionRepository();
        $this->responses = new ResponseRepository();
        $this->viewsPath = dirname(__DIR__) . '/views';
        $this->uploadsDir = dirname(__DIR__) . '/uploads/courses';
        
        // Create uploads directory if it doesn't exist
        if (!is_dir($this->uploadsDir)) {
            @mkdir($this->uploadsDir, 0755, true);
        }
    }

    /**
     * Handle image file upload
     */
    private function handleImageUpload(array $file): ?string
    {
        error_log('handleImageUpload called with file: ' . print_r($file, true));

        if (empty($file['name'])) {
            error_log('No file name provided');
            return null;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
                UPLOAD_ERR_PARTIAL => 'File only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
            ];
            $errorMsg = $errorMessages[$file['error']] ?? 'Unknown upload error';
            error_log('Upload error: ' . $errorMsg . ' (code: ' . $file['error'] . ')');
            return null;
        }

        // Validate file type
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        error_log('Detected MIME type: ' . $mimeType);

        if (!in_array($mimeType, $allowedMimes)) {
            error_log('Invalid MIME type: ' . $mimeType);
            return null;
        }

        // Validate file size (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            error_log('File too large: ' . $file['size'] . ' bytes');
            return null;
        }

        // Generate unique filename
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'course_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $filepath = $this->uploadsDir . '/' . $filename;

        error_log('Target path: ' . $filepath);
        error_log('Uploads dir exists: ' . (is_dir($this->uploadsDir) ? 'yes' : 'no'));
        error_log('Uploads dir writable: ' . (is_writable($this->uploadsDir) ? 'yes' : 'no'));

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            $relativePath = 'uploads/courses/' . $filename;
            error_log('File moved successfully to: ' . $relativePath);
            return $relativePath;
        }

        error_log('Failed to move uploaded file. Source exists: ' . (file_exists($file['tmp_name']) ? 'yes' : 'no'));
        return null;
    }

    public function index(array $params = []): void
    {
        $courses = $this->courses->getAll();
        require $this->viewsPath . '/backoffice/courses/index.php';
    }

    public function create(array $params = []): void
    {
        require $this->viewsPath . '/backoffice/courses/create.php';
    }

    public function store(array $data = []): void
    {
        // Debug: Log what we receive
        error_log('BackofficeCoursesController::store called');
        error_log('POST data: ' . print_r($data, true));
        error_log('FILES data: ' . print_r($_FILES, true));

        // Handle image upload if file was provided
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            error_log('Processing image upload...');
            $imagePath = $this->handleImageUpload($_FILES['image']);
            if ($imagePath) {
                $data['image'] = $imagePath;
                error_log('Image uploaded successfully: ' . $imagePath);
            } else {
                error_log('Image upload failed - handleImageUpload returned null');
            }
        } else {
            error_log('No image file provided or UPLOAD_ERR_NO_FILE');
        }

        $id = $this->courses->create($data);
        error_log('Course created with ID: ' . $id . ', data: ' . print_r($data, true));
        header('Location: ' . backofficeRoute('courses', 'index'));
        exit;
    }

    public function edit(array $params = []): void
    {
        $id = isset($params['id']) ? (int) $params['id'] : 0;
        $course = $this->courses->getById($id);

        if (!$course) {
            http_response_code(404);
            echo 'Course not found';
            return;
        }

        require $this->viewsPath . '/backoffice/courses/edit.php';
    }

    public function update(array $data = []): void
    {
        $id = isset($data['id']) ? (int) $data['id'] : 0;
        $course = $this->courses->getById($id);

        if (!$course) {
            http_response_code(404);
            echo 'Course not found';
            return;
        }

        // Handle image upload if file was provided
        if (!empty($_FILES['image'])) {
            $imagePath = $this->handleImageUpload($_FILES['image']);
            if ($imagePath) {
                // Delete old image if exists
                if (!empty($course['image']) && file_exists(dirname(__DIR__) . '/' . $course['image'])) {
                    @unlink(dirname(__DIR__) . '/' . $course['image']);
                }
                $data['image'] = $imagePath;
            }
        }

        $this->courses->update($id, $data);
        header('Location: ' . backofficeRoute('courses', 'index'));
        exit;
    }

    public function delete(array $params = []): void
    {
        $id = isset($params['id']) ? (int) $params['id'] : 0;
        $course = $this->courses->getById($id);

        if ($course) {
            // Delete image file if exists
            if (!empty($course['image']) && file_exists(dirname(__DIR__) . '/' . $course['image'])) {
                @unlink(dirname(__DIR__) . '/' . $course['image']);
            }
        }

        $this->courses->delete($id);
        header('Location: ' . backofficeRoute('courses', 'index'));
        exit;
    }

    public function quizzes(array $params = []): void
    {
        $courseId = isset($params['course_id']) ? (int) $params['course_id'] : 0;
        $course = $this->courses->getById($courseId);

        if (!$course) {
            http_response_code(404);
            echo 'Course not found';
            return;
        }

        $quizzes = $this->quizzes->getByCourseId($courseId);
        require $this->viewsPath . '/backoffice/quizzes/index.php';
    }

    public function stats(array $params = []): void
    {
        $courses = $this->courses->getAll();

        $levelCounts = [
            'beginner' => 0,
            'intermediate' => 0,
            'advanced' => 0,
            'other' => 0,
        ];

        foreach ($courses as $course) {
            $level = strtolower(trim((string) ($course['level'] ?? 'beginner')));
            if (isset(self::LEVEL_LABELS[$level])) {
                $levelCounts[$level]++;
            } else {
                $levelCounts['other']++;
            }
        }

        $totalCourses = count($courses);
        $levelPercents = [];
        foreach ($levelCounts as $key => $count) {
            $levelPercents[$key] = $totalCourses > 0 ? round(($count / $totalCourses) * 100, 1) : 0.0;
        }

        $quizCountsByCourseId = $this->quizzes->getQuizCountsByCourseId();
        $topQuizCourses = [];
        foreach ($courses as $course) {
            $courseId = (int) ($course['id'] ?? 0);
            if ($courseId <= 0) {
                continue;
            }

            $topQuizCourses[] = [
                'id' => $courseId,
                'title' => (string) ($course['title'] ?? ''),
                'level' => (string) ($course['level'] ?? 'beginner'),
                'quiz_count' => (int) ($quizCountsByCourseId[$courseId] ?? 0),
                'status' => (string) ($course['status'] ?? 'draft'),
            ];
        }

        usort($topQuizCourses, static function (array $a, array $b): int {
            if (($b['quiz_count'] ?? 0) !== ($a['quiz_count'] ?? 0)) {
                return (int) ($b['quiz_count'] ?? 0) <=> (int) ($a['quiz_count'] ?? 0);
            }
            return strcasecmp((string) ($a['title'] ?? ''), (string) ($b['title'] ?? ''));
        });

        $topQuizCourses = array_slice($topQuizCourses, 0, 10);

        require $this->viewsPath . '/backoffice/courses/stats.php';
    }

    public function generate(array $params = []): void
    {
        require $this->viewsPath . '/backoffice/courses/generate.php';
    }

    public function storeAi(array $data = []): void
    {
        $topic = $data['topic'] ?? '';
        $level = $data['level'] ?? 'beginner';

        if (empty($topic)) {
            // Re-render form with error
            $error = "Topic is required.";
            require $this->viewsPath . '/backoffice/courses/generate.php';
            return;
        }

        try {
            $generator = new AiCourseGenerator();
            $aiData = $generator->generateCourse($topic, $level);

            // Save Course
            $courseId = $this->courses->create([
                'title' => $aiData['course']['title'],
                'description' => $aiData['course']['description'],
                'level' => $aiData['course']['level'],
                'status' => 'draft'
            ]);

            // Save Lessons
            if (!empty($aiData['lessons'])) {
                foreach ($aiData['lessons'] as $idx => $lessonData) {
                    $this->lessons->create([
                        'course_id' => $courseId,
                        'title' => $lessonData['title'],
                        'summary' => $lessonData['summary'],
                        'content' => $lessonData['content'],
                        'duration_minutes' => $lessonData['duration_minutes'] ?? 10,
                        'lesson_order' => $idx + 1,
                        'status' => 'published'
                    ]);
                }
            }

            // Save Quiz
            if (!empty($aiData['quiz'])) {
                $quizId = $this->quizzes->create([
                    'course_id' => $courseId,
                    'title' => $aiData['quiz']['title'],
                    'description' => $aiData['quiz']['description'],
                    'duration_minutes' => $aiData['quiz']['duration_minutes'] ?? 20,
                    'passing_score' => $aiData['quiz']['passing_score'] ?? 60,
                    'max_attempts' => 3,
                    'is_timed' => 1
                ]);

                // Save Questions and Responses
                if (!empty($aiData['quiz']['questions'])) {
                    foreach ($aiData['quiz']['questions'] as $qIdx => $questionData) {
                        $questionId = $this->questions->create([
                            'quiz_id' => $quizId,
                            'question_text' => $questionData['question_text'],
                            'question_type' => $questionData['question_type'] ?? 'multiple_choice',
                            'points' => $questionData['points'] ?? 1,
                            'question_order' => $qIdx + 1
                        ]);

                        if (!empty($questionData['responses'])) {
                            foreach ($questionData['responses'] as $rIdx => $responseData) {
                                $this->responses->create([
                                    'question_id' => $questionId,
                                    'response_text' => $responseData['response_text'],
                                    'is_correct' => $responseData['is_correct'] ?? 0,
                                    'response_order' => $rIdx + 1
                                ]);
                            }
                        }
                    }
                }
            }

            header('Location: ' . backofficeRoute('courses', 'index'));
            exit;

        } catch (Exception $e) {
            $error = $e->getMessage();
            require $this->viewsPath . '/backoffice/courses/generate.php';
        }
    }
}
