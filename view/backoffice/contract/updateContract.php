<?php
declare(strict_types=1);
require dirname(__DIR__) . '/layout/header.php';
$contract = $contract ?? [];
$partners = $partners ?? [];
?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h2 class="h5 mb-0">Update Contract</h2>
        <a class="btn btn-outline-secondary btn-sm" href="<?= h(appUrl(['controller' => 'contract', 'action' => 'list'])); ?>">Back to list</a>
    </div>
    <div class="card-body">
        <form action="<?= h(appUrl(['controller' => 'contract', 'action' => 'update', 'id' => (int)($contract['id'] ?? 0)])); ?>" method="post" enctype="multipart/form-data" class="row g-3" novalidate>
            <div class="col-md-6">
                <label class="form-label" for="contract_ref">Contract Reference</label>
                <input class="form-control" type="text" id="contract_ref" name="contract_ref" value="<?= h((string)($contract['contract_ref'] ?? '')); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="company_name">Company Name *</label>
                <input class="form-control" type="text" id="company_name" name="company_name" list="partner_organizations" required value="<?= h((string)($contract['company_name'] ?? '')); ?>">
                <datalist id="partner_organizations">
                    <?php foreach ($partners as $partner): ?>
                        <option value="<?= h((string)($partner['organization_name'] ?? '')); ?>"></option>
                    <?php endforeach; ?>
                </datalist>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="type_contrat">Contract Type *</label>
                <?php $selectedType = (string)($contract['type_contrat'] ?? ''); ?>
                <select class="form-select" id="type_contrat" name="type_contrat" required>
                    <option value="">Choose...</option>
                    <option value="CDD"<?= $selectedType === 'CDD' ? ' selected' : ''; ?>>CDD</option>
                    <option value="CDI"<?= $selectedType === 'CDI' ? ' selected' : ''; ?>>CDI</option>
                    <option value="Partenariat"<?= $selectedType === 'Partenariat' ? ' selected' : ''; ?>>Partenariat</option>
                    <option value="Stage"<?= $selectedType === 'Stage' ? ' selected' : ''; ?>>Stage</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="statut">Status *</label>
                <?php $selectedStatus = (string)($contract['statut'] ?? 'Actif'); ?>
                <select class="form-select" id="statut" name="statut" required>
                    <option value="Actif"<?= $selectedStatus === 'Actif' ? ' selected' : ''; ?>>Actif</option>
                    <option value="Expire"<?= $selectedStatus === 'Expire' || $selectedStatus === 'Expiré' ? ' selected' : ''; ?>>Expire</option>
                    <option value="Suspendu"<?= $selectedStatus === 'Suspendu' ? ' selected' : ''; ?>>Suspendu</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="date_debut">Start Date *</label>
                <input class="form-control" type="date" id="date_debut" name="date_debut" required value="<?= h((string)($contract['date_debut'] ?? '')); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="date_fin">End Date *</label>
                <input class="form-control" type="date" id="date_fin" name="date_fin" required value="<?= h((string)($contract['date_fin'] ?? '')); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="date_signature">Signature Date *</label>
                <input class="form-control" type="date" id="date_signature" name="date_signature" required value="<?= h((string)($contract['date_signature'] ?? '')); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="renouvellement_auto">Auto Renewal *</label>
                <?php $selectedRenewal = (string)($contract['renouvellement_auto'] ?? ''); ?>
                <select class="form-select" id="renouvellement_auto" name="renouvellement_auto" required>
                    <option value="">Choose...</option>
                    <option value="oui"<?= $selectedRenewal === 'oui' ? ' selected' : ''; ?>>Yes</option>
                    <option value="non"<?= $selectedRenewal === 'non' ? ' selected' : ''; ?>>No</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="document_pdf">Replace PDF (optional)</label>
                <input class="form-control" type="file" id="document_pdf" name="document_pdf" accept="application/pdf">
                <?php if (!empty($contract['pdf_file_name'])): ?>
                    <small class="text-muted d-block mt-1">Current: <?= h((string)$contract['pdf_file_name']); ?></small>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="auth_key">New Authentication Key (optional)</label>
                <input class="form-control" type="password" id="auth_key" name="auth_key" placeholder="Leave empty to keep current key">
            </div>
            <div class="col-12">
                <label class="form-label" for="details">Details *</label>
                <textarea class="form-control" id="details" name="details" rows="4" minlength="15" required><?= h((string)($contract['details'] ?? '')); ?></textarea>
            </div>
            <div class="col-12 d-flex justify-content-end gap-2">
                <a class="btn btn-outline-secondary" href="<?= h(appUrl(['controller' => 'contract', 'action' => 'list'])); ?>">Cancel</a>
                <button class="btn btn-dark" type="submit">Update Contract</button>
            </div>
        </form>
    </div>
</div>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
