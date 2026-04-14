<?php ob_start(); ?>
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <img src="<?php echo htmlspecialchars($data['evenement']['image_evenement'] ?: 'https://via.placeholder.com/800x400?text=Evenement'); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($data['evenement']['titre']); ?>">
            <div class="card-body p-4">
                <h1 class="display-5 fw-bold mb-3"><?php echo htmlspecialchars($data['evenement']['titre']); ?></h1>
                <div class="d-flex flex-wrap gap-3 mb-4">
                    <span class="badge bg-primary fs-6"><?php echo htmlspecialchars($data['evenement']['type_evenement']); ?></span>
                    <span class="text-muted"><?php echo htmlspecialchars($data['evenement']['date_debut']); ?> - <?php echo htmlspecialchars($data['evenement']['date_fin']); ?></span>
                    <span class="text-muted"><?php echo htmlspecialchars($data['evenement']['heure_debut']); ?> - <?php echo htmlspecialchars($data['evenement']['heure_fin']); ?></span>
                </div>
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
                <button class="btn btn-secondary btn-lg w-100" disabled>Complet</button>
                <?php endif; ?>
                <p class="text-center text-muted mt-3 small">Inscription gratuite et rapide</p>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/front_layout.php'; ?>
