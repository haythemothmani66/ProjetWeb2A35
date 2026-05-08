<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

// Ensure we only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

// Get input data
$input = json_decode(file_get_contents('php://input'), true);
$userMessage = $input['message'] ?? '';

if (empty($userMessage)) {
    echo json_encode(['error' => 'Empty message']);
    exit;
}

// API Configuration from .env
$apiKey = $_ENV['GROQ_API_KEY'] ?? getenv('GROQ_API_KEY');
$apiUrl = $_ENV['GROQ_API_URL'] ?? 'https://api.groq.com/openai/v1/chat/completions';
$model = $_ENV['GROQ_MODEL'] ?? 'llama-3.3-70b-versatile';

if (!$apiKey) {
    echo json_encode(['error' => 'Groq API Key not configured']);
    exit;
}

// Prepare the payload (EduMatch Context)
$systemPrompt = "Tu es l'assistant officiel d'EduMatch, une plateforme éducative qui connecte étudiants, professeurs et partenaires professionnels.\n\n" .
                "RÈGLE ABSOLUE : Tu ne réponds QU'AUX questions liées à EduMatch et ses fonctionnalités. " .
                "Si l'utilisateur pose une question qui n'a AUCUN rapport avec EduMatch (recettes, sport, météo, politique, culture générale, etc.), " .
                "tu dois REFUSER catégoriquement de répondre avec ce message exact :\n" .
                "\"Je suis l'assistant EduMatch et je ne peux répondre qu'aux questions concernant notre plateforme. " .
                "Pour toute autre question, veuillez utiliser un moteur de recherche.\"\n\n" .
                "Tu ne dois JAMAIS fournir d'informations hors-sujet, même partiellement, même si l'utilisateur insiste.\n\n" .
                "Sujets autorisés :\n" .
                "- Partenariats : comment devenir partenaire, soumettre une candidature, les badges (Nouveau/Populaire), vérifier le statut\n" .
                "- Devoirs & Projets : dépôt de devoirs, correction, suivi\n" .
                "- Événements EduMatch : événements à venir sur la plateforme\n" .
                "- Quiz : système de quiz éducatifs\n" .
                "- Offres d'emploi : consultation et candidature aux offres\n" .
                "- Compte utilisateur : inscription, connexion, profil, rôles (étudiant, encadrant, partenariat, admin)\n" .
                "- Contact : comment joindre l'équipe support EduMatch\n\n" .
                "Réponds toujours en français, de manière polie, concise et professionnelle.";

$messages = [
    ['role' => 'system', 'content' => $systemPrompt],
    ['role' => 'user', 'content' => $userMessage]
];

$data = [
    'model' => $model,
    'messages' => $messages,
    'temperature' => 0.7,
    'max_tokens' => 1024
];

// cURL Request
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 20);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// Error Handling
if ($curlError) {
    echo json_encode(['error' => 'Connection Error: ' . $curlError]);
    exit;
}

if ($httpCode !== 200) {
    $errorData = json_decode($response, true);
    $errorMessage = $errorData['error']['message'] ?? 'Groq API Error (Status ' . $httpCode . ')';
    echo json_encode(['error' => $errorMessage]);
    exit;
}

// Success
$result = json_decode($response, true);
$botReply = $result['choices'][0]['message']['content'] ?? 'Désolé, je n\'ai pas pu générer de réponse.';

// Filter out internal thinking process if present (common in models like Qwen/DeepSeek)
$botReply = preg_replace('/<think>.*?<\/think>/s', '', $botReply);
$botReply = trim($botReply);

echo json_encode([
    'success' => true,
    'reply' => $botReply
]);
