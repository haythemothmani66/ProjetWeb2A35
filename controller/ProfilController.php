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

        /* Etudiant extra fields */
        $classe              = trim($_POST['classe'] ?? '');
        $email_universitaire = trim($_POST['email_universitaire'] ?? '');
        $adresse             = trim($_POST['adresse'] ?? '');
        $etablissement_ecole = trim($_POST['etablissement_ecole'] ?? '');
        $identifiant_card    = trim($_POST['identifiant_card'] ?? '');
        $annee_universitaire = trim($_POST['annee_universitaire'] ?? '');

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

        // --- Téléphone (optionnel, mais si rempli => 8 chiffres) ---
        if (!empty($telephone) && !preg_match('/^[0-9]{8}$/', $telephone))
            $errors[] = "Le numéro de téléphone doit contenir exactement 8 chiffres.";

        // --- Champs etudiant (optionnels, mais si remplis => max length + email format) ---
        if (!empty($niveau) && mb_strlen($niveau) > 40)
            $errors[] = "Le niveau ne doit pas depasser 40 caracteres.";
        if (!empty($classe) && mb_strlen($classe) > 20)
            $errors[] = "La classe ne doit pas depasser 20 caracteres.";
        if (!empty($email_universitaire)) {
            if (mb_strlen($email_universitaire) > 50)
                $errors[] = "L'email universitaire ne doit pas depasser 50 caracteres.";
            elseif (!preg_match('/^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/', $email_universitaire))
                $errors[] = "Format d'email universitaire invalide.";
        }
        if (!empty($etablissement_ecole) && mb_strlen($etablissement_ecole) > 100)
            $errors[] = "L'etablissement ne doit pas depasser 100 caracteres.";
        if (!empty($identifiant_card) && mb_strlen($identifiant_card) > 15)
            $errors[] = "L'identifiant carte ne doit pas depasser 15 caracteres.";
        if (!empty($annee_universitaire) && mb_strlen($annee_universitaire) > 20)
            $errors[] = "L'annee universitaire ne doit pas depasser 20 caracteres.";
        if (!empty($specialite) && mb_strlen($specialite) > 40)
            $errors[] = "La specialite ne doit pas depasser 40 caracteres.";
        if (!empty($adresse) && mb_strlen($adresse) > 100)
            $errors[] = "L'adresse ne doit pas depasser 100 caracteres.";

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
            $profil->setClasse($classe ?: null);
            $profil->setEmailUniversitaire($email_universitaire ?: null);
            $profil->setAdresse($adresse ?: null);
            $profil->setEtablissementEcole($etablissement_ecole ?: null);
            $profil->setIdentifiantCard($identifiant_card ?: null);
            $profil->setAnneeUniversitaire($annee_universitaire ?: null);

            /* Upload carte etudiant */
            $cardImage = null;
            if (!empty($_FILES['card_image']['name']) && $_FILES['card_image']['error'] === UPLOAD_ERR_OK) {
                $cardUploaded = $this->uploadPhoto($_FILES['card_image']);
                if ($cardUploaded) {
                    $cardImage = $cardUploaded;
                }
            }

            $sql = "UPDATE profil SET bio_text=?, niveau=?, specialite=?, classe=?, email_universitaire=?, adresse=?, etablissement_ecole=?, identifiant_card=?, annee_universitaire=?";
            $params = [$profil->getBioText(), $profil->getNiveau(), $profil->getSpecialite(), $profil->getClasse(), $profil->getEmailUniversitaire(), $profil->getAdresse(), $profil->getEtablissementEcole(), $profil->getIdentifiantCard(), $profil->getAnneeUniversitaire()];

            if ($cardImage) {
                $sql .= ", card_image=?";
                $params[] = $cardImage;
            }
            $sql .= " WHERE user_id=?";
            $params[] = $profil->getUserId();

            $stmt2 = $this->db->prepare($sql);
            $stmt2->execute($params);

            $_SESSION['user_nom']    = $user->getNom();
            $_SESSION['user_prenom'] = $user->getPrenom();
            $_SESSION['user_photo']  = $user->getPhoto();
            $_SESSION['success'] = "Profil mis à jour avec succès.";
            $redirect = ($_SESSION['user_role'] === 'admin')
                ? '/gestion_users/view/backoffice/src/pages/backoffice/profil-admin.php'
                : '/gestion_users/view/template/profil.php';
            header('Location: ' . $redirect);
            exit;
        }

        $_SESSION['errors'] = $errors;
        $redirect = ($_SESSION['user_role'] === 'admin')
            ? '/gestion_users/view/backoffice/src/pages/backoffice/profil-admin.php'
            : '/gestion_users/view/template/profil.php';
        header('Location: ' . $redirect);
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

        // --- Nouveau mot de passe sécurisé ---
        if (empty($new))                        $errors[] = "Le nouveau mot de passe est obligatoire.";
        elseif (strlen($new) < 8)               $errors[] = "Le nouveau mot de passe doit contenir au moins 8 caractères.";
        elseif (strlen($new) > 50)              $errors[] = "Le nouveau mot de passe ne doit pas dépasser 50 caractères.";
        elseif (!preg_match('/[A-Z]/', $new))   $errors[] = "Le mot de passe doit contenir au moins une majuscule.";
        elseif (!preg_match('/[a-z]/', $new))   $errors[] = "Le mot de passe doit contenir au moins une minuscule.";
        elseif (!preg_match('/[0-9]/', $new))   $errors[] = "Le mot de passe doit contenir au moins un chiffre.";
        elseif (!preg_match('/[^A-Za-z0-9]/', $new)) $errors[] = "Le mot de passe doit contenir au moins un caractère spécial.";

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
            $redirect = ($_SESSION['user_role'] === 'admin')
                ? '/gestion_users/view/backoffice/src/pages/backoffice/profil-admin.php'
                : '/gestion_users/view/template/profil.php';
            header('Location: ' . $redirect);
            exit;
        }

        $_SESSION['errors'] = $errors;
        $redirect = ($_SESSION['user_role'] === 'admin')
            ? '/gestion_users/view/backoffice/src/pages/backoffice/profil-admin.php'
            : '/gestion_users/view/template/profil.php';
        header('Location: ' . $redirect);
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
