<?php

require_once __DIR__ . '/../model/User.php';
require_once __DIR__ . '/../model/Profil.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../lib/PHPMailer/SMTP.php';
require_once __DIR__ . '/../lib/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

class AuthController {

    private PDO $db;

    public function __construct() {
        $this->db = Config::getConnexion();
    }

    // =========================================================
    // GET /auth/login — rediriger vers sign-in.php
    // =========================================================
    public function login(): void {
        header('Location: /gestion_users/view/template/sign-in.php');
        exit;
    }

    // =========================================================
    // POST /auth/doLogin — traiter la connexion
    // =========================================================
    public function doLogin(): void {
        $errors = [];

        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($email))    $errors[] = "L'email est obligatoire.";
        if (empty($password)) $errors[] = "Le mot de passe est obligatoire.";

        if (empty($errors)) {
            $stmt = $this->db->prepare("SELECT * FROM user WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $row = $stmt->fetch();

            if (!$row) {
                $errors[] = "Email ou mot de passe incorrect.";
            } elseif (!password_verify($password, $row['password'])) {
                $errors[] = "Email ou mot de passe incorrect.";
            } elseif ($row['statut'] == 0) {
                $errors[] = "Votre compte a été bloqué. Veuillez contacter l'administrateur pour plus d'informations.";
            } elseif ($row['token_verif'] !== null) {
                $errors[] = "Veuillez vérifier votre email avant de vous connecter.";
            } else {
                /* --- Check student verification expiration (trial period) --- */
                if ($row['role'] === 'etudiant' && (int)$row['verification_student'] === 0) {
                    $trialDays = (int)($row['trial_days'] ?: 7);

                    $createdDate = new DateTime($row['created_at']);
                    $now = new DateTime();
                    /* Calculate precise hours elapsed, convert to days */
                    $hoursElapsed = ($now->getTimestamp() - $createdDate->getTimestamp()) / 3600;
                    $daysSinceCreation = floor($hoursElapsed / 24);

                    if ($daysSinceCreation >= $trialDays) {
                        /* Auto-block: trial expired, student not verified */
                        $this->db->prepare("UPDATE user SET statut = 0 WHERE id = ?")->execute([$row['id']]);
                        $errors[] = "Votre période d'essai de {$trialDays} jours a expiré. Veuillez contacter l'administrateur pour vérifier votre statut étudiant.";
                    }
                }

                if (empty($errors)) {
                    $_SESSION['user_id']    = $row['id'];
                    $_SESSION['user_nom']   = $row['nom'];
                    $_SESSION['user_prenom']= $row['prenom'];
                    $_SESSION['user_role']  = $row['role'];
                    $_SESSION['user_photo'] = $row['photo'];

                    /* Set user online */
                    $this->db->prepare("UPDATE user SET etat = 'online' WHERE id = ?")->execute([$row['id']]);

                    if ($row['role'] === 'admin') {
                        header('Location: /gestion_users/view/backoffice/src/pages/backoffice/dashboard.php');
                    } else {
                        header('Location: /gestion_users/view/template/index.php');
                    }
                    exit;
                }
            }
        }

        $_SESSION['errors']    = $errors;
        $_SESSION['form_data'] = ['email' => $email];
        header('Location: /gestion_users/view/template/sign-in.php');
        exit;
    }

    // =========================================================
    // GET /auth/signup — rediriger vers sign-up.php
    // =========================================================
    public function signup(): void {
        header('Location: /gestion_users/view/template/sign-up.php');
        exit;
    }

    // =========================================================
    // POST /auth/doSignup — traiter l'inscription
    // =========================================================
    public function doSignup(): void {
        $errors = [];

        $nom        = trim($_POST['nom']         ?? '');
        $prenom     = trim($_POST['prenom']      ?? '');
        $email      = trim($_POST['email']       ?? '');
        $password   = trim($_POST['password']    ?? '');
        $confirm    = trim($_POST['confirm']     ?? '');
        $telephone  = trim($_POST['telephone']   ?? '');
        $role       = trim($_POST['role']        ?? 'etudiant');
        $specialite = trim($_POST['specialite']  ?? '');
        $adresse    = trim($_POST['adresse']     ?? '');

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

        // --- Téléphone (optionnel, mais si rempli => 8 chiffres) ---
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

        // --- Confirmation ---
        if ($password !== $confirm)              $errors[] = "Les mots de passe ne correspondent pas.";

        // --- Rôle ---
        if (!in_array($role, ['encadrant', 'etudiant', 'partenariat'])) $errors[] = "Rôle invalide.";

        // --- Specialite + Adresse (obligatoires si role=encadrant) ---
        if ($role === 'encadrant') {
            if (empty($specialite)) {
                $errors[] = "La specialite est obligatoire pour les encadrants.";
            } elseif (mb_strlen($specialite) > 100) {
                $errors[] = "La specialite ne doit pas depasser 100 caracteres.";
            }
            if (empty($adresse)) {
                $errors[] = "L'adresse est obligatoire pour les encadrants (utilisee pour les seances en presentiel).";
            } elseif (mb_strlen($adresse) < 5) {
                $errors[] = "L'adresse doit contenir au moins 5 caracteres.";
            } elseif (mb_strlen($adresse) > 255) {
                $errors[] = "L'adresse ne doit pas depasser 255 caracteres.";
            }
        } else {
            // Reset si pas encadrant
            $specialite = '';
            $adresse = '';
        }

        if (empty($errors)) {
            $stmt = $this->db->prepare("SELECT id FROM user WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = "Cet email est déjà utilisé.";
            }
        }

        if (empty($errors)) {
            $token = bin2hex(random_bytes(32));
            $hash  = password_hash($password, PASSWORD_DEFAULT);

            $user = new User();
            $user->setNom($nom);
            $user->setPrenom($prenom);
            $user->setEmail($email);
            $user->setPassword($hash);
            $user->setTelephone($telephone);
            $user->setRole($role);
            $user->setStatut(1);
            $user->setPhoto('default.png');
            $user->setTokenVerif($token);

            /* Get current trial days from parametres for students */
            $trialDaysForUser = null;
            if ($role === 'etudiant') {
                $pStmt = $this->db->prepare("SELECT valeur FROM parametres WHERE cle = 'expiration_verification_jours' LIMIT 1");
                $pStmt->execute();
                $trialDaysForUser = (int)($pStmt->fetchColumn() ?: 7);
            }

            $stmt = $this->db->prepare("
                INSERT INTO user (nom, prenom, email, password, telephone, role, statut, photo, token_verif, trial_days, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $user->getNom(), $user->getPrenom(), $user->getEmail(),
                $user->getPassword(), $user->getTelephone(), $user->getRole(),
                $user->getStatut(), $user->getPhoto(), $user->getTokenVerif(),
                $trialDaysForUser,
            ]);

            $userId = $this->db->lastInsertId();

            $profil = new Profil();
            $profil->setUserId((int)$userId);

            // Si encadrant : on stocke specialite + adresse, sinon profil vide
            if ($role === 'encadrant' && !empty($specialite)) {
                $stmt2 = $this->db->prepare("INSERT INTO profil (user_id, specialite, adresse, created_at) VALUES (?, ?, ?, NOW())");
                $stmt2->execute([$profil->getUserId(), $specialite, $adresse]);

                // Creation automatique de disponibilites par defaut pour le nouvel encadrant
                // lundi-vendredi 8h-12h + 14h-18h (modifiable ensuite dans "Mes disponibilites")
                $defaultSlots = [
                    ['lundi',    '08:00:00', '12:00:00'],
                    ['lundi',    '14:00:00', '18:00:00'],
                    ['mardi',    '08:00:00', '12:00:00'],
                    ['mardi',    '14:00:00', '18:00:00'],
                    ['mercredi', '08:00:00', '12:00:00'],
                    ['mercredi', '14:00:00', '18:00:00'],
                    ['jeudi',    '08:00:00', '12:00:00'],
                    ['jeudi',    '14:00:00', '18:00:00'],
                    ['vendredi', '08:00:00', '12:00:00'],
                    ['vendredi', '14:00:00', '18:00:00'],
                ];
                $dispoStmt = $this->db->prepare("INSERT INTO disponibilites (id_encadrant, jour_semaine, heure_debut, heure_fin, actif) VALUES (?, ?, ?, ?, 1)");
                foreach ($defaultSlots as $slot) {
                    try {
                        $dispoStmt->execute([(int)$userId, $slot[0], $slot[1], $slot[2]]);
                    } catch (PDOException $e) {
                        // Ignorer silencieusement si la table disponibilites n'existe pas encore
                    }
                }
            } else {
                $stmt2 = $this->db->prepare("INSERT INTO profil (user_id, created_at) VALUES (?, NOW())");
                $stmt2->execute([$profil->getUserId()]);
            }

            $this->sendVerificationEmail($email, $nom, $token);

            $_SESSION['success'] = "Inscription réussie ! Vérifiez votre email pour activer votre compte.";
            header('Location: /gestion_users/view/template/sign-in.php');
            exit;
        }

        $_SESSION['errors']    = $errors;
        $_SESSION['form_data'] = compact('nom', 'prenom', 'email', 'telephone', 'role', 'specialite', 'adresse');
        header('Location: /gestion_users/view/template/sign-up.php');
        exit;
    }

    // =========================================================
    // GET /auth/verify?token=xxx — vérification email
    // =========================================================
    public function verify(): void {
        $token = $_GET['token'] ?? '';

        if (empty($token)) {
            $_SESSION['errors'] = ["Token invalide."];
            header('Location: /gestion_users/view/template/sign-in.php');
            exit;
        }

        $stmt = $this->db->prepare("SELECT id FROM user WHERE token_verif = ? LIMIT 1");
        $stmt->execute([$token]);
        $row = $stmt->fetch();

        if (!$row) {
            $_SESSION['errors'] = ["Lien de vérification invalide ou expiré."];
            header('Location: /gestion_users/view/template/sign-in.php');
            exit;
        }

        $stmt = $this->db->prepare("UPDATE user SET token_verif = NULL WHERE id = ?");
        $stmt->execute([$row['id']]);

        $_SESSION['success'] = "Compte activé avec succès ! Vous pouvez maintenant vous connecter.";
        header('Location: /gestion_users/view/template/sign-in.php');
        exit;
    }

    // =========================================================
    // POST /auth/doForgotPassword — envoyer code OTP
    // =========================================================
    public function doForgotPassword(): void {
        $errors = [];
        $email  = trim($_POST['email'] ?? '');

        if (empty($email)) $errors[] = "L'email est obligatoire.";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalide.";

        if (empty($errors)) {
            $stmt = $this->db->prepare("SELECT id, nom FROM user WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $row = $stmt->fetch();

            if (!$row) {
                $errors[] = "Aucun compte trouvé avec cet email.";
            } else {
                $code    = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));

                $stmt = $this->db->prepare("UPDATE user SET reset_code = ?, reset_expires = ? WHERE id = ?");
                $stmt->execute([$code, $expires, $row['id']]);

                $this->sendResetCodeEmail($email, $row['nom'], $code);

                $_SESSION['reset_email'] = $email;
                $_SESSION['success']     = "Un code à 6 chiffres a été envoyé à votre email.";
                header('Location: /gestion_users/view/template/otp-verification.php');
                exit;
            }
        }

        $_SESSION['errors']    = $errors;
        $_SESSION['form_data'] = ['email' => $email];
        header('Location: /gestion_users/view/template/forget-password.php');
        exit;
    }

    // =========================================================
    // POST /auth/doOtp — vérifier le code OTP
    // =========================================================
    public function doOtp(): void {
        $errors = [];
        $code   = trim($_POST['code'] ?? '');
        $email  = $_SESSION['reset_email'] ?? '';

        if (empty($code))  $errors[] = "Le code est obligatoire.";
        if (empty($email)) { header('Location: /gestion_users/view/template/forget-password.php'); exit; }

        if (empty($errors)) {
            $stmt = $this->db->prepare("
                SELECT id FROM user
                WHERE email = ? AND reset_code = ? AND reset_expires > NOW()
                LIMIT 1
            ");
            $stmt->execute([$email, $code]);
            $row = $stmt->fetch();

            if (!$row) {
                $errors[] = "Code incorrect ou expiré.";
            } else {
                $_SESSION['reset_user_id'] = $row['id'];
                header('Location: /gestion_users/view/template/reset-password.php');
                exit;
            }
        }

        $_SESSION['errors'] = $errors;
        header('Location: /gestion_users/view/template/otp-verification.php');
        exit;
    }

    // =========================================================
    // POST /auth/doResetPassword — enregistrer nouveau password
    // =========================================================
    public function doResetPassword(): void {
        $errors   = [];
        $password = trim($_POST['password']  ?? '');
        $confirm  = trim($_POST['confirm']   ?? '');
        $userId   = $_SESSION['reset_user_id'] ?? null;

        if (empty($userId)) { header('Location: /gestion_users/view/template/forget-password.php'); exit; }

        // --- Mot de passe sécurisé ---
        if (empty($password))                    $errors[] = "Le mot de passe est obligatoire.";
        elseif (strlen($password) < 8)           $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
        elseif (strlen($password) > 50)          $errors[] = "Le mot de passe ne doit pas dépasser 50 caractères.";
        elseif (!preg_match('/[A-Z]/', $password)) $errors[] = "Le mot de passe doit contenir au moins une majuscule.";
        elseif (!preg_match('/[a-z]/', $password)) $errors[] = "Le mot de passe doit contenir au moins une minuscule.";
        elseif (!preg_match('/[0-9]/', $password)) $errors[] = "Le mot de passe doit contenir au moins un chiffre.";
        elseif (!preg_match('/[^A-Za-z0-9]/', $password)) $errors[] = "Le mot de passe doit contenir au moins un caractère spécial.";

        if ($password !== $confirm) $errors[] = "Les mots de passe ne correspondent pas.";

        if (empty($errors)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE user SET password = ?, reset_code = NULL, reset_expires = NULL WHERE id = ?");
            $stmt->execute([$hash, $userId]);

            unset($_SESSION['reset_email'], $_SESSION['reset_user_id']);
            $_SESSION['success'] = "Mot de passe modifié avec succès !";
            header('Location: /gestion_users/view/template/sign-in.php');
            exit;
        }

        $_SESSION['errors'] = $errors;
        header('Location: /gestion_users/view/template/reset-password.php');
        exit;
    }

    // =========================================================
    // GET /auth/logout
    // =========================================================
    public function logout(): void {
        $userId = $_SESSION['user_id'] ?? null;

        if ($userId) {
            $this->db->prepare("UPDATE user SET etat = 'offline' WHERE id = ?")->execute([$userId]);
        }

        session_destroy();
        header('Location: /gestion_users/view/template/sign-in.php');
        exit;
    }

    // =========================================================
    // PRIVATE — Envoi email vérification
    // =========================================================
    private function sendVerificationEmail(string $to, string $nom, string $token): void {
        $link    = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/gestion_users/auth/verify?token=' . $token;
        $subject = "Activez votre compte EduMatch";
        $body    = "Bonjour {$nom},\n\nCliquez sur ce lien pour activer votre compte :\n{$link}\n\nEduMatch";
        $this->sendMail($to, $subject, $body);
    }

    // =========================================================
    // PRIVATE — Envoi email code OTP
    // =========================================================
    private function sendResetCodeEmail(string $to, string $nom, string $code): void {
        $subject = "Réinitialisation de mot de passe EduMatch";
        $body    = "Bonjour {$nom},\n\nVotre code de réinitialisation est : {$code}\n\nCe code expire dans 15 minutes.\n\nEduMatch";
        $this->sendMail($to, $subject, $body);
    }

    // =========================================================
    // PRIVATE — Envoi email via SMTP Gmail (PHPMailer)
    // =========================================================
    private function sendMail(string $to, string $subject, string $body): void {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'benouiraneminyar84@gmail.com';
            $mail->Password   = 'wxfj ydxh kmlm ekjl';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom('benouiraneminyar84@gmail.com', 'EduMatch');
            $mail->addAddress($to);

            $mail->isHTML(false);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();
        } catch (MailException $e) {
            error_log('Erreur envoi email: ' . $mail->ErrorInfo);
        }
    }
}
