<?php
/**
 * Email Template: Candidature Refused
 * Variables: $candidat_nom, $offre_titre, $motif_refus
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
        .header { background-color: #dc3545; color: white; padding: 20px; text-align: center; border-radius: 5px; }
        .content { background-color: #f9f9f9; padding: 20px; margin: 20px 0; border-left: 4px solid #dc3545; }
        .detail { margin: 10px 0; }
        .detail-label { font-weight: bold; color: #dc3545; }
        .footer { font-size: 12px; color: #999; text-align: center; margin-top: 30px; }
        .encouragement { background-color: #e8f4f8; padding: 15px; border-radius: 5px; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Résultat de Votre Candidature</h1>
        </div>

        <div class="content">
            <p>Bonjour <strong><?php echo htmlspecialchars($candidat_nom ?? 'Candidat'); ?></strong>,</p>

            <p>Merci beaucoup d'avoir postulé pour le poste de <strong><?php echo htmlspecialchars($offre_titre ?? '(sans titre)'); ?></strong>.</p>

            <p style="color: #dc3545; font-weight: bold;">Malheureusement, nous regrettons de vous informer que votre candidature n'a pas été retenue pour cette position.</p>

            <?php if (!empty($motif_refus)): ?>
            <div class="detail">
                <span class="detail-label">Raison :</span><br>
                <?php echo nl2br(htmlspecialchars($motif_refus)); ?>
            </div>
            <?php endif; ?>

            <div class="encouragement">
                <p><strong>Nous vous encourageons à continuer votre recherche !</strong></p>
                <p>Votre profil pourrait correspondre à d'autres offres à venir. N'hésitez pas à consulter régulièrement nos annonces.</p>
            </div>

            <p style="margin-top: 20px;">
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
