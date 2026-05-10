<?php ob_start(); ?>
<h2>Modifier une Categorie</h2>

<?php if (!empty($data['errors']['general'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($data['errors']['general']); ?></div>
<?php endif; ?>

<form action="<?php echo BASE_URL; ?>/AdminCategorie/edit/<?php echo (int) $data['categorie']['id_categorie']; ?>" method="POST" id="categorieForm" class="needs-validation" novalidate>
    <div class="mb-3">
        <label for="nom_categorie" class="form-label">Nom de la categorie</label>
        <input type="text" class="form-control <?php echo isset($data['errors']['nom_categorie']) ? 'is-invalid' : ''; ?>" id="nom_categorie" name="nom_categorie" value="<?php echo htmlspecialchars($data['categorie']['nom_categorie']); ?>">
        <div class="invalid-feedback d-block" id="nom_categorie_error"><?php echo htmlspecialchars($data['errors']['nom_categorie'] ?? ''); ?></div>
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($data['categorie']['description']); ?></textarea>
    </div>

    <div class="mb-3">
        <label class="form-label d-block">Couleur</label>
        <?php
        $selectedColor = $data['categorie']['couleur'] ?? '#1F5DB8';
        $presetColors = ['#1F5DB8', '#2D79DF', '#32B7C5', '#F2B705', '#F26A4B', '#6A5ACD'];
        foreach ($presetColors as $color):
        ?>
        <label class="color-choice-wrap">
            <input type="radio" class="d-none color-choice" name="couleur" value="<?php echo $color; ?>" <?php echo ($selectedColor === $color) ? 'checked' : ''; ?>>
            <span class="preset-color <?php echo isset($data['errors']['couleur']) ? 'is-invalid' : ''; ?>" style="background-color: <?php echo $color; ?>;"></span>
            <small><?php echo $color; ?></small>
        </label>
        <?php endforeach; ?>
        <div class="invalid-feedback d-block"><?php echo htmlspecialchars($data['errors']['couleur'] ?? ''); ?></div>
    </div>

    <div class="mb-3">
        <label for="statut" class="form-label">Statut</label>
        <select class="form-select <?php echo isset($data['errors']['statut']) ? 'is-invalid' : ''; ?>" id="statut" name="statut">
            <option value="actif" <?php echo ($data['categorie']['statut'] === 'actif') ? 'selected' : ''; ?>>Actif</option>
            <option value="inactif" <?php echo ($data['categorie']['statut'] === 'inactif') ? 'selected' : ''; ?>>Inactif</option>
        </select>
        <div class="invalid-feedback d-block"><?php echo htmlspecialchars($data['errors']['statut'] ?? ''); ?></div>
    </div>

    <button type="submit" class="btn btn-success">Enregistrer les modifications</button>
    <a href="<?php echo BASE_URL; ?>/AdminCategorie/index" class="btn btn-secondary">Annuler</a>
</form>

<?php $content = ob_get_clean(); include __DIR__ . '/../../layouts/back_layout.php'; ?>
