<?php
/**
 * Check if the logged-in user has been blocked (statut=0).
 * Include this file AFTER session_start() and database.php in front-office pages.
 * If blocked: sets etat=offline, destroys session, redirects to login with message.
 */
if (!empty($_SESSION['user_id'])) {
    $__db = Config::getConnexion();
    $__st = $__db->prepare("SELECT statut FROM user WHERE id = ? LIMIT 1");
    $__st->execute([$_SESSION['user_id']]);
    $__row = $__st->fetch();

    if ($__row && (int)$__row['statut'] === 0) {
        $__db->prepare("UPDATE user SET etat = 'offline' WHERE id = ?")->execute([$_SESSION['user_id']]);
        session_destroy();
        session_start();
        $_SESSION['errors'] = ["Votre compte a ete bloque par l'administrateur. Veuillez le contacter pour plus d'informations."];
        header('Location: /gestion_users/view/template/sign-in.php');
        exit;
    }
    unset($__db, $__st, $__row);
}
