<?php

require_once __DIR__ . '/../model/User.php';
require_once __DIR__ . '/../model/Profil.php';
require_once __DIR__ . '/../config/database.php';

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
                $errors[] = "Votre compte est bloqué. Contactez l'administrateur.";
            } elseif ($row['token_verif'] !== null) {
                $errors[] = "Veuillez vérifier votre email avant de vous connecter.";
            } else {
                $_SESSION['user_id']   = $row['id'];
                $_SESSION['user_nom']  = $row['nom'];
                $_SESSION['user_role'] = $row['role'];
                $_SESSION['user_photo']= $row['photo'];

                if ($row['role'] === 'admin') {
                    header('Location: /gestion_users/view/backoffice/src/pages/backoffice/users.php');
                } else {
                    header('Location: /gestion_users/view/template/profil.php');
                }
                exit;
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

        $nom       = trim($_POST['nom']       ?? '');
        $prenom    = trim($_POST['prenom']     ?? '');
        $email     = trim($_POST['email']      ?? '');
        $password  = trim($_POST['password']   ?? '');
        $confirm   = trim($_POST['confirm']    ?? '');
        $telephone = trim($_POST['telephone']  ?? '');
        $role      = trim($_POST['role']       ?? 'etudiant');

        if (empty($nom))      $errors[] = "Le nom est obligatoire.";
        if (empty($prenom))   $errors[] = "Le prénom est obligatoire.";
        if (empty($email))    $errors[] = "L'email est obligatoire.";
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalide.";
        if (strlen($password) < 6) $errors[] = "Le mot de passe doit contenir au moins 6 caractères.";
        if ($password !== $confirm) $errors[] = "Les mots de passe ne correspondent pas.";
        if (!in_array($role, ['admin', 'encadrant', 'etudiant'])) $errors[] = "Rôle invalide.";

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

            $stmt = $this->db->prepare("
                INSERT INTO user (nom, prenom, email, password, telephone, role, statut, photo, token_verif, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $user->getNom(), $user->getPrenom(), $user->getEmail(),
                $user->getPassword(), $user->getTelephone(), $user->getRole(),
                $user->getStatut(), $user->getPhoto(), $user->getTokenVerif(),
            ]);

            $userId = $this->db->lastInsertId();

            $profil = new Profil();
            $profil->setUserId((int)$userId);

            $stmt2 = $this->db->prepare("INSERT INTO profil (user_id, created_at) VALUES (?, NOW())");
            $stmt2->execute([$profil->getUserId()]);

            $this->sendVerificationEmail($email, $nom, $token);

            $_SESSION['success'] = "Inscription réussie ! Vérifiez votre email pour activer votre compte.";
            header('Location: /gestion_users/view/template/sign-in.php');
            exit;
        }

        $_SESSION['errors']    = $errors;
        $_SESSION['form_data'] = compact('nom', 'prenom', 'email', 'telephone', 'role');
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
        if (strlen($password) < 6) $errors[] = "Le mot de passe doit contenir au moins 6 caractères.";
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
    // PRIVATE — Envoi email via SMTP
    // =========================================================
    private function sendMail(string $to, string $subject, string $body): void {
        $smtpUser = 'benouiraneminyar84@gmail.com';
        $headers  = "From: EduMatch <{$smtpUser}>\r\n";
        $headers .= "Reply-To: {$smtpUser}\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        @mail($to, $subject, $body, $headers);
    }
}
