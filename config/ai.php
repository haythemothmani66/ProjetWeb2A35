<?php
/**
 * Config IA pour CvMatchService (matching CV/offre via Groq)
 * Lit .env simple a la racine du projet (sans Composer/Dotenv)
 */
declare(strict_types=1);

if (!function_exists('_loadDotEnvFile')) {
    function _loadDotEnvFile(string $path): array {
        $vars = [];
        if (!is_file($path)) return $vars;
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            if (strpos($line, '=') === false) continue;
            [$k, $v] = explode('=', $line, 2);
            $vars[trim($k)] = trim($v, " \t\"'");
        }
        return $vars;
    }
}

$env = _loadDotEnvFile(__DIR__ . '/../.env');
$get = static function (string $key, $default = null) use ($env) {
    return $env[$key] ?? $_ENV[$key] ?? getenv($key) ?: $default;
};

return [
    'enabled' => filter_var($get('AI_ENABLED', 'true'), FILTER_VALIDATE_BOOLEAN),
    'provider' => strtolower((string) $get('AI_PROVIDER', 'groq')),
    'base_url' => rtrim((string) $get('AI_BASE_URL', 'https://api.groq.com/openai/v1'), '/'),
    // Reuse the unique GROQ_API_KEY across the project
    'api_key' => (string) ($get('GROQ_API_KEY') ?: $get('AI_API_KEY') ?: ''),
    'model' => (string) $get('AI_MODEL', $get('GROQ_MODEL', 'llama-3.3-70b-versatile')),
    'temperature' => (float) $get('AI_TEMPERATURE', 0.2),
    'timeout' => (int) $get('AI_TIMEOUT', 45),
    'response_format' => (string) $get('AI_RESPONSE_FORMAT', 'json_object'),
    'max_cv_chars' => (int) $get('AI_MAX_CV_CHARS', 12000),
    'max_offer_chars' => (int) $get('AI_MAX_OFFER_CHARS', 6000),
    'fallback_enabled' => filter_var($get('AI_FALLBACK_ENABLED', 'false'), FILTER_VALIDATE_BOOLEAN),
    'pdftotext_path' => (string) $get('PDFTOTEXT_PATH', 'pdftotext'),
];
