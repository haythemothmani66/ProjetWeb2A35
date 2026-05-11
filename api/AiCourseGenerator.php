<?php

/**
 * AiCourseGenerator - Genere un cours complet (lessons + quiz + questions + reponses) via IA.
 *
 * Utilise Groq (API compatible OpenAI chat completions) avec llama-3.3-70b-versatile.
 * Configuration : config/ai_quiz.php (qui lit .env, gitignored).
 */
class AiCourseGenerator
{
    private string $apiKey;
    private string $apiUrl;
    private string $model;
    private float $temperature;
    private int $timeout;

    public function __construct()
    {
        $config = require dirname(__DIR__) . '/config/ai_quiz.php';
        $this->apiKey     = (string) ($config['api_key'] ?? '');
        $this->model      = (string) ($config['model'] ?? 'llama-3.3-70b-versatile');
        $this->apiUrl     = (string) ($config['api_url'] ?? 'https://api.groq.com/openai/v1/chat/completions');
        $this->temperature = (float) ($config['temperature'] ?? 0.7);
        $this->timeout    = (int) ($config['timeout'] ?? 45);
    }

    /**
     * Generate a complete course structure using Groq (chat completions)
     */
    public function generateCourse(string $topic, string $level): ?array
    {
        if ($this->apiKey === '' || strpos($this->apiKey, 'YOUR_') === 0) {
            throw new Exception("Please configure GROQ_API_KEY in .env (loaded by config/ai_quiz.php)");
        }

        $prompt = $this->buildPrompt($topic, $level);

        $payload = [
            'model' => $this->model,
            'temperature' => $this->temperature,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an expert curriculum designer. Generate a comprehensive online course structure as STRICT JSON only, no markdown, no explanation outside the JSON.',
                ],
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            'response_format' => ['type' => 'json_object'],
        ];

        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        // SSL verification disabled for local XAMPP (cert bundle souvent manquant)
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log("cURL Error in AiCourseGenerator: " . $error);
            throw new Exception("Failed to connect to AI service: " . $error);
        }

        if ($httpCode !== 200) {
            error_log("Groq API Error (HTTP $httpCode): " . $response);
            // Extraire message d'erreur si possible
            $err = json_decode((string) $response, true);
            $msg = $err['error']['message'] ?? 'AI service returned HTTP ' . $httpCode;
            throw new Exception("AI service error: " . $msg);
        }

        $result = json_decode((string) $response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Failed to parse JSON response from AI: " . json_last_error_msg());
        }

        // Format chat completions : choices[0].message.content
        if (!isset($result['choices'][0]['message']['content'])) {
            error_log("Unexpected Groq response: " . substr((string) $response, 0, 500));
            throw new Exception("Unexpected response format from AI service.");
        }

        $jsonText = (string) $result['choices'][0]['message']['content'];

        // Cleanup d'eventuels markdown fences (au cas ou)
        $jsonText = preg_replace('/^\s*```(json)?\s*/i', '', $jsonText);
        $jsonText = preg_replace('/\s*```\s*$/i', '', $jsonText);

        $courseData = json_decode($jsonText, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Failed to parse inner JSON from AI: " . json_last_error_msg() . " | Raw: " . substr($jsonText, 0, 300));
            throw new Exception("AI returned invalid JSON structure: " . json_last_error_msg());
        }

        return $courseData;
    }

    private function buildPrompt(string $topic, string $level): string
    {
        return <<<PROMPT
Generate a comprehensive online course about "{$topic}" tailored for a "{$level}" level audience.

Return ONLY a valid JSON object with the following EXACT structure (no markdown, no comments, no extra text):

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
    }
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
      }
    ]
  }
}

Constraints:
- Generate exactly 3 lessons in the "lessons" array.
- Generate exactly 3 questions in the "quiz.questions" array.
- Each question must have exactly 4 responses with exactly 1 correct answer.
- All texts must be in French.
- The lesson "content" must be valid HTML with at least one <h2>, two <p>, and one <ul>.
- Do not wrap the JSON in markdown code blocks.
PROMPT;
    }
}
