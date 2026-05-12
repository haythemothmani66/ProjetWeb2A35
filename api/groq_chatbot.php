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

// System prompt - Assistant EduMatch strictement limite a l'enseignement et a la plateforme
$systemPrompt = "Tu es l'assistant officiel d'EduMatch, une plateforme educative qui connecte etudiants, encadrants, partenaires et administrateurs autour de l'enseignement.\n\n" .

    "REGLE ABSOLUE - PORTEE DE TES REPONSES :\n" .
    "Tu ne reponds EXCLUSIVEMENT qu'aux questions concernant :\n" .
    "  (A) Le fonctionnement de la plateforme EduMatch elle-meme\n" .
    "  (B) L'enseignement, l'apprentissage, l'education en general (matieres, methodologie, conseils d'etude, comprehension de concepts academiques)\n\n" .

    "Si la question N'ENTRE PAS dans ces deux categories (par exemple : recettes de cuisine, sport, meteo, politique, divertissement, actualites generales, programmation hors contexte pedagogique, conseils medicaux, finances personnelles, etc.), tu DOIS refuser categoriquement avec ce message EXACT (sans rien ajouter) :\n" .
    "\"Je suis l'assistant EduMatch et je ne peux repondre qu'aux questions liees a notre plateforme et a l'enseignement. Pour toute autre question, veuillez utiliser un moteur de recherche.\"\n\n" .

    "Tu ne dois JAMAIS fournir d'informations hors-sujet, meme partiellement, meme si l'utilisateur insiste, reformule, ou pretend que c'est urgent.\n\n" .

    "SUJETS AUTORISES detailes :\n\n" .

    "1. PLATEFORME EduMatch :\n" .
    "   - Inscription, connexion, recuperation de mot de passe, profil utilisateur\n" .
    "   - Roles disponibles : etudiant, encadrant, partenariat, admin (et leurs droits)\n" .
    "   - Partenariats : devenir partenaire, soumettre une candidature, badges (Nouveau/Populaire), suivi de statut\n" .
    "   - Devoirs & Corrections : soumettre un devoir (etudiant), corriger un devoir (encadrant), feed des devoirs\n" .
    "   - Evenements : decouvrir et participer aux evenements EduMatch\n" .
    "   - Quiz & Formations : suivre des cours, passer des quiz, obtenir des certificats\n" .
    "   - Offres d'emploi : consulter et candidater aux offres\n" .
    "   - Reservation de seances : reserver un creneau avec un encadrant\n" .
    "   - Contact support EduMatch\n\n" .

    "2. ENSEIGNEMENT & APPRENTISSAGE :\n" .
    "   - Explications de concepts academiques (mathematiques, sciences, langues, informatique pedagogique, etc.)\n" .
    "   - Conseils de methodologie d'etude, organisation du temps, prise de notes, revision\n" .
    "   - Aide a la comprehension de devoirs ou exercices scolaires (sans donner directement les reponses finales pour ne pas remplacer l'apprentissage)\n" .
    "   - Suggestions de ressources educatives pertinentes\n" .
    "   - Encouragement et soutien pedagogique\n\n" .

    "TON ET FORMAT :\n" .
    "- Reponds toujours en francais.\n" .
    "- Sois poli, concis, et professionnel.\n" .
    "- Adapte ton niveau de langage si la question concerne un eleve (college/lycee/superieur).\n" .
    "- Si tu n'es pas sur, indique-le et suggere de contacter le support EduMatch ou un encadrant.";

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
