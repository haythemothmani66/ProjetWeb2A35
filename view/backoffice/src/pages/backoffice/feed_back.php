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

// Récupère TOUTES les corrections avec toutes leurs colonnes
$corrections = $conn->query("
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <style>

        /* Bouton PDF */
.btn-pdf {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    color: white;
    border: none;
    border-radius: 0.75rem;
    padding: 0.75rem 1.5rem;
    font-weight: 700;
    font-size: 0.95rem;
    text-decoration: none;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
}
.btn-pdf:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(220,38,38,0.3);
    color: white;
    background: linear-gradient(135deg, #b91c1c, #991b1b);
}

/* Loader PDF */
.pdf-loader {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    color: white;
    font-weight: bold;
    font-size: 1.2rem;
    flex-direction: column;
    gap: 1rem;
}
.pdf-loader .spinner {
    width: 50px;
    height: 50px;
    border: 5px solid rgba(255,255,255,0.3);
    border-top: 5px solid white;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Masquer boutons lors de l'export PDF */
@media print {
    .btn-delete, .btn-edit, .btn-add-correction, .btn-submit-link, 
    .btn-secondary, .btn-pdf, .btn-delete, .btn-edit, .btn-add-correction,
    .btn-submit-link, .btn-danger, .btn-warning, .btn-close, .modal, 
    .toast-success, .header-btn, .btn_one, .mobile_menu, #clearSearchBtn,
    .search-filter-bar .btn-secondary, .no-print {
        display: none !important;
    }
    .feed-card, .correction-sub-card {
        break-inside: avoid;
        page-break-inside: avoid;
    }
}

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
        
        .btn-add-correction {
            background: #10B981;
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
        
        .btn-add-correction:hover {
            background: #059669;
            transform: scale(1.05);
            color: white;
            text-decoration: none;
        }
        
        .btn-view {
            background: #6B7280;
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
        
        .btn-view:hover {
            background: #4B5563;
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
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.5rem;
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

        /* ============ UNIFIED FEED ============ */
        .devoir-block {
            margin-bottom: 2.5rem;
        }

        /* Corrections nested under devoir */
        .corrections-container {
            margin: 1rem 0 0 1.5rem;
            padding-left: 1.5rem;
            border-left: 3px solid rgba(16,185,129,0.3);
        }

        .corrections-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--success);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0.5rem 0.75rem;
            margin-bottom: 0.75rem;
            background: rgba(16,185,129,0.06);
            border-radius: 0.5rem;
            width: fit-content;
        }

        .no-correction-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.82rem;
            color: var(--muted);
            padding: 0.5rem 0.75rem;
            font-style: italic;
        }

        .correction-sub-card {
            background: var(--card-bg);
            border-radius: 1rem;
            box-shadow: 0 4px 14px rgba(15,23,42,0.06);
            margin-bottom: 1rem;
            overflow: hidden;
            border: 1px solid rgba(16,185,129,0.2);
            animation: slideUp 0.4s ease forwards;
        }
        .correction-sub-card:hover { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(15,23,42,0.12); transition: all 0.25s; }

        /* Responsive */
        @media (max-width: 576px) {
            .details-grid { grid-template-columns: 1fr 1fr; }
            .card-body-content { padding: 1rem; }
            .corrections-container { margin-left: 0.5rem; padding-left: 0.75rem; }
        }
        
        /* Search and filter bar */
        .search-filter-bar {
            background: white;
            border-radius: 1rem;
            padding: 1.25rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .devbar {
            margin-top: 1rem;
            padding: 0.75rem;
            background: rgba(0,0,0,0.02);
            border-radius: 0.5rem;
        }
        
        .stats-cards {
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 1rem;
            padding: 1.25rem;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
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
                    <p>Découvrez les devoirs soumis et leurs corrections</p>
                    <div class="stats">
                        <div class="stat-pill">
                            <i class="fas fa-file-alt"></i>
                            <span><?= count($devoirs) ?> devoir<?= count($devoirs) > 1 ? 's' : '' ?></span>
                        </div>
                        <div class="stat-pill">
                            <i class="fas fa-check-double"></i>
                            <span><?= count($corrections) ?> correction<?= count($corrections) > 1 ? 's' : '' ?></span>
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
            
            <!-- Search Bar -->
            <div class="search-filter-bar">
                <div class="row g-3">
                    <div class="col-md-10">
                        <input type="text" id="searchInput" class="form-control" placeholder="Rechercher un devoir ou une correction...">
                    </div>
                    <div class="col-md-2">
                        <button id="clearSearchBtn" class="btn btn-secondary w-100">Effacer</button>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row stats-cards">
                <div class="col-md-6">
                    <div class="stat-card">
                        <h5>Total Devoirs</h5>
                        <h2 class="text-primary"><?= count($devoirs) ?></h2>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stat-card">
                        <h5>Total Corrections</h5>
                        <h2 class="text-success"><?= count($corrections) ?></h2>
                    </div>
                </div>
            </div>

            <?php
            // Regrouper les corrections par id_devoir pour affichage hiérarchique
            $correctionsByDevoir = [];
            foreach ($corrections as $c) {
                $correctionsByDevoir[$c['id_devoir']][] = $c;
            }
            ?>

            <!-- ==================== FEED UNIFIÉ ==================== -->

            <div id="unified-feed">

                <?php if (empty($devoirs)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h4>Aucun devoir soumis pour l'instant</h4>
                        <p>Soyez le premier à soumettre un devoir !</p>
                        <div class="d-flex gap-2 align-self-center">
    <button id="exportPDFBtn" class="btn-pdf">
        <i class="fas fa-file-pdf"></i> Exporter PDF
    </button>
    <a href="<?= htmlspecialchars($backofficePageBaseUrl) ?>/submit_back.php" class="btn-submit-link">
        <i class="fas fa-plus"></i> Soumettre
    </a>
</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($devoirs as $i => $d): ?>
                    <?php $devoirCorrections = $correctionsByDevoir[$d['id_devoir']] ?? []; ?>
                    <div class="devoir-block" data-devoir-title="<?= strtolower(htmlspecialchars($d['titre'])) ?>" data-devoir-description="<?= strtolower(htmlspecialchars($d['description'])) ?>" data-devoir-keywords="<?= strtolower(htmlspecialchars($d['mots_cles'])) ?>" data-devoir-id="<?= $d['id_devoir'] ?>">
                        <div class="feed-card">

                            <!-- Header -->
                            <div class="card-header-bar devoir-header">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="card-type-badge badge-devoir">
                                        <i class="fas fa-book-open"></i> Devoir
                                    </span>
                                    <h5 class="mb-0"><?= htmlspecialchars($d['titre']) ?></h5>
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
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <a href="<?= htmlspecialchars($backofficePageBaseUrl) ?>/submit_back.php?add_correction_for=<?= (int)$d['id_devoir'] ?>&title=<?= urlencode($d['titre']) ?>" 
                               class="btn-add-correction btn-sm">
                                <i class="fas fa-plus-circle"></i> Ajouter une correction
                            </a>

                                    <a href="<?= htmlspecialchars($backofficePageBaseUrl) ?>/submit_back.php?edit=devoir&id=<?= (int)$d['id_devoir'] ?>" class="btn-edit btn-sm">
                                <i class="fas fa-edit"></i> Modifier </a>

                                    <button class="btn-delete" data-id="<?= $d['id_devoir'] ?>" data-type="devoir">
                                        <i class="fas fa-trash-alt"></i> Supprimer
                                    </button>
                                </div>
                            </div>

                            <!-- Body -->
                            <div class="card-body-content">

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
                                    <div class="detail-chip">
                                        <i class="fas fa-percentage"></i>
                                        <span><strong>Progression :</strong> <?= htmlspecialchars($d['progression_eleve']) ?>%</span>
                                    </div>
                                </div>

                                <!-- Progression bar -->
                                <div class="progression-mini mb-3">
                                    <div class="progression-label">Progression de l'élève</div>
                                    <div class="bar-track">
                                        <div class="bar-fill" style="width: <?= (int)$d['progression_eleve'] ?>%"></div>
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
                        </div><!-- /feed-card devoir -->

                        <!-- ===== CORRECTIONS IMBRIQUÉES ===== -->
                        <div class="corrections-container">
                            <?php if (empty($devoirCorrections)): ?>
                                <div class="no-correction-label">
                                    <i class="fas fa-comment-slash"></i> Aucune correction pour ce devoir
                                </div>
                            <?php else: ?>
                                <div class="corrections-label">
                                    <i class="fas fa-check-double"></i>
                                    <?= count($devoirCorrections) ?> correction<?= count($devoirCorrections) > 1 ? 's' : '' ?>
                                </div>
                                <?php foreach ($devoirCorrections as $j => $c): ?>
                                <div class="correction-sub-card" data-correction-comment="<?= strtolower(htmlspecialchars($c['commentaire'])) ?>">
                                    <!-- Header correction -->
                                    <div class="card-header-bar correction-header">
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <span class="card-type-badge badge-correction">
                                                <i class="fas fa-check-circle"></i> Correction
                                            </span>
                                            <span class="note-badge">
                                                <i class="fas fa-star"></i>
                                                <?= htmlspecialchars($c['note_estimee']) ?>/20
                                            </span>
                                            <span class="card-date">
                                                <i class="fas fa-calendar-check"></i>
                                                <?= htmlspecialchars($c['date_correction']) ?>
                                            </span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <form action="<?= htmlspecialchars($backofficePageBaseUrl) ?>/submit_back.php" method="post" class="m-0 d-inline">
                                                <input type="hidden" name="edit" value="correction">
                                                <input type="hidden" name="id_correction" value="<?= (int)$c['id_correction'] ?>">
                                                <input type="hidden" name="devoir_titre" value="<?= htmlspecialchars($d['titre'], ENT_QUOTES) ?>">
                                                <button type="submit" class="btn-edit">
                                                    <i class="fas fa-edit"></i> Modifier
                                                </button>
                                            </form>
                                            <button class="btn-delete" data-id="<?= $c['id_correction'] ?>" data-type="correction">
                                                <i class="fas fa-trash-alt"></i> Supprimer
                                            </button>
                                        </div>
                                    </div>
                                    <!-- Body correction -->
                                    <div class="card-body-content">
                                        <!-- Commentaire -->
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
                                        <?php if (!empty($c['suggestions_personnalisees'])): ?>
                                        <div class="extra-block">
                                            <strong><i class="fas fa-lightbulb"></i> Suggestions personnalisées</strong>
                                            <?= nl2br(htmlspecialchars($c['suggestions_personnalisees'])) ?>
                                        </div>
                                        <?php endif; ?>
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
                                </div><!-- /correction-sub-card -->
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div><!-- /corrections-container -->

                    </div><!-- /devoir-block -->
                    <?php endforeach; ?>
                <?php endif; ?>

            </div><!-- /unified-feed -->

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
            const toast = document.createElement('div');
            toast.className = 'toast-success';
            toast.innerHTML = '<i class="fas fa-check-circle"></i> Supprimé avec succès';
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 2000);
            
            // Reload the page to refresh the feed
            setTimeout(() => {
                window.location.reload();
            }, 1000);
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
    
    // ====== SEARCH FUNCTIONALITY ======
    const searchInput = document.getElementById('searchInput');
    const clearSearchBtn = document.getElementById('clearSearchBtn');
    const devoirBlocks = document.querySelectorAll('.devoir-block');
    
    function filterFeed() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        
        devoirBlocks.forEach(block => {
            const devoirTitle = block.dataset.devoirTitle || '';
            const devoirDescription = block.dataset.devoirDescription || '';
            const devoirKeywords = block.dataset.devoirKeywords || '';
            const corrections = block.querySelectorAll('.correction-sub-card');
            
            let devoirMatch = false;
            let correctionMatch = false;
            
            // Check if devoir matches
            if (searchTerm === '') {
                devoirMatch = true;
            } else {
                if (devoirTitle.includes(searchTerm) || 
                    devoirDescription.includes(searchTerm) || 
                    devoirKeywords.includes(searchTerm)) {
                    devoirMatch = true;
                }
            }
            
            // Check corrections
            corrections.forEach(correction => {
                const correctionComment = correction.dataset.correctionComment || '';
                if (searchTerm !== '' && correctionComment.includes(searchTerm)) {
                    correctionMatch = true;
                    correction.style.display = 'block';
                } else if (searchTerm === '') {
                    correction.style.display = 'block';
                } else if (!correctionComment.includes(searchTerm)) {
                    correction.style.display = 'none';
                }
            });
            
            // Show/hide devoir block
            if (devoirMatch || correctionMatch) {
                block.style.display = 'block';
            } else if (searchTerm !== '' && !devoirMatch && !correctionMatch) {
                block.style.display = 'none';
            } else if (searchTerm === '') {
                block.style.display = 'block';
            } else {
                block.style.display = 'none';
            }
        });
    }
    
    if (searchInput) {
        searchInput.addEventListener('input', filterFeed);
    }
    
    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function() {
            searchInput.value = '';
            filterFeed();
        });
    }
    </script>

</body>
</html>