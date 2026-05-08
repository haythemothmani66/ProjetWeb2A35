<?php
ob_start();
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

require_once __DIR__ . '/../config/database.php';

// --- Protection : seul un utilisateur connecte peut soumettre/corriger des devoirs ---
if (empty($_SESSION['user_id'])) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non authentifie. Veuillez vous connecter.']);
    exit;
}

class Devoirs
{
    private $conn;

    public function __construct()
    {
        $this->conn = getDBConnection();
    }

    // ============================================================
    //   SOUMETTRE UN DEVOIR
    // ============================================================
    public function submit()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            exit;
        }

        // --- Récupération des données ---
        $titre                    = trim($_POST['titre'] ?? '');
        $description              = trim($_POST['description'] ?? '');
        $niveau_difficulte        = trim($_POST['niveau_difficulte'] ?? '');
        $date_soumission          = trim($_POST['date_soumission'] ?? '');
        $type_erreur_predominant  = trim($_POST['type_erreur_predominant'] ?? '');
        $temps_estime_resolution  = (int)($_POST['temps_estime_resolution'] ?? 0);
        $progression_eleve        = (int)($_POST['progression_eleve'] ?? 0);
        $mots_cles                = trim($_POST['mots_cles'] ?? '');
        $urgence                  = trim($_POST['urgence'] ?? '');

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
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = $_POST;
             echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
            exit;
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
            $_SESSION['success_message'] = "✓ Devoir '{$titre}' ajouté avec succès !";
            echo json_encode(['success' => true, 'message' => "✓ Devoir '{$titre}' ajouté avec succès !"]);
            exit;
        } else {
            $info = $stmt->errorInfo();
            echo json_encode(['success' => false, 'message' => 'Erreur base de données : ' . $info[2]]);
            exit;
        }
    }

    // ============================================================
    //   SOUMETTRE UNE CORRECTION
    // ============================================================
    public function correct()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            exit;
        }

        // --- Récupération des données ---
        $id_devoir                  = (int)($_POST['id_devoir'] ?? 0);
        $commentaire                = trim($_POST['commentaire'] ?? '');
        $date_correction            = trim($_POST['date_correction'] ?? '');
        $type_feedback              = trim($_POST['type_feedback'] ?? '');
        $note_estimee               = (float)($_POST['note_estimee'] ?? 0);
        $competences_evaluees       = trim($_POST['competences_evaluees'] ?? '');
        $nombre_iterations          = (int)($_POST['nombre_iterations'] ?? 1);
        $suggestions_personnalisees = trim($_POST['suggestions_personnalisees'] ?? '');
        $ressources_recommandees    = trim($_POST['ressources_recommandees'] ?? '');
        $rapidite_correction        = (int)($_POST['rapidite_correction'] ?? 0);
        $ton_feedback               = trim($_POST['ton_feedback'] ?? '');

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
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = $_POST;
            echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
            exit;
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
            $_SESSION['success_message'] = "✓ Correction ajoutée avec succès pour le devoir #{$id_devoir} !";
             echo json_encode(['success' => true, 'message' => "✓ Correction ajoutée avec succès pour le devoir #{$id_devoir} !"]);
            exit;
        } else {
            $info = $stmt->errorInfo();
            echo json_encode(['success' => false, 'message' => 'Erreur base de données : ' . $info[2]]);
            exit;
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
} else {
    echo json_encode(['success' => false, 'message' => 'Action non reconnue']);
    exit;
}
?>