<?php
require_once __DIR__ . '/../config/Database.php';

class QuizRepository {
    private $db;

    public function __construct() {
        $this->db = Database::connection();
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM quizzes ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByCourseId($course_id) {
        $stmt = $this->db->prepare("SELECT * FROM quizzes WHERE course_id = :course_id ORDER BY id DESC");
        $stmt->execute(['course_id' => $course_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM quizzes WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $sql = "INSERT INTO quizzes (course_id, title, description, duration_minutes, passing_score, max_attempts, is_timed) 
                VALUES (:course_id, :title, :description, :duration_minutes, :passing_score, :max_attempts, :is_timed)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'course_id' => $data['course_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'duration_minutes' => $data['duration_minutes'] ?? 30,
            'passing_score' => $data['passing_score'] ?? 50.00,
            'max_attempts' => $data['max_attempts'] ?? 1,
            'is_timed' => $data['is_timed'] ?? 1
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $sql = "UPDATE quizzes SET course_id = :course_id, title = :title, description = :description, 
                duration_minutes = :duration_minutes, passing_score = :passing_score, 
                max_attempts = :max_attempts, is_timed = :is_timed WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'course_id' => $data['course_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'duration_minutes' => $data['duration_minutes'] ?? 30,
            'passing_score' => $data['passing_score'] ?? 50.00,
            'max_attempts' => $data['max_attempts'] ?? 1,
            'is_timed' => $data['is_timed'] ?? 1
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM quizzes WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
