<?php
declare(strict_types=1);

// Guard : ce fichier doit etre inclus par le controller, pas accede directement
if (!isset($recommendations)) {
    if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
    if (empty($_SESSION['user_id'])) {
        header('Location: /gestion_users/view/template/sign-in.php');
        exit;
    }
    // Si accede directement sans controller, rediriger vers la page recommandations
    header('Location: /gestion_users/view/frontoffice/partners_may_like.php');
    exit;
}
/** @var array $recommendations */
/** @var array|null $targetPartner */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Partners You May Like - EduMatch AI</title>
    
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        :root {
            --primary-color: #ff7f50;
            --secondary-color: #2d3436;
            --bg-gradient: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background-color: #f8f9fa;
        }

        .recommendation-header {
            background: var(--bg-gradient);
            padding: 80px 0 40px;
            text-align: center;
            margin-bottom: 50px;
        }

        .ai-badge {
            background: rgba(255, 127, 80, 0.1);
            color: var(--primary-color);
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
            display: inline-block;
        }

        .partner-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            height: 100%;
            border: 1px solid rgba(0,0,0,0.02);
            position: relative;
            overflow: hidden;
        }

        .partner-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }

        .similarity-score {
            position: absolute;
            top: 20px;
            right: 20px;
            background: #4caf50;
            color: white;
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .partner-logo {
            width: 80px;
            height: 80px;
            border-radius: 15px;
            object-fit: cover;
            margin-bottom: 20px;
            background: #f0f2f5;
        }

        .partner-name {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 5px;
        }

        .partner-type {
            color: var(--primary-color);
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 15px;
        }

        .partner-desc {
            font-size: 0.9rem;
            color: #636e72;
            line-height: 1.6;
            margin-bottom: 20px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .btn-view {
            background: var(--secondary-color);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 10px;
            font-weight: 600;
            transition: 0.3s;
            width: 100%;
        }

        .btn-view:hover {
            background: var(--primary-color);
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 100px 0;
        }

        .empty-state i {
            font-size: 4rem;
            color: #dfe6e9;
            margin-bottom: 20px;
        }

        /* External Badges Styles */
        .badges-container {
            margin-bottom: 15px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .badge-custom {
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-new {
            background-color: #e0fbff;
            color: #00b8d9;
            border: 1px solid #00b8d9;
        }

        .badge-popular {
            background-color: #fff9db;
            color: #fcc419;
            border: 1px solid #fcc419;
        }
    </style>
</head>
<body>

    <section class="recommendation-header">
        <div class="container">
            <span class="ai-badge"><i class="fas fa-robot me-2"></i> AI-Powered Recommendations</span>
            <?php if ($targetPartner): ?>
                <h1>Partners similar to <strong><?= htmlspecialchars($targetPartner['organization_name']) ?></strong></h1>
                <p class="text-muted">Based on organization type, domain of expertise, and mission statement.</p>
            <?php else: ?>
                <h1>Partners You May Like</h1>
                <p class="text-muted">Discover companies and organizations that match your profile.</p>
            <?php endif; ?>
        </div>
    </section>

    <div class="container mb-5">
        <div class="row g-4">
            <?php if (empty($recommendations)): ?>
                <div class="col-12">
                    <div class="empty-state">
                        <i class="fas fa-search"></i>
                        <h3>No recommendations found yet</h3>
                        <p class="text-muted">Add more information to your profile or wait for more partners to join.</p>
                        <a href="index.php?controller=frontoffice&page=partenariat" class="btn btn-primary mt-3">Apply as Partner</a>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($recommendations as $partner): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="partner-card">
                            <span class="similarity-score">
                                <?= number_format($partner['similarity_score'], 0) ?>% Match
                            </span>
                            
                            <?php if ($partner['logo']): ?>
                                <img src="<?= htmlspecialchars($partner['logo']) ?>" alt="Logo" class="partner-logo">
                            <?php else: ?>
                                <div class="partner-logo d-flex align-items-center justify-content-center">
                                    <i class="fas fa-building fa-2x text-muted"></i>
                                </div>
                            <?php endif; ?>

                            <h3 class="partner-name"><?= htmlspecialchars($partner['organization_name']) ?></h3>
                            <div class="partner-type"><?= htmlspecialchars($partner['partner_type']) ?></div>
                            
                            <?php if (!empty($partner['badges']) && is_array($partner['badges'])): ?>
                                <div class="badges-container">
                                    <?php foreach ($partner['badges'] as $badge): ?>
                                        <?php 
                                            $badgeText = is_array($badge) ? ($badge['label'] ?? 'Badge') : $badge;
                                            $badgeIcon = is_array($badge) ? ($badge['icon'] ?? '') : '';
                                            
                                            $badgeClass = 'badge-custom';
                                            $badgeTextLower = mb_strtolower((string)$badgeText);
                                            
                                            if (strpos($badgeTextLower, 'new') !== false || strpos($badgeTextLower, 'nouveau') !== false) {
                                                $badgeClass .= ' badge-new';
                                                $defaultIcon = 'sparkles';
                                            } elseif (strpos($badgeTextLower, 'pop') !== false) {
                                                $badgeClass .= ' badge-popular';
                                                $defaultIcon = 'fire';
                                            } else {
                                                $badgeClass .= ' bg-secondary text-white';
                                                $defaultIcon = 'tag';
                                            }

                                            $iconClass = !empty($badgeIcon) ? "" : "fas fa-$defaultIcon";
                                        ?>
                                        <span class="<?= $badgeClass ?> mb-1">
                                            <?php if ($iconClass): ?><i class="<?= $iconClass ?> me-1"></i><?php endif; ?>
                                            <?= !empty($badgeIcon) ? $badgeIcon . ' ' : '' ?>
                                            <?= htmlspecialchars((string)$badgeText) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <p class="partner-desc"><?= htmlspecialchars($partner['description'] ?: 'No description available.') ?></p>
                            
                            <button class="btn-view">View Details</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <footer class="py-5 bg-white border-top text-center">
        <div class="container">
            <p class="text-muted mb-0">&copy; <?= date('Y') ?> EduMatch Platform. Smart Recommendation Engine v1.0</p>
        </div>
    </footer>

</body>
</html>
