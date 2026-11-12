<?php
// Local preview only (php -S). Mimics the .htaccess rules; not needed on real hosting.
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
if (preg_match('~(^|/)\.|\.(json|md|ini|log|bak|sh)$|/config\.php$|/HOW-TO-INSTALL\.txt$|^/router\.php$~i', $path)) {
    http_response_code(403); exit('Forbidden');
}
$file = __DIR__ . $path;
if ($path !== '/' && is_file($file)) {
    if (substr($file, -4) === '.php') { $_SERVER['SCRIPT_NAME'] = $path; chdir(dirname($file)); require $file; return true; }
    return false; // static file
}
if (is_dir($file) && is_file(rtrim($file, '/') . '/index.php')) {
    $_SERVER['SCRIPT_NAME'] = rtrim($path, '/') . '/index.php';
    chdir($file); require rtrim($file, '/') . '/index.php'; return true;
}
$_SERVER['SCRIPT_NAME'] = '/index.php';
require __DIR__ . '/index.php';
