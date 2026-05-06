<?php

return [
    'enabled' => filter_var($_ENV['AI_ENABLED'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
    'provider' => strtolower((string) ($_ENV['AI_PROVIDER'] ?? 'groq')),
    'base_url' => rtrim((string) ($_ENV['AI_BASE_URL'] ?? 'https://api.groq.com/openai/v1'), '/'),
    // Read provider-specific env first for clarity; fall back to generic AI_API_KEY if present.
    'api_key' => (string) ($_ENV['GROQ_API_KEY'] ?? ($_ENV['AI_API_KEY'] ?? '')),
    'model' => (string) ($_ENV['AI_MODEL'] ?? 'gpt-4o-mini'),
    'temperature' => (float) ($_ENV['AI_TEMPERATURE'] ?? 0.2),
    'timeout' => (int) ($_ENV['AI_TIMEOUT'] ?? 45),
    'response_format' => (string) ($_ENV['AI_RESPONSE_FORMAT'] ?? 'json_object'),
    'max_cv_chars' => (int) ($_ENV['AI_MAX_CV_CHARS'] ?? 12000),
    'max_offer_chars' => (int) ($_ENV['AI_MAX_OFFER_CHARS'] ?? 6000),
    'fallback_enabled' => filter_var($_ENV['AI_FALLBACK_ENABLED'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
    // Optional: path to pdftotext binary. If empty, 'pdftotext' will be used from PATH.
    'pdftotext_path' => (string) ($_ENV['PDFTOTEXT_PATH'] ?? 'pdftotext'),
];