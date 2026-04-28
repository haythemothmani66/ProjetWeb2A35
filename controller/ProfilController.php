<?php

require_once __DIR__ . '/../model/User.php';
require_once __DIR__ . '/../model/Profil.php';
require_once __DIR__ . '/../config/database.php';

class ProfilController {

    private PDO $db;

    public function __construct() {
        $this->db = Config::getConnexion();
        $this->requireAuth();
    }

    private function requireAuth(): void {
        if (empty($_SESSION['user_id'])) {
            header('Location: /gestion_users/view/template/sign-in.php');
            exit;
        }
    }

    // POST /profil/doEdit
    public function doEdit(): void {
        $errors = [];
        $id     = (int)$_SESSION['user_id'];

        $nom       = trim($_POST['nom']       ?? '');
        $prenom    = trim($_POST['prenom']     ?? '');
        $telephone = trim($_POST['telephone']  ?? '');
        $bio_text  = trim($_POST['bio_text']   ?? '');
        $niveau    = trim($_POST['niveau']     ?? '');
        $specialite= trim($_POST['specialite'] ?? '');

        if (empty($nom))    $errors[] = "Le nom est obligatoire.";
        if (empty($prenom)) $errors[] = "Le prénom est obligatoire.";

        if (empty($errors)) {
            $stmt = $this->db->prepare("SELECT photo FROM user WHERE id = ? LIMIT 1");
            $stmt->execute([$id]);
            $currentPhoto = $stmt->fetchColumn();
            $photo = $currentPhoto;

            if (!empty($_FILES['photo']['name'])) {
                $newPhoto = $this->uploadPhoto($_FILES['photo']);
                if ($newPhoto) {
                    if ($currentPhoto !== 'default.png') {
                        @unlink(__DIR__ . '/../uploads/photos/' . $currentPhoto);
                    }
                    $photo = $newPhoto;
                } else {
                    $errors[] = "Format de photo invalide (jpg, jpeg, png).";
                }
            }
        }

        if (empty($errors)) {
            $user = new User();
            $user->setNom($nom);
            $user->setPrenom($prenom);
            $user->setTelephone($telephone);
            $user->setPhoto($photo);

            $stmt = $this->db->prepare("UPDATE user SET nom=?, prenom=?, telephone=?, photo=? WHERE id=?");
            $stmt->execute([$user->getNom(), $user->getPrenom(), $user->getTelephone(), $user->getPhoto(), $id]);

            $profil = new Profil();
            $profil->setUserId($id);
            $profil->setBioText($bio_text ?: null);
            $profil->setNiveau($niveau ?: null);
            $profil->setSpecialite($specialite ?: null);

            $stmt2 = $this->db->prepare("UPDATE profil SET bio_text=?, niveau=?, specialite=? WHERE user_id=?");
            $stmt2->execute([$profil->getBioText(), $profil->getNiveau(), $profil->getSpecialite(), $profil->getUserId()]);

            $_SESSION['user_nom']   = $user->getNom();
            $_SESSION['user_photo'] = $user->getPhoto();
            $_SESSION['success']    = "Profil mis à jour avec succès.";
            header('Location: /gestion_users/view/template/profil.php');
            exit;
        }

        $_SESSION['errors'] = $errors;
        header('Location: /gestion_users/view/template/profil.php');
        exit;
    }

    // POST /profil/changePassword
    public function changePassword(): void {
        $errors   = [];
        $id       = (int)$_SESSION['user_id'];
        $current  = trim($_POST['current_password'] ?? '');
        $new      = trim($_POST['new_password']     ?? '');
        $confirm  = trim($_POST['confirm_password'] ?? '');

        if (empty($current)) $errors[] = "Le mot de passe actuel est obligatoire.";
        if (strlen($new) < 6) $errors[] = "Le nouveau mot de passe doit contenir au moins 6 caractères.";
        if ($new !== $confirm) $errors[] = "Les nouveaux mots de passe ne correspondent pas.";

        if (empty($errors)) {
            $stmt = $this->db->prepare("SELECT password FROM user WHERE id = ? LIMIT 1");
            $stmt->execute([$id]);
            $hash = $stmt->fetchColumn();

            if (!password_verify($current, $hash)) {
                $errors[] = "Mot de passe actuel incorrect.";
            }
        }

        if (empty($errors)) {
            $newHash = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE user SET password=? WHERE id=?");
            $stmt->execute([$newHash, $id]);

            $_SESSION['success'] = "Mot de passe modifié avec succès.";
            header('Location: /gestion_users/view/template/profil.php');
            exit;
        }

        $_SESSION['errors'] = $errors;
        header('Location: /gestion_users/view/template/profil.php');
        exit;
    }

    private function uploadPhoto(array $file): string|false {
        $allowed   = ['image/jpeg', 'image/png'];
        $uploadDir = __DIR__ . '/../uploads/photos/';
        $finfo     = finfo_open(FILEINFO_MIME_TYPE);
        $mime      = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowed)) return false;

        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('photo_', true) . '.' . strtolower($ext);

        if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            return $filename;
        }
        return false;
    }
}
