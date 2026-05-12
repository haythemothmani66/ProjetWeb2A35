<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once $_SERVER['DOCUMENT_ROOT'] . '/gestion_users/config/database.php';
$baseUrl = '/gestion_users';
?>
<!DOCTYPE html>
<html lang="fr">

	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<title>EduMatch AI - Recommandations intelligentes</title>			
		<link rel="stylesheet" href="<?= $baseUrl ?>/assets/bootstrap/css/bootstrap.min.css">		
		<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
		<link href="https://fonts.googleapis.com/css2?family=Jost:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
		<link rel="stylesheet" href="<?= $baseUrl ?>/assets/fonts/themify-icons.css">
		<link rel="stylesheet" href="<?= $baseUrl ?>/assets/owlcarousel/css/owl.carousel.css">
		<link rel="stylesheet" href="<?= $baseUrl ?>/assets/owlcarousel/css/owl.theme.css">
		<link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/jquery-simple-mobilemenu.css">
		<link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/magnific-popup.css">
		<link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/animate.css">
		<link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/style.css">
        <style>
            :root {
                --ai-primary: #6366f1;
                --ai-secondary: #ff7f50;
                --ai-glass: rgba(255, 255, 255, 0.7);
                --ai-accent: #4f46e5;
            }

            body {
                background: #f8fafc;
                font-family: 'DM Sans', sans-serif;
            }

            .ai-nebula-bg {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: -1;
                background: radial-gradient(circle at 20% 30%, rgba(99, 102, 241, 0.05) 0%, transparent 40%),
                            radial-gradient(circle at 80% 70%, rgba(255, 127, 80, 0.05) 0%, transparent 40%);
                animation: nebulaFloat 20s infinite alternate;
            }

            @keyframes nebulaFloat {
                0% { transform: scale(1); }
                100% { transform: scale(1.1); }
            }

            .section-top-premium {
                padding: 120px 0 80px;
                background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
                color: white;
                position: relative;
                overflow: hidden;
                text-align: center;
            }

            .section-top-premium::before {
                content: '';
                position: absolute;
                top: 0; left: 0; right: 0; bottom: 0;
                background: url('https://www.transparenttextures.com/patterns/cubes.png');
                opacity: 0.1;
            }

            .ai-main-title {
                font-size: 3.5rem;
                font-weight: 800;
                background: linear-gradient(to right, #fff, #94a3b8);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                margin-bottom: 20px;
            }

            .partner-card {
                background: var(--ai-glass);
                backdrop-filter: blur(12px);
                border: 1px solid rgba(255, 255, 255, 0.3);
                border-radius: 24px;
                padding: 30px;
                transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                height: 100%;
                display: flex;
                flex-direction: column;
                position: relative;
                box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            }

            .partner-card:hover {
                transform: translateY(-12px) scale(1.02);
                box-shadow: 0 20px 40px rgba(99, 102, 241, 0.1);
                border-color: rgba(99, 102, 241, 0.4);
            }

            .partner-logo-container {
                width: 80px;
                height: 80px;
                background: white;
                border-radius: 20px;
                padding: 10px;
                box-shadow: 0 8px 20px rgba(0,0,0,0.05);
                margin-bottom: 20px;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.3s ease;
            }

            .partner-card:hover .partner-logo-container {
                transform: rotate(-5deg);
            }

            .partner-logo {
                width: 100%;
                height: 100%;
                object-fit: contain;
                border-radius: 12px;
            }

            .partner-name {
                font-size: 1.4rem;
                font-weight: 700;
                color: #1e293b;
                margin-bottom: 8px;
            }

            .match-meter {
                height: 6px;
                background: #e2e8f0;
                border-radius: 10px;
                margin: 15px 0;
                overflow: hidden;
                position: relative;
            }

            .match-progress {
                height: 100%;
                background: linear-gradient(to right, var(--ai-primary), #818cf8);
                border-radius: 10px;
                transition: width 1s ease-out;
            }

            .match-label {
                font-size: 0.75rem;
                font-weight: 700;
                color: var(--ai-primary);
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .badges-wrapper {
                display: flex;
                gap: 8px;
                margin-bottom: 15px;
            }

            .badge-ai {
                font-size: 0.7rem;
                font-weight: 700;
                padding: 6px 14px;
                border-radius: 50px;
                display: flex;
                align-items: center;
                gap: 6px;
                transition: all 0.3s ease;
            }

            .badge-new {
                background: #f0f9ff;
                color: #0369a1;
                border: 1px solid #bae6fd;
            }

            .badge-popular {
                background: #fff7ed;
                color: #c2410c;
                border: 1px solid #fed7aa;
                animation: pulseGlow 2s infinite;
            }

            @keyframes pulseGlow {
                0% { box-shadow: 0 0 0 0 rgba(255, 127, 80, 0.4); }
                70% { box-shadow: 0 0 0 10px rgba(255, 127, 80, 0); }
                100% { box-shadow: 0 0 0 0 rgba(255, 127, 80, 0); }
            }

            .theme-header {
                margin: 60px 0 30px;
                display: flex;
                align-items: center;
                gap: 20px;
            }

            .theme-icon {
                width: 50px;
                height: 50px;
                background: white;
                color: var(--ai-primary);
                border-radius: 15px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 20px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            }

            .theme-title {
                font-size: 1.8rem;
                font-weight: 800;
                color: #1e293b;
                margin: 0;
            }

            .ai-loader {
                width: 80px;
                height: 80px;
                margin: 0 auto;
                position: relative;
            }

            .ai-loader div {
                position: absolute;
                width: 100%;
                height: 100%;
                border: 4px solid transparent;
                border-top-color: var(--ai-primary);
                border-radius: 50%;
                animation: aiSpin 1.5s linear infinite;
            }

            @keyframes aiSpin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }

            .header-group { display: flex; flex-direction: row; align-items: center; gap: 10px; }
            .btn-backoffice { background: linear-gradient(135deg, #6366f1, #8B5CF6); color: white; padding: 10px 20px; border-radius: 2px; text-decoration: none; font-weight: 600; font-size: 13px; transition: all 0.3s ease; display: inline-block; text-align: center; border: none; cursor: pointer; }
            .btn-backoffice:hover { background: linear-gradient(135deg, #4f46e5, #7c3aed); color: white; text-decoration: none; }
            .user-dropdown { position: relative; display: flex; align-items: center; gap: 8px; cursor: pointer; }
            .user-dropdown .user-avatar { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; border: 2px solid #525fe1; }
            .user-dropdown .user-name { font-weight: 600; font-size: 14px; color: #0b104a; white-space: nowrap; }
            .user-dropdown .dropdown-caret { font-size: 10px; color: #6c757d; transition: transform 0.2s; }
            .user-dropdown:hover .dropdown-caret { transform: rotate(180deg); }
            .user-dropdown-menu { display: none; position: absolute; top: 100%; right: 0; background: white; border-radius: 10px; box-shadow: 0 8px 25px rgba(0,0,0,0.12); min-width: 200px; padding: 8px 0; z-index: 1000; margin-top: 8px; }
            .user-dropdown-menu.show { display: block; }
            .user-dropdown-menu a { display: flex; align-items: center; gap: 10px; padding: 10px 18px; color: #333; text-decoration: none; font-size: 14px; font-weight: 500; transition: background 0.2s; }
            .user-dropdown-menu a:hover { background: #f5f7fa; color: #525fe1; }
            .user-dropdown-menu a i { width: 18px; text-align: center; }
            .user-dropdown-menu hr { margin: 6px 0; border-color: #eee; }
        </style>
	</head>
	
    <body>
        <div class="ai-nebula-bg"></div>

		<?php include $_SERVER['DOCUMENT_ROOT'] . '/gestion_users/view/template/_navbar.php'; ?>

		<!-- START HEADER -->
		<header class="section-top-premium">
			<div class="container">
				<div class="row">
					<div class="col-lg-10 offset-lg-1">
						<span style="color: var(--ai-primary); font-weight: 700; text-transform: uppercase; letter-spacing: 2px;">Decouverte propulsee par l'IA</span>
						<h1 class="ai-main-title">Partenaires qui pourraient vous interesser</h1>
						<p style="color: #94a3b8; font-size: 1.2rem; max-width: 700px; margin: 0 auto;">Notre moteur intelligent analyse des milliers de donnees pour trouver les organisations qui correspondent parfaitement a vos objectifs academiques et professionnels.</p>
					</div>
				</div>
			</div>
		</header>	
		<!-- END HEADER -->

		<!-- START PARTNERS GRID -->
		<section class="partners_grid_area section-padding">
			<div class="container">
                <div id="loader" class="text-center py-5">
                    <div class="ai-loader"><div></div></div>
                    <p class="mt-4" style="font-weight: 600; color: #64748b;">Synchronisation avec le moteur neuronal...</p>
                </div>

				<div class="row" id="partners-grid" style="display: none;">
                    <!-- Partners will be loaded here -->
                </div>
			</div>
		</section>
		<!-- END PARTNERS GRID -->

		<!-- START FOOTER -->
		<footer class="footer py-5" style="background: #0f172a; color: white; border-top: 1px solid rgba(255,255,255,0.05);">
			<div class="container text-center">
				<p class="mb-0 opacity-50">&copy; 2026 EduMatch AI. Propulse l'avenir des connexions academiques.</p>
			</div>
		</footer>
		<!-- END FOOTER -->

        <!-- CHATBOT PLACEHOLDER -->
        <div id="chatbot-placeholder"></div>

		<!-- Scripts -->
		<script src="<?= $baseUrl ?>/assets/js/jquery-1.12.4.min.js"></script>
		<script src="<?= $baseUrl ?>/assets/bootstrap/js/bootstrap.min.js"></script>
		<script src="<?= $baseUrl ?>/assets/js/modernizr-2.8.3.min.js"></script>
		<script src="<?= $baseUrl ?>/assets/js/jquery-simple-mobilemenu.js"></script>
		<script src="<?= $baseUrl ?>/assets/owlcarousel/js/owl.carousel.min.js"></script>
		<script src="<?= $baseUrl ?>/assets/js/jquery.magnific-popup.min.js"></script>
		<script src="<?= $baseUrl ?>/assets/js/jquery.inview.min.js"></script>
		<script src="<?= $baseUrl ?>/assets/js/scrolltopcontrol.js"></script>
		<script src="<?= $baseUrl ?>/assets/js/wow.min.js"></script>
		<script src="<?= $baseUrl ?>/assets/js/scripts.js"></script>
        
        <script>
            $(document).ready(function() {
                // Chatbot : desormais inclus dans _navbar.php (toutes pages), ancien fetch retire pour eviter doublon

                // Fetch recommendations from API
                fetch('<?= $baseUrl ?>/api/get_recommendations.php?limit=12') 
                    .then(response => response.json())
                    .then(data => {
                        $('#loader').fadeOut();
                        const grid = $('#partners-grid');
                        
                        setTimeout(() => {
                            grid.fadeIn();

                            if (data.success && data.data.length > 0) {
                                let currentTheme = '';
                                data.data.forEach((partner, index) => {
                                    if (partner.theme && partner.theme !== currentTheme) {
                                        currentTheme = partner.theme;
                                        let themeIcon = 'fa-brain';
                                        if(currentTheme.includes('Tech')) themeIcon = 'fa-microchip';
                                        if(currentTheme.includes('Organic')) themeIcon = 'fa-leaf';
                                        if(currentTheme.includes('Education')) themeIcon = 'fa-graduation-cap';

                                        grid.append(`
                                            <div class="col-12 wow fadeInUp">
                                                <div class="theme-header">
                                                    <div class="theme-icon"><i class="fas ${themeIcon}"></i></div>
                                                    <h2 class="theme-title">${currentTheme}</h2>
                                                </div>
                                            </div>
                                        `);
                                    }

                                    // Placeholder SVG inline (data URI) - jamais casse, pas de dependance
                                    const placeholderLogo = 'data:image/svg+xml;utf8,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 150 150"><defs><linearGradient id="g" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#6366f1"/><stop offset="100%" stop-color="#8B5CF6"/></linearGradient></defs><rect width="150" height="150" fill="url(#g)" rx="20"/><text x="75" y="95" font-family="Arial" font-size="60" fill="white" text-anchor="middle" font-weight="700">🏢</text></svg>');
                                    let logo = placeholderLogo;
                                    if (partner.logo) {
                                        if (/^https?:\/\//i.test(partner.logo)) {
                                            // URL externe (Unsplash, etc.)
                                            logo = partner.logo;
                                        } else if (partner.logo.indexOf('assets/uploads/') === 0) {
                                            // Chemin relatif vers uploads
                                            logo = '<?= $baseUrl ?>/' + partner.logo;
                                        } else {
                                            // Nom de fichier seul (legacy uploads)
                                            logo = '<?= $baseUrl ?>/assets/uploads/partners/' + partner.logo;
                                        }
                                    }
                                    const score = Math.round(partner.similarity_score || 0);
                                    
                                    let badgesHtml = '<div class="badges-wrapper">';
                                    if (partner.badges && Array.isArray(partner.badges)) {
                                        partner.badges.forEach(badge => {
                                            let label = typeof badge === 'object' ? (badge.label || 'Badge') : badge;
                                            let icon = typeof badge === 'object' ? (badge.icon || '') : '';
                                            let badgeClass = 'badge-ai';
                                            
                                            const labelLower = label.toLowerCase();
                                            if (labelLower.includes('new') || labelLower.includes('nouveau')) {
                                                badgeClass += ' badge-new';
                                                if (!icon) icon = '✨';
                                            } else if (labelLower.includes('pop')) {
                                                badgeClass += ' badge-popular';
                                                if (!icon) icon = '🔥';
                                            }
                                            badgesHtml += `<span class="${badgeClass}">${icon ? icon + ' ' : ''}${label}</span>`;
                                        });
                                    }
                                    badgesHtml += '</div>';

                                    const card = `
                                        <div class="col-lg-4 col-md-6 mb-4 wow fadeInUp" data-wow-delay="${index * 0.1}s">
                                            <div class="partner-card">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div class="partner-logo-container">
                                                        <img src="${logo}" alt="${partner.organization_name}" class="partner-logo" onerror="this.onerror=null;this.src='${placeholderLogo}';">
                                                    </div>
                                                    <div class="text-end">
                                                        <span class="match-label">Compatibilite</span>
                                                        <div style="font-size: 1.2rem; font-weight: 800; color: #1e293b;">${score}%</div>
                                                    </div>
                                                </div>
                                                <h3 class="partner-name">${partner.organization_name}</h3>
                                                <div style="color: #64748b; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; margin-bottom: 15px;">${partner.partner_type}</div>
                                                
                                                <div class="match-meter">
                                                    <div class="match-progress" style="width: ${score}%"></div>
                                                </div>

                                                ${badgesHtml}
                                                
                                                <p style="font-size: 0.95rem; color: #475569; line-height: 1.6; flex-grow: 1;">${partner.description || 'Aucune description disponible.'}</p>
                                                
                                                <div class="mt-4">
                                                    <a href="partner_details.php?id=${partner.id}" class="btn btn-primary w-100" style="background: var(--ai-primary); border: none; border-radius: 12px; padding: 12px; font-weight: 600; transition: all 0.3s ease;">
                                                        Explorer le profil <i class="fas fa-chevron-right ms-2"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    `;
                                    grid.append(card);
                                });
                            } else {
                                grid.html('<div class="col-12 text-center py-5"><p>Donnees neuronales vides. Veuillez revenir plus tard !</p></div>');
                            }
                        }, 800);
                    })
                    .catch(error => {
                        $('#loader').hide();
                        $('#partners-grid').show().html('<div class="col-12 text-center text-danger"><p>Erreur de connexion avec le moteur neuronal.</p></div>');
                    });
            });
        </script>
	</body>
</html>
