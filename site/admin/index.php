<?php
/** ETCO website – admin panel. Open /admin/ in your browser to manage the website. */
define('ROOT', dirname(__DIR__));
$b = rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME']))), '/');
define('BASE', $b === '/' || $b === '.' ? '' : $b);
define('ADMIN_DIR', basename(__DIR__)); // the admin folder can be renamed to anything
require ROOT . '/inc/core.php';
require __DIR__ . '/lib.php';
require __DIR__ . '/schema.php';
require __DIR__ . '/views.php';
set_lang('en');
start_session();
header('X-Frame-Options: SAMEORIGIN');
header('X-Robots-Tag: noindex, nofollow');

$s = preg_replace('/[^a-z_]/', '', $_GET['s'] ?? 'dashboard');
$a = preg_replace('/[^a-z_]/', '', $_GET['a'] ?? '');
$post = $_SERVER['REQUEST_METHOD'] === 'POST';

/* ---------- First run: create the first administrator ---------- */
if (!users()) {
    if ($post && csrf_ok()) {
        $u = trim($_POST['username'] ?? ''); $p1 = $_POST['password'] ?? ''; $p2 = $_POST['password2'] ?? '';
        $code = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $_POST['setup_code'] ?? ''));
        $real = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) ($CONFIG['setup_code'] ?? '')));
        if (rate_limited('setup_' . client_ip(), 10, 900)) $err = 'Too many attempts. Please wait 15 minutes.';
        elseif ($real === '' || !hash_equals($real, $code)) $err = 'The setup code is not correct. You find it in the file config.php.';
        elseif (!preg_match('/^[A-Za-z0-9._@-]{3,40}$/', $u)) $err = 'Username: 3–40 letters, numbers, dots or dashes.';
        elseif (strlen($p1) < 8) $err = 'Password must be at least 8 characters.';
        elseif ($p1 !== $p2) $err = 'The two passwords are different.';
        else {
            data_save('users', [['username' => $u, 'hash' => password_hash($p1, PASSWORD_DEFAULT), 'created' => date('Y-m-d H:i')]]);
            session_regenerate_id(true);
            $_SESSION['admin_user'] = $u; $_SESSION['admin_last'] = time();
            aflash('ok', 'Welcome! Your administrator account has been created.');
            go('dashboard');
        }
    }
    view_setup($err ?? '');
    exit;
}

/* ---------- Login / logout ---------- */
if ($s === 'login') {
    if ($post) {
        $u = trim($_POST['username'] ?? ''); $p = $_POST['password'] ?? '';
        if (!csrf_ok()) $err = 'Session expired, please try again.';
        elseif (rate_limited('login_' . client_ip(), 8, 900) || rate_limited('loginuser_' . strtolower($u), 10, 900)) $err = 'Too many attempts. For your security, sign-in is paused for 15 minutes.';
        else {
            $us = users();
            foreach ($us as $k => $usr) {
                if (strcasecmp($usr['username'], $u) === 0 && password_verify($p, $usr['hash'])) {
                    session_regenerate_id(true);
                    $_SESSION['admin_user'] = $usr['username']; $_SESSION['admin_last'] = time();
                    $_SESSION['prev_login'] = $usr['last_login'] ?? null;
                    $us[$k]['last_login'] = ['date' => date('Y-m-d H:i'), 'ip' => client_ip()];
                    data_save('users', $us);
                    go('dashboard');
                }
            }
            usleep(400000); // slow down password guessing
            $err = 'Wrong username or password.';
        }
    }
    view_login($err ?? (isset($_GET['expired']) ? 'You were logged out after a long time without activity.' : ''));
    exit;
}
if ($s === 'logout') { $_SESSION = []; session_destroy(); header('Location: ' . aurl('login')); exit; }

require_login();
$C = content();
$SECTIONS = admin_sections();

/* ---------- Content sections (single & list) ---------- */
if (isset($SECTIONS[$s])) {
    $sec = $SECTIONS[$s];
    $sec['id'] = $s;
    if ($sec['type'] === 'single') {
        $cur = get_path($C, $sec['path']);
        if ($post) {
            need_csrf();
            [$new, $errors] = parse_fields($sec['fields'], $cur, 'images');
            foreach ($errors as $er) aflash('err', $er);
            $r = &ref_path($C, $sec['path']); $r = $new; unset($r);
            if (save_content($C)) aflash('ok', 'Changes saved. They are already visible on the website.');
            go($s);
        }
        view_single($sec, $cur);
        exit;
    }

    // list
    $list = get_path($C, $sec['path']);
    $id = $_GET['id'] ?? $_POST['id'] ?? '';
    $findIdx = function ($id) use (&$list) { foreach ($list as $i => $it) if (($it['id'] ?? '') === $id) return $i; return null; };

    if ($post) {
        need_csrf();
        $i = $findIdx($id);
        if ($a === 'save') {
            $isNew = $i === null;
            $old = $isNew ? ['id' => new_id(substr($s, 0, 1)), 'visible' => true] : $list[$i];
            [$item, $errors] = parse_fields($sec['fields'], $old, 'images');
            foreach ($errors as $er) aflash('err', $er);
            $hasRequiredError = (bool) array_filter($errors, function ($x) { return strpos($x, 'required') !== false; });
            if ($hasRequiredError) {
                // not saved yet: keep what was typed so nothing is lost
                $_SESSION['draft'][$s . '|' . ($isNew ? 'new' : $item['id'])] = $item;
                go($s, ['a' => 'edit', 'id' => $isNew ? 'new' : $item['id']]);
            }
            if (!empty($sec['slug'])) {
                $base = $item['slug'] ?: slugify(t($item[$sec['name']] ?? '', 'en'));
                $slug = $base; $n = 2;
                while (array_filter($list, function ($o) use ($slug, $item) { return ($o['slug'] ?? '') === $slug && $o['id'] !== $item['id']; })) $slug = $base . '-' . $n++;
                $item['slug'] = $slug;
            }
            if ($isNew) { if (!empty($_POST['add_top'])) array_unshift($list, $item); else $list[] = $item; } else $list[$i] = $item;
            $r = &ref_path($C, $sec['path']); $r = array_values($list); unset($r);
            if (save_content($C)) aflash('ok', ucfirst($sec['item'] ?? 'item') . ' saved.');
            if (!empty($_POST['stay'])) go($s, ['a' => 'edit', 'id' => $item['id']]);
            go($s);
        }
        if ($a === 'delete' && $i !== null) {
            $name = item_label($list[$i], $sec['name']);
            array_splice($list, $i, 1);
            $r = &ref_path($C, $sec['path']); $r = array_values($list); unset($r);
            if (save_content($C)) aflash('ok', '"' . $name . '" deleted. (You can undo this from Settings → Backups.)');
            go($s);
        }
        if ($a === 'move' && $i !== null) {
            $j = $i + (($_POST['dir'] ?? '') === 'up' ? -1 : 1);
            if (isset($list[$j])) { $tmp = $list[$i]; $list[$i] = $list[$j]; $list[$j] = $tmp; }
            $r = &ref_path($C, $sec['path']); $r = array_values($list); unset($r);
            save_content($C);
            go($s, ['hl' => $id]);
        }
        if ($a === 'reorder') {
            $order = array_flip(explode(',', $_POST['order'] ?? ''));
            usort($list, function ($x, $y) use ($order) { return ($order[$x['id']] ?? 9999) <=> ($order[$y['id']] ?? 9999); });
            $r = &ref_path($C, $sec['path']); $r = array_values($list); unset($r);
            if (save_content($C)) aflash('ok', 'New order saved.');
            go($s);
        }
        if ($a === 'toggle' && $i !== null) {
            $k = $_POST['key'] ?? 'visible';
            if (in_array($k, array_merge(['visible'], array_keys($sec['toggles'] ?? [])), true)) {
                $list[$i][$k] = empty($list[$i][$k]);
                $r = &ref_path($C, $sec['path']); $r = array_values($list); unset($r);
                save_content($C);
            }
            go($s, ['hl' => $id]);
        }
        if ($a === 'bulk' && $s === 'gallery') {
            $added = 0;
            foreach (files_list('u_bulk') as $file) {
                $res = store_upload($file, 'gallery');
                if ($res === null) continue;
                if (isset($res['error'])) { aflash('err', $res['error']); continue; }
                $list[] = ['id' => new_id('g'), 'visible' => true, 'image' => $res['path'], 'category' => (string) ($_POST['category'] ?? ''), 'caption' => ['en' => '', 'ar' => '']];
                $added++;
            }
            $r = &ref_path($C, $sec['path']); $r = array_values($list); unset($r);
            if ($added && save_content($C)) aflash('ok', $added . ' photo' . ($added > 1 ? 's' : '') . ' added to the gallery.');
            go($s);
        }
        go($s);
    }

    if ($a === 'edit') {
        $i = $findIdx($id);
        $dk = $s . '|' . $id;
        if ($id === 'new') $item = ['visible' => true];
        elseif ($i === null) { aflash('err', 'That item no longer exists.'); go($s); }
        else $item = $list[$i];
        if (isset($_SESSION['draft'][$dk])) { $item = $_SESSION['draft'][$dk]; unset($_SESSION['draft'][$dk]); }
        view_item($sec, $item, $id === 'new');
        exit;
    }
    view_list($sec, $list);
    exit;
}

/* ---------- Other screens ---------- */
switch ($s) {
    case 'dashboard': view_dashboard(); break;

    case 'labels':
        if ($post) {
            need_csrf();
            foreach ((array) ($_POST['lb'] ?? []) as $k => $v) {
                if (!isset($C['labels'][$k])) continue;
                $C['labels'][$k] = ['en' => trim((string) ($v['en'] ?? '')), 'ar' => trim((string) ($v['ar'] ?? ''))];
            }
            if (save_content($C)) aflash('ok', 'Texts saved.');
            go('labels');
        }
        view_labels();
        break;

    case 'messages':
    case 'applications':
        $box = data_load($s, []);
        $id = $_GET['id'] ?? $_POST['id'] ?? '';
        if ($post) {
            need_csrf();
            foreach ($box as $k => $m) if ($m['id'] === $id) {
                if ($a === 'delete') {
                    if (!empty($m['cv'])) @unlink(DATA_DIR . '/cv/' . basename($m['cv']));
                    array_splice($box, $k, 1); aflash('ok', 'Deleted.');
                }
                if ($a === 'unread') { $box[$k]['read'] = false; aflash('ok', 'Marked as unread.'); }
                break;
            }
            data_save($s, array_values($box));
            go($s);
        }
        if ($a === 'cv') {
            foreach ($box as $m) if ($m['id'] === $id && !empty($m['cv'])) {
                $f = DATA_DIR . '/cv/' . basename($m['cv']);
                if (is_file($f)) {
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename="' . basename($f) . '"');
                    header('Content-Length: ' . filesize($f));
                    readfile($f); exit;
                }
            }
            aflash('err', 'CV file not found.'); go($s);
        }
        if ($a === 'view') {
            foreach ($box as $k => $m) if ($m['id'] === $id) {
                if (empty($m['read'])) { $box[$k]['read'] = true; data_save($s, $box); }
                view_message($s, $m); exit;
            }
            go($s);
        }
        view_inbox($s, $box);
        break;

    case 'account':
        $us = users();
        if ($post) {
            need_csrf();
            $me = current_user();
            if ($a === 'password') {
                foreach ($us as $k => $u) if ($u['username'] === $me) {
                    if (!password_verify($_POST['current'] ?? '', $u['hash'])) aflash('err', 'Your current password is not correct.');
                    elseif (strlen($_POST['new'] ?? '') < 8) aflash('err', 'The new password must be at least 8 characters.');
                    elseif (($_POST['new'] ?? '') !== ($_POST['new2'] ?? '')) aflash('err', 'The two new passwords are different.');
                    else { $us[$k]['hash'] = password_hash($_POST['new'], PASSWORD_DEFAULT); data_save('users', $us); aflash('ok', 'Password changed.'); }
                }
            }
            if ($a === 'add') {
                $u = trim($_POST['username'] ?? '');
                if (!preg_match('/^[A-Za-z0-9._@-]{3,40}$/', $u)) aflash('err', 'Username: 3–40 letters, numbers, dots or dashes.');
                elseif (find_by($us, 'username', $u)) aflash('err', 'This username already exists.');
                elseif (strlen($_POST['password'] ?? '') < 8) aflash('err', 'Password must be at least 8 characters.');
                else { $us[] = ['username' => $u, 'hash' => password_hash($_POST['password'], PASSWORD_DEFAULT), 'created' => date('Y-m-d H:i')]; data_save('users', $us); aflash('ok', 'User "' . $u . '" added.'); }
            }
            if ($a === 'remove') {
                $u = $_POST['username'] ?? '';
                if ($u === $me) aflash('err', 'You cannot delete your own account.');
                else { $us = array_values(array_filter($us, function ($x) use ($u) { return $x['username'] !== $u; })); data_save('users', $us); aflash('ok', 'User removed.'); }
            }
            go('account');
        }
        view_account($us);
        break;

    case 'backups':
        $dir = DATA_DIR . '/backups';
        $files = is_dir($dir) ? glob($dir . '/content-*.json') : [];
        rsort($files);
        if ($post) {
            need_csrf();
            $f = $dir . '/' . basename($_POST['file'] ?? '');
            if ($a === 'restore' && is_file($f) && is_array(json_decode(file_get_contents($f), true))) {
                data_save('content', json_decode(file_get_contents($f), true), true);
                aflash('ok', 'The website content was restored to the version of ' . backup_date($f) . '. (The version you replaced was kept as a backup too.)');
            }
            if ($a === 'create') { data_save('content', $C, true); aflash('ok', 'Backup created.'); }
            go('backups');
        }
        if ($a === 'download') {
            $f = ($_GET['file'] ?? '') === 'current' ? data_path('content') : $dir . '/' . basename($_GET['file'] ?? '');
            if (is_file($f)) { header('Content-Type: application/json; charset=utf-8'); header('Content-Disposition: attachment; filename="etco-' . basename($f) . '"'); readfile($f); exit; }
            go('backups');
        }
        view_backups($files);
        break;

    case 'tools':
        if ($post && $a === 'import') {
            need_csrf();
            [$copied, $missing] = import_legacy_images($C);
            if ($copied) { save_content($C); aflash('ok', $copied . ' photos were copied into the "uploads" folder. The old "cms" and "images" folders are no longer needed by the website.'); }
            if ($missing) aflash('err', count($missing) . ' photos could not be found on the server: ' . implode(', ', array_slice($missing, 0, 5)) . (count($missing) > 5 ? '…' : ''));
            if (!$copied && !$missing) aflash('ok', 'All photos are already in the "uploads" folder. Nothing to do.');
            go('tools');
        }
        if ($post && $a === 'testmail') {
            need_csrf();
            $to = $C['settings']['notify_email'] ?: $C['settings']['email'];
            $ok = send_mail($to, 'Test email from your website', "This is a test email sent from the ETCO website admin panel on " . date('Y-m-d H:i') . ".\n\nIf you received it, the contact form emails work.");
            aflash($ok ? 'ok' : 'err', $ok ? "Test email sent to $to. Check the inbox (and the spam folder)." : 'The server refused to send the email. Ask your hosting provider to enable PHP mail(). Messages are still saved in the admin panel.');
            go('tools');
        }
        view_tools();
        break;

    default:
        go('dashboard');
}

function backup_date($f) {
    if (preg_match('/(\d{4}-\d{2}-\d{2})_(\d{2})-(\d{2})-(\d{2})/', basename($f), $m)) return $m[1] . ' ' . $m[2] . ':' . $m[3];
    return basename($f);
}

/** Copy photos that still live in the old website folders into /uploads/legacy and update the content. */
function import_legacy_images(&$C) {
    $copied = 0; $missing = [];
    $walk = function (&$node) use (&$walk, &$copied, &$missing) {
        if (is_array($node)) { foreach ($node as &$v) $walk($v); unset($v); return; }
        if (!is_string($node) || !preg_match('~^(cms|images)/.+\.(jpe?g|png|webp|gif|svg)$~i', $node)) return;
        $src = ROOT . '/' . $node;
        $rel = 'uploads/legacy/' . preg_replace('~[^A-Za-z0-9._/-]+~', '-', $node);
        $dst = ROOT . '/' . $rel;
        if (!is_file($src)) { if (is_file($dst)) { $node = $rel; } else $missing[] = $node; return; }
        if (!is_dir(dirname($dst))) @mkdir(dirname($dst), 0755, true);
        if (is_file($dst) || @copy($src, $dst)) { $node = $rel; $copied++; } else $missing[] = $node;
    };
    $walk($C);
    $missing = array_values(array_unique($missing));
    return [$copied, $missing];
}
