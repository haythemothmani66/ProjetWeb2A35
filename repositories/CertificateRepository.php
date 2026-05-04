<?php

class CertificateRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function getAll(): array
    {
        $stmt = $this->db->query("
            SELECT c.*, co.title as course_title, q.title as quiz_title 
            FROM certificates c
            LEFT JOIN courses co ON c.course_id = co.id
            LEFT JOIN quizzes q ON c.quiz_id = q.id
            ORDER BY c.issued_at DESC
        ");
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT c.*, co.title as course_title, q.title as quiz_title 
            FROM certificates c
            LEFT JOIN courses co ON c.course_id = co.id
            LEFT JOIN quizzes q ON c.quiz_id = q.id
            WHERE c.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $cert = $stmt->fetch();
        $stmt->closeCursor();
        return $cert ?: null;
    }

    public function getByQuizAndName(int $quizId, string $studentName): ?array
    {
        $stmt = $this->db->prepare("
            SELECT c.*, co.title as course_title, q.title as quiz_title 
            FROM certificates c
            LEFT JOIN courses co ON c.course_id = co.id
            LEFT JOIN quizzes q ON c.quiz_id = q.id
            WHERE c.quiz_id = :quiz_id AND c.student_name = :student_name
        ");
        $stmt->execute([
            'quiz_id' => $quizId,
            'student_name' => $studentName
        ]);
        $cert = $stmt->fetch();
        $stmt->closeCursor();
        return $cert ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO certificates (course_id, quiz_id, student_name)
            VALUES (:course_id, :quiz_id, :student_name)
        ");
        $stmt->execute([
            'course_id' => $data['course_id'],
            'quiz_id' => $data['quiz_id'],
            'student_name' => $data['student_name']
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM certificates WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
