<?php
require_once __DIR__ . '/../../config/database.php';
$conn = getDBConnection();

// Récupération des paramètres GET pour recherche et tri
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'date_desc';
$level = isset($_GET['level']) ? $_GET['level'] : '';
$urgent = isset($_GET['urgent']) ? $_GET['urgent'] : '';

// Construction de la requête SQL pour les devoirs
$sqlDevoirs = "
    SELECT id_devoir, titre, description, fichier, date_soumission,
           niveau_difficulte, type_erreur_predominant,
           temps_estime_resolution, progression_eleve,
           mots_cles, urgence,
           sentiment, sentiment_score, alerte_urgence
    FROM devoirs
    WHERE 1=1
";

$params = array();

// Filtre recherche
if (!empty($search)) {
    $sqlDevoirs .= " AND (titre LIKE :search OR description LIKE :search OR mots_cles LIKE :search)";
    $params[':search'] = "%$search%";
}

// Filtre niveau
if (!empty($level)) {
    $sqlDevoirs .= " AND niveau_difficulte = :level";
    $params[':level'] = $level;
}

// Filtre urgence
if (!empty($urgent)) {
    $sqlDevoirs .= " AND urgence = :urgent";
    $params[':urgent'] = $urgent;
}

// Tri
if ($sort == 'date_asc') {
    $sqlDevoirs .= " ORDER BY date_soumission ASC";
} elseif ($sort == 'date_desc') {
    $sqlDevoirs .= " ORDER BY date_soumission DESC";
} elseif ($sort == 'level_asc') {
    $sqlDevoirs .= " ORDER BY CASE niveau_difficulte 
                    WHEN 'facile' THEN 1 
                    WHEN 'moyen' THEN 2 
                    WHEN 'difficile' THEN 3 
                    ELSE 4 END ASC";
} elseif ($sort == 'level_desc') {
    $sqlDevoirs .= " ORDER BY CASE niveau_difficulte 
                    WHEN 'difficile' THEN 1 
                    WHEN 'moyen' THEN 2 
                    WHEN 'facile' THEN 3 
                    ELSE 4 END ASC";
} elseif ($sort == 'urgence') {
    $sqlDevoirs .= " ORDER BY CASE urgence 
                    WHEN 'urgente' THEN 1 
                    WHEN 'moyenne' THEN 2 
                    WHEN 'faible' THEN 3 
                    ELSE 4 END ASC";
} else {
    $sqlDevoirs .= " ORDER BY date_soumission DESC";
}

// Exécution de la requête
$stmt = $conn->prepare($sqlDevoirs);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$devoirs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupère TOUTES les corrections
$corrections = $conn->query("
    SELECT c.id_correction, c.commentaire, c.fichier_corrige, c.date_correction,
           c.type_feedback, c.note_estimee, c.competences_evaluees,
           c.nombre_iterations, c.suggestions_personnalisees,
           c.ressources_recommandees, c.rapidite_correction,
           c.ton_feedback, c.id_devoir,
           d.titre AS devoir_titre
    FROM correction c
    LEFT JOIN devoirs d ON c.id_devoir = d.id_devoir
    ORDER BY c.id_correction DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Organiser les corrections par id_devoir
$correctionsByDevoir = [];
foreach ($corrections as $corr) {
    $devoirId = $corr['id_devoir'];
    if (!isset($correctionsByDevoir[$devoirId])) {
        $correctionsByDevoir[$devoirId] = [];
    }
    $correctionsByDevoir[$devoirId][] = $corr;
}

// ============================================================
// STATISTIQUES AVANCÉES (CORRIGÉES)
// ============================================================

// 1. Nombre de devoirs urgents non corrigés
$stmtUrgents = $conn->prepare("
    SELECT COUNT(*) as count 
    FROM devoirs d 
    WHERE d.urgence = 'urgente' 
    AND NOT EXISTS (SELECT 1 FROM correction c WHERE c.id_devoir = d.id_devoir)
");
$stmtUrgents->execute();
$urgentsNonCorriges = $stmtUrgents->fetch(PDO::FETCH_ASSOC)['count'];

// 2. Devoirs sans correction depuis plus de 7 jours
$stmtSansCorrection = $conn->prepare("
    SELECT COUNT(*) as count 
    FROM devoirs d 
    WHERE NOT EXISTS (SELECT 1 FROM correction c WHERE c.id_devoir = d.id_devoir)
    AND d.date_soumission < DATE_SUB(NOW(), INTERVAL 7 DAY)
");
$stmtSansCorrection->execute();
$devoirsSansCorrectionLong = $stmtSansCorrection->fetch(PDO::FETCH_ASSOC)['count'];

// 3. Moyenne des notes
$stmtMoyenneNotes = $conn->query("
    SELECT AVG(note_estimee) as moyenne 
    FROM correction 
    WHERE note_estimee IS NOT NULL
");
$moyenneNotes = round($stmtMoyenneNotes->fetch(PDO::FETCH_ASSOC)['moyenne'] ?? 0, 1);

// 4. Devoirs en difficulté (progression < 30% OU note < 8)
$stmtDevoirsDifficiles = $conn->query("
    SELECT COUNT(*) as count 
    FROM devoirs d 
    WHERE d.progression_eleve < 30 
    OR d.id_devoir IN (
        SELECT c.id_devoir 
        FROM correction c 
        WHERE c.note_estimee < 8
    )
");
$devoirsDifficiles = $stmtDevoirsDifficiles->fetch(PDO::FETCH_ASSOC)['count'];

// 5. Taux de correction
$nbDevoirsTotal = count($devoirs);
$nbCorrectionsTotal = count($corrections);
$tauxCorrection = $nbDevoirsTotal > 0 ? round(($nbCorrectionsTotal / $nbDevoirsTotal) * 100) : 0;

// 6. Temps moyen de correction (en heures)
$stmtTempsMoyen = $conn->query("
    SELECT AVG(TIMESTAMPDIFF(HOUR, d.date_soumission, c.date_correction)) as temps_moyen
    FROM correction c
    JOIN devoirs d ON c.id_devoir = d.id_devoir
    WHERE c.date_correction IS NOT NULL AND d.date_soumission IS NOT NULL
");
$tempsMoyenCorrection = round($stmtTempsMoyen->fetch(PDO::FETCH_ASSOC)['temps_moyen'] ?? 0);

// 7. Devoirs avec stress (vérifie la casse)
$devoirsStress = count(array_filter($devoirs, function($d) {
    $sentiment = strtolower($d['sentiment'] ?? '');
    return $sentiment === 'stress' || $sentiment === 'stresse';
}));

// 8. Devoirs avec sentiment positif
$devoirsPositifs = count(array_filter($devoirs, function($d) {
    return strtolower($d['sentiment'] ?? '') === 'positif';
}));

// 9. Total devoirs urgents
$stmtUrgentsTotal = $conn->query("SELECT COUNT(*) as count FROM devoirs WHERE urgence = 'urgente'");
$urgentsTotal = $stmtUrgentsTotal->fetch(PDO::FETCH_ASSOC)['count'];

// Message de succès si redirigé depuis submit
$successType = $_GET['success'] ?? '';




?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Feed - EduFeed</title>
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
    <!-- Garder SEULEMENT ces deux, dans cet ordre -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<!-- Ajouter Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <style>

        /* ============================================================ */
/* BINGO DES COMPÉTENCES                                        */
/* ============================================================ */

.bingo-section {
    background: white;
    border-radius: 25px;
    padding: 1.5rem;
    margin: 2rem 0;
    box-shadow: 0 4px 20px rgba(0,0,0,0.07);
    border: 1px solid var(--border);
}

.bingo-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--border);
}

.bingo-title {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.bingo-title h2 {
    margin: 0;
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--text);
}

.bingo-title i {
    font-size: 1.8rem;
    color: #f59e0b;
}

.bingo-stats {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.bingo-stats span {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--muted);
}

.bingo-progress-bar {
    width: 150px;
    height: 8px;
    background: var(--border);
    border-radius: 10px;
    overflow: hidden;
}

.bingo-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #10b981, #34d399);
    border-radius: 10px;
    transition: width 0.3s ease;
}

.btn-reset-bingo {
    background: #ef4444;
    color: white;
    border: none;
    border-radius: 10px;
    padding: 0.5rem 1rem;
    font-size: 0.8rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-reset-bingo:hover {
    background: #dc2626;
    transform: scale(1.02);
}

/* Grille Bingo */
.bingo-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 12px;
    margin: 1.5rem 0;
}

.bingo-cell {
    aspect-ratio: 1 / 1;
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    border-radius: 16px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 0.5rem;
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
}

.bingo-cell:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}

.bingo-cell.completed {
    background: linear-gradient(135deg, #d1fae5, #a7f3d0);
    border-color: #10b981;
}

.bingo-cell.completed::after {
    content: "✓";
    position: absolute;
    top: 8px;
    right: 12px;
    color: #10b981;
    font-size: 18px;
    font-weight: bold;
}

.bingo-cell-icon {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

.bingo-cell-name {
    font-size: 0.7rem;
    font-weight: 600;
    color: var(--text);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.bingo-cell.completed .bingo-cell-name {
    color: #065f46;
}

/* Message de félicitations */
.bingo-message {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 10000;
    animation: fadeIn 0.3s ease;
}

.bingo-message-content {
    background: linear-gradient(135deg, #10b981, #34d399);
    color: white;
    padding: 1.5rem 2rem;
    border-radius: 20px;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 20px 40px rgba(16, 185, 129, 0.4);
}

.bingo-message-content i {
    font-size: 2.5rem;
}

.bingo-message-content button {
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    font-size: 1.2rem;
    cursor: pointer;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    transition: all 0.2s;
}

.bingo-message-content button:hover {
    background: rgba(255,255,255,0.4);
}

@keyframes fadeIn {
    from { opacity: 0; transform: translate(-50%, -50%) scale(0.9); }
    to { opacity: 1; transform: translate(-50%, -50%) scale(1); }
}

/* Responsive Bingo */
@media (max-width: 768px) {
    .bingo-grid {
        gap: 8px;
    }
    
    .bingo-cell-icon {
        font-size: 1.2rem;
    }
    
    .bingo-cell-name {
        font-size: 0.55rem;
    }
    
    .bingo-header {
        flex-direction: column;
        align-items: flex-start;
    }
}

@media (max-width: 480px) {
    .bingo-grid {
        gap: 5px;
    }
    
    .bingo-cell {
        padding: 0.25rem;
    }
}

        /* ============================================================ */
/* STYLES POUR LA LECTURE AUDIO (TEXT-TO-SPEECH) */
/* ============================================================ */

.btn-audio {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: white;
    border: none;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-left: 0.5rem;
}

.btn-audio:hover {
    transform: scale(1.1);
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.4);
}

.btn-audio.playing {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    animation: pulse-audio 1s infinite;
}

@keyframes pulse-audio {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.1); opacity: 0.8; }
    100% { transform: scale(1); opacity: 1; }
}

.btn-audio-small {
    width: 28px;
    height: 28px;
    font-size: 12px;
}

.audio-controls {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.speech-tooltip {
    position: absolute;
    background: #1e293b;
    color: white;
    font-size: 11px;
    padding: 4px 8px;
    border-radius: 6px;
    white-space: nowrap;
    z-index: 100;
    transform: translateY(-30px);
    pointer-events: none;
}

        /* ============================================================ */
/* CHATBOT STYLES */
/* ============================================================ */

.chatbot-toggle {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 65px;
    height: 65px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    border: none;
    color: white;
    font-size: 28px;
    cursor: pointer;
    box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
    z-index: 1000;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.chatbot-toggle:hover {
    transform: scale(1.1);
    box-shadow: 0 12px 35px rgba(99, 102, 241, 0.5);
}

.chatbot-badge {
    position: absolute;
    bottom: -5px;
    right: -5px;
    background: #10b981;
    color: white;
    font-size: 10px;
    font-weight: bold;
    padding: 4px 6px;
    border-radius: 12px;
    white-space: nowrap;
}

.chatbot-window {
    position: fixed;
    bottom: 110px;
    right: 30px;
    width: 400px;
    height: 550px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    z-index: 1000;
    animation: slideUpChat 0.3s ease;
    border: 1px solid rgba(99, 102, 241, 0.2);
}

@keyframes slideUpChat {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.chatbot-header {
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    padding: 15px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    color: white;
}

.chatbot-header-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.chatbot-avatar {
    width: 45px;
    height: 45px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.chatbot-info h4 {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
}

.chatbot-info p {
    margin: 0;
    font-size: 11px;
    opacity: 0.8;
}

.chatbot-close {
    background: none;
    border: none;
    color: white;
    font-size: 20px;
    cursor: pointer;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
}

.chatbot-close:hover {
    background: rgba(255, 255, 255, 0.2);
}

.chatbot-messages {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    background: #f8fafc;
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.message {
    display: flex;
    gap: 10px;
    animation: fadeInMessage 0.3s ease;
}

@keyframes fadeInMessage {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.message.user {
    flex-direction: row-reverse;
}

.message-avatar {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.message.bot .message-avatar {
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: white;
}

.message.user .message-avatar {
    background: #10b981;
    color: white;
}

.message-content {
    max-width: 70%;
}

.message-text {
    background: white;
    padding: 10px 14px;
    border-radius: 18px;
    font-size: 13px;
    line-height: 1.5;
    color: #1e293b;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
}

.message.user .message-text {
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: white;
    border: none;
}

.message-time {
    font-size: 10px;
    color: #94a3b8;
    margin-top: 5px;
    text-align: right;
}

.message.user .message-time {
    text-align: right;
}

.message.bot .message-time {
    text-align: left;
    margin-left: 45px;
}

.chatbot-input-area {
    padding: 15px;
    border-top: 1px solid #e2e8f0;
    background: white;
}

.quick-questions {
    display: flex;
    gap: 8px;
    margin-bottom: 12px;
    flex-wrap: wrap;
}

.quick-question {
    background: #f1f5f9;
    border: none;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 11px;
    cursor: pointer;
    transition: all 0.2s;
    color: #475569;
}

.quick-question:hover {
    background: #e2e8f0;
    transform: translateY(-1px);
}

.input-wrapper {
    display: flex;
    gap: 10px;
}

#chatbotInput {
    flex: 1;
    padding: 12px 15px;
    border: 1.5px solid #e2e8f0;
    border-radius: 25px;
    outline: none;
    font-size: 14px;
    transition: all 0.2s;
}

#chatbotInput:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1);
}

#chatbotSendBtn {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    border: none;
    color: white;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

#chatbotSendBtn:hover {
    transform: scale(1.05);
}

.typing-indicator {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 10px 0;
    margin-top: 8px;
}

.typing-indicator span {
    width: 8px;
    height: 8px;
    background: #94a3b8;
    border-radius: 50%;
    animation: bounce 1.4s infinite ease-in-out;
}

.typing-indicator span:nth-child(1) { animation-delay: 0s; }
.typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
.typing-indicator span:nth-child(3) { animation-delay: 0.4s; }

.typing-indicator .typing-text {
    font-size: 11px;
    color: #94a3b8;
    animation: none;
    width: auto;
    height: auto;
    background: none;
    margin-left: 5px;
}

@keyframes bounce {
    0%, 60%, 100% { transform: translateY(0); }
    30% { transform: translateY(-8px); }
}

/* Responsive chatbot */
@media (max-width: 500px) {
    .chatbot-window {
        width: calc(100vw - 40px);
        right: 20px;
        bottom: 100px;
    }
    
    .quick-questions {
        overflow-x: auto;
        flex-wrap: nowrap;
        padding-bottom: 5px;
    }
}

        /* Badges de sentiment */
.sentiment-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 600;
}

.sentiment-positif { background: #D1FAE5; color: #065F46; }
.sentiment-negatif { background: #FEE2E2; color: #991B1B; }
.sentiment-neutre { background: #F1F5F9; color: #475569; }
.sentiment-frustration { background: #FEF3C7; color: #92400E; }
.sentiment-confusion { background: #E0E7FF; color: #3730A3; }
.sentiment-stress { 
    background: #FEE2E2; 
    color: #DC2626; 
    animation: pulse 1.5s infinite;
}

@keyframes pulse {
    0% { opacity: 0.7; }
    50% { opacity: 1; background: #FECACA; }
    100% { opacity: 0.7; }
}

.sentiment-urgent {
    border: 2px solid #DC2626;
}

        .btn-export-pdf {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    color: white;
    border: none;
    border-radius: 0.5rem;
    padding: 0.3rem 0.8rem;
    font-size: 0.75rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
}

.btn-export-pdf:hover {
    background: linear-gradient(135deg, #b91c1c, #991b1b);
    transform: scale(1.05);
}

        /* Styles pour les statistiques */
.stats-dashboard {
    margin-bottom: 1rem;
}

.stat-card {
    background: white;
    border-radius: 20px;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.07);
    transition: all 0.3s ease;
    border: 1px solid var(--border);
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 35px rgba(0,0,0,0.13);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    color: white;
}

.stat-info h3 {
    font-size: 1.8rem;
    font-weight: 800;
    margin: 0;
    color: var(--text);
}

.stat-info p {
    margin: 0;
    color: var(--muted);
    font-size: 0.85rem;
    font-weight: 500;
}

.chart-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.07);
    border: 1px solid var(--border);
    overflow: hidden;
    transition: all 0.3s ease;
}

.chart-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 35px rgba(0,0,0,0.13);
}

.chart-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--border);
    background: var(--bg);
}

.chart-header h5 {
    margin: 0;
    font-weight: 700;
    color: var(--text);
}

.chart-body {
    padding: 1.5rem;
}

.form-select-sm {
    border-radius: 12px;
    border-color: var(--border);
    font-size: 0.85rem;
    cursor: pointer;
}

.form-select-sm:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 0.2rem rgba(108, 99, 255, 0.25);
}

.badge {
    padding: 0.5rem 0.75rem;
    border-radius: 12px;
    font-weight: 600;
}

/* Responsive stats */
@media (max-width: 768px) {
    .stat-card {
        padding: 1rem;
    }
    
    .stat-icon {
        width: 50px;
        height: 50px;
        font-size: 1.4rem;
    }
    
    .stat-info h3 {
        font-size: 1.4rem;
    }
    
    .chart-header, .chart-body {
        padding: 1rem;
    }
}

              /* Bouton PDF */
      .btn-pdf {
        background: linear-gradient(135deg, #dc2626, #b91c1c);
        color: white;
        border: none;
        border-radius: 0.75rem;
        padding: 0.7rem 1.5rem;
        font-weight: 600;
        font-size: 0.95rem;
        text-decoration: none;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
      }
      .btn-pdf:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(220,38,38,0.3);
        color: white;
        background: linear-gradient(135deg, #b91c1c, #991b1b);
      }
      
      /* Loader PDF */
      .pdf-loader {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        color: white;
        font-weight: bold;
        font-size: 1.2rem;
        flex-direction: column;
        gap: 1rem;
      }
      .pdf-loader .spinner {
        width: 50px;
        height: 50px;
        border: 5px solid rgba(255,255,255,0.3);
        border-top: 5px solid white;
        border-radius: 50%;
        animation: spin 1s linear infinite;
      }
      @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
      }
      
      /* Masquer les boutons lors de l'impression/PDF */
      @media print {
        .btn-pdf, .btn-refresh, .btn-delete, .btn-edit, .btn-add-correction,
        .btn-submit-link, .header-btn, .btn_one, .mobile_menu, .site-navigation,
        .modern-footer, .search-filter-form, .toast-success {
          display: none !important;
        }
        .feed-card {
          break-inside: avoid;
          page-break-inside: avoid;
          box-shadow: none;
          border: 1px solid #ddd;
        }
        body {
          background: white;
        }
        .container {
          max-width: 100%;
        }
      }

        /* Bouton Refresh */
.btn-refresh {
    background: linear-gradient(135deg, #06b6d4, #3b82f6);
    color: white;
    border: none;
    border-radius: 0.75rem;
    padding: 0.7rem 1.5rem;
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

.btn-refresh:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(6, 182, 212, 0.3);
    background: linear-gradient(135deg, #0891b2, #2563eb);
}
        .search-filter-form {
    background: rgba(255,255,255,0.1);
    border-radius: 1rem;
    padding: 1.25rem;
    margin-top: 1rem;
}

.search-input-wrapper {
    position: relative;
}

.search-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    z-index: 1;
}

.search-input {
    padding-left: 2.5rem !important;
    background: rgba(255,255,255,0.95) !important;
}

.filter-select {
    background: rgba(255,255,255,0.95) !important;
    cursor: pointer;
}
        .btn-edit {
            background: #F59E0B;
            color: white;
            border: none;
            border-radius: 0.5rem;
            padding: 0.3rem 0.8rem;
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            text-decoration: none;
        }

        .btn-edit:hover {
            background: #D97706;
            transform: scale(1.05);
            color: white;
            text-decoration: none;
        }

        .btn-delete {
            background: #EF4444;
            color: white;
            border: none;
            border-radius: 0.5rem;
            padding: 0.3rem 0.8rem;
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }

        .btn-delete:hover {
            background: #DC2626;
            transform: scale(1.05);
        }

        .btn-add-correction {
            background: #10B981;
            color: white;
            border: none;
            border-radius: 0.5rem;
            padding: 0.3rem 0.8rem;
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            text-decoration: none;
        }

        .btn-add-correction:hover {
            background: #059669;
            transform: scale(1.05);
            color: white;
            text-decoration: none;
        }

        /* Popup confirmation */
        .confirm-popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 1.5rem;
            border-radius: 1rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            z-index: 10000;
            text-align: center;
            min-width: 300px;
        }

        .confirm-popup button {
            margin: 0.5rem;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
        }

        .confirm-popup .btn-confirm {
            background: #EF4444;
            color: white;
        }

        .confirm-popup .btn-cancel {
            background: #94A3B8;
            color: white;
        }
        
        :root {
            --primary: #6C63FF;
            --secondary: #00D4FF;
            --success: #10B981;
            --danger: #EF4444;
            --warning: #F59E0B;
            --info: #3B82F6;
            --bg: #F0F4FF;
            --card-bg: #ffffff;
            --border: #E2E8F0;
            --text: #1E293B;
            --muted: #64748B;
        }

        body { background: var(--bg); font-family: 'DM Sans', sans-serif; }

        /* ============ FEED HEADER ============ */
        .feed-hero {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: white;
            padding: 3rem 0 2rem;
            margin-bottom: 2.5rem;
        }
        .feed-hero h1 { font-size: 2.2rem; font-weight: 800; margin-bottom: 0.3rem; }
        .feed-hero p  { color: #cbd5e1; font-size: 1rem; margin: 0; }
        .feed-hero .stats { display: flex; gap: 2rem; margin-top: 1.5rem; flex-wrap: wrap; }
        .feed-hero .stat-pill {
            background: rgba(255,255,255,0.1);
            border-radius: 999px;
            padding: 0.4rem 1rem;
            font-size: 0.88rem;
            display: flex; align-items: center; gap: 0.4rem;
        }
        .feed-hero .stat-pill i { color: var(--secondary); }

        /* ============ SUCCESS TOAST ============ */
        .toast-success {
            position: fixed;
            top: 1.5rem; right: 1.5rem;
            background: var(--success);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 0.75rem;
            display: flex; align-items: center; gap: 0.75rem;
            font-weight: 600;
            box-shadow: 0 10px 30px rgba(16,185,129,0.35);
            z-index: 9999;
            animation: fadeInRight 0.4s ease, fadeOut 0.4s ease 3.5s forwards;
        }
        @keyframes fadeInRight { from { opacity:0; transform:translateX(40px); } to { opacity:1; transform:translateX(0); } }
        @keyframes fadeOut     { from { opacity:1; } to { opacity:0; pointer-events:none; } }

        /* ============ CARD ============ */
        .feed-card {
            background: var(--card-bg);
            border-radius: 1.25rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.07);
            margin-bottom: 2rem;
            overflow: hidden;
            border: 1px solid var(--border);
            transition: transform 0.25s, box-shadow 0.25s;
            animation: slideUp 0.4s ease forwards;
        }
        .feed-card:hover { transform: translateY(-4px); box-shadow: 0 12px 35px rgba(0,0,0,0.13); }

        @keyframes slideUp {
            from { opacity:0; transform:translateY(20px); }
            to   { opacity:1; transform:translateY(0); }
        }

        /* Card header */
        .card-header-bar {
            padding: 1rem 1.5rem;
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: 0.5rem;
            border-bottom: 1px solid var(--border);
        }
        .card-header-bar.devoir-header {
            background: linear-gradient(135deg, rgba(108,99,255,0.08), rgba(0,212,255,0.05));
        }

        .card-type-badge {
            display: inline-flex; align-items: center; gap: 0.4rem;
            padding: 0.3rem 0.9rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-devoir     { background: rgba(108,99,255,0.12); color: var(--primary); }
        .badge-correction { background: rgba(16,185,129,0.12); color: var(--success); }

        .card-id { font-size: 0.82rem; color: var(--muted); font-weight: 500; }
        .card-date { font-size: 0.82rem; color: var(--muted); }
        .card-date i { margin-right: 0.3rem; }

        /* Card body */
        .card-body-content { padding: 1.25rem 1.5rem; }

        .card-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 0.5rem;
        }
        .card-description {
            color: var(--muted);
            font-size: 0.93rem;
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        /* Details grid */
        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 0.6rem;
            margin-bottom: 1rem;
        }
        .detail-chip {
            display: flex; align-items: center; gap: 0.5rem;
            background: var(--bg);
            border-radius: 0.6rem;
            padding: 0.45rem 0.75rem;
            font-size: 0.85rem;
            color: var(--text);
            border: 1px solid var(--border);
        }
        .detail-chip i {
            font-size: 0.8rem;
            color: var(--primary);
            width: 14px;
            flex-shrink: 0;
        }
        .detail-chip strong { color: var(--text); margin-right: 0.2rem; }

        /* Correction-specific chips */
        .detail-chip .icon-green { color: var(--success); }
        .detail-chip .icon-orange { color: var(--warning); }

        /* Tags mots clés */
        .tags-row { display: flex; flex-wrap: wrap; gap: 0.4rem; margin-top: 0.5rem; }
        .tag {
            background: rgba(108,99,255,0.1);
            color: var(--primary);
            border-radius: 999px;
            padding: 0.2rem 0.7rem;
            font-size: 0.78rem;
            font-weight: 600;
        }
        .tag.green { background: rgba(16,185,129,0.1); color: var(--success); }

        /* Note badge */
        .note-badge {
            display: inline-flex; align-items: center; gap: 0.3rem;
            background: linear-gradient(135deg, #F59E0B, #EF4444);
            color: white;
            border-radius: 999px;
            padding: 0.35rem 0.9rem;
            font-weight: 800;
            font-size: 1rem;
        }

        /* Urgence pill */
        .urgence-pill {
            display: inline-flex; align-items: center; gap: 0.3rem;
            border-radius: 999px;
            padding: 0.25rem 0.75rem;
            font-size: 0.8rem;
            font-weight: 700;
        }
        .urgence-faible  { background: rgba(16,185,129,0.12); color: var(--success); }
        .urgence-moyenne { background: rgba(245,158,11,0.12); color: var(--warning); }
        .urgence-urgente { background: rgba(239,68,68,0.12); color: var(--danger); }

        /* Progression bar */
        .progression-mini { margin-top: 0.3rem; }
        .progression-mini .bar-track {
            height: 6px; background: var(--border); border-radius: 999px; overflow: hidden;
        }
        .progression-mini .bar-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            border-radius: 999px;
        }
        .progression-label { font-size: 0.78rem; color: var(--muted); margin-bottom: 0.2rem; }

        /* Fichier lien */
        .file-link {
            display: inline-flex; align-items: center; gap: 0.4rem;
            color: var(--primary);
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            border: 1.5px solid rgba(108,99,255,0.3);
            border-radius: 0.5rem;
            padding: 0.3rem 0.75rem;
            transition: all 0.2s;
        }
        .file-link:hover { background: rgba(108,99,255,0.08); text-decoration: none; }

        /* Suggestions / ressources bloc */
        .extra-block {
            background: var(--bg);
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            margin-top: 0.75rem;
            font-size: 0.88rem;
            color: var(--muted);
            border-left: 3px solid var(--primary);
        }
        .extra-block strong { color: var(--text); display: block; margin-bottom: 0.2rem; }

        /* Linked devoir tag */
        .linked-devoir {
            display: inline-flex; align-items: center; gap: 0.4rem;
            background: rgba(108,99,255,0.07);
            color: var(--primary);
            border-radius: 0.5rem;
            padding: 0.3rem 0.75rem;
            font-size: 0.82rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--muted);
        }
        .empty-state i { font-size: 3.5rem; margin-bottom: 1rem; opacity: 0.3; }
        .empty-state h4 { font-size: 1.2rem; font-weight: 600; }

        /* Submit button */
        .btn-submit-link {
            display: inline-flex; align-items: center; gap: 0.5rem;
            background: linear-gradient(135deg, var(--primary), #8B5CF6);
            color: white;
            border-radius: 0.75rem;
            padding: 0.75rem 1.5rem;
            font-weight: 700;
            font-size: 0.95rem;
            text-decoration: none;
            transition: all 0.3s;
        }
        .btn-submit-link:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(108,99,255,0.3); color:white; text-decoration:none; }

        /* Correction card inside devoir */
        .correction-subcard {
            background: linear-gradient(135deg, rgba(16,185,129,0.03), rgba(5,150,105,0.02));
            border-top: 2px solid var(--border);
            margin-top: 0;
            padding: 1.25rem 1.5rem;
        }
        .correction-subcard:first-child {
            margin-top: 0;
        }
        .correction-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px dashed var(--border);
        }
        .correction-title {
            font-weight: 700;
            color: var(--success);
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        /* Separator entre devoir et corrections */
        .corrections-separator {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 0 1.5rem 1rem 1.5rem;
            padding-top: 0.5rem;
        }
        .corrections-separator::before,
        .corrections-separator::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--border), transparent);
        }
        .corrections-separator span {
            font-size: 0.8rem;
            color: var(--muted);
            font-weight: 600;
            background: white;
            padding: 0 0.75rem;
        }

        /* Responsive */
        @media (max-width: 576px) {
            .details-grid { grid-template-columns: 1fr 1fr; }
            .card-body-content { padding: 1rem; }
        }
    </style>
</head>

<body data-spy="scroll" data-offset="80">

    <!-- PRELOADER -->
    <div class="preloaders"><span class="loader"></span></div>

    <?php if ($successType === 'devoir'): ?>
    <div class="toast-success">
        <i class="fas fa-check-circle fa-lg"></i>
        Devoir publié avec succès dans le feed !
    </div>
    <?php elseif ($successType === 'correction'): ?>
    <div class="toast-success">
        <i class="fas fa-check-circle fa-lg"></i>
        Correction soumise avec succès !
    </div>
    <?php endif; ?>

    <!-- NAVBAR -->
    <div id="navigation" class="navbar-light bg-faded site-navigation">
        <div class="container-fluid">
            <div class="row">
                <div class="col-20 align-self-center">
                    <div class="site-logo">
                        <a href="index.html"><img src="../../assets/img/logo.png" alt=""></a>
                    </div>
                </div>
                <div class="col-60 d-flex">
                    <nav id="main-menu">
                        <ul>
                            <li><a href="index.html">Home</a></li>
                            <li><a href="about.html">About</a></li>
                            <li class="menu-item-has-children">
                                <a href="#">Edufeed</a>
                                <ul>
                                    <li><a href="/eduleb/submit.html">Submit Assignment</a></li>
                                    <li><a href="/eduleb/feed.html">Learning Feed</a></li>
                                </ul>
                            </li>
                            <li><a href="partenariat.html">Partenariat</a></li>
                            <li><a href="evenement.html">Événement</a></li>
                            <li><a href="quiz.html">Quiz</a></li>
                            <li><a href="offre-emploi.html">Offre d'emploi</a></li>
                            <li><a href="contact.html">Contact</a></li>
                        </ul>
                    </nav>
                </div>
                <div class="col-20 d-none d-xl-block text-end align-self-center">
                    <a href="#" class="header-btn">Sign In</a>
                    <a href="contact.html" class="btn_one">Sign Up</a>
                </div>
                <ul class="mobile_menu">
                    <li><a href="index.html">Home</a></li>
                    <li><a href="about.html">About</a></li>
                    <li><a href="#">Edufeed</a>
                        <ul class="sub-menu">
                            <li><a href="/eduleb/submit.html">Submit Assignment</a></li>
                            <li><a href="/eduleb/feed.html">Learning Feed</a></li>
                        </ul>
                    </li>
                    <li><a href="partenariat.html">Partenariat</a></li>
                    <li><a href="evenement.html">Événement</a></li>
                    <li><a href="quiz.html">Quiz</a></li>
                    <li><a href="offre-emploi.html">Offre d'emploi</a></li>
                    <li><a href="contact.html">Contact</a></li>
                </ul>
            </div>
        </div>
    </div>
    <!-- END NAVBAR -->

   <!-- HERO HEADER -->
<div class="feed-hero">
    <div class="container">
        <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
            <div>
                <h1><i class="fas fa-graduation-cap me-2"></i> EduFeed</h1>
                <p>Découvrez les devoirs soumis et leurs corrections</p>
                <div class="stats">
                    <div class="stat-pill">
                        <i class="fas fa-file-alt"></i>
                        <span><?= count($devoirs) ?> devoir<?= count($devoirs) > 1 ? 's' : '' ?></span>
                    </div>
                    <div class="stat-pill">
                        <i class="fas fa-check-double"></i>
                        <span><?= count($corrections) ?> correction<?= count($corrections) > 1 ? 's' : '' ?></span>
                    </div>
                </div>
            </div>
                        <div class="d-flex gap-2 align-self-center">
                
                <a href="/eduleb/submit.html" class="btn-submit-link">
                    <i class="fas fa-plus"></i> Nouveau devoir
                </a>
            </div>
        </div>

        <!-- Barre de recherche et filtres -->
        <form method="GET" action="" class="search-filter-form">
            <div class="row g-3">
                <div class="col-md-5">
                    <div class="search-input-wrapper">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="search" class="form-control search-input" 
                               placeholder="Rechercher par titre, description ou mots-clés..." 
                               value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    </div>
                </div>
                
                <div class="col-md-2">
                    <select name="sort" class="form-control filter-select" onchange="this.form.submit()">
                        <option value="date_desc" <?= ($_GET['sort'] ?? 'date_desc') == 'date_desc' ? 'selected' : '' ?>>📅 Récent d'abord</option>
                        <option value="date_asc" <?= ($_GET['sort'] ?? '') == 'date_asc' ? 'selected' : '' ?>>📅 Ancien d'abord</option>
                        <option value="level_asc" <?= ($_GET['sort'] ?? '') == 'level_asc' ? 'selected' : '' ?>>📈 Niveau croissant</option>
                        <option value="level_desc" <?= ($_GET['sort'] ?? '') == 'level_desc' ? 'selected' : '' ?>>📉 Niveau décroissant</option>
                        <option value="urgence" <?= ($_GET['sort'] ?? '') == 'urgence' ? 'selected' : '' ?>>⚠️ Par urgence</option>
                    </select>
                </div>
                <!-- Bouton Refresh -->
        <div class="col-md-3">
            <button type="button" onclick="refreshPage()" class="btn-refresh">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
            </div>
            
        </form>
    </div>
</div>

<!-- DASHBOARD STATISTIQUES -->
<div class="container mt-4">
    <div class="stats-dashboard">
        <div class="row g-4 mb-5">
            <!-- Carte 1: Total Devoirs -->
            <div class="col-md-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #6366f1, #06b6d4);">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="statTotalDevoirs"><?= count($devoirs) ?></h3>
                        <p>Total Devoirs</p>
                    </div>
                </div>
            </div>
            
            <!-- Carte 2: Total Corrections -->
            <div class="col-md-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #10b981, #34d399);">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="statTotalCorrections"><?= count($corrections) ?></h3>
                        <p>Total Corrections</p>
                    </div>
                </div>
            </div>
            
            <!-- Carte 3: Devoirs par mois (Camembert) -->
            <div class="col-md-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b, #ef4444);">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="statMoisActif">-</h3>
                        <p>Mois le plus actif</p>
                    </div>
                </div>
            </div>
            
            <!-- Carte 4: Moyenne devoirs/mois -->
            <div class="col-md-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #8b5cf6, #ec489a);">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="statMoyenneMois">0</h3>
                        <p>Moyenne devoirs/mois</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Graphiques -->
        <div class="row g-4 mb-5">
            <!-- Camembert: Répartition par mois -->
            <div class="col-lg-6">
                <div class="chart-card">
                    <div class="chart-header">
                        <h5><i class="fas fa-calendar-alt me-2 text-primary"></i> Devoirs par mois</h5>
                        <p class="text-muted small mb-0">Répartition mensuelle des soumissions</p>
                    </div>
                    <div class="chart-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <select id="chartPeriodSelect" class="form-select form-select-sm w-auto">
                                <option value="month">Par mois</option>
                                <option value="week">Par semaine</option>
                            </select>
                            <span class="badge bg-primary" id="chartTotalLabel">Total: 0 devoirs</span>
                        </div>
                        <canvas id="devoirsPieChart" style="max-height: 280px; width: 100%;"></canvas>
                    </div>
                </div>
            </div>

            <!-- Courbe d'activité -->
            <div class="col-lg-6">
                <div class="chart-card">
                    <div class="chart-header">
                        <h5><i class="fas fa-chart-line me-2 text-success"></i> Activité des élèves</h5>
                        <p class="text-muted small mb-0">Évolution du nombre de devoirs soumis</p>
                    </div>
                    <div class="chart-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <select id="activityPeriodSelect" class="form-select form-select-sm w-auto">
                                <option value="6months">6 derniers mois</option>
                                <option value="12months">12 derniers mois</option>
                                <option value="all">Toute la période</option>
                            </select>
                            <span class="badge bg-success" id="activityTotalLabel">Total: 0 devoirs</span>
                        </div>
                        <canvas id="activityLineChart" style="max-height: 280px; width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- ROW 2 : STATISTIQUES DÉTAILLÉES -->
<div class="row g-4 mb-5">
    <!-- Carte: Devoirs urgents non corrigés -->
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                <i class="fas fa-bell"></i>
            </div>
            <div class="stat-info">
                <h3><?= $urgentsNonCorriges ?></h3>
                <p>Devoirs urgents non corrigés</p>
                <small class="text-muted">À traiter en priorité</small>
            </div>
        </div>
    </div>
    
    <!-- Carte: Devoirs sans correction (+7 jours) -->
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <div class="stat-info">
                <h3><?= $devoirsSansCorrectionLong ?></h3>
                <p>Devoirs sans correction > 7j</p>
                <small class="text-muted">En attente depuis longtemps</small>
            </div>
        </div>
    </div>
    
    <!-- Carte: Taux de correction -->
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                <i class="fas fa-percentage"></i>
            </div>
            <div class="stat-info">
                <h3><?= $tauxCorrection ?>%</h3>
                <p>Taux de correction</p>
                <small class="text-muted"><?= $nbCorrectionsTotal ?> / <?= $nbDevoirsTotal ?> devoirs corrigés</small>
            </div>
        </div>
    </div>
    
    <!-- Carte: Moyenne des notes -->
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                <i class="fas fa-star"></i>
            </div>
            <div class="stat-info">
                <h3><?= $moyenneNotes ?>/20</h3>
                <p>Moyenne des notes</p>
                <small class="text-muted">Sur l'ensemble des corrections</small>
            </div>
        </div>
    </div>
</div>

<!-- ROW 3 : STATISTIQUES DE PERFORMANCE -->
<div class="row g-4 mb-5">
    
    
    
    
    <!-- Carte: Devoirs avec stress détecté -->
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #ec489a, #db2777);">
                <i class="fas fa-heartbeat"></i>
            </div>
            <div class="stat-info">
                <h3><?= $devoirsStress ?></h3>
                <p>Devoirs avec stress détecté</p>
                <small class="text-muted">Intervention recommandée</small>
            </div>
        </div>
    </div>
    
    <!-- Carte: Devoirs avec sentiment positif -->
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #22c55e, #16a34a);">
                <i class="fas fa-smile"></i>
            </div>
            <div class="stat-info">
                <h3><?= $devoirsPositifs ?></h3>
                <p>Devoirs avec sentiment positif</p>
                <small class="text-muted">Élèves motivés</small>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- BINGO DES COMPÉTENCES                                        -->
<!-- ============================================================ -->
<div class="bingo-section">
    <div class="bingo-header">
        <div class="bingo-title">
            <i class="fas fa-gamepad"></i> 
            <h2>🏆 Mon Bingo des compétences</h2>
        </div>
        <div class="bingo-stats">
            <span id="bingoProgress">0%</span>
            <div class="bingo-progress-bar">
                <div class="bingo-progress-fill" style="width: 0%"></div>
            </div>
            <span id="bingoCount">0 / 25 compétences</span>
        </div>
        <button id="resetBingoBtn" class="btn-reset-bingo" title="Réinitialiser ma progression">
            <i class="fas fa-trash-alt"></i> Réinitialiser
        </button>
    </div>
    
    <div class="bingo-grid" id="bingoGrid">
        <!-- La grille sera générée par JavaScript -->
    </div>
    
    <div class="bingo-message" id="bingoMessage" style="display: none;">
        <div class="bingo-message-content">
            <i class="fas fa-trophy"></i>
            <span id="bingoMessageText">🎉 Félicitations ! Vous avez complété une ligne !</span>
            <button onclick="closeBingoMessage()">✕</button>
        </div>
    </div>
</div>
    

    <!-- MAIN CONTENT -->
    <section class="py-4">
        <div class="container" id="feedContainer">

            <?php if (empty($devoirs)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h4>Aucun devoir soumis pour l'instant</h4>
                    <p>Soyez le premier à soumettre un devoir !</p>
                    <a href="/eduleb/submit.html" class="btn-submit-link mt-3">
                        <i class="fas fa-plus"></i> Soumettre un devoir
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($devoirs as $i => $d): ?>
                <div class="feed-card" style="animation-delay: <?= $i * 0.07 ?>s" data-devoir-id="<?= $d['id_devoir'] ?>">

                    <!-- Header Devoir -->

                    
                    <div class="card-header-bar devoir-header">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="card-type-badge badge-devoir">
                                <i class="fas fa-book-open"></i> Devoir
                            </span>
                            <!-- ID caché mais accessible via data attribute si besoin -->
                        </div>
                        <!-- Dans la section card-header-bar, après l'urgence-pill -->
<span class="sentiment-badge sentiment-<?= htmlspecialchars($d['sentiment'] ?? 'neutre') ?> <?= ($d['alerte_urgence'] ?? 0) ? 'sentiment-urgent' : '' ?>">
    <?php
    $icons = [
        'positif' => '😊 Très motivé',
        'negatif' => '😟 En difficulté',
        'neutre' => '😐 Neutre',
        'frustration' => '😤 Frustré',
        'confusion' => '😕 Perdu',
        'stress' => '😰 Stressé'
    ];
    $sentiment = $d['sentiment'] ?? 'neutre';
    echo $icons[$sentiment];
    ?>
</span>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <a href="/eduleb/submit.html?add_correction_for=<?= $d['id_devoir'] ?>&title=<?= urlencode($d['titre']) ?>" 
                               class="btn-add-correction btn-sm">
                                <i class="fas fa-plus-circle"></i> Ajouter une correction
                            </a>
                            <button class="btn-delete btn-sm" data-id="<?= $d['id_devoir'] ?>" data-type="devoir">
                                <i class="fas fa-trash-alt"></i> Supprimer
                            </button>
                            <button class="btn-export-pdf btn-sm" data-devoir-id="<?= $d['id_devoir'] ?>">
    <i class="fas fa-file-pdf"></i> Exporter PDF
</button>
                            <a href="/eduleb/submit.html?edit=devoir&id=<?= $d['id_devoir'] ?>" class="btn-edit btn-sm">
                                <i class="fas fa-edit"></i> Modifier
                            </a>
                            <?php
                                $urg = $d['urgence'] ?? 'faible';
                                $urgClass = 'urgence-' . strtolower($urg);
                                $urgIcon  = ($urg === 'urgente') ? '🔴' : (($urg === 'moyenne') ? '🟡' : '🟢');
                            ?>
                            <span class="urgence-pill <?= $urgClass ?>">
                                <?= $urgIcon ?> <?= htmlspecialchars(ucfirst($urg)) ?>
                            </span>
                            <span class="card-date">
                                <i class="fas fa-calendar-alt"></i>
                                <?= htmlspecialchars($d['date_soumission']) ?>
                            </span>
                        </div>
                    </div>

                    <!-- Body Devoir -->
                    <div class="card-body-content">
                        <h4 class="card-title">
                            <i class="fas fa-heading" style="color:var(--primary);margin-right:0.4rem;"></i>
                            <?= htmlspecialchars($d['titre']) ?>
                             <button class="btn-audio speak-description" 
            data-text="<?= htmlspecialchars(strip_tags($d['description'])) ?>"
            title="Lire la description à voix haute">
        <i class="fas fa-volume-up"></i>
    </button>
                        </h4>
                        <p class="card-description">
                            <?= nl2br(htmlspecialchars($d['description'])) ?>
                        </p>

                        <div class="details-grid">
                            <div class="detail-chip">
                                <i class="fas fa-graduation-cap"></i>
                                <span><strong>Niveau :</strong> <?= htmlspecialchars(ucfirst($d['niveau_difficulte'])) ?></span>
                            </div>
                            <div class="detail-chip">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span><strong>Erreur :</strong> <?= htmlspecialchars(ucfirst($d['type_erreur_predominant'])) ?></span>
                            </div>
                            <div class="detail-chip">
                                <i class="fas fa-clock"></i>
                                <span><strong>Temps :</strong> <?= htmlspecialchars($d['temps_estime_resolution']) ?> min</span>
                            </div>
                        </div>

                        <div class="detail-chip mb-3" style="flex-direction:column;align-items:flex-start;gap:0.3rem;">
                            <div class="d-flex align-items-center gap-2 w-100">
                                <i class="fas fa-percentage" style="color:var(--primary);"></i>
                                <strong>Progression : <?= htmlspecialchars($d['progression_eleve']) ?>%</strong>
                            </div>
                            <div class="w-100" style="height:6px;background:var(--border);border-radius:999px;overflow:hidden;">
                                <div style="width:<?= (int)$d['progression_eleve'] ?>%;height:100%;background:linear-gradient(90deg,var(--primary),var(--secondary));border-radius:999px;"></div>
                            </div>
                        </div>

                        <?php if (!empty($d['mots_cles'])): ?>
                        <div class="mb-2">
                            <small style="color:var(--muted);font-weight:600;"><i class="fas fa-tags"></i> Mots clés :</small>
                            <div class="tags-row">
                                <?php foreach (explode(',', $d['mots_cles']) as $tag): ?>
                                    <span class="tag"><?= htmlspecialchars(trim($tag)) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($d['fichier'])): ?>
                        <div class="mt-2">
                            <a href="/eduleb/uploads/devoirs/<?= htmlspecialchars($d['fichier']) ?>"
                               class="file-link" target="_blank">
                                <i class="fas fa-file-code"></i>
                                <?= htmlspecialchars($d['fichier']) ?>
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- SECTION CORRECTIONS -->
                    <?php if (!empty($correctionsByDevoir[$d['id_devoir']])): ?>
                        <div class="corrections-separator">
                            <span><i class="fas fa-check-circle"></i> Corrections (<?= count($correctionsByDevoir[$d['id_devoir']]) ?>)</span>
                        </div>
                        
                        <?php foreach ($correctionsByDevoir[$d['id_devoir']] as $c): ?>
                            <div class="correction-subcard" data-fichier-corrige="<?= htmlspecialchars($c['fichier_corrige'] ?? '') ?>">
                                <div class="correction-header">
                                    <div class="correction-title">
                                        <i class="fas fa-chalkboard-teacher"></i>
                                        Correction 
                                        <button class="btn-audio btn-audio-small speak-text" 
                data-text="<?= htmlspecialchars(strip_tags($c['commentaire'])) ?>"
                title="Lire le commentaire">
            <i class="fas fa-volume-up"></i>
        </button>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <button class="btn-delete btn-sm" data-id="<?= $c['id_correction'] ?>" data-type="correction">
                                            <i class="fas fa-trash-alt"></i> Supprimer
                                        </button>
                                        <a href="/eduleb/submit.html?edit=correction&id=<?= $c['id_correction'] ?>" class="btn-edit btn-sm">
                                            <i class="fas fa-edit"></i> Modifier
                                        </a>
                                        <span class="note-badge">
                                            <i class="fas fa-star"></i>
                                            <?= htmlspecialchars($c['note_estimee']) ?>/20
                                        </span>
                                        <span class="card-date">
                                            <i class="fas fa-calendar-check"></i>
                                            <?= htmlspecialchars($c['date_correction']) ?>
                                        </span>
                                    </div>
                                </div>

                                <p class="card-description" style="margin-bottom: 0.75rem;">
                                    <strong>Commentaire :</strong> <?= nl2br(htmlspecialchars($c['commentaire'])) ?>
                                </p>

                                <div class="details-grid">
                                    <div class="detail-chip">
                                        <i class="fas fa-comment icon-green"></i>
                                        <span><strong>Type :</strong> <?= htmlspecialchars(ucfirst($c['type_feedback'])) ?></span>
                                    </div>
                                    <div class="detail-chip">
                                        <i class="fas fa-smile icon-green"></i>
                                        <span><strong>Ton :</strong> <?= htmlspecialchars(ucfirst($c['ton_feedback'])) ?></span>
                                    </div>
                                    <div class="detail-chip">
                                        <i class="fas fa-sync-alt icon-orange"></i>
                                        <span><strong>Itérations :</strong> <?= htmlspecialchars($c['nombre_iterations']) ?></span>
                                    </div>
                                    <div class="detail-chip">
                                        <i class="fas fa-hourglass-end icon-orange"></i>
                                        <span><strong>Rapidité :</strong> <?= htmlspecialchars($c['rapidite_correction']) ?> min</span>
                                    </div>
                                </div>

                                <?php if (!empty($c['competences_evaluees'])): ?>
                                <div class="mb-2">
                                    <small style="color:var(--muted);font-weight:600;"><i class="fas fa-brain"></i> Compétences :</small>
                                    <div class="tags-row">
                                        <?php foreach (explode(',', $c['competences_evaluees']) as $comp): ?>
                                            <span class="tag green"><?= htmlspecialchars(trim($comp)) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($c['suggestions_personnalisees'])): ?>
                                <div class="extra-block">
                                    <strong><i class="fas fa-lightbulb"></i> Suggestions personnalisées</strong>
                                    <?= nl2br(htmlspecialchars($c['suggestions_personnalisees'])) ?>
                                    <button class="btn-audio btn-audio-small speak-text" 
            data-text="<?= htmlspecialchars(strip_tags($c['suggestions_personnalisees'])) ?>"
            title="Lire les suggestions"
            style="margin-left: 0.75rem;">
        <i class="fas fa-volume-up"></i>
    </button>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($c['ressources_recommandees'])): ?>
                                <div class="extra-block" style="border-left-color:var(--success);">
                                    <strong><i class="fas fa-link"></i> Ressources recommandées</strong>
                                    <?php foreach (explode(',', $c['ressources_recommandees']) as $url): ?>
                                        <?php $url = trim($url); if (empty($url)) continue; ?>
                                        <a href="<?= htmlspecialchars($url) ?>" target="_blank" style="display:block;color:var(--primary);font-size:0.85rem;">
                                            <?= htmlspecialchars($url) ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($c['fichier_corrige'])): ?>
<div class="mt-2">
    <a href="/eduleb/uploads/correction/<?= htmlspecialchars($c['fichier_corrige']) ?>"
       class="file-link" 
       data-fichier-corrige="<?= htmlspecialchars($c['fichier_corrige']) ?>"
       target="_blank">
        <i class="fas fa-file-code"></i>
        <?= htmlspecialchars($c['fichier_corrige']) ?>
    </a>
</div>
<?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="corrections-separator">
                            <span><i class="fas fa-clock"></i> Aucune correction pour ce devoir</span>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>

            

        </div><!-- /container -->
    </section>

    <!-- START MODERN FOOTER -->
    <footer class="modern-footer bg-dark text-white py-5">
      <div class="container">
        <div class="row">
          <div class="col-lg-4 col-md-6 mb-4">
            <div class="footer-brand">
              <a href="index.html" class="text-decoration-none">
                <img src="../../assets/img/logo.png" alt="EduMatch Logo" class="mb-3" style="height: 50px;">
                <h3 class="text-white fw-bold">EduMatch</h3>
              </a>
              <p class="mt-3 text-light opacity-75">
                Smart matching platform connecting students with expert professors across all academic subjects for personalized learning experiences.
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
            <h5 class="fw-bold mb-3">Platform</h5>
            <ul class="list-unstyled">
              <li class="mb-2"><a href="submit.html" class="text-light text-decoration-none">Submit Requirements</a></li>
              <li class="mb-2"><a href="feed.html" class="text-light text-decoration-none">Professor Matches</a></li>
              <li class="mb-2"><a href="about.html" class="text-light text-decoration-none">How It Works</a></li>
              <li class="mb-2"><a href="contact.html" class="text-light text-decoration-none">Get Matched</a></li>
            </ul>
          </div>
          <div class="col-lg-2 col-md-6 mb-4">
            <h5 class="fw-bold mb-3">Academic Subjects</h5>
            <ul class="list-unstyled">
              <li class="mb-2"><a href="#" class="text-light text-decoration-none">Mathematics</a></li>
              <li class="mb-2"><a href="#" class="text-light text-decoration-none">Sciences</a></li>
							<li class="mb-2"><a href="#" class="text-light text-decoration-none">coding</a></li>
							<li class="mb-2"><a href="#" class="text-light text-decoration-none">algorithm</a></li>
              <li class="mb-2"><a href="#" class="text-light text-decoration-none">Languages</a></li>
              <li class="mb-2"><a href="#" class="text-light text-decoration-none">Humanities</a></li>
            </ul>
          </div>
          <div class="col-lg-4 col-md-6 mb-4">
            <h5 class="fw-bold mb-3">Contact Info</h5>
            <div class="contact-info">
              <p class="mb-2"><i class="fas fa-map-marker-alt me-2"></i>Tunisia,Tunis</p>
              <p class="mb-2"><i class="fas fa-phone me-2"></i>+216 90 549 254</p>
              <p class="mb-2"><i class="fas fa-envelope me-2"></i>edumatch@gmail.com</p>
            </div>
            <div class="newsletter mt-3">
              <h6 class="fw-bold mb-2">Stay Updated on Academic Tutoring</h6>
              <div class="input-group">
                <input type="email" class="form-control" placeholder="Your email" style="border-radius: 25px 0 0 25px;">
                <button class="btn btn-primary" type="button" style="border-radius: 0 25px 25px 0;">Subscribe</button>
              </div>
            </div>
          </div>
        </div>
        <hr class="my-4 opacity-25">
        <div class="row align-items-center">
          <div class="col-md-6">
            <p class="mb-0 text-light opacity-75">&copy; 2026 EduMatch. All rights reserved.</p>
          </div>
          <div class="col-md-6 text-md-end">
            <a href="#" class="text-light text-decoration-none me-3">Privacy Policy</a>
            <a href="#" class="text-light text-decoration-none me-3">Terms of Service</a>
            <a href="#" class="text-light text-decoration-none">Support</a>
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
    // Fonction pour supprimer
function deleteItem(id, type) {
    const label = type === 'devoir' ? 'devoir et ses corrections' : 'correction';
    if (!confirm(`Êtes-vous sûr de vouloir supprimer ce ${type === 'devoir' ? 'devoir' : 'correction'} ?`)) {
        return;
    }
    
    const action = type === 'devoir' ? 'delete' : 'deletecorrection';
    
    fetch('/eduleb/controller/devoirs.php?action=' + action + '&id=' + id, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Afficher un toast de succès
            const toast = document.createElement('div');
            toast.className = 'toast-success';
            toast.innerHTML = '<i class="fas fa-check-circle"></i> ' + data.message;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 2000);
            
            // Recharger la page après 1 seconde
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur lors de la suppression: ' + error.message);
    });
}

    // Ajouter les écouteurs sur tous les boutons supprimer
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.dataset.id;
            const type = this.dataset.type;
            deleteItem(id, type);
        });
    });

    // AUTO-HIDE TOAST
    setTimeout(function() {
        const toast = document.querySelector('.toast-success');
        if (toast) toast.remove();
    }, 4000);
    </script>
<script>
// Fonction pour rafraîchir la page et réinitialiser tous les paramètres
function refreshPage() {
    // Redirige vers feed.php sans aucun paramètre
    window.location.href = 'feed.html';
}

// Option 2: Si tu veux juste réinitialiser les champs sans recharger la page
function resetFilters() {
    document.querySelector('input[name="search"]').value = '';
    document.querySelector('select[name="sort"]').value = 'date_desc';
    // Soumettre le formulaire
    document.querySelector('.search-filter-form').submit();
}
</script>
<script>
// ============================================================
// export_devoir_pdf_v2.js — Export PDF amélioré
// ============================================================

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>'"]/g, function(m) {
        return { '&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;' }[m];
    });
}

// Convertit une URL relative en URL absolue propre
function resolveUrl(url) {
    if (!url) return null;
    let u = url.trim();
    if (u.startsWith('uploads/') || u.startsWith('./uploads/')) u = '/eduleb/' + u;
    if (!u.startsWith('http') && !u.startsWith('/')) u = '/' + u;
    return u;
}

// Charge et rend un fichier (image ou PDF) dans un conteneur
// Retourne une promesse résolue quand le média est prêt
function renderMedia(container, rawUrl, label) {
    return new Promise((resolve) => {
        if (!rawUrl) {
            container.innerHTML = `<p style="color:#94a3b8;font-size:13px;margin:0;">Aucun fichier joint</p>`;
            return resolve();
        }

        const url = resolveUrl(rawUrl);
        const ext = url.split('?')[0].split('.').pop().toLowerCase();
        const imgExts = ['jpg','jpeg','png','gif','webp','bmp','svg'];

        if (imgExts.includes(ext)) {
            const img = new Image();
            img.onload = () => {
                // Limiter la hauteur de l'image pour ne pas saturer la page
                container.innerHTML = `
                    <img src="${url}" alt="${label}"
                         style="max-width:100%;max-height:260px;width:auto;
                                display:block;margin:0 auto;
                                border-radius:10px;border:1px solid #e2e8f0;">`;
                resolve();
            };
            img.onerror = () => {
                container.innerHTML = fileLink(url, label);
                resolve();
            };
            img.src = url;
        } else if (ext === 'pdf') {
            // Aperçu PDF compact avec lien
            container.innerHTML = `
                <div style="display:flex;align-items:center;gap:12px;
                            background:#f8faff;border:1px solid #dde6ff;
                            border-radius:10px;padding:14px 18px;">
                    <div style="font-size:28px;line-height:1;">📄</div>
                    <div>
                        <div style="font-size:13px;font-weight:600;color:#3b4cca;margin-bottom:4px;">${label}</div>
                        <a href="${url}" target="_blank"
                           style="font-size:12px;color:#6C63FF;text-decoration:none;">
                           Ouvrir le fichier PDF ↗
                        </a>
                    </div>
                </div>`;
            resolve();
        } else {
            container.innerHTML = fileLink(url, label);
            resolve();
        }
    });
}

function fileLink(url, label) {
    return `<a href="${url}" target="_blank"
               style="display:inline-flex;align-items:center;gap:8px;
                      padding:10px 18px;background:#6C63FF;color:white;
                      text-decoration:none;border-radius:8px;font-size:13px;">
                📁 Télécharger — ${escapeHtml(label)}
            </a>`;
}

// Crée un div hors-écran stylisé, le rend via html2canvas, l'injecte dans le PDF
// puis le retire du DOM. Retourne le nouveau Y.
async function stampSection(pdf, htmlContent, yPos, pageH = 277, margin = 12) {
    const wrapper = document.createElement('div');
    Object.assign(wrapper.style, {
        position: 'absolute', top: '-9999px', left: '0',
        width: '720px',                // ~190 mm @ 96 dpi
        backgroundColor: '#ffffff',
        fontFamily: "'Segoe UI', Arial, sans-serif",
        boxSizing: 'border-box'
    });
    wrapper.innerHTML = htmlContent;
    document.body.appendChild(wrapper);

    // Attente rendu navigateur
    await new Promise(r => setTimeout(r, 80));

    const canvas = await html2canvas(wrapper, {
        scale: 2,
        backgroundColor: '#ffffff',
        useCORS: true,
        logging: false,
        allowTaint: false
    });
    document.body.removeChild(wrapper);

    const imgW  = 190 - margin * 0; // utilise toute la largeur utile
    const imgH  = (canvas.height * imgW) / canvas.width;

    if (yPos + imgH > pageH) {
        pdf.addPage();
        yPos = 12;
    }

    pdf.addImage(canvas.toDataURL('image/jpeg', 0.92), 'JPEG',
                 10, yPos, imgW, imgH);
    return yPos + imgH + 6;
}

// ─── Fonctions HTML de rendu ─────────────────────────────────

function htmlHeader(titre, niveau, erreur, temps, description) {
    return `
    <div style="padding:24px 28px 0;">

      <!-- Bandeau titre -->
      <div style="background:linear-gradient(135deg,#6C63FF,#9B8FFF);
                  border-radius:14px;padding:20px 24px;margin-bottom:20px;">
          <div style="display:flex;align-items:center;gap:12px;">
              <div style="background:rgba(255,255,255,.2);border-radius:10px;
                          width:44px;height:44px;display:flex;align-items:center;
                          justify-content:center;font-size:22px;">📘</div>
              <div>
                  <h1 style="margin:0;font-size:20px;font-weight:700;color:#fff;">
                      ${escapeHtml(titre)}
                  </h1>
                  <p style="margin:4px 0 0;font-size:12px;color:rgba(255,255,255,.75);">
                      Soumis le ${new Date().toLocaleDateString('fr-FR', {day:'2-digit',month:'long',year:'numeric'})}
                  </p>
              </div>
          </div>
      </div>

      <!-- Chips infos -->
      <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;">
          ${chip('🎓', 'Niveau', niveau, '#EEF2FF', '#4F46E5')}
          ${chip('⚠️', 'Erreur', erreur,  '#FFF7ED', '#C2410C')}
          ${chip('⏱️', 'Durée',  temps + ' min', '#F0FDF4', '#15803D')}
      </div>

      <!-- Description -->
      ${description ? `
      <div style="background:#F8FAFC;border-left:4px solid #6C63FF;
                  border-radius:0 10px 10px 0;padding:14px 16px;margin-bottom:4px;">
          <p style="margin:0 0 4px;font-size:11px;font-weight:700;color:#6C63FF;
                    text-transform:uppercase;letter-spacing:.5px;">Description</p>
          <p style="margin:0;font-size:13px;color:#374151;line-height:1.6;">
              ${escapeHtml(description)}
          </p>
      </div>` : ''}

    </div>`;
}

function chip(icon, label, value, bg, color) {
    return `
    <div style="background:${bg};border-radius:8px;padding:8px 14px;
                display:inline-flex;align-items:center;gap:6px;min-width:0;">
        <span style="font-size:14px;">${icon}</span>
        <span style="font-size:11px;color:#6B7280;">${label} :</span>
        <span style="font-size:13px;font-weight:600;color:${color};">${escapeHtml(value || '—')}</span>
    </div>`;
}

function htmlSectionTitle(icon, title, color = '#6C63FF') {
    return `
    <div style="display:flex;align-items:center;gap:10px;
                border-bottom:2px solid ${color}20;padding-bottom:8px;margin-bottom:16px;">
        <div style="background:${color}15;border-radius:8px;padding:6px 10px;
                    font-size:16px;">${icon}</div>
        <h2 style="margin:0;font-size:15px;font-weight:700;color:${color};">${title}</h2>
    </div>`;
}

function htmlCorrectionCard(index, note, commentaire) {
    const pct = parseInt(note) || 0;
    const noteNum = note.split('/')[0] || '—';
    const noteTotal = note.split('/')[1] || '20';

    // Couleur dynamique selon la note
    let noteBg, noteColor;
    if (pct >= 14) { noteBg = '#D1FAE5'; noteColor = '#065F46'; }
    else if (pct >= 10) { noteBg = '#FEF3C7'; noteColor = '#92400E'; }
    else { noteBg = '#FEE2E2'; noteColor = '#991B1B'; }

    return `
    <div style="border:1.5px solid #E2E8F0;border-radius:14px;
                padding:18px 20px;background:#FAFAFA;">

        <div style="display:flex;justify-content:space-between;
                    align-items:center;margin-bottom:14px;">
            <div style="background:#6C63FF;color:#fff;border-radius:20px;
                        padding:5px 14px;font-size:12px;font-weight:700;">
                Correction ${index}
            </div>
            <div style="background:${noteBg};border-radius:20px;
                        padding:5px 14px;font-size:13px;font-weight:700;color:${noteColor};">
                ${escapeHtml(noteNum)} / ${escapeHtml(noteTotal)}
            </div>
        </div>

        ${commentaire ? `
        <div style="background:#fff;border:1px solid #E2E8F0;border-radius:10px;
                    padding:12px 14px;margin-bottom:14px;">
            <p style="margin:0 0 6px;font-size:11px;font-weight:700;color:#6C63FF;
                      text-transform:uppercase;letter-spacing:.5px;">💬 Commentaire</p>
            <p style="margin:0;font-size:13px;color:#374151;line-height:1.6;">
                ${escapeHtml(commentaire)}
            </p>
        </div>` : ''}
    </div>`;
}

// ─── Export principal ────────────────────────────────────────

async function exportDevoirToPDF(devoirCard, devoirId) {

    // ── 1. Récupération des données ──────────────────────────
    const titreElem = devoirCard.querySelector('.card-title');
    const titre = titreElem ? titreElem.innerText.replace(/[📘]/g,'').trim() : 'Devoir';

    const descElem = devoirCard.querySelector('.card-description');
    const description = descElem ? descElem.innerText.trim() : '';

    const chips = devoirCard.querySelectorAll('.detail-chip');
    const getText = (el, prefix) => {
        const s = el?.querySelector('span');
        return s ? s.innerText.replace(prefix,'').replace('min','').trim() : '';
    };
    const niveau = getText(chips[0], 'Niveau :');
    const erreur = getText(chips[1], 'Erreur :');
    const temps  = getText(chips[2], 'Temps :');

    const devoirFileLink = devoirCard.querySelector('.file-link');
    const devoirFileUrl  = devoirFileLink?.href || null;

    const correctionSubcards = devoirCard.querySelectorAll('.correction-subcard');
    const correctionsData = [];
    for (const sub of correctionSubcards) {
        const commentaire  = sub.querySelector('.card-description')?.innerText.replace('Commentaire :','').trim() || '';
        const note         = sub.querySelector('.note-badge')?.innerText.trim() || '';
        let fichierUrl     = sub.querySelector('.file-link')?.href || null;
        if (!fichierUrl) {
            const attr = sub.getAttribute('data-fichier-corrige');
            if (attr) fichierUrl = '/eduleb/uploads/correction/' + attr;
        }
        correctionsData.push({ commentaire, note, fichierUrl });
    }

    // ── 2. Loader ────────────────────────────────────────────
    const loader = document.createElement('div');
    loader.className = 'pdf-loader';
    loader.innerHTML = `
        <div style="position:fixed;inset:0;background:rgba(0,0,0,.45);
                    display:flex;flex-direction:column;align-items:center;
                    justify-content:center;z-index:99999;gap:16px;">
            <div style="width:48px;height:48px;border:4px solid #fff3;
                        border-top-color:#6C63FF;border-radius:50%;
                        animation:spin 1s linear infinite;"></div>
            <p style="color:#fff;font-size:14px;margin:0;">Génération du PDF…</p>
        </div>
        <style>@keyframes spin{to{transform:rotate(360deg)}}</style>`;
    document.body.appendChild(loader);

    try {
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF({ unit:'mm', format:'a4', orientation:'portrait' });
        let y = 10;

        // ── PAGE 1 : En-tête + méta ──────────────────────────
        y = await stampSection(pdf,
            htmlHeader(titre, niveau, erreur, temps, description),
            y);

        // ── Fichier du devoir ────────────────────────────────
        const mediaWrap = document.createElement('div');
        Object.assign(mediaWrap.style, {
            position:'absolute', top:'-9999px', left:'0',
            width:'720px', backgroundColor:'#ffffff',
            fontFamily:"'Segoe UI', Arial, sans-serif",
            padding:'0 28px 24px', boxSizing:'border-box'
        });
        mediaWrap.innerHTML = `
            <div style="padding:20px 0 0;">
                ${htmlSectionTitle('📎', 'Fichier du devoir')}
                <div id="__devoir_file__" style="text-align:center;min-height:40px;"></div>
            </div>`;
        document.body.appendChild(mediaWrap);
        await renderMedia(mediaWrap.querySelector('#__devoir_file__'), devoirFileUrl, titre);
        await new Promise(r => setTimeout(r, 400));

        const cvs = await html2canvas(mediaWrap, {
            scale:2, backgroundColor:'#ffffff',
            useCORS:true, logging:false, allowTaint:false
        });
        document.body.removeChild(mediaWrap);

        const imgW = 190, imgH = (cvs.height * imgW) / cvs.width;
        if (y + imgH > 277) { pdf.addPage(); y = 12; }
        pdf.addImage(cvs.toDataURL('image/jpeg', 0.92), 'JPEG', 10, y, imgW, imgH);
        y += imgH + 8;

        // ── PAGE(S) CORRECTIONS ──────────────────────────────
        if (correctionsData.length > 0) {
            // Titre de section corrections sur nouvelle page
            pdf.addPage(); y = 12;

            y = await stampSection(pdf, `
                <div style="padding:8px 28px 16px;">
                    ${htmlSectionTitle('✅', `${correctionsData.length} correction(s) reçue(s)`, '#10B981')}
                </div>`, y);

            for (let i = 0; i < correctionsData.length; i++) {
                const corr = correctionsData[i];

                // Carte textuelle
                y = await stampSection(pdf,
                    `<div style="padding:0 28px 4px;">
                        ${htmlCorrectionCard(i + 1, corr.note, corr.commentaire)}
                     </div>`, y);

                // Fichier corrigé
                if (corr.fichierUrl) {
                    const cWrap = document.createElement('div');
                    Object.assign(cWrap.style, {
                        position:'absolute', top:'-9999px', left:'0',
                        width:'720px', backgroundColor:'#ffffff',
                        fontFamily:"'Segoe UI', Arial, sans-serif",
                        padding:'0 28px 16px', boxSizing:'border-box'
                    });
                    cWrap.innerHTML = `
                        <div style="margin-top:4px;">
                            <p style="margin:0 0 8px;font-size:11px;font-weight:700;
                                      color:#6C63FF;text-transform:uppercase;
                                      letter-spacing:.5px;">📎 Fichier corrigé</p>
                            <div id="__cf_${i}__" style="text-align:center;min-height:40px;"></div>
                        </div>`;
                    document.body.appendChild(cWrap);
                    await renderMedia(cWrap.querySelector(`#__cf_${i}__`), corr.fichierUrl, `Correction ${i+1}`);
                    await new Promise(r => setTimeout(r, 400));

                    const cCvs = await html2canvas(cWrap, {
                        scale:2, backgroundColor:'#ffffff',
                        useCORS:true, logging:false, allowTaint:false
                    });
                    document.body.removeChild(cWrap);

                    const cW = 190, cH = (cCvs.height * cW) / cCvs.width;
                    if (y + cH > 277) { pdf.addPage(); y = 12; }
                    pdf.addImage(cCvs.toDataURL('image/jpeg', 0.92), 'JPEG', 10, y, cW, cH);
                    y += cH + 10;
                }

                // Séparateur léger entre corrections
                if (i < correctionsData.length - 1) y += 4;
            }
        } else {
            pdf.addPage(); y = 12;
            y = await stampSection(pdf, `
                <div style="padding:16px 28px;">
                    ${htmlSectionTitle('✅', 'Corrections', '#10B981')}
                    <div style="text-align:center;padding:32px;color:#94a3b8;font-size:14px;">
                        Aucune correction disponible pour ce devoir.
                    </div>
                </div>`, y);
        }

        // ── Pied de page sur chaque page ─────────────────────
        const totalPages = pdf.internal.getNumberOfPages();
        for (let p = 1; p <= totalPages; p++) {
            pdf.setPage(p);
            pdf.setFontSize(8);
            pdf.setTextColor(160, 160, 160);
            pdf.text(`Page ${p} / ${totalPages}`, 105, 291, { align:'center' });
            pdf.text(`Devoir #${devoirId} — ${new Date().toLocaleDateString('fr-FR')}`, 10, 291);
        }

        // ── Sauvegarde ───────────────────────────────────────
        const filename = `Devoir_${devoirId}_${new Date().toISOString().slice(0,10)}.pdf`;
        pdf.save(filename);

        // Toast succès
        const toast = document.createElement('div');
        toast.style.cssText = `
            position:fixed;bottom:24px;right:24px;
            background:#10B981;color:#fff;
            padding:12px 20px;border-radius:12px;
            z-index:100000;font-weight:600;font-size:14px;
            box-shadow:0 4px 16px rgba(16,185,129,.3);
            display:flex;align-items:center;gap:8px;`;
        toast.innerHTML = `<span>✓</span> PDF exporté avec succès !`;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3500);

    } catch (err) {
        console.error('[ExportPDF]', err);
        alert('Erreur lors de la génération du PDF :\n' + err.message);
    } finally {
        loader.remove();
    }
}

// ─── Attacher les événements ─────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-export-pdf').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const card = this.closest('.feed-card');
            const id   = this.getAttribute('data-devoir-id');
            if (card && id) {
                exportDevoirToPDF(card, id);
            } else {
                alert("Erreur : impossible d'identifier le devoir.");
            }
        });
    });
});
</script>

<script>
// Variables globales pour les graphiques
let devoirsPieChart = null;
let activityLineChart = null;

// Données PHP passées à JavaScript
const devoirsData = <?php 
    $devoirsJson = [];
    foreach ($devoirs as $d) {
        $devoirsJson[] = [
            'id_devoir' => $d['id_devoir'],
            'date_soumission' => $d['date_soumission']
        ];
    }
    echo json_encode($devoirsJson);
?>;

// Fonction utilitaire pour obtenir le numéro de semaine
function getWeekNumber(date) {
    const d = new Date(date);
    d.setHours(0, 0, 0, 0);
    d.setDate(d.getDate() + 3 - (d.getDay() + 6) % 7);
    const week1 = new Date(d.getFullYear(), 0, 4);
    return 1 + Math.round(((d - week1) / 86400000 - 3 + (week1.getDay() + 6) % 7) / 7);
}

// Compter les devoirs par mois
function countDevoirsByMonth(devoirs) {
    const counts = {};
    devoirs.forEach(devoir => {
        const date = new Date(devoir.date_soumission);
        const key = `${date.getFullYear()}-${date.getMonth() + 1}`;
        const label = date.toLocaleString('fr-FR', { month: 'short', year: 'numeric' });
        if (!counts[key]) {
            counts[key] = { count: 0, label: label, date: date };
        }
        counts[key].count++;
    });
    return Object.values(counts).sort((a, b) => a.date - b.date);
}

// Compter les devoirs par semaine
function countDevoirsByWeek(devoirs) {
    const counts = {};
    devoirs.forEach(devoir => {
        const date = new Date(devoir.date_soumission);
        const weekNumber = getWeekNumber(date);
        const key = `${date.getFullYear()}-S${weekNumber}`;
        const label = `S${weekNumber} ${date.getFullYear()}`;
        if (!counts[key]) {
            counts[key] = { count: 0, label: label, date: date };
        }
        counts[key].count++;
    });
    return Object.values(counts).sort((a, b) => a.date - b.date);
}

// Compter les devoirs par période pour la courbe
function countDevoirsByPeriod(devoirs, periodRange = '6months') {
    let filteredDevoirs = [...devoirs];
    
    if (periodRange !== 'all') {
        const monthsToShow = periodRange === '6months' ? 6 : 12;
        const cutoffDate = new Date();
        cutoffDate.setMonth(cutoffDate.getMonth() - monthsToShow);
        filteredDevoirs = devoirs.filter(d => new Date(d.date_soumission) >= cutoffDate);
    }
    
    const counts = {};
    filteredDevoirs.forEach(devoir => {
        const date = new Date(devoir.date_soumission);
        const key = `${date.getFullYear()}-${date.getMonth() + 1}`;
        const label = date.toLocaleString('fr-FR', { month: 'short', year: 'numeric' });
        if (!counts[key]) {
            counts[key] = { count: 0, label: label, date: date };
        }
        counts[key].count++;
    });
    
    const sorted = Object.values(counts).sort((a, b) => a.date - b.date);
    return {
        labels: sorted.map(item => item.label),
        data: sorted.map(item => item.count)
    };
}

// Mettre à jour le graphique en camembert
function updatePieChart(devoirs, period = 'month') {
    let data = period === 'month' ? countDevoirsByMonth(devoirs) : countDevoirsByWeek(devoirs);
    
    const labels = data.map(item => item.label);
    const counts = data.map(item => item.count);
    const total = counts.reduce((a, b) => a + b, 0);
    
    // Trouver le mois le plus actif
    if (data.length > 0) {
        const maxMonth = data.reduce((max, item) => item.count > max.count ? item : max, data[0]);
        document.getElementById('statMoisActif').textContent = maxMonth.label;
    }
    
    document.getElementById('chartTotalLabel').textContent = `Total: ${total} devoirs`;
    document.getElementById('statMoyenneMois').textContent = data.length > 0 ? Math.round(total / data.length) : 0;
    
    const ctx = document.getElementById('devoirsPieChart').getContext('2d');
    if (devoirsPieChart) devoirsPieChart.destroy();
    
    const colors = ['#6366f1', '#06b6d4', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec489a', '#14b8a6', '#f97316', '#84cc16'];
    
    devoirsPieChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: counts,
                backgroundColor: colors.slice(0, labels.length),
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { 
                    position: 'right', 
                    labels: { font: { size: 11 }, boxWidth: 10 } 
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.raw / total) * 100).toFixed(1);
                            return `${context.label}: ${context.raw} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

function loadSentimentStats() {
    fetch('/eduleb/controller/devoirs.php?action=sentimentStats')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const stats = data.stats;
                // Total des étudiants en difficulté (negatif + frustration + confusion + stress)
                const enDifficulte = (stats.negatif || 0) + (stats.frustration || 0) + (stats.confusion || 0) + (stats.stress || 0);
                document.getElementById('statSentimentNegatif').textContent = enDifficulte;
                document.getElementById('statUrgences').textContent = stats.urgences || 0;
            }
        })
        .catch(error => console.error('Erreur chargement stats sentiments:', error));
}


// Mettre à jour le graphique en courbe
function updateLineChart(devoirs, periodRange = '6months') {
    const { labels, data } = countDevoirsByPeriod(devoirs, periodRange);
    const total = data.reduce((a, b) => a + b, 0);
    
    document.getElementById('activityTotalLabel').textContent = `Total: ${total} devoirs`;
    
    const ctx = document.getElementById('activityLineChart').getContext('2d');
    if (activityLineChart) activityLineChart.destroy();
    
    activityLineChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Devoirs soumis',
                data: data,
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.3,
                pointBackgroundColor: '#10b981',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { position: 'top' },
                tooltip: { 
                    callbacks: { 
                        label: (ctx) => `📘 ${ctx.raw} devoir(s)` 
                    } 
                }
            },
            scales: {
                y: { 
                    beginAtZero: true, 
                    title: { display: true, text: 'Nombre de devoirs' }, 
                    ticks: { stepSize: 1, precision: 0 } 
                },
                x: { title: { display: true, text: 'Période' } }
            }
        }
    });
}

// Initialiser les graphiques au chargement
document.addEventListener('DOMContentLoaded', function() {
    if (devoirsData.length > 0) {
        updatePieChart(devoirsData, 'month');
        updateLineChart(devoirsData, '6months');
    }

    loadSentimentStats();
    
    // Écouteur pour le camembert
    const chartPeriodSelect = document.getElementById('chartPeriodSelect');
    if (chartPeriodSelect) {
        chartPeriodSelect.addEventListener('change', function() {
            updatePieChart(devoirsData, this.value);
        });
    }
    
    // Écouteur pour la courbe d'activité
    const activityPeriodSelect = document.getElementById('activityPeriodSelect');
    if (activityPeriodSelect) {
        activityPeriodSelect.addEventListener('change', function() {
            updateLineChart(devoirsData, this.value);
        });
    }
});
</script>

<script>
    // ============================================================
// CHATBOT EDUCATIF
// ============================================================

document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('chatbotToggleBtn');
    const chatbotWindow = document.getElementById('chatbotWindow');
    const closeBtn = document.getElementById('chatbotCloseBtn');
    const sendBtn = document.getElementById('chatbotSendBtn');
    const input = document.getElementById('chatbotInput');
    const messagesContainer = document.getElementById('chatbotMessages');
    const typingIndicator = document.getElementById('typingIndicator');
    
    let isTyping = false;
    
    // Ouvrir/fermer le chatbot
    toggleBtn.addEventListener('click', () => {
        chatbotWindow.style.display = 'flex';
        toggleBtn.style.display = 'none';
        input.focus();
    });
    
    closeBtn.addEventListener('click', () => {
        chatbotWindow.style.display = 'none';
        toggleBtn.style.display = 'flex';
    });
    
    // Envoyer un message
    function sendMessage() {
        const message = input.value.trim();
        if (!message || isTyping) return;
        
        // Afficher le message de l'utilisateur
        addMessage(message, 'user');
        input.value = '';
        
        // Afficher l'indicateur de frappe
        showTyping();
        
        // Appeler l'API
        fetch(`/eduleb/controller/devoirs.php?action=chat&message=${encodeURIComponent(message)}`)
            .then(response => response.json())
            .then(data => {
                hideTyping();
                addMessage(data.reply, 'bot');
            })
            .catch(error => {
                console.error('Erreur:', error);
                hideTyping();
                addMessage("Désolé, je rencontre une difficulté. Veuillez réessayer.", 'bot');
            });
    }
    
    // Ajouter un message
    function addMessage(text, sender) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${sender}`;
        
        const avatar = sender === 'bot' ? '<i class="fas fa-robot"></i>' : '<i class="fas fa-user-graduate"></i>';
        
        messageDiv.innerHTML = `
            <div class="message-avatar">
                ${avatar}
            </div>
            <div class="message-content">
                <div class="message-text">
                    ${formatMessage(text)}
                </div>
                <div class="message-time">
                    ${new Date().toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'})}
                </div>
            </div>
        `;
        
        messagesContainer.appendChild(messageDiv);
        scrollToBottom();
    }
    
    // Formater le message (liens, sauts de ligne, etc.)
    function formatMessage(text) {
        let formatted = text
            .replace(/\n/g, '<br>')
            .replace(/(https?:\/\/[^\s]+)/g, '<a href="$1" target="_blank" style="color:#6366f1;">$1</a>');
        return formatted;
    }
    
    function showTyping() {
        isTyping = true;
        typingIndicator.style.display = 'flex';
        scrollToBottom();
    }
    
    function hideTyping() {
        isTyping = false;
        typingIndicator.style.display = 'none';
    }
    
    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    
    // Questions rapides
    document.querySelectorAll('.quick-question').forEach(btn => {
        btn.addEventListener('click', () => {
            input.value = btn.getAttribute('data-question');
            sendMessage();
        });
    });
    
    // Envoyer avec Entrée
    input.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });
    
    sendBtn.addEventListener('click', sendMessage);
});
</script>

<!-- ============================================================ -->
<!-- CHATBOT FLOATING BUTTON & WINDOW -->
<!-- ============================================================ -->

<!-- Bouton flottant -->
<button id="chatbotToggleBtn" class="chatbot-toggle">
    <i class="fas fa-comment-dots"></i>
    <span class="chatbot-badge">EduBot</span>
</button>

<!-- Fenêtre du chatbot -->
<div id="chatbotWindow" class="chatbot-window" style="display: none;">
    <div class="chatbot-header">
        <div class="chatbot-header-left">
            <div class="chatbot-avatar">
                <i class="fas fa-robot"></i>
            </div>
            <div class="chatbot-info">
                <h4>EduBot</h4>
                <p>Assistant éducatif intelligent</p>
            </div>
        </div>
        <button id="chatbotCloseBtn" class="chatbot-close">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <div class="chatbot-messages" id="chatbotMessages">
        <div class="message bot">
            <div class="message-avatar">
                <i class="fas fa-robot"></i>
            </div>
            <div class="message-content">
                <div class="message-text">
                    Bonjour ! Je suis <strong>EduBot</strong> 🤖<br><br>
                    Je peux vous aider sur :<br>
                    • 📚 Comprendre un cours<br>
                    • ✏️ Résoudre un exercice<br>
                    • 💡 Expliquer un concept<br>
                    • 📖 Préparer un examen<br><br>
                    Posez-moi votre question !
                </div>
                <div class="message-time"><?= date('H:i') ?></div>
            </div>
        </div>
    </div>
    
    <div class="chatbot-input-area">
        <div class="quick-questions">
            <button class="quick-question" data-question="Explique-moi les fonctions en Python">🐍 Python : fonctions</button>
            <button class="quick-question" data-question="Comment faire une jointure SQL ?">🗄️ SQL : jointures</button>
            <button class="quick-question" data-question="C'est quoi un algorithme ?">🧠 Algorithmes</button>
            <button class="quick-question" data-question="Aide-moi pour mon devoir">📘 Aide devoir</button>
        </div>
        <div class="input-wrapper">
            <input type="text" id="chatbotInput" placeholder="Écrivez votre message..." autocomplete="off">
            <button id="chatbotSendBtn">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
        <div class="typing-indicator" id="typingIndicator" style="display: none;">
            <span></span>
            <span></span>
            <span></span>
            <span class="typing-text">EduBot écrit...</span>
        </div>
    </div>
</div>

<script>
    // ============================================================
// TEXT-TO-SPEECH (Synthèse vocale)
// ============================================================

class TextToSpeech {
    constructor() {
        this.synthesis = window.speechSynthesis;
        this.currentUtterance = null;
        this.isPlaying = false;
        this.currentButton = null;
    }
    
    speak(text, buttonElement) {
        // Arrêter la lecture en cours
        this.stop();
        
        if (!text || text.trim() === '') {
            this.showToast("Aucun texte à lire", "warning");
            return;
        }
        
        // Nettoyer le texte des balises HTML
        const cleanText = this.cleanText(text);
        
        // Créer une nouvelle utterance
        const utterance = new SpeechSynthesisUtterance(cleanText);
        
        // Configurer la voix (français par défaut)
        utterance.lang = 'fr-FR';
        utterance.rate = 0.9;  // Vitesse légèrement plus lente
        utterance.pitch = 1.0;
        utterance.volume = 1;
        
        // Événements
        utterance.onstart = () => {
            this.isPlaying = true;
            this.currentButton = buttonElement;
            if (buttonElement) {
                buttonElement.classList.add('playing');
                buttonElement.innerHTML = '<i class="fas fa-stop"></i>';
                this.showToast("Lecture en cours...", "info");
            }
        };
        
        utterance.onend = () => {
            this.isPlaying = false;
            if (this.currentButton) {
                this.currentButton.classList.remove('playing');
                this.currentButton.innerHTML = '<i class="fas fa-volume-up"></i>';
            }
            this.currentButton = null;
        };
        
        utterance.onerror = (event) => {
            console.error('Erreur de synthèse vocale:', event);
            this.isPlaying = false;
            if (this.currentButton) {
                this.currentButton.classList.remove('playing');
                this.currentButton.innerHTML = '<i class="fas fa-volume-up"></i>';
            }
            this.showToast("Erreur de lecture audio", "error");
        };
        
        this.currentUtterance = utterance;
        this.synthesis.speak(utterance);
    }
    
    stop() {
        if (this.synthesis.speaking || this.synthesis.pending) {
            this.synthesis.cancel();
        }
        this.isPlaying = false;
        if (this.currentButton) {
            this.currentButton.classList.remove('playing');
            this.currentButton.innerHTML = '<i class="fas fa-volume-up"></i>';
            this.currentButton = null;
        }
    }
    
    cleanText(text) {
        // Supprimer les balises HTML
        let clean = text.replace(/<[^>]*>/g, '');
        // Supprimer les emojis et caractères spéciaux
        clean = clean.replace(/[^\w\s\u00C0-\u00FF.,!?;:()\-]/g, '');
        // Nettoyer les espaces multiples
        clean = clean.replace(/\s+/g, ' ');
        return clean.trim();
    }
    
    showToast(message, type) {
        // Créer un toast temporaire
        const toast = document.createElement('div');
        toast.className = 'audio-toast';
        toast.innerHTML = `<i class="fas ${type === 'error' ? 'fa-exclamation-circle' : 'fa-volume-up'}"></i> ${message}`;
        toast.style.cssText = `
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: ${type === 'error' ? '#ef4444' : '#3b82f6'};
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            z-index: 10000;
            animation: fadeOut 2s ease forwards;
        `;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 2000);
    }
}

// Initialiser le TTS
const tts = new TextToSpeech();

// Arrêter la lecture quand on quitte la page
window.addEventListener('beforeunload', () => {
    if (tts.synthesis) {
        tts.synthesis.cancel();
    }
});
</script>

<script>

    // ============================================================
// ATTACHER LES ÉVÉNEMENTS DE LECTURE AUDIO
// ============================================================

function initTextToSpeech() {
    // Boutons pour lire la description du devoir
    document.querySelectorAll('.speak-description').forEach(btn => {
        // Enlever l'ancien écouteur s'il existe
        btn.removeEventListener('click', handleDescriptionClick);
        btn.addEventListener('click', handleDescriptionClick);
    });
    
    // Boutons pour lire n'importe quel texte
    document.querySelectorAll('.speak-text').forEach(btn => {
        btn.removeEventListener('click', handleTextClick);
        btn.addEventListener('click', handleTextClick);
    });
}

function handleDescriptionClick(e) {
    e.stopPropagation();
    const btn = e.currentTarget;
    const text = btn.getAttribute('data-text');
    
    if (tts.isPlaying && tts.currentButton === btn) {
        tts.stop();
    } else {
        tts.speak(text, btn);
    }
}

function handleTextClick(e) {
    e.stopPropagation();
    const btn = e.currentTarget;
    const text = btn.getAttribute('data-text');
    
    if (tts.isPlaying && tts.currentButton === btn) {
        tts.stop();
    } else {
        tts.speak(text, btn);
    }
}

// Réinitialiser les événements quand le DOM change (après chargement des cartes)
const observer = new MutationObserver(() => {
    initTextToSpeech();
});

observer.observe(document.getElementById('feedContainer'), { 
    childList: true, 
    subtree: true 
});

// Initialiser au chargement
document.addEventListener('DOMContentLoaded', initTextToSpeech);
</script>
<script>
    // ============================================================
// BINGO DES COMPÉTENCES
// ============================================================

/// Liste des matières (5x5)
const COMPETENCES = [
    // Ligne 1 - Sciences exactes
    { name: "Mathématiques", icon: "📐", keywords: ["math", "algèbre", "géométrie", "calcul", "équation", "fonction"] },
    { name: "Physique", icon: "⚡", keywords: ["physique", "mécanique", "électricité", "optique", "thermodynamique"] },
    { name: "Chimie", icon: "🧪", keywords: ["chimie", "réaction", "molécule", "atome", "tableau périodique"] },
    { name: "SVT", icon: "🔬", keywords: ["svt", "biologie", "géologie", "cellule", "écosystème", "génétique"] },
    { name: "Informatique", icon: "💻", keywords: ["info", "informatique", "programmation", "algorithme", "code", "python"] },
    // Ligne 2 - Sciences humaines
    { name: "Français", icon: "📖", keywords: ["français", "grammaire", "conjugaison", "orthographe", "littérature", "rédaction"] },
    { name: "Anglais", icon: "🇬🇧", keywords: ["anglais", "english", "vocabulaire", "grammaire anglaise", "traduction"] },
    { name: "Espagnol", icon: "🇪🇸", keywords: ["espagnol", "español", "vocabulario", "gramática"] },
    { name: "Histoire", icon: "🏛️", keywords: ["histoire", "guerre", "révolution", "antiquité", "moyen âge", "chronologie"] },
    { name: "Géographie", icon: "🌍", keywords: ["géographie", "carte", "population", "climat", "relief", "frontière"] },
    // Ligne 3 - Arts et culture
    { name: "Philosophie", icon: "💭", keywords: ["philosophie", "platon", "socrate", "cogito", "métaphysique", "éthique"] },
    { name: "Arts plastiques", icon: "🎨", keywords: ["art", "peinture", "dessin", "couleur", "perspective", "création"] },
    { name: "Musique", icon: "🎵", keywords: ["musique", "solfège", "partition", "instrument", "rythme", "mélodie"] },
    { name: "Éducation physique", icon: "⚽", keywords: ["sport", "eps", "athlétisme", "football", "basket", "natation"] },
    { name: "Technologie", icon: "🔧", keywords: ["techno", "technologie", "mécanique", "électronique", "design", "prototype"] },
    // Ligne 4 - Sciences sociales
    { name: "Économie", icon: "📊", keywords: ["économie", "marché", "offre", "demande", "prix", "croissance", "pib"] },
    { name: "SES", icon: "📈", keywords: ["ses", "sociologie", "économie", "politique", "social"] },
    { name: "Droit", icon: "⚖️", keywords: ["droit", "loi", "constitution", "justice", "contrat", "juridique"] },
    { name: "Management", icon: "👔", keywords: ["management", "gestion", "entreprise", "leadership", "stratégie"] },
    { name: "Communication", icon: "💬", keywords: ["communication", "média", "publicité", "réseaux", "information"] },
    // Ligne 5 - Spécialités et options
    { name: "NSI", icon: "🖥️", keywords: ["nsi", "numérique", "informatique", "python", "web", "base de données"] },
    { name: "LLCE", icon: "📚", keywords: ["llce", "littérature", "langue", "civilisation", "culture"] },
    { name: "HGGSP", icon: "🌐", keywords: ["hggsp", "géopolitique", "relations internationales", "puissance", "monde"] },
    { name: "Maths expertes", icon: "📐", keywords: ["maths expertes", "arithmétique", "complexe", "matrice", "géométrie"] },
    { name: "Option", icon: "⭐", keywords: ["option", "spécialité", "supplément", "approfondissement"] }
];
// État du bingo (stocké en localStorage)
let bingoState = {};
let unlockedLines = [];

// Initialiser le bingo
function initBingo() {
    // Charger depuis localStorage
    const saved = localStorage.getItem('edumatch_bingo');
    if (saved) {
        bingoState = JSON.parse(saved);
    } else {
        // Créer un nouvel état (tout false)
        COMPETENCES.forEach((comp, index) => {
            bingoState[index] = false;
        });
        saveBingo();
    }
    
    // Afficher la grille
    renderBingoGrid();
    
    // Mettre à jour les stats
    updateBingoStats();
}

// Sauvegarder l'état du bingo
function saveBingo() {
    localStorage.setItem('edumatch_bingo', JSON.stringify(bingoState));
}

// Afficher la grille
function renderBingoGrid() {
    const grid = document.getElementById('bingoGrid');
    if (!grid) return;
    
    grid.innerHTML = '';
    
    COMPETENCES.forEach((comp, index) => {
        const cell = document.createElement('div');
        cell.className = `bingo-cell ${bingoState[index] ? 'completed' : ''}`;
        cell.innerHTML = `
            <div class="bingo-cell-icon">${comp.icon}</div>
            <div class="bingo-cell-name">${comp.name}</div>
        `;
        cell.addEventListener('click', () => toggleCompetence(index));
        grid.appendChild(cell);
    });
}

// Basculer une compétence (manuellement)
function toggleCompetence(index) {
    bingoState[index] = !bingoState[index];
    saveBingo();
    renderBingoGrid();
    checkBingoLines();
    updateBingoStats();
    
    if (bingoState[index]) {
        showToast(`🎉 Compétence débloquée : ${COMPETENCES[index].name} !`, 'success');
    }
}

// Vérifier les lignes complétées (Bingo)
function checkBingoLines() {
    const newCompletedLines = [];
    
    // Vérifier les 5 lignes horizontales
    for (let i = 0; i < 5; i++) {
        let lineComplete = true;
        for (let j = 0; j < 5; j++) {
            if (!bingoState[i * 5 + j]) {
                lineComplete = false;
                break;
            }
        }
        if (lineComplete && !unlockedLines.includes(`h${i}`)) {
            newCompletedLines.push(`h${i}`);
            showBingoMessage(`🎉 Bingo ! Vous avez complété la ligne ${i + 1} !`);
        }
    }
    
    // Vérifier les 5 lignes verticales
    for (let i = 0; i < 5; i++) {
        let lineComplete = true;
        for (let j = 0; j < 5; j++) {
            if (!bingoState[j * 5 + i]) {
                lineComplete = false;
                break;
            }
        }
        if (lineComplete && !unlockedLines.includes(`v${i}`)) {
            newCompletedLines.push(`v${i}`);
            showBingoMessage(`🎉 Bingo ! Vous avez complété la colonne ${i + 1} !`);
        }
    }
    
    // Vérifier la diagonale principale
    let diag1Complete = true;
    for (let i = 0; i < 5; i++) {
        if (!bingoState[i * 5 + i]) {
            diag1Complete = false;
            break;
        }
    }
    if (diag1Complete && !unlockedLines.includes('d1')) {
        newCompletedLines.push('d1');
        showBingoMessage(`🎉 Bingo ! Vous avez complété la diagonale !`);
    }
    
    // Vérifier la diagonale secondaire
    let diag2Complete = true;
    for (let i = 0; i < 5; i++) {
        if (!bingoState[i * 5 + (4 - i)]) {
            diag2Complete = false;
            break;
        }
    }
    if (diag2Complete && !unlockedLines.includes('d2')) {
        newCompletedLines.push('d2');
        showBingoMessage(`🎉 Bingo ! Vous avez complété la diagonale !`);
    }
    
    // Ajouter les nouvelles lignes complétées
    newCompletedLines.forEach(line => unlockedLines.push(line));
    
    // Sauvegarder les lignes dans localStorage
    localStorage.setItem('edumatch_bingo_lines', JSON.stringify(unlockedLines));
}

// Charger les lignes déjà complétées
function loadUnlockedLines() {
    const saved = localStorage.getItem('edumatch_bingo_lines');
    if (saved) {
        unlockedLines = JSON.parse(saved);
    }
}

// Mettre à jour les statistiques
function updateBingoStats() {
    const completedCount = Object.values(bingoState).filter(v => v === true).length;
    const percentage = Math.round((completedCount / 25) * 100);
    
    const progressBar = document.querySelector('.bingo-progress-fill');
    const progressSpan = document.getElementById('bingoProgress');
    const countSpan = document.getElementById('bingoCount');
    
    if (progressBar) progressBar.style.width = `${percentage}%`;
    if (progressSpan) progressSpan.textContent = `${percentage}%`;
    if (countSpan) countSpan.textContent = `${completedCount} / 25 compétences`;
    
    // Si toutes les compétences sont débloquées
    if (completedCount === 25) {
        showBingoMessage(`🏆 Félicitations ! Vous avez complété TOUT le bingo ! Vous êtes un maître ! 🏆`);
    }
}

// Afficher un message de Bingo
function showBingoMessage(message) {
    const messageDiv = document.getElementById('bingoMessage');
    const messageText = document.getElementById('bingoMessageText');
    
    if (messageDiv && messageText) {
        messageText.textContent = message;
        messageDiv.style.display = 'block';
        
        // Jouer un son (optionnel - nécessite interaction utilisateur)
        // setTimeout(() => {
        //     messageDiv.style.display = 'none';
        // }, 3000);
    }
}

// Fermer le message
function closeBingoMessage() {
    const messageDiv = document.getElementById('bingoMessage');
    if (messageDiv) messageDiv.style.display = 'none';
}

// Réinitialiser le bingo
function resetBingo() {
    if (confirm('Êtes-vous sûr de vouloir réinitialiser toute votre progression ?')) {
        COMPETENCES.forEach((_, index) => {
            bingoState[index] = false;
        });
        unlockedLines = [];
        saveBingo();
        localStorage.setItem('edumatch_bingo_lines', JSON.stringify(unlockedLines));
        renderBingoGrid();
        updateBingoStats();
        showToast('Progression réinitialisée', 'info');
    }
}

// Débloquer les compétences basées sur les mots-clés des devoirs
function unlockCompetencesFromKeywords(motsCles) {
    if (!motsCles) return;
    
    const motsLower = motsCles.toLowerCase();
    let newUnlocked = false;
    
    COMPETENCES.forEach((comp, index) => {
        if (!bingoState[index]) {
            const matched = comp.keywords.some(keyword => 
                motsLower.includes(keyword.toLowerCase())
            );
            if (matched) {
                bingoState[index] = true;
                newUnlocked = true;
                showToast(`🎯 Nouvelle compétence débloquée : ${comp.name} !`, 'success');
            }
        }
    });
    
    if (newUnlocked) {
        saveBingo();
        renderBingoGrid();
        checkBingoLines();
        updateBingoStats();
    }
}

// Parcourir tous les devoirs existants pour débloquer les compétences
function unlockAllFromExistingDevoirs() {
    const devoirs = document.querySelectorAll('.feed-card');
    devoirs.forEach(card => {
        const motsClesElement = card.querySelector('.tags-row');
        if (motsClesElement) {
            const tags = motsClesElement.querySelectorAll('.tag');
            tags.forEach(tag => {
                unlockCompetencesFromKeywords(tag.textContent);
            });
        }
    });
}

// Initialisation
document.addEventListener('DOMContentLoaded', () => {
    loadUnlockedLines();
    initBingo();
    unlockAllFromExistingDevoirs();
});

// Attacher l'événement au bouton reset
document.getElementById('resetBingoBtn')?.addEventListener('click', resetBingo);

// Toast helper
function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = 'bingo-toast';
    toast.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-info-circle'}"></i> ${message}`;
    toast.style.cssText = `
        position: fixed;
        bottom: 100px;
        right: 20px;
        background: ${type === 'success' ? '#10b981' : '#3b82f6'};
        color: white;
        padding: 12px 20px;
        border-radius: 12px;
        z-index: 10000;
        font-size: 14px;
        animation: slideInRight 0.3s ease;
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}
</script>


</body>
</html>