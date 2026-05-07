<?php
declare(strict_types=1);
require dirname(__DIR__) . '/layout/header.php';
$partners = $partners ?? [];
?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h2 class="h5 mb-0">Partenaire Verification Queue</h2>
        <a class="btn btn-outline-secondary btn-sm" href="<?= h(appUrl(['controller' => 'partenaire', 'action' => 'list'])); ?>">Back to list</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Organization</th>
                    <th>Type</th>
                    <th>Email</th>
                    <th>Submitted</th>
                    <th class="text-end">Verification</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($partners)): ?>
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">No pending partner requests for verification.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($partners as $partner): ?>
                    <tr>
                        <td><?= h((string)($partner['organization_name'] ?? '')); ?></td>
                        <td><?= h((string)($partner['partner_type'] ?? '')); ?></td>
                        <td><?= h((string)($partner['email'] ?? '')); ?></td>
                        <td><?= h((string)($partner['created_at'] ?? '')); ?></td>
                        <td class="text-end">
                            <form class="d-inline" method="post" action="<?= h(appUrl(['controller' => 'partenaire', 'action' => 'verify', 'id' => (int)$partner['id']])); ?>">
                                <input type="hidden" name="status" value="approved">
                                <button class="btn btn-sm btn-outline-success" type="submit">Approve</button>
                            </form>
                            <form class="d-inline" method="post" action="<?= h(appUrl(['controller' => 'partenaire', 'action' => 'verify', 'id' => (int)$partner['id']])); ?>">
                                <input type="hidden" name="status" value="rejected">
                                <button class="btn btn-sm btn-outline-danger" type="submit">Reject</button>
                            </form>
                            <a class="btn btn-sm btn-outline-primary" href="<?= h(appUrl(['controller' => 'partenaire', 'action' => 'edit', 'id' => (int)$partner['id']])); ?>">Review</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
