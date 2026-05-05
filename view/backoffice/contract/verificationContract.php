<?php
declare(strict_types=1);
require dirname(__DIR__) . '/layout/header.php';
$contracts = $contracts ?? [];
?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h2 class="h5 mb-0">Contract Verification</h2>
        <a class="btn btn-outline-secondary btn-sm" href="<?= h(appUrl(['controller' => 'contract', 'action' => 'list'])); ?>">Back to list</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Reference</th>
                    <th>Company</th>
                    <th>Type</th>
                    <th>Current Status</th>
                    <th class="text-end">Set Status</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($contracts)): ?>
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">No contracts available for verification.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($contracts as $contract): ?>
                    <tr>
                        <td><?= h((string)($contract['contract_ref'] ?? '')); ?></td>
                        <td><?= h((string)($contract['company_name'] ?? '')); ?></td>
                        <td><?= h((string)($contract['type_contrat'] ?? '')); ?></td>
                        <td><?= h((string)($contract['statut'] ?? '')); ?></td>
                        <td class="text-end">
                            <form class="d-inline" method="post" action="<?= h(appUrl(['controller' => 'contract', 'action' => 'verify', 'id' => (int)$contract['id']])); ?>">
                                <input type="hidden" name="status" value="Actif">
                                <button class="btn btn-sm btn-outline-success" type="submit">Actif</button>
                            </form>
                            <form class="d-inline" method="post" action="<?= h(appUrl(['controller' => 'contract', 'action' => 'verify', 'id' => (int)$contract['id']])); ?>">
                                <input type="hidden" name="status" value="Suspendu">
                                <button class="btn btn-sm btn-outline-warning" type="submit">Suspendu</button>
                            </form>
                             <form class="d-inline" method="post" action="<?= h(appUrl(['controller' => 'contract', 'action' => 'verify', 'id' => (int)$contract['id']])); ?>">
                                <input type="hidden" name="status" value="Expire">
                                <button class="btn btn-sm btn-outline-secondary" type="submit">Expire</button>
                            </form>
                            <form class="d-inline" method="post" action="<?= h(appUrl(['controller' => 'contract', 'action' => 'verify', 'id' => (int)$contract['id']])); ?>">
                                <input type="hidden" name="status" value="Rejeté">
                                <button class="btn btn-sm btn-outline-danger" type="submit">Rejeté</button>
                            </form>
                            <a class="btn btn-sm btn-outline-primary" href="<?= h(appUrl(['controller' => 'contract', 'action' => 'edit', 'id' => (int)$contract['id']])); ?>">Review</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
