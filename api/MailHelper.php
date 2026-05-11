<?php
declare(strict_types=1);

// PHPMailer sans Composer — utilise les fichiers locaux du projet
require_once dirname(__DIR__) . '/lib/PHPMailer/Exception.php';
require_once dirname(__DIR__) . '/lib/PHPMailer/PHPMailer.php';
require_once dirname(__DIR__) . '/lib/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class MailHelper
{
    // Credentials SMTP (Gmail App Password)
    private const SMTP_HOST     = 'smtp.gmail.com';
    private const SMTP_PORT     = 587;
    private const SMTP_USER     = 'chahdtissaoui29@gmail.com';
    private const SMTP_PASS     = 'lnainfkoigwpvggw';
    private const SENDER_NAME   = 'EduMatch Team';

    /**
     * Cree une nouvelle instance PHPMailer configuree a chaque appel.
     * Pas de singleton — evite les problemes d'etat corrompu entre les envois.
     */
    private static function createMailer(): PHPMailer
    {
        $mailer = new PHPMailer(true);

        $mailer->isSMTP();
        $mailer->Host       = self::SMTP_HOST;
        $mailer->SMTPAuth   = true;
        $mailer->Username   = self::SMTP_USER;
        $mailer->Password   = self::SMTP_PASS;
        $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mailer->Port       = self::SMTP_PORT;

        // From DOIT correspondre au compte Gmail authentifie, sinon Gmail rejette/vide le mail
        $mailer->setFrom(self::SMTP_USER, self::SENDER_NAME);
        $mailer->addReplyTo(self::SMTP_USER, self::SENDER_NAME);

        $mailer->isHTML(true);
        $mailer->CharSet = 'UTF-8';

        return $mailer;
    }

    /**
     * Send partnership approval email
     */
    public static function sendApprovalEmail(string $toEmail, string $organizationName): bool
    {
        try {
            $mailer = self::createMailer();
            $mailer->addAddress($toEmail);

            // Lien vers la page partenariat (le partenaire devra se connecter pour voir son espace)
            $partnerLink = "http://localhost/gestion_users/view/frontoffice/partenariat.php";

            $mailer->Subject = "Partenariat Approuve - EduMatch";
            $mailer->Body = "
                <!DOCTYPE html>
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background: linear-gradient(135deg, #ff7f50 0%, #ff6347 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                        .button { display: inline-block; padding: 12px 30px; background: linear-gradient(135deg, #ff7f50 0%, #ff6347 100%); color: white; text-decoration: none; border-radius: 8px; margin-top: 20px; font-weight: bold; }
                        .footer { text-align: center; padding: 20px; font-size: 12px; color: #999; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h1>Felicitations {$organizationName} !</h1>
                        </div>
                        <div class='content'>
                            <h2>Votre demande de partenariat a ete approuvee !</h2>
                            <p>Cher(e) {$organizationName},</p>
                            <p>Nous avons le plaisir de vous informer que votre candidature de partenariat avec <strong>EduMatch</strong> a ete <strong>approuvee</strong>.</p>
                            <p>Nous sommes ravis de vous accueillir dans notre reseau de partenaires dédies a l'avenir de l'education.</p>
                            <p>Pour acceder a votre espace partenaire, cliquez sur le bouton ci-dessous :</p>
                            <p style='text-align: center;'>
                                <a href='{$partnerLink}' class='button'>Acceder a mon espace partenaire</a>
                            </p>
                            <p>Si vous avez des questions, n'hesitez pas a contacter notre equipe.</p>
                            <p>Cordialement,<br><strong>L'equipe EduMatch</strong></p>
                        </div>
                        <div class='footer'>
                            <p>&copy; 2026 EduMatch. Tous droits reserves.</p>
                        </div>
                    </div>
                </body>
                </html>
            ";

            $mailer->AltBody = "Felicitations {$organizationName} ! Votre demande de partenariat avec EduMatch a ete approuvee. Accedez a votre espace : {$partnerLink}";

            return $mailer->send();
        } catch (Exception $e) {
            error_log("MailHelper::sendApprovalEmail failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send partnership rejection email
     */
    public static function sendRejectionEmail(string $toEmail, string $organizationName): bool
    {
        try {
            $mailer = self::createMailer();
            $mailer->addAddress($toEmail);

            $mailer->Subject = "Mise a jour de votre candidature - EduMatch";
            $mailer->Body = "
                <!DOCTYPE html>
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background: #c62828; color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                        .footer { text-align: center; padding: 20px; font-size: 12px; color: #999; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h1>Mise a jour de votre candidature</h1>
                        </div>
                        <div class='content'>
                            <h2>Statut : Non approuvee</h2>
                            <p>Cher(e) {$organizationName},</p>
                            <p>Merci pour votre interet pour un partenariat avec <strong>EduMatch</strong>.</p>
                            <p>Apres examen attentif de votre candidature, nous ne sommes malheureusement pas en mesure d'approuver votre demande de partenariat pour le moment.</p>
                            <p>Nous vous invitons a consulter nos criteres de partenariat et a soumettre une nouvelle candidature a l'avenir.</p>
                            <p>Si vous avez des questions, n'hesitez pas a contacter notre equipe.</p>
                            <p>Cordialement,<br><strong>L'equipe EduMatch</strong></p>
                        </div>
                        <div class='footer'>
                            <p>&copy; 2026 EduMatch. Tous droits reserves.</p>
                        </div>
                    </div>
                </body>
                </html>
            ";

            $mailer->AltBody = "Cher(e) {$organizationName}, apres examen de votre candidature, nous ne sommes pas en mesure d'approuver votre demande de partenariat avec EduMatch pour le moment.";

            return $mailer->send();
        } catch (Exception $e) {
            error_log("MailHelper::sendRejectionEmail failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send pending confirmation email (when application is submitted)
     */
    public static function sendPendingEmail(string $toEmail, string $organizationName): bool
    {
        try {
            $mailer = self::createMailer();
            $mailer->addAddress($toEmail);

            $mailer->Subject = "Candidature recue - EduMatch";
            $mailer->Body = "
                <!DOCTYPE html>
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background: linear-gradient(135deg, #ff7f50 0%, #ff6347 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                        .footer { text-align: center; padding: 20px; font-size: 12px; color: #999; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h1>Candidature recue !</h1>
                        </div>
                        <div class='content'>
                            <h2>Merci pour votre interet, {$organizationName} !</h2>
                            <p>Nous avons bien recu votre candidature de partenariat pour <strong>EduMatch</strong>.</p>
                            <p>Notre equipe examinera votre dossier et vous recontactera dans les 48 heures.</p>
                            <p><strong>Prochaines etapes :</strong></p>
                            <ul>
                                <li>Notre equipe partenariat examine votre candidature</li>
                                <li>Vous recevrez un email avec la decision</li>
                                <li>Si approuvee, vous pourrez acceder a votre espace partenaire</li>
                            </ul>
                            <p>Cordialement,<br><strong>L'equipe EduMatch</strong></p>
                        </div>
                        <div class='footer'>
                            <p>&copy; 2026 EduMatch. Tous droits reserves.</p>
                        </div>
                    </div>
                </body>
                </html>
            ";

            $mailer->AltBody = "Merci {$organizationName} ! Nous avons bien recu votre candidature de partenariat EduMatch. Notre equipe vous recontactera dans les 48 heures.";

            return $mailer->send();
        } catch (Exception $e) {
            error_log("MailHelper::sendPendingEmail failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send final contract activation email
     */
    public static function sendContractFinalizedEmail(string $toEmail, string $organizationName, string $contractRef): bool
    {
        try {
            $mailer = self::createMailer();
            $mailer->addAddress($toEmail);

            $mailer->Subject = "Partenariat officiel confirme - EduMatch";
            $mailer->Body = "
                <!DOCTYPE html>
                <html>
                <head>
                    <style>
                        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); color: white; padding: 40px; text-align: center; border-radius: 15px 15px 0 0; }
                        .content { background: #ffffff; padding: 40px; border-radius: 0 0 15px 15px; border: 1px solid #e2e8f0; border-top: none; }
                        .welcome-box { background: #f8fafc; border-left: 4px solid #6366f1; padding: 20px; margin: 25px 0; }
                        .footer { text-align: center; padding: 30px; font-size: 13px; color: #64748b; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h1 style='margin:0;'>Bienvenue a bord !</h1>
                        </div>
                        <div class='content'>
                            <h2 style='color: #1e293b;'>Partenariat officiellement actif</h2>
                            <p>Cher(e) <strong>{$organizationName}</strong>,</p>
                            <p>Nous sommes ravis de vous informer que votre contrat de partenariat (Ref: <strong>{$contractRef}</strong>) a ete examine et <strong>officiellement active</strong> par notre equipe.</p>

                            <div class='welcome-box'>
                                <p style='margin:0;'><strong>Vous etes desormais un partenaire officiel d'EduMatch !</strong> Votre organisation est maintenant visible pour notre communaute d'etudiants et de professeurs.</p>
                            </div>

                            <p>En tant que partenaire, vous pouvez desormais :</p>
                            <ul style='color: #475569;'>
                                <li>Acceder a notre vivier de talents</li>
                                <li>Participer aux evenements officiels EduMatch</li>
                                <li>Mettre en avant votre expertise aupres de milliers d'etudiants</li>
                            </ul>

                            <p>Cordialement,<br><strong style='color: #6366f1;'>L'equipe Partenariat EduMatch</strong></p>
                        </div>
                        <div class='footer'>
                            <p>&copy; 2026 EduMatch. Tous droits reserves.</p>
                        </div>
                    </div>
                </body>
                </html>
            ";

            $mailer->AltBody = "Felicitations {$organizationName} ! Votre contrat de partenariat (Ref: {$contractRef}) est officiellement actif sur EduMatch.";

            return $mailer->send();
        } catch (Exception $e) {
            error_log("MailHelper::sendContractFinalizedEmail failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send contract rejection or suspension email
     */
    public static function sendContractRejectedEmail(string $toEmail, string $organizationName, string $contractRef, string $reason = ''): bool
    {
        try {
            $mailer = self::createMailer();
            $mailer->addAddress($toEmail);

            $reasonBlock = $reason
                ? "<div style='background: #fff5f5; border-left: 4px solid #e53e3e; padding: 15px; margin: 20px 0; font-style: italic;'>
                       <p style='margin:0;'><strong>Note de notre equipe :</strong> {$reason}</p>
                   </div>"
                : "";

            $mailer->Subject = "Mise a jour de votre contrat de partenariat - EduMatch";
            $mailer->Body = "
                <!DOCTYPE html>
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background: #e53e3e; color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                        .content { background: #ffffff; padding: 30px; border-radius: 0 0 10px 10px; border: 1px solid #e2e8f0; border-top: none; }
                        .footer { text-align: center; padding: 20px; font-size: 12px; color: #999; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h1 style='margin:0;'>Mise a jour du contrat</h1>
                        </div>
                        <div class='content'>
                            <h2>Contrat non valide</h2>
                            <p>Cher(e) <strong>{$organizationName}</strong>,</p>
                            <p>Nous vous ecrivons concernant votre contrat de partenariat (Ref: <strong>{$contractRef}</strong>).</p>
                            <p>Apres examen, notre equipe n'est pas en mesure de <strong>valider</strong> ce contrat dans son etat actuel.</p>
                            {$reasonBlock}
                            <p>Si vous pensez qu'il s'agit d'une erreur ou souhaitez discuter des etapes necessaires, veuillez contacter notre equipe support.</p>
                            <p>Cordialement,<br><strong>L'equipe EduMatch</strong></p>
                        </div>
                        <div class='footer'>
                            <p>&copy; 2026 EduMatch. Tous droits reserves.</p>
                        </div>
                    </div>
                </body>
                </html>
            ";

            $mailer->AltBody = "Cher(e) {$organizationName}, votre contrat de partenariat (Ref: {$contractRef}) n'a pas ete valide par notre equipe. Contactez-nous pour plus d'informations.";

            return $mailer->send();
        } catch (Exception $e) {
            error_log("MailHelper::sendContractRejectedEmail failed: " . $e->getMessage());
            return false;
        }
    }

    // ============================================================
    // RESERVATIONS DE SEANCES
    // ============================================================

    /**
     * Envoi a l'encadrant : nouvelle reservation a accepter/refuser
     * Le mail contient 2 liens uniques (token) pour accepter ou refuser
     */
    public static function sendReservationToEncadrant(
        string $emailEncadrant,
        string $nomEncadrant,
        string $nomEtudiant,
        string $dateReservation,
        string $heureDebut,
        string $heureFin,
        string $matiere,
        string $sujet,
        string $mode,
        string $token
    ): bool {
        try {
            $mailer = self::createMailer();
            $mailer->addAddress($emailEncadrant);

            $base = "http://localhost/gestion_users/api/reservation_response.php";
            $acceptLink = $base . "?token=" . urlencode($token) . "&action=accept";
            $refuseLink = $base . "?token=" . urlencode($token) . "&action=refuse";
            $modeLabel = ($mode === 'presentiel') ? 'Presentiel' : 'En ligne';
            $sujetSafe = htmlspecialchars($sujet ?: 'Non precise');

            $mailer->Subject = "Nouvelle reservation - " . $nomEtudiant;
            $mailer->Body = "
                <!DOCTYPE html>
                <html><head><style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(135deg, #6366f1 0%, #8B5CF6 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                    .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                    .info-box { background: white; border-radius: 8px; padding: 15px; margin: 15px 0; }
                    .info-box p { margin: 5px 0; }
                    .btn-accept { display: inline-block; background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 12px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; margin: 5px; }
                    .btn-refuse { display: inline-block; background: linear-gradient(135deg, #ef4444, #dc2626); color: white; padding: 12px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; margin: 5px; }
                    .footer { text-align: center; padding: 20px; font-size: 12px; color: #999; }
                </style></head>
                <body><div class='container'>
                    <div class='header'>
                        <h1>Nouvelle demande de reservation</h1>
                    </div>
                    <div class='content'>
                        <p>Bonjour <strong>{$nomEncadrant}</strong>,</p>
                        <p>L'etudiant <strong>{$nomEtudiant}</strong> souhaite reserver une seance avec vous.</p>
                        <div class='info-box'>
                            <p><strong>📅 Date :</strong> {$dateReservation}</p>
                            <p><strong>⏰ Horaire :</strong> {$heureDebut} - {$heureFin}</p>
                            <p><strong>📚 Matiere :</strong> {$matiere}</p>
                            <p><strong>💬 Sujet :</strong> {$sujetSafe}</p>
                            <p><strong>🌐 Mode :</strong> {$modeLabel}</p>
                        </div>
                        <p>Cliquez sur l'un des boutons ci-dessous pour repondre :</p>
                        <p style='text-align: center;'>
                            <a href='{$acceptLink}' class='btn-accept'>✓ Accepter</a>
                            <a href='{$refuseLink}' class='btn-refuse'>✕ Refuser</a>
                        </p>
                        <p style='font-size: 12px; color: #666; margin-top: 20px;'>
                            Ce lien est unique et ne peut etre utilise qu'une fois. Conservez-le confidentiel.
                        </p>
                        <p>Cordialement,<br><strong>L'equipe EduMatch</strong></p>
                    </div>
                    <div class='footer'><p>&copy; 2026 EduMatch. Tous droits reserves.</p></div>
                </div></body></html>
            ";
            $mailer->AltBody = "Nouvelle reservation de {$nomEtudiant} le {$dateReservation} a {$heureDebut}. Accepter : {$acceptLink} | Refuser : {$refuseLink}";

            return $mailer->send();
        } catch (Exception $e) {
            error_log("MailHelper::sendReservationToEncadrant failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Envoi a l'etudiant : reponse de l'encadrant (acceptee ou refusee)
     */
    public static function sendReservationStatusToEtudiant(
        string $emailEtudiant,
        string $nomEtudiant,
        string $nomEncadrant,
        string $dateReservation,
        string $heureDebut,
        string $heureFin,
        string $matiere,
        string $statut       // 'acceptee' ou 'refusee'
    ): bool {
        try {
            $mailer = self::createMailer();
            $mailer->addAddress($emailEtudiant);

            $isAccepted = ($statut === 'acceptee');
            $title = $isAccepted ? 'Reservation acceptee !' : 'Reservation refusee';
            $color = $isAccepted ? '#10b981' : '#ef4444';
            $icon  = $isAccepted ? '✓' : '✕';
            $message = $isAccepted
                ? "Bonne nouvelle ! Votre demande de seance a ete <strong>acceptee</strong> par {$nomEncadrant}."
                : "Votre demande de seance a ete <strong>refusee</strong> par {$nomEncadrant}. Vous pouvez essayer un autre creneau ou un autre encadrant.";

            $mailer->Subject = $title . " - EduMatch";
            $mailer->Body = "
                <!DOCTYPE html>
                <html><head><style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: {$color}; color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                    .header h1 { margin: 0; }
                    .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                    .info-box { background: white; border-radius: 8px; padding: 15px; margin: 15px 0; }
                    .info-box p { margin: 5px 0; }
                    .footer { text-align: center; padding: 20px; font-size: 12px; color: #999; }
                </style></head>
                <body><div class='container'>
                    <div class='header'>
                        <h1>{$icon} {$title}</h1>
                    </div>
                    <div class='content'>
                        <p>Bonjour <strong>{$nomEtudiant}</strong>,</p>
                        <p>{$message}</p>
                        <div class='info-box'>
                            <p><strong>👤 Encadrant :</strong> {$nomEncadrant}</p>
                            <p><strong>📅 Date :</strong> {$dateReservation}</p>
                            <p><strong>⏰ Horaire :</strong> {$heureDebut} - {$heureFin}</p>
                            <p><strong>📚 Matiere :</strong> {$matiere}</p>
                        </div>
                        <p>Vous pouvez consulter toutes vos reservations sur votre tableau de bord EduMatch.</p>
                        <p>Cordialement,<br><strong>L'equipe EduMatch</strong></p>
                    </div>
                    <div class='footer'><p>&copy; 2026 EduMatch. Tous droits reserves.</p></div>
                </div></body></html>
            ";
            $mailer->AltBody = "{$title} - Encadrant {$nomEncadrant} le {$dateReservation} a {$heureDebut} ({$matiere}).";

            return $mailer->send();
        } catch (Exception $e) {
            error_log("MailHelper::sendReservationStatusToEtudiant failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Envoi a l'encadrant : l'etudiant a annule sa reservation
     */
    public static function sendCancellationToEncadrant(
        string $emailEncadrant,
        string $nomEncadrant,
        string $nomEtudiant,
        string $dateReservation,
        string $heureDebut,
        string $matiere
    ): bool {
        try {
            $mailer = self::createMailer();
            $mailer->addAddress($emailEncadrant);

            $mailer->Subject = "Reservation annulee - EduMatch";
            $mailer->Body = "
                <!DOCTYPE html>
                <html><head><style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #f59e0b; color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                    .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                    .info-box { background: white; border-radius: 8px; padding: 15px; margin: 15px 0; }
                    .footer { text-align: center; padding: 20px; font-size: 12px; color: #999; }
                </style></head>
                <body><div class='container'>
                    <div class='header'><h1>Reservation annulee</h1></div>
                    <div class='content'>
                        <p>Bonjour <strong>{$nomEncadrant}</strong>,</p>
                        <p>L'etudiant <strong>{$nomEtudiant}</strong> a annule sa reservation prevue avec vous :</p>
                        <div class='info-box'>
                            <p><strong>📅 Date :</strong> {$dateReservation}</p>
                            <p><strong>⏰ Horaire :</strong> {$heureDebut}</p>
                            <p><strong>📚 Matiere :</strong> {$matiere}</p>
                        </div>
                        <p>Ce creneau est de nouveau disponible.</p>
                        <p>Cordialement,<br><strong>L'equipe EduMatch</strong></p>
                    </div>
                    <div class='footer'><p>&copy; 2026 EduMatch. Tous droits reserves.</p></div>
                </div></body></html>
            ";
            $mailer->AltBody = "Reservation annulee par {$nomEtudiant} le {$dateReservation} a {$heureDebut} ({$matiere}).";

            return $mailer->send();
        } catch (Exception $e) {
            error_log("MailHelper::sendCancellationToEncadrant failed: " . $e->getMessage());
            return false;
        }
    }
}
