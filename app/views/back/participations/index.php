<?php ob_start(); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gestion des Participations</h2>
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
            <th>Participant</th>
            <th>Événement</th>
            <th>Date Inscription</th>
            <th>Statut</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($data['participations'] as $part): ?>
        <tr>
            <td><?php echo $part['id_participation']; ?></td>
            <td><?php echo $part['nom_participant']; ?> (<?php echo $part['email']; ?>)</td>
            <td><?php echo $part['titre_evenement']; ?></td>
            <td><?php echo $part['date_inscription']; ?></td>
            <td><?php echo $part['statut_participation']; ?></td>
            <td>
                <a href="<?php echo BASE_URL; ?>/AdminParticipation/edit/<?php echo $part['id_participation']; ?>" class="btn btn-sm btn-warning">Modifier</a>
                <a href="<?php echo BASE_URL; ?>/AdminParticipation/delete/<?php echo $part['id_participation']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cette participation ?')">Supprimer</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php $content = ob_get_clean(); include __DIR__ . '/../../layouts/back_layout.php'; ?>
