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
            "Offre d'emploi" => '/gestion_users/controller/OffreEmploiController.php?espace=front&action=liste',
            'Quiz' => '#',
            'Evenements' => '#',
            'Devoirs' => '#',
        ];

        $moduleCardLinks = [
            "Offre d'emploi" => '/gestion_users/controller/OffreEmploiController.php?espace=front&action=liste',
        ];

        $moduleDescriptions = [
            'Parteneriat' => 'Explorez les opportunites de partenariat et les connexions utiles.',
            "Offre d'emploi" => 'Decouvrez les offres d\'emploi et de stage.',
            'Quiz' => 'Testez et ameliorez vos connaissances grace aux quiz.',
            'Evenements' => 'Restez informe des evenements a venir.',
            'Devoirs' => 'Accedez aux devoirs et aux taches de cours.',
        ];

        include __DIR__ . '/../../view/frontoffice/index.php';
    }
}
