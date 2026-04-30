# SMTP Email Configuration Guide

## Quick Start

The email notification system for candidature accept/refusal is now implemented. Follow these steps to activate it:

### 1. Configure Your Email Provider

Edit `config/mail.php` and replace the placeholders:

#### Option A: Gmail (Recommended for Testing)

```php
'host' => 'smtp.gmail.com',
'port' => 587,
'secure' => 'tls',
'username' => 'your-email@gmail.com',          // Your Gmail address
'password' => 'your-16-char-app-password',    // Generate here: https://myaccount.google.com/apppasswords
'from_email' => 'your-email@gmail.com',
'from_name' => 'EduMatch - Recrutement',
```

**To get a Gmail App Password:**

1. Go to https://myaccount.google.com/apppasswords
2. Select "Mail" and "Windows Computer"
3. Copy the 16-character password
4. Paste it in `config/mail.php` as the `password` value

#### Option B: SendGrid

```php
'host' => 'smtp.sendgrid.net',
'port' => 587,
'secure' => 'tls',
'username' => 'apikey',
'password' => 'SG.xxx...xxx',  // Your SendGrid API key
'from_email' => 'noreply@yourdomain.com',
'from_name' => 'EduMatch - Recrutement',
```

#### Option C: Other SMTP Providers

Contact your email provider for SMTP settings (Host, Port, Security, Username, Password).

### 2. Test the Configuration

To verify your SMTP is working, run this simple test:

```php
<?php
require_once __DIR__ . '/helpers/Mailer.php';

try {
    $mailer = new Mailer();
    $result = $mailer->send(
        'your-test-email@example.com',
        'Test Email from EduMatch',
        '<h1>Success!</h1><p>Your SMTP is configured correctly.</p>'
    );

    if ($result) {
        echo "✓ Email sent successfully!";
    } else {
        echo "✗ Email failed to send. Check server logs.";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage();
}
?>
```

Save as `test-smtp.php` in the project root and access it in your browser.

### 3. How It Works

When you accept or refuse a candidature in the Back Office:

1. **Accept:** The admin fills in:
   - Interview Date
   - Interview Time
   - Mode (Présentiel or Distanciel)
   - Location (if applicable)

   → Email is sent with all details

2. **Refuse:** The admin can provide:
   - Rejection Reason (optional)

   → Email is sent with the reason

### 4. Email Templates

Templates are stored in `views/emails/`:

- `candidature_accept.php` — Email sent when accepting
- `candidature_refuse.php` — Email sent when refusing

You can customize these templates directly. Variables available:

- Accept: `$candidat_nom`, `$offre_titre`, `$date_entretien`, `$heure_entretien`, `$mode_entretien`, `$lieu_entretien`
- Refuse: `$candidat_nom`, `$offre_titre`, `$motif_refus`

### 5. Troubleshooting

**"SMTP Connection failed"**

- Check host, port, and security settings
- Verify username and password
- For Gmail, ensure you generated an App Password (not your regular password)

**"Email sent but not received"**

- Check spam/junk folder
- Verify recipient email is correct
- Check server logs for errors

**"PHPMailer not found"**

- Run: `composer require phpmailer/phpmailer`
- Ensure `vendor/` folder exists

## Advanced Features

### Debug Mode

Set `'debug' => true` in `config/mail.php` to see detailed SMTP logs.

### Custom Email Design

Modify the HTML in `views/emails/` templates to match your branding.

### Additional Notifications

You can extend the `Mailer` class or call it from other controllers to send emails in different workflows.
