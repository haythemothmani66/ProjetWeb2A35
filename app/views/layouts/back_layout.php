<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Module Événement</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark edumatch-admin-navbar">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>/AdminEvenement/index">Admin Événements</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/AdminCategorie/index">Catégories</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/AdminEvenement/index">Événements</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/AdminParticipation/index">Participations</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/Home/index">Voir le site</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php echo $content; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL; ?>/public/js/validation.js"></script>
</body>
</html>
