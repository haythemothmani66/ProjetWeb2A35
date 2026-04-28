<?php
class Lesson {
    public $id;
    public $course_id;
    public $title;
    public $summary;
    public $content;
    public $duration_minutes;
    public $lesson_order;
    public $status;
    public $created_at;
    public $updated_at;

    public function __construct($data = []) {
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->course_id = $data['course_id'] ?? null;
            $this->title = $data['title'] ?? null;
            $this->summary = $data['summary'] ?? null;
            $this->content = $data['content'] ?? null;
            $this->duration_minutes = $data['duration_minutes'] ?? 10;
            $this->lesson_order = $data['lesson_order'] ?? 1;
            $this->status = $data['status'] ?? 'draft';
            $this->created_at = $data['created_at'] ?? null;
            $this->updated_at = $data['updated_at'] ?? null;
        }
    }
}
