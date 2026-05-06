<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
Dotenv\Dotenv::createImmutable(dirname(__DIR__))->load();

require __DIR__ . '/../helpers/CvMatchService.php';

$config = require __DIR__ . '/../config/ai.php';

$mask = static function (string $value): string {
    if ($value === '') {
        return '(empty)';
    }

    if (strlen($value) <= 8) {
        return str_repeat('*', strlen($value));
    }

    return substr($value, 0, 4) . str_repeat('*', max(0, strlen($value) - 8)) . substr($value, -4);
};

$service = new CvMatchService();
$result = $service->analyze(
    [
        'cv_file_path' => '',
        'cv_mime' => 'text/plain',
        'cv_original_name' => 'probe.txt',
    ],
    [
        'titre' => 'Developpeur PHP',
        'description' => 'Developpement web PHP et integration API',
        'competencesrequises' => 'PHP, SQL, API REST',
        'lieu' => 'Tunis',
        'typecontrat' => 'CDI',
        'datelimite' => '2026-12-31',
    ]
);

echo "=== AI CONFIG CHECK ===\n";
echo 'enabled=' . ($config['enabled'] ? 'true' : 'false') . "\n";
echo 'provider=' . (string) $config['provider'] . "\n";
echo 'base_url=' . (string) $config['base_url'] . "\n";
echo 'model=' . (string) $config['model'] . "\n";
echo 'api_key=' . $mask((string) $config['api_key']) . "\n";

echo "\n=== SERVICE RESULT ===\n";
echo 'status=' . (string) ($result['status'] ?? 'unknown') . "\n";
echo 'provider=' . (string) ($result['provider'] ?? '') . "\n";
echo 'model=' . (string) ($result['model'] ?? '') . "\n";
echo 'match_score=' . var_export($result['match_score'] ?? null, true) . "\n";
if (isset($result['match_details']['error'])) {
    echo 'error=' . (string) $result['match_details']['error'] . "\n";
}
