<?php
$offre = $offre ?? [];
$formatDateTime = static function ($value): string {
    $value = trim((string) $value);

    if ($value === '') {
        return '';
    }

    try {
        return (new DateTimeImmutable($value))->format('d/m/Y H:i');
    } catch (Throwable $exception) {
        return $value;
    }
};
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Details offre - Front Office</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Jost:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/fonts/themify-icons.css">
    <link rel="stylesheet" href="../assets/css/animate.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .retour-liste-btn {
            display: inline-block;
            white-space: nowrap;
            margin-top: 12px;
        }
    </style>
</head>
<body>
    <div id="navigation" class="navbar-light bg-faded site-navigation">
        <div class="container-fluid">
            <div class="row">
                <div class="col-20 align-self-center">
                    <div class="site-logo">
                        <a href="index.php?espace=front&module=offreemploi&action=liste"><img src="../assets/img/logo.png" alt="logo"></a>
                    </div>
                </div>
                <div class="col-60 d-flex">
                    <nav id="main-menu">
                        <ul>
                            <li><a href="index.php?espace=front&module=offreemploi&action=liste">Offres d'emploi</a></li>
                            <li><a href="index.php?espace=back&module=offreemploi&action=liste">Back Office</a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <section class="section-top">
        <div class="container">
            <div class="col-lg-10 offset-lg-1 text-center">
                <div class="section-top-title wow fadeInRight" data-wow-duration="1s" data-wow-delay="0.3s" data-wow-offset="0">
                    <h1><?= htmlspecialchars((string) $offre['titre']) ?></h1>
                    <ul>
                        <li><a href="index.php?espace=front&module=offreemploi&action=liste">Liste des offres</a></li>
                        <li> / Details</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section class="section-padding">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="single_course">
                        <h4>Description du poste</h4>
                        <p><?= nl2br(htmlspecialchars((string) $offre['description'])) ?></p>

                        <h4 class="mt-4">Competences requises</h4>
                        <p><?= nl2br(htmlspecialchars((string) ($offre['competencesrequises'] ?? 'Non specifiees'))) ?></p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="single_course">
                        <h4>Informations</h4>
                        <p><span class="ti-location-pin"></span> <strong>Lieu:</strong> <?= htmlspecialchars((string) $offre['lieu']) ?></p>
                        <p><span class="ti-briefcase"></span> <strong>Contrat:</strong> <?= htmlspecialchars((string) $offre['typecontrat']) ?></p>
                        <p><span class="ti-wallet"></span> <strong>Salaire min:</strong> <?= htmlspecialchars((string) ($offre['salairemin'] ?? '-')) ?></p>
                        <p><span class="ti-money"></span> <strong>Salaire max:</strong> <?= htmlspecialchars((string) ($offre['salairemax'] ?? '-')) ?></p>
                        <p><span class="ti-calendar"></span> <strong>Date limite:</strong> <?= htmlspecialchars($formatDateTime($offre['datelimite'])) ?></p>
                        <p><span class="ti-check-box"></span> <strong>Statut:</strong> <?= htmlspecialchars((string) $offre['statut']) ?></p>

                        <?php if (($offre['statut'] ?? '') === 'ouverte'): ?>
                            <a href="index.php?espace=front&module=candidature&action=ajouter&offreid=<?= (int) $offre['id'] ?>" class="btn_one retour-liste-btn">Candidater maintenant</a>
                        <?php else: ?>
                            <div class="alert alert-warning mt-3">Cette offre n'est pas ouverte aux candidatures pour le moment.</div>
                        <?php endif; ?>
                        <a href="index.php?espace=front&module=offreemploi&action=liste" class="btn_one retour-liste-btn">Retour a la liste</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="../assets/js/jquery-1.12.4.min.js"></script>
    <script src="../assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="../assets/js/wow.min.js"></script>
    <script src="../assets/js/scripts.js"></script>
</body>
</html>
