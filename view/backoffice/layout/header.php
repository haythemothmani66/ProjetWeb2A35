<?php
declare(strict_types=1);

$pageTitle = $pageTitle ?? 'EduMatch BackOffice';
$messages = $messages ?? [];
$currentController = strtolower((string)($_GET['controller'] ?? ''));
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($pageTitle); ?> - EduMatch</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= h(assetUrl('view/backoffice/src/assets/css/theme.css')); ?>">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="<?= h(appUrl()); ?>">EduMatch MVC</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link<?= $currentController === 'partenaire' ? ' active' : ''; ?>" href="<?= h(appUrl(['controller' => 'partenaire', 'action' => 'list'])); ?>">Partenaires</a></li>
                <li class="nav-item"><a class="nav-link<?= $currentController === 'contract' ? ' active' : ''; ?>" href="<?= h(appUrl(['controller' => 'contract', 'action' => 'list'])); ?>">Contracts</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= h(assetUrl('view/frontoffice/index.html')); ?>">FrontOffice</a></li>
            </ul>
        </div>
    </div>
</nav>

<main class="container pb-5">
    <?php foreach ($messages as $message): ?>
        <?php
        $type = (string)($message['type'] ?? 'info');
        $allowed = ['success', 'danger', 'warning', 'info'];
        if (!in_array($type, $allowed, true)) {
            $type = 'info';
        }
        ?>
        <div class="alert alert-<?= h($type); ?>" role="alert"><?= h((string)($message['message'] ?? '')); ?></div>
    <?php endforeach; ?>
