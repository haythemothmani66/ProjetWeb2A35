<?php
class Certificate
{
    public int $id;
    public int $course_id;
    public int $quiz_id;
    public string $student_name;
    public string $issued_at;

    public function __construct(array $data)
    {
        $this->id = (int) ($data['id'] ?? 0);
        $this->course_id = (int) ($data['course_id'] ?? 0);
        $this->quiz_id = (int) ($data['quiz_id'] ?? 0);
        $this->student_name = $data['student_name'] ?? '';
        $this->issued_at = $data['issued_at'] ?? '';
    }
}
