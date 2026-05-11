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

$moduleLinks = [
    "Offre d'emploi" => 'index.php?espace=front&module=offreemploi&action=liste',
    'Candidatures' => 'index.php?espace=front&module=candidature&action=liste',
];

$status = (string) ($offre['statut'] ?? '');
$isOpen = $status === 'ouverte';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= htmlspecialchars((string) $offre['titre']) ?> - EduMatch</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Jost:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="assets/fonts/themify-icons.css">
    <link rel="stylesheet" href="assets/css/animate.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .offer-hero {
            padding: 84px 0 28px;
            background:
                linear-gradient(180deg, rgba(255,255,255,.72), rgba(255,255,255,.98)),
                url('assets/img/bg/home-bg3.jpg') center/cover no-repeat;
        }

        .offer-hero-panel {
            background: rgba(255,255,255,.86);
            border: 1px solid rgba(15, 23, 42, .08);
            border-radius: 32px;
            box-shadow: 0 18px 55px rgba(15, 23, 42, .09);
            padding: 30px;
        }

        .offer-kicker {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 9px 14px;
            border-radius: 999px;
            background: rgba(82, 95, 225, .10);
            color: #4250d8;
            font-weight: 700;
            font-size: .9rem;
            margin-bottom: 16px;
        }

        .offer-title {
            font-family: 'Jost', sans-serif;
            font-size: clamp(2rem, 4vw, 3.8rem);
            line-height: 1;
            letter-spacing: -.04em;
            margin: 0 0 14px;
        }

        .offer-subtitle {
            color: #5b6478;
            font-size: 1.02rem;
            line-height: 1.8;
            max-width: 70ch;
            margin-bottom: 20px;
        }

        .offer-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 14px;
        }

        .offer-badges .badge {
            border-radius: 999px;
            padding: .65rem .9rem;
            font-size: .84rem;
        }

        .offer-layout {
            padding: 28px 0 70px;
            background: linear-gradient(180deg, #ffffff 0%, #f7f9ff 100%);
        }

        .content-card,
        .side-card {
            background: #fff;
            border-radius: 28px;
            box-shadow: 0 14px 40px rgba(15, 23, 42, .08);
            border: 1px solid rgba(15, 23, 42, .06);
        }

        .content-card {
            padding: 28px;
        }

        .side-card {
            padding: 24px;
            position: sticky;
            top: 24px;
        }

        .section-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: .82rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 12px;
        }

        .info-list {
            display: grid;
            gap: 12px;
            margin-top: 16px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            padding: 12px 14px;
            border-radius: 18px;
            background: #f8faff;
        }

        .info-item strong {
            color: #0b104a;
        }

        .info-item span:last-child {
            color: #5b6478;
            text-align: right;
        }

        .offer-actions {
            display: grid;
            gap: 10px;
            margin-top: 22px;
        }

        .offer-actions .btn {
            border-radius: 14px;
            padding: 12px 16px;
            font-weight: 700;
        }

        .offer-description h4 {
            font-family: 'Jost', sans-serif;
            font-size: 1.3rem;
            margin-bottom: 12px;
        }

        .offer-description p {
            color: #5b6478;
            line-height: 1.9;
            margin-bottom: 0;
        }

        .detail-visual {
            border-radius: 24px;
            overflow: hidden;
            margin-bottom: 18px;
            box-shadow: 0 14px 36px rgba(15, 23, 42, .10);
        }

        @media (max-width: 991.98px) {
            .offer-hero {
                padding-top: 72px;
            }

            .side-card {
                position: static;
                margin-top: 22px;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../partials/navbar.php'; ?>

    <section class="offer-hero">
        <div class="container">
            <div class="offer-hero-panel">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8">
                        <div class="offer-kicker"><i class="fa-solid fa-file-lines"></i> Détails de l'offre</div>
                        <h1 class="offer-title"><?= htmlspecialchars((string) $offre['titre']) ?></h1>
                        <p class="offer-subtitle">
                            Consultez les informations essentielles avant de candidater : mission, compétences attendues, statut et date limite.
                        </p>
                        <div class="offer-badges">
                            <span class="badge <?= $isOpen ? 'bg-success' : 'bg-danger' ?>"><?= htmlspecialchars($status) ?></span>
                            <span class="badge bg-light text-dark">Lieu: <?= htmlspecialchars((string) $offre['lieu']) ?></span>
                            <span class="badge bg-light text-dark">Contrat: <?= htmlspecialchars((string) $offre['typecontrat']) ?></span>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="detail-visual">
                            <img src="assets/img/course/2.png" class="img-fluid" alt="Aperçu offre">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="offer-layout">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="content-card offer-description">
                        <div class="section-label"><i class="fa-solid fa-align-left"></i> Description</div>
                        <h4>Mission du poste</h4>
                        <p><?= nl2br(htmlspecialchars((string) $offre['description'])) ?></p>

                        <hr class="my-4">

                        <div class="section-label"><i class="fa-solid fa-list-check"></i> Compétences</div>
                        <h4>Compétences requises</h4>
                        <p><?= nl2br(htmlspecialchars((string) ($offre['competencesrequises'] ?? 'Non spécifiées'))) ?></p>
                    </div>
                </div>

                <div class="col-lg-4">
                    <aside class="side-card">
                        <div class="section-label"><i class="fa-solid fa-circle-info"></i> Informations</div>
                        <div class="info-list">
                            <div class="info-item"><strong><span class="ti-location-pin me-1"></span>Lieu</strong><span><?= htmlspecialchars((string) $offre['lieu']) ?></span></div>
                            <div class="info-item"><strong><span class="ti-briefcase me-1"></span>Contrat</strong><span><?= htmlspecialchars((string) $offre['typecontrat']) ?></span></div>
                            <div class="info-item"><strong><span class="ti-wallet me-1"></span>Salaire min</strong><span><?= htmlspecialchars((string) ($offre['salairemin'] ?? '-')) ?></span></div>
                            <div class="info-item"><strong><span class="ti-money me-1"></span>Salaire max</strong><span><?= htmlspecialchars((string) ($offre['salairemax'] ?? '-')) ?></span></div>
                            <div class="info-item"><strong><span class="ti-calendar me-1"></span>Date limite</strong><span><?= htmlspecialchars($formatDateTime($offre['datelimite'])) ?></span></div>
                            <div class="info-item"><strong><span class="ti-check-box me-1"></span>Statut</strong><span><?= htmlspecialchars($status) ?></span></div>
                        </div>

                        <div class="offer-actions">
                            <?php if ($isOpen): ?>
                                <a href="index.php?espace=front&module=candidature&action=ajouter&offreid=<?= (int) $offre['id'] ?>" class="btn btn-primary btn-lg">Candidater maintenant</a>
                            <?php else: ?>
                                <div class="alert alert-warning mb-0">Cette offre n'est pas ouverte aux candidatures pour le moment.</div>
                            <?php endif; ?>
                            <a href="index.php?espace=front&module=offreemploi&action=liste" class="btn btn-outline-secondary btn-lg">Retour à la liste</a>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </section>

    <script src="assets/js/jquery-1.12.4.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/wow.min.js"></script>
    <script src="assets/js/scripts.js"></script>
</body>
</html>
