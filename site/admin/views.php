<?php
/** Admin panel screens (HTML). */

function admin_head($title, $active = '') {
    $menu = admin_menu();
    $um = count_unread('messages'); $ua = count_unread('applications');
    $C = content();
    ?><!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title) ?> · ETCO admin</title>
<link rel="icon" href="<?= e(asset_v('assets/img/favicon.svg')) ?>" type="image/svg+xml">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Cairo:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(asset_v(ADMIN_DIR . '/admin.css')) ?>">
</head><body>
<aside class="side" id="side">
  <a class="side__brand" href="<?= e(aurl()) ?>"><?php $sl = ($C['settings']['logo_light'] ?? '') ?: ($C['settings']['logo'] ?? ''); if ($sl): ?><img src="<?= e(asset($sl)) ?>" alt="ETCO"><?php endif; ?><span>Admin</span></a>
  <nav>
    <?php foreach ($menu as $group => $items): ?>
      <p class="side__group"><?= e($group) ?></p>
      <?php foreach ($items as [$key, $label, $ic]):
        $badge = $key === 'messages' ? $um : ($key === 'applications' ? $ua : 0); ?>
        <a class="side__link<?= $active === $key ? ' is-on' : '' ?>" href="<?= e(aurl($key)) ?>"><?= icon($ic) ?><span><?= e($label) ?></span><?php if ($badge): ?><b class="badge"><?= $badge ?></b><?php endif; ?></a>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </nav>
</aside>
<div class="main">
  <header class="top">
    <button class="top__burger" type="button" onclick="document.body.classList.toggle('side-open')" aria-label="Menu"><?= icon('menu') ?></button>
    <div class="top__view">
      <a class="btn btn--line btn--sm" href="<?= e(url('home', '', 'en')) ?>" target="_blank"><?= icon('globe') ?> View website</a>
      <a class="btn btn--line btn--sm" href="<?= e(url('home', '', 'ar')) ?>" target="_blank" lang="ar">الموقع بالعربية</a>
    </div>
    <div class="top__user"><?= icon('satisfaction') ?> <?= e(current_user()) ?> <a href="<?= e(aurl('logout')) ?>">Log out</a></div>
  </header>
  <div class="content">
  <?php foreach (aflash_take() as [$t, $m]): ?><div class="flash flash--<?= e($t) ?>"><?= icon($t === 'ok' ? 'check' : 'close') ?><span><?= e($m) ?></span></div><?php endforeach; ?>
<?php
}

function admin_foot() { ?>
  </div>
</div>
<div class="side-shade" onclick="document.body.classList.remove('side-open')"></div>
<script src="<?= e(asset_v(ADMIN_DIR . '/admin.js')) ?>"></script>
</body></html>
<?php }

function page_head($title, $intro = '', $actions = '') { ?>
  <div class="ph">
    <div><h1><?= e($title) ?></h1><?php if ($intro): ?><p class="ph__intro"><?= e($intro) ?></p><?php endif; ?></div>
    <?php if ($actions): ?><div class="ph__act"><?= $actions ?></div><?php endif; ?>
  </div>
<?php }

function section_tabs($sec) {
    if (empty($sec['group'])) return;
    $tabs = array_filter(admin_sections(), function ($x) use ($sec) { return ($x['group'] ?? '') === $sec['group']; });
    if (count($tabs) < 2) return;
    echo '<div class="tabs">';
    foreach ($tabs as $k => $t) echo '<a class="tab' . ($k === $sec['id'] ? ' is-on' : '') . '" href="' . e(aurl($k)) . '">' . e($t['tab'] ?? $t['title']) . '</a>';
    echo '</div>';
}

function view_link($sec, $item = null) {
    if (empty($sec['view'])) return '';
    $slug = $item['slug'] ?? '';
    return '<a class="btn btn--line btn--sm" target="_blank" href="' . e(url($sec['view'], $slug, 'en')) . '">' . icon('globe') . ' View on website</a>';
}

/* ---------- Login / setup ---------- */
function auth_page($title, $body) { ?><!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex">
<title><?= e($title) ?> · ETCO admin</title><link rel="icon" href="<?= e(asset_v('assets/img/favicon.svg')) ?>" type="image/svg+xml">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(asset_v(ADMIN_DIR . '/admin.css')) ?>"></head>
<body class="auth"><div class="auth__box"><?php $C = content(); if (!empty($C['settings']['logo'])): ?><img class="auth__logo" src="<?= e(asset($C['settings']['logo'])) ?>" alt="ETCO"><?php endif; ?><?= $body ?></div></body></html>
<?php }

function view_login($err) {
    ob_start(); ?>
    <h1>Sign in to manage your website</h1>
    <?php if ($err): ?><div class="flash flash--err"><?= e($err) ?></div><?php endif; ?>
    <form method="post" action="<?= e(aurl('login')) ?>" class="stack"><?= csrf_field() ?>
      <label class="lbl">Username<input class="in" name="username" autocomplete="username" required autofocus></label>
      <label class="lbl">Password<input class="in" type="password" name="password" autocomplete="current-password" required></label>
      <button class="btn btn--primary btn--block">Sign in</button>
    </form>
    <p class="auth__foot"><a href="<?= e(url('home', '', 'en')) ?>">← Back to the website</a></p>
    <?php auth_page('Sign in', ob_get_clean());
}

function view_setup($err) {
    ob_start(); ?>
    <h1>Welcome to your new admin panel</h1>
    <p>Create your private administrator account. Only someone with the setup code can do this, and it can only be done once.</p>
    <?php if ($err): ?><div class="flash flash--err"><?= e($err) ?></div><?php endif; ?>
    <form method="post" class="stack"><?= csrf_field() ?>
      <label class="lbl">Setup code <small>(in the file config.php on your server)</small><input class="in" name="setup_code" required autofocus autocomplete="off" placeholder="XXXX-XXXX-XXXX" style="letter-spacing:.1em;text-transform:uppercase"></label>
      <label class="lbl">Choose a username<input class="in" name="username" value="<?= e($_POST['username'] ?? '') ?>" required></label>
      <label class="lbl">Choose a password <small>(at least 8 characters)</small><input class="in" type="password" name="password" required autocomplete="new-password"></label>
      <label class="lbl">Type the password again<input class="in" type="password" name="password2" required autocomplete="new-password"></label>
      <button class="btn btn--primary btn--block">Create my account</button>
    </form>
    <?php auth_page('Setup', ob_get_clean());
}

/* ---------- Dashboard ---------- */
function view_dashboard() {
    $C = content();
    $msgs = data_load('messages', []); $apps = data_load('applications', []);
    admin_head('Dashboard', 'dashboard');
    page_head('Hello ' . current_user() . ' 👋', 'Everything on your website can be changed from here. Changes appear on the website as soon as you click Save.');
    $prev = $_SESSION['prev_login'] ?? null;
    if ($prev): ?><p class="lastlogin"><?= icon('integrity') ?> Your previous sign-in: <b><?= e($prev['date']) ?></b> from IP <?= e($prev['ip']) ?>. If this wasn't you, change your password in “My account”.</p><?php endif;
    $cards = [
        ['projects', 'Projects', count($C['projects']), 'structural'], ['sectors', 'Sectors', count($C['sectors']), 'mep'],
        ['gallery', 'Gallery photos', count($C['gallery']), 'image'], ['messages', 'Unread messages', count_unread('messages'), 'mail'],
        ['applications', 'New applications', count_unread('applications'), 'expertise'],
    ]; ?>
    <div class="kpis">
      <?php foreach ($cards as [$k, $l, $n, $ic]): ?><a class="kpi" href="<?= e(aurl($k)) ?>"><span class="kpi__ic"><?= icon($ic) ?></span><b><?= (int) $n ?></b><span><?= e($l) ?></span></a><?php endforeach; ?>
    </div>
    <div class="cols">
      <div class="card">
        <h2>Quick actions</h2>
        <div class="qa">
          <a href="<?= e(aurl('projects', ['a' => 'edit', 'id' => 'new'])) ?>"><?= icon('plus') ?><b>Add a project</b><span>New project page with photos</span></a>
          <a href="<?= e(aurl('gallery')) ?>"><?= icon('upload') ?><b>Upload photos</b><span>Add photos to the gallery</span></a>
          <a href="<?= e(aurl('slides')) ?>"><?= icon('image') ?><b>Change banner photos</b><span>Home page slideshow</span></a>
          <a href="<?= e(aurl('settings')) ?>"><?= icon('phone') ?><b>Phone, email & address</b><span>Contact details everywhere</span></a>
          <a href="<?= e(aurl('positions', ['a' => 'edit', 'id' => 'new'])) ?>"><?= icon('expertise') ?><b>Post a job</b><span>Appears on "Join us"</span></a>
          <a href="<?= e(aurl('labels')) ?>"><?= icon('tag') ?><b>Menu & button texts</b><span>Header, buttons, forms</span></a>
        </div>
      </div>
      <div class="card">
        <h2>Latest messages</h2>
        <?php if (!$msgs && !$apps): ?><p class="muted">No messages yet. Messages sent from the Contact and Join us forms will appear here.</p><?php endif; ?>
        <ul class="mini">
          <?php foreach (array_slice($msgs, 0, 5) as $m): ?><li class="<?= empty($m['read']) ? 'is-new' : '' ?>"><a href="<?= e(aurl('messages', ['a' => 'view', 'id' => $m['id']])) ?>"><b><?= e($m['name']) ?></b><span><?= e(mb_strimwidth($m['subject'] ?: $m['message'], 0, 60, '…')) ?></span><time><?= e($m['date']) ?></time></a></li><?php endforeach; ?>
          <?php foreach (array_slice($apps, 0, 3) as $m): ?><li class="<?= empty($m['read']) ? 'is-new' : '' ?>"><a href="<?= e(aurl('applications', ['a' => 'view', 'id' => $m['id']])) ?>"><b><?= e($m['name']) ?></b><span>Job application · <?= e($m['position']) ?></span><time><?= e($m['date']) ?></time></a></li><?php endforeach; ?>
        </ul>
      </div>
    </div>
    <div class="card help-card">
      <h2>How it works</h2>
      <div class="howto">
        <div><b>1. Pick a page</b><p>Use the menu on the left. Each website page has its own section, with tabs for its parts.</p></div>
        <div><b>2. Edit English & Arabic side by side</b><p>Every text has an English box and an Arabic box. If the Arabic box is empty, the English text is shown.</p></div>
        <div><b>3. Click Save</b><p>Your change is online immediately. A backup is made automatically every time, so you can always go back.</p></div>
      </div>
    </div>
    <?php admin_foot();
}

/* ---------- Single (one set of fields) ---------- */
function view_single($sec, $cur) {
    admin_head($sec['title'], admin_menu_key($sec['id']));
    page_head($sec['title'], $sec['intro'] ?? '', view_link($sec));
    section_tabs($sec); ?>
    <form method="post" enctype="multipart/form-data" class="card form" data-dirty>
      <?= csrf_field() ?>
      <?php foreach ($sec['fields'] as $f) echo render_field($f, isset($f['key']) ? ($cur[$f['key']] ?? null) : null); ?>
      <div class="savebar"><button class="btn btn--primary"><?= icon('check') ?> Save changes</button><span class="savebar__hint">Changes go live as soon as you save.</span></div>
    </form>
    <?php admin_foot();
}

/* ---------- List ---------- */
function view_list($sec, $list) {
    $C = content();
    $item = $sec['item'] ?? 'item';
    $add = '<a class="btn btn--primary" href="' . e(aurl($sec['id'], ['a' => 'edit', 'id' => 'new'])) . '">' . icon('plus') . ' Add ' . e($item) . '</a>';
    admin_head($sec['title'], admin_menu_key($sec['id']));
    page_head($sec['title'], $sec['intro'] ?? '', view_link($sec) . $add);
    section_tabs($sec);
    $hl = $_GET['hl'] ?? '';
    $catLabel = function ($k, $v) use ($C, $sec) {
        if ($k === 'category') { $src = $sec['id'] === 'gallery' ? 'gallery_categories' : 'categories'; $c = find_by($C[$src], 'id', $v); return $c ? t($c['name'], 'en') : '—'; }
        if ($k === 'year') return $v === 'ongoing' ? 'Ongoing' : ($v ?: '—');
        return is_array($v) ? t($v, 'en') : (string) $v;
    };

    if (!empty($sec['grid'])): ?>
      <form method="post" enctype="multipart/form-data" action="<?= e(aurl($sec['id'], ['a' => 'bulk'])) ?>" class="card bulk">
        <?= csrf_field() ?>
        <div><h2><?= icon('upload') ?> Add photos</h2><p class="muted">Select one or many photos at once. Big photos are reduced automatically.</p></div>
        <div class="bulk__row">
          <select class="in in--md" name="category"><?php foreach ($C['gallery_categories'] as $gc): ?><option value="<?= e($gc['id']) ?>"><?= e(t($gc['name'], 'en')) ?></option><?php endforeach; ?></select>
          <label class="btn btn--line"><?= icon('image') ?> Choose photos<input type="file" name="u_bulk[]" accept="image/*" multiple data-resize data-multi hidden></label>
          <span class="help-inline" data-name></span>
          <button class="btn btn--primary"><?= icon('upload') ?> Upload</button>
        </div>
      </form>
      <div class="filterbar"><input class="in in--md" type="search" placeholder="Search…" data-search=".gtile"> <span class="muted">Drag photos to change their order.</span></div>
      <div class="gtiles" data-sortable data-sort-url="<?= e(aurl($sec['id'], ['a' => 'reorder'])) ?>" data-csrf="<?= e(csrf_token()) ?>">
        <?php foreach ($list as $it): ?>
        <div class="gtile<?= empty($it['visible']) ? ' is-off' : '' ?>" data-id="<?= e($it['id']) ?>" draggable="true" data-text="<?= e(item_label($it, 'caption') . ' ' . $catLabel('category', $it['category'] ?? '')) ?>">
          <a href="<?= e(aurl($sec['id'], ['a' => 'edit', 'id' => $it['id']])) ?>"><?= thumb_html($it['image'] ?? '', 'gt') ?></a>
          <div class="gtile__bar">
            <span><?= e($catLabel('category', $it['category'] ?? '')) ?></span>
            <?= toggle_btn($sec, $it, 'visible') ?>
            <a class="ib" href="<?= e(aurl($sec['id'], ['a' => 'edit', 'id' => $it['id']])) ?>" title="Edit"><?= icon('wrench') ?></a>
            <?= delete_btn($sec, $it) ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="card card--flush">
        <?php if (count($list) > 8): ?><div class="filterbar filterbar--in"><input class="in in--md" type="search" placeholder="Search…" data-search="tbody tr"></div><?php endif; ?>
        <?php if (!$list): ?><p class="empty">Nothing here yet. Click “Add <?= e($item) ?>” to create the first one.</p><?php else: ?>
        <table class="tbl">
          <thead><tr><th class="w-ord">Order</th><?php if (!empty($sec['thumb'])): ?><th class="w-th"></th><?php endif; ?><th>Name</th>
            <?php foreach ($sec['cols'] ?? [] as $ck => $cl): ?><th><?= e($cl) ?></th><?php endforeach; ?>
            <th class="w-t">On website</th><?php foreach ($sec['toggles'] ?? [] as $tk => $tl): ?><th class="w-t"><?= e($tl) ?></th><?php endforeach; ?><th class="w-act"></th></tr></thead>
          <tbody>
          <?php foreach ($list as $n => $it): $edit = aurl($sec['id'], ['a' => 'edit', 'id' => $it['id']]); ?>
            <tr class="<?= empty($it['visible']) ? 'is-off' : '' ?><?= $hl === $it['id'] ? ' is-hl' : '' ?>">
              <td class="w-ord"><?= move_btns($sec, $it, $n, count($list)) ?></td>
              <?php if (!empty($sec['thumb'])): ?><td class="w-th"><a href="<?= e($edit) ?>"><?= thumb_html($it[$sec['thumb']] ?? '') ?></a></td><?php endif; ?>
              <td><a class="tbl__name" href="<?= e($edit) ?>"><?php if (!empty($sec['iconcol'])): ?><span class="tbl__ic"><?= icon($it['icon'] ?? 'check') ?></span><?php endif; ?><?= e(item_label($it, $sec['name'])) ?></a>
                <?php if (!empty($sec['sub']) && ($sub = $catLabel($sec['sub'], $it[$sec['sub']] ?? ''))): ?><small class="tbl__sub"><?= e(mb_strimwidth($sub, 0, 90, '…')) ?></small><?php endif; ?></td>
              <?php foreach ($sec['cols'] ?? [] as $ck => $cl): ?><td><?= e($catLabel($ck, $it[$ck] ?? '')) ?></td><?php endforeach; ?>
              <td class="w-t"><?= toggle_btn($sec, $it, 'visible') ?></td>
              <?php foreach ($sec['toggles'] ?? [] as $tk => $tl): ?><td class="w-t"><?= toggle_btn($sec, $it, $tk) ?></td><?php endforeach; ?>
              <td class="w-act"><a class="btn btn--line btn--sm" href="<?= e($edit) ?>">Edit</a><?= delete_btn($sec, $it) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>
    <?php endif;
    admin_foot();
}

function mini_form($sec, $action, $id, $inner, $extra = '') {
    return '<form method="post" action="' . e(aurl($sec['id'], ['a' => $action])) . '" class="inl"' . $extra . '>' . csrf_field() . '<input type="hidden" name="id" value="' . e($id) . '">' . $inner . '</form>';
}
function toggle_btn($sec, $it, $key) {
    $on = $key === 'visible' ? !isset($it['visible']) || $it['visible'] : !empty($it[$key]);
    $title = $key === 'visible' ? ($on ? 'Shown on website – click to hide' : 'Hidden – click to show') : ($on ? 'Yes – click to turn off' : 'No – click to turn on');
    return mini_form($sec, 'toggle', $it['id'], '<input type="hidden" name="key" value="' . e($key) . '"><button class="pill' . ($on ? ' pill--on' : '') . '" title="' . e($title) . '">' . ($on ? 'Yes' : 'No') . '</button>');
}
function delete_btn($sec, $it) {
    return mini_form($sec, 'delete', $it['id'], '<button class="ib ib--danger" title="Delete">' . icon('close') . '</button>', ' data-confirm="Delete &quot;' . e(item_label($it, $sec['name'])) . '&quot;?"');
}
function move_btns($sec, $it, $n, $total) {
    $up = $n > 0 ? mini_form($sec, 'move', $it['id'], '<input type="hidden" name="dir" value="up"><button class="ib" title="Move up">↑</button>') : '<span class="ib ib--dis">↑</span>';
    $dn = $n < $total - 1 ? mini_form($sec, 'move', $it['id'], '<input type="hidden" name="dir" value="down"><button class="ib" title="Move down">↓</button>') : '<span class="ib ib--dis">↓</span>';
    return '<span class="ord">' . $up . $dn . '</span>';
}

/* ---------- Edit one item ---------- */
function view_item($sec, $item, $isNew) {
    $item_name = $sec['item'] ?? 'item';
    $title = $isNew ? 'New ' . $item_name : item_label($item, $sec['name']);
    admin_head($title, admin_menu_key($sec['id']));
    ?>
    <a class="back" href="<?= e(aurl($sec['id'])) ?>">← <?= e($sec['title']) ?></a>
    <?php page_head($title, '', $isNew ? '' : view_link($sec, $item)); ?>
    <form method="post" enctype="multipart/form-data" action="<?= e(aurl($sec['id'], ['a' => 'save'])) ?>" class="card form" data-dirty>
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= e($isNew ? 'new' : $item['id']) ?>">
      <?php foreach ($sec['fields'] as $f) echo render_field($f, isset($f['key']) ? ($item[$f['key']] ?? null) : null); ?>
      <div class="savebar">
        <button class="btn btn--primary"><?= icon('check') ?> Save</button>
        <button class="btn btn--line" name="stay" value="1">Save and keep editing</button>
        <?php if ($isNew && in_array($sec['id'], ['projects', 'positions'], true)): ?><label class="tick"><input type="checkbox" name="add_top" value="1" checked> Put it first in the list</label><?php endif; ?>
        <a class="btn btn--ghost" href="<?= e(aurl($sec['id'])) ?>">Cancel</a>
      </div>
    </form>
    <?php admin_foot();
}

/* ---------- Labels ---------- */
function view_labels() {
    $C = content();
    $groups = ['Menu' => '/^nav_|^all_|^menu|^close/', 'Buttons & links' => '/^(learn_more|view_project|discover|call_us|email_us|send_email|apply$|back_|prev_|next_|get_dir)/', 'Forms' => '/^form_|^apply_|^spontaneous/', 'Other texts' => '/./'];
    $done = [];
    admin_head('Menu & button texts', 'labels');
    page_head('Menu & button texts', 'The small texts used across the website: menu items, buttons, form labels and titles. Change any of them in English and Arabic.'); ?>
    <form method="post" class="card form" data-dirty>
      <?= csrf_field() ?>
      <div class="filterbar"><input class="in in--md" type="search" placeholder="Search a text…" data-search=".lbrow"></div>
      <?php foreach ($groups as $g => $re): ?>
        <h3 class="fh"><?= e($g) ?></h3>
        <?php foreach ($C['labels'] as $k => $v): if (isset($done[$k]) || !preg_match($re, $k)) continue; $done[$k] = 1; ?>
          <div class="lbrow" data-text="<?= e($k . ' ' . ($v['en'] ?? '') . ' ' . ($v['ar'] ?? '')) ?>">
            <div class="two">
              <div class="lang-col"><input class="in" name="lb[<?= e($k) ?>][en]" value="<?= e($v['en'] ?? '') ?>"></div>
              <div class="lang-col lang-col--ar"><input class="in" dir="rtl" name="lb[<?= e($k) ?>][ar]" value="<?= e($v['ar'] ?? '') ?>"></div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endforeach; ?>
      <div class="savebar"><button class="btn btn--primary"><?= icon('check') ?> Save changes</button><span class="savebar__hint">Tip: *stars* around a word make it blue in titles.</span></div>
    </form>
    <?php admin_foot();
}

/* ---------- Messages ---------- */
function view_inbox($box, $items) {
    $title = $box === 'messages' ? 'Messages' : 'Job applications';
    admin_head($title, $box);
    page_head($title, $box === 'messages' ? 'Messages sent from the Contact us form. A copy is also emailed to you.' : 'Applications sent from the Join us page, with CVs. A copy is also emailed to you.'); ?>
    <div class="card card--flush">
      <?php if (!$items): ?><p class="empty">No <?= $box === 'messages' ? 'messages' : 'applications' ?> yet.</p><?php else: ?>
      <table class="tbl tbl--inbox">
        <thead><tr><th></th><th>From</th><th><?= $box === 'messages' ? 'Subject / message' : 'Position' ?></th><th>Date</th><th class="w-act"></th></tr></thead>
        <tbody>
        <?php foreach ($items as $m): $v = aurl($box, ['a' => 'view', 'id' => $m['id']]); ?>
          <tr class="<?= empty($m['read']) ? 'is-new' : '' ?>">
            <td class="w-dot"><span class="ndot"></span></td>
            <td><a class="tbl__name" href="<?= e($v) ?>"><?= e($m['name']) ?></a><small class="tbl__sub"><?= e($m['email']) ?></small></td>
            <td><a href="<?= e($v) ?>"><?= e(mb_strimwidth($box === 'messages' ? ($m['subject'] ? $m['subject'] . ' — ' : '') . $m['message'] : $m['position'], 0, 90, '…')) ?></a></td>
            <td class="nowrap"><?= e($m['date']) ?></td>
            <td class="w-act"><a class="btn btn--line btn--sm" href="<?= e($v) ?>">Open</a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
    <?php admin_foot();
}

function view_message($box, $m) {
    $sec = ['id' => $box];
    admin_head($m['name'], $box); ?>
    <a class="back" href="<?= e(aurl($box)) ?>">← <?= $box === 'messages' ? 'Messages' : 'Job applications' ?></a>
    <div class="card msg">
      <div class="msg__head">
        <div><h1><?= e($m['name']) ?></h1><p class="muted"><?= e($m['date']) ?> · sent from the <?= $m['lang'] === 'ar' ? 'Arabic' : 'English' ?> website</p></div>
        <div class="msg__act">
          <a class="btn btn--primary" href="mailto:<?= e($m['email']) ?>?subject=<?= rawurlencode('Re: ' . ($m['subject'] ?? $m['position'] ?? 'Your message')) ?>"><?= icon('mail') ?> Reply by email</a>
          <?php if (!empty($m['phone'])): ?><a class="btn btn--line" href="tel:<?= e(preg_replace('/[^0-9+]/', '', $m['phone'])) ?>"><?= icon('phone') ?> Call</a><?php endif; ?>
          <?php if (!empty($m['cv'])): ?><a class="btn btn--line" href="<?= e(aurl($box, ['a' => 'cv', 'id' => $m['id']])) ?>"><?= icon('upload') ?> Download CV</a><?php endif; ?>
        </div>
      </div>
      <dl class="msg__meta">
        <div><dt>Email</dt><dd><a href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a></dd></div>
        <div><dt>Phone</dt><dd><?= e($m['phone'] ?: '—') ?></dd></div>
        <?php if ($box === 'messages'): ?><div><dt>Subject</dt><dd><?= e($m['subject'] ?: '—') ?></dd></div><?php else: ?><div><dt>Position</dt><dd><?= e($m['position'] ?: '—') ?></dd></div><?php endif; ?>
      </dl>
      <div class="msg__body" dir="auto"><?= nl2br(e($m['message'] ?: '(no message)')) ?></div>
      <div class="msg__foot">
        <?= mini_form($sec, 'unread', $m['id'], '<button class="btn btn--line btn--sm">Mark as unread</button>') ?>
        <?= mini_form($sec, 'delete', $m['id'], '<button class="btn btn--danger btn--sm">' . icon('close') . ' Delete</button>', ' data-confirm="Delete this ' . ($box === 'messages' ? 'message' : 'application') . '?"') ?>
      </div>
    </div>
    <?php admin_foot();
}

/* ---------- Account ---------- */
function view_account($us) {
    $sec = ['id' => 'account'];
    admin_head('My account & users', 'account');
    page_head('My account & users'); ?>
    <div class="cols">
      <form method="post" action="<?= e(aurl('account', ['a' => 'password'])) ?>" class="card stack"><?= csrf_field() ?>
        <h2>Change my password</h2>
        <label class="lbl">Current password<input class="in" type="password" name="current" required autocomplete="current-password"></label>
        <label class="lbl">New password <small>(at least 8 characters)</small><input class="in" type="password" name="new" required autocomplete="new-password"></label>
        <label class="lbl">New password again<input class="in" type="password" name="new2" required autocomplete="new-password"></label>
        <button class="btn btn--primary">Change password</button>
      </form>
      <div class="card">
        <h2>People who can sign in</h2>
        <p class="muted">Only the people listed here can open the admin panel. Remove anyone who should not have access.</p>
        <table class="tbl"><tbody>
          <?php foreach ($us as $u): ?><tr><td><b><?= e($u['username']) ?></b><?= $u['username'] === current_user() ? ' <span class="muted">(you)</span>' : '' ?><small class="tbl__sub">since <?= e($u['created'] ?? '') ?><?= !empty($u['last_login']) ? ' · last sign-in ' . e($u['last_login']['date']) : '' ?></small></td>
            <td class="w-act"><?php if ($u['username'] !== current_user()): ?><form method="post" action="<?= e(aurl('account', ['a' => 'remove'])) ?>" class="inl" data-confirm="Remove <?= e($u['username']) ?>?"><?= csrf_field() ?><input type="hidden" name="username" value="<?= e($u['username']) ?>"><button class="btn btn--line btn--sm">Remove</button></form><?php endif; ?></td></tr><?php endforeach; ?>
        </tbody></table>
        <form method="post" action="<?= e(aurl('account', ['a' => 'add'])) ?>" class="stack mt"><?= csrf_field() ?>
          <h3>Add a person</h3>
          <div class="two"><input class="in" name="username" placeholder="Username" required><input class="in" type="password" name="password" placeholder="Password (8+ characters)" required autocomplete="new-password"></div>
          <button class="btn btn--line">Add user</button>
        </form>
      </div>
    </div>
    <?php admin_foot();
}

/* ---------- Backups ---------- */
function view_backups($files) {
    admin_head('Backups', 'backups');
    page_head('Backups', 'A copy of all website texts and settings is saved automatically every time you click Save (the last 40 are kept). If something goes wrong, restore an earlier version.',
        '<a class="btn btn--line" href="' . e(aurl('backups', ['a' => 'download', 'file' => 'current'])) . '">' . icon('upload') . ' Download current content</a>'
        . '<form method="post" action="' . e(aurl('backups', ['a' => 'create'])) . '" class="inl">' . csrf_field() . '<button class="btn btn--primary">' . icon('plus') . ' Make a backup now</button></form>'); ?>
    <div class="card card--flush">
      <?php if (!$files): ?><p class="empty">No backups yet. One is created automatically the first time you save something.</p><?php else: ?>
      <table class="tbl"><thead><tr><th>Saved on</th><th>Size</th><th class="w-act"></th></tr></thead><tbody>
      <?php foreach ($files as $f): ?>
        <tr><td><b><?= e(backup_date($f)) ?></b></td><td><?= e(human_size(filesize($f))) ?></td>
          <td class="w-act"><a class="btn btn--line btn--sm" href="<?= e(aurl('backups', ['a' => 'download', 'file' => basename($f)])) ?>">Download</a>
          <form method="post" action="<?= e(aurl('backups', ['a' => 'restore'])) ?>" class="inl" data-confirm="Restore the website to the version of <?= e(backup_date($f)) ?>? Changes made after that date will be undone (but kept as a backup)."><?= csrf_field() ?><input type="hidden" name="file" value="<?= e(basename($f)) ?>"><button class="btn btn--primary btn--sm">Restore</button></form></td></tr>
      <?php endforeach; ?>
      </tbody></table>
      <?php endif; ?>
    </div>
    <p class="muted mt">Photos are never deleted by restoring a backup. Messages and job applications are stored separately and are not affected.</p>
    <?php admin_foot();
}

/* ---------- Tools ---------- */
function view_tools() {
    $C = content();
    $legacy = 0;
    array_walk_recursive($C, function ($v) use (&$legacy) { if (is_string($v) && preg_match('~^(cms|images)/.+\.(jpe?g|png|webp|gif|svg)$~i', $v)) $legacy++; });
    $checks = [
        ['PHP version ' . PHP_VERSION, version_compare(PHP_VERSION, '7.4', '>=')],
        ['"data" folder can be written', is_writable(DATA_DIR)],
        ['"uploads" folder can be written', is_writable(UPLOAD_DIR)],
        ['Image resizing (GD) available', function_exists('imagecreatetruecolor')],
        ['Email function available', function_exists('mail')],
        ['Max upload size: ' . ini_get('upload_max_filesize') . ' (photos are reduced in your browser before uploading)', true],
    ];
    admin_head('Tools & server check', 'tools');
    page_head('Tools & server check'); ?>
    <div class="cols">
      <div class="card">
        <h2>Server check</h2>
        <ul class="checks"><?php foreach ($checks as [$l, $ok]): ?><li class="<?= $ok ? 'ok' : 'bad' ?>"><?= icon($ok ? 'check' : 'close') ?> <?= e($l) ?></li><?php endforeach; ?></ul>
        <form method="post" action="<?= e(aurl('tools', ['a' => 'testmail'])) ?>" class="inl mt"><?= csrf_field() ?><button class="btn btn--line"><?= icon('mail') ?> Send a test email</button></form>
      </div>
      <div class="card">
        <h2>Move old photos into "uploads"</h2>
        <p>The website currently uses <b><?= $legacy ?></b> photos from your old website folders (<code>cms/</code> and <code>images/</code>). Click below to copy them into the new <code>uploads</code> folder. After that, the old folders can be deleted safely.</p>
        <form method="post" action="<?= e(aurl('tools', ['a' => 'import'])) ?>" class="inl"><?= csrf_field() ?><button class="btn btn--primary"<?= $legacy ? '' : ' disabled' ?>><?= icon('upload') ?> Copy old photos now</button></form>
      </div>
    </div>
    <?php admin_foot();
}
