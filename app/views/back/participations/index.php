<?php ob_start(); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gestion des Participations</h2>
    <div class="d-flex gap-2">
        <a href="<?php echo BASE_URL; ?>/AdminParticipation/stats" class="btn btn-outline-dark">Statistiques</a>
        <button type="button" class="btn btn-outline-secondary js-export-pdf" data-table-id="table-participations" data-title="Liste des participations">Exporter PDF</button>
    </div>
</div>

<?php if (!empty($data['flash'])): ?>
<div class="alert alert-<?php echo htmlspecialchars($data['flash']['type']); ?>">
    <?php echo htmlspecialchars($data['flash']['message']); ?>
</div>
<?php endif; ?>

<div class="row g-2 mb-3">
    <div class="col-md-2"><a class="text-decoration-none text-dark" href="<?php echo BASE_URL; ?>AdminParticipation/index"><div class="card border-0 bg-light"><div class="card-body py-2"><strong>Total:</strong> <?php echo (int) ($data['stats']['total'] ?? 0); ?></div></div></a></div>
    <div class="col-md-2"><a class="text-decoration-none text-dark" href="<?php echo BASE_URL; ?>AdminParticipation/index&status_filter=inscrit"><div class="card border-0 bg-light"><div class="card-body py-2"><strong>Inscrits:</strong> <?php echo (int) ($data['stats']['inscrit'] ?? 0); ?></div></div></a></div>
    <div class="col-md-2"><a class="text-decoration-none text-dark" href="<?php echo BASE_URL; ?>AdminParticipation/index&status_filter=confirm%C3%A9"><div class="card border-0 bg-light"><div class="card-body py-2"><strong>Confirmes:</strong> <?php echo (int) ($data['stats']['confirme'] ?? 0); ?></div></div></a></div>
    <div class="col-md-2"><a class="text-decoration-none text-dark" href="<?php echo BASE_URL; ?>AdminParticipation/index&status_filter=pr%C3%A9sent"><div class="card border-0 bg-light"><div class="card-body py-2"><strong>Presents:</strong> <?php echo (int) ($data['stats']['present'] ?? 0); ?></div></div></a></div>
    <div class="col-md-2"><a class="text-decoration-none text-dark" href="<?php echo BASE_URL; ?>AdminParticipation/index&status_filter=absent"><div class="card border-0 bg-light"><div class="card-body py-2"><strong>Absents:</strong> <?php echo (int) ($data['stats']['absent'] ?? 0); ?></div></div></a></div>
    <div class="col-md-2"><a class="text-decoration-none text-dark" href="<?php echo BASE_URL; ?>AdminParticipation/index&status_filter=annul%C3%A9"><div class="card border-0 bg-light"><div class="card-body py-2"><strong>Annules:</strong> <?php echo (int) ($data['stats']['annule'] ?? 0); ?></div></div></a></div>
</div>

<form method="GET" action="<?php echo PROJECT_URL; ?>/public/index.php" class="row g-2 mb-3">
    <input type="hidden" name="url" value="AdminParticipation/index">
    <div class="col-md-6">
        <input type="text" class="form-control" name="search" placeholder="Rechercher par participant, email, evenement ou statut" value="<?php echo htmlspecialchars($data['filters']['search'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    </div>
    <div class="col-md-4">
        <select class="form-select" name="sort">
            <option value="nom_asc" <?php echo (($data['filters']['sort'] ?? '') === 'nom_asc') ? 'selected' : ''; ?>>Nom A-Z</option>
            <option value="nom_desc" <?php echo (($data['filters']['sort'] ?? '') === 'nom_desc') ? 'selected' : ''; ?>>Nom Z-A</option>
            <option value="date_desc" <?php echo (($data['filters']['sort'] ?? '') === 'date_desc') ? 'selected' : ''; ?>>Date inscription (plus recente)</option>
            <option value="date_asc" <?php echo (($data['filters']['sort'] ?? '') === 'date_asc') ? 'selected' : ''; ?>>Date inscription (plus ancienne)</option>
            <option value="event_asc" <?php echo (($data['filters']['sort'] ?? '') === 'event_asc') ? 'selected' : ''; ?>>Evenement A-Z</option>
            <option value="event_desc" <?php echo (($data['filters']['sort'] ?? '') === 'event_desc') ? 'selected' : ''; ?>>Evenement Z-A</option>
            <option value="statut_asc" <?php echo (($data['filters']['sort'] ?? '') === 'statut_asc') ? 'selected' : ''; ?>>Statut A-Z</option>
            <option value="statut_desc" <?php echo (($data['filters']['sort'] ?? '') === 'statut_desc') ? 'selected' : ''; ?>>Statut Z-A</option>
        </select>
    </div>
    <input type="hidden" name="status_filter" value="<?php echo htmlspecialchars($data['filters']['status_filter'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    <div class="col-md-2 d-grid">
        <button type="submit" class="btn btn-outline-primary">Filtrer</button>
    </div>
</form>

<table class="table table-striped" id="table-participations">
    <thead>
        <tr>
            <th>Nom complet</th>
            <th>Email</th>
            <th>Telephone</th>
            <th>Mode</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($data['participations'] as $part): ?>
        <tr>
            <td><?php echo htmlspecialchars($part['nom_participant']); ?></td>
            <td><?php echo htmlspecialchars($part['email']); ?></td>
            <td><?php echo htmlspecialchars($part['telephone']); ?></td>
            <td><?php echo htmlspecialchars($part['mode_participation']); ?></td>
            <td>
                <a href="<?php echo BASE_URL; ?>/AdminParticipation/edit/<?php echo $part['id_participation']; ?>" class="btn btn-sm btn-warning">Modifier</a>
                <a href="<?php echo BASE_URL; ?>/AdminParticipation/delete/<?php echo $part['id_participation']; ?>" class="btn btn-sm btn-danger js-confirm-delete" data-confirm-message="Est-ce que vous voulez supprimer ce participant ?">Supprimer</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php $content = ob_get_clean(); include __DIR__ . '/../../layouts/back_layout.php'; ?>
