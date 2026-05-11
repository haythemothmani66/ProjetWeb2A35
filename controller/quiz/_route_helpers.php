<?php
/**
 * Helpers de routes pour le module Quiz (compatibilite avec controllers/vues originaux).
 *
 * Le module quiz utilisait `backofficeRoute('courses', 'index')` qui generait
 * `admin.php?route=backoffice/courses/index`. Ici on remappe vers notre routeur unifie :
 *   /gestion_users/controller/QuizController.php?espace=back&resource=courses&action=index
 */

if (!function_exists('backofficeRoute')) {
    function backofficeRoute(string $resource, string $action = 'index', array $params = []): string
    {
        $qs = http_build_query(array_merge([
            'espace'   => 'back',
            'resource' => $resource,
            'action'   => $action,
        ], $params));
        return '/gestion_users/controller/QuizController.php?' . $qs;
    }
}

if (!function_exists('frontofficeRoute')) {
    function frontofficeRoute(string $resource, string $action = 'index', array $params = []): string
    {
        $qs = http_build_query(array_merge([
            'espace'   => 'front',
            'resource' => $resource,
            'action'   => $action,
        ], $params));
        return '/gestion_users/controller/QuizController.php?' . $qs;
    }
}

if (!function_exists('route')) {
    function route(string $area, string $resource, string $action = 'index', array $params = []): string
    {
        if ($area === 'backoffice') {
            return backofficeRoute($resource, $action, $params);
        }
        return frontofficeRoute($resource, $action, $params);
    }
}

if (!function_exists('htmlLink')) {
    function htmlLink(string $text, string $href, array $attributes = []): string
    {
        $attrs = '';
        foreach ($attributes as $key => $value) {
            $attrs .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
        }
        return '<a href="' . $href . '"' . $attrs . '>' . $text . '</a>';
    }
}
