<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../api/RecommendationService.php';

// --- Protection : tout utilisateur connecte peut acceder ---
if (empty($_SESSION['user_id'])) {
    header('Location: /gestion_users/view/template/sign-in.php');
    exit;
}

$partnerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($partnerId <= 0) {
    header('Location: partners_may_like.php');
    exit;
}

// Fetch partner details
$conn = getConnexion();
$stmt = $conn->prepare("SELECT * FROM partenaires WHERE id = ?");
$stmt->execute([$partnerId]);
$partner = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$partner) {
    echo "<h1>Partenaire non trouvé</h1><a href='partners_may_like.php'>Retour aux recommandations</a>";
    exit;
}

// Increment view count for the "Popular" badge logic
$updateStmt = $conn->prepare("UPDATE partenaires SET view_count = view_count + 1 WHERE id = ?");
$updateStmt->execute([$partnerId]);

// Fetch similar partners for the "Related" section
$similarPartners = RecommendationService::getRecommendations($conn, $partnerId, 3);

// Helper : resoudre le chemin du logo (URL externe, upload, ou placeholder)
function resolveLogo($logo) {
    static $placeholder = null;
    if ($placeholder === null) {
        $placeholder = 'data:image/svg+xml;utf8,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 150 150"><defs><linearGradient id="g" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#6366f1"/><stop offset="100%" stop-color="#8B5CF6"/></linearGradient></defs><rect width="150" height="150" fill="url(#g)" rx="20"/><text x="75" y="95" font-family="Arial" font-size="60" fill="white" text-anchor="middle" font-weight="700">🏢</text></svg>');
    }
    $logo = trim((string)$logo);
    if ($logo === '') return $placeholder;
    if (preg_match('#^https?://#i', $logo)) return $logo;
    if (strpos($logo, 'assets/uploads/') === 0) return '/gestion_users/' . $logo;
    return '/gestion_users/assets/uploads/partners/' . $logo;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($partner['organization_name']); ?> - Profil Partenaire EduMatch</title>
    
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <style>
        :root {
            --primary: #6366f1;
            --secondary: #ff7f50;
            --dark: #0f172a;
            --glass: rgba(255, 255, 255, 0.8);
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: #f1f5f9;
            color: var(--dark);
            overflow-x: hidden;
        }

        /* Animated Hero Section */
        .hero-profile {
            height: 450px;
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            overflow: hidden;
        }

        .hero-profile::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.1) 0%, transparent 50%);
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .floating-logo {
            width: 150px;
            height: 150px;
            background: white;
            border-radius: 30px;
            padding: 20px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
            margin-bottom: 25px;
            position: relative;
            z-index: 2;
            display: inline-block;
            animation: float 4s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }

        /* Profile Content */
        .profile-container {
            margin-top: -100px;
            position: relative;
            z-index: 5;
            padding-bottom: 80px;
        }

        .glass-card {
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.4);
            border-radius: 30px;
            padding: 50px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.05);
        }

        .stat-badge {
            background: white;
            padding: 15px 25px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.02);
            transition: transform 0.3s ease;
        }

        .stat-badge:hover {
            transform: scale(1.05);
        }

        .stat-icon {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        /* Section Titles */
        .section-title {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .section-title::after {
            content: '';
            flex: 1;
            height: 2px;
            background: linear-gradient(to right, #e2e8f0, transparent);
        }

        /* Similar Partners */
        .similar-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid #f1f5f9;
        }

        .similar-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        }

        /* Back Button */
        .back-btn {
            position: absolute;
            top: 30px;
            left: 30px;
            z-index: 10;
            background: rgba(255,255,255,0.1);
            color: white;
            border: 1px solid rgba(255,255,255,0.2);
            padding: 10px 20px;
            border-radius: 50px;
            text-decoration: none;
            backdrop-filter: blur(10px);
            transition: all 0.3s;
        }

        .back-btn:hover {
            background: white;
            color: var(--dark);
        }
    </style>
</head>
<body>

    <a href="partners_may_like.php" class="back-btn">
        <i class="fas fa-arrow-left me-2"></i> Retour aux recommandations
    </a>

    <!-- Hero Section -->
    <div class="hero-profile">
        <div class="container">
            <div class="floating-logo">
                <img src="<?php echo resolveLogo($partner['logo']); ?>" alt="Logo" style="width: 100%; height: 100%; object-fit: contain;" onerror="this.onerror=null;this.src='<?php echo resolveLogo(null); ?>';">
            </div>
            <h1 style="font-weight: 800; font-size: 3.5rem;"><?php echo htmlspecialchars($partner['organization_name']); ?></h1>
            <p style="opacity: 0.8; font-size: 1.2rem;"><?php echo htmlspecialchars($partner['partner_type']); ?></p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container profile-container">
        <div class="row g-4">
            <!-- Left Side: Main Info -->
            <div class="col-lg-8">
                <div class="glass-card mb-4">
                    <h2 class="section-title"><i class="fas fa-info-circle text-primary"></i> À propos du partenaire</h2>
                    <p style="font-size: 1.1rem; line-height: 1.8; color: #475569;">
                        <?php echo nl2br(htmlspecialchars($partner['description'] ?: "Aucune description détaillée n'est disponible pour le moment.")); ?>
                    </p>
                    
                    <div class="mt-5 pt-4 border-top">
                        <h2 class="section-title"><i class="fas fa-map-marker-alt text-primary"></i> Localisation & Contact</h2>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="stat-badge">
                                    <div class="stat-icon" style="background: #e0e7ff; color: #4338ca;"><i class="fas fa-globe"></i></div>
                                    <div>
                                        <div style="font-size: 0.8rem; color: #94a3b8;">Pays</div>
                                        <div style="font-weight: 700;">Tunisie</div> <!-- Dynamiser plus tard si la colonne existe -->
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="stat-badge">
                                    <div class="stat-icon" style="background: #fef3c7; color: #d97706;"><i class="fas fa-envelope"></i></div>
                                    <div>
                                        <div style="font-size: 0.8rem; color: #94a3b8;">Contact</div>
                                        <div style="font-weight: 700;">contact@<?php echo strtolower(str_replace(' ', '', $partner['organization_name'])); ?>.com</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Stats & Actions -->
            <div class="col-lg-4">
                <div class="glass-card mb-4 text-center">
                    <h2 class="section-title">Performances</h2>
                    <div class="mb-4">
                        <div style="font-size: 3rem; font-weight: 800; color: var(--primary);"><?php echo number_format($partner['view_count']); ?></div>
                        <div style="text-transform: uppercase; letter-spacing: 2px; font-size: 0.8rem; color: #94a3b8;">Vues Totales</div>
                    </div>
                    <div class="match-meter-container mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span style="font-weight: 700;">Score IA</span>
                            <span style="color: var(--primary); font-weight: 700;">94%</span>
                        </div>
                        <div style="height: 8px; background: #e2e8f0; border-radius: 10px; overflow: hidden;">
                            <div style="width: 94%; height: 100%; background: linear-gradient(to right, var(--primary), var(--secondary));"></div>
                        </div>
                    </div>
                    <button class="btn btn-primary w-100 py-3" style="border-radius: 15px; font-weight: 700; background: var(--primary); border: none;">
                        Devenir Partenaire <i class="fas fa-handshake ms-2"></i>
                    </button>
                </div>

                <!-- Similar Partners Widget -->
                <div class="glass-card">
                    <h2 class="section-title">Partenaires Similaires</h2>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach($similarPartners as $similar): ?>
                            <a href="partner_details.php?id=<?php echo $similar['id']; ?>" style="text-decoration: none; color: inherit;">
                                <div class="similar-card d-flex align-items-center gap-3">
                                    <img src="<?php echo resolveLogo($similar['logo']); ?>" style="width: 40px; height: 40px; border-radius: 10px; object-fit: contain;" onerror="this.onerror=null;this.src='<?php echo resolveLogo(null); ?>';">
                                    <div style="font-weight: 700; font-size: 0.9rem;"><?php echo htmlspecialchars($similar['organization_name']); ?></div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Area -->
    <footer style="background: var(--dark); color: white; padding: 40px 0; text-align: center;">
        <div class="container">
            <p class="mb-0 opacity-50">&copy; 2026 EduMatch AI. Profil auto-généré par notre moteur intelligent.</p>
        </div>
    </footer>

    <!-- Note : le chatbot EduMatch est inclus dans _navbar.php (toutes pages frontoffice) -->

    <script src="../../assets/js/jquery-1.12.4.min.js"></script>
</body>
</html>
