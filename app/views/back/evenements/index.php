<?php ob_start(); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gestion des Événements</h2>
    <a href="<?php echo BASE_URL; ?>/AdminEvenement/create" class="btn btn-primary">Ajouter un Événement</a>
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
            <th>Titre</th>
            <th>Catégorie</th>
            <th>Date</th>
            <th>Statut</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($data['evenements'] as $ev): ?>
        <tr>
            <td><?php echo $ev['id_evenement']; ?></td>
            <td><?php echo $ev['titre']; ?></td>
            <td><?php echo $ev['nom_categorie']; ?></td>
            <td><?php echo $ev['date_debut']; ?></td>
            <td><?php echo $ev['statut']; ?></td>
            <td>
                <a href="<?php echo BASE_URL; ?>/AdminEvenement/edit/<?php echo $ev['id_evenement']; ?>" class="btn btn-sm btn-warning">Modifier</a>
                <a href="<?php echo BASE_URL; ?>/AdminEvenement/delete/<?php echo $ev['id_evenement']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cet événement ?')">Supprimer</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php $content = ob_get_clean(); include __DIR__ . '/../../layouts/back_layout.php'; ?>
