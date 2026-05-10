<?php ob_start(); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gestion des Catégories</h2>
    <div class="d-flex gap-2">
        <a href="<?php echo BASE_URL; ?>/AdminCategorie/stats" class="btn btn-outline-dark">Statistiques</a>
        <button type="button" class="btn btn-outline-secondary js-export-pdf" data-table-id="table-categories" data-title="Liste des categories">Exporter PDF</button>
        <a href="<?php echo BASE_URL; ?>/AdminCategorie/create" class="btn btn-primary">Ajouter une Catégorie</a>
    </div>
</div>

<?php if (!empty($data['flash'])): ?>
<div class="alert alert-<?php echo htmlspecialchars($data['flash']['type']); ?>">
    <?php echo htmlspecialchars($data['flash']['message']); ?>
</div>
<?php endif; ?>

<div class="row g-2 mb-3">
    <div class="col-md-3">
        <a class="text-decoration-none text-dark" href="<?php echo BASE_URL; ?>/AdminCategorie/index?status_filter">
            <div class="card border-0 bg-light"><div class="card-body py-2"><strong>Total:</strong> <?php echo (int) ($data['stats']['total'] ?? 0); ?></div></div>
        </a>
    </div>
    <div class="col-md-3">
        <a class="text-decoration-none text-dark" href="<?php echo BASE_URL; ?>/AdminCategorie/index?status_filter=actif">
            <div class="card border-0 bg-light"><div class="card-body py-2"><strong>Actives:</strong> <?php echo (int) ($data['stats']['actif'] ?? 0); ?></div></div>
        </a>
    </div>
    <div class="col-md-3">
        <a class="text-decoration-none text-dark" href="<?php echo BASE_URL; ?>/AdminCategorie/index?status_filter=inactif">
            <div class="card border-0 bg-light"><div class="card-body py-2"><strong>Inactives:</strong> <?php echo (int) ($data['stats']['inactif'] ?? 0); ?></div></div>
        </a>
    </div>
</div>

<form method="GET" action="<?php echo BASE_URL; ?>/AdminCategorie/index" class="row g-2 mb-3">
    <div class="col-md-6">
        <input type="text" class="form-control" name="search" placeholder="Rechercher par nom, description ou statut" value="<?php echo htmlspecialchars($data['filters']['search'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    </div>
    <div class="col-md-4">
        <select class="form-select" name="sort">
            <option value="date_desc" <?php echo (($data['filters']['sort'] ?? '') === 'date_desc') ? 'selected' : ''; ?>>Date creation (plus recente)</option>
            <option value="date_asc" <?php echo (($data['filters']['sort'] ?? '') === 'date_asc') ? 'selected' : ''; ?>>Date creation (plus ancienne)</option>
            <option value="nom_asc" <?php echo (($data['filters']['sort'] ?? '') === 'nom_asc') ? 'selected' : ''; ?>>Nom A-Z</option>
            <option value="nom_desc" <?php echo (($data['filters']['sort'] ?? '') === 'nom_desc') ? 'selected' : ''; ?>>Nom Z-A</option>
            <option value="statut_asc" <?php echo (($data['filters']['sort'] ?? '') === 'statut_asc') ? 'selected' : ''; ?>>Statut A-Z</option>
            <option value="statut_desc" <?php echo (($data['filters']['sort'] ?? '') === 'statut_desc') ? 'selected' : ''; ?>>Statut Z-A</option>
        </select>
    </div>
    <input type="hidden" name="status_filter" value="<?php echo htmlspecialchars($data['filters']['status_filter'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    <div class="col-md-2 d-grid">
        <button type="submit" class="btn btn-outline-primary">Filtrer</button>
    </div>
</form>

<table class="table table-striped" id="table-categories">
    <thead>
        <tr>
            <th>Nom</th>
            <th>Couleur</th>
            <th>Statut</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($data['categories'] as $cat): ?>
        <tr>
            <td><?php echo $cat['nom_categorie']; ?></td>
            <td><span class="badge" style="background-color: <?php echo $cat['couleur']; ?>;"><?php echo $cat['couleur']; ?></span></td>
            <td><?php echo $cat['statut']; ?></td>
            <td>
                <a href="<?php echo BASE_URL; ?>/AdminCategorie/edit/<?php echo $cat['id_categorie']; ?>" class="btn btn-sm btn-warning">Modifier</a>
                <a href="<?php echo BASE_URL; ?>/AdminCategorie/delete/<?php echo $cat['id_categorie']; ?>" class="btn btn-sm btn-danger js-confirm-delete" data-confirm-message="Est-ce que vous voulez supprimer cette categorie ?">Supprimer</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php $content = ob_get_clean(); include __DIR__ . '/../../layouts/back_layout.php'; ?>
