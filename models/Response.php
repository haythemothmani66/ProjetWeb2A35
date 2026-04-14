<?php
class Response {
    public $id;
    public $question_id;
    public $response_text;
    public $is_correct;
    public $response_order;
    public $explanation;

    public function __construct($data = []) {
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->question_id = $data['question_id'] ?? null;
            $this->response_text = $data['response_text'] ?? null;
            $this->is_correct = $data['is_correct'] ?? 0;
            $this->response_order = $data['response_order'] ?? 1;
            $this->explanation = $data['explanation'] ?? null;
        }
    }
}
