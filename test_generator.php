<?php
$key = 'AIzaSyC6z6nBYSMeTZe3zdrfYOdq6QFYssXRkFc';
$model = 'gemini-flash-latest';
$url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . $key;

$topic = "Python basics";
$level = "beginner";
$prompt = <<<PROMPT
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
          {"response_text": "Wrong answer 1", "is_correct": 0}
        ]
      }
    ]
  }
}
PROMPT;

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

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
echo "HTTP Code: " . curl_getinfo($ch, CURLINFO_HTTP_CODE) . "\n";
echo substr($response, 0, 500); // Output only the first 500 chars to avoid huge log
?>
