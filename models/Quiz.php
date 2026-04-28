<?php
class Quiz {
    public $id;
    public $course_id;
    public $title;
    public $description;
    public $duration_minutes;
    public $passing_score;
    public $max_attempts;
    public $is_timed;
    public $created_at;
    public $updated_at;

    public function __construct($data = []) {
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->course_id = $data['course_id'] ?? null;
            $this->title = $data['title'] ?? null;
            $this->description = $data['description'] ?? null;
            $this->duration_minutes = $data['duration_minutes'] ?? 30;
            $this->passing_score = $data['passing_score'] ?? 50.00;
            $this->max_attempts = $data['max_attempts'] ?? 1;
            $this->is_timed = $data['is_timed'] ?? 1;
            $this->created_at = $data['created_at'] ?? null;
            $this->updated_at = $data['updated_at'] ?? null;
        }
    }
}
