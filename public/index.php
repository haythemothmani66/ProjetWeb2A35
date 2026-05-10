<?php
session_start();

define('BASE_URL', rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME']))), '/'));

require_once '../config/database.php';
require_once '../app/App.php';
require_once '../app/controllers/Controller.php';

$app = new App();
?>
