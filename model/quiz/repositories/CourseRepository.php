<?php
require_once __DIR__ . '/../../../config/database.php';

class CourseRepository {
    private $db;

    public function __construct() {
        $this->db = Config::getConnexion();
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM courses ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM courses WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $sql = "INSERT INTO courses (title, description, image, level, status) VALUES (:title, :description, :image, :level, :status)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'image' => $data['image'] ?? null,
            'level' => $data['level'] ?? 'beginner',
            'status' => $data['status'] ?? 'draft'
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $sql = "UPDATE courses SET title = :title, description = :description, image = :image, level = :level, status = :status WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'image' => $data['image'] ?? null,
            'level' => $data['level'] ?? 'beginner',
            'status' => $data['status'] ?? 'draft'
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM courses WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
