<?php
require_once __DIR__ . '/../../../config/database.php';

class LessonRepository {
    private $db;

    public function __construct() {
        $this->db = Config::getConnexion();
    }

    public function getByCourseId($courseId) {
        $stmt = $this->db->prepare("SELECT * FROM lessons WHERE course_id = :course_id ORDER BY lesson_order ASC, id ASC");
        $stmt->execute(['course_id' => $courseId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPublishedByCourseId($courseId) {
        $stmt = $this->db->prepare("SELECT * FROM lessons WHERE course_id = :course_id AND status = 'published' ORDER BY lesson_order ASC, id ASC");
        $stmt->execute(['course_id' => $courseId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM lessons WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->db->prepare(
            "INSERT INTO lessons (course_id, title, summary, content, duration_minutes, lesson_order, status)
             VALUES (:course_id, :title, :summary, :content, :duration_minutes, :lesson_order, :status)"
        );

        $stmt->execute([
            'course_id' => $data['course_id'],
            'title' => $data['title'],
            'summary' => $data['summary'] ?? null,
            'content' => $data['content'] ?? null,
            'duration_minutes' => $data['duration_minutes'] ?? 10,
            'lesson_order' => $data['lesson_order'] ?? 1,
            'status' => $data['status'] ?? 'draft',
        ]);

        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare(
            "UPDATE lessons
             SET title = :title,
                 summary = :summary,
                 content = :content,
                 duration_minutes = :duration_minutes,
                 lesson_order = :lesson_order,
                 status = :status
             WHERE id = :id"
        );

        return $stmt->execute([
            'id' => $id,
            'title' => $data['title'],
            'summary' => $data['summary'] ?? null,
            'content' => $data['content'] ?? null,
            'duration_minutes' => $data['duration_minutes'] ?? 10,
            'lesson_order' => $data['lesson_order'] ?? 1,
            'status' => $data['status'] ?? 'draft',
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM lessons WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
