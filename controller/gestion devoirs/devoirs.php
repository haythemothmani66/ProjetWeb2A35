<?php
require_once __DIR__ . '/../config/database.php';

class Devoirs
{
    private $conn;

    public function __construct()
    {
        $this->conn = getDBConnection();
    }

    public function submit()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titre = $_POST['titre'] ?? '';
            $description = $_POST['description'] ?? '';
            $niveau = $_POST['niveau_difficulte'] ?? '';
            $datesoumission = $_POST['date_soumission'] ?? '';

            // Handle file upload
            $fileName = '';
            if (isset($_FILES['file1']) && $_FILES['file1']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $fileName = basename($_FILES['file1']['name']);
                $filePath = $uploadDir . $fileName;
                move_uploaded_file($_FILES['file1']['tmp_name'], $filePath);
            }

            // Insert into database
            $stmt = $this->conn->prepare("INSERT INTO devoirs (titre, description, niveau_difficulte, date_soumission, fichier) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$titre, $description, $niveau, $datesoumission, $fileName]);

            // Redirect to feed
            header("Location: /eduleb/view/template/feed.php");
            exit;
        }

    }

    public function correct()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $commentaire = $_POST['commentaire'] ?? '';
            $typefeedback = $_POST['typefeedback'] ?? '';
            $note = (float)($_POST['note'] ?? 0);
            $competences = $_POST['competences'] ?? '';
            $datecorrection = $_POST['datecorrection'] ?? '';

            // Handle file upload
            $fileName = '';
            if (isset($_FILES['file2']) && $_FILES['file2']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $fileName = basename($_FILES['file2']['name']);
                $filePath = $uploadDir . $fileName;
                move_uploaded_file($_FILES['file2']['tmp_name'], $filePath);
            }

            // Insert into database
            $stmt = $this->conn->prepare("INSERT INTO corrections (commentaire, typefeedback, note, competences, datecorrection, fichier) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$commentaire, $typefeedback, $note, $competences, $datecorrection, $fileName]);

            // Redirect to feed
            header("Location: /eduleb/feed");
            exit;
        }
    }
}
?>