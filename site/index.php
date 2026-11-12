<?php
/** ETCO website – public front controller. All pages go through this file. */
define('ROOT', __DIR__);
$b = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
define('BASE', $b === '/' ? '' : $b);
require ROOT . '/inc/core.php';

if (!is_file(DATA_DIR . '/content.json')) { http_response_code(500); exit('Content file missing: data/content.json'); }

/* ---- Work out the requested language / page / item ---- */
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = rawurldecode(substr($path, strlen(BASE)));
$path = trim(preg_replace('~^/?index\.php~', '', $path), '/');
$seg = $path === '' ? [] : explode('/', $path);

// Old website addresses → new pages (keeps Google links working)
$legacy = ['Home' => 'home', 'Services' => 'sectors', 'Gallery' => 'projects', 'Projects' => 'projects', 'Contactus' => 'contact', 'Aboutus' => 'about'];
if (count($seg) >= 1 && isset($legacy[$seg[0]])) {
    set_lang('en');
    header('Location: ' . url($legacy[$seg[0]], '', 'en', $seg[0] === 'Gallery' ? 'gallery' : ''), true, 301);
    exit;
}

$l = $_GET['lang'] ?? null; $page = $_GET['p'] ?? null; $slug = $_GET['s'] ?? '';
if (!$l && isset($seg[0]) && in_array($seg[0], ['en', 'ar'], true)) { $l = $seg[0]; $page = $seg[1] ?? ''; $slug = $seg[2] ?? ''; }
elseif (!$l && !$seg) {
    // first visit: pick language from the browser
    $al = strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '');
    $l = (strpos($al, 'ar') === 0) ? 'ar' : 'en';
    set_lang($l);
    header('Location: ' . url('home', '', $l), true, 302);
    exit;
} elseif (!$l) { $page = '404'; $l = 'en'; }
set_lang($l);

$routes = ['' => 'home', 'sectors' => 'sectors', 'projects' => 'projects', 'values' => 'values', 'locations' => 'locations',
           'about-us' => 'about', 'contact-us' => 'contact', 'join-us' => 'careers'];
$view = $routes[$page ?? ''] ?? '404';
$C = content();

require ROOT . '/inc/layout.php';
require ROOT . '/inc/components.php';

if ($view === 'sectors' && $slug !== '') $view = 'sector';
if ($view === 'projects' && $slug !== '') $view = 'project';

$file = ROOT . '/pages/' . $view . '.php';
if (!is_file($file)) $file = ROOT . '/pages/404.php';
include $file;
