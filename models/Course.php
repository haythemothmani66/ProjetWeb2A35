<?php
class Course
{
    private $id;
    private $title;
    private $description;
    private $image;
    private $level;
    private $status;
    private $created_at;
    private $updated_at;

    public function __construct($data = [])
    {
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

    // Getters
    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function getLevel()
    {
        return $this->level;
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

    public function setImage($image)
    {
        $this->image = $image;
        return $this;
    }

    public function setLevel($level)
    {
        $validLevels = ['beginner', 'intermediate', 'advanced'];
        if (in_array($level, $validLevels)) {
            $this->level = $level;
        }
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
