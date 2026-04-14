<?php ob_start(); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gestion des Catégories</h2>
    <a href="<?php echo BASE_URL; ?>/AdminCategorie/create" class="btn btn-primary">Ajouter une Catégorie</a>
</div>

<?php if (!empty($data['flash'])): ?>
<div class="alert alert-<?php echo htmlspecialchars($data['flash']['type']); ?>">
    <?php echo htmlspecialchars($data['flash']['message']); ?>
</div>
<?php endif; ?>

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Couleur</th>
            <th>Statut</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($data['categories'] as $cat): ?>
        <tr>
            <td><?php echo $cat['id_categorie']; ?></td>
            <td><?php echo $cat['nom_categorie']; ?></td>
            <td><span class="badge" style="background-color: <?php echo $cat['couleur']; ?>;"><?php echo $cat['couleur']; ?></span></td>
            <td><?php echo $cat['statut']; ?></td>
            <td>
                <a href="<?php echo BASE_URL; ?>/AdminCategorie/edit/<?php echo $cat['id_categorie']; ?>" class="btn btn-sm btn-warning">Modifier</a>
                <a href="<?php echo BASE_URL; ?>/AdminCategorie/delete/<?php echo $cat['id_categorie']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cette catégorie ?')">Supprimer</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php $content = ob_get_clean(); include __DIR__ . '/../../layouts/back_layout.php'; ?>
