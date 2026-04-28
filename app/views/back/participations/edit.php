<?php ob_start(); ?>
<h2>Modifier une Participation</h2>

<?php if (!empty($data['errors']['general'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($data['errors']['general']); ?></div>
<?php endif; ?>

<form action="<?php echo BASE_URL; ?>/AdminParticipation/edit/<?php echo (int) $data['participation']['id_participation']; ?>" method="POST" id="participationForm" class="needs-validation" novalidate>
    <div class="mb-3">
        <label for="nom_participant" class="form-label">Nom complet</label>
        <input type="text" class="form-control <?php echo isset($data['errors']['nom_participant']) ? 'is-invalid' : ''; ?>" id="nom_participant" name="nom_participant" value="<?php echo htmlspecialchars($data['participation']['nom_participant']); ?>">
        <div class="invalid-feedback d-block"><?php echo htmlspecialchars($data['errors']['nom_participant'] ?? ''); ?></div>
    </div>
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="text" class="form-control <?php echo isset($data['errors']['email']) ? 'is-invalid' : ''; ?>" id="email" name="email" value="<?php echo htmlspecialchars($data['participation']['email']); ?>">
        <div class="invalid-feedback d-block"><?php echo htmlspecialchars($data['errors']['email'] ?? ''); ?></div>
    </div>
    <div class="mb-3">
        <label for="telephone" class="form-label">Telephone</label>
        <input type="text" class="form-control <?php echo isset($data['errors']['telephone']) ? 'is-invalid' : ''; ?>" id="telephone" name="telephone" value="<?php echo htmlspecialchars($data['participation']['telephone']); ?>">
        <div class="invalid-feedback d-block"><?php echo htmlspecialchars($data['errors']['telephone'] ?? ''); ?></div>
    </div>
    <div class="mb-3">
        <label for="mode_participation" class="form-label">Mode de participation</label>
        <select class="form-select <?php echo isset($data['errors']['mode_participation']) ? 'is-invalid' : ''; ?>" id="mode_participation" name="mode_participation">
            <?php
            $modes = ['en ligne', 'présentiel', 'hybride'];
            foreach ($modes as $mode):
            ?>
            <option value="<?php echo $mode; ?>" <?php echo ($data['participation']['mode_participation'] === $mode) ? 'selected' : ''; ?>><?php echo $mode; ?></option>
            <?php endforeach; ?>
        </select>
        <div class="invalid-feedback d-block"><?php echo htmlspecialchars($data['errors']['mode_participation'] ?? ''); ?></div>
    </div>
    <div class="mb-3">
        <label for="statut_participation" class="form-label">Statut</label>
        <select class="form-select <?php echo isset($data['errors']['statut_participation']) ? 'is-invalid' : ''; ?>" id="statut_participation" name="statut_participation">
            <?php
            $statuts = ['inscrit', 'confirmé', 'présent', 'absent', 'annulé'];
            foreach ($statuts as $statut):
            ?>
            <option value="<?php echo $statut; ?>" <?php echo ($data['participation']['statut_participation'] === $statut) ? 'selected' : ''; ?>><?php echo $statut; ?></option>
            <?php endforeach; ?>
        </select>
        <div class="invalid-feedback d-block"><?php echo htmlspecialchars($data['errors']['statut_participation'] ?? ''); ?></div>
    </div>
    <div class="mb-3">
        <label for="feedback" class="form-label">Feedback</label>
        <textarea class="form-control" id="feedback" name="feedback" rows="3"><?php echo htmlspecialchars($data['participation']['feedback'] ?? ''); ?></textarea>
    </div>
    <div class="mb-3">
        <label for="note" class="form-label">Note (0-5)</label>
        <input type="number" class="form-control <?php echo isset($data['errors']['note']) ? 'is-invalid' : ''; ?>" id="note" name="note" min="0" max="5" value="<?php echo htmlspecialchars((string) ($data['participation']['note'] ?? '')); ?>">
        <div class="invalid-feedback d-block"><?php echo htmlspecialchars($data['errors']['note'] ?? ''); ?></div>
    </div>

    <button type="submit" class="btn btn-success">Mettre a jour</button>
    <a href="<?php echo BASE_URL; ?>/AdminParticipation/index" class="btn btn-secondary">Annuler</a>
</form>

<?php $content = ob_get_clean(); include __DIR__ . '/../../layouts/back_layout.php'; ?>
