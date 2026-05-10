<?php ob_start(); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gestion des Événements</h2>
    <div class="d-flex gap-2">
        <a href="<?php echo BASE_URL; ?>/AdminEvenement/stats" class="btn btn-outline-dark">Statistiques</a>
        <button type="button" class="btn btn-outline-secondary js-export-pdf" data-table-id="table-evenements" data-title="Liste des evenements">Exporter PDF</button>
        <a href="<?php echo BASE_URL; ?>/AdminEvenement/create" class="btn btn-primary">Ajouter un Événement</a>
    </div>
</div>

<?php if (!empty($data['flash'])): ?>
<div class="alert alert-<?php echo htmlspecialchars($data['flash']['type']); ?>">
    <?php echo htmlspecialchars($data['flash']['message']); ?>
</div>
<?php endif; ?>

<div class="row g-2 mb-3">
    <div class="col-md-2"><a class="text-decoration-none text-dark" href="<?php echo BASE_URL; ?>/AdminEvenement/index?status_filter"><div class="card border-0 bg-light"><div class="card-body py-2"><strong>Total:</strong> <?php echo (int) ($data['stats']['total'] ?? 0); ?></div></div></a></div>
    <div class="col-md-2"><a class="text-decoration-none text-dark" href="<?php echo BASE_URL; ?>/AdminEvenement/index?status_filter=planifi%C3%A9"><div class="card border-0 bg-light"><div class="card-body py-2"><strong>Planifies:</strong> <?php echo (int) ($data['stats']['planifie'] ?? 0); ?></div></div></a></div>
    <div class="col-md-2"><a class="text-decoration-none text-dark" href="<?php echo BASE_URL; ?>/AdminEvenement/index?status_filter=en%20cours"><div class="card border-0 bg-light"><div class="card-body py-2"><strong>En cours:</strong> <?php echo (int) ($data['stats']['en_cours'] ?? 0); ?></div></div></a></div>
    <div class="col-md-2"><a class="text-decoration-none text-dark" href="<?php echo BASE_URL; ?>/AdminEvenement/index?status_filter=termin%C3%A9"><div class="card border-0 bg-light"><div class="card-body py-2"><strong>Termines:</strong> <?php echo (int) ($data['stats']['termine'] ?? 0); ?></div></div></a></div>
    <div class="col-md-2"><a class="text-decoration-none text-dark" href="<?php echo BASE_URL; ?>/AdminEvenement/index?status_filter=annul%C3%A9"><div class="card border-0 bg-light"><div class="card-body py-2"><strong>Annules:</strong> <?php echo (int) ($data['stats']['annule'] ?? 0); ?></div></div></a></div>
</div>

<form method="GET" action="<?php echo BASE_URL; ?>/AdminEvenement/index" class="row g-2 mb-3">
    <div class="col-md-6">
        <input type="text" class="form-control" name="search" placeholder="Rechercher par titre, categorie, type ou statut" value="<?php echo htmlspecialchars($data['filters']['search'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    </div>
    <div class="col-md-4">
        <select class="form-select" name="sort">
            <option value="date_asc" <?php echo (($data['filters']['sort'] ?? '') === 'date_asc') ? 'selected' : ''; ?>>Date debut (plus proche)</option>
            <option value="date_desc" <?php echo (($data['filters']['sort'] ?? '') === 'date_desc') ? 'selected' : ''; ?>>Date debut (plus lointaine)</option>
            <option value="titre_asc" <?php echo (($data['filters']['sort'] ?? '') === 'titre_asc') ? 'selected' : ''; ?>>Titre A-Z</option>
            <option value="titre_desc" <?php echo (($data['filters']['sort'] ?? '') === 'titre_desc') ? 'selected' : ''; ?>>Titre Z-A</option>
            <option value="categorie_asc" <?php echo (($data['filters']['sort'] ?? '') === 'categorie_asc') ? 'selected' : ''; ?>>Categorie A-Z</option>
            <option value="categorie_desc" <?php echo (($data['filters']['sort'] ?? '') === 'categorie_desc') ? 'selected' : ''; ?>>Categorie Z-A</option>
            <option value="statut_asc" <?php echo (($data['filters']['sort'] ?? '') === 'statut_asc') ? 'selected' : ''; ?>>Statut A-Z</option>
            <option value="statut_desc" <?php echo (($data['filters']['sort'] ?? '') === 'statut_desc') ? 'selected' : ''; ?>>Statut Z-A</option>
        </select>
    </div>
    <input type="hidden" name="status_filter" value="<?php echo htmlspecialchars($data['filters']['status_filter'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    <div class="col-md-2 d-grid">
        <button type="submit" class="btn btn-outline-primary">Filtrer</button>
    </div>
</form>

<table class="table table-striped" id="table-evenements">
    <thead>
        <tr>
            <th>Titre</th>
            <th>Catégorie</th>
            <th>Date</th>
            <th>Participants</th>
            <th>Statut</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($data['evenements'] as $ev): ?>
        <?php $eventParticipants = $data['participationsParEvenement'][(int) $ev['id_evenement']] ?? []; ?>
        <?php $participantCount = count($eventParticipants); ?>
        <tr class="event-row" data-target="participants-<?php echo (int) $ev['id_evenement']; ?>" style="cursor: pointer;">
            <td><?php echo htmlspecialchars($ev['titre']); ?></td>
            <td><?php echo htmlspecialchars($ev['nom_categorie']); ?></td>
            <td><?php echo htmlspecialchars($ev['date_debut']); ?></td>
            <td><?php echo $participantCount; ?></td>
            <td><?php echo htmlspecialchars($ev['statut']); ?></td>
            <td class="event-actions">
                <a href="<?php echo BASE_URL; ?>/AdminEvenement/edit/<?php echo $ev['id_evenement']; ?>" class="btn btn-sm btn-warning">Modifier</a>
                <a href="<?php echo BASE_URL; ?>/AdminEvenement/delete/<?php echo $ev['id_evenement']; ?>" class="btn btn-sm btn-danger js-confirm-delete" data-confirm-message="Est-ce que vous voulez supprimer cet evenement ?">Supprimer</a>
            </td>
        </tr>
        <tr class="table-light participants-row d-none" id="participants-<?php echo (int) $ev['id_evenement']; ?>">
            <td colspan="6">
                <strong>Participants :</strong>
                <?php if (empty($eventParticipants)): ?>
                    <span class="text-muted">Aucun participant pour cet evenement.</span>
                <?php else: ?>
                    <ul class="mb-0 mt-2">
                        <?php foreach ($eventParticipants as $part): ?>
                        <li>
                            <?php echo htmlspecialchars($part['nom_participant']); ?>
                            (<?php echo htmlspecialchars($part['email']); ?>)
                            - Statut: <?php echo htmlspecialchars($part['statut_participation']); ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var rows = document.querySelectorAll('.event-row');
    rows.forEach(function (row) {
        row.addEventListener('click', function (event) {
            if (event.target.closest('.event-actions')) {
                return;
            }
            var targetId = row.getAttribute('data-target');
            var participantsRow = document.getElementById(targetId);
            if (participantsRow) {
                participantsRow.classList.toggle('d-none');
            }
        });
    });
});
</script>

<?php $content = ob_get_clean(); include __DIR__ . '/../../layouts/back_layout.php'; ?>
