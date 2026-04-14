<?php
class Course {
    public $id;
    public $title;
    public $description;
    public $image;
    public $level;
    public $status;
    public $created_at;
    public $updated_at;

    public function __construct($data = []) {
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->title = $data['title'] ?? null;
            $this->description = $data['description'] ?? null;
            $this->image = $data['image'] ?? null;
            $this->level = $data['level'] ?? 'beginner';
            $this->status = $data['status'] ?? 'draft';
            $this->created_at = $data['created_at'] ?? null;
            $this->updated_at = $data['updated_at'] ?? null;
        }
    }
}
