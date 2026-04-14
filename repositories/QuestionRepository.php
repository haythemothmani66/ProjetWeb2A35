<?php
require_once __DIR__ . '/../config/Database.php';

class QuestionRepository
{
    private $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function getByQuizId($quizId)
    {
        $stmt = $this->db->prepare('SELECT * FROM questions WHERE quiz_id = :quiz_id ORDER BY question_order ASC, id ASC');
        $stmt->execute(['quiz_id' => $quizId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare('SELECT * FROM questions WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
