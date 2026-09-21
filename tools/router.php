<?php
// Development only. Bind PHP's server to loopback, never a public interface.
$root = dirname(__DIR__);
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
if (str_contains($path, '..') || str_contains($path, '\\') || str_contains($path, "\0")) { http_response_code(404); exit; }
$static = preg_match('~^/(site/(assets|templates/assets|modules/ProcessCraft)|wire/(modules|templates-admin))/~', $path)
    && preg_match('~\.(css|js|jpg|jpeg|png|webp|gif|svg|ico|woff2?|ttf|map)$~i', $path);
if ($static && is_file($root . $path)) return false;
if ($path !== '/index.php' && preg_match('~^/(?:storage|tools|tests|resources|vendor|wire|site|\.runtime)(?:/|$)|^/\.|\.(php|md|json|sql|zip|log|lock)$~i', $path)) {
    http_response_code(404); exit;
}
$_GET['it'] = ltrim($path, '/');
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';
require $root . '/index.php';
