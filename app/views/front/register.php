<?php ob_start(); ?>
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 p-4">
            <h2 class="fw-bold mb-4">Inscription à l'événement</h2>
            <h4 class="text-primary mb-4"><?php echo htmlspecialchars($data['evenement']['titre']); ?></h4>
            <?php if (!empty($data['errors']['general'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($data['errors']['general']); ?></div>
            <?php endif; ?>
            <form action="<?php echo BASE_URL; ?>/Home/register/<?php echo (int) $data['evenement']['id_evenement']; ?>" method="POST" id="registerForm" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="nom_participant" class="form-label">Nom complet</label>
                    <input type="text" class="form-control <?php echo isset($data['errors']['nom_participant']) ? 'is-invalid' : ''; ?>" id="nom_participant" name="nom_participant" value="<?php echo htmlspecialchars($data['old']['nom_participant'] ?? ''); ?>">
                    <div class="invalid-feedback d-block" id="nom_participant_error"><?php echo htmlspecialchars($data['errors']['nom_participant'] ?? ''); ?></div>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Adresse Email</label>
                    <input type="text" class="form-control <?php echo isset($data['errors']['email']) ? 'is-invalid' : ''; ?>" id="email" name="email" value="<?php echo htmlspecialchars($data['old']['email'] ?? ''); ?>">
                    <div class="invalid-feedback d-block" id="email_error"><?php echo htmlspecialchars($data['errors']['email'] ?? ''); ?></div>
                </div>
                <div class="mb-3">
                    <label for="telephone" class="form-label">Téléphone</label>
                    <input type="text" class="form-control <?php echo isset($data['errors']['telephone']) ? 'is-invalid' : ''; ?>" id="telephone" name="telephone" value="<?php echo htmlspecialchars($data['old']['telephone'] ?? ''); ?>">
                    <div class="invalid-feedback d-block" id="telephone_error"><?php echo htmlspecialchars($data['errors']['telephone'] ?? ''); ?></div>
                </div>
                <div class="mb-4">
                    <label for="mode_participation" class="form-label">Mode de participation</label>
                    <select class="form-select <?php echo isset($data['errors']['mode_participation']) ? 'is-invalid' : ''; ?>" id="mode_participation" name="mode_participation">
                        <option value="">Choisir un mode...</option>
                        <option value="en ligne" <?php echo (($data['old']['mode_participation'] ?? '') === 'en ligne') ? 'selected' : ''; ?>>En ligne</option>
                        <option value="présentiel" <?php echo (($data['old']['mode_participation'] ?? '') === 'présentiel') ? 'selected' : ''; ?>>Présentiel</option>
                    </select>
                    <div class="invalid-feedback d-block" id="mode_participation_error"><?php echo htmlspecialchars($data['errors']['mode_participation'] ?? ''); ?></div>
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100">Confirmer l'inscription</button>
                <a href="<?php echo BASE_URL; ?>/Home/detail/<?php echo (int) $data['evenement']['id_evenement']; ?>" class="btn btn-link w-100 mt-2">Retour aux détails</a>
            </form>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/front_layout.php'; ?>
