<?php

require_once __DIR__ . '/../model/User.php';
require_once __DIR__ . '/../config/database.php';

class UserController {

    private PDO $db;

    public function __construct() {
        $this->db = Config::getConnexion();
        $this->requireAdmin();
    }

    private function requireAdmin(): void {
        if (empty($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /gestion_users/view/template/sign-in.php');
            exit;
        }
    }

    // POST /user/doAdd
    public function doAdd(): void {
        $errors = [];

        $nom       = trim($_POST['nom']       ?? '');
        $prenom    = trim($_POST['prenom']     ?? '');
        $email     = trim($_POST['email']      ?? '');
        $password  = trim($_POST['password']   ?? '');
        $telephone = trim($_POST['telephone']  ?? '');
        $role      = trim($_POST['role']       ?? 'etudiant');

        // --- Nom ---
        if (empty($nom))                         $errors[] = "Le nom est obligatoire.";
        elseif (mb_strlen($nom) < 2)             $errors[] = "Le nom doit contenir au moins 2 caractères.";
        elseif (mb_strlen($nom) > 30)            $errors[] = "Le nom ne doit pas dépasser 30 caractères.";
        elseif (!preg_match('/^[A-Za-zÀ-ÿ\s\-\']+$/', $nom)) $errors[] = "Le nom ne doit contenir que des lettres.";

        // --- Prénom ---
        if (empty($prenom))                      $errors[] = "Le prénom est obligatoire.";
        elseif (mb_strlen($prenom) < 2)          $errors[] = "Le prénom doit contenir au moins 2 caractères.";
        elseif (mb_strlen($prenom) > 30)         $errors[] = "Le prénom ne doit pas dépasser 30 caractères.";
        elseif (!preg_match('/^[A-Za-zÀ-ÿ\s\-\']+$/', $prenom)) $errors[] = "Le prénom ne doit contenir que des lettres.";

        // --- Email ---
        if (empty($email))                       $errors[] = "L'email est obligatoire.";
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Format d'email invalide.";

        // --- Téléphone (optionnel) ---
        if (!empty($telephone) && !preg_match('/^[0-9]{8}$/', $telephone))
            $errors[] = "Le numéro de téléphone doit contenir exactement 8 chiffres.";

        // --- Mot de passe sécurisé ---
        if (empty($password))                    $errors[] = "Le mot de passe est obligatoire.";
        elseif (strlen($password) < 8)           $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
        elseif (strlen($password) > 50)          $errors[] = "Le mot de passe ne doit pas dépasser 50 caractères.";
        elseif (!preg_match('/[A-Z]/', $password)) $errors[] = "Le mot de passe doit contenir au moins une majuscule.";
        elseif (!preg_match('/[a-z]/', $password)) $errors[] = "Le mot de passe doit contenir au moins une minuscule.";
        elseif (!preg_match('/[0-9]/', $password)) $errors[] = "Le mot de passe doit contenir au moins un chiffre.";
        elseif (!preg_match('/[^A-Za-z0-9]/', $password)) $errors[] = "Le mot de passe doit contenir au moins un caractère spécial.";

        // --- Rôle ---
        if (!in_array($role, ['admin', 'encadrant', 'etudiant'])) $errors[] = "Rôle invalide.";

        if (empty($errors)) {
            $stmt = $this->db->prepare("SELECT id FROM user WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            if ($stmt->fetch()) $errors[] = "Cet email est déjà utilisé.";
        }

        if (empty($errors)) {
            $photo = 'default.png';
            if (!empty($_FILES['photo']['name'])) {
                $photo = $this->uploadPhoto($_FILES['photo']);
                if (!$photo) { $errors[] = "Format de photo invalide (jpg, jpeg, png)."; }
            }
        }

        if (empty($errors)) {
            $user = new User();
            $user->setNom($nom);
            $user->setPrenom($prenom);
            $user->setEmail($email);
            $user->setPassword(password_hash($password, PASSWORD_DEFAULT));
            $user->setTelephone($telephone);
            $user->setRole($role);
            $user->setStatut(1);
            $user->setPhoto($photo ?? 'default.png');

            $stmt = $this->db->prepare("
                INSERT INTO user (nom, prenom, email, password, telephone, role, statut, photo, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $user->getNom(), $user->getPrenom(), $user->getEmail(),
                $user->getPassword(), $user->getTelephone(), $user->getRole(),
                $user->getStatut(), $user->getPhoto(),
            ]);

            $userId = $this->db->lastInsertId();
            $stmt2 = $this->db->prepare("INSERT INTO profil (user_id, created_at) VALUES (?, NOW())");
            $stmt2->execute([$userId]);

            $_SESSION['success'] = "Utilisateur ajouté avec succès.";
            header('Location: /gestion_users/view/backoffice/src/pages/backoffice/users.php');
            exit;
        }

        $_SESSION['errors']    = $errors;
        $_SESSION['form_data'] = compact('nom', 'prenom', 'email', 'telephone', 'role');
        header('Location: /gestion_users/view/backoffice/src/pages/backoffice/add-user.php');
        exit;
    }

    // POST /user/doEdit
    public function doEdit(): void {
        $errors = [];
        $id     = (int)($_POST['id'] ?? 0);

        $nom       = trim($_POST['nom']       ?? '');
        $prenom    = trim($_POST['prenom']     ?? '');
        $email     = trim($_POST['email']      ?? '');
        $telephone = trim($_POST['telephone']  ?? '');
        $role      = trim($_POST['role']       ?? 'etudiant');

        // --- Nom ---
        if (empty($nom))                         $errors[] = "Le nom est obligatoire.";
        elseif (mb_strlen($nom) < 2)             $errors[] = "Le nom doit contenir au moins 2 caractères.";
        elseif (mb_strlen($nom) > 30)            $errors[] = "Le nom ne doit pas dépasser 30 caractères.";
        elseif (!preg_match('/^[A-Za-zÀ-ÿ\s\-\']+$/', $nom)) $errors[] = "Le nom ne doit contenir que des lettres.";

        // --- Prénom ---
        if (empty($prenom))                      $errors[] = "Le prénom est obligatoire.";
        elseif (mb_strlen($prenom) < 2)          $errors[] = "Le prénom doit contenir au moins 2 caractères.";
        elseif (mb_strlen($prenom) > 30)         $errors[] = "Le prénom ne doit pas dépasser 30 caractères.";
        elseif (!preg_match('/^[A-Za-zÀ-ÿ\s\-\']+$/', $prenom)) $errors[] = "Le prénom ne doit contenir que des lettres.";

        // --- Email ---
        if (empty($email))                       $errors[] = "L'email est obligatoire.";
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Format d'email invalide.";

        // --- Téléphone (optionnel) ---
        if (!empty($telephone) && !preg_match('/^[0-9]{8}$/', $telephone))
            $errors[] = "Le numéro de téléphone doit contenir exactement 8 chiffres.";

        // --- Rôle ---
        if (!in_array($role, ['admin', 'encadrant', 'etudiant'])) $errors[] = "Rôle invalide.";

        if (empty($errors)) {
            $stmt = $this->db->prepare("SELECT id FROM user WHERE email = ? AND id != ? LIMIT 1");
            $stmt->execute([$email, $id]);
            if ($stmt->fetch()) $errors[] = "Cet email est déjà utilisé par un autre utilisateur.";
        }

        if (empty($errors)) {
            $stmt = $this->db->prepare("SELECT photo FROM user WHERE id = ? LIMIT 1");
            $stmt->execute([$id]);
            $currentPhoto = $stmt->fetchColumn();
            $photo = $currentPhoto;

            if (!empty($_FILES['photo']['name'])) {
                $newPhoto = $this->uploadPhoto($_FILES['photo']);
                if ($newPhoto) {
                    $photo = $newPhoto;
                    if ($currentPhoto !== 'default.png') {
                        @unlink(__DIR__ . '/../uploads/photos/' . $currentPhoto);
                    }
                } else {
                    $errors[] = "Format de photo invalide (jpg, jpeg, png).";
                }
            }
        }

        if (empty($errors)) {
            $user = new User();
            $user->setId($id);
            $user->setNom($nom);
            $user->setPrenom($prenom);
            $user->setEmail($email);
            $user->setTelephone($telephone);
            $user->setRole($role);

            $stmt = $this->db->prepare("UPDATE user SET nom=?, prenom=?, email=?, telephone=?, role=?, photo=? WHERE id=?");
            $stmt->execute([
                $user->getNom(), $user->getPrenom(), $user->getEmail(),
                $user->getTelephone(), $user->getRole(), $photo, $user->getId()
            ]);

            $_SESSION['success'] = "Utilisateur modifié avec succès.";
            header('Location: /gestion_users/view/backoffice/src/pages/backoffice/users.php');
            exit;
        }

        $_SESSION['errors']    = $errors;
        $_SESSION['form_data'] = compact('nom', 'prenom', 'email', 'telephone', 'role');
        header('Location: /gestion_users/view/backoffice/src/pages/backoffice/edit-user.php?id=' . $id);
        exit;
    }

    // POST /user/delete
    public function delete(): void {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $this->db->prepare("SELECT photo FROM user WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if ($row) {
            if ($row['photo'] !== 'default.png') {
                @unlink(__DIR__ . '/../uploads/photos/' . $row['photo']);
            }
            $stmt = $this->db->prepare("DELETE FROM user WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['success'] = "Utilisateur supprimé avec succès.";
        } else {
            $_SESSION['errors'] = ["Utilisateur introuvable."];
        }

        header('Location: /gestion_users/view/backoffice/src/pages/backoffice/users.php');
        exit;
    }

    // POST /user/toggleStatut
    public function toggleStatut(): void {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $this->db->prepare("SELECT statut FROM user WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if ($row) {
            $nouveauStatut = ($row['statut'] == 1) ? 0 : 1;
            $stmt = $this->db->prepare("UPDATE user SET statut = ? WHERE id = ?");
            $stmt->execute([$nouveauStatut, $id]);
            $_SESSION['success'] = $nouveauStatut ? "Utilisateur débloqué." : "Utilisateur bloqué.";
        }

        header('Location: /gestion_users/view/backoffice/src/pages/backoffice/users.php');
        exit;
    }

    private function uploadPhoto(array $file): string|false {
        $allowed    = ['image/jpeg', 'image/png'];
        $uploadDir  = __DIR__ . '/../uploads/photos/';
        $finfo      = finfo_open(FILEINFO_MIME_TYPE);
        $mime       = finfo_file($finfo, $file['tmp_name']);
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
