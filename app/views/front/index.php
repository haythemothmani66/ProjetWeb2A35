<?php ob_start(); ?>
<div class="row mb-5 text-center">
    <div class="col-lg-8 mx-auto">
        <h1 class="display-4 fw-bold">Découvrez nos Événements</h1>
        <p class="lead text-muted">Webinaires, ateliers et conférences pour booster votre apprentissage.</p>
    </div>
</div>

<?php if (!empty($data['flash'])): ?>
<div class="alert alert-<?php echo htmlspecialchars($data['flash']['type']); ?> alert-dismissible fade show" role="alert">
    <?php echo htmlspecialchars($data['flash']['message']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="row row-cols-1 row-cols-md-3 g-4">
    <?php foreach ($data['evenements'] as $ev): ?>
    <div class="col">
        <div class="card h-100 shadow-sm border-0">
            <img src="<?php echo htmlspecialchars($ev['image_evenement'] ?: 'https://via.placeholder.com/400x200?text=Evenement'); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($ev['titre']); ?>">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge" style="background-color: <?php echo htmlspecialchars($ev['couleur'] ?: '#0d6efd'); ?>;"><?php echo htmlspecialchars($ev['nom_categorie']); ?></span>
                    <small class="text-muted"><?php echo htmlspecialchars($ev['date_debut']); ?></small>
                </div>
                <h5 class="card-title fw-bold"><?php echo htmlspecialchars($ev['titre']); ?></h5>
                <p class="card-text text-muted"><?php echo htmlspecialchars(substr($ev['description'], 0, 100)) . '...'; ?></p>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-primary fw-bold"><?php echo (int) $ev['nb_places_disponibles']; ?> places restantes</span>
                    <a href="<?php echo BASE_URL; ?>/Home/detail/<?php echo $ev['id_evenement']; ?>" class="btn btn-outline-primary btn-sm">Détails</a>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/front_layout.php'; ?>
