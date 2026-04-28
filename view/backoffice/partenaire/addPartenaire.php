<?php
declare(strict_types=1);
require dirname(__DIR__) . '/layout/header.php';
$oldInput = $oldInput ?? [];
?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h2 class="h5 mb-0">Add New Partenaire</h2>
        <a class="btn btn-outline-secondary btn-sm" href="<?= h(appUrl(['controller' => 'partenaire', 'action' => 'list'])); ?>">Back to list</a>
    </div>
    <div class="card-body">
        <form action="<?= h(appUrl(['controller' => 'partenaire', 'action' => 'store'])); ?>" method="post" enctype="multipart/form-data" class="row g-3" novalidate>
            <div class="col-md-6">
                <label class="form-label" for="organization_name">Organization Name *</label>
                <input class="form-control" type="text" id="organization_name" name="organization_name" minlength="3" maxlength="120" required value="<?= h((string)($oldInput['organization_name'] ?? '')); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="partner_type">Partner Type *</label>
                <select class="form-select" id="partner_type" name="partner_type" required>
                    <?php $selectedType = (string)($oldInput['partner_type'] ?? ''); ?>
                    <option value="">Choose...</option>
                    <option value="company"<?= $selectedType === 'company' ? ' selected' : ''; ?>>Company</option>
                    <option value="university"<?= $selectedType === 'university' ? ' selected' : ''; ?>>University</option>
                    <option value="startup"<?= $selectedType === 'startup' ? ' selected' : ''; ?>>Startup</option>
                    <option value="ngo"<?= $selectedType === 'ngo' ? ' selected' : ''; ?>>NGO</option>
                    <option value="other"<?= $selectedType === 'other' ? ' selected' : ''; ?>>Other</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="email">Email *</label>
                <input class="form-control" type="email" id="email" name="email" required value="<?= h((string)($oldInput['email'] ?? '')); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="telephone">Telephone *</label>
                <input class="form-control" type="tel" id="telephone" name="telephone" pattern="[0-9]{7,15}" required value="<?= h((string)($oldInput['telephone'] ?? '')); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="address">Address</label>
                <input class="form-control" type="text" id="address" name="address" value="<?= h((string)($oldInput['address'] ?? '')); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="country">Country</label>
                <input class="form-control" type="text" id="country" name="country" value="<?= h((string)($oldInput['country'] ?? '')); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="domain">Website Domain</label>
                <input class="form-control" type="url" id="domain" name="domain" value="<?= h((string)($oldInput['domain'] ?? '')); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="logo">Logo (max 2MB)</label>
                <input class="form-control" type="file" id="logo" name="logo" accept="image/*">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="status">Status *</label>
                <?php $selectedStatus = (string)($oldInput['status'] ?? 'pending'); ?>
                <select class="form-select" id="status" name="status" required>
                    <option value="pending"<?= $selectedStatus === 'pending' ? ' selected' : ''; ?>>Pending</option>
                    <option value="approved"<?= $selectedStatus === 'approved' ? ' selected' : ''; ?>>Approved</option>
                    <option value="rejected"<?= $selectedStatus === 'rejected' ? ' selected' : ''; ?>>Rejected</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="auth_key">Authentication Key *</label>
                <input class="form-control" type="password" id="auth_key" name="auth_key" required>
            </div>
            <div class="col-12">
                <label class="form-label" for="description">Description</label>
                <textarea class="form-control" id="description" name="description" rows="4" maxlength="1000"><?= h((string)($oldInput['description'] ?? '')); ?></textarea>
            </div>
            <div class="col-12 d-flex justify-content-end gap-2">
                <button class="btn btn-outline-secondary" type="reset">Reset</button>
                <button class="btn btn-dark" type="submit">Save Partenaire</button>
            </div>
        </form>
    </div>
</div>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
