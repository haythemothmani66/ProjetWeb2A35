<?php
function route(string $area, string $resource, string $action = 'index', array $params = []): string
{
    $url = "index.php?route={$area}/{$resource}/{$action}";
    
    if (!empty($params)) {
        $url .= '&' . http_build_query($params);
    }
    
    return $url;
}

function frontofficeRoute(string $resource, string $action = 'index', array $params = []): string
{
    return route('frontoffice', $resource, $action, $params);
}

function backofficeRoute(string $resource, string $action = 'index', array $params = []): string
{
    $url = "admin.php?route=backoffice/{$resource}/{$action}";
    
    if (!empty($params)) {
        $url .= '&' . http_build_query($params);
    }
    
    return $url;
}

function htmlLink(string $text, string $href, array $attributes = []): string
{
    $attrs = '';
    foreach ($attributes as $key => $value) {
        $attrs .= " {$key}=\"" . htmlspecialchars($value) . "\"";
    }
    
    return "<a href=\"{$href}\"{$attrs}>{$text}</a>";
}
