<?php
/**
 * Email Template: Candidature recue (confirmation au candidat apres soumission)
 * Variables: $candidat_nom, $offre_titre, $offre_lieu, $offre_type_contrat
 */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background: #f4f6f9; }
        .container { max-width: 600px; margin: 30px auto; background: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #525fe1, #6366f1); color: #ffffff; padding: 30px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 30px 25px; }
        .greeting { font-size: 16px; margin-bottom: 20px; }
        .offer-card { background: #f8f9ff; border-left: 4px solid #525fe1; padding: 18px; margin: 20px 0; border-radius: 6px; }
        .offer-card h3 { margin: 0 0 12px 0; color: #525fe1; }
        .detail { margin: 8px 0; font-size: 14px; }
        .detail-label { font-weight: bold; color: #444; display: inline-block; min-width: 110px; }
        .next-steps { background: #fff8e1; border-left: 4px solid #ffa000; padding: 16px; margin: 20px 0; border-radius: 6px; font-size: 14px; }
        .footer { background: #f4f6f9; padding: 20px; text-align: center; font-size: 12px; color: #888; }
        .signature { margin-top: 25px; padding-top: 15px; border-top: 1px solid #e0e0e0; color: #666; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Candidature bien recue</h1>
            <p style="margin: 8px 0 0; opacity: .9;">Nous avons enregistre votre dossier</p>
        </div>

        <div class="content">
            <p class="greeting">Bonjour <strong><?= htmlspecialchars($candidat_nom ?? 'Candidat') ?></strong>,</p>

            <p>Nous vous confirmons la bonne reception de votre candidature pour l'offre suivante&nbsp;:</p>

            <div class="offer-card">
                <h3><?= htmlspecialchars($offre_titre ?? 'Offre d\'emploi') ?></h3>
                <?php if (!empty($offre_lieu)): ?>
                    <div class="detail"><span class="detail-label">Lieu :</span> <?= htmlspecialchars($offre_lieu) ?></div>
                <?php endif; ?>
                <?php if (!empty($offre_type_contrat)): ?>
                    <div class="detail"><span class="detail-label">Contrat :</span> <?= htmlspecialchars($offre_type_contrat) ?></div>
                <?php endif; ?>
                <div class="detail"><span class="detail-label">Date de depot :</span> <?= date('d/m/Y H:i') ?></div>
            </div>

            <div class="next-steps">
                <strong>Et apres ?</strong><br>
                Notre equipe va analyser votre profil et reviendra vers vous par email dans les plus brefs delais
                pour vous communiquer la suite donnee a votre candidature.
            </div>

            <p>En attendant, nous vous remercions de l'interet que vous portez a EduMatch.</p>

            <div class="signature">
                Cordialement,<br>
                <strong>L'equipe recrutement EduMatch</strong>
            </div>
        </div>

        <div class="footer">
            Cet email a ete envoye automatiquement, merci de ne pas y repondre directement.<br>
            &copy; <?= date('Y') ?> EduMatch &mdash; Tous droits reserves
        </div>
    </div>
</body>
</html>
