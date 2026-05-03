<?php
class Lesson
{
    private $id;
    private $course_id;
    private $title;
    private $summary;
    private $content;
    private $duration_minutes;
    private $lesson_order;
    private $status;
    private $created_at;
    private $updated_at;

    public function __construct($data = [])
    {
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

    public function getSummary()
    {
        return $this->summary;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getDurationMinutes()
    {
        return $this->duration_minutes;
    }

    public function getLessonOrder()
    {
        return $this->lesson_order;
    }

    public function getStatus()
    {
        return $this->status;
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

    public function setSummary($summary)
    {
        $this->summary = $summary;
        return $this;
    }

    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    public function setDurationMinutes($duration_minutes)
    {
        // Ensure duration is a positive integer
        $duration = (int)$duration_minutes;
        $this->duration_minutes = $duration > 0 ? $duration : 0;
        return $this;
    }

    public function setLessonOrder($lesson_order)
    {
        // Ensure lesson order is a positive integer
        $order = (int)$lesson_order;
        $this->lesson_order = $order > 0 ? $order : 1;
        return $this;
    }

    public function setStatus($status)
    {
        $validStatuses = ['draft', 'published', 'archived'];
        if (in_array($status, $validStatuses)) {
            $this->status = $status;
        }
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
