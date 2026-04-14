<?php
require_once __DIR__ . '/../config/Database.php';

class ResponseRepository
{
    private $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function getByQuestionId($questionId)
    {
        $stmt = $this->db->prepare('SELECT * FROM responses WHERE question_id = :question_id ORDER BY response_order ASC, id ASC');
        $stmt->execute(['question_id' => $questionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCorrectByQuestionId($questionId)
    {
        $stmt = $this->db->prepare('SELECT * FROM responses WHERE question_id = :question_id AND is_correct = 1 ORDER BY response_order ASC, id ASC');
        $stmt->execute(['question_id' => $questionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
