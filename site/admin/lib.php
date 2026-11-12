<?php
/** Admin panel helpers: login, uploads, form fields. */

/* ---------- Users & login ---------- */
function users() { return data_load('users', []); }
function current_user() { start_session(); return $_SESSION['admin_user'] ?? null; }

function require_login() {
    start_session();
    if (empty($_SESSION['admin_user'])) { header('Location: ' . aurl('login')); exit; }
    if (time() - ($_SESSION['admin_last'] ?? 0) > 8 * 3600) { session_destroy(); header('Location: ' . aurl('login', ['expired' => 1])); exit; }
    $_SESSION['admin_last'] = time();
}

function aurl($s = 'dashboard', $q = []) {
    return base() . '/' . (defined('ADMIN_DIR') ? ADMIN_DIR : 'admin') . '/index.php?' . http_build_query(array_merge(['s' => $s], $q));
}

function go($s, $q = []) { header('Location: ' . aurl($s, $q), true, 303); exit; }

function aflash($type, $msg) { start_session(); $_SESSION['aflash'][] = [$type, $msg]; }
function aflash_take() { start_session(); $f = $_SESSION['aflash'] ?? []; unset($_SESSION['aflash']); return $f; }

function need_csrf() { if (!csrf_ok()) { aflash('err', 'Your session expired. Please try again.'); go($_GET['s'] ?? 'dashboard'); } }

/* ---------- Path helpers for nested content ---------- */
function &ref_path(array &$arr, array $path) {
    $r = &$arr;
    foreach ($path as $p) { if (!isset($r[$p]) || !is_array($r[$p])) $r[$p] = []; $r = &$r[$p]; }
    return $r;
}
function get_path($arr, $path) { foreach ($path as $p) $arr = $arr[$p] ?? []; return $arr; }

function save_content($C) {
    if (!data_save('content', $C, true)) { aflash('err', 'Could not save. Please check that the "data" folder is writable (permissions 755).'); return false; }
    return true;
}

/* ---------- Uploads ---------- */
function upload_error_text($code) {
    $max = ini_get('upload_max_filesize');
    $m = [UPLOAD_ERR_INI_SIZE => "the file is larger than your server allows ($max)", UPLOAD_ERR_FORM_SIZE => 'the file is too large',
          UPLOAD_ERR_PARTIAL => 'the upload was interrupted', UPLOAD_ERR_NO_TMP_DIR => 'the server has no temporary folder', UPLOAD_ERR_CANT_WRITE => 'the server could not write the file'];
    return $m[$code] ?? 'unknown upload error';
}

/** Normalise $_FILES[name] (single or multiple) into a list of files. */
function files_list($name) {
    if (empty($_FILES[$name])) return [];
    $f = $_FILES[$name];
    if (!is_array($f['name'])) return [$f];
    $out = [];
    foreach ($f['name'] as $i => $n) $out[] = ['name' => $n, 'type' => $f['type'][$i], 'tmp_name' => $f['tmp_name'][$i], 'error' => $f['error'][$i], 'size' => $f['size'][$i]];
    return $out;
}

/**
 * Store an uploaded file in /uploads/<folder>/. Returns ['path' => 'uploads/...'] or ['error' => '...'].
 * Large photos are reduced to 2400 px so pages stay fast.
 */
function store_upload($file, $folder = 'images', $exts = null, $allowSvg = false) {
    if ($file['error'] === UPLOAD_ERR_NO_FILE) return null;
    $name = $file['name'];
    if ($file['error'] !== UPLOAD_ERR_OK) return ['error' => "\"$name\": " . upload_error_text($file['error'])];
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if ($ext === 'jpeg') $ext = 'jpg';
    $imgExts = ['jpg', 'png', 'webp', 'gif'];
    if ($allowSvg) $imgExts[] = 'svg';
    $exts = $exts ?: $imgExts;
    if (!in_array($ext, $exts, true)) return ['error' => "\"$name\": file type .$ext is not allowed (allowed: " . implode(', ', $exts) . ')'];
    if (in_array($ext, ['jpg', 'png', 'webp', 'gif'], true) && !@getimagesize($file['tmp_name'])) return ['error' => "\"$name\" is not a valid image"];
    if ($ext === 'svg') {
        $svg = file_get_contents($file['tmp_name']);
        if (stripos($svg, '<svg') === false || preg_match('/<script|on\w+\s*=|javascript:/i', $svg)) return ['error' => "\"$name\": this SVG file cannot be used"];
    }
    $dir = UPLOAD_DIR . '/' . $folder . '/' . date('Y-m');
    if (!is_dir($dir) && !@mkdir($dir, 0755, true)) return ['error' => 'The "uploads" folder is not writable.'];
    $base = slugify(pathinfo($name, PATHINFO_FILENAME));
    $fn = substr($base, 0, 50) . '-' . substr(md5(uniqid('', true)), 0, 6) . '.' . $ext;
    $dest = $dir . '/' . $fn;
    if (!move_uploaded_file($file['tmp_name'], $dest) && !@rename($file['tmp_name'], $dest)) return ['error' => "\"$name\": could not be saved"];
    @chmod($dest, 0644);
    if (in_array($ext, ['jpg', 'png', 'webp'], true)) shrink_image($dest, $ext, 2400);
    return ['path' => 'uploads/' . $folder . '/' . date('Y-m') . '/' . $fn];
}

function shrink_image($file, $ext, $max) {
    if (!function_exists('imagecreatetruecolor')) return;
    $info = @getimagesize($file);
    if (!$info) return;
    [$w, $h] = $info;
    $orient = 1;
    if ($ext === 'jpg' && function_exists('exif_read_data')) { $ex = @exif_read_data($file); $orient = (int) ($ex['Orientation'] ?? 1); }
    if ($w <= $max && $h <= $max && $orient === 1) return;
    $src = $ext === 'jpg' ? @imagecreatefromjpeg($file) : ($ext === 'png' ? @imagecreatefrompng($file) : (function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($file) : null));
    if (!$src) return;
    if ($orient === 3) $src = imagerotate($src, 180, 0);
    if ($orient === 6) { $src = imagerotate($src, -90, 0); [$w, $h] = [$h, $w]; }
    if ($orient === 8) { $src = imagerotate($src, 90, 0); [$w, $h] = [$h, $w]; }
    $k = min(1, $max / max($w, $h));
    $nw = (int) round($w * $k); $nh = (int) round($h * $k);
    $dst = imagecreatetruecolor($nw, $nh);
    if ($ext !== 'jpg') { imagealphablending($dst, false); imagesavealpha($dst, true); }
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, imagesx($src), imagesy($src));
    if ($ext === 'jpg') imagejpeg($dst, $file, 84);
    elseif ($ext === 'png') imagepng($dst, $file, 8);
    else imagewebp($dst, $file, 84);
    imagedestroy($src); imagedestroy($dst);
}

/* ---------- Form fields ---------- */
function field_options($f) { $o = $f['options'] ?? []; return is_callable($o) ? $o() : $o; }

function thumb_html($path, $class = 'thumb') {
    if (!$path) return '<span class="' . $class . ' ' . $class . '--empty">' . icon('image') . '</span>';
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if ($ext === 'mp4') return '<span class="' . $class . ' ' . $class . '--empty">MP4</span>';
    return '<span class="' . $class . '"><img src="' . e(asset($path)) . '" alt="" loading="lazy"></span>';
}

function render_field($f, $v) {
    $k = $f['key'] ?? '';
    $type = $f['type'];
    $name = 'f[' . $k . ']';
    $help = !empty($f['help']) ? '<p class="help">' . hl($f['help']) . '</p>' : '';
    $req = !empty($f['required']) ? ' <em class="req">required</em>' : '';
    $label = '<div class="fl__label"><label>' . e($f['label']) . $req . '</label>' . $help . '</div>';

    switch ($type) {
        case 'heading':
            return '<h3 class="fh">' . e($f['label']) . '</h3>';
        case 'text':
        case 'slug':
            $w = $f['width'] ?? 'lg';
            return '<div class="fl">' . $label . '<div class="fl__in"><input class="in in--' . $w . '" type="text" name="' . $name . '" value="' . e($v) . '"' . ($type === 'slug' ? ' dir="ltr" pattern="[a-z0-9\-]*" placeholder="created automatically from the English name"' : '') . '></div></div>';
        case 'i18n':
        case 'i18n_area':
            $v = is_array($v) ? $v : ['en' => (string) $v, 'ar' => ''];
            $rows = (int) ($f['rows'] ?? 3);
            $mk = function ($l) use ($type, $name, $v, $rows) {
                $dir = $l === 'ar' ? 'rtl' : 'ltr';
                $val = $v[$l] ?? '';
                $in = $type === 'i18n'
                    ? '<input class="in" type="text" dir="' . $dir . '" lang="' . $l . '" name="' . $name . '[' . $l . ']" value="' . e($val) . '">'
                    : '<textarea class="in" dir="' . $dir . '" lang="' . $l . '" rows="' . $rows . '" name="' . $name . '[' . $l . ']" data-autogrow>' . e($val) . '</textarea>';
                return '<div class="lang-col lang-col--' . $l . '"><span class="lang-tag">' . ($l === 'en' ? 'English' : 'العربية') . '</span>' . $in . '</div>';
            };
            return '<div class="fl">' . $label . '<div class="fl__in two">' . $mk('en') . $mk('ar') . '</div></div>';
        case 'points':
            $en = []; $ar = [];
            foreach ((array) $v as $p) { $en[] = $p['en'] ?? ''; $ar[] = $p['ar'] ?? ''; }
            return '<div class="fl">' . $label . '<div class="fl__in two">'
                . '<div class="lang-col"><span class="lang-tag">English</span><textarea class="in" rows="6" name="' . $name . '[en]" data-autogrow>' . e(implode("\n", $en)) . '</textarea></div>'
                . '<div class="lang-col lang-col--ar"><span class="lang-tag">العربية</span><textarea class="in" rows="6" dir="rtl" name="' . $name . '[ar]" data-autogrow>' . e(implode("\n", $ar)) . '</textarea></div></div></div>';
        case 'checkbox':
            $on = $v === null ? !empty($f['default']) : (bool) $v;
            return '<div class="fl fl--check"><label class="switch"><input type="checkbox" name="' . $name . '" value="1"' . ($on ? ' checked' : '') . '><span></span>' . e($f['label']) . '</label>' . $help . '</div>';
        case 'select':
            $o = '';
            foreach (field_options($f) as $ok => $ol) $o .= '<option value="' . e($ok) . '"' . ((string) $ok === (string) $v ? ' selected' : '') . '>' . e($ol) . '</option>';
            return '<div class="fl">' . $label . '<div class="fl__in"><select class="in in--md" name="' . $name . '"><option value="">— choose —</option>' . $o . '</select></div></div>';
        case 'multi':
            $o = '';
            foreach (field_options($f) as $ok => $ol) $o .= '<label class="tick"><input type="checkbox" name="' . $name . '[]" value="' . e($ok) . '"' . (in_array($ok, (array) $v, true) ? ' checked' : '') . '> ' . e($ol) . '</label>';
            return '<div class="fl">' . $label . '<div class="fl__in ticks">' . $o . '</div></div>';
        case 'icon':
            $o = '';
            foreach (icon_names() as $ic) $o .= '<label class="icopt" title="' . e($ic) . '"><input type="radio" name="' . $name . '" value="' . e($ic) . '"' . ($ic === $v ? ' checked' : '') . '><span>' . icon($ic) . '</span></label>';
            return '<div class="fl">' . $label . '<div class="fl__in icons">' . $o . '</div></div>';
        case 'image':
        case 'file':
            $isImg = $type === 'image';
            $accept = $isImg ? 'image/*' . (!empty($f['svg']) ? ',.svg' : '') : ($f['accept'] ?? '*');
            return '<div class="fl">' . $label . '<div class="fl__in"><div class="imgf" data-imgf>'
                . '<div class="imgf__prev" data-prev>' . thumb_html($v, 'pv') . '</div>'
                . '<div class="imgf__ctl">'
                . '<input type="hidden" name="' . $name . '" value="' . e($v) . '" data-path>'
                . '<label class="btn btn--line btn--sm">' . icon('upload') . ' ' . ($v ? 'Replace' : 'Upload') . ($isImg ? ' photo' : ' file') . '<input type="file" name="u_' . e($k) . '" accept="' . e($accept) . '"' . ($isImg ? ' data-resize' : '') . ' hidden></label>'
                . ($v ? '<label class="tick tick--danger"><input type="checkbox" name="rm_' . e($k) . '" value="1"> Remove</label>' : '')
                . '<span class="imgf__name" data-name>' . e($v ? basename($v) : 'No file yet') . '</span>'
                . '</div></div></div></div>';
        case 'images':
            $items = '';
            foreach ((array) $v as $p) $items .= '<div class="mimg" draggable="true">' . thumb_html($p, 'pv') . '<input type="hidden" name="' . $name . '[]" value="' . e($p) . '"><button type="button" class="mimg__x" data-mimg-del title="Remove">' . icon('close') . '</button></div>';
            return '<div class="fl">' . $label . '<div class="fl__in"><div class="mimgs" data-mimgs>' . $items . '</div>'
                . '<label class="btn btn--line btn--sm mt-s">' . icon('plus') . ' Add photos<input type="file" name="u_' . e($k) . '[]" accept="image/*" multiple data-resize data-multi hidden></label> <span class="help-inline" data-name></span>'
                . '<p class="help">Drag photos to change their order. Click × to remove one.</p></div></div>';
    }
    return '';
}

/** Read submitted values for a set of fields. Returns [values, errors]. */
function parse_fields($fields, $old, $folder) {
    $in = $_POST['f'] ?? [];
    $out = $old;
    $errors = [];
    foreach ($fields as $f) {
        $k = $f['key'] ?? null;
        if (!$k || $f['type'] === 'heading') continue;
        $raw = $in[$k] ?? null;
        switch ($f['type']) {
            case 'text': $out[$k] = trim((string) $raw); break;
            case 'slug': $out[$k] = trim((string) $raw) === '' ? '' : slugify((string) $raw); break;
            case 'i18n':
                $out[$k] = ['en' => trim((string) ($raw['en'] ?? '')), 'ar' => trim((string) ($raw['ar'] ?? ''))]; break;
            case 'i18n_area':
                $out[$k] = ['en' => trim(str_replace("\r", '', (string) ($raw['en'] ?? ''))), 'ar' => trim(str_replace("\r", '', (string) ($raw['ar'] ?? '')))]; break;
            case 'points':
                $en = lines_of($raw['en'] ?? '');
                $ar = lines_of($raw['ar'] ?? '');
                $pts = [];
                for ($i = 0; $i < max(count($en), count($ar)); $i++) $pts[] = ['en' => $en[$i] ?? '', 'ar' => $ar[$i] ?? ''];
                $out[$k] = $pts; break;
            case 'checkbox': $out[$k] = !empty($raw); break;
            case 'select': $opts = field_options($f); $out[$k] = isset($opts[$raw]) ? (string) $raw : ''; break;
            case 'multi': $opts = field_options($f); $out[$k] = array_values(array_filter((array) $raw, function ($x) use ($opts) { return isset($opts[$x]); })); break;
            case 'icon': $out[$k] = in_array($raw, icon_names(), true) ? $raw : ($old[$k] ?? 'check'); break;
            case 'image':
            case 'file':
                $path = trim((string) $raw);
                if (!empty($_POST['rm_' . $k])) $path = '';
                foreach (files_list('u_' . $k) as $file) {
                    $r = store_upload($file, $f['type'] === 'image' ? 'images' : 'files', $f['exts'] ?? null, !empty($f['svg']));
                    if ($r === null) continue;
                    if (isset($r['error'])) $errors[] = $r['error']; else $path = $r['path'];
                }
                $out[$k] = $path; break;
            case 'images':
                $list = array_values(array_filter(array_map('trim', (array) $raw), 'strlen'));
                foreach (files_list('u_' . $k) as $file) {
                    $r = store_upload($file, 'images');
                    if ($r === null) continue;
                    if (isset($r['error'])) $errors[] = $r['error']; else $list[] = $r['path'];
                }
                $out[$k] = $list; break;
        }
        if (!empty($f['required'])) {
            $val = $out[$k] ?? '';
            $empty = is_array($val) ? trim((string) ($val['en'] ?? '')) === '' && !array_filter($val, function ($x) { return !is_array($x) && trim((string) $x) !== ''; }) : trim((string) $val) === '';
            if ($empty) $errors[] = '"' . $f['label'] . '" is required' . (in_array($f['type'], ['i18n', 'i18n_area']) ? ' (English at least)' : '') . '.';
        }
    }
    return [$out, $errors];
}

function lines_of($txt) {
    $out = [];
    foreach (explode("\n", str_replace("\r", '', (string) $txt)) as $line) { $line = trim($line); if ($line !== '') $out[] = $line; }
    return $out;
}

function item_label($item, $key) {
    $v = $item[$key] ?? '';
    $s = is_array($v) ? t($v, 'en') : (string) $v;
    return $s !== '' ? $s : '(untitled)';
}

function count_unread($name) { return count(array_filter(data_load($name, []), function ($m) { return empty($m['read']); })); }

function human_size($b) { return $b > 1048576 ? round($b / 1048576, 1) . ' MB' : round($b / 1024) . ' KB'; }
