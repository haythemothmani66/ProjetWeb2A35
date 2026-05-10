<?php
$placeholderImg = 'https://via.placeholder.com/800x400?text=Evenement';
$imgSrc = !empty($data['evenement']['image_display']) ? $data['evenement']['image_display'] : $placeholderImg;
ob_start();
?>

<div class="row">
    <div class="col-lg-8">
        <?php if (!empty($data['flash'])): ?>
        <div class="alert alert-<?php echo htmlspecialchars($data['flash']['type']); ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($data['flash']['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <?php if ((int) $data['evenement']['nb_places_disponibles'] <= 0 && !empty($data['evenement']['is_metiers_avances'])): ?>
        <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-4" role="alert">
            <span class="fs-4 me-3" aria-hidden="true">&#9888;</span>
            <div>
                <strong>Événement complet</strong> — Cette activité « Métiers avancés » n’accepte plus d’inscriptions. Merci de consulter nos autres événements.
            </div>
        </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0 mb-4">
            <img src="<?php echo htmlspecialchars($imgSrc); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($data['evenement']['titre']); ?>" referrerpolicy="no-referrer" onerror="this.onerror=null;this.src='<?php echo htmlspecialchars($placeholderImg, ENT_QUOTES, 'UTF-8'); ?>';">
            <div class="card-body p-4">
                <h1 class="display-5 fw-bold mb-3"><?php echo htmlspecialchars($data['evenement']['titre']); ?></h1>
                <div class="d-flex flex-wrap gap-3 mb-4">
                    <span class="badge bg-primary fs-6"><?php echo htmlspecialchars($data['evenement']['type_evenement']); ?></span>
                    <span class="text-muted"><?php echo htmlspecialchars($data['evenement']['date_debut']); ?> - <?php echo htmlspecialchars($data['evenement']['date_fin']); ?></span>
                    <span class="text-muted"><?php echo htmlspecialchars($data['evenement']['heure_debut']); ?> - <?php echo htmlspecialchars($data['evenement']['heure_fin']); ?></span>
                </div>
                <p class="text-muted mb-3"><strong><?php echo (int) $data['evenement']['nb_participants']; ?></strong> participant<?php echo ((int) $data['evenement']['nb_participants'] > 1) ? 's' : ''; ?> inscrit<?php echo ((int) $data['evenement']['nb_participants'] > 1) ? 's' : ''; ?></p>
                <h4 class="fw-bold mb-3">Description</h4>
                <p class="lead text-muted mb-4"><?php echo nl2br(htmlspecialchars($data['evenement']['description'])); ?></p>
                <div class="row g-4">
                    <div class="col-md-6">
                        <h5 class="fw-bold">Lieu / Accès</h5>
                        <p class="text-muted"><?php echo htmlspecialchars($data['evenement']['lieu'] ?: 'Non specifie'); ?></p>
                    </div>
                    <div class="col-md-6">
                        <h5 class="fw-bold">Organisateur</h5>
                        <p class="text-muted"><?php echo htmlspecialchars($data['evenement']['organisateur'] ?: 'Non specifie'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 sticky-top" style="top: 2rem;">
            <div class="card-body p-4">
                <h4 class="fw-bold mb-3">Inscription</h4>
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Places disponibles</span>
                        <span class="fw-bold"><?php echo (int) $data['evenement']['nb_places_disponibles']; ?> / <?php echo (int) $data['evenement']['capacite_max']; ?></span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <?php $percent = ((int) $data['evenement']['capacite_max'] > 0) ? (((int) $data['evenement']['nb_places_disponibles'] / (int) $data['evenement']['capacite_max']) * 100) : 0; ?>
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $percent; ?>%"></div>
                    </div>
                </div>
                <?php if ((int) $data['evenement']['nb_places_disponibles'] > 0): ?>
                <a href="<?php echo BASE_URL; ?>/Home/register/<?php echo (int) $data['evenement']['id_evenement']; ?>" class="btn btn-primary btn-lg w-100">S'inscrire maintenant</a>
                <?php else: ?>
                <button type="button" class="btn btn-secondary btn-lg w-100" disabled title="Événement complet">Complet</button>
                <?php if (!empty($data['evenement']['is_metiers_avances'])): ?>
                <div class="alert alert-warning small mt-3 mb-0">Les événements « Métiers avancés » complets ne permettent pas de nouvelle inscription.</div>
                <?php endif; ?>
                <?php endif; ?>
                <p class="text-center text-muted mt-3 small">Inscription gratuite et rapide</p>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/front_layout.php'; ?>
