<?php
$moduleLinks = $moduleLinks ?? [];
?>
<div id="navigation" class="navbar-light bg-faded site-navigation">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-20">
                <div class="site-logo">
                    <a href="index.php?page=home"><img src="assets/img/EduMatch Logo.png" alt="EduMatch"></a>
                </div>
            </div>

            <div class="col-60 d-flex">
                <nav id="main-menu">
                    <ul>
                        <li><a href="index.php?page=home">Accueil</a></li>
                        <?php foreach ($moduleLinks as $label => $url): ?>
                            <li><a href="<?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </nav>
            </div>

            <div class="col-20 d-none d-xl-block text-end d-flex gap-2 justify-content-end align-items-center">
                <?php
                $isHomePage = ($_GET['page'] ?? '') === 'home' || !isset($_GET['espace']);
                if ($isHomePage):
                ?>
                    <a href="index.php?page=connexion" class="header-btn">Connexion</a>
                    <a href="index.php?page=inscription" class="btn_one">Inscription</a>
                <?php else: ?>
                    <a href="index.php?espace=back&module=offreemploi&action=liste" class="btn_one">Back Office</a>
                <?php endif; ?>
            </div>

            <ul class="mobile_menu">
                <li><a href="index.php?page=home">Accueil</a></li>
                <li><a href="index.php?espace=back&module=offreemploi&action=liste">Back Office</a></li>
                <?php foreach ($moduleLinks as $label => $url): ?>
                    <li><a href="<?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
