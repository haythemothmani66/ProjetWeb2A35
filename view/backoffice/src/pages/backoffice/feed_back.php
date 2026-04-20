<?php
require_once __DIR__ . '/../../../../../config/database.php';
$conn = getDBConnection();

$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$pageMarker = '/view/backoffice/src/pages/backoffice/';
$appBaseUrl = '';
$markerPos = strpos($scriptName, $pageMarker);

if ($markerPos !== false) {
    $appBaseUrl = substr($scriptName, 0, $markerPos);
} else {
    $appBaseUrl = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
    if ($appBaseUrl === '.' || $appBaseUrl === '/') {
        $appBaseUrl = '';
    }
}

$backofficeSrcBaseUrl = $appBaseUrl . '/view/backoffice/src';
$backofficePageBaseUrl = $backofficeSrcBaseUrl . '/pages/backoffice';
$controllerUrl = $appBaseUrl . '/controller/devoirs.php';

// Récupère TOUS les devoirs avec toutes leurs colonnes
$devoirs = $conn->query("
    SELECT id_devoir, titre, description, fichier, date_soumission,
           niveau_difficulte, type_erreur_predominant,
           temps_estime_resolution, progression_eleve,
           mots_cles, urgence
    FROM devoirs
    ORDER BY id_devoir DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Récupère TOUTES les correction avec toutes leurs colonnes
$correction = $conn->query("
    SELECT c.id_correction, c.commentaire, c.fichier_corrige, c.date_correction,
           c.type_feedback, c.note_estimee, c.competences_evaluees,
           c.nombre_iterations, c.suggestions_personnalisees,
           c.ressources_recommandees, c.rapidite_correction,
           c.ton_feedback, c.id_devoir,
           d.titre AS devoir_titre
    FROM correction c
    LEFT JOIN devoirs d ON c.id_devoir = d.id_devoir
    ORDER BY c.id_correction DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Message de succès si redirigé depuis submit
$successType = $_GET['success'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>EduFeed Backoffice</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="<?= htmlspecialchars($appBaseUrl) ?>/assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($backofficeSrcBaseUrl) ?>/assets/css/theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($appBaseUrl) ?>/assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($appBaseUrl) ?>/assets/fonts/themify-icons.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($appBaseUrl) ?>/assets/owlcarousel/css/owl.carousel.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($appBaseUrl) ?>/assets/owlcarousel/css/owl.theme.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($appBaseUrl) ?>/assets/css/jquery-simple-mobilemenu.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($appBaseUrl) ?>/assets/css/magnific-popup.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($appBaseUrl) ?>/assets/css/animate.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($appBaseUrl) ?>/assets/css/style.css">

    <style>
        .btn-edit {
    background: #F59E0B;
    color: white;
    border: none;
    border-radius: 0.5rem;
    padding: 0.3rem 0.8rem;
    font-size: 0.75rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    text-decoration: none;
}

.btn-edit:hover {
    background: #D97706;
    transform: scale(1.05);
    color: white;
    text-decoration: none;
}
        .btn-delete {
    background: #EF4444;
    color: white;
    border: none;
    border-radius: 0.5rem;
    padding: 0.3rem 0.8rem;
    font-size: 0.75rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
}

.btn-delete:hover {
    background: #DC2626;
    transform: scale(1.05);
}

/* Popup confirmation */
.confirm-popup {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 1.5rem;
    border-radius: 1rem;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    z-index: 10000;
    text-align: center;
    min-width: 300px;
}

.confirm-popup button {
    margin: 0.5rem;
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 0.5rem;
    cursor: pointer;
}

.confirm-popup .btn-confirm {
    background: #EF4444;
    color: white;
}

.confirm-popup .btn-cancel {
    background: #94A3B8;
    color: white;
}
        :root {
            --primary: #465FFF;
            --secondary: #0EA5E9;
            --success: #16A34A;
            --danger: #EF4444;
            --warning: #F59E0B;
            --info: #0284C7;
            --bg: #F5F7FB;
            --card-bg: #ffffff;
            --border: #DFE6EF;
            --text: #111827;
            --muted: #6B7280;
        }

        body { background: var(--bg); font-family: 'Public Sans', sans-serif; }

        /* ============ FEED HEADER ============ */
        .feed-hero {
            background: linear-gradient(130deg, #111827 0%, #1d4ed8 100%);
            color: white;
            padding: 2.5rem 0 1.8rem;
            margin-bottom: 2rem;
        }
        .feed-hero h1 { font-size: 2.2rem; font-weight: 800; margin-bottom: 0.3rem; }
        .feed-hero p  { color: #cbd5e1; font-size: 1rem; margin: 0; }
        .feed-hero .stats { display: flex; gap: 2rem; margin-top: 1.5rem; flex-wrap: wrap; }
        .feed-hero .stat-pill {
            background: rgba(255,255,255,0.1);
            border-radius: 999px;
            padding: 0.4rem 1rem;
            font-size: 0.88rem;
            display: flex; align-items: center; gap: 0.4rem;
        }
        .feed-hero .stat-pill i { color: var(--secondary); }

        /* ============ SUCCESS TOAST ============ */
        .toast-success {
            position: fixed;
            top: 1.5rem; right: 1.5rem;
            background: var(--success);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 0.75rem;
            display: flex; align-items: center; gap: 0.75rem;
            font-weight: 600;
            box-shadow: 0 10px 30px rgba(16,185,129,0.35);
            z-index: 9999;
            animation: fadeInRight 0.4s ease, fadeOut 0.4s ease 3.5s forwards;
        }
        @keyframes fadeInRight { from { opacity:0; transform:translateX(40px); } to { opacity:1; transform:translateX(0); } }
        @keyframes fadeOut     { from { opacity:1; } to { opacity:0; pointer-events:none; } }

        /* ============ TABS ============ */
        .feed-tabs {
            display: flex; gap: 0.5rem;
            margin-bottom: 2rem;
            background: white;
            padding: 0.4rem;
            border-radius: 1rem;
            border: 1px solid var(--border);
            box-shadow: 0 6px 18px rgba(15,23,42,0.06);
        }
        .tab-btn {
            flex: 1;
            padding: 0.7rem 1rem;
            border: none;
            border-radius: 0.75rem;
            font-weight: 600;
            font-size: 0.92rem;
            cursor: pointer;
            background: transparent;
            color: var(--muted);
            transition: all 0.25s;
        }
        .tab-btn.active {
            background: linear-gradient(135deg, #111827, #334155);
            color: white;
            box-shadow: 0 8px 18px rgba(15,23,42,0.22);
        }
        .tab-btn i { margin-right: 0.4rem; }

        /* ============ CARD ============ */
        .feed-card {
            background: var(--card-bg);
            border-radius: 1rem;
            box-shadow: 0 8px 24px rgba(15,23,42,0.07);
            margin-bottom: 1.5rem;
            overflow: hidden;
            border: 1px solid var(--border);
            transition: transform 0.25s, box-shadow 0.25s;
            animation: slideUp 0.4s ease forwards;
        }
        .feed-card:hover { transform: translateY(-4px); box-shadow: 0 16px 36px rgba(15,23,42,0.15); }

        @keyframes slideUp {
            from { opacity:0; transform:translateY(20px); }
            to   { opacity:1; transform:translateY(0); }
        }

        /* Card header */
        .card-header-bar {
            padding: 1rem 1.5rem;
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: 0.5rem;
            border-bottom: 1px solid var(--border);
        }
        .card-header-bar.devoir-header {
            background: linear-gradient(135deg, rgba(108,99,255,0.08), rgba(0,212,255,0.05));
        }
        .card-header-bar.correction-header {
            background: linear-gradient(135deg, rgba(16,185,129,0.08), rgba(5,150,105,0.04));
        }

        .card-type-badge {
            display: inline-flex; align-items: center; gap: 0.4rem;
            padding: 0.3rem 0.9rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-devoir     { background: rgba(108,99,255,0.12); color: var(--primary); }
        .badge-correction { background: rgba(16,185,129,0.12); color: var(--success); }

        .card-id { font-size: 0.82rem; color: var(--muted); font-weight: 500; }
        .card-date { font-size: 0.82rem; color: var(--muted); }
        .card-date i { margin-right: 0.3rem; }

        /* Card body */
        .card-body-content { padding: 1.25rem 1.5rem; }

        .card-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 0.5rem;
        }
        .card-description {
            color: var(--muted);
            font-size: 0.93rem;
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        /* Details grid */
        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 0.6rem;
            margin-bottom: 1rem;
        }
        .detail-chip {
            display: flex; align-items: center; gap: 0.5rem;
            background: var(--bg);
            border-radius: 0.6rem;
            padding: 0.45rem 0.75rem;
            font-size: 0.85rem;
            color: var(--text);
            border: 1px solid var(--border);
        }
        .detail-chip i {
            font-size: 0.8rem;
            color: var(--primary);
            width: 14px;
            flex-shrink: 0;
        }
        .detail-chip strong { color: var(--text); margin-right: 0.2rem; }

        /* Correction-specific chips */
        .detail-chip .icon-green { color: var(--success); }
        .detail-chip .icon-orange { color: var(--warning); }

        /* Tags mots clés */
        .tags-row { display: flex; flex-wrap: wrap; gap: 0.4rem; margin-top: 0.5rem; }
        .tag {
            background: rgba(108,99,255,0.1);
            color: var(--primary);
            border-radius: 999px;
            padding: 0.2rem 0.7rem;
            font-size: 0.78rem;
            font-weight: 600;
        }
        .tag.green { background: rgba(16,185,129,0.1); color: var(--success); }

        /* Note badge */
        .note-badge {
            display: inline-flex; align-items: center; gap: 0.3rem;
            background: linear-gradient(135deg, #F59E0B, #EF4444);
            color: white;
            border-radius: 999px;
            padding: 0.35rem 0.9rem;
            font-weight: 800;
            font-size: 1rem;
        }

        /* Urgence pill */
        .urgence-pill {
            display: inline-flex; align-items: center; gap: 0.3rem;
            border-radius: 999px;
            padding: 0.25rem 0.75rem;
            font-size: 0.8rem;
            font-weight: 700;
        }
        .urgence-faible  { background: rgba(16,185,129,0.12); color: var(--success); }
        .urgence-moyenne { background: rgba(245,158,11,0.12); color: var(--warning); }
        .urgence-urgente { background: rgba(239,68,68,0.12); color: var(--danger); }

        /* Progression bar */
        .progression-mini { margin-top: 0.3rem; }
        .progression-mini .bar-track {
            height: 6px; background: var(--border); border-radius: 999px; overflow: hidden;
        }
        .progression-mini .bar-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            border-radius: 999px;
        }
        .progression-label { font-size: 0.78rem; color: var(--muted); margin-bottom: 0.2rem; }

        /* Fichier lien */
        .file-link {
            display: inline-flex; align-items: center; gap: 0.4rem;
            color: var(--primary);
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            border: 1.5px solid rgba(108,99,255,0.3);
            border-radius: 0.5rem;
            padding: 0.3rem 0.75rem;
            transition: all 0.2s;
        }
        .file-link:hover { background: rgba(108,99,255,0.08); text-decoration: none; }

        /* Suggestions / ressources bloc */
        .extra-block {
            background: var(--bg);
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            margin-top: 0.75rem;
            font-size: 0.88rem;
            color: var(--muted);
            border-left: 3px solid var(--primary);
        }
        .extra-block strong { color: var(--text); display: block; margin-bottom: 0.2rem; }

        /* Linked devoir tag */
        .linked-devoir {
            display: inline-flex; align-items: center; gap: 0.4rem;
            background: rgba(108,99,255,0.07);
            color: var(--primary);
            border-radius: 0.5rem;
            padding: 0.3rem 0.75rem;
            font-size: 0.82rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--muted);
        }
        .empty-state i { font-size: 3.5rem; margin-bottom: 1rem; opacity: 0.3; }
        .empty-state h4 { font-size: 1.2rem; font-weight: 600; }

        /* Submit button */
        .btn-submit-link {
            display: inline-flex; align-items: center; gap: 0.5rem;
            background: linear-gradient(135deg, var(--primary), #8B5CF6);
            color: white;
            border-radius: 0.75rem;
            padding: 0.75rem 1.5rem;
            font-weight: 700;
            font-size: 0.95rem;
            text-decoration: none;
            transition: all 0.3s;
        }
        .btn-submit-link:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(108,99,255,0.3); color:white; text-decoration:none; }

        /* Tab panels */
        .tab-panel { display: none; }
        .tab-panel.active { display: block; }

        /* Responsive */
        @media (max-width: 576px) {
            .details-grid { grid-template-columns: 1fr 1fr; }
            .card-body-content { padding: 1rem; }
        }
    </style>
</head>

<body data-spy="scroll" data-offset="80">

    <!-- PRELOADER -->
    <div class="preloaders"><span class="loader"></span></div>

    <?php if ($successType === 'devoir'): ?>
    <div class="toast-success">
        <i class="fas fa-check-circle fa-lg"></i>
        Devoir publié avec succès dans le feed !
    </div>
    <?php elseif ($successType === 'correction'): ?>
    <div class="toast-success">
        <i class="fas fa-check-circle fa-lg"></i>
        Correction soumise avec succès !
    </div>
    <?php endif; ?>

    <!-- NAVBAR -->
    <div id="navigation" class="navbar-light bg-faded site-navigation">
        <div class="container-fluid">
            <div class="row">
                <div class="col-20 align-self-center">
                    <div class="site-logo">
                        <a href="<?= htmlspecialchars($backofficeSrcBaseUrl) ?>/index.html"><img src="<?= htmlspecialchars($appBaseUrl) ?>/assets/img/logo.png" alt=""></a>
                    </div>
                </div>
                <div class="col-60 d-flex">
                    <nav id="main-menu">
                        <ul>
                            <li><a href="<?= htmlspecialchars($backofficeSrcBaseUrl) ?>/index.html">Home</a></li>
                            <li><a href="about.html">About</a></li>
                            <li class="menu-item-has-children">
                                <a href="#">Edufeed</a>
                                <ul>
                                    <li><a href="<?= htmlspecialchars($backofficePageBaseUrl) ?>/submit_back.php">Submit Assignment</a></li>
                                    <li><a href="<?= htmlspecialchars($backofficePageBaseUrl) ?>/feed_back.php">Learning Feed</a></li>
                                </ul>
                            </li>
                            <li><a href="partenariat.html">Partenariat</a></li>
                            <li><a href="evenement.html">Événement</a></li>
                            <li><a href="quiz.html">Quiz</a></li>
                            <li><a href="offre-emploi.html">Offre d'emploi</a></li>
                            <li><a href="contact.html">Contact</a></li>
                        </ul>
                    </nav>
                </div>
                <div class="col-20 d-none d-xl-block text-end align-self-center">
                    <a href="#" class="header-btn">Sign In</a>
                    <a href="contact.html" class="btn_one">Sign Up</a>
                </div>
                <ul class="mobile_menu">
                    <li><a href="<?= htmlspecialchars($backofficeSrcBaseUrl) ?>/index.html">Home</a></li>
                    <li><a href="about.html">About</a></li>
                    <li><a href="#">Edufeed</a>
                        <ul class="sub-menu">
                            <li><a href="<?= htmlspecialchars($backofficePageBaseUrl) ?>/submit_back.php">Submit Assignment</a></li>
                            <li><a href="<?= htmlspecialchars($backofficePageBaseUrl) ?>/feed_back.php">Learning Feed</a></li>
                        </ul>
                    </li>
                    <li><a href="partenariat.html">Partenariat</a></li>
                    <li><a href="evenement.html">Événement</a></li>
                    <li><a href="quiz.html">Quiz</a></li>
                    <li><a href="offre-emploi.html">Offre d'emploi</a></li>
                    <li><a href="contact.html">Contact</a></li>
                </ul>
            </div>
        </div>
    </div>
    <!-- END NAVBAR -->

    <!-- HERO HEADER -->
    <div class="feed-hero">
        <div class="container">
            <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
                <div>
                    <h1><i class="fas fa-graduation-cap me-2"></i> EduFeed</h1>
                    <p>Découvrez les devoirs soumis et leurs correction</p>
                    <div class="stats">
                        <div class="stat-pill">
                            <i class="fas fa-file-alt"></i>
                            <span><?= count($devoirs) ?> devoir<?= count($devoirs) > 1 ? 's' : '' ?></span>
                        </div>
                        <div class="stat-pill">
                            <i class="fas fa-check-double"></i>
                            <span><?= count($correction) ?> correction<?= count($correction) > 1 ? 's' : '' ?></span>
                        </div>
                    </div>
                </div>
                <a href="<?= htmlspecialchars($backofficePageBaseUrl) ?>/submit_back.php" class="btn-submit-link align-self-center">
                    <i class="fas fa-plus"></i> Soumettre
                </a>
            </div>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <section class="py-4">
        <div class="container">

            <!-- TABS -->
            <div class="feed-tabs">
                <button class="tab-btn active" onclick="switchTab('devoirs', this)">
                    <i class="fas fa-book"></i> Devoirs (<?= count($devoirs) ?>)
                </button>
                <button class="tab-btn" onclick="switchTab('correction', this)">
                    <i class="fas fa-check-circle"></i> correction (<?= count($correction) ?>)
                </button>
            </div>

            <!-- ==================== TAB DEVOIRS ==================== -->
             
            <div class="tab-panel active" id="panel-devoirs">

                <?php if (empty($devoirs)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h4>Aucun devoir soumis pour l'instant</h4>
                        <p>Soyez le premier à soumettre un devoir !</p>
                        <a href="<?= htmlspecialchars($backofficePageBaseUrl) ?>/submit_back.php" class="btn-submit-link mt-3">
                            <i class="fas fa-plus"></i> Soumettre un devoir
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($devoirs as $i => $d): ?>
                    <div class="feed-card" style="animation-delay: <?= $i * 0.07 ?>s">

                        <!-- Header -->
                         <!-- Dans l'en-tête de la carte devoir, ajoutez ce bouton -->
<div class="card-header-bar devoir-header">
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <span class="card-type-badge badge-devoir">
            <i class="fas fa-book-open"></i> Devoir
        </span>
        <span class="card-id"># <?= htmlspecialchars($d['id_devoir']) ?></span>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <!-- AJOUTEZ CE BOUTON SUPPRIMER -->
        <button class="btn-delete btn-sm" data-id="<?= $d['id_devoir'] ?>" data-type="devoir">
            <i class="fas fa-trash-alt"></i> Supprimer
        </button>
        <a href="<?= htmlspecialchars($backofficePageBaseUrl) ?>/submit_back.php?edit=devoir&id=<?= $d['id_devoir'] ?>" class="btn-edit btn-sm">
            <i class="fas fa-edit"></i> Modifier
        </a>
        <!-- Fin du bouton -->
        <?php
            $urg = $d['urgence'] ?? 'faible';
            $urgClass = 'urgence-' . strtolower($urg);
            $urgIcon  = ($urg === 'urgente') ? '🔴' : (($urg === 'moyenne') ? '🟡' : '🟢');
        ?>
        <span class="urgence-pill <?= $urgClass ?>">
            <?= $urgIcon ?> <?= htmlspecialchars(ucfirst($urg)) ?>
        </span>
        <span class="card-date">
            <i class="fas fa-calendar-alt"></i>
            <?= htmlspecialchars($d['date_soumission']) ?>
        </span>
    </div>
</div>
                        

                        <!-- Body -->
                        <div class="card-body-content">

                            <!-- Titre -->
                            <h4 class="card-title">
                                <i class="fas fa-heading" style="color:var(--primary);margin-right:0.4rem;"></i>
                                <?= htmlspecialchars($d['titre']) ?>
                            </h4>

                            <!-- Description -->
                            <p class="card-description">
                                <?= nl2br(htmlspecialchars($d['description'])) ?>
                            </p>

                            <!-- Détails en grille -->
                            <div class="details-grid">
                                <div class="detail-chip">
                                    <i class="fas fa-graduation-cap"></i>
                                    <span><strong>Niveau :</strong> <?= htmlspecialchars(ucfirst($d['niveau_difficulte'])) ?></span>
                                </div>
                                <div class="detail-chip">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <span><strong>Erreur :</strong> <?= htmlspecialchars(ucfirst($d['type_erreur_predominant'])) ?></span>
                                </div>
                                <div class="detail-chip">
                                    <i class="fas fa-clock"></i>
                                    <span><strong>Temps :</strong> <?= htmlspecialchars($d['temps_estime_resolution']) ?> min</span>
                                </div>
                            </div>

                            <!-- Progression -->
                            <div class="detail-chip mb-3" style="flex-direction:column;align-items:flex-start;gap:0.3rem;">
                                <div class="d-flex align-items-center gap-2 w-100">
                                    <i class="fas fa-percentage" style="color:var(--primary);"></i>
                                    <strong>Progression : <?= htmlspecialchars($d['progression_eleve']) ?>%</strong>
                                </div>
                                <div class="w-100" style="height:6px;background:var(--border);border-radius:999px;overflow:hidden;">
                                    <div style="width:<?= (int)$d['progression_eleve'] ?>%;height:100%;background:linear-gradient(90deg,var(--primary),var(--secondary));border-radius:999px;"></div>
                                </div>
                            </div>

                            <!-- Mots clés -->
                            <?php if (!empty($d['mots_cles'])): ?>
                            <div class="mb-2">
                                <small style="color:var(--muted);font-weight:600;"><i class="fas fa-tags"></i> Mots clés :</small>
                                <div class="tags-row">
                                    <?php foreach (explode(',', $d['mots_cles']) as $tag): ?>
                                        <span class="tag"><?= htmlspecialchars(trim($tag)) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Fichier -->
                            <?php if (!empty($d['fichier'])): ?>
                            <div class="mt-2">
                                          <a href="<?= htmlspecialchars($appBaseUrl) ?>/uploads/devoirs/<?= htmlspecialchars($d['fichier']) ?>"
                                   class="file-link" target="_blank">
                                    <i class="fas fa-file-code"></i>
                                    <?= htmlspecialchars($d['fichier']) ?>
                                </a>
                            </div>
                            <?php endif; ?>

                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>

            </div><!-- /panel-devoirs -->

            <!-- ==================== TAB correction ==================== -->
            
            <div class="tab-panel" id="panel-correction">

                <?php if (empty($correction)): ?>
                    <div class="empty-state">
                        <i class="fas fa-comments"></i>
                        <h4>Aucune correction soumise pour l'instant</h4>
                        <p>Les correction apparaîtront ici après soumission.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($correction as $i => $c): ?>
                    <div class="feed-card" style="animation-delay: <?= $i * 0.07 ?>s">

                        <!-- Header -->
                          <!-- Dans l'en-tête de la carte correction -->
<div class="card-header-bar correction-header">
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <span class="card-type-badge badge-correction">
            <i class="fas fa-check-circle"></i> Correction
        </span>
        <span class="card-id"># <?= htmlspecialchars($c['id_correction']) ?></span>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <!-- AJOUTEZ CE BOUTON SUPPRIMER -->
        <button class="btn-delete btn-sm" data-id="<?= $c['id_correction'] ?>" data-type="correction">
            <i class="fas fa-trash-alt"></i> Supprimer
        </button>
        <a href="<?= htmlspecialchars($backofficePageBaseUrl) ?>/submit_back.php?edit=correction&id=<?= $c['id_correction'] ?>" class="btn-edit btn-sm">
            <i class="fas fa-edit"></i> Modifier
        </a>
        <!-- Fin du bouton -->
        <span class="note-badge">
            <i class="fas fa-star"></i>
            <?= htmlspecialchars($c['note_estimee']) ?>/20
        </span>
        <span class="card-date">
            <i class="fas fa-calendar-check"></i>
            <?= htmlspecialchars($c['date_correction']) ?>
        </span>
    </div>
</div>
                        

                        <!-- Body -->
                        <div class="card-body-content">

                            <!-- Lien vers le devoir corrigé -->
                            <?php if (!empty($c['devoir_titre'])): ?>
                            <div class="linked-devoir">
                                <i class="fas fa-link"></i>
                                Devoir #<?= htmlspecialchars($c['id_devoir']) ?> :
                                <?= htmlspecialchars($c['devoir_titre']) ?>
                            </div>
                            <?php endif; ?>

                            <!-- Commentaire -->
                            <h4 class="card-title"><i class="fas fa-comment-dots" style="color:var(--success);margin-right:0.4rem;"></i> Commentaire</h4>
                            <p class="card-description"><?= nl2br(htmlspecialchars($c['commentaire'])) ?></p>

                            <!-- Détails grille -->
                            <div class="details-grid">
                                <div class="detail-chip">
                                    <i class="fas fa-comment icon-green"></i>
                                    <span><strong>Type :</strong> <?= htmlspecialchars(ucfirst($c['type_feedback'])) ?></span>
                                </div>
                                <div class="detail-chip">
                                    <i class="fas fa-smile icon-green"></i>
                                    <span><strong>Ton :</strong> <?= htmlspecialchars(ucfirst($c['ton_feedback'])) ?></span>
                                </div>
                                <div class="detail-chip">
                                    <i class="fas fa-sync-alt icon-orange"></i>
                                    <span><strong>Itérations :</strong> <?= htmlspecialchars($c['nombre_iterations']) ?></span>
                                </div>
                                <div class="detail-chip">
                                    <i class="fas fa-hourglass-end icon-orange"></i>
                                    <span><strong>Rapidité :</strong> <?= htmlspecialchars($c['rapidite_correction']) ?> min</span>
                                </div>
                            </div>

                            <!-- Compétences évaluées -->
                            <?php if (!empty($c['competences_evaluees'])): ?>
                            <div class="mb-2">
                                <small style="color:var(--muted);font-weight:600;"><i class="fas fa-brain"></i> Compétences :</small>
                                <div class="tags-row">
                                    <?php foreach (explode(',', $c['competences_evaluees']) as $comp): ?>
                                        <span class="tag green"><?= htmlspecialchars(trim($comp)) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Suggestions -->
                            <?php if (!empty($c['suggestions_personnalisees'])): ?>
                            <div class="extra-block">
                                <strong><i class="fas fa-lightbulb"></i> Suggestions personnalisées</strong>
                                <?= nl2br(htmlspecialchars($c['suggestions_personnalisees'])) ?>
                            </div>
                            <?php endif; ?>

                            <!-- Ressources -->
                            <?php if (!empty($c['ressources_recommandees'])): ?>
                            <div class="extra-block" style="border-left-color:var(--success);">
                                <strong><i class="fas fa-link"></i> Ressources recommandées</strong>
                                <?php foreach (explode(',', $c['ressources_recommandees']) as $url): ?>
                                    <?php $url = trim($url); if (empty($url)) continue; ?>
                                    <a href="<?= htmlspecialchars($url) ?>" target="_blank" style="display:block;color:var(--primary);font-size:0.85rem;">
                                        <?= htmlspecialchars($url) ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>

                            <!-- Fichier corrigé -->
                            <?php if (!empty($c['fichier_corrige'])): ?>
                            <div class="mt-2">
                                          <a href="<?= htmlspecialchars($appBaseUrl) ?>/uploads/corrections/<?= htmlspecialchars($c['fichier_corrige']) ?>"
                                   class="file-link" target="_blank">
                                    <i class="fas fa-file-code"></i>
                                    Télécharger le fichier corrigé
                                </a>
                            </div>
                            <?php endif; ?>

                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>

            </div><!-- /panel-correction -->

        </div><!-- /container -->
    </section>

    <!-- FOOTER -->
    <div class="modern-footer" style="background:#1e293b;color:white;padding:2rem 0;margin-top:3rem;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p style="margin:0;color:#94a3b8;font-size:0.9rem;">
                        &copy; 2026 EduMatch. Tous droits réservés.
                        Made with <i class="fas fa-heart" style="color:#EF4444;"></i> for education.
                    </p>
                </div>
                <div class="col-md-6 text-end">
                    <a href="<?= htmlspecialchars($backofficePageBaseUrl) ?>/submit_back.php" style="color:#6C63FF;text-decoration:none;font-weight:600;font-size:0.9rem;">
                        <i class="fas fa-plus-circle"></i> Soumettre un devoir
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= htmlspecialchars($appBaseUrl) ?>/assets/js/jquery-1.12.4.min.js"></script>
    <script src="<?= htmlspecialchars($appBaseUrl) ?>/assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="<?= htmlspecialchars($appBaseUrl) ?>/assets/js/modernizr-2.8.3.min.js"></script>
    <script src="<?= htmlspecialchars($appBaseUrl) ?>/assets/js/jquery-simple-mobilemenu.js"></script>
    <script src="<?= htmlspecialchars($appBaseUrl) ?>/assets/owlcarousel/js/owl.carousel.min.js"></script>
    <script src="<?= htmlspecialchars($appBaseUrl) ?>/assets/js/jquery.magnific-popup.min.js"></script>
    <script src="<?= htmlspecialchars($appBaseUrl) ?>/assets/js/jquery.inview.min.js"></script>
    <script src="<?= htmlspecialchars($appBaseUrl) ?>/assets/js/scrolltopcontrol.js"></script>
    <script src="<?= htmlspecialchars($appBaseUrl) ?>/assets/js/wow.min.js"></script>
    <script src="<?= htmlspecialchars($appBaseUrl) ?>/assets/js/scripts.js"></script>

    <script>
    // ====== TABS ======
    function switchTab(tab, btn) {
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById('panel-' + tab).classList.add('active');
        btn.classList.add('active');
    }
    
// Fonction pour afficher le popup de confirmation
function showConfirmPopup(message, onConfirm) {
    const popup = document.createElement('div');
    popup.className = 'confirm-popup';
    popup.innerHTML = `
        <p style="margin-bottom: 1rem;">${message}</p>
        <button class="btn-confirm">Oui, supprimer</button>
        <button class="btn-cancel">Annuler</button>
    `;
    document.body.appendChild(popup);
    
    popup.querySelector('.btn-confirm').onclick = () => {
        onConfirm();
        popup.remove();
    };
    popup.querySelector('.btn-cancel').onclick = () => popup.remove();
}

// Fonction pour supprimer
function deleteItem(id, type) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce ' + (type === 'devoir' ? 'devoir' : 'correction') + ' ?')) {
        return;
    }
    
    const action = type === 'devoir' ? 'delete' : 'deletecorrection';
    const controllerUrl = <?= json_encode($controllerUrl) ?>;
    
    fetch(controllerUrl + '?action=' + action + '&id=' + encodeURIComponent(id), {
        method: 'GET'
    })
    .then(response => response.text())
    .then(data => {
        // Afficher un petit message de succès
        const toast = document.createElement('div');
        toast.className = 'toast-success';
        toast.innerHTML = '<i class="fas fa-check-circle"></i> Supprimé avec succès';
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 2000);
        
        // Supprimer la carte du DOM sans recharger
        const card = document.querySelector(`.btn-delete[data-id="${id}"]`).closest('.feed-card');
        if (card) {
            card.remove();
        }
    })
    .catch(error => {
        alert('Erreur: ' + error.message);
    });
}

// Ajouter les écouteurs sur tous les boutons supprimer
document.querySelectorAll('.btn-delete').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        const id = this.dataset.id;
        const type = this.dataset.type;
        deleteItem(id, type);
    });
});

    // ====== AUTO-HIDE TOAST ======
    setTimeout(function() {
        const toast = document.querySelector('.toast-success');
        if (toast) toast.remove();
    }, 4000);

    // Ouvrir onglet correction si redirigé depuis correction submit
    <?php if ($successType === 'correction'): ?>
    document.addEventListener('DOMContentLoaded', function() {
        switchTab('correction', document.querySelectorAll('.tab-btn')[1]);
    });
    <?php endif; ?>
    </script>

</body>
</html>