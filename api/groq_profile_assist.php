<?php
/**
 * Endpoint Groq pour assistance IA dans la page profil.
 *
 * Remplace l'ancien appel direct a Ollama (http://localhost:11434) qui necessitait
 * une installation locale + dependance externe ollama.com (en panne).
 *
 * Reception POST JSON : { task: 'ocr_extract' | 'bio_generate', prompt: '...', schema?: {...} }
 * Retour JSON : { success: true, response: '...' } ou { success: false, error: '...' }
 *
 * 2 modes :
 *  - task=ocr_extract  : force response_format json_object pour extraction structuree
 *  - task=bio_generate : text libre court (2-3 phrases)
 */
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Methode non autorisee']);
    exit;
}

$rawInput = file_get_contents('php://input');
$input = json_decode((string) $rawInput, true);

if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'JSON invalide']);
    exit;
}

$task = (string) ($input['task'] ?? '');
$userPrompt = trim((string) ($input['prompt'] ?? ''));

if ($userPrompt === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Prompt vide']);
    exit;
}

$apiKey = $_ENV['GROQ_API_KEY'] ?? getenv('GROQ_API_KEY');
if (!$apiKey) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'GROQ_API_KEY non configuree dans .env']);
    exit;
}

$apiUrl = $_ENV['GROQ_API_URL'] ?? 'https://api.groq.com/openai/v1/chat/completions';
$model  = $_ENV['GROQ_MODEL'] ?? 'llama-3.3-70b-versatile';

// System prompts adaptes selon le task
$systemPrompts = [
    'ocr_extract' => "Tu es un assistant qui extrait des informations structurees depuis du texte OCR d'une carte etudiant. " .
                     "Tu reponds EXCLUSIVEMENT en JSON strict valide, sans aucun texte autour, sans markdown, sans commentaires. " .
                     "Les champs que tu ne peux pas identifier avec certitude doivent rester en chaine vide ''.",
    'bio_generate' => "Tu es un assistant qui ecrit des bios courtes et professionnelles en francais (2-3 phrases maximum). " .
                      "Tu reponds UNIQUEMENT avec le texte de la bio, sans guillemets, sans markdown, sans introduction ni conclusion.",
];

$systemPrompt = $systemPrompts[$task] ?? "Tu es un assistant professionnel et concis. Reponds en francais.";

// Build payload
$payload = [
    'model' => $model,
    'messages' => [
        ['role' => 'system', 'content' => $systemPrompt],
        ['role' => 'user',   'content' => $userPrompt],
    ],
    'temperature' => $task === 'ocr_extract' ? 0.1 : 0.7, // basse temp pour extraction precise
    'max_tokens' => $task === 'bio_generate' ? 200 : 800,
];

// Forcer JSON pour la tache extraction
if ($task === 'ocr_extract') {
    $payload['response_format'] = ['type' => 'json_object'];
}

// Call Groq
$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json',
    ],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_TIMEOUT => 30,
    CURLOPT_CONNECTTIMEOUT => 8,
    CURLOPT_SSL_VERIFYPEER => false,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr = curl_error($ch);
curl_close($ch);

if ($curlErr !== '') {
    error_log("groq_profile_assist cURL: $curlErr");
    http_response_code(502);
    echo json_encode(['success' => false, 'error' => "Erreur connexion IA: $curlErr"]);
    exit;
}

if ($httpCode !== 200) {
    error_log("groq_profile_assist HTTP $httpCode: $response");
    $errData = json_decode((string) $response, true);
    $msg = $errData['error']['message'] ?? "Erreur IA (HTTP $httpCode)";
    http_response_code(502);
    echo json_encode(['success' => false, 'error' => "Service IA: $msg"]);
    exit;
}

$result = json_decode((string) $response, true);
$content = $result['choices'][0]['message']['content'] ?? '';

if ($content === '') {
    http_response_code(502);
    echo json_encode(['success' => false, 'error' => 'Reponse IA vide']);
    exit;
}

// Cleanup eventuel markdown fences (au cas ou)
$content = preg_replace('/^\s*```(json)?\s*/i', '', $content);
$content = preg_replace('/\s*```\s*$/i', '', $content);
$content = trim($content);

echo json_encode([
    'success' => true,
    'response' => $content,
    'task' => $task,
]);
