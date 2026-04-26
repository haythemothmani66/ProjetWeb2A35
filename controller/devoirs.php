<?php
ob_start();
session_start(); // Pour les messages de succès/erreur
require_once __DIR__ . '/../model/devoirs_class.php';
require_once __DIR__ . '/../model/correction_class.php';

require_once __DIR__ . '/../config/database.php';

class Devoirs
{
    private $conn;

    public function __construct()
    {
        $this->conn = getDBConnection();
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
        $defaultUrl = '/eduleb/submit.html';
        $referer = $_SERVER['HTTP_REFERER'] ?? '';

        if (empty($referer)) {
            return $defaultUrl;
        }

        $parts = parse_url($referer);
        if ($parts === false) {
            return $defaultUrl;
        }

        $path = $parts['path'] ?? '';
        if ($path === '' || strpos($path, '/eduleb/') !== 0) {
            return $defaultUrl;
        }

        $query = isset($parts['query']) && $parts['query'] !== '' ? ('?' . $parts['query']) : '';
        return $path . $query;
    }

    private function respond(bool $success, string $message, int $statusCode = 200)
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

        $target = $this->getReturnUrl();
        $separator = strpos($target, '?') === false ? '?' : '&';
        $target .= $separator
            . 'flash_status=' . ($success ? 'success' : 'error')
            . '&flash_message=' . rawurlencode($message);

        if (!headers_sent()) {
            header('Location: ' . $target);
        }
        exit;
    }

    // ============================================================
    //   SOUMETTRE UN DEVOIR
    // ============================================================
    public function submit()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->respond(false, 'Méthode non autorisée', 405);
        }

        $devoirObj = new Devoir(
    null,
    trim($_POST['titre'] ?? ''),
    trim($_POST['description'] ?? ''),
    '', // fichier (sera rempli après upload)
    trim($_POST['date_soumission'] ?? ''),
    trim($_POST['niveau_difficulte'] ?? ''),
    trim($_POST['type_erreur_predominant'] ?? ''),
    (int)($_POST['temps_estime_resolution'] ?? 0),
    (int)($_POST['progression_eleve'] ?? 0),
    trim($_POST['mots_cles'] ?? ''),
    trim($_POST['urgence'] ?? ''),
    null // id_eleve (à gérer plus tard)
);

// récupérer les valeurs (pour ne rien casser)
$titre = $devoirObj->getTitre();
$description = $devoirObj->getDescription();
$date_soumission = $devoirObj->getDateSoumission();
$niveau_difficulte = $devoirObj->getNiveauDifficulte();
$type_erreur_predominant = $devoirObj->getTypeErreur();
$temps_estime_resolution = $devoirObj->getTempsEstime();
$progression_eleve = $devoirObj->getProgression();
$mots_cles = $devoirObj->getMotsCles();
$urgence = $devoirObj->getUrgence();

        // --- Validation serveur ---
        $errors = [];
        if (empty($titre))                   $errors[] = 'Le titre est requis.';
        if (empty($description))             $errors[] = 'La description est requise.';
        if (empty($niveau_difficulte))       $errors[] = 'Le niveau de difficulté est requis.';
        if (empty($date_soumission))         $errors[] = 'La date de soumission est requise.';
        if (empty($type_erreur_predominant)) $errors[] = "Le type d'erreur est requis.";
        if ($temps_estime_resolution < 1 || $temps_estime_resolution > 480) 
            $errors[] = 'Le temps estimé doit être entre 1 et 480 minutes.';
        if ($progression_eleve < 0 || $progression_eleve > 100) 
            $errors[] = 'La progression doit être entre 0 et 100%.';
        if (empty($mots_cles))               $errors[] = 'Les mots clés sont requis.';
        if (empty($urgence))                 $errors[] = "L'urgence est requise.";

        if (!empty($errors)) {
            $this->respond(false, implode(', ', $errors), 422);
        }

        // --- Upload fichier ---
        $fileName = '';
        if (isset($_FILES['file1']) && $_FILES['file1']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../uploads/devoirs/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $ext      = strtolower(pathinfo($_FILES['file1']['name'], PATHINFO_EXTENSION));
            $allowed  = ['py', 'js', 'java', 'cpp', 'c', 'png', 'jpg', 'jpeg'];

            if (in_array($ext, $allowed)) {
                $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES['file1']['name']));
                move_uploaded_file($_FILES['file1']['tmp_name'], $uploadDir . $fileName);
            }
        }

        // --- Insertion en base de données ---
        $stmt = $this->conn->prepare("
            INSERT INTO devoirs
                (titre, description, fichier, date_soumission,
                 niveau_difficulte, type_erreur_predominant,
                 temps_estime_resolution, progression_eleve,
                 mots_cles, urgence)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if ($stmt->execute([
            $titre, $description, $fileName, $date_soumission,
            $niveau_difficulte, $type_erreur_predominant,
            $temps_estime_resolution, $progression_eleve,
            $mots_cles, $urgence
        ])) {
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
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->respond(false, 'Méthode non autorisée', 405);
        }

        $correctionObj = new Correction(
    null,
    trim($_POST['commentaire'] ?? ''),
    '', // fichier corrigé
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
    null // id_encadrant
);

// récupérer les valeurs sans casser ton code
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

        // --- Validation serveur ---
        $errors = [];
        if ($id_devoir <= 0)            $errors[] = 'Veuillez sélectionner un devoir.';
        if (empty($commentaire))        $errors[] = 'Le commentaire est requis.';
        if (strlen($commentaire) < 10)  $errors[] = 'Le commentaire doit faire au moins 10 caractères.';
        if (empty($date_correction))    $errors[] = 'La date de correction est requise.';
        if (empty($type_feedback))      $errors[] = 'Le type de feedback est requis.';
        if ($note_estimee < 0 || $note_estimee > 20) 
            $errors[] = 'La note doit être entre 0 et 20.';
        if (empty($competences_evaluees)) $errors[] = 'Les compétences sont requises.';
        if ($nombre_iterations < 1 || $nombre_iterations > 10) 
            $errors[] = 'Le nombre d\'itérations doit être entre 1 et 10.';
        if ($rapidite_correction < 1 || $rapidite_correction > 480) 
            $errors[] = 'La rapidité doit être entre 1 et 480 minutes.';
        if (empty($ton_feedback))       $errors[] = 'Le ton du feedback est requis.';

        if (!empty($errors)) {
            $this->respond(false, implode(', ', $errors), 422);
        }

        // --- Upload fichier corrigé ---
        $fichier_corrige = '';
        if (isset($_FILES['file2']) && $_FILES['file2']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../uploads/corrections/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $ext     = strtolower(pathinfo($_FILES['file2']['name'], PATHINFO_EXTENSION));
            $allowed = ['py', 'js', 'java', 'cpp', 'c'];

            if (in_array($ext, $allowed)) {
                $fichier_corrige = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES['file2']['name']));
                move_uploaded_file($_FILES['file2']['tmp_name'], $uploadDir . $fichier_corrige);
            }
        }

        // --- Insertion en base de données ---
        $stmt = $this->conn->prepare("
            INSERT INTO correction
                (commentaire, fichier_corrige, date_correction, type_feedback,
                 note_estimee, competences_evaluees, nombre_iterations,
                 suggestions_personnalisees, ressources_recommandees,
                 rapidite_correction, ton_feedback, id_devoir)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if ($stmt->execute([
            $commentaire, $fichier_corrige, $date_correction, $type_feedback,
            $note_estimee, $competences_evaluees, $nombre_iterations,
            $suggestions_personnalisees, $ressources_recommandees,
            $rapidite_correction, $ton_feedback, $id_devoir
        ])) {
            $this->respond(true, "Correction ajoutée avec succès pour le devoir #{$id_devoir} !");
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
    $id_devoir = (int)($_GET['id'] ?? 0);

    if ($id_devoir <= 0) {
        echo "ID invalide";
        return;
    }

    // Supprimer d'abord les corrections liées
    $stmtCorr = $this->conn->prepare("DELETE FROM correction WHERE id_devoir = ?");
    $stmtCorr->execute([$id_devoir]);

    // Puis supprimer le devoir
    $stmt = $this->conn->prepare("DELETE FROM devoirs WHERE id_devoir = ?");
    
    if ($stmt->execute([$id_devoir])) {
        echo "succès";
    } else {
        echo "erreur";
    }
}

// ============================================================
//   SUPPRIMER UNE CORRECTION
// ============================================================
public function deleteCorrection()
{
    $id_correction = (int)($_GET['id'] ?? 0);

    if ($id_correction <= 0) {
        echo "ID invalide";
        return;
    }

    $stmt = $this->conn->prepare("DELETE FROM correction WHERE id_correction = ?");
    
    if ($stmt->execute([$id_correction])) {
        echo "succès";
    } else {
        echo "erreur";
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
        echo json_encode(['success' => true, 'data' => $devoir]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Devoir non trouvé']);
    }
}

// ============================================================
//   MODIFIER UN DEVOIR
// ============================================================
public function updateDevoir()
{
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
}

// --- Appel selon l'action ---
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
}else {
    echo "Action non reconnue";
}
?>