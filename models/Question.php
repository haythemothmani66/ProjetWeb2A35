<?php
class Question {
    public $id;
    public $quiz_id;
    public $question_text;
    public $question_type;
    public $points;
    public $question_order;
    public $explanation;
    public $created_at;
    public $updated_at;

    public function __construct($data = []) {
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
}
