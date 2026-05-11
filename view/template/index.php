<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/check_blocked.php';
/* Auto-refresh session data if user_prenom is missing (old session) */
if (!empty($_SESSION['user_id']) && empty($_SESSION['user_prenom'])) {
    $stmt = Config::getConnexion()->prepare("SELECT nom, prenom, photo FROM user WHERE id = ? LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    $u = $stmt->fetch();
    if ($u) {
        $_SESSION['user_nom']    = $u['nom'];
        $_SESSION['user_prenom'] = $u['prenom'];
        $_SESSION['user_photo']  = $u['photo'];
    }
}

/* ============================================================
   Recuperer les evenements actifs pour la section "Decouvrez nos Evenements"
   ============================================================ */
$homeEvents = [];
$homePartners = [];
$homeDevoirsStats = [
    'total_devoirs' => 0,
    'total_corrections' => 0,
    'urgents' => 0,
    'sentiment_positif' => 0,
];
$homeRecentDevoirs = [];
// Placeholder SVG inline (data URI) - pas de dependance externe, toujours dispo
$homePlaceholderImg = 'data:image/svg+xml;utf8,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 250"><defs><linearGradient id="g" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#525fe1"/><stop offset="100%" stop-color="#00D4FF"/></linearGradient></defs><rect width="400" height="250" fill="url(#g)"/><g fill="white" opacity="0.9" font-family="Arial,sans-serif" text-anchor="middle"><text x="200" y="115" font-size="56" font-weight="700">📅</text><text x="200" y="160" font-size="22" font-weight="600">EduMatch Event</text></g></svg>');
$homePartnerPlaceholder = 'data:image/svg+xml;utf8,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 250"><defs><linearGradient id="g2" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#525fe1"/><stop offset="100%" stop-color="#8B5CF6"/></linearGradient></defs><rect width="400" height="250" fill="url(#g2)"/><text x="200" y="155" font-size="80" fill="white" text-anchor="middle" font-weight="700">🏢</text></svg>');
try {
    $pdoHome = Config::getConnexion();
    $stmtHome = $pdoHome->query("
        SELECT e.*, c.nom_categorie, c.couleur,
               (SELECT COUNT(*) FROM participations p WHERE p.id_evenement = e.id_evenement AND p.statut_participation <> 'annulé') AS nb_participants
        FROM evenements e
        LEFT JOIN categories c ON e.id_categorie = c.id_categorie
        WHERE c.statut = 'actif' AND e.statut IN ('planifié', 'en cours')
        ORDER BY e.date_debut ASC
    ");
    $homeEvents = $stmtHome->fetchAll(PDO::FETCH_ASSOC);

    // Recuperer 3 partenaires approved (les plus populaires par view_count)
    $stmtPartners = $pdoHome->query("
        SELECT id, organization_name, partner_type, logo, description, view_count
        FROM partenaires
        WHERE status = 'approved'
        ORDER BY view_count DESC, id DESC
        LIMIT 3
    ");
    $homePartners = $stmtPartners->fetchAll(PDO::FETCH_ASSOC);

    // Stats module Devoirs
    $homeDevoirsStats['total_devoirs'] = (int) $pdoHome->query("SELECT COUNT(*) FROM devoirs")->fetchColumn();
    $homeDevoirsStats['total_corrections'] = (int) $pdoHome->query("SELECT COUNT(*) FROM correction")->fetchColumn();
    $homeDevoirsStats['urgents'] = (int) $pdoHome->query("SELECT COUNT(*) FROM devoirs WHERE alerte_urgence = 1 OR urgence = 'urgente'")->fetchColumn();
    $homeDevoirsStats['sentiment_positif'] = (int) $pdoHome->query("SELECT COUNT(*) FROM devoirs WHERE sentiment = 'positif'")->fetchColumn();

    // 3 devoirs recents pour preview (avec compteur de corrections)
    $stmtDevoirs = $pdoHome->query("
        SELECT d.id_devoir, d.titre, d.description, d.niveau_difficulte, d.urgence, d.sentiment, d.mots_cles, d.date_soumission,
               (SELECT COUNT(*) FROM correction c WHERE c.id_devoir = d.id_devoir) AS nb_corrections
        FROM devoirs d
        ORDER BY d.id_devoir DESC
        LIMIT 3
    ");
    $homeRecentDevoirs = $stmtDevoirs->fetchAll(PDO::FETCH_ASSOC);

    // 3 offres d'emploi ouvertes les plus recentes (avec nb candidatures)
    $stmtOffres = $pdoHome->query("
        SELECT o.id, o.titre, o.lieu, o.type_contrat, o.date_limite, o.statut, o.date_creation,
               (SELECT COUNT(*) FROM candidature c WHERE c.offre_id = o.id) AS nb_candidatures
        FROM offre_emploi o
        WHERE o.statut = 'ouverte'
        ORDER BY o.date_creation DESC
        LIMIT 3
    ");
    $homeOffres = $stmtOffres->fetchAll(PDO::FETCH_ASSOC);

    // Stats globales offres
    $homeOffresStats = [
        'total'    => (int) $pdoHome->query("SELECT COUNT(*) FROM offre_emploi")->fetchColumn(),
        'ouvertes' => (int) $pdoHome->query("SELECT COUNT(*) FROM offre_emploi WHERE statut='ouverte'")->fetchColumn(),
        'candidatures' => (int) $pdoHome->query("SELECT COUNT(*) FROM candidature")->fetchColumn(),
    ];
} catch (Throwable $e) {
    $homeEvents = [];
    $homePartners = [];
    $homeRecentDevoirs = [];
    $homeOffres = [];
    $homeOffresStats = ['total' => 0, 'ouvertes' => 0, 'candidatures' => 0];
}
?>
<!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>EduMatch - Plateforme Educative</title>
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Jost:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link rel="stylesheet" href="../../assets/fonts/font-awesome.min.css" />
    <link rel="stylesheet" href="../../assets/fonts/themify-icons.css" />
    <link rel="stylesheet" href="../../assets/owlcarousel/css/owl.carousel.css" />
    <link rel="stylesheet" href="../../assets/owlcarousel/css/owl.theme.css" />
    <link rel="stylesheet" href="../../assets/css/jquery-simple-mobilemenu.css" />
    <link rel="stylesheet" href="../../assets/css/magnific-popup.css" />
    <link rel="stylesheet" href="../../assets/css/animate.css" />
    <link rel="stylesheet" href="../../assets/css/style.css" />
    <style>
      /* ===== Section Evenements home (creative cards) ===== */
      .event-card-creative:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(15,23,42,0.15) !important;
      }
      .event-card-creative:hover img {
        transform: scale(1.08);
      }
      .event-card-creative a.btn:hover {
        background: linear-gradient(135deg, #1e293b, #334155) !important;
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(0,0,0,0.2);
      }

      /* ===== Section Devoirs home (creative) ===== */
      .step-card:hover {
        transform: translateX(8px);
        box-shadow: 0 8px 24px rgba(99,102,241,0.15) !important;
      }
      .stat-tile:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 30px rgba(99,102,241,0.2) !important;
      }
      .recent-devoirs-list > div:last-child {
        border-bottom: none !important;
      }

      /* ===== Section Partenaires home (creative cards) ===== */
      .partner-card-creative:hover {
        transform: translateY(-10px);
        background: rgba(255,255,255,0.15) !important;
        border-color: rgba(255,255,255,0.3) !important;
        box-shadow: 0 25px 50px rgba(0,0,0,0.3);
      }
      .partner-card-creative:hover img {
        transform: scale(1.1);
      }
      .partner-card-creative a.btn:hover {
        background: white !important;
        color: #1e1b4b !important;
        transform: translateY(-2px);
      }
      /* ===== Section Offres d'emploi home (creative cards) ===== */
      .job-card-creative:hover {
        transform: translateY(-10px) scale(1.02);
        background: rgba(255,255,255,0.15) !important;
        border-color: rgba(255,255,255,0.3) !important;
        box-shadow: 0 25px 55px rgba(0,0,0,0.4), 0 0 0 1px rgba(82, 95, 225, 0.3);
      }
      .job-card-creative a.btn:hover {
        transform: translateY(-2px);
        filter: brightness(1.1);
      }
      .job-offers-section .col-4 > div:hover {
        transform: translateY(-4px);
      }
      .header-group { display: flex; flex-direction: row; align-items: center; gap: 10px; }
      .btn-backoffice { background: linear-gradient(135deg, #525fe1, #3b47c9); color: white; padding: 10px 20px; border-radius: 2px; text-decoration: none; font-weight: 600; font-size: 13px; transition: all 0.3s ease; display: inline-block; text-align: center; border: none; cursor: pointer; }
      .btn-backoffice:hover { background: linear-gradient(135deg, #4f46e5, #7c3aed); color: white; text-decoration: none; }
      /* User dropdown navbar */
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

  <body data-spy="scroll" data-offset="80">
    <!-- START PRELOADER -->
    <div class="preloaders"><span class="loader"></span></div>
    <!-- END PRELOADER -->

    <?php include __DIR__ . '/_navbar.php'; ?>

    <!-- START HERO -->
    <section class="hero-section" style="background: linear-gradient(135deg, #525fe1 0%, #3b47c9 50%, #1e1b4b 100%); min-height: 100vh; display: flex; align-items: center;">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-lg-6 col-md-12">
            <div class="hero-content">
              <h1 class="hero-title" style="font-size: 3.5rem; font-weight: 700; color: white; margin-bottom: 1.5rem; line-height: 1.2;">
                EduMatch – <span style="color: #00D4FF;">Connectez les Etudiants</span><br>avec des Professeurs Experts
              </h1>
              <p class="hero-subtitle" style="font-size: 1.25rem; color: rgba(255,255,255,0.9); margin-bottom: 2rem; line-height: 1.6;">
                Trouvez le professeur ideal pour vos besoins academiques. Des mises en relation personnalisees dans toutes les matieres pour une meilleure comprehension et une reussite scolaire.
              </p>
              <div class="hero-ctas">
                <?php if (empty($_SESSION['user_id'])): ?>
                <a href="sign-up.php" class="btn btn-primary btn-lg me-3" style="background: linear-gradient(45deg, #00D4FF, #525fe1); border: none; padding: 1rem 2rem; border-radius: 50px; font-weight: 600; text-decoration: none; color: white; transition: all 0.3s ease;">
                  Commencer <i class="fas fa-rocket ms-2"></i>
                </a>
                <a href="sign-in.php" class="btn btn-outline-light btn-lg" style="border: 2px solid white; color: white; padding: 1rem 2rem; border-radius: 50px; font-weight: 600; text-decoration: none; transition: all 0.3s ease;">
                  Connexion <i class="fas fa-sign-in-alt ms-2"></i>
                </a>
                <?php else: ?>
                <a href="profil.php" class="btn btn-primary btn-lg me-3" style="background: linear-gradient(45deg, #00D4FF, #525fe1); border: none; padding: 1rem 2rem; border-radius: 50px; font-weight: 600; text-decoration: none; color: white; transition: all 0.3s ease;">
                  Mon Profil <i class="fas fa-user ms-2"></i>
                </a>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <div class="col-lg-6 col-md-12 text-center">
            <div class="hero-image">
              <img src="../../assets/img/home-img2.png" alt="EduMatch Platform" class="img-fluid" style="max-width: 80%; filter: drop-shadow(0 20px 40px rgba(0,0,0,0.2));">
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- END HERO -->

    <!-- START CALL TO ACTION -->
    <section class="cta-section py-5" style="background: linear-gradient(135deg, #525fe1 0%, #3b47c9 100%); color: white;">
      <div class="container text-center">
        <h2 class="cta-title" style="font-size: 2.5rem; font-weight: 700; margin-bottom: 1rem;">Pret a trouver votre professeur ideal ?</h2>
        <p class="cta-subtitle" style="font-size: 1.25rem; margin-bottom: 2rem; opacity: 0.9;">Rejoignez des milliers d'etudiants qui ont trouve leur professeur ideal dans toutes les matieres. Commencez votre parcours vers l'excellence academique aujourd'hui.</p>
        <div class="cta-buttons">
          <?php if (empty($_SESSION['user_id'])): ?>
          <a href="sign-up.php" class="btn btn-light btn-lg me-3" style="background: white; color: #525fe1; border: none; padding: 1rem 2rem; border-radius: 50px; font-weight: 600; text-decoration: none; transition: all 0.3s ease;">
            Rejoindre EduMatch <i class="fas fa-rocket ms-2"></i>
          </a>
          <a href="sign-in.php" class="btn btn-outline-light btn-lg" style="border: 2px solid white; color: white; padding: 1rem 2rem; border-radius: 50px; font-weight: 600; text-decoration: none; transition: all 0.3s ease;">
            Deja inscrit ? <i class="fas fa-sign-in-alt ms-2"></i>
          </a>
          <?php else: ?>
          <a href="/gestion_users/view/frontoffice/encadrants_list.php" class="btn btn-light btn-lg" style="background: white; color: #525fe1; border: none; padding: 1rem 2rem; border-radius: 50px; font-weight: 600; text-decoration: none; transition: all 0.3s ease;">
            Reserver une seance <i class="fas fa-calendar-plus ms-2"></i>
          </a>
          <?php endif; ?>
        </div>
      </div>
    </section>
    <!-- END CALL TO ACTION -->

<?php
  // Variables de role pour boutons partenariat (utilisees dans la section partenaires plus bas)
  $userRole = $_SESSION['user_role'] ?? '';
  $isLoggedIn = !empty($_SESSION['user_id']);
?>

    <!-- START DEVOIRS SECTION (Creative) -->
    <section class="devoirs-section py-5" style="background: linear-gradient(135deg, #faf5ff 0%, #ede9fe 50%, #e0e7ff 100%); position: relative; overflow: hidden;">
      <!-- Decorations -->
      <div style="position: absolute; top: -120px; left: -120px; width: 450px; height: 450px; background: radial-gradient(circle, rgba(99,102,241,0.12) 0%, transparent 65%); border-radius: 50%; pointer-events: none;"></div>
      <div style="position: absolute; bottom: -100px; right: -80px; width: 380px; height: 380px; background: radial-gradient(circle, rgba(139,92,246,0.12) 0%, transparent 65%); border-radius: 50%; pointer-events: none;"></div>

      <!-- Floating books illustration (decoration) -->
      <div style="position: absolute; top: 15%; right: 8%; font-size: 3rem; opacity: 0.08; pointer-events: none; transform: rotate(-15deg);">📚</div>
      <div style="position: absolute; bottom: 20%; left: 5%; font-size: 2.5rem; opacity: 0.08; pointer-events: none; transform: rotate(20deg);">✏️</div>

      <div class="container position-relative">

        <!-- Header -->
        <div class="row mb-5 text-center">
          <div class="col-lg-8 mx-auto">
            <span class="badge mb-3" style="background: linear-gradient(135deg, #525fe1, #3b47c9); color: white; padding: 0.5rem 1.25rem; border-radius: 50px; font-size: 0.85rem; font-weight: 600; letter-spacing: 1px;">
              <i class="fas fa-graduation-cap me-1"></i> EDUFEED
            </span>
            <h2 class="display-5 fw-bold mb-3" style="color: #312e81;">Soumettez. Corrigez. Progressez.</h2>
            <p class="lead mb-0" style="color: #4338ca;">
              Une plateforme intelligente ou les etudiants soumettent leurs devoirs et les encadrants les corrigent avec l'aide de l'IA.
            </p>
          </div>
        </div>

        <div class="row g-4 mb-5 align-items-stretch">

          <!-- LEFT : Comment ca marche (3 etapes visuelles) -->
          <div class="col-lg-6">
            <h3 class="fw-bold mb-4" style="color: #312e81;">Comment ca marche ?</h3>

            <div class="devoirs-steps">

              <!-- Etape 1 : Soumettre -->
              <div class="step-card mb-3" style="background: white; border-radius: 1rem; padding: 1.25rem; box-shadow: 0 4px 12px rgba(99,102,241,0.08); border-left: 4px solid #525fe1; transition: all 0.3s ease; position: relative;">
                <div class="d-flex align-items-start gap-3">
                  <div style="flex-shrink: 0; width: 50px; height: 50px; background: linear-gradient(135deg, #525fe1, #3b47c9); border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; font-weight: 800; box-shadow: 0 4px 12px rgba(82,95,225,0.3);">1</div>
                  <div>
                    <h5 class="fw-bold mb-1" style="color: #312e81;">
                      <i class="fas fa-upload me-2" style="color: #525fe1;"></i>Soumettre un devoir
                    </h5>
                    <p class="mb-0 text-muted" style="font-size: 0.92rem;">
                      Etudiants : deposez vos devoirs avec niveau, mots-cles et urgence. L'IA analyse automatiquement le sentiment de votre demande.
                    </p>
                  </div>
                </div>
              </div>

              <!-- Etape 2 : Correction -->
              <div class="step-card mb-3" style="background: white; border-radius: 1rem; padding: 1.25rem; box-shadow: 0 4px 12px rgba(139,92,246,0.08); border-left: 4px solid #8B5CF6; transition: all 0.3s ease;">
                <div class="d-flex align-items-start gap-3">
                  <div style="flex-shrink: 0; width: 50px; height: 50px; background: linear-gradient(135deg, #8B5CF6, #a855f7); border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; font-weight: 800; box-shadow: 0 4px 12px rgba(139,92,246,0.3);">2</div>
                  <div>
                    <h5 class="fw-bold mb-1" style="color: #312e81;">
                      <i class="fas fa-pen-fancy me-2" style="color: #8B5CF6;"></i>Correction par encadrants
                    </h5>
                    <p class="mb-0 text-muted" style="font-size: 0.92rem;">
                      Encadrants : corrigez avec commentaires detailles, notes, ton du feedback et generation automatique grace a Groq AI.
                    </p>
                  </div>
                </div>
              </div>

              <!-- Etape 3 : Feedback -->
              <div class="step-card mb-3" style="background: white; border-radius: 1rem; padding: 1.25rem; box-shadow: 0 4px 12px rgba(168,85,247,0.08); border-left: 4px solid #a855f7; transition: all 0.3s ease;">
                <div class="d-flex align-items-start gap-3">
                  <div style="flex-shrink: 0; width: 50px; height: 50px; background: linear-gradient(135deg, #a855f7, #c026d3); border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; font-weight: 800; box-shadow: 0 4px 12px rgba(168,85,247,0.3);">3</div>
                  <div>
                    <h5 class="fw-bold mb-1" style="color: #312e81;">
                      <i class="fas fa-chart-line me-2" style="color: #a855f7;"></i>Suivi & Progression
                    </h5>
                    <p class="mb-0 text-muted" style="font-size: 0.92rem;">
                      Consultez le feed unifie, suivez votre progression, recevez des suggestions personnalisees et ressources recommandees.
                    </p>
                  </div>
                </div>
              </div>

            </div>

            <!-- CTA buttons -->
            <div class="d-flex flex-wrap gap-3 mt-4">
              <?php if (!empty($_SESSION['user_id'])): ?>
                <a href="/gestion_users/view/frontoffice/submit.php" class="btn btn-lg" style="background: linear-gradient(135deg, #525fe1, #3b47c9); color: white; border: none; padding: 0.85rem 2rem; border-radius: 50px; font-weight: 700; text-decoration: none; transition: all 0.3s ease; box-shadow: 0 8px 25px rgba(82,95,225,0.3);">
                  <i class="fas fa-plus-circle me-2"></i>Soumettre un devoir
                </a>
                <a href="/gestion_users/view/frontoffice/feed.php" class="btn btn-lg" style="background: white; color: #525fe1; border: 2px solid #525fe1; padding: 0.75rem 2rem; border-radius: 50px; font-weight: 700; text-decoration: none; transition: all 0.3s ease;">
                  <i class="fas fa-stream me-2"></i>Voir le Feed
                </a>
              <?php else: ?>
                <a href="/gestion_users/view/template/sign-in.php" class="btn btn-lg" style="background: linear-gradient(135deg, #525fe1, #3b47c9); color: white; border: none; padding: 0.85rem 2rem; border-radius: 50px; font-weight: 700; text-decoration: none; transition: all 0.3s ease; box-shadow: 0 8px 25px rgba(82,95,225,0.3);">
                  <i class="fas fa-sign-in-alt me-2"></i>Se connecter pour soumettre
                </a>
              <?php endif; ?>
            </div>
          </div>

          <!-- RIGHT : Stats + recent devoirs -->
          <div class="col-lg-6">

            <!-- Stats cards (2x2 grid) -->
            <div class="row g-3 mb-4">
              <div class="col-6">
                <div class="stat-tile" style="background: white; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 4px 12px rgba(99,102,241,0.1); text-align: center; transition: all 0.3s ease;">
                  <div style="font-size: 2.5rem; color: #525fe1;"><i class="fas fa-book"></i></div>
                  <div style="font-size: 2.25rem; font-weight: 800; color: #312e81; line-height: 1; margin: 0.5rem 0;"><?= $homeDevoirsStats['total_devoirs'] ?></div>
                  <div style="font-size: 0.8rem; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Devoirs soumis</div>
                </div>
              </div>
              <div class="col-6">
                <div class="stat-tile" style="background: white; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 4px 12px rgba(139,92,246,0.1); text-align: center; transition: all 0.3s ease;">
                  <div style="font-size: 2.5rem; color: #8B5CF6;"><i class="fas fa-check-double"></i></div>
                  <div style="font-size: 2.25rem; font-weight: 800; color: #312e81; line-height: 1; margin: 0.5rem 0;"><?= $homeDevoirsStats['total_corrections'] ?></div>
                  <div style="font-size: 0.8rem; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Corrections</div>
                </div>
              </div>
              <div class="col-6">
                <div class="stat-tile" style="background: white; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 4px 12px rgba(239,68,68,0.1); text-align: center; transition: all 0.3s ease;">
                  <div style="font-size: 2.5rem; color: #ef4444;"><i class="fas fa-fire"></i></div>
                  <div style="font-size: 2.25rem; font-weight: 800; color: #312e81; line-height: 1; margin: 0.5rem 0;"><?= $homeDevoirsStats['urgents'] ?></div>
                  <div style="font-size: 0.8rem; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Urgents</div>
                </div>
              </div>
              <div class="col-6">
                <div class="stat-tile" style="background: white; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 4px 12px rgba(16,185,129,0.1); text-align: center; transition: all 0.3s ease;">
                  <div style="font-size: 2.5rem; color: #10b981;"><i class="fas fa-smile-beam"></i></div>
                  <div style="font-size: 2.25rem; font-weight: 800; color: #312e81; line-height: 1; margin: 0.5rem 0;"><?= $homeDevoirsStats['sentiment_positif'] ?></div>
                  <div style="font-size: 0.8rem; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Sentiment +</div>
                </div>
              </div>
            </div>

            <!-- Recent devoirs preview -->
            <?php if (!empty($homeRecentDevoirs)): ?>
              <div style="background: white; border-radius: 1rem; padding: 1.25rem; box-shadow: 0 4px 12px rgba(99,102,241,0.08);">
                <div class="d-flex align-items-center justify-content-between mb-3">
                  <h5 class="fw-bold mb-0" style="color: #312e81;">
                    <i class="fas fa-bolt me-2" style="color: #f59e0b;"></i>Recemment soumis
                  </h5>
                  <a href="/gestion_users/view/frontoffice/feed.php" class="small fw-semibold text-decoration-none" style="color: #525fe1;">
                    Voir tout <i class="fas fa-arrow-right ms-1"></i>
                  </a>
                </div>

                <div class="recent-devoirs-list">
                  <?php foreach ($homeRecentDevoirs as $d):
                      $niveauColors = ['facile' => '#10b981', 'moyen' => '#f59e0b', 'difficile' => '#ef4444'];
                      $niveauColor = $niveauColors[$d['niveau_difficulte']] ?? '#6b7280';
                      $sentimentEmojis = ['positif' => '😊', 'negatif' => '😟', 'stress' => '😰', 'confusion' => '😕', 'frustration' => '😤', 'neutre' => '😐'];
                      $emoji = $sentimentEmojis[$d['sentiment']] ?? '😐';
                  ?>
                    <div class="d-flex align-items-center gap-3 py-2" style="border-bottom: 1px solid #f3f4f6;">
                      <div style="flex-shrink: 0; width: 40px; height: 40px; background: linear-gradient(135deg, #eef2ff, #f5f3ff); border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                        <?= $emoji ?>
                      </div>
                      <div class="flex-grow-1" style="min-width: 0;">
                        <div class="d-flex align-items-center gap-2 mb-1">
                          <span class="fw-semibold" style="color: #312e81; font-size: 0.92rem;"><?= htmlspecialchars($d['titre']) ?></span>
                          <?php if ($d['urgence'] === 'urgente'): ?>
                            <span class="badge" style="background: #fee2e2; color: #b91c1c; font-size: 0.65rem; padding: 0.2rem 0.5rem;">Urgent</span>
                          <?php endif; ?>
                        </div>
                        <div class="d-flex align-items-center gap-2" style="font-size: 0.75rem; color: #6b7280;">
                          <span style="color: <?= $niveauColor ?>; font-weight: 600; text-transform: capitalize;"><?= htmlspecialchars($d['niveau_difficulte']) ?></span>
                          <span>•</span>
                          <span><?= (int) $d['nb_corrections'] ?> correction<?= (int) $d['nb_corrections'] > 1 ? 's' : '' ?></span>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endif; ?>

          </div>
        </div>

      </div>
    </section>
    <!-- END DEVOIRS SECTION -->

    <!-- START EVENEMENTS SECTION -->
    <section class="evenements-section py-5" style="background: linear-gradient(135deg, #f8fafc 0%, #eef2f7 100%); position: relative; overflow: hidden;">
      <!-- Background decoration -->
      <div style="position: absolute; top: -100px; right: -100px; width: 400px; height: 400px; background: radial-gradient(circle, rgba(17,153,142,0.08) 0%, transparent 70%); border-radius: 50%; pointer-events: none;"></div>
      <div style="position: absolute; bottom: -100px; left: -100px; width: 400px; height: 400px; background: radial-gradient(circle, rgba(56,239,125,0.08) 0%, transparent 70%); border-radius: 50%; pointer-events: none;"></div>

      <div class="container position-relative">

        <!-- Header creatif -->
        <div class="row mb-5 text-center">
          <div class="col-lg-8 mx-auto">
            <span class="badge mb-3" style="background: linear-gradient(135deg, #00D4FF, #525fe1); color: white; padding: 0.5rem 1.25rem; border-radius: 50px; font-size: 0.85rem; font-weight: 600; letter-spacing: 1px;">
              <i class="fas fa-calendar-star me-1"></i> EVENEMENTS A LA UNE
            </span>
            <h2 class="display-5 fw-bold mb-3" style="color: #0b104a;">Decouvrez nos Evenements</h2>
            <p class="lead text-muted mb-0">Webinaires, ateliers et conferences pour booster votre apprentissage.</p>
          </div>
        </div>

        <?php if (!empty($homeEvents)): ?>
          <!-- Liste creative des evenements (max 6) en grid 3 colonnes -->
          <div class="row g-4 mb-4">
            <?php foreach (array_slice($homeEvents, 0, 6) as $i => $ev):
                // image_evenement contient soit une URL complete (http/https), soit un nom de fichier, soit NULL
                $rawImg = trim((string)($ev['image_evenement'] ?? ''));
                if ($rawImg === '' || preg_match('#^https?://.*/index\.php#', $rawImg)) {
                    // Vide ou URL corrompue (formulaire bugué qui a sauvé l'URL de la page au lieu de l'image)
                    $imgSrc = $homePlaceholderImg;
                } elseif (preg_match('#^https?://#', $rawImg)) {
                    // URL Google Drive ou autre - utiliser le helper si dispo
                    $imgSrc = $rawImg;
                    if (strpos($rawImg, 'drive.google.com') !== false && class_exists('ImageHelper')) {
                        $imgSrc = ImageHelper::normalizeEventImageUrl($rawImg);
                    }
                } else {
                    // Nom de fichier local (upload futur)
                    $imgSrc = '/gestion_users/uploads/evenements/' . $rawImg;
                }
                $typeColors = [
                    'en ligne' => ['icon' => 'fa-video', 'bg' => '#3b82f6'],
                    'présentiel' => ['icon' => 'fa-map-marker-alt', 'bg' => '#10b981'],
                    'hybride' => ['icon' => 'fa-globe', 'bg' => '#8b5cf6'],
                ];
                $type = $typeColors[$ev['type_evenement']] ?? ['icon' => 'fa-calendar', 'bg' => '#6b7280'];
                $dateFr = date('d M Y', strtotime($ev['date_debut']));
                $jour = date('d', strtotime($ev['date_debut']));
                $mois = strtoupper(date('M', strtotime($ev['date_debut'])));
                $placesPercent = ($ev['capacite_max'] > 0) ? min(100, round((($ev['capacite_max'] - $ev['nb_places_disponibles']) / $ev['capacite_max']) * 100)) : 0;
            ?>
              <div class="col-lg-4 col-md-6">
                <div class="event-card-creative h-100" style="background: white; border-radius: 1.25rem; overflow: hidden; box-shadow: 0 4px 20px rgba(15,23,42,0.06); transition: all 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275); position: relative;">

                  <!-- Image avec overlay date -->
                  <div style="position: relative; height: 200px; overflow: hidden;">
                    <img src="<?= htmlspecialchars($imgSrc) ?>" alt="<?= htmlspecialchars($ev['titre']) ?>" referrerpolicy="no-referrer" onerror="this.onerror=null;this.src='<?= htmlspecialchars($homePlaceholderImg) ?>';" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease;">

                    <!-- Date badge top-left -->
                    <div style="position: absolute; top: 1rem; left: 1rem; background: white; border-radius: 0.75rem; padding: 0.5rem 0.75rem; text-align: center; min-width: 60px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                      <div style="font-size: 1.5rem; font-weight: 800; line-height: 1; color: <?= htmlspecialchars($ev['couleur'] ?: '#0d6efd') ?>;"><?= $jour ?></div>
                      <div style="font-size: 0.7rem; font-weight: 700; letter-spacing: 1px; color: #64748b; text-transform: uppercase;"><?= $mois ?></div>
                    </div>

                    <!-- Type badge top-right -->
                    <div style="position: absolute; top: 1rem; right: 1rem; background: <?= $type['bg'] ?>; color: white; padding: 0.4rem 0.8rem; border-radius: 50px; font-size: 0.72rem; font-weight: 700; display: flex; align-items: center; gap: 0.3rem;">
                      <i class="fas <?= $type['icon'] ?>"></i>
                      <span style="text-transform: capitalize;"><?= htmlspecialchars($ev['type_evenement']) ?></span>
                    </div>

                    <!-- Gradient overlay -->
                    <div style="position: absolute; bottom: 0; left: 0; right: 0; height: 80px; background: linear-gradient(to top, rgba(0,0,0,0.6), transparent);"></div>

                    <!-- Categorie en bas a gauche -->
                    <span class="badge" style="position: absolute; bottom: 1rem; left: 1rem; background: <?= htmlspecialchars($ev['couleur'] ?: '#0d6efd') ?>; color: white; padding: 0.4rem 0.85rem; border-radius: 50px; font-size: 0.75rem; font-weight: 600;">
                      <?= htmlspecialchars($ev['nom_categorie']) ?>
                    </span>
                  </div>

                  <!-- Body -->
                  <div style="padding: 1.5rem;">
                    <h3 class="fw-bold mb-2" style="font-size: 1.15rem; color: #0b104a; line-height: 1.4; min-height: 3.2rem;">
                      <?= htmlspecialchars($ev['titre']) ?>
                    </h3>
                    <p class="text-muted mb-3" style="font-size: 0.88rem; line-height: 1.5; min-height: 3.5rem;">
                      <?= htmlspecialchars(substr($ev['description'], 0, 90)) ?>...
                    </p>

                    <!-- Lieu si present -->
                    <?php if (!empty($ev['lieu'])): ?>
                      <div class="d-flex align-items-center text-muted small mb-3" style="gap: 0.5rem;">
                        <i class="fas fa-map-marker-alt" style="color: #525fe1;"></i>
                        <span style="font-weight: 500;"><?= htmlspecialchars(substr($ev['lieu'], 0, 35)) ?><?= strlen($ev['lieu']) > 35 ? '...' : '' ?></span>
                      </div>
                    <?php endif; ?>

                    <!-- Heure -->
                    <div class="d-flex align-items-center text-muted small mb-3" style="gap: 0.5rem;">
                      <i class="fas fa-clock" style="color: #525fe1;"></i>
                      <span style="font-weight: 500;"><?= substr($ev['heure_debut'], 0, 5) ?> - <?= substr($ev['heure_fin'], 0, 5) ?></span>
                    </div>

                    <!-- Progress bar des places -->
                    <div class="mb-3">
                      <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted fw-semibold"><?= (int) $ev['nb_participants'] ?> inscrits / <?= (int) $ev['capacite_max'] ?> places</small>
                        <?php if ((int) $ev['nb_places_disponibles'] > 0): ?>
                          <small class="fw-bold" style="color: #525fe1;"><?= (int) $ev['nb_places_disponibles'] ?> restantes</small>
                        <?php else: ?>
                          <small class="fw-bold text-danger">Complet</small>
                        <?php endif; ?>
                      </div>
                      <div style="height: 6px; background: #e2e8f0; border-radius: 50px; overflow: hidden;">
                        <div style="height: 100%; width: <?= $placesPercent ?>%; background: linear-gradient(90deg, #00D4FF, #525fe1); border-radius: 50px; transition: width 0.6s ease;"></div>
                      </div>
                    </div>

                    <!-- Bouton details -->
                    <a href="/gestion_users/public/index.php?url=Home/detail/<?= (int) $ev['id_evenement'] ?>" class="btn w-100" style="background: linear-gradient(135deg, #0b104a, #1e293b); color: white; border: none; padding: 0.7rem; border-radius: 0.75rem; font-weight: 600; transition: all 0.3s ease; text-decoration: none;">
                      Voir les details <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <!-- Bouton "Voir tous les evenements" (toujours visible) -->
          <div class="text-center mt-5">
            <a href="/gestion_users/public/index.php?url=Home/index" class="btn btn-lg" style="background: linear-gradient(135deg, #00D4FF, #525fe1); color: white; border: none; padding: 1rem 3rem; border-radius: 50px; font-weight: 700; text-decoration: none; box-shadow: 0 8px 25px rgba(82,95,225,0.3); transition: all 0.3s ease;">
              <i class="fas fa-calendar-alt me-2"></i>Voir tous les evenements <?php if (count($homeEvents) > 6): ?>(<?= count($homeEvents) ?>)<?php endif; ?>
            </a>

            <?php if (!empty($_SESSION['user_id']) && ($_SESSION['user_role'] ?? '') === 'admin'): ?>
              <a href="/gestion_users/public/index.php?url=AdminEvenement/index" class="btn btn-lg ms-2" style="background: white; color: #0b104a; border: 2px solid #0b104a; padding: 1rem 2.5rem; border-radius: 50px; font-weight: 700; text-decoration: none; transition: all 0.3s ease;">
                <i class="fas fa-cog me-2"></i>Gerer
              </a>
            <?php endif; ?>
          </div>

        <?php else: ?>
          <!-- Aucun evenement -->
          <div class="text-center py-5">
            <i class="fas fa-calendar-times" style="font-size: 4rem; color: #94a3b8; opacity: 0.5;"></i>
            <h4 class="mt-3 text-muted">Aucun evenement actif pour le moment</h4>
            <p class="text-muted">Revenez bientot pour decouvrir nos prochains evenements.</p>
            <?php if (!empty($_SESSION['user_id']) && ($_SESSION['user_role'] ?? '') === 'admin'): ?>
              <a href="/gestion_users/public/index.php?url=AdminEvenement/create" class="btn btn-lg" style="background: linear-gradient(135deg, #00D4FF, #525fe1); color: white; border: none; padding: 0.85rem 2rem; border-radius: 50px; font-weight: 600; text-decoration: none;">
                <i class="fas fa-plus me-2"></i>Creer un evenement
              </a>
            <?php endif; ?>
          </div>
        <?php endif; ?>

      </div>
    </section>
    <!-- END EVENEMENTS SECTION -->

    <!-- START OUR PARTNERS SECTION (Creative) -->
    <section class="partners-section py-5" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%); position: relative; overflow: hidden;">
      <!-- Decoration de fond -->
      <div style="position: absolute; top: -150px; right: -150px; width: 500px; height: 500px; background: radial-gradient(circle, rgba(139,92,246,0.25) 0%, transparent 60%); border-radius: 50%; pointer-events: none;"></div>
      <div style="position: absolute; bottom: -150px; left: -100px; width: 450px; height: 450px; background: radial-gradient(circle, rgba(99,102,241,0.2) 0%, transparent 60%); border-radius: 50%; pointer-events: none;"></div>

      <div class="container position-relative">

        <!-- Header -->
        <div class="row mb-5 text-center">
          <div class="col-lg-8 mx-auto">
            <span class="badge mb-3" style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); color: white; padding: 0.5rem 1.25rem; border-radius: 50px; font-size: 0.85rem; font-weight: 600; letter-spacing: 1px; border: 1px solid rgba(255,255,255,0.2);">
              <i class="fas fa-handshake me-1"></i> NOTRE RESEAU
            </span>
            <h2 class="display-5 fw-bold mb-3 text-white">Nos Partenaires</h2>
            <p class="lead mb-0" style="color: rgba(255,255,255,0.85);">
              Entreprises, universites et startups qui faconnent l'avenir de l'education avec EduMatch.
            </p>
          </div>
        </div>

        <?php if (!empty($homePartners)): ?>
          <!-- Cards des partenaires populaires -->
          <div class="row g-4 mb-5">
            <?php foreach ($homePartners as $p):
                $logoUrl = '';
                $rawLogo = trim((string)($p['logo'] ?? ''));
                if ($rawLogo === '') {
                    $logoUrl = $homePartnerPlaceholder;
                } elseif (preg_match('#^https?://#i', $rawLogo)) {
                    $logoUrl = $rawLogo;
                } elseif (strpos($rawLogo, 'assets/uploads/') === 0) {
                    $logoUrl = '/gestion_users/' . $rawLogo;
                } else {
                    $logoUrl = '/gestion_users/assets/uploads/partners/' . $rawLogo;
                }

                $typeIcons = [
                    'university' => 'fa-university',
                    'company' => 'fa-building',
                    'startup' => 'fa-rocket',
                ];
                $typeIcon = $typeIcons[$p['partner_type']] ?? 'fa-handshake';
                $shortDesc = mb_strlen($p['description']) > 80 ? mb_substr($p['description'], 0, 80) . '...' : $p['description'];
            ?>
              <div class="col-lg-4 col-md-6">
                <div class="partner-card-creative h-100" style="background: rgba(255,255,255,0.08); backdrop-filter: blur(15px); border: 1px solid rgba(255,255,255,0.15); border-radius: 1.25rem; overflow: hidden; transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
                  <!-- Image -->
                  <div style="position: relative; height: 180px; overflow: hidden;">
                    <img src="<?= htmlspecialchars($logoUrl) ?>" alt="<?= htmlspecialchars($p['organization_name']) ?>" referrerpolicy="no-referrer" onerror="this.onerror=null;this.src='<?= htmlspecialchars($homePartnerPlaceholder) ?>';" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease;">
                    <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(30,27,75,0.85), transparent 60%);"></div>
                    <!-- Type badge -->
                    <span class="badge" style="position: absolute; top: 1rem; right: 1rem; background: rgba(255,255,255,0.95); color: #1e1b4b; padding: 0.45rem 0.9rem; border-radius: 50px; font-size: 0.72rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.4rem; text-transform: uppercase; letter-spacing: 0.5px;">
                      <i class="fas <?= $typeIcon ?>"></i>
                      <?= htmlspecialchars($p['partner_type']) ?>
                    </span>
                    <!-- Views badge -->
                    <?php if ((int)$p['view_count'] > 0): ?>
                      <span class="badge" style="position: absolute; bottom: 1rem; left: 1rem; background: rgba(255,255,255,0.95); color: #1e1b4b; padding: 0.4rem 0.85rem; border-radius: 50px; font-size: 0.7rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.3rem;">
                        <i class="fas fa-eye"></i> <?= (int)$p['view_count'] ?>
                      </span>
                    <?php endif; ?>
                  </div>

                  <!-- Body -->
                  <div style="padding: 1.5rem; color: white;">
                    <h3 class="fw-bold mb-2" style="font-size: 1.15rem; line-height: 1.4;">
                      <?= htmlspecialchars($p['organization_name']) ?>
                    </h3>
                    <p class="mb-3" style="font-size: 0.88rem; line-height: 1.5; color: rgba(255,255,255,0.75); min-height: 3rem;">
                      <?= htmlspecialchars($shortDesc) ?>
                    </p>
                    <a href="/gestion_users/view/frontoffice/partner_details.php?id=<?= (int)$p['id'] ?>" class="btn w-100" style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); color: white; border: 1px solid rgba(255,255,255,0.3); padding: 0.6rem; border-radius: 0.75rem; font-weight: 600; text-decoration: none; transition: all 0.3s ease;">
                      Voir le profil <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <!-- Description courte + boutons -->
        <div class="row align-items-center mt-4">
          <div class="col-lg-7 col-md-12 mb-4 mb-lg-0">
            <p style="color: rgba(255,255,255,0.9); font-size: 1.05rem; line-height: 1.7; margin-bottom: 1.5rem;">
              Rejoignez un reseau croissant d'entreprises et institutions dedies a l'avenir de l'education.
              Beneficiez de visibilite, recommandations IA personnalisees et contrats en temps reel.
            </p>

            <div class="d-flex flex-wrap gap-3">
              <?php if ($isLoggedIn && ($userRole === 'partenariat' || $userRole === 'admin')): ?>
                <a href="/gestion_users/view/frontoffice/partenariat.php" class="btn btn-lg" style="background: white; color: #1e1b4b; border: none; padding: 0.85rem 2rem; border-radius: 50px; font-weight: 700; text-decoration: none; transition: all 0.3s ease; box-shadow: 0 8px 25px rgba(0,0,0,0.2);">
                  <i class="fas fa-handshake me-2"></i>Postuler comme partenaire
                </a>
              <?php elseif ($isLoggedIn): ?>
                <div class="d-inline-block" style="cursor: not-allowed;">
                  <button disabled class="btn btn-lg" style="background: rgba(255,255,255,0.2); color: rgba(255,255,255,0.6); border: none; padding: 0.85rem 2rem; border-radius: 50px; font-weight: 700; pointer-events: none;">
                    <i class="fas fa-lock me-2"></i>Postuler comme partenaire
                  </button>
                </div>
                <small style="color: rgba(255,255,255,0.7); align-self: center;">
                  <i class="fas fa-info-circle me-1"></i>Reserve au role <strong>partenariat</strong>
                </small>
              <?php else: ?>
                <a href="/gestion_users/view/template/sign-in.php" class="btn btn-lg" style="background: white; color: #1e1b4b; border: none; padding: 0.85rem 2rem; border-radius: 50px; font-weight: 700; text-decoration: none; transition: all 0.3s ease; box-shadow: 0 8px 25px rgba(0,0,0,0.2);">
                  <i class="fas fa-handshake me-2"></i>Postuler comme partenaire
                </a>
              <?php endif; ?>

              <a href="/gestion_users/view/frontoffice/partners_may_like.php" class="btn btn-lg" style="background: transparent; color: white; border: 2px solid rgba(255,255,255,0.4); padding: 0.75rem 2rem; border-radius: 50px; font-weight: 600; text-decoration: none; transition: all 0.3s ease;">
                <i class="fas fa-brain me-2"></i>Decouvrir tous les partenaires
              </a>
            </div>
          </div>

          <div class="col-lg-5 col-md-12">
            <!-- Stats grid -->
            <div class="row g-3 text-center">
              <div class="col-4">
                <div style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); border-radius: 1rem; padding: 1.25rem 0.5rem; border: 1px solid rgba(255,255,255,0.15);">
                  <div style="font-size: 2rem; font-weight: 800; color: white; line-height: 1;">15+</div>
                  <div style="font-size: 0.75rem; color: rgba(255,255,255,0.7); font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-top: 0.5rem;">Partenaires</div>
                </div>
              </div>
              <div class="col-4">
                <div style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); border-radius: 1rem; padding: 1.25rem 0.5rem; border: 1px solid rgba(255,255,255,0.15);">
                  <div style="font-size: 2rem; font-weight: 800; color: white; line-height: 1;">3</div>
                  <div style="font-size: 0.75rem; color: rgba(255,255,255,0.7); font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-top: 0.5rem;">Categories</div>
                </div>
              </div>
              <div class="col-4">
                <div style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); border-radius: 1rem; padding: 1.25rem 0.5rem; border: 1px solid rgba(255,255,255,0.15);">
                  <div style="font-size: 2rem; font-weight: 800; color: white; line-height: 1;">AI</div>
                  <div style="font-size: 0.75rem; color: rgba(255,255,255,0.7); font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-top: 0.5rem;">Mise en relation IA</div>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </section>
    <!-- END OUR PARTNERS SECTION -->

    <!-- START OFFRES D'EMPLOI SECTION (Creative) -->
    <section class="job-offers-section py-5" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%); position: relative; overflow: hidden;">
      <!-- Decorations de fond -->
      <div style="position: absolute; top: -100px; left: -100px; width: 460px; height: 460px; background: radial-gradient(circle, rgba(0, 212, 255, 0.20) 0%, transparent 60%); border-radius: 50%; pointer-events: none;"></div>
      <div style="position: absolute; bottom: -120px; right: -120px; width: 520px; height: 520px; background: radial-gradient(circle, rgba(82, 95, 225, 0.25) 0%, transparent 60%); border-radius: 50%; pointer-events: none;"></div>
      <!-- Pattern grille subtle -->
      <div style="position: absolute; inset: 0; background-image: linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px); background-size: 50px 50px; pointer-events: none;"></div>

      <div class="container position-relative">

        <!-- Header -->
        <div class="row mb-5 text-center">
          <div class="col-lg-8 mx-auto">
            <span class="badge mb-3" style="background: linear-gradient(135deg, #00D4FF, #525fe1); color: white; padding: 0.5rem 1.25rem; border-radius: 50px; font-size: 0.85rem; font-weight: 600; letter-spacing: 1px; box-shadow: 0 8px 20px rgba(82, 95, 225, 0.4);">
              <i class="fas fa-briefcase me-1"></i> CARRIERES
            </span>
            <h2 class="display-5 fw-bold mb-3 text-white">Offres d'Emploi</h2>
            <p class="lead mb-0" style="color: rgba(255,255,255,0.85);">
              Decouvrez les opportunites de carriere chez nos partenaires et propulsez votre avenir professionnel.
            </p>
          </div>
        </div>

        <?php if (!empty($homeOffres)): ?>
          <!-- Cards offres recentes -->
          <div class="row g-4 mb-5">
            <?php foreach ($homeOffres as $idx => $offre):
                $contractColors = [
                    'CDI'      => ['#00D4FF', '#525fe1'],
                    'CDD'      => ['#525fe1', '#3b47c9'],
                    'Stage'    => ['#a855f7', '#8b5cf6'],
                    'Freelance'=> ['#7c8aff', '#525fe1'],
                ];
                $contractKey = $offre['type_contrat'] ?? 'CDI';
                $colors = $contractColors[$contractKey] ?? ['#525fe1', '#3b47c9'];

                $daysLeft = '';
                $isUrgent = false;
                if (!empty($offre['date_limite'])) {
                    $ts = strtotime($offre['date_limite']);
                    if ($ts !== false) {
                        $diff = ceil(($ts - time()) / 86400);
                        if ($diff <= 0) { $daysLeft = 'Cloture'; }
                        elseif ($diff <= 3) { $daysLeft = $diff . 'j restants'; $isUrgent = true; }
                        else { $daysLeft = $diff . 'j restants'; }
                    }
                }
            ?>
              <div class="col-lg-4 col-md-6">
                <div class="job-card-creative h-100" style="background: rgba(255,255,255,0.06); backdrop-filter: blur(15px); border: 1px solid rgba(255,255,255,0.12); border-radius: 1.25rem; overflow: hidden; transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); position: relative;">
                  <!-- Accent bar haut -->
                  <div style="height: 5px; background: linear-gradient(90deg, <?= $colors[0] ?>, <?= $colors[1] ?>);"></div>

                  <div style="padding: 1.75rem; color: white;">
                    <!-- Header card : icone + badge contrat -->
                    <div class="d-flex justify-content-between align-items-start mb-3">
                      <div style="width: 56px; height: 56px; background: linear-gradient(135deg, <?= $colors[0] ?>, <?= $colors[1] ?>); border-radius: 14px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 20px <?= $colors[0] ?>40;">
                        <i class="fas fa-briefcase text-white" style="font-size: 1.4rem;"></i>
                      </div>
                      <span class="badge" style="background: rgba(255,255,255,0.12); color: white; padding: 0.4rem 0.85rem; border-radius: 50px; font-size: 0.72rem; font-weight: 700; letter-spacing: 0.5px; border: 1px solid rgba(255,255,255,0.18);">
                        <?= htmlspecialchars($offre['type_contrat']) ?>
                      </span>
                    </div>

                    <!-- Titre offre -->
                    <h3 class="fw-bold mb-2" style="font-size: 1.2rem; line-height: 1.35; color: white;">
                      <?= htmlspecialchars($offre['titre']) ?>
                    </h3>

                    <!-- Meta info -->
                    <div class="mb-3" style="color: rgba(255,255,255,0.7); font-size: 0.9rem;">
                      <div class="mb-1"><i class="fas fa-map-marker-alt me-2" style="color: <?= $colors[0] ?>;"></i><?= htmlspecialchars($offre['lieu']) ?></div>
                      <?php if ($daysLeft !== ''): ?>
                        <div><i class="fas fa-clock me-2" style="color: <?= $isUrgent ? '#fb7185' : $colors[0] ?>;"></i>
                          <span style="<?= $isUrgent ? 'color:#fb7185; font-weight:600;' : '' ?>"><?= htmlspecialchars($daysLeft) ?></span>
                        </div>
                      <?php endif; ?>
                    </div>

                    <!-- Stats candidatures -->
                    <div class="d-flex align-items-center mb-3 pb-3" style="border-bottom: 1px solid rgba(255,255,255,0.1); gap: 0.5rem;">
                      <div style="width: 32px; height: 32px; background: rgba(0, 212, 255, 0.18); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-users" style="color: #00D4FF; font-size: 0.8rem;"></i>
                      </div>
                      <div style="font-size: 0.82rem; color: rgba(255,255,255,0.85);">
                        <strong style="color: white;"><?= (int)$offre['nb_candidatures'] ?></strong> candidature<?= ((int)$offre['nb_candidatures'] > 1) ? 's' : '' ?>
                      </div>
                    </div>

                    <!-- CTA -->
                    <a href="/gestion_users/controller/OffreEmploiController.php?espace=front&action=details&id=<?= (int)$offre['id'] ?>" class="btn w-100" style="background: linear-gradient(135deg, <?= $colors[0] ?>, <?= $colors[1] ?>); color: white; border: none; padding: 0.7rem; border-radius: 0.75rem; font-weight: 600; text-decoration: none; transition: all 0.3s ease; box-shadow: 0 6px 18px <?= $colors[0] ?>40;">
                      Voir l'offre <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <!-- Empty state -->
          <div class="row mb-5">
            <div class="col-lg-8 mx-auto text-center">
              <div style="background: rgba(255,255,255,0.06); backdrop-filter: blur(15px); border: 1px solid rgba(255,255,255,0.12); border-radius: 1.25rem; padding: 3rem 2rem; color: white;">
                <i class="fas fa-briefcase mb-3" style="font-size: 3rem; color: rgba(0, 212, 255, 0.6);"></i>
                <h3 class="fw-bold mb-2" style="color: white;">Aucune offre ouverte pour le moment</h3>
                <p style="color: rgba(255,255,255,0.7);">Revenez bientot pour decouvrir de nouvelles opportunites.</p>
              </div>
            </div>
          </div>
        <?php endif; ?>

        <!-- Description + CTA + Stats -->
        <div class="row align-items-center mt-4">
          <div class="col-lg-7 col-md-12 mb-4 mb-lg-0">
            <p style="color: rgba(255,255,255,0.9); font-size: 1.05rem; line-height: 1.7; margin-bottom: 1.5rem;">
              Notre plateforme connecte les meilleurs talents avec les opportunites les plus inspirantes.
              Soumettez votre candidature en quelques clics et beneficiez d'une analyse IA personnalisee
              pour maximiser vos chances de reussite.
            </p>

            <div class="d-flex flex-wrap gap-3">
              <a href="/gestion_users/controller/OffreEmploiController.php?espace=front&action=liste" class="btn btn-lg" style="background: white; color: #525fe1; border: none; padding: 0.85rem 2rem; border-radius: 50px; font-weight: 700; text-decoration: none; transition: all 0.3s ease; box-shadow: 0 8px 25px rgba(0,0,0,0.2);">
                <i class="fas fa-briefcase me-2"></i>Voir toutes les offres
              </a>

              <?php if ($isLoggedIn && ($userRole === 'encadrant' || $userRole === 'admin')): ?>
                <a href="/gestion_users/controller/OffreEmploiController.php?espace=front&action=liste" class="btn btn-lg" style="background: transparent; color: white; border: 2px solid rgba(255,255,255,0.4); padding: 0.75rem 2rem; border-radius: 50px; font-weight: 600; text-decoration: none; transition: all 0.3s ease;">
                  <i class="fas fa-paper-plane me-2"></i>Candidater maintenant
                </a>
              <?php elseif ($isLoggedIn): ?>
                <div class="d-inline-block" style="cursor: not-allowed;">
                  <button disabled class="btn btn-lg" style="background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.5); border: 2px solid rgba(255,255,255,0.15); padding: 0.75rem 2rem; border-radius: 50px; font-weight: 600; pointer-events: none;">
                    <i class="fas fa-lock me-2"></i>Candidater
                  </button>
                </div>
                <small style="color: rgba(255,255,255,0.7); align-self: center;">
                  <i class="fas fa-info-circle me-1"></i>Reserve aux <strong>encadrants</strong>
                </small>
              <?php else: ?>
                <a href="/gestion_users/view/template/sign-in.php" class="btn btn-lg" style="background: transparent; color: white; border: 2px solid rgba(255,255,255,0.4); padding: 0.75rem 2rem; border-radius: 50px; font-weight: 600; text-decoration: none; transition: all 0.3s ease;">
                  <i class="fas fa-sign-in-alt me-2"></i>Se connecter pour postuler
                </a>
              <?php endif; ?>
            </div>
          </div>

          <div class="col-lg-5 col-md-12">
            <!-- Stats grid -->
            <div class="row g-3 text-center">
              <div class="col-4">
                <div style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); border-radius: 1rem; padding: 1.25rem 0.5rem; border: 1px solid rgba(255,255,255,0.15); transition: transform 0.3s ease;">
                  <div style="font-size: 2rem; font-weight: 800; color: white; line-height: 1;"><?= (int)$homeOffresStats['total'] ?></div>
                  <div style="font-size: 0.72rem; color: rgba(255,255,255,0.7); font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-top: 0.5rem;">Offres totales</div>
                </div>
              </div>
              <div class="col-4">
                <div style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); border-radius: 1rem; padding: 1.25rem 0.5rem; border: 1px solid rgba(255,255,255,0.15);">
                  <div style="font-size: 2rem; font-weight: 800; color: white; line-height: 1;"><?= (int)$homeOffresStats['ouvertes'] ?></div>
                  <div style="font-size: 0.72rem; color: rgba(255,255,255,0.7); font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-top: 0.5rem;">Ouvertes</div>
                </div>
              </div>
              <div class="col-4">
                <div style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); border-radius: 1rem; padding: 1.25rem 0.5rem; border: 1px solid rgba(255,255,255,0.15);">
                  <div style="font-size: 2rem; font-weight: 800; color: white; line-height: 1;"><?= (int)$homeOffresStats['candidatures'] ?></div>
                  <div style="font-size: 0.72rem; color: rgba(255,255,255,0.7); font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-top: 0.5rem;">Candidatures</div>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </section>
    <!-- END OFFRES D'EMPLOI SECTION -->

    <!-- START MODERN FOOTER -->
    <footer class="modern-footer bg-dark text-white py-5">
      <div class="container">
        <div class="row">
          <div class="col-lg-4 col-md-6 mb-4">
            <a href="index.php" class="text-decoration-none">
              <img src="../../assets/img/logo.png" alt="EduMatch Logo" class="mb-3" style="height: 50px;">
            </a>
            <p class="mt-3 text-light opacity-75">Plateforme intelligente de mise en relation des etudiants avec des professeurs experts dans toutes les matieres academiques pour des experiences d'apprentissage personnalisees.</p>
          </div>
          <div class="col-lg-2 col-md-6 mb-4">
            <h5 class="fw-bold mb-3">Plateforme</h5>
            <ul class="list-unstyled">
              <li class="mb-2"><a href="index.php" class="text-light text-decoration-none">Accueil</a></li>
              <?php if (empty($_SESSION['user_id'])): ?>
              <li class="mb-2"><a href="sign-in.php" class="text-light text-decoration-none">Connexion</a></li>
              <li class="mb-2"><a href="sign-up.php" class="text-light text-decoration-none">Inscription</a></li>
              <?php else: ?>
              <li class="mb-2"><a href="profil.php" class="text-light text-decoration-none">Mon Profil</a></li>
              <li class="mb-2"><a href="/gestion_users/auth/logout" class="text-light text-decoration-none">Deconnexion</a></li>
              <?php endif; ?>
            </ul>
          </div>
          <div class="col-lg-2 col-md-6 mb-4">
            <h5 class="fw-bold mb-3">Matieres academiques</h5>
            <ul class="list-unstyled">
              <li class="mb-2"><a href="#" class="text-light text-decoration-none">Mathematiques</a></li>
              <li class="mb-2"><a href="#" class="text-light text-decoration-none">Sciences</a></li>
              <li class="mb-2"><a href="#" class="text-light text-decoration-none">Programmation</a></li>
              <li class="mb-2"><a href="#" class="text-light text-decoration-none">Algorithmique</a></li>
              <li class="mb-2"><a href="#" class="text-light text-decoration-none">Langues</a></li>
              <li class="mb-2"><a href="#" class="text-light text-decoration-none">Sciences humaines</a></li>
            </ul>
          </div>
          <div class="col-lg-4 col-md-6 mb-4">
            <h5 class="fw-bold mb-3">Coordonnees</h5>
            <p class="mb-2"><i class="fas fa-map-marker-alt me-2"></i>Tunis, Tunisie</p>
            <p class="mb-2"><i class="fas fa-phone me-2"></i>+216 90 549 254</p>
            <p class="mb-2"><i class="fas fa-envelope me-2"></i>edumatch@gmail.com</p>
          </div>
        </div>
        <hr class="my-4 opacity-25">
        <div class="row align-items-center">
          <div class="col-md-6">
            <p class="mb-0 text-light opacity-75">&copy; 2026 EduMatch. Tous droits reserves.</p>
          </div>
          <div class="col-md-6 text-md-end">
            <a href="#" class="text-light text-decoration-none me-3">Politique de confidentialite</a>
            <a href="#" class="text-light text-decoration-none me-3">Conditions d'utilisation</a>
            <a href="#" class="text-light text-decoration-none">Assistance</a>
          </div>
        </div>
      </div>
    </footer>
    <!-- END MODERN FOOTER -->

    <!-- Latest jQuery -->
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
    <!-- User dropdown JS is in _navbar.php -->
  </body>
</html>
