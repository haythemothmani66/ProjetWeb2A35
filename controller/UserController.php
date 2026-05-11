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
        if (!in_array($role, ['admin', 'encadrant', 'etudiant', 'partenariat'])) $errors[] = "Rôle invalide.";

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

            /* Get current trial days for students */
            $trialDaysForUser = null;
            if ($role === 'etudiant') {
                $pStmt = $this->db->prepare("SELECT valeur FROM parametres WHERE cle = 'expiration_verification_jours' LIMIT 1");
                $pStmt->execute();
                $trialDaysForUser = (int)($pStmt->fetchColumn() ?: 7);
            }

            $stmt = $this->db->prepare("
                INSERT INTO user (nom, prenom, email, password, telephone, role, statut, photo, trial_days, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $user->getNom(), $user->getPrenom(), $user->getEmail(),
                $user->getPassword(), $user->getTelephone(), $user->getRole(),
                $user->getStatut(), $user->getPhoto(), $trialDaysForUser,
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
        $verifStudent = isset($_POST['verification_student']) ? (int) $_POST['verification_student'] : null;
        $statutCompte = isset($_POST['statut']) ? (int) $_POST['statut'] : null;
        $specialite   = trim($_POST['specialite'] ?? '');
        $adresseEnc   = trim($_POST['adresse'] ?? '');

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
        if (!in_array($role, ['admin', 'encadrant', 'etudiant', 'partenariat'])) $errors[] = "Rôle invalide.";

        // --- Verification student & statut (optionnels mais valides si presents) ---
        if ($verifStudent !== null && !in_array($verifStudent, [0, 1], true)) $errors[] = "Valeur de verification invalide.";
        if ($statutCompte !== null && !in_array($statutCompte, [0, 1], true)) $errors[] = "Valeur de statut invalide.";

        // --- Specialite + Adresse (uniquement si encadrant) ---
        if ($role !== 'encadrant') {
            $specialite = '';
            $adresseEnc = '';
        } else {
            if (mb_strlen($specialite) > 100) {
                $errors[] = "La specialite ne doit pas depasser 100 caracteres.";
            }
            if (!empty($adresseEnc) && mb_strlen($adresseEnc) > 255) {
                $errors[] = "L'adresse ne doit pas depasser 255 caracteres.";
            }
        }

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

            // Recuperer les valeurs actuelles si non transmises (champs optionnels)
            $stmtCur = $this->db->prepare("SELECT verification_student, statut FROM user WHERE id = ? LIMIT 1");
            $stmtCur->execute([$id]);
            $cur = $stmtCur->fetch(PDO::FETCH_ASSOC);
            $finalVerif  = $verifStudent !== null ? $verifStudent : (int)$cur['verification_student'];
            $finalStatut = $statutCompte !== null ? $statutCompte : (int)$cur['statut'];

            // Si l'admin verifie manuellement un etudiant, on active aussi le compte
            if ($finalVerif === 1 && (int)$cur['verification_student'] === 0) {
                $finalStatut = 1;
            }

            $stmt = $this->db->prepare("UPDATE user SET nom=?, prenom=?, email=?, telephone=?, role=?, photo=?, verification_student=?, statut=? WHERE id=?");
            $stmt->execute([
                $user->getNom(), $user->getPrenom(), $user->getEmail(),
                $user->getTelephone(), $user->getRole(), $photo,
                $finalVerif, $finalStatut,
                $user->getId()
            ]);

            // Specialite : mise a jour de profil
            // - Si role = encadrant : on UPSERT la specialite
            // - Sinon : on met specialite a NULL (au cas ou l'utilisateur etait encadrant avant)
            $existsStmt = $this->db->prepare("SELECT id_profil FROM profil WHERE user_id = ? LIMIT 1");
            $existsStmt->execute([$id]);
            $hasProfil = (bool) $existsStmt->fetchColumn();

            if ($role === 'encadrant') {
                $specValue = $specialite !== '' ? $specialite : null;
                $adrValue  = $adresseEnc !== '' ? $adresseEnc : null;
                if ($hasProfil) {
                    $this->db->prepare("UPDATE profil SET specialite = ?, adresse = ? WHERE user_id = ?")->execute([$specValue, $adrValue, $id]);
                } else {
                    $this->db->prepare("INSERT INTO profil (user_id, specialite, adresse, created_at) VALUES (?, ?, ?, NOW())")->execute([$id, $specValue, $adrValue]);
                }
            } else {
                // Role autre que encadrant : on retire specialite et adresse
                if ($hasProfil) {
                    $this->db->prepare("UPDATE profil SET specialite = NULL, adresse = NULL WHERE user_id = ?")->execute([$id]);
                }
            }

            $_SESSION['success'] = "Utilisateur modifié avec succès.";
            header('Location: /gestion_users/view/backoffice/src/pages/backoffice/users.php');
            exit;
        }

        $_SESSION['errors']    = $errors;
        $_SESSION['form_data'] = compact('nom', 'prenom', 'email', 'telephone', 'role', 'verifStudent', 'statutCompte', 'specialite') + [
            'verification_student' => $verifStudent,
            'statut'  => $statutCompte,
            'adresse' => $adresseEnc,
        ];
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

    // POST /user/toggleVerification
    public function toggleVerification(): void {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $this->db->prepare("SELECT verification_student, role FROM user WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if ($row && $row['role'] === 'etudiant') {
            $newVal = ($row['verification_student'] == 1) ? 0 : 1;
            $this->db->prepare("UPDATE user SET verification_student = ? WHERE id = ?")->execute([$newVal, $id]);
            if ($newVal === 1) {
                /* Re-activate if was auto-blocked */
                $this->db->prepare("UPDATE user SET statut = 1 WHERE id = ? AND statut = 0")->execute([$id]);
            }
            $_SESSION['success'] = $newVal ? "Etudiant verifie avec succes." : "Verification etudiant retiree.";
        }

        $referer = $_SERVER['HTTP_REFERER'] ?? '/gestion_users/view/backoffice/src/pages/backoffice/users.php';
        header('Location: ' . $referer);
        exit;
    }

    // POST /user/rejectStudent — block student immediately
    public function rejectStudent(): void {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $this->db->prepare("SELECT role FROM user WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if ($row && $row['role'] === 'etudiant') {
            $this->db->prepare("UPDATE user SET statut = 0 WHERE id = ?")->execute([$id]);
            $_SESSION['success'] = "Etudiant refuse et bloque avec succes.";
        }

        $referer = $_SERVER['HTTP_REFERER'] ?? '/gestion_users/view/backoffice/src/pages/backoffice/users.php';
        header('Location: ' . $referer);
        exit;
    }

    // POST /user/updateParametres
    public function updateParametres(): void {
        $expiration = (int)($_POST['expiration_verification_jours'] ?? 7);
        if ($expiration < 1) $expiration = 1;
        if ($expiration > 365) $expiration = 365;

        $this->db->prepare("UPDATE parametres SET valeur = ? WHERE cle = 'expiration_verification_jours'")
                 ->execute([(string)$expiration]);

        $_SESSION['success'] = "Parametres mis a jour avec succes (expiration: {$expiration} jours).";
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
