<?php

declare(strict_types=1);

class HomeController
{
    public function index(): void
    {
        $websiteName = 'EduMatch';
        $pageTitle = 'EduMatch - Espace Front';
        $moduleLinks = [
            'Parteneriat' => '#',
            "Offre d'emploi" => 'index.php?espace=front&module=offreemploi&action=liste',
            'Quiz' => '#',
            'Evenements' => '#',
            'Devoirs' => '#',
        ];

        $moduleCardLinks = [
            "Offre d'emploi" => 'index.php?espace=front&module=offreemploi&action=liste',
        ];

        $moduleDescriptions = [
            'Parteneriat' => 'Explorez les opportunites de partenariat et les connexions utiles.',
            "Offre d'emploi" => 'Decouvrez les offres d\'emploi et de stage.',
            'Quiz' => 'Testez et ameliorez vos connaissances grace aux quiz.',
            'Evenements' => 'Restez informe des evenements a venir.',
            'Devoirs' => 'Accedez aux devoirs et aux taches de cours.',
        ];

        include __DIR__ . '/../../views/front/home/index.php';
    }
}
