<?php
ob_start();
session_start(); // Pour les messages de succès/erreur

require_once __DIR__ . '/../config/database.php';

class Devoirs
{
    private $conn;

    public function __construct()
    {
        $this->conn = getDBConnection();
        $this->ensureTables();
    }

    private function ensureTables()
    {
        $this->conn->exec("\n            CREATE TABLE IF NOT EXISTS devoirs (\n                id_devoir INT AUTO_INCREMENT PRIMARY KEY,\n                titre VARCHAR(255) NOT NULL,\n                description TEXT NOT NULL,\n                fichier VARCHAR(255) DEFAULT NULL,\n                date_soumission DATE NOT NULL,\n                niveau_difficulte VARCHAR(50) NOT NULL,\n                type_erreur_predominant VARCHAR(50) NOT NULL,\n                temps_estime_resolution INT NOT NULL,\n                progression_eleve INT NOT NULL,\n                mots_cles TEXT NOT NULL,\n                urgence VARCHAR(50) NOT NULL\n            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4\n        ");

        $this->conn->exec("\n            CREATE TABLE IF NOT EXISTS correction (\n                id_correction INT AUTO_INCREMENT PRIMARY KEY,\n                commentaire TEXT NOT NULL,\n                fichier_corrige VARCHAR(255) DEFAULT NULL,\n                date_correction DATE NOT NULL,\n                type_feedback VARCHAR(50) NOT NULL,\n                note_estimee DECIMAL(4,2) NOT NULL,\n                competences_evaluees TEXT NOT NULL,\n                nombre_iterations INT NOT NULL,\n                suggestions_personnalisees TEXT NULL,\n                ressources_recommandees TEXT NULL,\n                rapidite_correction INT NOT NULL,\n                ton_feedback VARCHAR(50) NOT NULL,\n                id_devoir INT NOT NULL,\n                CONSTRAINT fk_correction_devoir\n                  FOREIGN KEY (id_devoir) REFERENCES devoirs(id_devoir)\n                  ON DELETE CASCADE\n            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4\n        ");
    }

    private function emitJson(array $payload)
    {
        if (ob_get_level() > 0 && ob_get_length() > 0) {
            ob_clean();
        }

        if (!headers_sent()) {
            header('Content-Type: application/json');
        }

        echo json_encode($payload);
    }

    // ============================================================
    //   SOUMETTRE UN DEVOIR
    // ============================================================
    public function submit()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->emitJson(['success' => false, 'message' => 'Méthode non autorisée']);
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
            $this->emitJson(['success' => false, 'message' => implode(', ', $errors)]);
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
            echo "succès|Devoir '{$titre}' ajouté avec succès !";
           return;
        } else {
            $info = $stmt->errorInfo();
            echo "erreur|Erreur base de données : " . $info[2];
    return;
        }
    }


    // ============================================================
    //   SOUMETTRE UNE CORRECTION
    // ============================================================
    public function correct()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->emitJson(['success' => false, 'message' => 'Méthode non autorisée']);
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
            $this->emitJson(['success' => false, 'message' => implode(', ', $errors)]);
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
            echo "succès|Correction ajoutée avec succès pour le devoir #{$id_devoir} !";
        return;
        } else {
            $info = $stmt->errorInfo();
            echo "erreur|Erreur base de données : " . $info[2];
            return;
        }
    }

    // ============================================================
    //   LISTER LES DEVOIRS (JSON)
    // ============================================================
    public function listDevoirs()
    {
        try {
            $stmt = $this->conn->query("\n                SELECT id_devoir, titre, description, fichier, date_soumission,\n                       niveau_difficulte, type_erreur_predominant,\n                       temps_estime_resolution, progression_eleve,\n                       mots_cles, urgence\n                FROM devoirs\n                ORDER BY id_devoir DESC\n            ");

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->emitJson(['success' => true, 'data' => $rows]);
        } catch (PDOException $e) {
            $this->emitJson(['success' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    // ============================================================
    //   LISTER LES CORRECTIONS (JSON)
    // ============================================================
    public function listCorrections()
    {
        try {
            $stmt = $this->conn->query("\n                SELECT c.id_correction, c.commentaire, c.fichier_corrige, c.date_correction,\n                       c.type_feedback, c.note_estimee, c.competences_evaluees,\n                       c.nombre_iterations, c.suggestions_personnalisees,\n                       c.ressources_recommandees, c.rapidite_correction, c.ton_feedback,\n                       c.id_devoir, d.titre AS devoir_titre\n                FROM correction c\n                LEFT JOIN devoirs d ON d.id_devoir = c.id_devoir\n                ORDER BY c.id_correction DESC\n            ");

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->emitJson(['success' => true, 'data' => $rows]);
        } catch (PDOException $e) {
            $this->emitJson(['success' => false, 'message' => $e->getMessage(), 'data' => []]);
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
    $id_devoir = (int)($_GET['id'] ?? 0);
    
    if ($id_devoir <= 0) {
        $this->emitJson(['success' => false, 'message' => 'ID invalide']);
        return;
    }
    
    $stmt = $this->conn->prepare("SELECT * FROM devoirs WHERE id_devoir = ?");
    $stmt->execute([$id_devoir]);
    $devoir = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($devoir) {
        $this->emitJson(['success' => true, 'data' => $devoir]);
    } else {
        $this->emitJson(['success' => false, 'message' => 'Devoir non trouvé']);
    }
}

// ============================================================
//   MODIFIER UN DEVOIR
// ============================================================
public function updateDevoir()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $this->emitJson(['success' => false, 'message' => 'Méthode non autorisée']);
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
        $this->emitJson(['success' => true, 'message' => 'Devoir modifié avec succès']);
    } else {
        $this->emitJson(['success' => false, 'message' => 'Erreur lors de la modification']);
    }
}

// ============================================================
//   RÉCUPÉRER UNE CORRECTION POUR MODIFICATION
// ============================================================
public function getCorrection()
{
    $id_correction = (int)($_GET['id'] ?? 0);
    
    if ($id_correction <= 0) {
        $this->emitJson(['success' => false, 'message' => 'ID invalide']);
        return;
    }
    
    $stmt = $this->conn->prepare("SELECT * FROM correction WHERE id_correction = ?");
    $stmt->execute([$id_correction]);
    $correction = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($correction) {
        $this->emitJson(['success' => true, 'data' => $correction]);
    } else {
        $this->emitJson(['success' => false, 'message' => 'Correction non trouvée']);
    }
}

// ============================================================
//   MODIFIER UNE CORRECTION
// ============================================================
public function updateCorrection()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $this->emitJson(['success' => false, 'message' => 'Méthode non autorisée']);
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
        $this->emitJson(['success' => true, 'message' => 'Correction modifiée avec succès']);
    } else {
        $this->emitJson(['success' => false, 'message' => 'Erreur lors de la modification']);
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
} elseif ($action === 'listdevoirs') {
    $devoir->listDevoirs();
} elseif ($action === 'listcorrections') {
    $devoir->listCorrections();
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