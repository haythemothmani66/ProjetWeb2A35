<?php ob_start(); ?>
<h2>Ajouter un Événement</h2>

<form action="<?php echo BASE_URL; ?>/AdminEvenement/create" method="POST" id="evenementForm" class="needs-validation" novalidate>
    <?php if (!empty($data['errors']['general'])): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($data['errors']['general']); ?></div>
    <?php endif; ?>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="titre" class="form-label">Titre de l'événement</label>
            <input type="text" class="form-control <?php echo isset($data['errors']['titre']) ? 'is-invalid' : ''; ?>" id="titre" name="titre" value="<?php echo htmlspecialchars($data['old']['titre'] ?? ''); ?>">
            <div class="invalid-feedback d-block" id="titre_error"><?php echo htmlspecialchars($data['errors']['titre'] ?? ''); ?></div>
        </div>
        <div class="col-md-6 mb-3">
            <label for="id_categorie" class="form-label">Catégorie</label>
            <select class="form-select" id="id_categorie" name="id_categorie">
                <option value="">Choisir une catégorie...</option>
                <?php foreach ($data['categories'] as $cat): ?>
                <option value="<?php echo $cat['id_categorie']; ?>" <?php echo (($data['old']['id_categorie'] ?? '') == $cat['id_categorie']) ? 'selected' : ''; ?>><?php echo $cat['nom_categorie']; ?></option>
                <?php endforeach; ?>
            </select>
            <div class="invalid-feedback d-block" id="id_categorie_error"><?php echo htmlspecialchars($data['errors']['id_categorie'] ?? ''); ?></div>
        </div>
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control <?php echo isset($data['errors']['description']) ? 'is-invalid' : ''; ?>" id="description" name="description" rows="3"><?php echo htmlspecialchars($data['old']['description'] ?? ''); ?></textarea>
        <div class="invalid-feedback d-block"><?php echo htmlspecialchars($data['errors']['description'] ?? ''); ?></div>
    </div>

    <div class="row">
        <div class="col-md-3 mb-3">
            <label for="type_evenement" class="form-label">Type</label>
            <select class="form-select <?php echo isset($data['errors']['type_evenement']) ? 'is-invalid' : ''; ?>" id="type_evenement" name="type_evenement">
                <option value="en ligne" <?php echo (($data['old']['type_evenement'] ?? '') === 'en ligne') ? 'selected' : ''; ?>>En ligne</option>
                <option value="présentiel" <?php echo (($data['old']['type_evenement'] ?? '') === 'présentiel') ? 'selected' : ''; ?>>Présentiel</option>
                <option value="hybride" <?php echo (($data['old']['type_evenement'] ?? '') === 'hybride') ? 'selected' : ''; ?>>Hybride</option>
            </select>
            <div class="invalid-feedback d-block"><?php echo htmlspecialchars($data['errors']['type_evenement'] ?? ''); ?></div>
        </div>
        <div class="col-md-3 mb-3">
            <label for="date_debut" class="form-label">Date Début</label>
            <input type="date" class="form-control <?php echo isset($data['errors']['date_debut']) ? 'is-invalid' : ''; ?>" id="date_debut" name="date_debut" min="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($data['old']['date_debut'] ?? ''); ?>">
            <div class="invalid-feedback d-block" id="date_debut_error"><?php echo htmlspecialchars($data['errors']['date_debut'] ?? ''); ?></div>
        </div>
        <div class="col-md-3 mb-3">
            <label for="date_fin" class="form-label">Date Fin</label>
            <input type="date" class="form-control <?php echo isset($data['errors']['date_fin']) ? 'is-invalid' : ''; ?>" id="date_fin" name="date_fin" value="<?php echo htmlspecialchars($data['old']['date_fin'] ?? ''); ?>">
            <div class="invalid-feedback d-block" id="date_fin_error"><?php echo htmlspecialchars($data['errors']['date_fin'] ?? ''); ?></div>
        </div>
        <div class="col-md-3 mb-3">
            <label for="capacite_max" class="form-label">Capacité Max</label>
            <input type="number" class="form-control <?php echo isset($data['errors']['capacite_max']) ? 'is-invalid' : ''; ?>" id="capacite_max" name="capacite_max" min="1" value="<?php echo htmlspecialchars($data['old']['capacite_max'] ?? ''); ?>">
            <div class="invalid-feedback d-block" id="capacite_max_error"><?php echo htmlspecialchars($data['errors']['capacite_max'] ?? ''); ?></div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 mb-3">
            <label for="heure_debut" class="form-label">Heure Début</label>
            <input type="time" class="form-control <?php echo isset($data['errors']['heure_debut']) ? 'is-invalid' : ''; ?>" id="heure_debut" name="heure_debut" value="<?php echo htmlspecialchars($data['old']['heure_debut'] ?? ''); ?>">
            <div class="invalid-feedback d-block"><?php echo htmlspecialchars($data['errors']['heure_debut'] ?? ''); ?></div>
        </div>
        <div class="col-md-3 mb-3">
            <label for="heure_fin" class="form-label">Heure Fin</label>
            <input type="time" class="form-control <?php echo isset($data['errors']['heure_fin']) ? 'is-invalid' : ''; ?>" id="heure_fin" name="heure_fin" value="<?php echo htmlspecialchars($data['old']['heure_fin'] ?? ''); ?>">
            <div class="invalid-feedback d-block" id="heure_fin_error"><?php echo htmlspecialchars($data['errors']['heure_fin'] ?? ''); ?></div>
        </div>
        <div class="col-md-3 mb-3">
            <label for="partenariat" class="form-label">Partenariat</label>
            <select class="form-select <?php echo isset($data['errors']['partenariat']) ? 'is-invalid' : ''; ?>" id="partenariat" name="partenariat">
                <option value="non" <?php echo (($data['old']['partenariat'] ?? 'non') === 'non') ? 'selected' : ''; ?>>Non</option>
                <option value="oui" <?php echo (($data['old']['partenariat'] ?? '') === 'oui') ? 'selected' : ''; ?>>Oui</option>
            </select>
            <div class="invalid-feedback d-block"><?php echo htmlspecialchars($data['errors']['partenariat'] ?? ''); ?></div>
        </div>
        <div class="col-md-3 mb-3">
            <label for="statut" class="form-label">Statut</label>
            <select class="form-select <?php echo isset($data['errors']['statut']) ? 'is-invalid' : ''; ?>" id="statut" name="statut">
                <option value="planifié" <?php echo (($data['old']['statut'] ?? 'planifié') === 'planifié') ? 'selected' : ''; ?>>Planifié</option>
                <option value="en cours" <?php echo (($data['old']['statut'] ?? '') === 'en cours') ? 'selected' : ''; ?>>En cours</option>
                <option value="terminé" <?php echo (($data['old']['statut'] ?? '') === 'terminé') ? 'selected' : ''; ?>>Terminé</option>
                <option value="annulé" <?php echo (($data['old']['statut'] ?? '') === 'annulé') ? 'selected' : ''; ?>>Annulé</option>
            </select>
            <div class="invalid-feedback d-block"><?php echo htmlspecialchars($data['errors']['statut'] ?? ''); ?></div>
        </div>
    </div>

    <div class="mb-3">
        <label for="lieu" class="form-label">Lieu</label>
        <input type="text" class="form-control <?php echo isset($data['errors']['lieu']) ? 'is-invalid' : ''; ?>" id="lieu" name="lieu" value="<?php echo htmlspecialchars($data['old']['lieu'] ?? ''); ?>">
        <div class="invalid-feedback d-block"><?php echo htmlspecialchars($data['errors']['lieu'] ?? ''); ?></div>
    </div>

    <div class="mb-3">
        <label for="lien_acces" class="form-label">Lien d'acces</label>
        <input type="text" class="form-control <?php echo isset($data['errors']['lien_acces']) ? 'is-invalid' : ''; ?>" id="lien_acces" name="lien_acces" value="<?php echo htmlspecialchars($data['old']['lien_acces'] ?? ''); ?>">
        <div class="invalid-feedback d-block"><?php echo htmlspecialchars($data['errors']['lien_acces'] ?? ''); ?></div>
    </div>

    <div class="mb-3">
        <label for="organisateur" class="form-label">Organisateur</label>
        <input type="text" class="form-control <?php echo isset($data['errors']['organisateur']) ? 'is-invalid' : ''; ?>" id="organisateur" name="organisateur" value="<?php echo htmlspecialchars($data['old']['organisateur'] ?? ''); ?>">
        <div class="invalid-feedback d-block"><?php echo htmlspecialchars($data['errors']['organisateur'] ?? ''); ?></div>
    </div>

    <div class="mb-3">
        <label for="image_evenement" class="form-label">URL Image</label>
        <input type="text" class="form-control" id="image_evenement" name="image_evenement" value="<?php echo htmlspecialchars($data['old']['image_evenement'] ?? ''); ?>">
    </div>

    <button type="submit" class="btn btn-success">Enregistrer</button>
    <a href="<?php echo BASE_URL; ?>/AdminEvenement/index" class="btn btn-secondary">Annuler</a>
</form>

<?php $content = ob_get_clean(); include __DIR__ . '/../../layouts/back_layout.php'; ?>
