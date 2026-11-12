<?php
/** Handles the Contact and Join-us forms: validates, stores in /data, emails the company. */

function form_handle_contact() {
    global $C;
    $f = ['name' => trim($_POST['name'] ?? ''), 'email' => trim($_POST['email'] ?? ''), 'phone' => trim($_POST['phone'] ?? ''),
          'subject' => trim($_POST['subject'] ?? ''), 'message' => trim($_POST['message'] ?? '')];
    $err = [];
    if (!empty($_POST['website'])) return ['ok' => true, 'fields' => []];            // spam bot (hidden field filled)
    if (!csrf_ok()) $err['form'] = true;
    if (mb_strlen($f['name']) < 2) $err['name'] = true;
    if (!filter_var($f['email'], FILTER_VALIDATE_EMAIL)) $err['email'] = true;
    if (mb_strlen($f['message']) < 5) $err['message'] = true;
    foreach ($f as $k => $v) $f[$k] = mb_substr($v, 0, $k === 'message' ? 5000 : 200);
    if ($err) return ['ok' => false, 'errors' => $err, 'fields' => $f];
    if (rate_limited('contact_' . client_ip(), 5, 3600)) return ['ok' => true, 'fields' => []];

    $msgs = data_load('messages', []);
    $item = $f + ['id' => new_id('m'), 'date' => date('Y-m-d H:i'), 'lang' => lang(), 'read' => false, 'ip' => client_ip()];
    array_unshift($msgs, $item);
    data_save('messages', $msgs);

    $to = $C['settings']['notify_email'] ?: $C['settings']['email'];
    $body = "New message from the website contact form\n\n" .
        "Name: {$f['name']}\nEmail: {$f['email']}\nPhone: {$f['phone']}\nSubject: {$f['subject']}\n\n{$f['message']}\n\n— Sent " . $item['date'];
    send_mail($to, 'Website message: ' . ($f['subject'] ?: $f['name']), $body, $f['email']);
    return ['ok' => true, 'fields' => []];
}

function form_handle_apply() {
    global $C;
    $f = ['name' => trim($_POST['name'] ?? ''), 'email' => trim($_POST['email'] ?? ''), 'phone' => trim($_POST['phone'] ?? ''),
          'position' => trim($_POST['position'] ?? ''), 'message' => trim($_POST['message'] ?? '')];
    $err = [];
    if (!empty($_POST['website'])) return ['ok' => true, 'fields' => []];
    if (!csrf_ok()) $err['form'] = true;
    if (mb_strlen($f['name']) < 2) $err['name'] = true;
    if (!filter_var($f['email'], FILTER_VALIDATE_EMAIL)) $err['email'] = true;
    if (mb_strlen($f['phone']) < 6) $err['phone'] = true;
    foreach ($f as $k => $v) $f[$k] = mb_substr($v, 0, $k === 'message' ? 5000 : 200);

    $cv = '';
    $up = $_FILES['cv'] ?? null;
    if ($up && $up['error'] !== UPLOAD_ERR_NO_FILE) {
        $ext = strtolower(pathinfo($up['name'], PATHINFO_EXTENSION));
        if ($up['error'] !== UPLOAD_ERR_OK || !in_array($ext, ['pdf', 'doc', 'docx'], true) || $up['size'] > 5 * 1024 * 1024) $err['cv'] = true;
    }
    if ($err) return ['ok' => false, 'errors' => $err, 'fields' => $f];
    if (rate_limited('apply_' . client_ip(), 5, 3600)) return ['ok' => true, 'fields' => []];

    if ($up && $up['error'] === UPLOAD_ERR_OK) {
        $dir = DATA_DIR . '/cv';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $cv = date('Ymd-His') . '-' . slugify($f['name']) . '.' . strtolower(pathinfo($up['name'], PATHINFO_EXTENSION));
        if (!move_uploaded_file($up['tmp_name'], $dir . '/' . $cv)) $cv = '';
    }
    $apps = data_load('applications', []);
    $item = $f + ['id' => new_id('a'), 'date' => date('Y-m-d H:i'), 'lang' => lang(), 'read' => false, 'cv' => $cv, 'ip' => client_ip()];
    array_unshift($apps, $item);
    data_save('applications', $apps);

    $to = $C['settings']['notify_email'] ?: $C['settings']['email'];
    $body = "New job application from the website\n\nName: {$f['name']}\nEmail: {$f['email']}\nPhone: {$f['phone']}\nPosition: {$f['position']}\n\n{$f['message']}\n\nCV: " . ($cv ? 'attached (also saved in the admin panel)' : 'none') . "\n— Sent " . $item['date'];
    send_mail($to, 'Job application: ' . $f['name'] . ($f['position'] ? ' – ' . $f['position'] : ''), $body, $f['email'],
        $cv ? [['path' => DATA_DIR . '/cv/' . $cv, 'name' => $cv]] : []);
    return ['ok' => true, 'fields' => []];
}

function field_val($r, $k) { return e($r['fields'][$k] ?? ''); }
function field_err($r, $k) { return !empty($r['errors'][$k]) ? ' is-err' : ''; }
