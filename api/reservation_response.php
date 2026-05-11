<?php
/**
 * Endpoint public (mais securise par token unique 64 chars)
 * Permet a un encadrant d'accepter ou refuser une reservation depuis l'email
 *
 * URL: /gestion_users/api/reservation_response.php?token=XXX&action=accept|refuse
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/MailHelper.php';

$token  = trim((string)($_GET['token']  ?? ''));
$action = strtolower(trim((string)($_GET['action'] ?? '')));

$baseUrl = '/gestion_users';

function renderPage(string $title, string $iconClass, string $color, string $message, string $details = '', string $baseUrl = '/gestion_users'): void
{
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= htmlspecialchars($title) ?> - EduMatch</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
        <style>
            body { font-family: 'Public Sans', Arial, sans-serif; background: linear-gradient(135deg, #f8fafc 0%, #eef2f7 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; margin: 0; }
            .card-result { background: white; border-radius: 1.5rem; box-shadow: 0 20px 60px rgba(15,23,42,0.1); padding: 3rem 2.5rem; text-align: center; max-width: 540px; width: 100%; }
            .icon-circle { width: 90px; height: 90px; border-radius: 50%; background: <?= $color ?>; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1.5rem; }
            .icon-circle i { font-size: 2.5rem; color: white; }
            h1 { color: #0b104a; font-weight: 800; margin-bottom: 0.5rem; }
            .details-box { background: #f8fafc; border-radius: 0.75rem; padding: 1rem 1.25rem; margin-top: 1.5rem; text-align: left; font-size: 0.95rem; }
            .btn-home { display: inline-block; margin-top: 1.5rem; background: linear-gradient(135deg, #6366f1, #8B5CF6); color: white; padding: 0.75rem 2rem; border-radius: 50px; text-decoration: none; font-weight: 600; transition: transform 0.2s ease; }
            .btn-home:hover { transform: translateY(-2px); color: white; }
        </style>
    </head>
    <body>
        <div class="card-result">
            <div class="icon-circle">
                <i class="fas <?= htmlspecialchars($iconClass) ?>"></i>
            </div>
            <h1><?= htmlspecialchars($title) ?></h1>
            <p class="text-muted"><?= $message /* HTML allowed */ ?></p>
            <?php if ($details): ?>
                <div class="details-box"><?= $details ?></div>
            <?php endif; ?>
            <a href="<?= htmlspecialchars($baseUrl) ?>/view/template/index.php" class="btn-home">
                <i class="fas fa-home me-2"></i>Retour a l'accueil
            </a>
        </div>
    </body>
    </html>
    <?php
}

if ($token === '' || !preg_match('/^[a-f0-9]{64}$/', $token)) {
    renderPage('Lien invalide', 'fa-exclamation-triangle', '#ef4444', 'Le lien que vous avez utilise est invalide ou mal forme.', '', $baseUrl);
    exit;
}

if (!in_array($action, ['accept', 'refuse'], true)) {
    renderPage('Action invalide', 'fa-exclamation-triangle', '#ef4444', 'L\'action demandee n\'est pas valide.', '', $baseUrl);
    exit;
}

try {
    $pdo = Config::getConnexion();

    // Recuperer la reservation + adresse encadrant (utile si presentiel)
    $stmt = $pdo->prepare("
        SELECT r.*,
               ue.nom AS encadrant_nom, ue.prenom AS encadrant_prenom, ue.email AS encadrant_email,
               pe.adresse AS encadrant_adresse,
               us.nom AS etudiant_nom, us.prenom AS etudiant_prenom, us.email AS etudiant_email
        FROM reservations r
        INNER JOIN user ue ON ue.id = r.id_encadrant
        LEFT JOIN profil pe ON pe.user_id = ue.id
        INNER JOIN user us ON us.id = r.id_etudiant
        WHERE r.token_action = ?
        LIMIT 1
    ");
    $stmt->execute([$token]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$res) {
        renderPage('Reservation introuvable', 'fa-exclamation-triangle', '#ef4444', 'Aucune reservation ne correspond a ce lien. Il a peut-etre deja ete utilise.', '', $baseUrl);
        exit;
    }

    if ($res['statut'] !== 'en_attente') {
        $statutLabel = ucfirst($res['statut']);
        renderPage(
            'Deja traitee',
            'fa-info-circle',
            '#f59e0b',
            'Cette reservation a deja ete traitee. Statut actuel : <strong>' . htmlspecialchars($statutLabel) . '</strong>.',
            '<strong>Etudiant :</strong> ' . htmlspecialchars($res['etudiant_prenom'] . ' ' . $res['etudiant_nom']) . '<br>'
            . '<strong>Date :</strong> ' . htmlspecialchars($res['date_reservation']) . ' a ' . htmlspecialchars(substr($res['heure_debut'], 0, 5)) . '<br>'
            . '<strong>Matiere :</strong> ' . htmlspecialchars($res['matiere']),
            $baseUrl
        );
        exit;
    }

    // Mettre a jour le statut
    $newStatut = ($action === 'accept') ? 'acceptee' : 'refusee';
    $upd = $pdo->prepare("UPDATE reservations SET statut = ?, date_reponse = NOW() WHERE id_reservation = ?");
    $upd->execute([$newStatut, $res['id_reservation']]);

    // Envoyer mail a l'etudiant (avec adresse si presentiel + acceptee)
    MailHelper::sendReservationStatusToEtudiant(
        $res['etudiant_email'],
        trim($res['etudiant_prenom'] . ' ' . $res['etudiant_nom']),
        trim($res['encadrant_prenom'] . ' ' . $res['encadrant_nom']),
        $res['date_reservation'],
        substr($res['heure_debut'], 0, 5),
        substr($res['heure_fin'], 0, 5),
        $res['matiere'],
        $newStatut,
        $res['mode'] ?? null,
        $res['encadrant_adresse'] ?? null
    );

    // Page de confirmation pour l'encadrant
    if ($action === 'accept') {
        renderPage(
            'Reservation acceptee',
            'fa-check-circle',
            '#10b981',
            'Vous avez <strong>accepte</strong> la reservation. L\'etudiant a ete informe par email.',
            '<strong>Etudiant :</strong> ' . htmlspecialchars($res['etudiant_prenom'] . ' ' . $res['etudiant_nom']) . '<br>'
            . '<strong>Date :</strong> ' . htmlspecialchars($res['date_reservation']) . ' a ' . htmlspecialchars(substr($res['heure_debut'], 0, 5)) . '<br>'
            . '<strong>Matiere :</strong> ' . htmlspecialchars($res['matiere']),
            $baseUrl
        );
    } else {
        renderPage(
            'Reservation refusee',
            'fa-times-circle',
            '#ef4444',
            'Vous avez <strong>refuse</strong> la reservation. L\'etudiant a ete informe par email.',
            '<strong>Etudiant :</strong> ' . htmlspecialchars($res['etudiant_prenom'] . ' ' . $res['etudiant_nom']) . '<br>'
            . '<strong>Date :</strong> ' . htmlspecialchars($res['date_reservation']) . ' a ' . htmlspecialchars(substr($res['heure_debut'], 0, 5)) . '<br>'
            . '<strong>Matiere :</strong> ' . htmlspecialchars($res['matiere']),
            $baseUrl
        );
    }
} catch (Throwable $e) {
    error_log('reservation_response.php error: ' . $e->getMessage());
    renderPage('Erreur', 'fa-exclamation-triangle', '#ef4444', 'Une erreur est survenue lors du traitement. Veuillez reessayer ou contacter l\'administrateur.', '', $baseUrl);
}
