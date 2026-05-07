<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Require Composer autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

class MailHelper
{
    private static ?PHPMailer $mailer = null;
    
    private static function getMailer(): PHPMailer
    {
        if (self::$mailer === null) {
            self::$mailer = new PHPMailer(true);
            
            // Server settings - UPDATE THESE WITH YOUR ACTUAL CREDENTIALS
            self::$mailer->isSMTP();
            // self::$mailer->SMTPDebug = 2; // Uncomment for detailed debug
            self::$mailer->Host       = 'smtp.gmail.com';  
            self::$mailer->SMTPAuth   = true;
            self::$mailer->Username   = 'chahdtissaoui29@gmail.com';  // Your email
            self::$mailer->Password   = 'lnainfkoigwpvggw';     // Your app password
            self::$mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            self::$mailer->Port       = 587;
            
            // Sender
            self::$mailer->setFrom('noreply@edumatch.com', 'EduMatch Team');
            self::$mailer->addReplyTo('contact@edumatch.com', 'EduMatch Support');
            
            // Content
            self::$mailer->isHTML(true);
            self::$mailer->CharSet = 'UTF-8';
        }
        
        return self::$mailer;
    }
    
    /**
     * Send partnership approval email with contract link
     */
    public static function sendApprovalEmail(string $toEmail, string $organizationName): bool
    {
        try {
            $mailer = self::getMailer();
            $mailer->clearAddresses();
            $mailer->addAddress($toEmail);
            
            $contractLink = "http://localhost/edumatch/edumatch/view/frontoffice/contract.html";
            
            $mailer->Subject = "🎉 Partnership Approved - EduMatch";
            $mailer->Body = "
                <!DOCTYPE html>
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background: linear-gradient(135deg, #ff7f50 0%, #ff6347 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                        .button { display: inline-block; padding: 12px 30px; background: linear-gradient(135deg, #ff7f50 0%, #ff6347 100%); color: white; text-decoration: none; border-radius: 8px; margin-top: 20px; }
                        .footer { text-align: center; padding: 20px; font-size: 12px; color: #999; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h1>Congratulations {$organizationName}!</h1>
                        </div>
                        <div class='content'>
                            <h2>Your Partnership Has Been Approved! 🎉</h2>
                            <p>Dear {$organizationName},</p>
                            <p>We are pleased to inform you that your partnership application with <strong>EduMatch</strong> has been <strong>approved</strong>.</p>
                            <p>We are excited to welcome you to our growing network of partners dedicated to shaping the future of education together.</p>
                            <p>To proceed with the partnership, please click the button below to complete the partnership contract:</p>
                            <p style='text-align: center;'>
                                <a href='{$contractLink}' class='button'>Complete Partnership Contract →</a>
                            </p>
                            <p>If you have any questions, please don't hesitate to contact our partnership team.</p>
                            <p>Best regards,<br><strong>The EduMatch Team</strong></p>
                        </div>
                        <div class='footer'>
                            <p>&copy; 2026 EduMatch. All rights reserved.</p>
                        </div>
                    </div>
                </body>
                </html>
            ";
            
            $mailer->AltBody = strip_tags(str_replace(['<br>', '</p>'], "\n", $mailer->Body));
            
            return $mailer->send();
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send partnership rejection email with standard message
     */
    public static function sendRejectionEmail(string $toEmail, string $organizationName): bool
    {
        try {
            $mailer = self::getMailer();
            $mailer->clearAddresses();
            $mailer->addAddress($toEmail);
            
            $mailer->Subject = "Update on Your Partnership Application - EduMatch";
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
                            <h1>Update on Your Application</h1>
                        </div>
                        <div class='content'>
                            <h2>Application Status: Not Approved</h2>
                            <p>Dear {$organizationName},</p>
                            <p>Thank you for your interest in partnering with <strong>EduMatch</strong>.</p>
                            <p>After careful review of your application, we regret to inform you that we are unable to approve your partnership request at this time as it does not fully align with our current partnership policies and criteria.</p>
                            
                            <p>We sincerely apologize for any inconvenience this may have caused. We appreciate your interest in EduMatch and encourage you to review our partnership guidelines and reapply in the future.</p>
                            
                            <p>If you have any questions or would like to discuss this decision further, please don't hesitate to contact our partnership team.</p>
                            
                            <p>Best regards,<br><strong>The EduMatch Team</strong></p>
                        </div>
                        <div class='footer'>
                            <p>&copy; 2026 EduMatch. All rights reserved.</p>
                        </div>
                    </div>
                </body>
                </html>
            ";
            
            $mailer->AltBody = strip_tags(str_replace(['<br>', '</p>'], "\n", $mailer->Body));
            
            return $mailer->send();
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send pending confirmation email (when application is submitted)
     */
    public static function sendPendingEmail(string $toEmail, string $organizationName): bool
    {
        try {
            $mailer = self::getMailer();
            $mailer->clearAddresses();
            $mailer->addAddress($toEmail);
            
            $mailer->Subject = "Partnership Application Received - EduMatch";
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
                            <h1>Application Received!</h1>
                        </div>
                        <div class='content'>
                            <h2>Thank You for Your Interest, {$organizationName}!</h2>
                            <p>We have successfully received your partnership application for <strong>EduMatch</strong>.</p>
                            <p>Our team will carefully review your application and get back to you within 48 hours.</p>
                            <p><strong>What happens next?</strong></p>
                            <ul>
                                <li>✅ Our partnership team reviews your application</li>
                                <li>📧 You will receive an email with the decision</li>
                                <li>🎉 If approved, you'll get a link to complete the partnership contract</li>
                            </ul>
                            <p>You can check your application status at any time by visiting our Partnership Page and using the 'Check Status' feature.</p>
                            <p>Best regards,<br><strong>The EduMatch Team</strong></p>
                        </div>
                        <div class='footer'>
                            <p>&copy; 2026 EduMatch. All rights reserved.</p>
                        </div>
                    </div>
                </body>
                </html>
            ";
            
            $mailer->AltBody = strip_tags(str_replace(['<br>', '</p>'], "\n", $mailer->Body));
            
            return $mailer->send();
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send final contract activation email
     */
    public static function sendContractFinalizedEmail(string $toEmail, string $organizationName, string $contractRef): bool
    {
        try {
            $mailer = self::getMailer();
            $mailer->clearAddresses();
            $mailer->addAddress($toEmail);
            
            $mailer->Subject = "🤝 Official Partnership Confirmed - EduMatch";
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
                            <h1 style='margin:0;'>Welcome Aboard!</h1>
                        </div>
                        <div class='content'>
                            <h2 style='color: #1e293b;'>Partnership Officially Active 🚀</h2>
                            <p>Dear <strong>{$organizationName}</strong>,</p>
                            <p>We are thrilled to inform you that your partnership contract (Ref: <strong>{$contractRef}</strong>) has been reviewed and <strong>officially activated</strong> by our team.</p>
                            
                            <div class='welcome-box'>
                                <p style='margin:0;'><strong>You are now an official partner of EduMatch!</strong> Your organization is now visible to our students and professors community.</p>
                            </div>

                            <p>As a partner, you can now:</p>
                            <ul style='color: #475569;'>
                                <li>Access our exclusive talent pool</li>
                                <li>Participate in official EduMatch events</li>
                                <li>Showcase your expertise to thousands of students</li>
                            </ul>

                            <p>We look forward to a fruitful collaboration and the amazing things we will achieve together.</p>
                            
                            <p style='margin-top: 30px;'>Best regards,<br><strong style='color: #6366f1;'>The EduMatch Partnership Team</strong></p>
                        </div>
                        <div class='footer'>
                            <p>&copy; 2026 EduMatch Platform. Empowering Education Connections.</p>
                        </div>
                    </div>
                </body>
                </html>
            ";
            
            $mailer->AltBody = strip_tags(str_replace(['<br>', '</p>', '</div>'], "\n", $mailer->Body));
            
            return $mailer->send();
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send contract rejection or suspension email
     */
    public static function sendContractRejectedEmail(string $toEmail, string $organizationName, string $contractRef, string $reason = ''): bool
    {
        try {
            $mailer = self::getMailer();
            $mailer->clearAddresses();
            $mailer->addAddress($toEmail);
            
            $mailer->Subject = "⚠️ Update Regarding Your Partnership Contract - EduMatch";
            $mailer->Body = "
                <!DOCTYPE html>
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background: #e53e3e; color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                        .content { background: #ffffff; padding: 30px; border-radius: 0 0 10px 10px; border: 1px solid #e2e8f0; border-top: none; }
                        .reason-box { background: #fff5f5; border-left: 4px solid #e53e3e; padding: 15px; margin: 20px 0; font-style: italic; }
                        .footer { text-align: center; padding: 20px; font-size: 12px; color: #999; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h1 style='margin:0;'>Contract Status Update</h1>
                        </div>
                        <div class='content'>
                            <h2>Contract Not Validated</h2>
                            <p>Dear <strong>{$organizationName}</strong>,</p>
                            <p>We are writing to inform you of a status update regarding your partnership contract (Ref: <strong>{$contractRef}</strong>).</p>
                            
                            <p>After a formal review, our team is currently <strong>unable to validate</strong> this contract in its current state.</p>

                            " . ($reason ? "
                            <div class='reason-box'>
                                <p style='margin:0;'><strong>Note from our team:</strong> {$reason}</p>
                            </div>
                            " : "") . "

                            <p>If you believe this is a mistake or if you would like to discuss the steps necessary to re-activate your partnership, please contact our support team at <a href='mailto:contact@edumatch.com'>contact@edumatch.com</a>.</p>
                            
                            <p>Thank you for your understanding.</p>
                            <p>Best regards,<br><strong>The EduMatch Team</strong></p>
                        </div>
                        <div class='footer'>
                            <p>&copy; 2026 EduMatch. All rights reserved.</p>
                        </div>
                    </div>
                </body>
                </html>
            ";
            
            $mailer->AltBody = strip_tags(str_replace(['<br>', '</p>', '</div>'], "\n", $mailer->Body));
            
            return $mailer->send();
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }
}