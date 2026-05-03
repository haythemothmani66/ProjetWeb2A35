<?php
$candidature = $candidature ?? [];
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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Details de candidature</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700;800&family=Jost:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --ink: #12202f;
            --muted: #587089;
            --primary: #0f6e8b;
            --primary-dark: #0a4f66;
            --accent: #f3a712;
            --card: rgba(255,255,255,.92);
            --shadow: 0 16px 40px rgba(16, 33, 52, .12);
        }
        body {
            margin: 0;
            font-family: 'DM Sans', sans-serif;
            color: var(--ink);
            background:
                radial-gradient(circle at 12% 12%, rgba(15,110,139,.18), transparent 28%),
                linear-gradient(145deg, #eef4f9 0%, #f9fbfd 45%, #edf4fb 100%);
        }
        .topbar {
            background: rgba(10, 20, 30, .82);
            backdrop-filter: blur(10px);
            color: #fff;
        }
        .brand-dot {
            width: 42px; height: 42px; border-radius: 14px;
            background: linear-gradient(135deg, #f3a712, #ff7a59);
            display:flex; align-items:center; justify-content:center;
            font-weight: 800; color: #111;
        }
        .box {
            border-radius: 24px;
            background: var(--card);
            box-shadow: var(--shadow);
            border: 1px solid rgba(18, 32, 47, .08);
            overflow: hidden;
        }
        .hero-head {
            padding: 28px;
            background: linear-gradient(135deg, rgba(15,110,139,.08), rgba(243,167,18,.08));
            border-bottom: 1px solid rgba(18,32,47,.08);
        }
        .title {
            font-family: 'Jost', sans-serif;
            font-weight: 800;
            letter-spacing: -.02em;
        }
        .info-grid { display:grid; grid-template-columns: repeat(auto-fit,minmax(220px,1fr)); gap: 14px; }
        .info-card {
            border-radius: 18px;
            padding: 16px;
            background: #fff;
            border: 1px solid rgba(18,32,47,.08);
        }
        .label { color: var(--muted); font-size: .78rem; text-transform: uppercase; letter-spacing: .05em; font-weight: 700; }
        .value { margin-top: 6px; font-weight: 600; color: #1c2f43; }
        .badge-soft {
            display:inline-flex; align-items:center; padding: 6px 11px; border-radius: 999px;
            font-size: .75rem; font-weight: 800; text-transform: uppercase; letter-spacing: .05em;
            background: rgba(31,146,84,.14); color: #15784a;
        }
        .muted { color: var(--muted); }
        .btn-primary-job {
            border: 0; border-radius: 999px; padding: 12px 18px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: #fff; font-weight: 700; text-decoration:none;
        }
        .btn-secondary-job {
            border: 0; border-radius: 999px; padding: 12px 18px;
            background: #e9eff4; color:#173042; font-weight: 700; text-decoration:none;
        }
    </style>
</head>
<body>
    <div class="topbar py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <div class="brand-dot">PW</div>
                <div>
                    <div class="fw-bold">ProjetWeb2A35</div>
                    <small class="text-white-50">Details de candidature</small>
                </div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="index.php?espace=front&module=candidature&action=liste" class="btn-secondary-job">Offres ouvertes</a>
                <a href="index.php?espace=back&module=candidature&action=liste" class="btn-secondary-job">Administration</a>
            </div>
        </div>
    </div>

    <main class="container py-5">
        <div class="box">
            <div class="hero-head">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                    <div>
                        <span class="badge-soft"><?= htmlspecialchars((string) $candidature['statut']) ?></span>
                        <h1 class="title h2 mt-3 mb-2">Candidature #<?= (int) $candidature['id'] ?></h1>
                        <p class="muted mb-0">Deposee le <?= htmlspecialchars($formatDateTime($candidature['datecandidature'])) ?></p>
                    </div>
                    <a href="index.php?espace=front&module=candidature&action=liste" class="btn-primary-job">Retour aux offres</a>
                </div>
            </div>

            <div class="p-4 p-lg-5">
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="info-grid">
                            <div class="info-card">
                                <div class="label">Offre</div>
                                <div class="value"><?= htmlspecialchars((string) ($candidature['offre_titre'] ?? 'Aucune offre')) ?></div>
                            </div>
                            <div class="info-card">
                                <div class="label">Candidat</div>
                                <div class="value"><?= htmlspecialchars(trim((string) ($candidature['prenom'] ?? '') . ' ' . (string) ($candidature['nom'] ?? ''))) ?></div>
                            </div>
                            <div class="info-card">
                                <div class="label">Email</div>
                                <div class="value"><?= htmlspecialchars((string) $candidature['email']) ?></div>
                            </div>
                            <div class="info-card">
                                <div class="label">CV</div>
                                <div class="value">
                                    <?php if (!empty($candidature['cv_source']) && $candidature['cv_source'] === 'upload'): ?>
                                        <a href="<?= htmlspecialchars((string) $candidature['cvurl']) ?>" target="_blank" rel="noopener">
                                            <?= htmlspecialchars((string) ($candidature['cv_original_name'] ?? 'Telecharger le CV')) ?>
                                        </a>
                                    <?php elseif (!empty($candidature['cvurl'])): ?>
                                        <a href="<?= htmlspecialchars((string) $candidature['cvurl']) ?>" target="_blank" rel="noopener">Ouvrir le CV</a>
                                    <?php else: ?>
                                        <span class="text-muted">Aucun CV fourni</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 info-card">
                            <div class="label mb-2">Lettre de motivation</div>
                            <div class="value" style="font-weight:400; line-height:1.8; white-space:pre-wrap;"><?= htmlspecialchars((string) $candidature['lettremotivation']) ?></div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="info-card h-100">
                            <div class="label">Statut actuel</div>
                            <div class="value mb-3"><?= htmlspecialchars((string) $candidature['statut']) ?></div>

                            <div class="label">Date de candidature</div>
                            <div class="value mb-3"><?= htmlspecialchars($formatDateTime($candidature['datecandidature'])) ?></div>

                            <div class="label">Date de reponse</div>
                            <div class="value mb-4"><?= htmlspecialchars(isset($candidature['datereponse']) ? $formatDateTime($candidature['datereponse']) : 'En attente') ?></div>

                            <a href="index.php?espace=front&module=candidature&action=ajouter&offreid=<?= (int) $candidature['offreid'] ?>" class="btn-primary-job w-100 d-block text-center">Repostuler a cette offre</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
