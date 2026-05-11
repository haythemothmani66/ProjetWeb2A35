<?php
require_once __DIR__ . '/../../../config/database.php';

class ResponseRepository
{
    private $db;

    public function __construct()
    {
        $this->db = Config::getConnexion();
    }

    public function create(array $data)
    {
        $stmt = $this->db->prepare(
            'INSERT INTO responses (question_id, response_text, is_correct, response_order, explanation)
             VALUES (:question_id, :response_text, :is_correct, :response_order, :explanation)'
        );
        $stmt->execute([
            'question_id' => $data['question_id'],
            'response_text' => $data['response_text'],
            'is_correct' => $data['is_correct'] ?? 0,
            'response_order' => $data['response_order'] ?? 1,
            'explanation' => $data['explanation'] ?? null,
        ]);
        return $this->db->lastInsertId();
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

    public function replaceByQuestionId($questionId, array $responses)
    {
        $deleteStmt = $this->db->prepare('DELETE FROM responses WHERE question_id = :question_id');
        $deleteStmt->execute(['question_id' => $questionId]);

        if (empty($responses)) {
            return true;
        }

        $insertStmt = $this->db->prepare(
            'INSERT INTO responses (question_id, response_text, is_correct, response_order, explanation)
             VALUES (:question_id, :response_text, :is_correct, :response_order, :explanation)'
        );

        foreach ($responses as $response) {
            $insertStmt->execute([
                'question_id' => $questionId,
                'response_text' => $response['response_text'],
                'is_correct' => $response['is_correct'],
                'response_order' => $response['response_order'],
                'explanation' => $response['explanation'] ?? null,
            ]);
        }

        return true;
    }
}
