<?php
/**
 * Vue : Coach IA - Affichage du plan personnalise sur 7 jours
 * Recoit : $coachPlan (row coach_plans), $plan (decoded), $snapshot (decoded), $success
 */
$coachPlan = $coachPlan ?? [];
$plan = $plan ?? [];
$snapshot = $snapshot ?? [];
$success = $success ?? false;

$score = (int) ($plan['overall_score'] ?? 50);
$recoLevel = (string) ($plan['recommendation_level'] ?? 'medium');
$summary = (string) ($plan['summary'] ?? '');
$weakAreas = $plan['weak_areas'] ?? [];
$strongAreas = $plan['strong_areas'] ?? [];
$days = $plan['days'] ?? [];

// Couleur du score
$scoreColor = '#525fe1';
if ($score >= 75) { $scoreColor = '#10b981'; }
elseif ($score >= 50) { $scoreColor = '#525fe1'; }
elseif ($score >= 30) { $scoreColor = '#f59e0b'; }
else { $scoreColor = '#ef4444'; }

$recoBadgeStyle = [
    'high'   => ['bg' => 'linear-gradient(135deg,#10b981,#059669)', 'text' => 'Excellent profil'],
    'medium' => ['bg' => 'linear-gradient(135deg,#f59e0b,#f97316)', 'text' => 'En progression'],
    'low'    => ['bg' => 'linear-gradient(135deg,#ef4444,#b91c1c)', 'text' => 'A renforcer'],
];
$reco = $recoBadgeStyle[$recoLevel] ?? $recoBadgeStyle['medium'];

$dayColors = [
    1 => ['#525fe1', '#3b47c9'],
    2 => ['#00D4FF', '#525fe1'],
    3 => ['#a855f7', '#8b5cf6'],
    4 => ['#10b981', '#059669'],
    5 => ['#f59e0b', '#f97316'],
    6 => ['#ef4444', '#b91c1c'],
    7 => ['#525fe1', '#1e1b4b'],
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Mon plan de progression - Coach IA EduMatch</title>
    <link rel="stylesheet" href="/gestion_users/assets/bootstrap/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700;800&family=Jost:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="/gestion_users/assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="/gestion_users/assets/fonts/themify-icons.css">
    <link rel="stylesheet" href="/gestion_users/assets/css/jquery-simple-mobilemenu.css">
    <link rel="stylesheet" href="/gestion_users/assets/css/style.css">
    <style>
        body { background: linear-gradient(145deg,#f5f3ff 0%,#eef0ff 50%,#e0e7ff 100%); min-height: 100vh; }

        .plan-hero {
            padding: 70px 0 50px;
            background: linear-gradient(135deg, #525fe1 0%, #3b47c9 50%, #1e1b4b 100%);
            color: white;
            position: relative;
            overflow: hidden;
        }
        .plan-hero::before, .plan-hero::after {
            content: ''; position: absolute; border-radius: 50%; pointer-events: none;
        }
        .plan-hero::before {
            top: -120px; right: -80px; width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(0,212,255,0.22), transparent 60%);
        }
        .plan-hero::after {
            bottom: -100px; left: -100px; width: 350px; height: 350px;
            background: radial-gradient(circle, rgba(168,85,247,0.2), transparent 60%);
        }
        .plan-hero-content { position: relative; z-index: 2; }

        .score-circle {
            width: 140px; height: 140px; border-radius: 50%;
            background: white;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.25);
            border: 6px solid <?= $scoreColor ?>;
        }
        .score-circle .num {
            font-size: 2.4rem; font-weight: 800; color: <?= $scoreColor ?>;
            line-height: 1; font-family: 'Jost', sans-serif;
        }
        .score-circle .lbl { font-size: 0.7rem; color: #64748b; letter-spacing: 1px; text-transform: uppercase; font-weight: 700; margin-top: 4px; }

        .reco-pill {
            background: <?= $reco['bg'] ?>;
            color: white; padding: 8px 20px; border-radius: 50px;
            font-weight: 700; display: inline-block; font-size: 0.9rem;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }

        .summary-card {
            background: white; border-radius: 22px;
            box-shadow: 0 14px 40px rgba(82,95,225,0.1);
            padding: 2rem; margin-bottom: 2rem;
            border: 1px solid rgba(82,95,225,0.08);
        }

        .area-pill {
            display: inline-block; padding: 8px 14px;
            border-radius: 50px; font-size: 0.85rem; font-weight: 600;
            margin: 4px;
        }
        .weak-pill { background: rgba(239,68,68,0.12); color: #b91c1c; border: 1px solid rgba(239,68,68,0.25); }
        .strong-pill { background: rgba(16,185,129,0.12); color: #059669; border: 1px solid rgba(16,185,129,0.25); }

        /* Timeline 7 jours */
        .timeline-container {
            position: relative;
            padding-left: 60px;
        }
        .timeline-container::before {
            content: ''; position: absolute;
            left: 24px; top: 20px; bottom: 20px;
            width: 4px;
            background: linear-gradient(180deg,#525fe1 0%, #00D4FF 50%, #a855f7 100%);
            border-radius: 2px;
        }
        .day-card {
            background: white; border-radius: 20px;
            box-shadow: 0 12px 35px rgba(82,95,225,0.10);
            padding: 1.75rem; margin-bottom: 1.5rem;
            position: relative;
            border-left: 5px solid;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .day-card:hover { transform: translateY(-4px); box-shadow: 0 20px 50px rgba(82,95,225,0.18); }
        .day-marker {
            position: absolute; left: -54px; top: 1.5rem;
            width: 50px; height: 50px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 800; font-size: 1.1rem;
            box-shadow: 0 8px 20px rgba(82,95,225,0.3);
            border: 4px solid white;
        }
        .day-card h4 {
            font-family: 'Jost', sans-serif; font-weight: 700;
            color: #0b104a; margin: 0 0 0.5rem; font-size: 1.3rem;
        }
        .day-objective {
            background: linear-gradient(135deg, rgba(82,95,225,0.05), rgba(0,212,255,0.05));
            border-left: 3px solid #525fe1;
            padding: 12px 16px; border-radius: 10px;
            margin: 1rem 0; font-size: 0.95rem; color: #0b104a;
        }
        .day-actions, .day-resources {
            margin-top: 1rem;
        }
        .day-actions li, .day-resources li {
            padding: 8px 0 8px 28px;
            position: relative;
            font-size: 0.92rem; color: #4b5563;
        }
        .day-actions li::before {
            content: '\\f00c'; font-family: 'Font Awesome 6 Free'; font-weight: 900;
            position: absolute; left: 0; top: 9px;
            color: #10b981; font-size: 0.85rem;
        }
        .day-resources li::before {
            content: '\\f02d'; font-family: 'Font Awesome 6 Free'; font-weight: 900;
            position: absolute; left: 0; top: 9px;
            color: #525fe1; font-size: 0.85rem;
        }
        .motivation {
            margin-top: 1.25rem; padding: 14px 18px;
            background: linear-gradient(135deg, #fff8e1, #fef3c7);
            border-radius: 12px;
            color: #92400e; font-style: italic;
            border-left: 3px solid #f59e0b;
            font-size: 0.92rem;
        }
        .motivation::before {
            content: '\\f10d'; font-family: 'Font Awesome 6 Free'; font-weight: 900;
            color: #f59e0b; margin-right: 8px;
        }

        .duration-badge {
            display: inline-block; padding: 4px 10px;
            background: rgba(82,95,225,0.12); color: #3b47c9;
            border-radius: 50px; font-size: 0.78rem; font-weight: 700;
            margin-left: 8px;
        }

        .actions-bar {
            position: sticky; bottom: 0; z-index: 100;
            background: rgba(255,255,255,0.95); backdrop-filter: blur(15px);
            border-top: 1px solid rgba(82,95,225,0.1);
            padding: 1rem; margin: 2rem -15px -15px;
            display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;
        }
        .btn-action {
            border: none; padding: 12px 28px; border-radius: 50px;
            font-weight: 700; cursor: pointer;
            transition: all 0.3s ease; text-decoration: none;
            display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-back { background: white; color: #525fe1; border: 2px solid #525fe1; }
        .btn-back:hover { background: #525fe1; color: white; }
        .btn-complete { background: linear-gradient(135deg, #10b981, #059669); color: white; box-shadow: 0 8px 20px rgba(16,185,129,0.3); }
        .btn-complete:hover { transform: translateY(-2px); filter: brightness(1.1); color: white; }

        .alert-success-coach {
            background: linear-gradient(135deg, rgba(16,185,129,0.1), rgba(82,95,225,0.05));
            border-left: 4px solid #10b981;
            color: #059669; border-radius: 14px;
            padding: 1rem 1.5rem; margin-bottom: 2rem;
        }
    </style>
</head>
<body data-spy="scroll" data-offset="80">
    <div class="preloaders"><span class="loader"></span></div>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/gestion_users/view/template/_navbar.php'; ?>

    <!-- HERO -->
    <section class="plan-hero">
        <div class="container plan-hero-content">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <div style="display:inline-flex;align-items:center;gap:10px;background:rgba(255,255,255,0.15);backdrop-filter:blur(10px);color:white;padding:8px 16px;border-radius:50px;font-size:0.85rem;font-weight:600;letter-spacing:1px;border:1px solid rgba(255,255,255,0.2);margin-bottom:1.25rem;">
                        <i class="fas fa-route"></i> PLAN PERSONNALISE 7 JOURS
                    </div>
                    <h1 class="fw-bold mb-3" style="font-family:'Jost',sans-serif;font-size:clamp(1.6rem,3.5vw,2.6rem);">
                        Ton parcours de progression
                    </h1>
                    <p class="mb-3" style="opacity:0.92;font-size:1.05rem;">
                        Genere par IA Groq llama-3.3-70b a partir de <strong><?= (int)$coachPlan['nb_devoirs_analyses'] ?> devoir(s)</strong> analyse(s)
                        - Note moyenne : <strong><?= number_format((float)$coachPlan['note_moyenne'], 1) ?>/20</strong>
                    </p>
                    <span class="reco-pill"><i class="fas fa-award me-1"></i> <?= htmlspecialchars($reco['text']) ?></span>
                </div>
                <div class="col-lg-4 text-center">
                    <div class="score-circle mx-auto">
                        <div class="num"><?= $score ?></div>
                        <div class="lbl">SCORE IA / 100</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="container my-5">

        <?php if ($success): ?>
            <div class="alert-success-coach">
                <strong><i class="fas fa-check-circle me-2"></i>Plan genere avec succes !</strong>
                L'IA a analyse ton profil et cree un parcours d'apprentissage personnalise sur 7 jours.
            </div>
        <?php endif; ?>

        <!-- Summary + areas -->
        <div class="summary-card">
            <h3 style="font-family:'Jost',sans-serif;color:#0b104a;font-weight:700;margin-bottom:1rem;"><i class="fas fa-quote-left" style="color:#525fe1;margin-right:10px;"></i>Analyse de ton profil</h3>
            <p style="font-size:1.05rem;line-height:1.7;color:#374151;"><?= htmlspecialchars($summary) ?></p>

            <div class="row mt-4">
                <?php if (!empty($weakAreas)): ?>
                <div class="col-md-6 mb-3">
                    <h5 style="color:#b91c1c;font-weight:700;font-size:0.95rem;text-transform:uppercase;letter-spacing:1px;">
                        <i class="fas fa-exclamation-triangle me-2"></i>Points a renforcer
                    </h5>
                    <?php foreach ($weakAreas as $w): ?>
                        <span class="area-pill weak-pill"><?= htmlspecialchars($w) ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <?php if (!empty($strongAreas)): ?>
                <div class="col-md-6 mb-3">
                    <h5 style="color:#059669;font-weight:700;font-size:0.95rem;text-transform:uppercase;letter-spacing:1px;">
                        <i class="fas fa-star me-2"></i>Tes points forts
                    </h5>
                    <?php foreach ($strongAreas as $s): ?>
                        <span class="area-pill strong-pill"><?= htmlspecialchars($s) ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Timeline 7 jours -->
        <h2 style="font-family:'Jost',sans-serif;font-weight:800;color:#0b104a;margin-bottom:1.5rem;">
            <i class="fas fa-calendar-alt" style="color:#525fe1;margin-right:10px;"></i>Ton plan sur 7 jours
        </h2>

        <div class="timeline-container">
            <?php foreach ($days as $idx => $day):
                $dayNum = (int) ($day['day'] ?? ($idx + 1));
                $colors = $dayColors[$dayNum] ?? ['#525fe1', '#3b47c9'];
                $duration = (int) ($day['duration_minutes'] ?? 60);
                $theme = (string) ($day['theme'] ?? 'Jour ' . $dayNum);
                $objective = (string) ($day['objective'] ?? '');
                $actions = $day['actions'] ?? [];
                $resources = $day['resources'] ?? [];
                $motivation = (string) ($day['motivation'] ?? '');
            ?>
                <div class="day-card" style="border-left-color: <?= $colors[0] ?>;">
                    <div class="day-marker" style="background: linear-gradient(135deg, <?= $colors[0] ?>, <?= $colors[1] ?>);">
                        J<?= $dayNum ?>
                    </div>
                    <h4>
                        <?= htmlspecialchars($theme) ?>
                        <span class="duration-badge"><i class="fas fa-clock me-1"></i><?= $duration ?> min</span>
                    </h4>
                    <?php if ($objective !== ''): ?>
                        <div class="day-objective">
                            <strong><i class="fas fa-bullseye me-2"></i>Objectif :</strong> <?= htmlspecialchars($objective) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($actions)): ?>
                        <div class="day-actions">
                            <strong style="color:#10b981;font-size:0.9rem;text-transform:uppercase;letter-spacing:1px;">Actions concretes</strong>
                            <ul class="list-unstyled mt-2">
                                <?php foreach ($actions as $a): ?>
                                    <li><?= htmlspecialchars((string) $a) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($resources)): ?>
                        <div class="day-resources">
                            <strong style="color:#525fe1;font-size:0.9rem;text-transform:uppercase;letter-spacing:1px;">Ressources recommandees</strong>
                            <ul class="list-unstyled mt-2">
                                <?php foreach ($resources as $r): ?>
                                    <li><?= htmlspecialchars((string) $r) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if ($motivation !== ''): ?>
                        <div class="motivation"><?= htmlspecialchars($motivation) ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Actions bar -->
        <div class="actions-bar">
            <a href="/gestion_users/controller/CoachController.php?action=dashboard" class="btn-action btn-back">
                <i class="fas fa-arrow-left"></i> Retour au tableau de bord
            </a>
            <?php if ($coachPlan['status'] === 'active'): ?>
                <form method="post" action="/gestion_users/controller/CoachController.php?action=mark_complete" style="display:inline;">
                    <input type="hidden" name="id" value="<?= (int)$coachPlan['id'] ?>">
                    <button type="submit" class="btn-action btn-complete">
                        <i class="fas fa-check-double"></i> J'ai termine ce plan
                    </button>
                </form>
            <?php elseif ($coachPlan['status'] === 'completed'): ?>
                <span class="btn-action" style="background:rgba(16,185,129,0.15);color:#059669;cursor:default;">
                    <i class="fas fa-trophy"></i> Plan complete le <?= (new DateTime($coachPlan['completed_at'] ?? $coachPlan['generated_at']))->format('d/m/Y') ?>
                </span>
            <?php endif; ?>
        </div>

    </div>

    <!-- FOOTER -->
    <footer class="modern-footer bg-dark text-white py-5">
      <div class="container">
        <div class="row">
          <div class="col-md-6 mb-3">
            <img src="/gestion_users/assets/img/logo.png" alt="EduMatch Logo" style="height: 45px;">
            <p class="mt-2 text-light opacity-75 small">Coach IA - Powered by Groq Llama 3.3 70B</p>
          </div>
          <div class="col-md-6 mb-3 text-md-end">
            <p class="mb-0 text-light opacity-75">&copy; 2026 EduMatch. Tous droits reserves.</p>
          </div>
        </div>
      </div>
    </footer>

    <script src="/gestion_users/assets/js/jquery-1.12.4.min.js"></script>
    <script src="/gestion_users/assets/js/jquery-simple-mobilemenu.js"></script>
    <script src="/gestion_users/assets/js/scripts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
