<?php
/**
 * Email Template: Candidature Accepted
 * Variables: $candidat_nom, $offre_titre, $date_entretien, $heure_entretien, $mode_entretien, $lieu_entretien, $contact_email, $contact_phone
 */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #28a745; color: white; padding: 20px; text-align: center; border-radius: 5px; }
        .content { background-color: #f9f9f9; padding: 20px; margin: 20px 0; border-left: 4px solid #28a745; }
        .detail { margin: 10px 0; }
        .detail-label { font-weight: bold; color: #28a745; }
        .footer { font-size: 12px; color: #999; text-align: center; margin-top: 30px; }
        .button { display: inline-block; background-color: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Félicitations !</h1>
        </div>

        <div class="content">
            <p>Bonjour <strong><?php echo htmlspecialchars($candidat_nom ?? 'Candidat'); ?></strong>,</p>

            <p>Nous avons le plaisir de vous informer que votre candidature pour le poste de <strong><?php echo htmlspecialchars($offre_titre ?? '(sans titre)'); ?></strong> a été <strong>acceptée</strong> !</p>

            <p>Nous aimerions vous rencontrer lors d'un entretien. Voici les détails :</p>

            <div class="detail">
                <span class="detail-label">📅 Date & Heure :</span><br>
                <?php 
                    $date_formatted = !empty($date_entretien) ? date('d/m/Y', strtotime($date_entretien)) : '(à confirmer)';
                    $heure_formatted = !empty($heure_entretien) ? htmlspecialchars($heure_entretien) : '';
                    echo $date_formatted . ($heure_formatted ? ' à ' . $heure_formatted : '');
                ?>
            </div>

            <div class="detail">
                <span class="detail-label">📍 Lieu :</span><br>
                <?php echo !empty($lieu_entretien) ? htmlspecialchars($lieu_entretien) : '(à confirmer - Présentiel ou Distanciel)'; ?>
            </div>

            <div class="detail">
                <span class="detail-label">💻 Mode :</span><br>
                <?php echo !empty($mode_entretien) ? ucfirst(htmlspecialchars($mode_entretien)) : '(à confirmer)'; ?>
            </div>

            <?php if (!empty($contact_email) || !empty($contact_phone)): ?>
            <div class="detail">
                <span class="detail-label">📞 Contact :</span><br>
                <?php if (!empty($contact_email)): ?>
                    Email : <a href="mailto:<?php echo htmlspecialchars($contact_email); ?>"><?php echo htmlspecialchars($contact_email); ?></a><br>
                <?php endif; ?>
                <?php if (!empty($contact_phone)): ?>
                    Téléphone : <?php echo htmlspecialchars($contact_phone); ?><br>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <p style="margin-top: 20px; font-style: italic;">
                Si vous avez des questions, n'hésitez pas à nous contacter.
            </p>

            <p>
                Cordialement,<br>
                <strong>L'équipe EduMatch</strong>
            </p>
        </div>

        <div class="footer">
            <p>Cet e-mail a été envoyé automatiquement. Veuillez ne pas répondre à cet e-mail.</p>
        </div>
    </div>
</body>
</html>
