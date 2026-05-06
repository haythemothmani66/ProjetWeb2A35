<?php
declare(strict_types=1);

// Simple web diagnostics for Apache environment
require __DIR__ . '/../vendor/autoload.php';
Dotenv\Dotenv::createImmutable(dirname(__DIR__))->load();
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../helpers/CvMatchService.php';
$config = require __DIR__ . '/../config/ai.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== Server environment ===\n";
echo "PHP SAPI: " . PHP_SAPI . "\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "User: " . (function_exists('posix_getpwuid') ? posix_getpwuid(posix_geteuid())['name'] : (get_current_user() ?: 'unknown')) . "\n";

echo "\n=== PATH / environment ===\n";
$path = getenv('PATH') ?: ($_SERVER['PATH'] ?? '');
echo "PATH=" . $path . "\n";

echo "\n=== PHP disabled functions ===\n";
echo ini_get('disable_functions') . "\n";

echo "\n=== pdftotext check ===\n";
$pdftotext = $config['pdftotext_path'] ?? 'pdftotext';
echo "Configured pdftotext path: " . $pdftotext . "\n";

$check = null;
if (function_exists('shell_exec')) {
    $check = @shell_exec(escapeshellcmd($pdftotext) . ' -v 2>&1');
    if (trim((string)$check) === '') {
        // try where.exe (Windows)
        $where = @shell_exec('where.exe ' . escapeshellarg($pdftotext) . ' 2>&1');
        $check = $where ?: $check;
    }
}

echo "shell_exec available: " . (function_exists('shell_exec') ? 'yes' : 'no') . "\n";
echo "pdftotext output (first 400 chars):\n" . substr((string)$check, 0, 400) . "\n";

// Find latest uploaded CV
$stmt = $pdo->query("SELECT id, cv_file_path, cv_mime, cv_original_name FROM candidature WHERE cv_file_path IS NOT NULL AND cv_file_path != '' ORDER BY datecandidature DESC LIMIT 1");
$cand = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$cand) {
    echo "\nNo uploaded CV found in DB.\n";
    exit;
}

$cvPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . $cand['cv_file_path'];
echo "\nLatest candidature id={$cand['id']} cv={$cand['cv_file_path']}\n";

if (!is_file($cvPath)) {
    echo "CV file not found: {$cvPath}\n";
    exit;
}

$service = new CvMatchService();
$reflect = new ReflectionClass($service);
$extractM = $reflect->getMethod('extractTextFromFile');
$extractM->setAccessible(true);

$extracted = $extractM->invoke($service, $cvPath, $cand['cv_mime'] ?? '', $cand['cv_original_name'] ?? '');
$len = mb_strlen((string)$extracted, 'UTF-8');

echo "\nExtracted text length: {$len}\n";
if ($len > 0) {
    echo "Excerpt:\n" . mb_substr((string)$extracted, 0, 800, 'UTF-8') . "\n";
} else {
    echo "No text extracted from CV via web context.\n";
}

// Optionally run analyze to see what web path returns
try {
    $analysis = $service->analyze([
        'cv_file_path' => $cand['cv_file_path'],
        'cv_mime' => $cand['cv_mime'] ?? '',
        'cv_original_name' => $cand['cv_original_name'] ?? '',
    ], ['titre' => '(test)', 'description' => '', 'competencesrequises' => '', 'lieu' => '', 'typecontrat' => '', 'datelimite' => '']);

    echo "\nAnalysis status: " . ($analysis['status'] ?? 'unknown') . "\n";
    echo "match_score=" . var_export($analysis['match_score'] ?? null, true) . "\n";
    if (isset($analysis['match_details']['error'])) {
        echo "error=" . $analysis['match_details']['error'] . "\n";
    }
} catch (Throwable $e) {
    echo "Analysis threw: " . $e->getMessage() . "\n";
}

echo "\nDone.\n";
