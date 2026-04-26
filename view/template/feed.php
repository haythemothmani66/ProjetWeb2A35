<?php
require_once __DIR__ . '/../../config/database.php';
$conn = getDBConnection();

// Récupération des paramètres GET pour recherche et tri
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'date_desc';
$level = isset($_GET['level']) ? $_GET['level'] : '';
$urgent = isset($_GET['urgent']) ? $_GET['urgent'] : '';

// Construction de la requête SQL pour les devoirs
$sqlDevoirs = "
    SELECT id_devoir, titre, description, fichier, date_soumission,
           niveau_difficulte, type_erreur_predominant,
           temps_estime_resolution, progression_eleve,
           mots_cles, urgence
    FROM devoirs
    WHERE 1=1
";

$params = array();

// Filtre recherche
if (!empty($search)) {
    $sqlDevoirs .= " AND (titre LIKE :search OR description LIKE :search OR mots_cles LIKE :search)";
    $params[':search'] = "%$search%";
}

// Filtre niveau
if (!empty($level)) {
    $sqlDevoirs .= " AND niveau_difficulte = :level";
    $params[':level'] = $level;
}

// Filtre urgence
if (!empty($urgent)) {
    $sqlDevoirs .= " AND urgence = :urgent";
    $params[':urgent'] = $urgent;
}

// Tri
if ($sort == 'date_asc') {
    $sqlDevoirs .= " ORDER BY date_soumission ASC";
} elseif ($sort == 'date_desc') {
    $sqlDevoirs .= " ORDER BY date_soumission DESC";
} elseif ($sort == 'level_asc') {
    $sqlDevoirs .= " ORDER BY CASE niveau_difficulte 
                    WHEN 'facile' THEN 1 
                    WHEN 'moyen' THEN 2 
                    WHEN 'difficile' THEN 3 
                    ELSE 4 END ASC";
} elseif ($sort == 'level_desc') {
    $sqlDevoirs .= " ORDER BY CASE niveau_difficulte 
                    WHEN 'difficile' THEN 1 
                    WHEN 'moyen' THEN 2 
                    WHEN 'facile' THEN 3 
                    ELSE 4 END ASC";
} elseif ($sort == 'urgence') {
    $sqlDevoirs .= " ORDER BY CASE urgence 
                    WHEN 'urgente' THEN 1 
                    WHEN 'moyenne' THEN 2 
                    WHEN 'faible' THEN 3 
                    ELSE 4 END ASC";
} else {
    $sqlDevoirs .= " ORDER BY date_soumission DESC";
}

// Exécution de la requête
$stmt = $conn->prepare($sqlDevoirs);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$devoirs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupère TOUTES les corrections
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

// Organiser les corrections par id_devoir
$correctionsByDevoir = [];
foreach ($corrections as $corr) {
    $devoirId = $corr['id_devoir'];
    if (!isset($correctionsByDevoir[$devoirId])) {
        $correctionsByDevoir[$devoirId] = [];
    }
    $correctionsByDevoir[$devoirId][] = $corr;
}

// Message de succès si redirigé depuis submit
$successType = $_GET['success'] ?? '';




?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Feed - EduFeed</title>
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Jost:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../../assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="../../assets/fonts/themify-icons.css">
    <link rel="stylesheet" href="../../assets/owlcarousel/css/owl.carousel.css">
    <link rel="stylesheet" href="../../assets/owlcarousel/css/owl.theme.css">
    <link rel="stylesheet" href="../../assets/css/jquery-simple-mobilemenu.css">
    <link rel="stylesheet" href="../../assets/css/magnific-popup.css">
    <link rel="stylesheet" href="../../assets/css/animate.css">
    <link rel="stylesheet" href="../../assets/css/style.css">

    <style>

        /* Bouton Refresh */
.btn-refresh {
    background: linear-gradient(135deg, #06b6d4, #3b82f6);
    color: white;
    border: none;
    border-radius: 0.75rem;
    padding: 0.7rem 1.5rem;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.btn-refresh:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(6, 182, 212, 0.3);
    background: linear-gradient(135deg, #0891b2, #2563eb);
}
        .search-filter-form {
    background: rgba(255,255,255,0.1);
    border-radius: 1rem;
    padding: 1.25rem;
    margin-top: 1rem;
}

.search-input-wrapper {
    position: relative;
}

.search-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    z-index: 1;
}

.search-input {
    padding-left: 2.5rem !important;
    background: rgba(255,255,255,0.95) !important;
}

.filter-select {
    background: rgba(255,255,255,0.95) !important;
    cursor: pointer;
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
            --primary: #6C63FF;
            --secondary: #00D4FF;
            --success: #10B981;
            --danger: #EF4444;
            --warning: #F59E0B;
            --info: #3B82F6;
            --bg: #F0F4FF;
            --card-bg: #ffffff;
            --border: #E2E8F0;
            --text: #1E293B;
            --muted: #64748B;
        }

        body { background: var(--bg); font-family: 'DM Sans', sans-serif; }

        /* ============ FEED HEADER ============ */
        .feed-hero {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: white;
            padding: 3rem 0 2rem;
            margin-bottom: 2.5rem;
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
            border-radius: 1.25rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.07);
            margin-bottom: 2rem;
            overflow: hidden;
            border: 1px solid var(--border);
            transition: transform 0.25s, box-shadow 0.25s;
            animation: slideUp 0.4s ease forwards;
        }
        .feed-card:hover { transform: translateY(-4px); box-shadow: 0 12px 35px rgba(0,0,0,0.13); }

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

        /* Correction card inside devoir */
        .correction-subcard {
            background: linear-gradient(135deg, rgba(16,185,129,0.03), rgba(5,150,105,0.02));
            border-top: 2px solid var(--border);
            margin-top: 0;
            padding: 1.25rem 1.5rem;
        }
        .correction-subcard:first-child {
            margin-top: 0;
        }
        .correction-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px dashed var(--border);
        }
        .correction-title {
            font-weight: 700;
            color: var(--success);
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        /* Separator entre devoir et corrections */
        .corrections-separator {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 0 1.5rem 1rem 1.5rem;
            padding-top: 0.5rem;
        }
        .corrections-separator::before,
        .corrections-separator::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--border), transparent);
        }
        .corrections-separator span {
            font-size: 0.8rem;
            color: var(--muted);
            font-weight: 600;
            background: white;
            padding: 0 0.75rem;
        }

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
                        <a href="index.html"><img src="../../assets/img/logo.png" alt=""></a>
                    </div>
                </div>
                <div class="col-60 d-flex">
                    <nav id="main-menu">
                        <ul>
                            <li><a href="index.html">Home</a></li>
                            <li><a href="about.html">About</a></li>
                            <li class="menu-item-has-children">
                                <a href="#">Edufeed</a>
                                <ul>
                                    <li><a href="/eduleb/submit.html">Submit Assignment</a></li>
                                    <li><a href="/eduleb/feed.html">Learning Feed</a></li>
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
                    <li><a href="index.html">Home</a></li>
                    <li><a href="about.html">About</a></li>
                    <li><a href="#">Edufeed</a>
                        <ul class="sub-menu">
                            <li><a href="/eduleb/submit.html">Submit Assignment</a></li>
                            <li><a href="/eduleb/feed.html">Learning Feed</a></li>
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
        <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
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
            <a href="/eduleb/submit.html" class="btn-submit-link align-self-center">
                <i class="fas fa-plus"></i> Nouveau devoir
            </a>
        </div>

        <!-- Barre de recherche et filtres -->
        <form method="GET" action="" class="search-filter-form">
            <div class="row g-3">
                <div class="col-md-5">
                    <div class="search-input-wrapper">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="search" class="form-control search-input" 
                               placeholder="Rechercher par titre, description ou mots-clés..." 
                               value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    </div>
                </div>
                
                <div class="col-md-2">
                    <select name="sort" class="form-control filter-select" onchange="this.form.submit()">
                        <option value="date_desc" <?= ($_GET['sort'] ?? 'date_desc') == 'date_desc' ? 'selected' : '' ?>>📅 Récent d'abord</option>
                        <option value="date_asc" <?= ($_GET['sort'] ?? '') == 'date_asc' ? 'selected' : '' ?>>📅 Ancien d'abord</option>
                        <option value="level_asc" <?= ($_GET['sort'] ?? '') == 'level_asc' ? 'selected' : '' ?>>📈 Niveau croissant</option>
                        <option value="level_desc" <?= ($_GET['sort'] ?? '') == 'level_desc' ? 'selected' : '' ?>>📉 Niveau décroissant</option>
                        <option value="urgence" <?= ($_GET['sort'] ?? '') == 'urgence' ? 'selected' : '' ?>>⚠️ Par urgence</option>
                    </select>
                </div>
                <!-- Bouton Refresh -->
        <div class="col-md-3">
            <button type="button" onclick="refreshPage()" class="btn-refresh">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
            </div>
        </form>
    </div>
</div>
    

    <!-- MAIN CONTENT -->
    <section class="py-4">
        <div class="container">

            <?php if (empty($devoirs)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h4>Aucun devoir soumis pour l'instant</h4>
                    <p>Soyez le premier à soumettre un devoir !</p>
                    <a href="/eduleb/submit.html" class="btn-submit-link mt-3">
                        <i class="fas fa-plus"></i> Soumettre un devoir
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($devoirs as $i => $d): ?>
                <div class="feed-card" style="animation-delay: <?= $i * 0.07 ?>s" data-devoir-id="<?= $d['id_devoir'] ?>">

                    <!-- Header Devoir -->
                    <div class="card-header-bar devoir-header">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="card-type-badge badge-devoir">
                                <i class="fas fa-book-open"></i> Devoir
                            </span>
                            <!-- ID caché mais accessible via data attribute si besoin -->
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <a href="/eduleb/submit.html?add_correction_for=<?= $d['id_devoir'] ?>&title=<?= urlencode($d['titre']) ?>" 
                               class="btn-add-correction btn-sm">
                                <i class="fas fa-plus-circle"></i> Ajouter une correction
                            </a>
                            <button class="btn-delete btn-sm" data-id="<?= $d['id_devoir'] ?>" data-type="devoir">
                                <i class="fas fa-trash-alt"></i> Supprimer
                            </button>
                            <a href="/eduleb/submit.html?edit=devoir&id=<?= $d['id_devoir'] ?>" class="btn-edit btn-sm">
                                <i class="fas fa-edit"></i> Modifier
                            </a>
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

                    <!-- Body Devoir -->
                    <div class="card-body-content">
                        <h4 class="card-title">
                            <i class="fas fa-heading" style="color:var(--primary);margin-right:0.4rem;"></i>
                            <?= htmlspecialchars($d['titre']) ?>
                        </h4>
                        <p class="card-description">
                            <?= nl2br(htmlspecialchars($d['description'])) ?>
                        </p>

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

                        <div class="detail-chip mb-3" style="flex-direction:column;align-items:flex-start;gap:0.3rem;">
                            <div class="d-flex align-items-center gap-2 w-100">
                                <i class="fas fa-percentage" style="color:var(--primary);"></i>
                                <strong>Progression : <?= htmlspecialchars($d['progression_eleve']) ?>%</strong>
                            </div>
                            <div class="w-100" style="height:6px;background:var(--border);border-radius:999px;overflow:hidden;">
                                <div style="width:<?= (int)$d['progression_eleve'] ?>%;height:100%;background:linear-gradient(90deg,var(--primary),var(--secondary));border-radius:999px;"></div>
                            </div>
                        </div>

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

                        <?php if (!empty($d['fichier'])): ?>
                        <div class="mt-2">
                            <a href="/eduleb/uploads/devoirs/<?= htmlspecialchars($d['fichier']) ?>"
                               class="file-link" target="_blank">
                                <i class="fas fa-file-code"></i>
                                <?= htmlspecialchars($d['fichier']) ?>
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- SECTION CORRECTIONS -->
                    <?php if (!empty($correctionsByDevoir[$d['id_devoir']])): ?>
                        <div class="corrections-separator">
                            <span><i class="fas fa-check-circle"></i> Corrections (<?= count($correctionsByDevoir[$d['id_devoir']]) ?>)</span>
                        </div>
                        
                        <?php foreach ($correctionsByDevoir[$d['id_devoir']] as $c): ?>
                            <div class="correction-subcard">
                                <div class="correction-header">
                                    <div class="correction-title">
                                        <i class="fas fa-chalkboard-teacher"></i>
                                        Correction 
                                    </div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <button class="btn-delete btn-sm" data-id="<?= $c['id_correction'] ?>" data-type="correction">
                                            <i class="fas fa-trash-alt"></i> Supprimer
                                        </button>
                                        <a href="/eduleb/submit.html?edit=correction&id=<?= $c['id_correction'] ?>" class="btn-edit btn-sm">
                                            <i class="fas fa-edit"></i> Modifier
                                        </a>
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

                                <p class="card-description" style="margin-bottom: 0.75rem;">
                                    <strong>Commentaire :</strong> <?= nl2br(htmlspecialchars($c['commentaire'])) ?>
                                </p>

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
                                    <a href="/eduleb/uploads/correction/<?= htmlspecialchars($c['fichier_corrige']) ?>"
                                       class="file-link" target="_blank">
                                        <i class="fas fa-file-code"></i>
                                        Télécharger le fichier corrigé
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="corrections-separator">
                            <span><i class="fas fa-clock"></i> Aucune correction pour ce devoir</span>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </div><!-- /container -->
    </section>

    <!-- START MODERN FOOTER -->
    <footer class="modern-footer bg-dark text-white py-5">
      <div class="container">
        <div class="row">
          <div class="col-lg-4 col-md-6 mb-4">
            <div class="footer-brand">
              <a href="index.html" class="text-decoration-none">
                <img src="../../assets/img/logo.png" alt="EduMatch Logo" class="mb-3" style="height: 50px;">
                <h3 class="text-white fw-bold">EduMatch</h3>
              </a>
              <p class="mt-3 text-light opacity-75">
                Smart matching platform connecting students with expert professors across all academic subjects for personalized learning experiences.
              </p>
              <div class="social-links mt-3">
                <a href="#" class="text-white me-3 fs-4"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="text-white me-3 fs-4"><i class="fab fa-twitter"></i></a>
                <a href="#" class="text-white me-3 fs-4"><i class="fab fa-linkedin-in"></i></a>
                <a href="#" class="text-white me-3 fs-4"><i class="fab fa-instagram"></i></a>
              </div>
            </div>
          </div>
          <div class="col-lg-2 col-md-6 mb-4">
            <h5 class="fw-bold mb-3">Platform</h5>
            <ul class="list-unstyled">
              <li class="mb-2"><a href="submit.html" class="text-light text-decoration-none">Submit Requirements</a></li>
              <li class="mb-2"><a href="feed.html" class="text-light text-decoration-none">Professor Matches</a></li>
              <li class="mb-2"><a href="about.html" class="text-light text-decoration-none">How It Works</a></li>
              <li class="mb-2"><a href="contact.html" class="text-light text-decoration-none">Get Matched</a></li>
            </ul>
          </div>
          <div class="col-lg-2 col-md-6 mb-4">
            <h5 class="fw-bold mb-3">Academic Subjects</h5>
            <ul class="list-unstyled">
              <li class="mb-2"><a href="#" class="text-light text-decoration-none">Mathematics</a></li>
              <li class="mb-2"><a href="#" class="text-light text-decoration-none">Sciences</a></li>
							<li class="mb-2"><a href="#" class="text-light text-decoration-none">coding</a></li>
							<li class="mb-2"><a href="#" class="text-light text-decoration-none">algorithm</a></li>
              <li class="mb-2"><a href="#" class="text-light text-decoration-none">Languages</a></li>
              <li class="mb-2"><a href="#" class="text-light text-decoration-none">Humanities</a></li>
            </ul>
          </div>
          <div class="col-lg-4 col-md-6 mb-4">
            <h5 class="fw-bold mb-3">Contact Info</h5>
            <div class="contact-info">
              <p class="mb-2"><i class="fas fa-map-marker-alt me-2"></i>Tunisia,Tunis</p>
              <p class="mb-2"><i class="fas fa-phone me-2"></i>+216 90 549 254</p>
              <p class="mb-2"><i class="fas fa-envelope me-2"></i>edumatch@gmail.com</p>
            </div>
            <div class="newsletter mt-3">
              <h6 class="fw-bold mb-2">Stay Updated on Academic Tutoring</h6>
              <div class="input-group">
                <input type="email" class="form-control" placeholder="Your email" style="border-radius: 25px 0 0 25px;">
                <button class="btn btn-primary" type="button" style="border-radius: 0 25px 25px 0;">Subscribe</button>
              </div>
            </div>
          </div>
        </div>
        <hr class="my-4 opacity-25">
        <div class="row align-items-center">
          <div class="col-md-6">
            <p class="mb-0 text-light opacity-75">&copy; 2026 EduMatch. All rights reserved.</p>
          </div>
          <div class="col-md-6 text-md-end">
            <a href="#" class="text-light text-decoration-none me-3">Privacy Policy</a>
            <a href="#" class="text-light text-decoration-none me-3">Terms of Service</a>
            <a href="#" class="text-light text-decoration-none">Support</a>
          </div>
        </div>
      </div>
    </footer>
    <!-- END MODERN FOOTER -->

    <script src="../../assets/js/jquery-1.12.4.min.js"></script>
    <script src="../../assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="../../assets/js/modernizr-2.8.3.min.js"></script>
    <script src="../../assets/js/jquery-simple-mobilemenu.js"></script>
    <script src="../../assets/owlcarousel/js/owl.carousel.min.js"></script>
    <script src="../../assets/js/jquery.magnific-popup.min.js"></script>
    <script src="../../assets/js/jquery.inview.min.js"></script>
    <script src="../../assets/js/scrolltopcontrol.js"></script>
    <script src="../../assets/js/wow.min.js"></script>
    <script src="../../assets/js/scripts.js"></script>

    <script>
    // Fonction pour supprimer
    function deleteItem(id, type) {
        if (!confirm('Êtes-vous sûr de vouloir supprimer ce ' + (type === 'devoir' ? 'devoir' : 'correction') + ' ?')) {
            return;
        }
        
        const action = type === 'devoir' ? 'delete' : 'deletecorrection';
        
        fetch('/eduleb/controller/devoirs.php?action=' + action + '&id=' + id, {
            method: 'GET'
        })
        .then(response => response.text())
        .then(data => {
            const toast = document.createElement('div');
            toast.className = 'toast-success';
            toast.innerHTML = '<i class="fas fa-check-circle"></i> Supprimé avec succès';
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 2000);
            
            // Recharger la page pour mettre à jour l'affichage
            location.reload();
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

    // AUTO-HIDE TOAST
    setTimeout(function() {
        const toast = document.querySelector('.toast-success');
        if (toast) toast.remove();
    }, 4000);
    </script>
<script>
// Fonction pour rafraîchir la page et réinitialiser tous les paramètres
function refreshPage() {
    // Redirige vers feed.php sans aucun paramètre
    window.location.href = 'feed.html';
}

// Option 2: Si tu veux juste réinitialiser les champs sans recharger la page
function resetFilters() {
    document.querySelector('input[name="search"]').value = '';
    document.querySelector('select[name="sort"]').value = 'date_desc';
    // Soumettre le formulaire
    document.querySelector('.search-filter-form').submit();
}
</script>
</body>
</html>