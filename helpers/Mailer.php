<?php

declare(strict_types=1);

$autoloadPath = __DIR__ . '/../../vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Mailer Service - Wrapper around PHPMailer for sending emails
 */
class Mailer
{
    private $mail;
    private $config;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../config/mail.php';
        $this->mail = new PHPMailer(true);
        $this->configureSMTP();
    }

    /**
     * Configure SMTP settings from config/mail.php
     */
    private function configureSMTP(): void
    {
        try {
            $this->mail->isSMTP();
            $this->mail->Host = $this->config['host'];
            $this->mail->Port = $this->config['port'];
            $this->mail->SMTPSecure = $this->config['secure'];
            $this->mail->SMTPAuth = true;
            $this->mail->Username = $this->config['username'];
            $this->mail->Password = $this->config['password'];
            $this->mail->SMTPDebug = $this->config['debug'] ? 2 : 0;
            $this->mail->CharSet = 'UTF-8';
        } catch (Exception $e) {
            throw new Exception("SMTP Configuration Error: " . $e->getMessage());
        }
    }

    /**
     * Send an email
     * 
     * @param string $to         Recipient email address
     * @param string $subject    Email subject
     * @param string $htmlBody   HTML email body
     * @param string|null $altBody Plain text alternative (optional)
     * @return bool              True if sent successfully
     */
    public function send(string $to, string $subject, string $htmlBody, ?string $altBody = null): bool
    {
        try {
            // Clear recipients (for reuse)
            $this->mail->clearAddresses();
            $this->mail->clearReplyTos();
            $this->mail->clearAttachments();

            // Set sender
            $this->mail->setFrom($this->config['from_email'], $this->config['from_name']);

            // Set reply-to if configured
            if (!empty($this->config['reply_to'])) {
                $this->mail->addReplyTo($this->config['reply_to']);
            }

            // Set recipient
            $this->mail->addAddress($to);

            // Set subject and body
            $this->mail->Subject = $subject;
            $this->mail->isHTML(true);
            $this->mail->Body = $htmlBody;

            // Set plain text alternative if provided
            if ($altBody) {
                $this->mail->AltBody = $altBody;
            }

            // Send
            return $this->mail->send();
        } catch (Exception $e) {
            error_log("Email sending error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send email with template
     * 
     * @param string $to          Recipient email
     * @param string $subject     Email subject
     * @param string $templatePath Path to email template file
     * @param array $data         Data to pass to template
     * @return bool               True if sent
     */
    public function sendFromTemplate(string $to, string $subject, string $templatePath, array $data = []): bool
    {
        if (!file_exists($templatePath)) {
            error_log("Email template not found: $templatePath");
            return false;
        }

        // Extract data variables for template
        extract($data);

        // Capture template output
        ob_start();
        include $templatePath;
        $htmlBody = ob_get_clean();

        return $this->send($to, $subject, $htmlBody);
    }

    /**
     * Get the underlying PHPMailer instance (advanced usage)
     */
    public function getMailer(): PHPMailer
    {
        return $this->mail;
    }
}
