<?php
/**
 * Config IA pour module Quiz - AiCourseGenerator (generation cours via IA)
 *
 * INSTRUCTIONS POUR COEQUIPIERS :
 * 1. Copier ce fichier en `config/ai_quiz.php`
 * 2. Ajouter GROQ_API_KEY dans le fichier .env a la racine (deja gitignored) :
 *    GROQ_API_KEY=gsk_xxxxxxxxxxxxxxxxxxxxxxxx
 * 3. Recuperer une cle sur https://console.groq.com/keys
 *
 * Provider : Groq (compatible OpenAI chat completions API)
 * Modele recommande : llama-3.3-70b-versatile (gratuit, rapide, bon en multilingue)
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
    'provider'    => 'groq',
    'api_key'     => (string) ($get('GROQ_API_KEY') ?: $get('AI_API_KEY') ?: ''),
    'model'       => (string) $get('GROQ_MODEL', 'llama-3.3-70b-versatile'),
    'api_url'     => (string) $get('GROQ_API_URL', 'https://api.groq.com/openai/v1/chat/completions'),
    'temperature' => 0.7,
    'timeout'     => 45,
];
