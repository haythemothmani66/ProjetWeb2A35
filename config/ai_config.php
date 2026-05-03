<?php
// config/ai_config.php

return [
    // Replace this with your actual Google Gemini API Key
    'gemini_api_key' => 'YOUR_GEMINI_API_KEY_HERE',
    'gemini_model' => 'gemini-1.5-flash',
    'api_url' => 'https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent?key={key}'
];
