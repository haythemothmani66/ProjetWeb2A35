<?php
class Quiz
{
    private $id;
    private $course_id;
    private $title;
    private $description;
    private $duration_minutes;
    private $passing_score;
    private $max_attempts;
    private $is_timed;
    private $created_at;
    private $updated_at;

    public function __construct($data = [])
    {
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

    // Getters
    public function getId()
    {
        return $this->id;
    }

    public function getCourseId()
    {
        return $this->course_id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getDurationMinutes()
    {
        return $this->duration_minutes;
    }

    public function getPassingScore()
    {
        return $this->passing_score;
    }

    public function getMaxAttempts()
    {
        return $this->max_attempts;
    }

    public function getIsTimed()
    {
        return $this->is_timed;
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

    public function setCourseId($course_id)
    {
        $this->course_id = $course_id;
        return $this;
    }

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function setDurationMinutes($duration_minutes)
    {
        // Ensure duration is a positive integer
        $duration = (int)$duration_minutes;
        $this->duration_minutes = $duration > 0 ? $duration : 0;
        return $this;
    }

    public function setPassingScore($passing_score)
    {
        // Ensure passing score is between 0 and 100
        $score = floatval($passing_score);
        $score = max(0, min(100, $score)); // Clamp between 0 and 100
        $this->passing_score = $score;
        return $this;
    }

    public function setMaxAttempts($max_attempts)
    {
        // Ensure max attempts is a positive integer
        $attempts = (int)$max_attempts;
        $this->max_attempts = $attempts > 0 ? $attempts : 1;
        return $this;
    }

    public function setIsTimed($is_timed)
    {
        // Ensure is_timed is a boolean (0 or 1)
        $this->is_timed = $is_timed ? 1 : 0;
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
