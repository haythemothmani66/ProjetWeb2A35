<?php
/**
 * Vue : Coach IA Adaptatif - Dashboard etudiant
 * Reçoit : $snapshot (array), $previousPlans (array)
 */
$snapshot = $snapshot ?? [];
$previousPlans = $previousPlans ?? [];
$competenceAverages = $snapshot['competence_averages'] ?? [];
$nbDevoirs = (int) ($snapshot['nb_devoirs'] ?? 0);
$nbCorrections = (int) ($snapshot['nb_corrections'] ?? 0);
$noteMoyenne = (float) ($snapshot['note_moyenne'] ?? 0);

$errMsg = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Mon Coach IA - EduMatch</title>
    <link rel="stylesheet" href="/gestion_users/assets/bootstrap/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Jost:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="/gestion_users/assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="/gestion_users/assets/fonts/themify-icons.css">
    <link rel="stylesheet" href="/gestion_users/assets/css/jquery-simple-mobilemenu.css">
    <link rel="stylesheet" href="/gestion_users/assets/css/style.css">
    <style>
        body { background: linear-gradient(145deg, #f5f3ff 0%, #eef0ff 50%, #e0e7ff 100%); min-height: 100vh; }
        .coach-hero {
            padding: 80px 0 40px;
            background: linear-gradient(135deg, #525fe1 0%, #3b47c9 50%, #1e1b4b 100%);
            color: white;
            position: relative;
            overflow: hidden;
        }
        .coach-hero::before, .coach-hero::after {
            content: ''; position: absolute; border-radius: 50%; pointer-events: none;
        }
        .coach-hero::before {
            top: -150px; right: -100px; width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(0,212,255,0.25), transparent 60%);
        }
        .coach-hero::after {
            bottom: -150px; left: -100px; width: 450px; height: 450px;
            background: radial-gradient(circle, rgba(168,85,247,0.2), transparent 60%);
        }
        .coach-hero-content { position: relative; z-index: 2; }
        .coach-badge {
            display: inline-flex; align-items: center; gap: 10px;
            background: rgba(255,255,255,0.15); backdrop-filter: blur(10px);
            color: white; padding: 8px 16px; border-radius: 50px;
            font-size: 0.85rem; font-weight: 600; letter-spacing: 1px;
            border: 1px solid rgba(255,255,255,0.2); margin-bottom: 1.5rem;
        }
        .stat-card {
            background: white; border-radius: 18px;
            box-shadow: 0 10px 30px rgba(82,95,225,0.1);
            padding: 1.5rem; border: 1px solid rgba(82,95,225,0.1);
            transition: all 0.3s ease;
        }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 18px 40px rgba(82,95,225,0.18); }
        .stat-card .stat-icon {
            width: 50px; height: 50px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 1.3rem; margin-bottom: 12px;
        }
        .stat-card .stat-value { font-size: 2rem; font-weight: 800; color: #0b104a; line-height: 1; }
        .stat-card .stat-label { color: #64748b; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; margin-top: 6px; }

        .section-card {
            background: white; border-radius: 22px;
            box-shadow: 0 12px 35px rgba(82,95,225,0.08);
            padding: 2rem; margin-bottom: 2rem;
            border: 1px solid rgba(82,95,225,0.08);
        }
        .section-title {
            font-family: 'Jost', sans-serif;
            font-weight: 700; color: #0b104a;
            margin-bottom: 1.25rem; font-size: 1.4rem;
        }
        .section-title i { color: #525fe1; margin-right: 10px; }

        .btn-generate {
            background: linear-gradient(135deg, #525fe1 0%, #3b47c9 100%);
            color: white; border: none; padding: 1rem 2.5rem;
            border-radius: 50px; font-weight: 700; font-size: 1.05rem;
            box-shadow: 0 12px 30px rgba(82,95,225,0.35);
            transition: all 0.3s ease; cursor: pointer;
            display: inline-flex; align-items: center; gap: 10px;
        }
        .btn-generate:hover { transform: translateY(-3px); box-shadow: 0 18px 40px rgba(82,95,225,0.45); filter: brightness(1.1); }
        .btn-generate:disabled { opacity: 0.6; cursor: wait; transform: none; }

        .empty-state { text-align: center; padding: 3rem 1rem; color: #64748b; }
        .empty-state i { font-size: 4rem; color: #cbd5e1; margin-bottom: 1rem; }

        .plan-history-card {
            background: linear-gradient(135deg, rgba(82,95,225,0.05), rgba(0,212,255,0.05));
            border-left: 4px solid #525fe1; border-radius: 14px;
            padding: 1.25rem; margin-bottom: 0.75rem; transition: all 0.3s ease;
        }
        .plan-history-card:hover { transform: translateX(4px); box-shadow: 0 6px 20px rgba(82,95,225,0.12); }
        .badge-reco-high { background: linear-gradient(135deg, #10b981, #059669); color: white; }
        .badge-reco-medium { background: linear-gradient(135deg, #f59e0b, #f97316); color: white; }
        .badge-reco-low { background: linear-gradient(135deg, #ef4444, #b91c1c); color: white; }
        .badge-status { padding: 4px 10px; border-radius: 50px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge-status-active { background: rgba(82,95,225,0.15); color: #3b47c9; }
        .badge-status-completed { background: rgba(16,185,129,0.15); color: #059669; }
        .badge-status-archived { background: rgba(100,116,139,0.15); color: #64748b; }

        .info-row { display: flex; gap: 1.5rem; flex-wrap: wrap; }
        .info-pill {
            background: #f5f3ff; padding: 8px 14px; border-radius: 50px;
            font-size: 0.85rem; color: #525fe1; font-weight: 600;
            border: 1px solid rgba(82,95,225,0.2);
        }

        #radarChart { max-height: 350px; }

        @keyframes pulse-gradient {
            0%, 100% { filter: brightness(1); }
            50% { filter: brightness(1.15); }
        }
        .btn-generate.loading { animation: pulse-gradient 1.5s ease-in-out infinite; }

        .alert-coach { border-radius: 14px; border: none; padding: 1rem 1.5rem; }
        .alert-coach-danger { background: rgba(239,68,68,0.1); color: #b91c1c; border-left: 4px solid #ef4444; }
        .alert-coach-warning { background: rgba(245,158,11,0.1); color: #92400e; border-left: 4px solid #f59e0b; }
    </style>
</head>
<body data-spy="scroll" data-offset="80">
    <div class="preloaders"><span class="loader"></span></div>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/gestion_users/view/template/_navbar.php'; ?>

    <!-- HERO -->
    <section class="coach-hero">
        <div class="container coach-hero-content">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <div class="coach-badge">
                        <i class="fas fa-robot"></i> COACH IA ADAPTATIF
                    </div>
                    <h1 class="fw-bold mb-3" style="font-family:'Jost',sans-serif; font-size:clamp(1.8rem,4vw,3rem); letter-spacing:-0.02em;">
                        Ton plan d'apprentissage personnalise
                    </h1>
                    <p class="lead mb-0" style="opacity:0.9; max-width: 700px;">
                        Notre IA analyse ton historique de devoirs et corrections pour generer un plan de progression sur <strong>7 jours</strong>,
                        adapte a tes competences faibles et fortes.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <i class="fas fa-brain" style="font-size:6rem; opacity:0.25;"></i>
                </div>
            </div>
        </div>
    </section>

    <div class="container my-5">

        <?php if ($errMsg === 'no_devoirs'): ?>
            <div class="alert alert-coach alert-coach-warning mb-4">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Aucun devoir trouve.</strong> Tu dois d'abord soumettre au moins un devoir pour que l'IA puisse analyser ton profil et generer ton plan personnalise.
                <a href="/gestion_users/view/frontoffice/submit.php#form-devoir-section" class="alert-link ms-2">Soumettre un devoir</a>
            </div>
        <?php elseif ($errMsg !== ''): ?>
            <div class="alert alert-coach alert-coach-danger mb-4">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Erreur :</strong> <?= htmlspecialchars(urldecode($errMsg)) ?>
            </div>
        <?php endif; ?>

        <!-- KPIs -->
        <div class="row g-4 mb-4">
            <div class="col-md-3 col-sm-6">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #525fe1, #3b47c9);"><i class="fas fa-file-alt"></i></div>
                    <div class="stat-value"><?= $nbDevoirs ?></div>
                    <div class="stat-label">Devoirs soumis</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #00D4FF, #525fe1);"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-value"><?= $nbCorrections ?></div>
                    <div class="stat-label">Corrections recues</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #10b981, #059669);"><i class="fas fa-star"></i></div>
                    <div class="stat-value"><?= number_format($noteMoyenne, 1) ?>/20</div>
                    <div class="stat-label">Note moyenne</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #a855f7, #8b5cf6);"><i class="fas fa-bullseye"></i></div>
                    <div class="stat-value"><?= count($competenceAverages) ?></div>
                    <div class="stat-label">Competences analysees</div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- BILAN COMPETENCES (RADAR) -->
            <div class="col-lg-7">
                <div class="section-card">
                    <h3 class="section-title"><i class="fas fa-radar"></i>Bilan de tes competences</h3>
                    <?php if (count($competenceAverages) >= 3): ?>
                        <canvas id="radarChart"></canvas>
                    <?php elseif (count($competenceAverages) > 0): ?>
                        <p class="text-secondary mb-3">Trop peu de competences evaluees pour afficher le radar. Voici ce que nous avons :</p>
                        <?php foreach ($competenceAverages as $comp => $note): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2 p-3" style="background:#f5f3ff;border-radius:10px;">
                                <strong><?= htmlspecialchars($comp) ?></strong>
                                <span class="info-pill"><?= number_format($note, 1) ?>/20</span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-chart-pie"></i>
                            <p>Aucune competence evaluee pour le moment.<br><small>Les competences apparaissent ici une fois que tes devoirs sont corriges.</small></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- INFOS ETUDIANT + GENERER PLAN -->
            <div class="col-lg-5">
                <div class="section-card text-center" style="background: linear-gradient(135deg, #525fe1, #3b47c9); color:white;">
                    <i class="fas fa-magic" style="font-size:3rem; opacity:0.85; margin-bottom:1rem;"></i>
                    <h3 class="fw-bold mb-3" style="font-family:'Jost',sans-serif;">Generer mon plan IA</h3>
                    <p style="opacity:0.92;">L'IA Groq llama-3.3-70b analyse tes <?= $nbDevoirs ?> devoir<?= $nbDevoirs > 1 ? 's' : '' ?> et te propose un plan personnalise sur 7 jours.</p>
                    <form method="post" action="/gestion_users/controller/CoachController.php?action=generate" id="coach-form">
                        <button type="submit" id="coach-btn" class="btn-generate" <?= $nbDevoirs === 0 ? 'disabled' : '' ?>>
                            <i class="fas fa-bolt"></i>
                            <span id="coach-btn-text">Generer mon plan</span>
                        </button>
                    </form>
                    <?php if ($nbDevoirs === 0): ?>
                        <p class="mt-3" style="font-size:0.85rem; opacity:0.8;">
                            <i class="fas fa-info-circle"></i> Soumets d'abord un devoir pour activer le bouton.
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Profil rapide -->
                <div class="section-card">
                    <h3 class="section-title"><i class="fas fa-user-graduate"></i>Ton profil</h3>
                    <div class="mb-3">
                        <strong>Sentiment dominant (30j) :</strong>
                        <?php
                            $sent = $snapshot['sentiment_dominant'] ?? 'neutre';
                            $sentColors = ['positif'=>'#10b981','negatif'=>'#ef4444','stress'=>'#f59e0b','frustration'=>'#dc2626','confusion'=>'#a855f7','neutre'=>'#64748b'];
                            $sentColor = $sentColors[$sent] ?? '#64748b';
                        ?>
                        <span class="badge ms-2" style="background:<?= $sentColor ?>;color:white;padding:6px 12px;border-radius:50px;"><?= htmlspecialchars($sent) ?></span>
                    </div>
                    <?php if (!empty($snapshot['types_erreurs'])): ?>
                        <div class="mb-3">
                            <strong>Erreurs frequentes :</strong>
                            <div class="info-row mt-2">
                                <?php foreach (array_slice($snapshot['types_erreurs'], 0, 3) as $err): ?>
                                    <span class="info-pill"><?= htmlspecialchars($err) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- HISTORIQUE PLANS -->
        <?php if (!empty($previousPlans)): ?>
            <div class="section-card mt-4">
                <h3 class="section-title"><i class="fas fa-history"></i>Tes plans precedents (<?= count($previousPlans) ?>)</h3>
                <?php foreach ($previousPlans as $pp): ?>
                    <div class="plan-history-card d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                <strong>Plan du <?= (new DateTime($pp['generated_at']))->format('d/m/Y a H\hi') ?></strong>
                                <span class="badge badge-status badge-status-<?= htmlspecialchars($pp['status']) ?>"><?= htmlspecialchars($pp['status']) ?></span>
                                <span class="badge badge-reco-<?= htmlspecialchars($pp['recommendation_level']) ?>" style="padding:4px 10px;border-radius:50px;font-size:0.72rem;font-weight:700;">
                                    Score IA : <?= (int)$pp['overall_score'] ?>/100
                                </span>
                            </div>
                            <small class="text-secondary">
                                <?= (int)$pp['nb_devoirs_analyses'] ?> devoir(s) analyse(s) ·
                                Note moyenne : <?= number_format((float)($pp['note_moyenne'] ?? 0), 1) ?>/20
                            </small>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="/gestion_users/controller/CoachController.php?action=plan&id=<?= (int)$pp['id'] ?>" class="btn btn-sm" style="background:#525fe1;color:white;border-radius:50px;padding:8px 18px;">
                                <i class="fas fa-eye"></i> Voir
                            </a>
                            <form method="post" action="/gestion_users/controller/CoachController.php?action=delete" onsubmit="return confirm('Supprimer ce plan ?');" style="display:inline;">
                                <input type="hidden" name="id" value="<?= (int)$pp['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger" style="border-radius:50px;padding:8px 18px;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>

    <!-- FOOTER EduMatch -->
    <footer class="modern-footer bg-dark text-white py-5">
      <div class="container">
        <div class="row">
          <div class="col-lg-4 col-md-6 mb-4">
            <a href="/gestion_users/view/template/index.php" class="text-decoration-none">
              <img src="/gestion_users/assets/img/logo.png" alt="EduMatch Logo" class="mb-3" style="height: 50px;">
            </a>
            <p class="mt-3 text-light opacity-75">Plateforme intelligente de mise en relation des etudiants avec des professeurs experts.</p>
          </div>
          <div class="col-lg-2 col-md-6 mb-4">
            <h5 class="fw-bold mb-3">Plateforme</h5>
            <ul class="list-unstyled">
              <li class="mb-2"><a href="/gestion_users/view/template/index.php" class="text-light text-decoration-none">Accueil</a></li>
              <li class="mb-2"><a href="/gestion_users/view/template/profil.php" class="text-light text-decoration-none">Mon Profil</a></li>
            </ul>
          </div>
          <div class="col-lg-2 col-md-6 mb-4">
            <h5 class="fw-bold mb-3">Apprentissage</h5>
            <ul class="list-unstyled">
              <li class="mb-2"><a href="/gestion_users/view/frontoffice/submit.php" class="text-light text-decoration-none">Devoirs</a></li>
              <li class="mb-2"><a href="/gestion_users/controller/QuizController.php?espace=front&resource=courses&action=index" class="text-light text-decoration-none">Cours & Quiz</a></li>
            </ul>
          </div>
          <div class="col-lg-4 col-md-6 mb-4">
            <h5 class="fw-bold mb-3">Coordonnees</h5>
            <p class="mb-2"><i class="fas fa-envelope me-2"></i>edumatch@gmail.com</p>
          </div>
        </div>
        <hr class="my-4 opacity-25">
        <p class="mb-0 text-light opacity-75 text-center">&copy; 2026 EduMatch. Tous droits reserves.</p>
      </div>
    </footer>

    <script src="/gestion_users/assets/js/jquery-1.12.4.min.js"></script>
    <script src="/gestion_users/assets/js/jquery-simple-mobilemenu.js"></script>
    <script src="/gestion_users/assets/js/scripts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

    <script>
        // Loading state au submit
        document.getElementById('coach-form').addEventListener('submit', function() {
            const btn = document.getElementById('coach-btn');
            const txt = document.getElementById('coach-btn-text');
            btn.disabled = true;
            btn.classList.add('loading');
            txt.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generation en cours (5-15s)...';
        });

        // Radar Chart competences
        <?php if (count($competenceAverages) >= 3): ?>
        const ctx = document.getElementById('radarChart');
        if (ctx && typeof Chart !== 'undefined') {
            const labels = <?= json_encode(array_keys($competenceAverages)) ?>;
            const data = <?= json_encode(array_values($competenceAverages)) ?>;
            new Chart(ctx, {
                type: 'radar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Maitrise (note moyenne /20)',
                        data: data,
                        backgroundColor: 'rgba(82,95,225,0.2)',
                        borderColor: '#525fe1',
                        borderWidth: 2,
                        pointBackgroundColor: '#525fe1',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: '#525fe1',
                        pointRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        r: {
                            beginAtZero: true,
                            max: 20,
                            ticks: { stepSize: 5, color: '#64748b', font: { size: 11 } },
                            pointLabels: { color: '#0b104a', font: { size: 12, weight: 600 } },
                            grid: { color: 'rgba(82,95,225,0.1)' },
                            angleLines: { color: 'rgba(82,95,225,0.15)' }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: { label: (c) => `Note : ${c.parsed.r.toFixed(1)}/20` }
                        }
                    }
                }
            });
        }
        <?php endif; ?>
    </script>
</body>
</html>
