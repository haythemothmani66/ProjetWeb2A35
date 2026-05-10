<?php ob_start(); ?>
<h2>Modifier un Evenement</h2>

<?php if (!empty($data['errors']['general'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($data['errors']['general']); ?></div>
<?php endif; ?>

<form action="<?php echo BASE_URL; ?>/AdminEvenement/edit/<?php echo (int) $data['evenement']['id_evenement']; ?>" method="POST" id="evenementForm" class="needs-validation" novalidate>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="titre" class="form-label">Titre</label>
            <input type="text" class="form-control <?php echo isset($data['errors']['titre']) ? 'is-invalid' : ''; ?>" id="titre" name="titre" value="<?php echo htmlspecialchars($data['evenement']['titre']); ?>">
            <div class="invalid-feedback d-block"><?php echo htmlspecialchars($data['errors']['titre'] ?? ''); ?></div>
        </div>
        <div class="col-md-6 mb-3">
            <label for="id_categorie" class="form-label">Categorie</label>
            <select class="form-select" id="id_categorie" name="id_categorie">
                <?php foreach ($data['categories'] as $cat): ?>
                <option value="<?php echo (int) $cat['id_categorie']; ?>" <?php echo ((int) $data['evenement']['id_categorie'] === (int) $cat['id_categorie']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($cat['nom_categorie']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control <?php echo isset($data['errors']['description']) ? 'is-invalid' : ''; ?>" id="description" name="description" rows="3"><?php echo htmlspecialchars($data['evenement']['description']); ?></textarea>
    </div>

    <div class="row">
        <div class="col-md-3 mb-3">
            <label for="type_evenement" class="form-label">Type</label>
            <select class="form-select" id="type_evenement" name="type_evenement">
                <option value="en ligne" <?php echo ($data['evenement']['type_evenement'] === 'en ligne') ? 'selected' : ''; ?>>En ligne</option>
                <option value="présentiel" <?php echo ($data['evenement']['type_evenement'] === 'présentiel') ? 'selected' : ''; ?>>Presentiel</option>
                <option value="hybride" <?php echo ($data['evenement']['type_evenement'] === 'hybride') ? 'selected' : ''; ?>>Hybride</option>
            </select>
        </div>
        <div class="col-md-3 mb-3">
            <label for="date_debut" class="form-label">Date debut</label>
            <input type="date" class="form-control <?php echo isset($data['errors']['date_debut']) ? 'is-invalid' : ''; ?>" id="date_debut" name="date_debut" min="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($data['evenement']['date_debut']); ?>">
            <div class="invalid-feedback d-block" id="date_debut_error"><?php echo htmlspecialchars($data['errors']['date_debut'] ?? ''); ?></div>
        </div>
        <div class="col-md-3 mb-3">
            <label for="date_fin" class="form-label">Date fin</label>
            <input type="date" class="form-control <?php echo isset($data['errors']['date_fin']) ? 'is-invalid' : ''; ?>" id="date_fin" name="date_fin" value="<?php echo htmlspecialchars($data['evenement']['date_fin']); ?>">
            <div class="invalid-feedback d-block"><?php echo htmlspecialchars($data['errors']['date_fin'] ?? ''); ?></div>
        </div>
        <div class="col-md-3 mb-3">
            <label for="capacite_max" class="form-label">Capacite max</label>
            <input type="number" class="form-control" id="capacite_max" name="capacite_max" value="<?php echo (int) $data['evenement']['capacite_max']; ?>">
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 mb-3">
            <label for="nb_places_disponibles" class="form-label">Places dispo</label>
            <input type="number" class="form-control <?php echo isset($data['errors']['nb_places_disponibles']) ? 'is-invalid' : ''; ?>" id="nb_places_disponibles" name="nb_places_disponibles" value="<?php echo (int) $data['evenement']['nb_places_disponibles']; ?>">
            <div class="invalid-feedback d-block"><?php echo htmlspecialchars($data['errors']['nb_places_disponibles'] ?? ''); ?></div>
        </div>
        <div class="col-md-3 mb-3">
            <label for="heure_debut" class="form-label">Heure debut</label>
            <input type="time" class="form-control <?php echo isset($data['errors']['heure_debut']) ? 'is-invalid' : ''; ?>" id="heure_debut" name="heure_debut" value="<?php echo htmlspecialchars($data['evenement']['heure_debut']); ?>">
            <div class="invalid-feedback d-block"><?php echo htmlspecialchars($data['errors']['heure_debut'] ?? ''); ?></div>
        </div>
        <div class="col-md-3 mb-3">
            <label for="heure_fin" class="form-label">Heure fin</label>
            <input type="time" class="form-control <?php echo isset($data['errors']['heure_fin']) ? 'is-invalid' : ''; ?>" id="heure_fin" name="heure_fin" value="<?php echo htmlspecialchars($data['evenement']['heure_fin']); ?>">
            <div class="invalid-feedback d-block" id="heure_fin_error"><?php echo htmlspecialchars($data['errors']['heure_fin'] ?? ''); ?></div>
        </div>
        <div class="col-md-3 mb-3">
            <label for="partenariat" class="form-label">Partenariat</label>
            <select class="form-select" id="partenariat" name="partenariat">
                <option value="non" <?php echo ($data['evenement']['partenariat'] === 'non') ? 'selected' : ''; ?>>Non</option>
                <option value="oui" <?php echo ($data['evenement']['partenariat'] === 'oui') ? 'selected' : ''; ?>>Oui</option>
            </select>
        </div>
    </div>

    <div class="mb-3">
        <label for="lieu" class="form-label">Lieu</label>
        <input type="text" class="form-control <?php echo isset($data['errors']['lieu']) ? 'is-invalid' : ''; ?>" id="lieu" name="lieu" value="<?php echo htmlspecialchars($data['evenement']['lieu']); ?>">
    </div>
    <div class="mb-3">
        <label for="lien_acces" class="form-label">Lien d'acces</label>
        <input type="text" class="form-control <?php echo isset($data['errors']['lien_acces']) ? 'is-invalid' : ''; ?>" id="lien_acces" name="lien_acces" value="<?php echo htmlspecialchars($data['evenement']['lien_acces']); ?>">
    </div>
    <div class="mb-3">
        <label for="organisateur" class="form-label">Organisateur</label>
        <input type="text" class="form-control <?php echo isset($data['errors']['organisateur']) ? 'is-invalid' : ''; ?>" id="organisateur" name="organisateur" value="<?php echo htmlspecialchars($data['evenement']['organisateur']); ?>">
    </div>
    <div class="mb-3">
        <label for="statut" class="form-label">Statut</label>
        <select class="form-select" id="statut" name="statut">
            <option value="planifié" <?php echo ($data['evenement']['statut'] === 'planifié') ? 'selected' : ''; ?>>Planifie</option>
            <option value="en cours" <?php echo ($data['evenement']['statut'] === 'en cours') ? 'selected' : ''; ?>>En cours</option>
            <option value="terminé" <?php echo ($data['evenement']['statut'] === 'terminé') ? 'selected' : ''; ?>>Termine</option>
            <option value="annulé" <?php echo ($data['evenement']['statut'] === 'annulé') ? 'selected' : ''; ?>>Annule</option>
        </select>
    </div>
    <div class="mb-3">
        <label for="image_evenement" class="form-label">Image URL</label>
        <input type="text" class="form-control" id="image_evenement" name="image_evenement" value="<?php echo htmlspecialchars($data['evenement']['image_evenement']); ?>">
        <div class="form-text">Google&nbsp;Drive&nbsp;: partage «&nbsp;Toute personne disposant du lien&nbsp;» obligatoire pour que l’image s’affiche sur le site.</div>
    </div>

    <button type="submit" class="btn btn-success">Mettre a jour</button>
    <a href="<?php echo BASE_URL; ?>/AdminEvenement/index" class="btn btn-secondary">Annuler</a>
</form>

<?php $content = ob_get_clean(); include __DIR__ . '/../../layouts/back_layout.php'; ?>
