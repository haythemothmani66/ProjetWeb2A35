<?php
class Controller {
    public function model($model) {
        require_once '../app/models/' . $model . '.php';
        return new $model();
    }

    public function view($view, $data = []) {
        if (file_exists('../app/views/' . $view . '.php')) {
            require_once '../app/views/' . $view . '.php';
        } else {
            die("La vue n'existe pas.");
        }
    }

    protected function redirect($path) {
        $base = defined('BASE_URL') ? BASE_URL : '';
        $url = $path;
        if (strpos($path, '/') === 0) {
            $url = $base . $path;
        }
        header('Location: ' . $url);
        exit;
    }

    protected function e($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    protected function setFlash($type, $message) {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message
        ];
    }

    protected function getFlash() {
        if (!isset($_SESSION['flash'])) {
            return null;
        }

        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
}
?>
