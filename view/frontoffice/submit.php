<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../../config/database.php';

// Detection requete AJAX (pour ne pas rediriger en HTML mais retourner JSON)
$isAjax = (
    (strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest') ||
    ($_SERVER['REQUEST_METHOD'] === 'POST' && stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false)
);

// Protection : seuls etudiants/encadrants/admins peuvent acceder
if (empty($_SESSION['user_id'])) {
    if ($isAjax) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Non authentifie']);
        exit;
    }
    header('Location: /gestion_users/view/template/sign-in.php');
    exit;
}
if (($_SESSION['user_role'] ?? '') !== 'etudiant' && ($_SESSION['user_role'] ?? '') !== 'encadrant' && ($_SESSION['user_role'] ?? '') !== 'admin') {
    if ($isAjax) {
        header('Content-Type: application/json');
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Role non autorise']);
        exit;
    }
    header('Location: /gestion_users/view/template/index.php');
    exit;
}

// Flags d'affichage des formulaires selon le role
//   etudiant -> formulaire 'Soumettre devoir' uniquement
//   encadrant -> formulaire 'Soumettre correction' uniquement
//   admin -> les deux formulaires
$role = $_SESSION['user_role'] ?? '';
$canSubmitDevoir     = in_array($role, ['etudiant', 'admin'], true);
$canSubmitCorrection = in_array($role, ['encadrant', 'admin'], true);

$baseUrl = '/gestion_users';





// Récupérer et effacer les messages
$successMessage = $_SESSION['success_message'] ?? '';
$formErrors = $_SESSION['form_errors'] ?? [];
$oldData = $_SESSION['form_data'] ?? [];

// Effacer les sessions après récupération
unset($_SESSION['success_message']);
unset($_SESSION['form_errors']);
unset($_SESSION['form_data']);



// Pour pré-remplir les champs (optionnel)
$oldDevoir = $oldData;
$oldCorrection = $oldData;

require_once __DIR__ . '/../../config/database.php';
$conn = getDBConnection();


// Vérifier si on vient du feed pour ajouter une correction à un devoir spécifique
$addCorrectionFor = $_GET['add_correction_for'] ?? '';
$devoirTitle = $_GET['title'] ?? '';

if ($addCorrectionFor && is_numeric($addCorrectionFor)) {
    // Stocker l'ID du devoir dans une variable pour pré-remplir le formulaire
    $prefillDevoirId = (int)$addCorrectionFor;
    $prefillDevoirTitle = htmlspecialchars($devoirTitle);
} else {
    $prefillDevoirId = null;
    $prefillDevoirTitle = null;
}

// Récupérer l'ID à modifier depuis l'URL
$editType = $_GET['edit'] ?? '';
$editId = (int)($_GET['id'] ?? 0);
$editDevoir = null;
$editCorrection = null;

if ($editType && $editId) {
    if ($editType === 'devoir') {
        $stmt = $conn->prepare("SELECT * FROM devoirs WHERE id_devoir = ?");
        $stmt->execute([$editId]);
        $editDevoir = $stmt->fetch(PDO::FETCH_ASSOC);
    } elseif ($editType === 'correction') {
        $stmt = $conn->prepare("SELECT * FROM correction WHERE id_correction = ?");
        $stmt->execute([$editId]);
        $editCorrection = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

$devoirs = $conn->query("SELECT id_devoir, titre FROM devoirs ORDER BY id_devoir DESC")->fetchAll(PDO::FETCH_ASSOC);
$correction = $conn->query("SELECT * FROM correction ORDER BY id_correction DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

$isEditDevoir = ($editType === 'devoir' && is_array($editDevoir));
$isEditCorrection = ($editType === 'correction' && is_array($editCorrection));

$devoirFormAction = $isEditDevoir
    ? '/gestion_users/controller/devoirs.php?action=updatedevoir'
    : '/gestion_users/controller/devoirs.php?action=submit';

$correctionFormAction = $isEditCorrection
    ? '/gestion_users/controller/devoirs.php?action=updatecorrection'
    : '/gestion_users/controller/devoirs.php?action=correct';

function oldOrEdit(string $field, array $oldData, ?array $editData): string
{
    if (array_key_exists($field, $oldData) && $oldData[$field] !== null && $oldData[$field] !== '') {
        return (string)$oldData[$field];
    }

    if (is_array($editData) && array_key_exists($field, $editData) && $editData[$field] !== null) {
        return (string)$editData[$field];
    }

    return '';
}

// Charger GROQ_API_KEY depuis .env (pas hardcode)
if (!defined('GROQ_API_KEY')) {
    $envFile = __DIR__ . '/../../.env';
    $apiKey = '';
    if (is_file($envFile)) {
        foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            if (strpos($line, 'GROQ_API_KEY=') === 0) {
                $apiKey = trim(substr($line, strlen('GROQ_API_KEY=')), " \t\"'");
                break;
            }
        }
    }
    define('GROQ_API_KEY', $apiKey);
}

// ============================================================
// FONCTION ASSISTANT CORRECTION IA (GROQ - Version complète)
// ============================================================


function callAICorrection($titre, $description, $niveau) {
    $prompt = "Tu es un professeur expert en programmation et pédagogie. 
    Génère une correction complète et détaillée pour le devoir suivant :
    
    TITRE: " . $titre . "
    DESCRIPTION: " . $description . "
    NIVEAU: " . $niveau . "
    
    
    Tu dois répondre UNIQUEMENT avec un objet JSON valide contenant EXACTEMENT ces 6 champs :
    {
        \"commentaire\": \"Commentaire détaillé de la correction (min 100 caractères)\",
        \"note_estimee\": 14,
        \"suggestions\": \"Suggestions d'amélioration concrètes (min 50 caractères)\",
        \"competences\": [\"compétence1\", \"compétence2\", \"compétence3\"],
        \"type_feedback\": \"explicatif\",
        \"ton_feedback\": \"encourageant\"
    }
    
    Règles importantes :
    - commentaire : entre 100 et 300 caractères, formaté avec des retours à la ligne
    - note_estimee : nombre entier entre 8 et 18
    - suggestions : entre 50 et 150 caractères
    - competences : tableau de 2 à 4 compétences (ex: Logique algorithmique, Syntaxe, SQL)
    - type_feedback : \"explicatif\", \"direct\" ou \"guide\"
    - ton_feedback : \"encourageant\", \"strict\" ou \"neutre\"
    
    Ne mets AUCUN texte avant ou après le JSON.";
    
    $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . GROQ_API_KEY
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'model' => 'llama-3.3-70b-versatile',
        'messages' => [
            ['role' => 'system', 'content' => 'Tu es un assistant pédagogique. Tu réponds UNIQUEMENT en JSON valide.'],
            ['role' => 'user', 'content' => $prompt]
        ],
        'temperature' => 0.5,
        'max_tokens' => 1000
    ]));
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        // Fallback avec valeurs par défaut
        return getDefaultCorrection($titre, $description);
    }
    
    $data = json_decode($response, true);
    $content = $data['choices'][0]['message']['content'] ?? '';
    
    // Nettoyer le JSON
    $content = preg_replace('/```json\s*|\s*```/', '', trim($content));
    
    // Extraire le JSON complet
    preg_match('/\{[^{}]*"commentaire"[^{}]*"ton_feedback"[^{}]*\}/s', $content, $matches);
    if (isset($matches[0])) {
        $result = json_decode($matches[0], true);
    } else {
        $result = json_decode($content, true);
    }
    
    if (!$result) {
        return getDefaultCorrection($titre, $description);
    }
    
    // Retourner avec TOUS les champs (même si manquants, on met des valeurs par défaut)
    return [
        'commentaire' => $result['commentaire'] ?? getDefaultCommentaire($titre),
        'note_estimee' => $result['note_estimee'] ?? 13,
        'suggestions' => $result['suggestions'] ?? getDefaultSuggestions(),
        'competences' => is_array($result['competences'] ?? null) ? $result['competences'] : ['Logique algorithmique', 'Résolution de problèmes', 'Analyse critique'],
        'type_feedback' => $result['type_feedback'] ?? 'explicatif',
        'ton_feedback' => $result['ton_feedback'] ?? 'encourageant'
    ];
}



// ============================================================
// TRAITEMENT AJAX POUR L'ASSISTANT IA (À AJOUTER ICI)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if ($input && isset($input['action']) && $input['action'] === 'generate_correction') {
        $result = callAICorrection(
            $input['titre'] ?? '',
            $input['description'] ?? '',
            $input['niveau'] ?? 'moyen'
        );
        echo json_encode($result);
        exit;
    }
}

?>



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Soumettre - EduFeed</title>
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Jost:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../../assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="../../assets/fonts/themify-icons.css">
    <link rel="stylesheet" href="../../assets/owlcarousel/css/owl.carousel.css">
    <link rel="stylesheet" href="../../assets/owlcarousel/css/owl.theme.css">
    <link rel="stylesheet" href="../../assets/css/jquery-simple-mobilemenu.css">
    <link rel="stylesheet" href="../../assets/css/magnific-popup.css">
    <link rel="stylesheet" href="../../assets/css/animate.css">
    <link rel="stylesheet" href="../../assets/css/style.css">

    <style>

        /* Toast IA */
.ai-toast {
    position: fixed;
    bottom: 30px;
    right: 30px;
    z-index: 10000;
    padding: 1rem 1.5rem;
    border-radius: 0.75rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    animation: slideInRight 0.3s ease forwards;
}
.ai-toast.success {
    background: #10B981;
    color: white;
}
.ai-toast.error {
    background: #EF4444;
    color: white;
}
@keyframes slideInRight {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
@keyframes slideOutRight {
    from { transform: translateX(0); opacity: 1; }
    to { transform: translateX(100%); opacity: 0; }
}

        /* Bouton IA */
.btn-ai {
    background: linear-gradient(135deg, #8B5CF6, #6C63FF);
    color: white;
    border: none;
    border-radius: 0.75rem;
    padding: 0.8rem 1.5rem;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.btn-ai:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(139, 92, 246, 0.4);
}

.btn-ai.loading {
    opacity: 0.7;
    cursor: not-allowed;
}

.btn-ai.loading i {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
        .popup-message {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    padding: 1rem 1.5rem;
    border-radius: 0.75rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    animation: slideInRight 0.3s ease forwards;
}
.popup-message.success { background: #10B981; color: white; }
.popup-message.error { background: #EF4444; color: white; }
@keyframes slideInRight {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
@keyframes slideOutRight {
    from { transform: translateX(0); opacity: 1; }
    to { transform: translateX(100%); opacity: 0; }
}
        :root {
            --primary: #6C63FF;
            --secondary: #00D4FF;
            --success: #10B981;
            --danger: #EF4444;
            --warning: #F59E0B;
            --light-bg: #F8FAFC;
            --border: #E2E8F0;
            --text: #1E293B;
            --shadow-lg: 0 15px 40px rgba(0,0,0,0.1);
        }

        /* ============ FORM CONTAINER ============ */
        .modern-form-container {
            background: linear-gradient(135deg, #ffffff 0%, #f3f6ff 100%);
            padding: 2.5rem;
            border-radius: 1.5rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(255,255,255,0.6);
            transition: all 0.3s ease;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
            animation: slideIn 0.5s ease forwards;
        }
        .modern-form-container::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            border-radius: 1.5rem 1.5rem 0 0;
        }
        .modern-form-container:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
        }

        /* ============ TITLES ============ */
        .form-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 0.4rem;
            text-align: center;
        }
        .form-subtitle {
            font-size: 0.95rem;
            color: #64748B;
            text-align: center;
            margin-bottom: 2rem;
            font-weight: 500;
        }

        /* ============ FORM GROUP ============ */
        .form-group {
            margin-bottom: 1.4rem;
            position: relative;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 0.4rem;
            font-size: 0.92rem;
            letter-spacing: 0.2px;
        }
        .form-group label .required-star {
            color: var(--danger);
            margin-left: 3px;
        }
        .form-group small.hint {
            display: block;
            color: #94A3B8;
            margin-top: 0.2rem;
            font-size: 0.82rem;
        }

        /* ============ INPUT FIELDS ============ */
        .form-control {
            border-radius: 0.75rem !important;
            padding: 0.8rem 1rem !important;
            border: 2px solid var(--border) !important;
            font-size: 0.95rem;
            transition: all 0.3s ease !important;
            background-color: #fff !important;
            color: var(--text) !important;
            font-family: 'DM Sans', sans-serif;
            width: 100%;
        }
        .form-control::placeholder { color: #CBD5E1; font-weight: 400; }
        .form-control:focus {
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 3px rgba(108,99,255,0.12) !important;
            outline: none !important;
            background-color: #fff !important;
        }
        textarea.form-control {
            min-height: 110px;
            resize: vertical;
            font-family: 'DM Sans', sans-serif;
        }
        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236C63FF' d='M0 3l6 6 6-6z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            padding-right: 2.5rem !important;
            cursor: pointer;
        }
        input[type="file"] { padding: 0.3rem 0 !important; }
        input[type="file"]::file-selector-button {
            background: linear-gradient(135deg, var(--primary), #8B5CF6);
            color: white;
            border: none;
            border-radius: 0.5rem;
            padding: 0.55rem 1.1rem;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.88rem;
            transition: all 0.3s ease;
            margin-right: 0.75rem;
        }
        input[type="file"]::file-selector-button:hover { transform: translateY(-1px); }

        /* ============ VALIDATION STATES ============ */
        .form-control.is-valid {
            border-color: var(--success) !important;
            background-image: none !important;
        }
        .form-control.is-valid:focus {
            box-shadow: 0 0 0 3px rgba(16,185,129,0.12) !important;
        }
        .form-control.is-invalid {
            border-color: var(--danger) !important;
            background-image: none !important;
        }
        .form-control.is-invalid:focus {
            box-shadow: 0 0 0 3px rgba(239,68,68,0.12) !important;
        }

        /* ============ FEEDBACK MESSAGES ============ */
        .field-feedback {
            display: none;
            font-size: 0.82rem;
            font-weight: 500;
            margin-top: 0.35rem;
            padding: 0.3rem 0.6rem;
            border-radius: 0.4rem;
            align-items: center;
            gap: 0.3rem;
        }
        .field-feedback.error {
            display: flex;
            color: var(--danger);
            background: rgba(239,68,68,0.08);
        }
        .field-feedback.success {
            display: flex;
            color: var(--success);
            background: rgba(16,185,129,0.08);
        }

        /* ============ CHAR COUNTER ============ */
        .char-counter {
            font-size: 0.8rem;
            color: #94A3B8;
            text-align: right;
            margin-top: 0.25rem;
            display: block;
        }
        .char-counter.warning { color: var(--warning); font-weight: 600; }
        .char-counter.over { color: var(--danger); font-weight: 700; }

        /* ============ FILE PREVIEW ============ */
        .file-preview {
            display: none;
            margin-top: 0.5rem;
            padding: 0.5rem 0.75rem;
            background: rgba(16,185,129,0.08);
            border-radius: 0.5rem;
            font-size: 0.85rem;
            color: var(--success);
            font-weight: 600;
            align-items: center;
            gap: 0.4rem;
        }
        .file-preview.show { display: flex; }

        /* ============ PROGRESS BAR ============ */
        .form-progress {
            margin-bottom: 2rem;
            text-align: center;
        }
        .form-progress-label {
            font-size: 0.85rem;
            color: #64748B;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        .progress {
            height: 6px;
            border-radius: 999px;
            background: var(--border);
        }
        .progress-bar {
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            border-radius: 999px;
            transition: width 0.4s ease;
        }

        /* ============ SECTION DIVIDER ============ */
        .section-divider {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 1.5rem 0;
            color: #94A3B8;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .section-divider::before, .section-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        /* ============ BUTTONS ============ */
        .btn-submit {
            background: linear-gradient(135deg, var(--primary), #8B5CF6) !important;
            color: white !important;
            border: none !important;
            border-radius: 0.75rem !important;
            padding: 0.9rem 2rem !important;
            font-weight: 700 !important;
            font-size: 1rem !important;
            transition: all 0.3s ease !important;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
            width: 100% !important;
            cursor: pointer;
        }
        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(108,99,255,0.35) !important;
        }
        .btn-submit:active { transform: translateY(-1px); }
        .btn-submit-correction {
            background: linear-gradient(135deg, var(--success), #059669) !important;
        }
        .btn-submit-correction:hover {
            box-shadow: 0 10px 25px rgba(16,185,129,0.35) !important;
        }

        /* ============ LOADING SPINNER ON SUBMIT ============ */
        .btn-submit .spinner {
            display: none;
            width: 18px; height: 18px;
            border: 2px solid rgba(255,255,255,0.4);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
            margin-right: 0.5rem;
        }
        .btn-submit.loading .spinner { display: inline-block; }
        .btn-submit.loading .btn-text { opacity: 0.7; }

        /* ============ ALERT ============ */
        .alert-modern {
            border-radius: 0.75rem;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 500;
            font-size: 0.95rem;
        }
        .alert-modern.success {
            background: rgba(16,185,129,0.1);
            border: 1px solid rgba(16,185,129,0.3);
            color: #065F46;
        }
        .alert-modern.error {
            background: rgba(239,68,68,0.08);
            border: 1px solid rgba(239,68,68,0.25);
            color: #991B1B;
        }
        .alert-modern i { font-size: 1.2rem; }

        /* ============ ANIMATION ============ */
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* ============ RESPONSIVE ============ */
        @media (max-width: 768px) {
            .modern-form-container { padding: 1.5rem; }
            .form-title { font-size: 1.4rem; }
        }

        /* ============ NAVBAR DROPDOWN (necessaire pour _navbar.php) ============ */
        .header-group { display: flex; flex-direction: row; align-items: center; gap: 10px; }
        .btn-backoffice { background: linear-gradient(135deg, #6366f1, #8B5CF6); color: white; padding: 10px 20px; border-radius: 2px; text-decoration: none; font-weight: 600; font-size: 13px; transition: all 0.3s ease; display: inline-block; text-align: center; border: none; cursor: pointer; }
        .btn-backoffice:hover { background: linear-gradient(135deg, #4f46e5, #7c3aed); color: white; text-decoration: none; }
        .user-dropdown { position: relative; display: flex; align-items: center; gap: 8px; cursor: pointer; }
        .user-dropdown .user-avatar { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; border: 2px solid #525fe1; }
        .user-dropdown .user-name { font-weight: 600; font-size: 14px; color: #0b104a; white-space: nowrap; }
        .user-dropdown .dropdown-caret { font-size: 10px; color: #6c757d; transition: transform 0.2s; }
        .user-dropdown:hover .dropdown-caret { transform: rotate(180deg); }
        .user-dropdown-menu { display: none; position: absolute; top: 100%; right: 0; background: white; border-radius: 10px; box-shadow: 0 8px 25px rgba(0,0,0,0.12); min-width: 200px; padding: 8px 0; z-index: 1000; margin-top: 8px; }
        .user-dropdown-menu.show { display: block; }
        .user-dropdown-menu a { display: flex; align-items: center; gap: 10px; padding: 10px 18px; color: #333; text-decoration: none; font-size: 14px; font-weight: 500; transition: background 0.2s; }
        .user-dropdown-menu a:hover { background: #f5f7fa; color: #525fe1; }
        .user-dropdown-menu a i { width: 18px; text-align: center; }
        .user-dropdown-menu hr { margin: 6px 0; border-color: #eee; }
    </style>
</head>

<body data-spy="scroll" data-offset="80">

    <!-- START PRELOADER -->
    <div class="preloaders"><span class="loader"></span></div>
    <!-- END PRELOADER -->

    <!-- START NAVBAR (unifie via _navbar.php) -->
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/gestion_users/view/template/_navbar.php'; ?>
    <!-- END NAVBAR -->

    <!-- START SECTION TOP -->
    <section class="section-top">
        <div class="container">
            <div class="col-lg-10 offset-lg-1 text-center">
                <div class="section-top-title wow fadeInRight" data-wow-duration="1s" data-wow-delay="0.3s" data-wow-offset="0">
                    <h1>EduFeed</h1>
                    <ul>
                        <li><a href="<?= $baseUrl ?>/view/template/index.php">Home</a></li>
                        <li> / Soumettre</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
    <!-- END SECTION TOP -->

    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2">

                    <!-- =========================================== -->
                    <!--        FORMULAIRE 1 : SOUMETTRE UN DEVOIR   -->
                    <!--        Visible : etudiant + admin           -->
                    <!-- =========================================== -->
                    <?php if ($canSubmitDevoir): ?>
                    <div class="modern-form-container" id="form-devoir-section">
                        <h3 class="form-title">
                            <i class="fas fa-file-upload"></i>
                            <?= $isEditDevoir ? 'Modifier un Devoir' : 'Soumettre un Devoir' ?>
                        </h3>
                        <p class="form-subtitle">
                            <?= $isEditDevoir
                                ? 'Mettez à jour les champs du devoir existant'
                                : 'Remplissez tous les champs pour soumettre votre devoir' ?>
                        </p>

                        <?php if (!empty($successMessage)): ?>
                            <div class="alert-modern success">
                                <i class="fas fa-check-circle"></i>
                                <?= htmlspecialchars($successMessage) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($formErrors)): ?>
                            <div class="alert-modern error">
                                <i class="fas fa-exclamation-triangle"></i>
                                <?= htmlspecialchars(implode(' ', $formErrors)) ?>
                            </div>
                        <?php endif; ?>

                        <!-- Barre de progression -->
                        <div class="form-progress">
                            <div class="form-progress-label" id="devoir-progress-label">Progression : 0 / 9 champs remplis</div>
                            <div class="progress">
                                <div class="progress-bar" id="devoir-progress-bar" style="width: 0%"></div>
                            </div>
                        </div>

                        <!-- ACTION → devoirs.php (submit) -->
                        <form id="form-devoir"
                              action="<?= htmlspecialchars($devoirFormAction) ?>"
                              method="POST"
                              enctype="multipart/form-data"
                              novalidate>

                            <?php if ($isEditDevoir): ?>
                                <input type="hidden" name="id_devoir" value="<?= htmlspecialchars((string)$editDevoir['id_devoir']) ?>">
                            <?php endif; ?>

                            <!-- TITRE -->
                            <div class="form-group">
                                <label for="titre">
                                    <i class="fas fa-heading"></i> Titre du devoir
                                    <span class="required-star">*</span>
                                </label>
                                <input type="text"
                                       name="titre"
                                       id="titre"
                                       class="form-control"
                                       placeholder="Ex: Algorithme de tri à bulles"
                                      
                                      
                                        value="<?= htmlspecialchars(oldOrEdit('titre', $oldData, $editDevoir)) ?>"
                                      >
                                <span class="field-feedback" id="fb-titre"></span>
                                <small class="hint">Donnez un titre descriptif (3 à 150 caractères)</small>
                            </div>

                            <!-- DESCRIPTION -->
                            <div class="form-group">
                                <label for="description">
                                    <i class="fas fa-align-left"></i> Description
                                    <span class="required-star">*</span>
                                </label>
                                <textarea name="description"
                                          id="description"
                                          class="form-control"
                                          placeholder="Décrivez le contexte et les défis du devoir..."
                                         
                                         
                                         ><?= htmlspecialchars(oldOrEdit('description', $oldData, $editDevoir)) ?></textarea>
                                <span class="char-counter" id="counter-description">0 / 1000</span>
                                <span class="field-feedback" id="fb-description"></span>
                            </div>

                            <!-- FICHIER CODE -->
                            <div class="form-group">
                                <label for="file1">
                                    <i class="fas fa-file-code"></i> Fichier du code
                                    <span class="required-star">*</span>
                                </label>
                                <input type="file"
                                       name="file1"
                                       id="file1"
                                       class="form-control"
                                       accept=".py,.js,.java,.cpp,.c,.png,.jpg,.jpeg"
                                       >
                                <div class="file-preview" id="preview-file1">
                                    <i class="fas fa-check-circle"></i>
                                    <span id="preview-file1-name"></span>
                                </div>
                                <span class="field-feedback" id="fb-file1"></span>
                                <small class="hint">Formats : .py .js .java .cpp .c .png .jpg .jpeg</small>
                                <?php if ($isEditDevoir && !empty($editDevoir['fichier'])): ?>
                                    <small class="hint">Fichier actuel : <?= htmlspecialchars($editDevoir['fichier']) ?></small>
                                <?php endif; ?>
                            </div>

                            <!-- DATE SOUMISSION -->
                            <div class="form-group">
                                <label for="date_soumission">
                                    <i class="fas fa-calendar-alt"></i> Date de soumission
                                    <span class="required-star">*</span>
                                </label>
                                <input type="date"
                                       name="date_soumission"
                                       id="date_soumission"
                                       class="form-control"
                                        value="<?= htmlspecialchars(oldOrEdit('date_soumission', $oldData, $editDevoir)) ?>"
                                      >
                                <span class="field-feedback" id="fb-date_soumission"></span>
                            </div>

                            <!-- NIVEAU DIFFICULTÉ -->
                            <div class="form-group">
                                <label for="niveau_difficulte">
                                    <i class="fas fa-graduation-cap"></i> Niveau de difficulté
                                    <span class="required-star">*</span>
                                </label>
                                <select name="niveau_difficulte" id="niveau_difficulte" class="form-control" 

                               >
                                    <option value="">-- Sélectionnez un niveau --</option>
                                    <option value="facile" <?= oldOrEdit('niveau_difficulte', $oldData, $editDevoir) === 'facile' ? 'selected' : '' ?>>🟢 Facile</option>
                                    <option value="moyen" <?= oldOrEdit('niveau_difficulte', $oldData, $editDevoir) === 'moyen' ? 'selected' : '' ?>>🟡 Moyen</option>
                                    <option value="difficile" <?= oldOrEdit('niveau_difficulte', $oldData, $editDevoir) === 'difficile' ? 'selected' : '' ?>>🔴 Difficile</option>
                                </select>
                                <span class="field-feedback" id="fb-niveau_difficulte"></span>
                            </div>

                            <!-- TYPE ERREUR PREDOMINANT -->
                            <div class="form-group">
                                <label for="type_erreur_predominant">
                                    <i class="fas fa-exclamation-triangle"></i> Type d'erreur prédominant
                                    <span class="required-star">*</span>
                                </label>
                                <select name="type_erreur_predominant" id="type_erreur_predominant" class="form-control">
                                    <option value="">-- Sélectionnez un type --</option>
                                    <option value="logique" <?= oldOrEdit('type_erreur_predominant', $oldData, $editDevoir) === 'logique' ? 'selected' : '' ?>>⚙️ Logique</option>
                                    <option value="syntaxe" <?= oldOrEdit('type_erreur_predominant', $oldData, $editDevoir) === 'syntaxe' ? 'selected' : '' ?>>📝 Syntaxe</option>
                                    <option value="comprehension" <?= oldOrEdit('type_erreur_predominant', $oldData, $editDevoir) === 'comprehension' ? 'selected' : '' ?>>🧠 Compréhension</option>
                                </select>
                                <span class="field-feedback" id="fb-type_erreur_predominant"></span>
                            </div>

                            <!-- TEMPS ESTIMÉ -->
                            <div class="form-group">
                                <label for="temps_estime_resolution">
                                    <i class="fas fa-clock"></i> Temps estimé de résolution (minutes)
                                    <span class="required-star">*</span>
                                </label>
                                <input type="number"
                                       name="temps_estime_resolution"
                                       id="temps_estime_resolution"
                                       class="form-control"
                                       placeholder="Ex: 45"
                                      
                                      
                                    value="<?= htmlspecialchars(oldOrEdit('temps_estime_resolution', $oldData, $editDevoir)) ?>"
                                      >
                                <span class="field-feedback" id="fb-temps_estime_resolution"></span>
                                <small class="hint">Entre 1 et 480 minutes</small>
                            </div>

                            <!-- PROGRESSION ÉLÈVE -->
                            <div class="form-group">
                                <label for="progression_eleve">
                                    <i class="fas fa-percentage"></i> Progression de l'élève (%)
                                    <span class="required-star">*</span>
                                </label>
                                <input type="number"
                                       name="progression_eleve"
                                       id="progression_eleve"
                                       class="form-control"
                                       placeholder="Ex: 75"
                                      
                                      
                                    value="<?= htmlspecialchars(oldOrEdit('progression_eleve', $oldData, $editDevoir)) ?>"
                                      >
                                <span class="field-feedback" id="fb-progression_eleve"></span>
                                <small class="hint">Pourcentage entre 0 et 100</small>
                            </div>

                            <!-- MOTS CLÉS -->
                            <div class="form-group">
                                <label for="mots_cles">
                                    <i class="fas fa-tags"></i> Mots clés
                                    <span class="required-star">*</span>
                                </label>
                                <input type="text"
                                       name="mots_cles"
                                       id="mots_cles"
                                       class="form-control"
                                       placeholder="Ex: SQL, jointures, récursion, pointeurs"
                                        value="<?= htmlspecialchars(oldOrEdit('mots_cles', $oldData, $editDevoir)) ?>"
                                      >
                                <span class="field-feedback" id="fb-mots_cles"></span>
                                <small class="hint">Séparez les mots clés par des virgules</small>
                            </div>

                            <!-- URGENCE -->
                            <div class="form-group">
                                <label for="urgence">
                                    <i class="fas fa-exclamation-circle"></i> Niveau d'urgence
                                    <span class="required-star">*</span>
                                </label>
                                <select name="urgence" id="urgence" class="form-control">
                                    <option value="">-- Sélectionnez l'urgence --</option>
                                    <option value="faible" <?= oldOrEdit('urgence', $oldData, $editDevoir) === 'faible' ? 'selected' : '' ?>>🟢 Faible</option>
                                    <option value="moyenne" <?= oldOrEdit('urgence', $oldData, $editDevoir) === 'moyenne' ? 'selected' : '' ?>>🟡 Moyenne</option>
                                    <option value="urgente" <?= oldOrEdit('urgence', $oldData, $editDevoir) === 'urgente' ? 'selected' : '' ?>>🔴 Urgente</option>
                                </select>
                                <span class="field-feedback" id="fb-urgence"></span>
                            </div>

                            <button type="submit" class="btn btn-submit" id="btn-devoir">
                                <span class="spinner"></span>
                                <span class="btn-text">
                                    <i class="fas <?= $isEditDevoir ? 'fa-save' : 'fa-paper-plane' ?>"></i>&nbsp;
                                    <?= $isEditDevoir ? 'Enregistrer la modification' : 'Publier le Devoir' ?>
                                </span>
                            </button>
                        </form>
                    </div>
                    <?php endif; // canSubmitDevoir ?>

                    <!-- ============================================ -->
                    <!--      FORMULAIRE 2 : SOUMETTRE UNE CORRECTION -->
                    <!--      Visible : encadrant + admin             -->
                    <!-- ============================================ -->
                    <?php if ($canSubmitCorrection): ?>
                    <div class="modern-form-container" id="form-correction-section">
                        <h3 class="form-title">
                            <i class="fas fa-check-circle"></i>
                            <?= $isEditCorrection ? 'Modifier une Correction' : 'Soumettre une Correction' ?>
                        </h3>
                        <p class="form-subtitle">
                            <?= $isEditCorrection
                                ? 'Mettez à jour les champs de la correction existante'
                                : 'Remplissez tous les champs pour corriger un devoir' ?>
                        </p>

                        <!-- Barre de progression correction -->
                        <div class="form-progress">
                            <div class="form-progress-label" id="correction-progress-label">Progression : 0 / 9 champs remplis</div>
                            <div class="progress">
                                <div class="progress-bar" style="background: linear-gradient(90deg, #10B981, #059669);"
                                     id="correction-progress-bar" style="width:0%"></div>
                            </div>
                        </div>

                        <!-- ACTION → devoirs.php (correct) -->
                        <form id="form-correction"
                              action="<?= htmlspecialchars($correctionFormAction) ?>"
                              method="POST"
                              enctype="multipart/form-data"
                              novalidate>

                            <?php if ($isEditCorrection): ?>
                                <input type="hidden" name="id_correction" value="<?= htmlspecialchars((string)$editCorrection['id_correction']) ?>">
                            <?php endif; ?>

                            <!-- ID DEVOIR (sélection) -->
                            <div class="form-group">
                                <label for="id_devoir">
                                    <i class="fas fa-link"></i> Quel devoir voulez-vous corriger ? 
                                    <span class="required-star">*</span>
                                </label>
                                <select name="id_devoir" id="id_devoir" class="form-control">
                                    <option value="">-- Sélectionnez un devoir --</option>
                                    <?php foreach ($devoirs as $d): ?>
                                        <option value="<?= htmlspecialchars($d['id_devoir']) ?>" <?= (oldOrEdit('id_devoir', $oldData, $editCorrection) === (string)$d['id_devoir']) 
        || ($prefillDevoirId === (int)$d['id_devoir']) ? 'selected' : '' ?>>
                                             <?= htmlspecialchars($d['titre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="field-feedback" id="fb-id_devoir"></span>
                                <small class="hint">Choisissez l'ID du devoir concerné avant de soumettre la correction.</small>
                            </div>

                            <!-- COMMENTAIRE -->
                            <div class="form-group">
                                <label for="commentaire">
                                    <i class="fas fa-comment-dots"></i> Commentaire
                                    <span class="required-star">*</span>
                                </label>
                                <textarea name="commentaire"
                                          id="commentaire"
                                          class="form-control"
                                          placeholder="Donnez votre feedback détaillé..."
                                         
                                         
                                         ><?= htmlspecialchars(oldOrEdit('commentaire', $oldData, $editCorrection)) ?></textarea>
                                <span class="char-counter" id="counter-commentaire">0 / 1000</span>
                                <span class="field-feedback" id="fb-commentaire"></span>
                            </div>

                            <!-- FICHIER CORRIGÉ -->
                            <div class="form-group">
                                <label for="file2">
                                    <i class="fas fa-file-code"></i> Fichier corrigé
                                    <span class="required-star">*</span>
                                </label>
                                <input type="file"
                                       name="file2"
                                       id="file2"
                                       class="form-control"
                                       accept=".py,.js,.java,.cpp,.c,.png,.jpg,.jpeg"
                                       >
                                <div class="file-preview" id="preview-file2">
                                    <i class="fas fa-check-circle"></i>
                                    <span id="preview-file2-name"></span>
                                </div>
                                <span class="field-feedback" id="fb-file2"></span>
                                <small class="hint">Formats : .py .js .java .cpp .c</small>
                                <?php if ($isEditCorrection && !empty($editCorrection['fichier_corrige'])): ?>
                                    <small class="hint">Fichier actuel : <?= htmlspecialchars($editCorrection['fichier_corrige']) ?></small>
                                <?php endif; ?>
                            </div>

                            <!-- DATE CORRECTION -->
                            <div class="form-group">
                                <label for="date_correction">
                                    <i class="fas fa-calendar-check"></i> Date de correction
                                    <span class="required-star">*</span>
                                </label>
                                <input type="date"
                                       name="date_correction"
                                       id="date_correction"
                                       class="form-control"
                                    value="<?= htmlspecialchars(oldOrEdit('date_correction', $oldData, $editCorrection)) ?>"
                                      >
                                <span class="field-feedback" id="fb-date_correction"></span>
                            </div>

                            <!-- TYPE FEEDBACK -->
                            <div class="form-group">
                                <label for="type_feedback">
                                    <i class="fas fa-comment"></i> Type de feedback
                                    <span class="required-star">*</span>
                                </label>
                                <select name="type_feedback" id="type_feedback" class="form-control" 

                               >
                                    <option value="">-- Sélectionnez un type --</option>
                                    <option value="explicatif" <?= oldOrEdit('type_feedback', $oldData, $editCorrection) === 'explicatif' ? 'selected' : '' ?>>📖 Explicatif</option>
                                    <option value="direct" <?= oldOrEdit('type_feedback', $oldData, $editCorrection) === 'direct' ? 'selected' : '' ?>>⚡ Direct</option>
                                    <option value="guide" <?= oldOrEdit('type_feedback', $oldData, $editCorrection) === 'guide' ? 'selected' : '' ?>>🧭 Guidé</option>
                                </select>
                                <span class="field-feedback" id="fb-type_feedback"></span>
                            </div>

                            <!-- NOTE ESTIMÉE -->
                            <div class="form-group">
                                <label for="note_estimee">
                                    <i class="fas fa-star"></i> Note estimée (/20)
                                    <span class="required-star">*</span>
                                </label>
                                <input type="number"
                                       name="note_estimee"
                                       id="note_estimee"
                                       class="form-control"
                                       placeholder="Ex: 15"
                                      
                                      
                                      
                                    value="<?= htmlspecialchars(oldOrEdit('note_estimee', $oldData, $editCorrection)) ?>"
                                      >
                                <span class="field-feedback" id="fb-note_estimee"></span>
                                <small class="hint">Valeur entre 0 et 20 (pas de 0.5)</small>
                            </div>

                            <!-- COMPÉTENCES ÉVALUÉES -->
                            <div class="form-group">
                                <label for="competences_evaluees">
                                    <i class="fas fa-brain"></i> Compétences évaluées
                                    <span class="required-star">*</span>
                                </label>
                                <input type="text"
                                       name="competences_evaluees"
                                       id="competences_evaluees"
                                       class="form-control"
                                       placeholder="Ex: Algorithmique, Français, Physique"
                                    value="<?= htmlspecialchars(oldOrEdit('competences_evaluees', $oldData, $editCorrection)) ?>"
                                      >
                                <span class="field-feedback" id="fb-competences_evaluees"></span>
                                <small class="hint">Séparez les compétences par des virgules</small>
                            </div>

                            <!-- NOMBRE D'ITÉRATIONS -->
                            <div class="form-group">
                                <label for="nombre_iterations">
                                    <i class="fas fa-sync-alt"></i> Nombre d'itérations
                                    <span class="required-star">*</span>
                                </label>
                                <input type="number"
                                       name="nombre_iterations"
                                       id="nombre_iterations"
                                       class="form-control"
                                       placeholder="Ex: 2"
                                      
                                      
                                    value="<?= htmlspecialchars(oldOrEdit('nombre_iterations', $oldData, $editCorrection)) ?>"
                                      >
                                <span class="field-feedback" id="fb-nombre_iterations"></span>
                                <small class="hint">Entre 1 et 10 itérations</small>
                            </div>

                            <!-- SUGGESTIONS PERSONNALISÉES -->
                            <div class="form-group">
                                <label for="suggestions_personnalisees">
                                    <i class="fas fa-lightbulb"></i> Suggestions personnalisées
                                </label>
                                <textarea name="suggestions_personnalisees"
                                          id="suggestions_personnalisees"
                                          class="form-control"
                                          placeholder="Suggérez des améliorations et ressources..."
                                         ><?= htmlspecialchars(oldOrEdit('suggestions_personnalisees', $oldData, $editCorrection)) ?></textarea>
                                <span class="char-counter" id="counter-suggestions">0 / 800</span>
                            </div>

                            <!-- RESSOURCES RECOMMANDÉES -->
                            <div class="form-group">
                                <label for="ressources_recommandees">
                                    <i class="fas fa-link"></i> Ressources recommandées
                                </label>
                                <input type="text"
                                       name="ressources_recommandees"
                                       id="ressources_recommandees"
                                       class="form-control"
                                       placeholder="Ex: https://exemple.com, https://tutoriel.com"
                                        value="<?= htmlspecialchars(oldOrEdit('ressources_recommandees', $oldData, $editCorrection)) ?>">
                                <small class="hint">Liens séparés par des virgules</small>
                            </div>

                            <!-- RAPIDITÉ CORRECTION -->
                            <div class="form-group">
                                <label for="rapidite_correction">
                                    <i class="fas fa-hourglass-end"></i> Rapidité de correction (minutes)
                                    <span class="required-star">*</span>
                                </label>
                                <input type="number"
                                       name="rapidite_correction"
                                       id="rapidite_correction"
                                       class="form-control"
                                       placeholder="Ex: 30"
                                      
                                      
                                    value="<?= htmlspecialchars(oldOrEdit('rapidite_correction', $oldData, $editCorrection)) ?>"
                                      >
                                <span class="field-feedback" id="fb-rapidite_correction"></span>
                                <small class="hint">Entre 1 et 480 minutes</small>
                            </div>

                            <!-- TON DU FEEDBACK -->
                            <div class="form-group">
                                <label for="ton_feedback">
                                    <i class="fas fa-smile"></i> Ton du feedback
                                    <span class="required-star">*</span>
                                </label>
                                <select name="ton_feedback" id="ton_feedback" class="form-control">
                                    <option value="">-- Sélectionnez un ton --</option>
                                    <option value="encourageant" <?= oldOrEdit('ton_feedback', $oldData, $editCorrection) === 'encourageant' ? 'selected' : '' ?>>😊 Encourageant</option>
                                    <option value="strict" <?= oldOrEdit('ton_feedback', $oldData, $editCorrection) === 'strict' ? 'selected' : '' ?>>😤 Strict</option>
                                    <option value="neutre" <?= oldOrEdit('ton_feedback', $oldData, $editCorrection) === 'neutre' ? 'selected' : '' ?>>😐 Neutre</option>
                                </select>
                                <span class="field-feedback" id="fb-ton_feedback"></span>
                            </div>

                            <!-- Ajouter après le champ commentaire ou avant le bouton submit -->
<div class="form-group">
    <button type="button" id="aiAssistBtn" class="btn btn-ai">
        <i class="fas fa-magic"></i> 🤖 Générer correction avec IA
    </button>
    <small class="hint">L'IA va analyser le devoir sélectionné et proposer une correction automatique</small>
</div>

                            <button type="submit" class="btn btn-submit btn-submit-correction" id="btn-correction">
                                <span class="spinner"></span>
                                <span class="btn-text">
                                    <i class="fas <?= $isEditCorrection ? 'fa-save' : 'fa-check' ?>"></i>&nbsp;
                                    <?= $isEditCorrection ? 'Enregistrer la modification' : 'Soumettre la Correction' ?>
                                </span>
                            </button>
                        </form>
                    </div>
                    <?php endif; // canSubmitCorrection ?>

                </div><!-- /col -->
            </div><!-- /row -->
        </div><!-- /container -->
    </section>

    <!-- START MODERN FOOTER -->
    <footer class="modern-footer bg-dark text-white py-5">
      <div class="container">
        <div class="row">
          <div class="col-lg-4 col-md-6 mb-4">
            <div class="footer-brand">
              <a href="<?= $baseUrl ?>/view/template/index.php" class="text-decoration-none">
                <img src="../../assets/img/logo.png" alt="Logo EduMatch" class="mb-3" style="height: 50px;">
                <h3 class="text-white fw-bold">EduMatch</h3>
              </a>
              <p class="mt-3 text-light opacity-75">
                Plateforme intelligente de mise en relation des etudiants avec des professeurs experts dans toutes les matieres academiques pour des experiences d'apprentissage personnalisees.
              </p>
              <div class="social-links mt-3">
                <a href="#" class="text-white me-3 fs-4"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="text-white me-3 fs-4"><i class="fab fa-twitter"></i></a>
                <a href="#" class="text-white me-3 fs-4"><i class="fab fa-linkedin-in"></i></a>
                <a href="#" class="text-white me-3 fs-4"><i class="fab fa-instagram"></i></a>
              </div>
            </div>
          </div>
          <div class="col-lg-2 col-md-6 mb-4">
            <h5 class="fw-bold mb-3">Plateforme</h5>
            <ul class="list-unstyled">
              <li class="mb-2"><a href="<?= $baseUrl ?>/view/frontoffice/submit.php" class="text-light text-decoration-none">Soumettre une demande</a></li>
              <li class="mb-2"><a href="<?= $baseUrl ?>/view/frontoffice/feed.php" class="text-light text-decoration-none">Mises en relation</a></li>
              <li class="mb-2"><a href="#" class="text-light text-decoration-none">Comment ca marche</a></li>
              <li class="mb-2"><a href="#" class="text-light text-decoration-none">Etre mis en relation</a></li>
            </ul>
          </div>
          <div class="col-lg-2 col-md-6 mb-4">
            <h5 class="fw-bold mb-3">Matieres academiques</h5>
            <ul class="list-unstyled">
              <li class="mb-2"><a href="#" class="text-light text-decoration-none">Mathematiques</a></li>
              <li class="mb-2"><a href="#" class="text-light text-decoration-none">Sciences</a></li>
							<li class="mb-2"><a href="#" class="text-light text-decoration-none">Programmation</a></li>
							<li class="mb-2"><a href="#" class="text-light text-decoration-none">Algorithmique</a></li>
              <li class="mb-2"><a href="#" class="text-light text-decoration-none">Langues</a></li>
              <li class="mb-2"><a href="#" class="text-light text-decoration-none">Sciences humaines</a></li>
            </ul>
          </div>
          <div class="col-lg-4 col-md-6 mb-4">
            <h5 class="fw-bold mb-3">Coordonnees</h5>
            <div class="contact-info">
              <p class="mb-2"><i class="fas fa-map-marker-alt me-2"></i>Tunisia,Tunis</p>
              <p class="mb-2"><i class="fas fa-phone me-2"></i>+216 90 549 254</p>
              <p class="mb-2"><i class="fas fa-envelope me-2"></i>edumatch@gmail.com</p>
            </div>
            <div class="newsletter mt-3">
              <h6 class="fw-bold mb-2">Restez informe sur le tutorat academique</h6>
              <div class="input-group">
                <input type="email" class="form-control" placeholder="Votre email" style="border-radius: 25px 0 0 25px;">
                <button class="btn btn-primary" type="button" style="border-radius: 0 25px 25px 0;">S'abonner</button>
              </div>
            </div>
          </div>
        </div>
        <hr class="my-4 opacity-25">
        <div class="row align-items-center">
          <div class="col-md-6">
            <p class="mb-0 text-light opacity-75">&copy; 2026 EduMatch. Tous droits reserves.</p>
          </div>
          <div class="col-md-6 text-md-end">
            <a href="#" class="text-light text-decoration-none me-3">Politique de confidentialite</a>
            <a href="#" class="text-light text-decoration-none me-3">Conditions d'utilisation</a>
            <a href="#" class="text-light text-decoration-none">Assistance</a>
          </div>
        </div>
      </div>
    </footer>
    <!-- END MODERN FOOTER -->

    <script src="../../assets/js/jquery-1.12.4.min.js"></script>
    <script src="../../assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="../../assets/js/modernizr-2.8.3.min.js"></script>
    <script src="../../assets/js/jquery-simple-mobilemenu.js"></script>
    <script src="../../assets/owlcarousel/js/owl.carousel.min.js"></script>
    <script src="../../assets/js/jquery.magnific-popup.min.js"></script>
    <script src="../../assets/js/jquery.inview.min.js"></script>
    <script src="../../assets/js/scrolltopcontrol.js"></script>
    <script src="../../assets/js/wow.min.js"></script>
    <script src="../../assets/js/scripts.js"></script>

    <script>
    // ============================================================
    //   MESSAGES D'ERREUR PAR CHAMP
    // ============================================================
    const MESSAGES = {
        titre:                  { empty: 'Le titre est requis.', short: 'Minimum 3 caractères.', ok: 'Titre valide ✓' },
        description:            { empty: 'La description est requise.', short: 'Minimum 10 caractères.', ok: 'Description valide ✓' },
        file1:                  { empty: 'Veuillez choisir un fichier.', ok: 'Fichier sélectionné ✓' },
        date_soumission:        { empty: 'La date est requise.', ok: 'Date valide ✓' },
        niveau_difficulte:      { empty: 'Veuillez sélectionner un niveau.', ok: 'Niveau sélectionné ✓' },
        type_erreur_predominant:{ empty: 'Veuillez sélectionner un type d\'erreur.', ok: 'Type sélectionné ✓' },
        temps_estime_resolution:{ empty: 'Le temps estimé est requis.', range: 'Valeur entre 1 et 480.', ok: 'Temps valide ✓' },
        progression_eleve:      { empty: 'La progression est requise.', range: 'Valeur entre 0 et 100.', ok: 'Progression valide ✓' },
        mots_cles:              { empty: 'Les mots clés sont requis.', ok: 'Mots clés valides ✓' },
        urgence:                { empty: 'Veuillez sélectionner l\'urgence.', ok: 'Urgence sélectionnée ✓' },
        // Correction
        id_devoir:              { empty: 'Veuillez sélectionner un devoir.', ok: 'Devoir sélectionné ✓' },
        commentaire:            { empty: 'Le commentaire est requis.', short: 'Minimum 10 caractères.', ok: 'Commentaire valide ✓' },
        file2:                  { empty: 'Veuillez choisir un fichier corrigé.', ok: 'Fichier sélectionné ✓' },
        date_correction:        { empty: 'La date de correction est requise.', ok: 'Date valide ✓' },
        type_feedback:          { empty: 'Veuillez sélectionner un type de feedback.', ok: 'Type sélectionné ✓' },
        note_estimee:           { empty: 'La note est requise.', range: 'Note entre 0 et 20.', ok: 'Note valide ✓' },
        competences_evaluees:   { empty: 'Les compétences sont requises.', ok: 'Compétences valides ✓' },
        nombre_iterations:      { empty: 'Le nombre d\'itérations est requis.', range: 'Valeur entre 1 et 10.', ok: 'Valide ✓' },
        rapidite_correction:    { empty: 'La rapidité est requise.', range: 'Valeur entre 1 et 480.', ok: 'Valide ✓' },
        ton_feedback:           { empty: 'Veuillez sélectionner un ton.', ok: 'Ton sélectionné ✓' },
    };

    const isEditDevoir = <?= $isEditDevoir ? 'true' : 'false' ?>;
    const isEditCorrection = <?= $isEditCorrection ? 'true' : 'false' ?>;

    const VALIDATION_RULES = {
        titre:                   { required: true, minLength: 3, maxLength: 150 },
        description:             { required: true, minLength: 10, maxLength: 1000 },
        file1:                   { required: !isEditDevoir },
        date_soumission:         { required: true },
        niveau_difficulte:       { required: true },
        type_erreur_predominant: { required: true },
        temps_estime_resolution: { required: true, min: 1, max: 480 },
        progression_eleve:       { required: true, min: 0, max: 100 },
        mots_cles:               { required: true },
        urgence:                 { required: true },
        id_devoir:               { required: true },
        commentaire:             { required: true, minLength: 10, maxLength: 1000 },
        file2:                   { required: !isEditCorrection },
        date_correction:         { required: true },
        type_feedback:           { required: true },
        note_estimee:            { required: true, min: 0, max: 20 },
        competences_evaluees:    { required: true },
        nombre_iterations:       { required: true, min: 1, max: 10 },
        suggestions_personnalisees: { required: false, maxLength: 800 },
        ressources_recommandees: { required: false },
        rapidite_correction:     { required: true, min: 1, max: 480 },
        ton_feedback:            { required: true },
    };

    function getRuleForField(field) {
        const key = field.id || field.name || '';
        return VALIDATION_RULES[key] || null;
    }

    function getRequiredFields(form) {
        return Array.from(form.querySelectorAll('input, select, textarea')).filter(function(field) {
            const rule = getRuleForField(field);
            return !!(rule && rule.required);
        });
    }

    // ============================================================
    //   AFFICHER FEEDBACK SOUS UN CHAMP
    // ============================================================
    function showFeedback(fieldId, type, message) {
        const el = document.getElementById('fb-' + fieldId);
        if (!el) return;
        el.textContent = (type === 'error' ? '⚠ ' : '✓ ') + message;
        el.className = 'field-feedback ' + type;
    }
    function clearFeedback(fieldId) {
        const el = document.getElementById('fb-' + fieldId);
        if (!el) return;
        el.textContent = '';
        el.className = 'field-feedback';
    }

    // ============================================================
    //   VALIDER UN CHAMP
    // ============================================================
    function validateField(field, mode = 'live') {
        const id   = field.id || field.name;
        const rule = VALIDATION_RULES[id] || null;
        if (!rule) return true;

        const msgs = MESSAGES[id] || {};
        const isSubmitValidation = mode === 'submit';
        const isTouched = field.dataset.touched === '1';
        const isFile = field.type === 'file';

        const rawValue = typeof field.value === 'string' ? field.value : '';
        const value = rawValue.trim();
        const hasValue = isFile
            ? (field.files && field.files.length > 0)
            : value.length > 0;
        const isRequired = !!rule.required;

        field.classList.remove('is-valid', 'is-invalid');

        // Champ requis vide
        if (isRequired && !hasValue) {
            if (isSubmitValidation || isTouched) {
                showFeedback(id, 'error', msgs.empty || 'Ce champ est requis.');
                field.classList.add('is-invalid');
            } else {
                clearFeedback(id);
            }

            return false;
        }

        // Champs optionnels vides : pas de message permanent
        if (!isRequired && !hasValue) {
            clearFeedback(id);
            return true;
        }

        // Longueur minimale
        if (!isFile && rule.minLength && value.length < rule.minLength) {
            showFeedback(id, 'error', msgs.short || `Minimum ${rule.minLength} caractères.`);
            field.classList.add('is-invalid');
            return false;
        }

        // Longueur maximale
        if (!isFile && rule.maxLength && value.length > rule.maxLength) {
            showFeedback(id, 'error', msgs.long || `Maximum ${rule.maxLength} caractères.`);
            field.classList.add('is-invalid');
            return false;
        }

        // Plage numérique
        if (field.type === 'number' && value !== '') {
            const val = parseFloat(value);
            const min = (typeof rule.min === 'number') ? rule.min : -Infinity;
            const max = (typeof rule.max === 'number') ? rule.max : Infinity;
            if (val < min || val > max) {
                showFeedback(id, 'error', msgs.range || `Valeur entre ${min} et ${max}.`);
                field.classList.add('is-invalid');
                return false;
            }
        }

        // Valide
        if (hasValue) {
            showFeedback(id, 'success', msgs.ok || 'Valide ✓');
            field.classList.add('is-valid');
        } else {
            clearFeedback(id);
        }

        return true;
    }

    // ============================================================
    //   BARRE DE PROGRESSION
    // ============================================================
    function updateProgress(formId, barId, labelId, total) {
        const form   = document.getElementById(formId);
        const bar    = document.getElementById(barId);
        const label  = document.getElementById(labelId);
        if (!form || !bar || !label) return;

        const required = getRequiredFields(form);
        let filled = 0;
        required.forEach(f => {
            if (f.type === 'file') { if (f.files && f.files.length > 0) filled++; }
            else if (f.value.trim()) filled++;
        });
        const pct = required.length > 0 ? Math.round((filled / required.length) * 100) : 0;
        bar.style.width = pct + '%';
        label.textContent = `Progression : ${filled} / ${required.length} champs remplis`;
    }

    // ============================================================
    //   COMPTEUR DE CARACTÈRES
    // ============================================================
    function setupCharCounter(textareaId, counterId, max) {
        const ta = document.getElementById(textareaId);
        const counter = document.getElementById(counterId);
        if (!ta || !counter) return;
        ta.addEventListener('input', function() {
            const len = this.value.length;
            counter.textContent = `${len} / ${max}`;
            counter.className = 'char-counter';
            if (len > max * 0.9) counter.classList.add('warning');
            if (len >= max) counter.classList.add('over');
        });
    }

    // ============================================================
    //   PRÉVISUALISATION FICHIER
    // ============================================================
    function setupFilePreview(inputId, previewId, previewNameId) {
        const input   = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        const nameEl  = document.getElementById(previewNameId);
        if (!input || !preview || !nameEl) return;
        input.addEventListener('change', function() {
            if (this.files && this.files.length > 0) {
                const file = this.files[0];
                const size = (file.size / 1024).toFixed(1);
                nameEl.textContent = `${file.name} (${size} Ko)`;
                preview.classList.add('show');
                showFeedback(inputId, 'success', MESSAGES[inputId]?.ok || 'Fichier sélectionné ✓');
                this.classList.add('is-valid');
                this.classList.remove('is-invalid');
            } else {
                preview.classList.remove('show');
            }
            updateProgress('form-devoir',     'devoir-progress-bar',     'devoir-progress-label');
            updateProgress('form-correction', 'correction-progress-bar', 'correction-progress-label');
        });
    }

    function showPopup(message, type) {
        const popup = document.createElement('div');
        popup.className = 'popup-message ' + type;
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
        popup.innerHTML = '<i class="fas ' + icon + '"></i> ' + message;
        document.body.appendChild(popup);
        setTimeout(() => {
            popup.style.animation = 'slideOutRight 0.3s ease forwards';
            setTimeout(() => popup.remove(), 300);
        }, 4000);
    }

    async function submitEditFormAjax(formElement, redirectTarget) {
        const response = await fetch(formElement.action, {
            method: 'POST',
            body: new FormData(formElement),
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const raw = await response.text();
        let payload = null;

        try {
            payload = JSON.parse(raw);
        } catch (error) {
            payload = null;
        }

        if (!response.ok || !payload || payload.success !== true) {
            throw new Error((payload && payload.message) ? payload.message : 'La modification a échoué.');
        }

        showPopup(payload.message || 'Modification enregistrée avec succès.', 'success');

        setTimeout(function() {
            window.location.href = redirectTarget;
        }, 900);
    }

    // ============================================================
    //   INITIALISATION
    // ============================================================
    document.addEventListener('DOMContentLoaded', function() {

        // Compteurs textarea
        setupCharCounter('description',               'counter-description',  1000);
        setupCharCounter('commentaire',               'counter-commentaire',  1000);
        setupCharCounter('suggestions_personnalisees','counter-suggestions',   800);

        // Previews fichiers
        setupFilePreview('file1', 'preview-file1', 'preview-file1-name');
        setupFilePreview('file2', 'preview-file2', 'preview-file2-name');

        // Validation en temps réel sur chaque champ
        document.querySelectorAll('.form-control').forEach(function(field) {
            ['blur', 'change'].forEach(function(evt) {
                field.addEventListener(evt, function() {
                    if (evt === 'change') {
                        this.dataset.touched = '1';
                    }
                    validateField(this, 'live');
                    updateProgress('form-devoir',     'devoir-progress-bar',     'devoir-progress-label');
                    updateProgress('form-correction', 'correction-progress-bar', 'correction-progress-label');
                });
            });

            if ((field.tagName === 'INPUT' && field.type !== 'file') || field.tagName === 'TEXTAREA') {
                field.addEventListener('input', function() {
                    this.dataset.touched = '1';
                    validateField(this, 'live');
                    updateProgress('form-devoir',     'devoir-progress-bar',     'devoir-progress-label');
                    updateProgress('form-correction', 'correction-progress-bar', 'correction-progress-label');
                });
            }
        });

        // ======================================================
        //   SOUMISSION FORMULAIRE DEVOIR
        // ======================================================
        const formDevoirEl = document.getElementById('form-devoir');
        if (formDevoirEl) formDevoirEl.addEventListener('submit', async function(e) {
            const requiredFields = getRequiredFields(this);
            let allValid = true;

            requiredFields.forEach(function(field) {
                if (!validateField(field, 'submit')) allValid = false;
            });

            if (!allValid) {
                e.preventDefault();
                // Scroll vers le premier champ invalide
                const firstInvalid = this.querySelector('.is-invalid');
                if (firstInvalid) firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            // Animation bouton chargement
            const btn = document.getElementById('btn-devoir');
            btn.classList.add('loading');
            btn.disabled = true;

            if (isEditDevoir) {
                e.preventDefault();

                try {
                    await submitEditFormAjax(this, '/gestion_users/view/frontoffice/feed.php?success=devoir');
                } catch (error) {
                    showPopup(error.message || 'Erreur lors de la mise à jour du devoir.', 'error');
                    btn.classList.remove('loading');
                    btn.disabled = false;
                }
            }
        });

        // ======================================================
        //   SOUMISSION FORMULAIRE CORRECTION
        // ======================================================
        const formCorrectionEl = document.getElementById('form-correction');
        if (formCorrectionEl) formCorrectionEl.addEventListener('submit', async function(e) {
            const requiredFields = getRequiredFields(this);
            let allValid = true;

            requiredFields.forEach(function(field) {
                if (!validateField(field, 'submit')) allValid = false;
            });

            if (!allValid) {
                e.preventDefault();
                const firstInvalid = this.querySelector('.is-invalid');
                if (firstInvalid) firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            const btn = document.getElementById('btn-correction');
            btn.classList.add('loading');
            btn.disabled = true;

            if (isEditCorrection) {
                e.preventDefault();

                try {
                    await submitEditFormAjax(this, '/gestion_users/view/frontoffice/feed.php?success=correction');
                } catch (error) {
                    showPopup(error.message || 'Erreur lors de la mise à jour de la correction.', 'error');
                    btn.classList.remove('loading');
                    btn.disabled = false;
                }
            }
        });
});
    
    </script>

    <script>
       // ============================================================
// ASSISTANT CORRECTION IA (Version améliorée)
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    const aiBtn = document.getElementById('aiAssistBtn');
    if (!aiBtn) return;
    
    aiBtn.addEventListener('click', async function() {
        const devoirSelect = document.getElementById('id_devoir');
        const devoirId = devoirSelect.value;
        
        if (!devoirId) {
            showAIToast('Veuillez d\'abord sélectionner un devoir à corriger', 'error');
            return;
        }
        
        const btn = this;
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Analyse du devoir...';
        
        try {
            // Récupérer les infos du devoir
            const devoirResponse = await fetch(`/gestion_users/controller/devoirs.php?action=getdevoir&id=${devoirId}`);
            
            if (!devoirResponse.ok) {
                throw new Error(`Erreur HTTP: ${devoirResponse.status}`);
            }
            
            const devoirData = await devoirResponse.json();
            
            if (!devoirData.success || !devoirData.devoir) {
                throw new Error(devoirData.message || 'Devoir non trouvé');
            }
            
            const devoir = devoirData.devoir;
            
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Génération de la correction...';
            
            const aiResponse = await fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    action: 'generate_correction',
                    titre: devoir.titre,
                    description: devoir.description,
                    niveau: devoir.niveau_difficulte
                })
            });
            
            const aiData = await aiResponse.json();
            
            // TOUS les champs avec valeurs par défaut
            const fields = {
                'commentaire': aiData.commentaire || getDefaultCommentaire(devoir.titre),
                'note_estimee': aiData.note_estimee || 13,
                'suggestions_personnalisees': aiData.suggestions || getDefaultSuggestions(),
                'competences_evaluees': Array.isArray(aiData.competences) ? aiData.competences.join(', ') : 'Logique, Algorithmique, Syntaxe',
                'type_feedback': aiData.type_feedback || 'explicatif',
                'ton_feedback': aiData.ton_feedback || 'encourageant',
                'nombre_iterations': 2,  // Valeur par défaut
                'rapidite_correction': 30  // Valeur par défaut
            };
            
            // Remplir tous les champs
            let filledCount = 0;
            for (const [fieldId, value] of Object.entries(fields)) {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.value = value;
                    field.dispatchEvent(new Event('blur'));
                    field.dispatchEvent(new Event('change'));
                    filledCount++;
                }
            }
            
            showAIToast(`✅ ${filledCount} champs remplis automatiquement !`, 'success');
            
        } catch (error) {
            console.error('Erreur détaillée:', error);
            showAIToast('❌ ' + error.message, 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    });
});

function getDefaultCommentaire(titre) {
    return "📝 Correction pour le devoir \"" + titre + "\"\n\n" +
           "L'étudiant a démontré une bonne compréhension des concepts. " +
           "Le code est bien structuré, mais quelques améliorations sont possibles pour optimiser les performances.\n\n" +
           "Points positifs : Logique claire et bonne organisation.\n" +
           "Points à améliorer : Ajouter plus de commentaires et gérer les cas limites.";
}

function getDefaultSuggestions() {
    return "1. Ajoutez des commentaires pour expliquer les étapes importantes\n" +
           "2. Testez votre code avec différentes entrées (valeurs extrêmes)\n" +
           "3. Utilisez des noms de variables plus explicites\n" +
           "4. Pensez à la réutilisabilité de vos fonctions";
}

function showAIToast(message, type) {
    const toast = document.createElement('div');
    toast.className = 'ai-toast ' + (type === 'warning' ? 'success' : type);
    const icon = type === 'success' ? 'fa-check-circle' : (type === 'warning' ? 'fa-exclamation-triangle' : 'fa-exclamation-triangle');
    const bgColor = type === 'warning' ? '#F59E0B' : (type === 'success' ? '#10B981' : '#EF4444');
    toast.style.background = bgColor;
    toast.innerHTML = `<i class="fas ${icon}"></i> ${message}`;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOutRight 0.3s ease forwards';
        setTimeout(() => toast.remove(), 400);
    }, 4000);
}
    </script>
</body>
</html>

