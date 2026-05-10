<?php
session_start();

// BASE_URL pointe vers public/index.php?url= pour le routing du module evenements
// Resultat : /gestion_users/public/index.php?url=
$projectBase = rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME']))), '/');
define('BASE_URL', $projectBase . '/public/index.php?url=');

// PROJECT_URL : base du projet (pour assets, redirections vers le frontoffice principal)
define('PROJECT_URL', $projectBase);

require_once '../config/database.php';
require_once '../app/App.php';
require_once '../app/controllers/Controller.php';

$app = new App();
?>
