<?php
ob_start();
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../model/devoirs_class.php';
require_once __DIR__ . '/../model/correction_class.php';
require_once __DIR__ . '/../config/database.php';

// Charger GROQ_API_KEY depuis .env (pas hardcode)
if (!defined('GROQ_API_KEY')) {
    $envFile = __DIR__ . '/../.env';
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

// Protection auth : seuls les utilisateurs connectes peuvent acceder aux endpoints
// (verification fine du role faite dans chaque action)
if (empty($_SESSION['user_id'])) {
    if (!headers_sent()) {
        header('Content-Type: application/json');
        http_response_code(401);
    }
    echo json_encode(['success' => false, 'message' => 'Non authentifie. Veuillez vous connecter.']);
    exit;
}

class Devoirs
{
    private $conn;
    private $userId;
    private $userRole;

    public function __construct()
    {
        $this->conn = Config::getConnexion();
        $this->userId = (int)($_SESSION['user_id'] ?? 0);
        $this->userRole = $_SESSION['user_role'] ?? '';
    }

    /**
     * Garde-fou : seul un role autorise peut executer l'action
     */
    private function requireRole(array $allowedRoles): void
    {
        if (!in_array($this->userRole, $allowedRoles, true)) {
            if (!headers_sent()) {
                header('Content-Type: application/json');
                http_response_code(403);
            }
            echo json_encode([
                'success' => false,
                'message' => 'Acces refuse. Role requis : ' . implode(' ou ', $allowedRoles)
            ]);
            exit;
        }
    }

    private function isAjaxRequest()
    {
        $requestedWith = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
        if ($requestedWith === 'xmlhttprequest') {
            return true;
        }

        $accept = strtolower($_SERVER['HTTP_ACCEPT'] ?? '');
        return strpos($accept, 'application/json') !== false;
    }

    private function getReturnUrl()
    {
        $defaultUrl = '/gestion_users/view/frontoffice/submit.php';
        $referer = $_SERVER['HTTP_REFERER'] ?? '';

        if (empty($referer)) {
            return $defaultUrl;
        }

        $parts = parse_url($referer);
        if ($parts === false) {
            return $defaultUrl;
        }

        $path = $parts['path'] ?? '';
        if ($path === '' || strpos($path, '/gestion_users/') !== 0) {
            return $defaultUrl;
        }

        $query = isset($parts['query']) && $parts['query'] !== '' ? ('?' . $parts['query']) : '';
        return $path . $query;
    }

    private function respond(bool $success, string $message, int $statusCode = 200, string $redirectUrl = null)
    {
        if ($this->isAjaxRequest()) {
            if (!headers_sent()) {
                header('Content-Type: application/json');
                http_response_code($statusCode);
            }

            echo json_encode([
                'success' => $success,
                'message' => $message,
            ]);
            exit;
        }

        if ($success) {
            $_SESSION['success_message'] = $message;
            unset($_SESSION['form_errors'], $_SESSION['form_data']);
        } else {
            $_SESSION['form_errors'] = [$message];
            $_SESSION['form_data'] = $_POST;
        }

        $target = $redirectUrl ?? $this->getReturnUrl();
        if (strpos($target, '?') === false) {
            $target .= '?flash_status=' . ($success ? 'success' : 'error') . '&flash_message=' . rawurlencode($message);
        } else {
            $target .= '&flash_status=' . ($success ? 'success' : 'error') . '&flash_message=' . rawurlencode($message);
        }

        if (!headers_sent()) {
            header('Location: ' . $target);
        }
        exit;
    }

    // ============================================================
// CHATBOT ÉDUCATIF AVEC GROQ
// ============================================================
    // [LEGACY chatbot() retire - remplace par api/groq_chatbot.php (Assistant EduMatch unifie)]

    // ============================================================
    //   SOUMETTRE UN DEVOIR
    // ============================================================
    public function submit()
    {
        // Etudiants soumettent leurs devoirs, admins peuvent ajouter depuis le backoffice
        $this->requireRole(['etudiant', 'admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->respond(false, 'Méthode non autorisée', 405);
        }

        $devoirObj = new Devoir(
            null,
            trim($_POST['titre'] ?? ''),
            trim($_POST['description'] ?? ''),
            '',
            trim($_POST['date_soumission'] ?? ''),
            trim($_POST['niveau_difficulte'] ?? ''),
            trim($_POST['type_erreur_predominant'] ?? ''),
            (int)($_POST['temps_estime_resolution'] ?? 0),
            (int)($_POST['progression_eleve'] ?? 0),
            trim($_POST['mots_cles'] ?? ''),
            trim($_POST['urgence'] ?? ''),
            null
        );

        $titre = $devoirObj->getTitre();
        $description = $devoirObj->getDescription();
        $date_soumission = $devoirObj->getDateSoumission();
        $niveau_difficulte = $devoirObj->getNiveauDifficulte();
        $type_erreur_predominant = $devoirObj->getTypeErreur();
        $temps_estime_resolution = $devoirObj->getTempsEstime();
        $progression_eleve = $devoirObj->getProgression();
        $mots_cles = $devoirObj->getMotsCles();
        $urgence = $devoirObj->getUrgence();

        // Validation serveur
        $errors = [];
        if (empty($titre)) $errors[] = 'Le titre est requis.';
        if (empty($description)) $errors[] = 'La description est requise.';
        if (empty($niveau_difficulte)) $errors[] = 'Le niveau de difficulté est requis.';
        if (empty($date_soumission)) $errors[] = 'La date de soumission est requise.';
        if (empty($type_erreur_predominant)) $errors[] = "Le type d'erreur est requis.";
        if ($temps_estime_resolution < 1 || $temps_estime_resolution > 480) 
            $errors[] = 'Le temps estimé doit être entre 1 et 480 minutes.';
        if ($progression_eleve < 0 || $progression_eleve > 100) 
            $errors[] = 'La progression doit être entre 0 et 100%.';
        if (empty($mots_cles)) $errors[] = 'Les mots clés sont requis.';
        if (empty($urgence)) $errors[] = "L'urgence est requise.";

        if (!empty($errors)) {
            $this->respond(false, implode(', ', $errors), 422);
        }

        // Upload fichier
        $fileName = '';
        if (isset($_FILES['file1']) && $_FILES['file1']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../uploads/devoirs/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $ext = strtolower(pathinfo($_FILES['file1']['name'], PATHINFO_EXTENSION));
            $allowed = ['py', 'js', 'java', 'cpp', 'c', 'png', 'jpg', 'jpeg'];

            if (in_array($ext, $allowed)) {
                $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES['file1']['name']));
                move_uploaded_file($_FILES['file1']['tmp_name'], $uploadDir . $fileName);
            }
        }

        // Insertion (avec id_eleve = utilisateur connecte)
        $stmt = $this->conn->prepare("
            INSERT INTO devoirs
                (titre, description, fichier, date_soumission,
                 niveau_difficulte, type_erreur_predominant,
                 temps_estime_resolution, progression_eleve,
                 mots_cles, urgence, id_eleve)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if ($stmt->execute([
            $titre, $description, $fileName, $date_soumission,
            $niveau_difficulte, $type_erreur_predominant,
            $temps_estime_resolution, $progression_eleve,
            $mots_cles, $urgence, $this->userId
        ])) {
            $devoirId = $this->conn->lastInsertId();
            $this->updateSentiment($devoirId, $description);
            $this->respond(true, "Devoir '{$titre}' ajouté avec succès !");
        } else {
            $info = $stmt->errorInfo();
            $this->respond(false, "Erreur base de données : " . $info[2], 500);
        }
    }

    // ============================================================
    //   SOUMETTRE UNE CORRECTION
    // ============================================================
    public function correct()
    {
        // Seuls les encadrants ou admins peuvent corriger
        $this->requireRole(['encadrant', 'admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->respond(false, 'Méthode non autorisée', 405);
        }

        $correctionObj = new Correction(
            null,
            trim($_POST['commentaire'] ?? ''),
            '',
            trim($_POST['date_correction'] ?? ''),
            trim($_POST['type_feedback'] ?? ''),
            (float)($_POST['note_estimee'] ?? 0),
            trim($_POST['competences_evaluees'] ?? ''),
            (int)($_POST['nombre_iterations'] ?? 1),
            trim($_POST['suggestions_personnalisees'] ?? ''),
            trim($_POST['ressources_recommandees'] ?? ''),
            (int)($_POST['rapidite_correction'] ?? 0),
            trim($_POST['ton_feedback'] ?? ''),
            (int)($_POST['id_devoir'] ?? 0),
            null
        );

        $id_devoir = $correctionObj->getIdDevoir();
        $commentaire = $correctionObj->getCommentaire();
        $date_correction = $correctionObj->getDateCorrection();
        $type_feedback = $correctionObj->getTypeFeedback();
        $note_estimee = $correctionObj->getNote();
        $competences_evaluees = $correctionObj->getCompetences();
        $nombre_iterations = $correctionObj->getIterations();
        $suggestions_personnalisees = $correctionObj->getSuggestions();
        $ressources_recommandees = $correctionObj->getRessources();
        $rapidite_correction = $correctionObj->getRapidite();
        $ton_feedback = $correctionObj->getTon();

        // Validation
        $errors = [];
        if ($id_devoir <= 0) $errors[] = 'Veuillez sélectionner un devoir.';
        if (empty($commentaire)) $errors[] = 'Le commentaire est requis.';
        if (strlen($commentaire) < 10) $errors[] = 'Le commentaire doit faire au moins 10 caractères.';
        if (empty($date_correction)) $errors[] = 'La date de correction est requise.';
        if (empty($type_feedback)) $errors[] = 'Le type de feedback est requis.';
        if ($note_estimee < 0 || $note_estimee > 20) $errors[] = 'La note doit être entre 0 et 20.';
        if (empty($competences_evaluees)) $errors[] = 'Les compétences sont requises.';
        if ($nombre_iterations < 1 || $nombre_iterations > 10) $errors[] = 'Le nombre d\'itérations doit être entre 1 et 10.';
        if ($rapidite_correction < 1 || $rapidite_correction > 480) $errors[] = 'La rapidité doit être entre 1 et 480 minutes.';
        if (empty($ton_feedback)) $errors[] = 'Le ton du feedback est requis.';

        if (!empty($errors)) {
            $this->respond(false, implode(', ', $errors), 422);
        }

        // Upload fichier corrigé
        $fichier_corrige = '';
        if (isset($_FILES['file2']) && $_FILES['file2']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../uploads/correction/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            
            $ext = strtolower(pathinfo($_FILES['file2']['name'], PATHINFO_EXTENSION));
            $allowed = ['py', 'js', 'java', 'cpp', 'c', 'png', 'jpg', 'jpeg', 'pdf'];
            
            if (in_array($ext, $allowed)) {
                $fichier_corrige = 'correction_' . time() . '_' . uniqid() . '.' . $ext;
                $destination = $uploadDir . $fichier_corrige;
                move_uploaded_file($_FILES['file2']['tmp_name'], $destination);
            } else {
                $this->respond(false, "Type de fichier non autorisé. Types acceptés: " . implode(', ', $allowed), 422, '/gestion_users/view/frontoffice/submit.php');
                return;
            }
        } else {
            $this->respond(false, "Veuillez sélectionner un fichier corrigé", 422, '/gestion_users/view/frontoffice/submit.php');
            return;
        }

        // Insertion (avec id_encadrant = utilisateur connecte)
        $stmt = $this->conn->prepare("
            INSERT INTO correction
                (commentaire, fichier_corrige, date_correction, type_feedback,
                 note_estimee, competences_evaluees, nombre_iterations,
                 suggestions_personnalisees, ressources_recommandees,
                 rapidite_correction, ton_feedback, id_devoir, id_encadrant)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if ($stmt->execute([
            $commentaire, $fichier_corrige, $date_correction, $type_feedback,
            $note_estimee, $competences_evaluees, $nombre_iterations,
            $suggestions_personnalisees, $ressources_recommandees,
            $rapidite_correction, $ton_feedback, $id_devoir, $this->userId
        ])) {
            $this->respond(true, "Correction ajoutée avec succès !", 200, '/gestion_users/view/frontoffice/feed.php?success=correction');
        } else {
            $info = $stmt->errorInfo();
            $this->respond(false, "Erreur base de données : " . $info[2], 500);
        }
    }

    // ============================================================
//   SUPPRIMER UN DEVOIR
// ============================================================
public function delete()
{
    // Etudiant peut supprimer ses propres devoirs, admin peut tout supprimer
    $this->requireRole(['etudiant', 'admin']);

    $id_devoir = (int)($_GET['id'] ?? 0);
    
    if ($id_devoir <= 0) {
        if ($this->isAjaxRequest()) {
            echo json_encode(['success' => false, 'message' => 'ID invalide']);
        } else {
            echo "ID invalide";
        }
        return;
    }

    // Vérifier si le devoir existe
    $stmt = $this->conn->prepare("SELECT id_devoir FROM devoirs WHERE id_devoir = ?");
    $stmt->execute([$id_devoir]);
    $devoir = $stmt->fetch();
    
    if (!$devoir) {
        if ($this->isAjaxRequest()) {
            echo json_encode(['success' => false, 'message' => 'Devoir non trouvé']);
        } else {
            echo "Devoir non trouvé";
        }
        return;
    }

    // Supprimer d'abord les corrections liées
    $stmtCorr = $this->conn->prepare("DELETE FROM correction WHERE id_devoir = ?");
    $stmtCorr->execute([$id_devoir]);

    // Puis supprimer le devoir
    $stmt = $this->conn->prepare("DELETE FROM devoirs WHERE id_devoir = ?");
    
    if ($stmt->execute([$id_devoir])) {
        if ($this->isAjaxRequest()) {
            echo json_encode(['success' => true, 'message' => 'Devoir et ses corrections supprimés avec succès']);
        } else {
            echo "succès";
        }
    } else {
        if ($this->isAjaxRequest()) {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression']);
        } else {
            echo "erreur";
        }
    }
}

    // ============================================================
//   SUPPRIMER UNE CORRECTION
// ============================================================
public function deleteCorrection()
{
    // Encadrant peut supprimer ses corrections, admin peut tout supprimer
    $this->requireRole(['encadrant', 'admin']);

    $id_correction = (int)($_GET['id'] ?? 0);
    
    if ($id_correction <= 0) {
        if ($this->isAjaxRequest()) {
            echo json_encode(['success' => false, 'message' => 'ID invalide']);
        } else {
            echo "ID invalide";
        }
        return;
    }

    // Vérifier si la correction existe
    $stmt = $this->conn->prepare("SELECT id_correction FROM correction WHERE id_correction = ?");
    $stmt->execute([$id_correction]);
    $correction = $stmt->fetch();
    
    if (!$correction) {
        if ($this->isAjaxRequest()) {
            echo json_encode(['success' => false, 'message' => 'Correction non trouvée']);
        } else {
            echo "Correction non trouvée";
        }
        return;
    }

    $stmt = $this->conn->prepare("DELETE FROM correction WHERE id_correction = ?");
    
    if ($stmt->execute([$id_correction])) {
        if ($this->isAjaxRequest()) {
            echo json_encode(['success' => true, 'message' => 'Correction supprimée avec succès']);
        } else {
            echo "succès";
        }
    } else {
        if ($this->isAjaxRequest()) {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression']);
        } else {
            echo "erreur";
        }
    }
}
    // ============================================================
    //   RÉCUPÉRER UN DEVOIR POUR MODIFICATION
    // ============================================================
    public function getDevoir()
    {
        header('Content-Type: application/json');
        
        $id_devoir = (int)($_GET['id'] ?? 0);
        if ($id_devoir <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID invalide']);
            return;
        }
        
        $stmt = $this->conn->prepare("SELECT * FROM devoirs WHERE id_devoir = ?");
        $stmt->execute([$id_devoir]);
        $devoir = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($devoir) {
            echo json_encode(['success' => true, 'devoir' => $devoir]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Devoir non trouvé']);
        }
    }

    // ============================================================
    //   MODIFIER UN DEVOIR
    // ============================================================
    public function updateDevoir()
    {
        $this->requireRole(['etudiant', 'admin']);
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }
        
        $id_devoir = (int)($_POST['id_devoir'] ?? 0);
        $titre = trim($_POST['titre'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $niveau_difficulte = trim($_POST['niveau_difficulte'] ?? '');
        $date_soumission = trim($_POST['date_soumission'] ?? '');
        $type_erreur_predominant = trim($_POST['type_erreur_predominant'] ?? '');
        $temps_estime_resolution = (int)($_POST['temps_estime_resolution'] ?? 0);
        $progression_eleve = (int)($_POST['progression_eleve'] ?? 0);
        $mots_cles = trim($_POST['mots_cles'] ?? '');
        $urgence = trim($_POST['urgence'] ?? '');
        
        $stmt = $this->conn->prepare("
            UPDATE devoirs SET 
                titre = ?, description = ?, niveau_difficulte = ?,
                date_soumission = ?, type_erreur_predominant = ?,
                temps_estime_resolution = ?, progression_eleve = ?,
                mots_cles = ?, urgence = ?
            WHERE id_devoir = ?
        ");
        
        if ($stmt->execute([$titre, $description, $niveau_difficulte, $date_soumission,
            $type_erreur_predominant, $temps_estime_resolution, $progression_eleve,
            $mots_cles, $urgence, $id_devoir])) {
            
            // Mettre à jour le sentiment après modification
            $this->updateSentiment($id_devoir, $description);
            
            echo json_encode(['success' => true, 'message' => 'Devoir modifié avec succès']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la modification']);
        }
    }

    // ============================================================
    //   RÉCUPÉRER UNE CORRECTION POUR MODIFICATION
    // ============================================================
    public function getCorrection()
    {
        header('Content-Type: application/json');
        
        $id_correction = (int)($_GET['id'] ?? 0);
        if ($id_correction <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID invalide']);
            return;
        }
        
        $stmt = $this->conn->prepare("SELECT * FROM correction WHERE id_correction = ?");
        $stmt->execute([$id_correction]);
        $correction = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($correction) {
            echo json_encode(['success' => true, 'data' => $correction]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Correction non trouvée']);
        }
    }

    // ============================================================
    //   MODIFIER UNE CORRECTION
    // ============================================================
    public function updateCorrection()
    {
        $this->requireRole(['encadrant', 'admin']);
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }
        
        $id_correction = (int)($_POST['id_correction'] ?? 0);
        $commentaire = trim($_POST['commentaire'] ?? '');
        $date_correction = trim($_POST['date_correction'] ?? '');
        $type_feedback = trim($_POST['type_feedback'] ?? '');
        $note_estimee = (float)($_POST['note_estimee'] ?? 0);
        $competences_evaluees = trim($_POST['competences_evaluees'] ?? '');
        $nombre_iterations = (int)($_POST['nombre_iterations'] ?? 1);
        $suggestions_personnalisees = trim($_POST['suggestions_personnalisees'] ?? '');
        $ressources_recommandees = trim($_POST['ressources_recommandees'] ?? '');
        $rapidite_correction = (int)($_POST['rapidite_correction'] ?? 0);
        $ton_feedback = trim($_POST['ton_feedback'] ?? '');
        
        $stmt = $this->conn->prepare("
            UPDATE correction SET 
                commentaire = ?, date_correction = ?, type_feedback = ?,
                note_estimee = ?, competences_evaluees = ?,
                nombre_iterations = ?, suggestions_personnalisees = ?,
                ressources_recommandees = ?, rapidite_correction = ?,
                ton_feedback = ?
            WHERE id_correction = ?
        ");
        
        if ($stmt->execute([$commentaire, $date_correction, $type_feedback,
            $note_estimee, $competences_evaluees, $nombre_iterations,
            $suggestions_personnalisees, $ressources_recommandees,
            $rapidite_correction, $ton_feedback, $id_correction])) {
            echo json_encode(['success' => true, 'message' => 'Correction modifiée avec succès']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la modification']);
        }
    }

    // ============================================================
// ANALYSE DE SENTIMENT 
// ============================================================
public function analyzeSentiment($commentaire) {
    if (empty($commentaire)) {
        return ['sentiment' => 'neutre', 'score' => 0.5, 'urgence' => false];
    }
    
    $commentaireLower = strtolower($commentaire);
    
    // Dictionnaires de mots-clés
    $motsPositifs = ['bien', 'excellent', 'super', 'génial', 'parfait', 'content', 'heureux', 'facile', 'réussi', 'merci', 'bravo', 'félicitations'];
    $motsNegatifs = ['difficile', 'compliqué', 'problème', 'erreur', 'faux', 'mal'];
    $motsStress = ['stress', 'urgence', 'examen', 'important', 'délai', 'pressé', 'note', 'réussir', 'deadline'];
    $motsConfusion = ['comprends pas', 'sais pas', 'ou est ce que', 'comment faire', 'expliquez', 'je ne comprend', 'pas clair', 'perdu'];
    $motsFrustration = ['frustré', 'énervé', 'fatiguant', 'trop long', 'marche pas', 'bloqué', 'aide'];
    
    // Compter les occurrences
    $comptePositif = 0;
    $compteNegatif = 0;
    $compteStress = 0;
    $compteConfusion = 0;
    $compteFrustration = 0;
    
    foreach ($motsPositifs as $mot) {
        if (strpos($commentaireLower, $mot) !== false) $comptePositif++;
    }
    foreach ($motsNegatifs as $mot) {
        if (strpos($commentaireLower, $mot) !== false) $compteNegatif++;
    }
    foreach ($motsStress as $mot) {
        if (strpos($commentaireLower, $mot) !== false) $compteStress++;
    }
    foreach ($motsConfusion as $mot) {
        if (strpos($commentaireLower, $mot) !== false) $compteConfusion++;
    }
    foreach ($motsFrustration as $mot) {
        if (strpos($commentaireLower, $mot) !== false) $compteFrustration++;
    }
    
    // Déterminer le sentiment (ordre de priorité)
    $sentiment = 'neutre';
    $urgence = false;
    
    // Priorité 1 : Stress (le plus urgent)
    if ($compteStress > 0) {
        $sentiment = 'stress';
        $urgence = true;
    } 
    // Priorité 2 : Frustration
    elseif ($compteFrustration > 0) {
        $sentiment = 'frustration';
        $urgence = true;
    }
    // Priorité 3 : Confusion
    elseif ($compteConfusion > 0) {
        $sentiment = 'confusion';
    }
    // Priorité 4 : Négatif (si plus de mots négatifs que positifs)
    elseif ($compteNegatif > $comptePositif) {
        $sentiment = 'negatif';
    }
    // Priorité 5 : Positif
    elseif ($comptePositif > $compteNegatif && $comptePositif > 0) {
        $sentiment = 'positif';
    }
    
    // Calcul du score
    $score = 0.5;
    $score += $comptePositif * 0.1;
    $score -= $compteNegatif * 0.1;
    $score = max(0, min(1, $score));
    
    // Log pour debug
    error_log("=== ANALYSE SENTIMENT ===");
    error_log("Texte: " . substr($commentaire, 0, 100));
    error_log("Positif:$comptePositif Negatif:$compteNegatif Stress:$compteStress Confusion:$compteConfusion Frustration:$compteFrustration");
    error_log("Sentiment: $sentiment, Score: $score, Urgence: " . ($urgence ? 'OUI' : 'NON'));
    
    return [
        'sentiment' => $sentiment,
        'score' => round($score, 2),
        'urgence' => $urgence
    ];
}

    public function updateSentiment($id_devoir, $commentaire) {
        $analysis = $this->analyzeSentiment($commentaire);
        
        $stmt = $this->conn->prepare("
            UPDATE devoirs 
            SET sentiment = ?, sentiment_score = ?, alerte_urgence = ?, date_analyse = NOW()
            WHERE id_devoir = ?
        ");
        
        $stmt->execute([
            $analysis['sentiment'],
            $analysis['score'],
            $analysis['urgence'] ? 1 : 0,
            $id_devoir
        ]);
        
        return $analysis;
    }

    public function getSentimentStats()
    {
        header('Content-Type: application/json');
        
        $stmt = $this->conn->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN sentiment = 'positif' THEN 1 ELSE 0 END) as positif,
                SUM(CASE WHEN sentiment = 'negatif' THEN 1 ELSE 0 END) as negatif,
                SUM(CASE WHEN sentiment = 'neutre' THEN 1 ELSE 0 END) as neutre,
                SUM(CASE WHEN sentiment = 'frustration' THEN 1 ELSE 0 END) as frustration,
                SUM(CASE WHEN sentiment = 'confusion' THEN 1 ELSE 0 END) as confusion,
                SUM(CASE WHEN sentiment = 'stress' THEN 1 ELSE 0 END) as stress,
                SUM(CASE WHEN alerte_urgence = 1 THEN 1 ELSE 0 END) as urgences
            FROM devoirs
        ");
        
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'stats' => $stats
        ]);
    }

    // ============================================================
    //   LISTER TOUS LES DEVOIRS (pour backoffice)
    // ============================================================
    public function listDevoirs()
    {
        header('Content-Type: application/json');
        $stmt = $this->conn->query("SELECT * FROM devoirs ORDER BY id_devoir DESC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $rows]);
    }

    // ============================================================
    //   LISTER TOUTES LES CORRECTIONS (pour backoffice)
    // ============================================================
    public function listCorrections()
    {
        header('Content-Type: application/json');
        $stmt = $this->conn->query("
            SELECT c.*, d.titre AS devoir_titre
            FROM correction c
            LEFT JOIN devoirs d ON d.id_devoir = c.id_devoir
            ORDER BY c.id_correction DESC
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $rows]);
    }
}

// ============================================================
// APPEL SELON L'ACTION
// ============================================================
$devoir = new Devoirs();
$action = $_GET['action'] ?? '';

if ($action === 'submit') {
    $devoir->submit();
} elseif ($action === 'correct') {
    $devoir->correct();
} elseif ($action === 'delete') {
    $devoir->delete();
} elseif ($action === 'deletecorrection') {
    $devoir->deleteCorrection();
} elseif ($action === 'getdevoir') {
    $devoir->getDevoir();
} elseif ($action === 'updatedevoir') {
    $devoir->updateDevoir();
} elseif ($action === 'getcorrection') {
    $devoir->getCorrection();
} elseif ($action === 'updatecorrection') {
    $devoir->updateCorrection();
} elseif ($action === 'sentimentStats') {
    $devoir->getSentimentStats();
} elseif ($action === 'listdevoirs') {
    $devoir->listDevoirs();
} elseif ($action === 'listcorrections') {
    $devoir->listCorrections();
} elseif ($action === 'chat') {
    // Legacy : action 'chat' redirige vers le nouveau chatbot unifie
    header('Content-Type: application/json');
    echo json_encode([
        'reply' => "Le chatbot a ete deplace. Utilisez l'assistant EduMatch sur la page d'accueil.",
        'redirect' => '/gestion_users/api/groq_chatbot.php'
    ]);
} else {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Action non reconnue : ' . htmlspecialchars($action)]);
}
?>