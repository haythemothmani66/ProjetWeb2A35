<?php

class AiCourseGenerator
{
    private string $apiKey;
    private string $apiUrl;
    private string $model;

    public function __construct()
    {
        $config = require dirname(__DIR__) . '/config/ai_config.php';
        $this->apiKey = $config['gemini_api_key'];
        $this->model = $config['gemini_model'];
        $this->apiUrl = str_replace(
            ['{model}', '{key}'],
            [$this->model, $this->apiKey],
            $config['api_url']
        );
    }

    /**
     * Generate a complete course structure using Gemini API
     */
    public function generateCourse(string $topic, string $level): ?array
    {
        if ($this->apiKey === 'YOUR_GEMINI_API_KEY_HERE') {
            throw new Exception("Please configure your Gemini API Key in config/ai_config.php");
        }

        $prompt = $this->buildPrompt($topic, $level);
        
        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'responseMimeType' => 'application/json',
            ]
        ];

        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        
        // Disable SSL verification for local dev (xampp)
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log("cURL Error in AiCourseGenerator: " . $error);
            throw new Exception("Failed to connect to AI service: " . $error);
        }

        if ($httpCode !== 200) {
            error_log("Gemini API Error (HTTP $httpCode): " . $response);
            throw new Exception("AI service returned an error. Check logs.");
        }

        $result = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Failed to parse JSON response from AI.");
        }

        // Extract text content from Gemini response
        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            $jsonText = $result['candidates'][0]['content']['parts'][0]['text'];
            
            // Clean up possible markdown block ticks around JSON
            $jsonText = preg_replace('/^```json\s*/i', '', $jsonText);
            $jsonText = preg_replace('/\s*```$/i', '', $jsonText);
            
            $courseData = json_decode($jsonText, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $courseData;
            }
            error_log("Failed to parse inner JSON: " . json_last_error_msg());
        }

        throw new Exception("Unexpected response format from AI service.");
    }

    private function buildPrompt(string $topic, string $level): string
    {
        return <<<PROMPT
You are an expert curriculum designer. Please generate a comprehensive online course about "{$topic}" tailored for a "{$level}" level audience.

Return ONLY a valid JSON object with the following exact structure. Do not include any markdown formatting or extra text outside the JSON.

{
  "course": {
    "title": "A catchy title for the course",
    "description": "A comprehensive 2-3 paragraph description",
    "level": "{$level}"
  },
  "lessons": [
    {
      "title": "Lesson 1 Title",
      "summary": "Short summary of the lesson",
      "content": "Full HTML content for the lesson using tags like <h2>, <p>, <ul>. Make it informative.",
      "duration_minutes": 15
    },
    // Generate exactly 3 lessons
  ],
  "quiz": {
    "title": "Final Quiz on {$topic}",
    "description": "Test your knowledge on the course material.",
    "duration_minutes": 20,
    "passing_score": 60,
    "questions": [
      {
        "question_text": "A meaningful multiple choice question?",
        "question_type": "multiple_choice",
        "points": 1,
        "responses": [
          {"response_text": "Correct answer", "is_correct": 1},
          {"response_text": "Wrong answer 1", "is_correct": 0},
          {"response_text": "Wrong answer 2", "is_correct": 0},
          {"response_text": "Wrong answer 3", "is_correct": 0}
        ]
      },
      // Generate exactly 3 questions
    ]
  }
}
PROMPT;
    }
}
