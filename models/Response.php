<?php
class Response
{
    private $id;
    private $question_id;
    private $response_text;
    private $is_correct;
    private $response_order;
    private $explanation;

    public function __construct($data = [])
    {
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->question_id = $data['question_id'] ?? null;
            $this->response_text = $data['response_text'] ?? null;
            $this->is_correct = $data['is_correct'] ?? 0;
            $this->response_order = $data['response_order'] ?? 1;
            $this->explanation = $data['explanation'] ?? null;
        }
    }

    // Getters
    public function getId()
    {
        return $this->id;
    }

    public function getQuestionId()
    {
        return $this->question_id;
    }

    public function getResponseText()
    {
        return $this->response_text;
    }

    public function getIsCorrect()
    {
        return $this->is_correct;
    }

    public function getResponseOrder()
    {
        return $this->response_order;
    }

    public function getExplanation()
    {
        return $this->explanation;
    }

    // Setters
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setQuestionId($question_id)
    {
        $this->question_id = $question_id;
        return $this;
    }

    public function setResponseText($response_text)
    {
        $this->response_text = $response_text;
        return $this;
    }

    public function setIsCorrect($is_correct)
    {
        // Ensure is_correct is boolean (0 or 1)
        $this->is_correct = $is_correct ? 1 : 0;
        return $this;
    }

    public function setResponseOrder($response_order)
    {
        // Ensure response order is a positive integer
        $order = (int)$response_order;
        $this->response_order = $order > 0 ? $order : 1;
        return $this;
    }

    public function setExplanation($explanation)
    {
        $this->explanation = $explanation;
        return $this;
    }
}
