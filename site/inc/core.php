<?php
/**
 * ETCO website – shared core functions (used by the public site and the admin panel).
 * You normally never need to edit this file: everything is managed from /admin.
 */
if (!defined('ROOT')) define('ROOT', dirname(__DIR__));
define('DATA_DIR', ROOT . '/data');
define('UPLOAD_DIR', ROOT . '/uploads');

$CONFIG = ['pretty_urls' => true, 'debug' => false];
if (is_file(ROOT . '/config.php')) { $CONFIG = array_merge($CONFIG, (array) include ROOT . '/config.php'); }

if (!$CONFIG['debug']) { error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING); ini_set('display_errors', '0'); }
mb_internal_encoding('UTF-8');
date_default_timezone_set('Asia/Beirut');

/* ---------- Data storage (JSON files in /data) ---------- */
function data_path($name) { return DATA_DIR . '/' . preg_replace('/[^a-z0-9_\-]/i', '', $name) . '.json'; }

function data_load($name, $default = []) {
    static $cache = [];
    $f = data_path($name);
    if (isset($cache[$f])) return $cache[$f];
    if (!is_file($f)) return $default;
    $raw = file_get_contents($f);
    $d = json_decode($raw, true);
    if (!is_array($d)) return $default;
    return $cache[$f] = $d;
}

function data_save($name, $data, $backup = false) {
    $f = data_path($name);
    if (!is_dir(DATA_DIR)) mkdir(DATA_DIR, 0755, true);
    if ($backup && is_file($f)) {
        $bdir = DATA_DIR . '/backups';
        if (!is_dir($bdir)) mkdir($bdir, 0755, true);
        copy($f, $bdir . '/' . $name . '-' . date('Y-m-d_H-i-s') . '.json');
        $all = glob($bdir . '/' . $name . '-*.json');
        sort($all);
        while (count($all) > 40) { @unlink(array_shift($all)); }
    }
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    if ($json === false) return false;
    $tmp = $f . '.tmp' . mt_rand();
    if (file_put_contents($tmp, $json, LOCK_EX) === false) return false;
    return rename($tmp, $f);
}

function content() { return data_load('content'); }

/* ---------- Language ---------- */
$GLOBALS['LANG'] = 'en';
function lang() { return $GLOBALS['LANG']; }
function set_lang($l) { $GLOBALS['LANG'] = ($l === 'ar') ? 'ar' : 'en'; }
function is_rtl() { return lang() === 'ar'; }
function other_lang() { return lang() === 'ar' ? 'en' : 'ar'; }

/** Translate a {en, ar} field. Falls back to English, then to any string. */
function t($v, $l = null) {
    $l = $l ?: lang();
    if (is_array($v)) {
        if (isset($v[$l]) && trim((string) $v[$l]) !== '') return (string) $v[$l];
        if (isset($v['en'])) return (string) $v['en'];
        return '';
    }
    return (string) $v;
}

/** Interface label (menu, buttons…), editable in Admin → Menu & labels. */
function L($key) {
    $c = content();
    return isset($c['labels'][$key]) ? t($c['labels'][$key]) : $key;
}

function e($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }

/** Escape text and turn *words* into highlighted <strong> words. */
function hl($s) { return preg_replace('/\*(.+?)\*/u', '<strong>$1</strong>', e($s)); }
function plain($s) { return str_replace('*', '', (string) $s); }

/** Escape multi-paragraph text: blank line = new paragraph, single line break = <br>. */
function paras($s, $class = '') {
    $s = trim(str_replace("\r", '', (string) $s));
    if ($s === '') return '';
    $out = '';
    foreach (preg_split('/\n\s*\n/', $s) as $p) {
        $out .= '<p' . ($class ? ' class="' . e($class) . '"' : '') . '>' . nl2br(hl(trim($p)), false) . '</p>';
    }
    return $out;
}

/* ---------- URLs ---------- */
function base() { return defined('BASE') ? BASE : ''; }

function url($page = '', $slug = '', $l = null, $hash = '') {
    global $CONFIG;
    $l = $l ?: lang();
    $map = ['home' => '', 'sectors' => 'sectors', 'projects' => 'projects', 'values' => 'values', 'locations' => 'locations',
            'about' => 'about-us', 'contact' => 'contact-us', 'careers' => 'join-us'];
    $p = isset($map[$page]) ? $map[$page] : $page;
    if ($CONFIG['pretty_urls']) {
        $u = base() . '/' . $l . '/' . ($p !== '' ? $p . '/' : '') . ($slug !== '' ? rawurlencode($slug) . '/' : '');
    } else {
        $u = base() . '/index.php?lang=' . $l . ($p !== '' ? '&p=' . $p : '') . ($slug !== '' ? '&s=' . rawurlencode($slug) : '');
    }
    return $u . ($hash ? '#' . $hash : '');
}

/** Public URL of an image/file path stored in the content (relative to the site root, or a full http URL). */
function asset($path) {
    $path = (string) $path;
    if ($path === '') return '';
    if (preg_match('~^(https?:)?//~i', $path) || strpos($path, 'data:') === 0) return $path;
    $parts = array_map('rawurlencode', explode('/', ltrim($path, '/')));
    return base() . '/' . implode('/', $parts);
}

function asset_v($path) { // static asset with cache-busting version
    $f = ROOT . '/' . $path;
    return base() . '/' . $path . (is_file($f) ? '?v=' . filemtime($f) : '');
}

/* ---------- Collections helpers ---------- */
function visible($list) { return array_values(array_filter((array) $list, function ($i) { return !isset($i['visible']) || $i['visible']; })); }

function find_by($list, $key, $val) {
    foreach ((array) $list as $i) if (isset($i[$key]) && (string) $i[$key] === (string) $val) return $i;
    return null;
}

function slugify($s) {
    $s = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', (string) $s)), '-'));
    return $s !== '' ? $s : 'item-' . substr(md5(uniqid('', true)), 0, 6);
}

function new_id($prefix = 'i') { return $prefix . substr(md5(uniqid('', true)), 0, 8); }

function years_active() {
    $c = content();
    $f = (int) ($c['settings']['founded'] ?? 2001);
    return max(1, (int) date('Y') - $f);
}

function stat_value($v) {
    $c = content();
    return strtr((string) $v, ['{years}' => years_active(), '{projects}' => count(visible($c['projects'] ?? []))]);
}

function project_year($p) {
    $y = trim((string) ($p['year'] ?? ''));
    if ($y === '') return '';
    if (strtolower($y) === 'ongoing') return L('ongoing');
    return $y;
}

/* ---------- Sessions & security ---------- */
function start_session() {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_name('etco_sid');
    session_set_cookie_params(['lifetime' => 0, 'path' => base() . '/', 'secure' => $secure, 'httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

function csrf_token() {
    start_session();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function csrf_field() { return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">'; }
function csrf_ok() { start_session(); return isset($_POST['_csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], (string) $_POST['_csrf']); }

function client_ip() { return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'; }

/** Very small file-based rate limiter: max $max hits per $window seconds per key. */
function rate_limited($key, $max, $window) {
    $f = DATA_DIR . '/ratelimit.json';
    $d = is_file($f) ? (json_decode(file_get_contents($f), true) ?: []) : [];
    $now = time();
    foreach ($d as $k => $hits) { $d[$k] = array_values(array_filter($hits, function ($t) use ($now, $window) { return $t > $now - $window; })); if (!$d[$k]) unset($d[$k]); }
    $hits = $d[$key] ?? [];
    $limited = count($hits) >= $max;
    if (!$limited) { $hits[] = $now; $d[$key] = $hits; }
    @file_put_contents($f, json_encode($d), LOCK_EX);
    return $limited;
}

/* ---------- Email ---------- */
function encode_header_utf8($s) { return '=?UTF-8?B?' . base64_encode($s) . '?='; }

function send_mail($to, $subject, $text, $replyTo = '', $attachments = []) {
    $c = content();
    $from = trim($c['settings']['mail_from'] ?? '') ?: ('no-reply@' . preg_replace('/^www\./', '', $_SERVER['HTTP_HOST'] ?? 'localhost'));
    $fromName = t($c['settings']['short_name'] ?? 'ETCO', 'en') . ' Website';
    $headers = "From: " . encode_header_utf8($fromName) . " <$from>\r\n";
    if ($replyTo && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) $headers .= "Reply-To: $replyTo\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    if (!$attachments) {
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n";
        $body = chunk_split(base64_encode($text));
    } else {
        $b = 'b' . md5(uniqid('', true));
        $headers .= "Content-Type: multipart/mixed; boundary=\"$b\"\r\n";
        $body = "--$b\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n" . chunk_split(base64_encode($text));
        foreach ($attachments as $a) {
            if (!is_file($a['path'])) continue;
            $body .= "--$b\r\nContent-Type: application/octet-stream; name=\"" . $a['name'] . "\"\r\nContent-Transfer-Encoding: base64\r\nContent-Disposition: attachment; filename=\"" . $a['name'] . "\"\r\n\r\n" . chunk_split(base64_encode(file_get_contents($a['path'])));
        }
        $body .= "--$b--";
    }
    return @mail($to, encode_header_utf8($subject), $body, $headers, '-f' . $from) || @mail($to, encode_header_utf8($subject), $body, $headers);
}

/* ---------- Icons (inline SVG, stroke style) ---------- */
function icon($name, $class = '') {
    static $p = [
        'phone' => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/>',
        'mail' => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/>',
        'pin' => '<path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/>',
        'clock' => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
        'arrow' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'chev' => '<path d="m6 9 6 6 6-6"/>',
        'chevr' => '<path d="m9 6 6 6-6 6"/>',
        'globe' => '<circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>',
        'menu' => '<path d="M3 6h18M3 12h18M3 18h18"/>',
        'close' => '<path d="M18 6 6 18M6 6l12 12"/>',
        'check' => '<path d="M20 6 9 17l-5-5"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
        'tag' => '<path d="M20.6 13.4 13.4 20.6a2 2 0 0 1-2.8 0L2 12V2h10l8.6 8.6a2 2 0 0 1 0 2.8z"/><circle cx="7" cy="7" r="1.5"/>',
        'wrench' => '<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.8-3.8a6 6 0 0 1-7.9 7.9l-6.9 6.9a2.1 2.1 0 0 1-3-3l6.9-6.9a6 6 0 0 1 7.9-7.9l-3.8 3.8z"/>',
        'upload' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/>',
        'image' => '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-5-5L5 21"/>',
        // sectors
        'mep' => '<path d="M13 2 4 14h7l-1 8 9-12h-7z"/>',
        'structural' => '<path d="M2 22h20M6 22V9l6-5 6 5v13M10 22v-6h4v6M9 11h6"/>',
        'renovation' => '<path d="m15 12-8.5 8.5a2.1 2.1 0 0 1-3-3L12 9"/><path d="M17.6 15 22 10.6M20 2l2 2-7 7-2-2z"/><path d="M13 7 9 3"/>',
        'infrastructure' => '<path d="M4 22 9 2M20 22 15 2M12 6v2M12 12v2M12 18v2"/>',
        'facility' => '<path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-4h6v4"/><path d="M9 10h.01M15 10h.01M9 13.5h.01M15 13.5h.01"/>',
        'pm' => '<rect x="8" y="2" width="8" height="4" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="m9 14 2 2 4-4"/>',
        // values
        'quality' => '<circle cx="12" cy="8" r="6"/><path d="M15.5 13 17 22l-5-3-5 3 1.5-9"/>',
        'integrity' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/>',
        'expertise' => '<path d="M22 10 12 5 2 10l10 5 10-5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>',
        'satisfaction' => '<circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2M9 9h.01M15 9h.01"/>',
        'safety' => '<path d="M2 18a10 10 0 0 1 20 0z"/><path d="M10 8V5a2 2 0 0 1 4 0v3M2 18h20v2H2z"/>',
        'sustainability' => '<path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.5 19 2c1 2 2 4.2 2 8 0 5.5-4.8 10-10 10z"/><path d="M2 21c0-3 1.9-5.4 5.1-6"/>',
        'approach' => '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/>',
        'projects' => '<path d="M3 21h18M6 21V5h12v16M9 9h2M13 9h2M9 13h2M13 13h2M10 21v-4h4v4"/>',
    ];
    $d = $p[$name] ?? $p['check'];
    return '<svg class="ic ic-' . e($name) . ($class ? ' ' . e($class) : '') . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $d . '</svg>';
}

function icon_names() { return ['mep', 'structural', 'renovation', 'infrastructure', 'facility', 'pm', 'quality', 'integrity', 'expertise', 'satisfaction', 'safety', 'sustainability', 'approach', 'projects', 'wrench', 'phone', 'mail', 'pin', 'clock', 'globe', 'check']; }
