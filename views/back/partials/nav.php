<?php
$activeTab = $activeTab ?? '';

$tabs = [
    'offres' => [
        'label' => "Offres d'emploi",
        'href' => 'index.php?espace=back&module=offreemploi&action=liste',
    ],
    'stats' => [
        'label' => 'Statistiques',
        'href' => 'index.php?espace=back&module=offreemploi&action=stats',
    ],
    'candidatures' => [
        'label' => 'Candidatures',
        'href' => 'index.php?espace=back&module=candidature&action=liste',
    ],
    'front_offres' => [
        'label' => 'Front Office offres',
        'href' => 'index.php?espace=front&module=offreemploi&action=liste',
    ],
];
?>
<nav style="padding:1rem;display:flex;flex-direction:column;gap:.35rem;">
    <?php foreach ($tabs as $key => $tab): ?>
        <?php $isActive = $activeTab === $key; ?>
        <a
            href="<?= htmlspecialchars($tab['href'], ENT_QUOTES, 'UTF-8') ?>"
            style="display:flex;align-items:center;gap:.75rem;padding:.8rem 1rem;border-radius:.75rem;text-decoration:none;color:rgba(255,255,255,.82);<?= $isActive ? 'background:rgba(255,255,255,.08);color:#fff;' : '' ?>"
            onmouseover="this.style.background='rgba(255,255,255,.08)';this.style.color='#fff';"
            onmouseout="<?= $isActive ? "this.style.background='rgba(255,255,255,.08)';this.style.color='#fff';" : "this.style.background='transparent';this.style.color='rgba(255,255,255,.82)';" ?>"
        >
            <?= htmlspecialchars($tab['label'], ENT_QUOTES, 'UTF-8') ?>
        </a>
    <?php endforeach; ?>
</nav>
