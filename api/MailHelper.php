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
}
