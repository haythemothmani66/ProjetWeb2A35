<?php
/**
 * Routeur Module Quiz / Formations / Certificats
 *
 * URLs:
 *   ?espace=front&resource=courses&action=index           -> public liste cours
 *   ?espace=front&resource=courses&action=show&id=X       -> public detail cours
 *   ?espace=front&resource=courses&action=pdf&id=X        -> public PDF
 *   ?espace=front&resource=lessons&action=show&id=X       -> public lecon
 *   ?espace=front&resource=quizzes&action=take&id=X       -> etudiant + admin (prendre quiz)
 *   ?espace=front&resource=quizzes&action=submit          -> etudiant + admin (soumettre)
 *   ?espace=front&resource=quizzes&action=battle&...      -> etudiant + admin (mode duel)
 *   ?espace=front&resource=quizzes&action=result&...      -> etudiant + admin (resultat)
 *   ?espace=front&resource=certificates&action=show&...   -> public (verifier certificat)
 *   ?espace=front&resource=certificates&action=generate   -> etudiant + admin (generer apres quiz)
 *
 *   ?espace=back  (toutes resources)                       -> encadrant + admin (CRUD complet)
 *     resources : dashboard, courses, quizzes, lessons, questions, certificates
 *     actions   : index, create, store, edit, update, delete, stats, generate, storeAi, quizzes
 */
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/quiz/_route_helpers.php';

// Autoload pour le module quiz
spl_autoload_register(function ($class) {
    $dirs = [
        __DIR__ . '/../model/quiz/',
        __DIR__ . '/../model/quiz/repositories/',
        __DIR__ . '/quiz/',
        __DIR__ . '/../api/',
    ];
    foreach ($dirs as $d) {
        $file = $d . $class . '.php';
        if (is_file($file)) { require_once $file; return; }
    }
});

$espace   = $_GET['espace']   ?? 'front';
$resource = $_GET['resource'] ?? 'courses';
$action   = $_GET['action']   ?? 'index';

// Whitelist resources & actions
$validResources = ['dashboard', 'courses', 'quizzes', 'lessons', 'questions', 'certificates'];
if (!in_array($resource, $validResources, true)) {
    http_response_code(404);
    echo '<h1>404 - Resource inconnue</h1>';
    exit;
}

// Securite role-based
if ($espace === 'back') {
    // Backoffice : encadrant + admin uniquement
    if (empty($_SESSION['user_id'])) {
        header('Location: /gestion_users/view/template/sign-in.php');
        exit;
    }
    $role = $_SESSION['user_role'] ?? '';
    if (!in_array($role, ['encadrant', 'admin'], true)) {
        header('Location: /gestion_users/controller/QuizController.php?espace=front&resource=courses&action=index&error=role');
        exit;
    }
} elseif ($espace === 'front') {
    // Actions front qui necessitent un compte etudiant + admin (prendre quiz, generer certificat)
    $protectedFrontActions = [
        'quizzes' => ['take', 'submit', 'battle', 'result'],
        'certificates' => ['generate'],
    ];
    if (isset($protectedFrontActions[$resource]) && in_array($action, $protectedFrontActions[$resource], true)) {
        if (empty($_SESSION['user_id'])) {
            header('Location: /gestion_users/view/template/sign-in.php');
            exit;
        }
        $role = $_SESSION['user_role'] ?? '';
        if (!in_array($role, ['etudiant', 'admin'], true)) {
            header('Location: /gestion_users/controller/QuizController.php?espace=front&resource=courses&action=index&error=role_student');
            exit;
        }
    }
}

// Determiner le controller a instancier
$prefix = $espace === 'back' ? 'Backoffice' : 'Frontoffice';
$controllerName = $prefix . ucfirst($resource) . 'Controller';

if (!class_exists($controllerName)) {
    http_response_code(404);
    echo "<h1>404 - Controller introuvable : $controllerName</h1>";
    exit;
}

$controller = new $controllerName();

if (!method_exists($controller, $action)) {
    http_response_code(404);
    echo "<h1>404 - Action introuvable : $controllerName::$action()</h1>";
    exit;
}

// Dispatch (POST envoie $_POST, GET envoie $_GET)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->{$action}($_POST);
} else {
    $controller->{$action}($_GET);
}
