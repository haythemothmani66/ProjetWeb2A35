<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
Dotenv\Dotenv::createImmutable(dirname(__DIR__))->load();
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../helpers/CvMatchService.php';

// Find latest candidature with uploaded CV
$stmt = $pdo->query("SELECT id, cv_file_path, cv_mime, cv_original_name, offreid FROM candidature WHERE cv_file_path IS NOT NULL AND cv_file_path != '' ORDER BY datecandidature DESC LIMIT 1");
$cand = $stmt->fetch(PDO::FETCH_ASSOC);
if (!is_array($cand)) {
    echo "No candidature with uploaded CV found.\n";
    exit(0);
}

$cvPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . $cand['cv_file_path'];
if (!is_file($cvPath)) {
    echo "CV file not found: {$cvPath}\n";
    exit(1);
}

echo "Found candidature id={$cand['id']} cv={$cand['cv_file_path']}\n";

$service = new CvMatchService();
// Get offer data
$offStmt = $pdo->prepare('SELECT titre, description, competencesrequises, lieu, typecontrat, datelimite FROM offreemploi WHERE id = :id LIMIT 1');
$offStmt->execute(['id' => (int)$cand['offreid']]);
$offer = $offStmt->fetch(PDO::FETCH_ASSOC) ?: [];

// Extract text only (call internal method via reflection)
$reflect = new ReflectionClass($service);
$extractM = $reflect->getMethod('extractTextFromFile');
$extractM->setAccessible(true);
$extracted = $extractM->invoke($service, $cvPath, $cand['cv_mime'] ?? '', $cand['cv_original_name'] ?? '');

$len = mb_strlen((string)$extracted, 'UTF-8');
echo "Extracted text length: {$len}\n";
$excerpt = mb_substr((string)$extracted, 0, 600, 'UTF-8');
if ($len > 0) {
    echo "Excerpt:\n" . $excerpt . "\n\n";
} else {
    echo "No text extracted from CV.\n";
}

// Run analyze to re-run the real pipeline
$result = $service->analyze([
    'cv_file_path' => 'uploads/cv/' . basename($cand['cv_file_path']),
    'cv_mime' => $cand['cv_mime'] ?? '',
    'cv_original_name' => $cand['cv_original_name'] ?? '',
], $offer);

echo "Analysis status: " . ($result['status'] ?? 'unknown') . "\n";
if (isset($result['match_score'])) {
    echo "match_score=" . var_export($result['match_score'], true) . "\n";
}
if (isset($result['match_details']['error'])) {
    echo "error=" . $result['match_details']['error'] . "\n";
}
if (isset($result['match_details']['raw'])) {
    echo "raw snippet: \n" . json_encode(array_slice($result['match_details']['raw'],0,6), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) . "\n";
}

// Persist result so you can see it in the admin UI as well
$update = $pdo->prepare('UPDATE candidature SET match_score = :ms, match_details = :md, match_provider = :mp, match_model = :mm, match_generated_at = :mg WHERE id = :id');
$update->execute([
    'ms' => $result['match_score'] ?? null,
    'md' => json_encode($result['match_details'] ?? [], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
    'mp' => $result['provider'] ?? null,
    'mm' => $result['model'] ?? null,
    'mg' => $result['generated_at'] ?? date('Y-m-d H:i:s'),
    'id' => (int)$cand['id'],
]);

echo "Persisted analysis to DB for candidature id={$cand['id']}.\n";
