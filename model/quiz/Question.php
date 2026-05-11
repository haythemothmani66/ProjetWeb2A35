<?php
class Question
{
    private $id;
    private $quiz_id;
    private $question_text;
    private $question_type;
    private $points;
    private $question_order;
    private $explanation;
    private $created_at;
    private $updated_at;

    public function __construct($data = [])
    {
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->quiz_id = $data['quiz_id'] ?? null;
            $this->question_text = $data['question_text'] ?? null;
            $this->question_type = $data['question_type'] ?? 'single_choice';
            $this->points = $data['points'] ?? 1.00;
            $this->question_order = $data['question_order'] ?? 1;
            $this->explanation = $data['explanation'] ?? null;
            $this->created_at = $data['created_at'] ?? null;
            $this->updated_at = $data['updated_at'] ?? null;
        }
    }

    // Getters
    public function getId()
    {
        return $this->id;
    }

    public function getQuizId()
    {
        return $this->quiz_id;
    }

    public function getQuestionText()
    {
        return $this->question_text;
    }

    public function getQuestionType()
    {
        return $this->question_type;
    }

    public function getPoints()
    {
        return $this->points;
    }

    public function getQuestionOrder()
    {
        return $this->question_order;
    }

    public function getExplanation()
    {
        return $this->explanation;
    }

    public function getCreatedAt()
    {
        return $this->created_at;
    }

    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    // Setters
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setQuizId($quiz_id)
    {
        $this->quiz_id = $quiz_id;
        return $this;
    }

    public function setQuestionText($question_text)
    {
        $this->question_text = $question_text;
        return $this;
    }

    public function setQuestionType($question_type)
    {
        $validTypes = ['single_choice', 'multiple_choice', 'true_false', 'text', 'matching'];
        if (in_array($question_type, $validTypes)) {
            $this->question_type = $question_type;
        }
        return $this;
    }

    public function setPoints($points)
    {
        // Ensure points is a positive number (can be decimal)
        $points = floatval($points);
        $this->points = $points > 0 ? $points : 0;
        return $this;
    }

    public function setQuestionOrder($question_order)
    {
        // Ensure question order is a positive integer
        $order = (int)$question_order;
        $this->question_order = $order > 0 ? $order : 1;
        return $this;
    }

    public function setExplanation($explanation)
    {
        $this->explanation = $explanation;
        return $this;
    }

    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;
        return $this;
    }
}
