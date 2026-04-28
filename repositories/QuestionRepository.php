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

    public function create($data)
    {
        $stmt = $this->db->prepare(
            'INSERT INTO questions (quiz_id, question_text, question_type, points, question_order, explanation)
             VALUES (:quiz_id, :question_text, :question_type, :points, :question_order, :explanation)'
        );

        $stmt->execute([
            'quiz_id' => $data['quiz_id'],
            'question_text' => $data['question_text'],
            'question_type' => $data['question_type'],
            'points' => $data['points'],
            'question_order' => $data['question_order'],
            'explanation' => $data['explanation'] ?? null,
        ]);

        return $this->db->lastInsertId();
    }

    public function update($id, $data)
    {
        $stmt = $this->db->prepare(
            'UPDATE questions
             SET question_text = :question_text,
                 question_type = :question_type,
                 points = :points,
                 question_order = :question_order,
                 explanation = :explanation
             WHERE id = :id'
        );

        return $stmt->execute([
            'id' => $id,
            'question_text' => $data['question_text'],
            'question_type' => $data['question_type'],
            'points' => $data['points'],
            'question_order' => $data['question_order'],
            'explanation' => $data['explanation'] ?? null,
        ]);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare('DELETE FROM questions WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
