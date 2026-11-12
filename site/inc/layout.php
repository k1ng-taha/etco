<?php
/** Page frame: <head>, top bar, header with navigation, footer. */

function page_start($o) {
    global $C;
    $s = $C['settings'];
    $GLOBALS['PAGE'] = $o;
    $title = isset($o['title']) && $o['title'] !== '' ? plain($o['title']) . ' | ' . t($s['short_name']) : t($s['company_name']);
    $desc = plain($o['desc'] ?? '') ?: t($s['meta_description']);
    $active = $o['nav'] ?? '';
    $transparent = !empty($o['transparent']);
    $cur = $o['page'] ?? 'home'; $curSlug = $o['slug'] ?? '';
    $altUrl = url($cur, $curSlug, other_lang());
    $sectors = visible($C['sectors']);
    $logo = $s['logo'] ?? '';
    $logoLight = ($s['logo_light'] ?? '') ?: $logo;
    $ogImg = $o['image'] ?? '';
    ?><!DOCTYPE html>
<html lang="<?= lang() ?>" dir="<?= is_rtl() ? 'rtl' : 'ltr' ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($desc) ?>">
<?php if ($ogImg): ?><meta property="og:image" content="<?= e(asset($ogImg)) ?>"><?php endif; ?>
<link rel="alternate" hreflang="en" href="<?= e(url($cur, $curSlug, 'en')) ?>">
<link rel="alternate" hreflang="ar" href="<?= e(url($cur, $curSlug, 'ar')) ?>">
<?php if (!empty($s['favicon'])): ?><link rel="icon" href="<?= e(asset($s['favicon'])) ?>"><?php else: ?><link rel="icon" href="<?= e(asset_v('assets/img/favicon.svg')) ?>" type="image/svg+xml"><?php endif; ?>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<?php if (is_rtl()): ?>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<?php else: ?>
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@500;600;700&family=Barlow:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<?php endif; ?>
<link rel="stylesheet" href="<?= e(asset_v('assets/css/site.css')) ?>">
</head>
<body class="<?= $transparent ? 'has-hero' : '' ?> page-<?= e($cur) ?>">
<a class="skip" href="#main"><?= is_rtl() ? 'انتقل إلى المحتوى' : 'Skip to content' ?></a>

<div class="topbar">
  <div class="wrap topbar__in">
    <span class="topbar__hours"><?= icon('clock') ?> <?= e(t($s['hours_week'])) ?> <span class="sep">·</span> <?= e(t($s['hours_sat'])) ?></span>
    <span class="topbar__contact">
      <a href="tel:<?= e($s['phone_link']) ?>" class="topbar__phone"><?= icon('phone') ?> <bdi dir="ltr"><?= e($s['phone_display']) ?></bdi></a>
      <a href="mailto:<?= e($s['email']) ?>" class="topbar__mail" title="<?= e(L('send_email')) ?>" aria-label="<?= e(L('send_email')) ?>"><?= icon('mail') ?></a>
    </span>
  </div>
</div>

<header class="hdr<?= $transparent ? ' hdr--over' : '' ?>" id="hdr">
  <div class="wrap hdr__in">
    <a class="hdr__logo" href="<?= e(url('home')) ?>" aria-label="<?= e(t($s['company_name'])) ?>">
      <?php if ($logo): ?><img class="logo-dark" src="<?= e(asset($logo)) ?>" alt="<?= e(t($s['company_name'])) ?>" width="151" height="48"><?php else: ?><span class="logo-txt">ETCO</span><?php endif; ?>
      <?php if ($logoLight && $transparent): ?><img class="logo-light" src="<?= e(asset($logoLight)) ?>" alt="" aria-hidden="true" width="151" height="48"><?php endif; ?>
    </a>

    <nav class="nav" id="nav" aria-label="Main">
      <div class="nav__head">
        <span class="nav__title"><?= e(L('menu')) ?></span>
        <button class="nav__close" type="button" data-close-nav aria-label="<?= e(L('close')) ?>"><?= icon('close') ?></button>
      </div>
      <ul class="nav__list">
        <li class="nav__item has-sub<?= $active === 'sectors' ? ' is-active' : '' ?>">
          <a href="<?= e(url('sectors')) ?>" class="nav__link" aria-haspopup="true"><?= e(L('nav_sectors')) ?> <?= icon('chev', 'nav__chev') ?></a>
          <div class="sub">
            <div class="sub__grid">
              <?php foreach ($sectors as $sec): ?>
              <a class="sub__item" href="<?= e(url('sectors', $sec['slug'])) ?>">
                <span class="sub__ic"><?= icon($sec['icon']) ?></span>
                <span><b><?= e(t($sec['title'])) ?></b><small><?= e(t($sec['summary'])) ?></small></span>
              </a>
              <?php endforeach; ?>
            </div>
            <a class="sub__all" href="<?= e(url('sectors')) ?>"><?= e(L('all_sectors')) ?> <?= icon('arrow', 'flip') ?></a>
          </div>
        </li>
        <li class="nav__item<?= $active === 'projects' ? ' is-active' : '' ?>"><a class="nav__link" href="<?= e(url('projects')) ?>"><?= e(L('nav_projects')) ?></a></li>
        <li class="nav__item<?= $active === 'values' ? ' is-active' : '' ?>"><a class="nav__link" href="<?= e(url('values')) ?>"><?= e(L('nav_values')) ?></a></li>
        <li class="nav__item<?= $active === 'locations' ? ' is-active' : '' ?>"><a class="nav__link" href="<?= e(url('locations')) ?>"><?= e(L('nav_locations')) ?></a></li>
        <li class="nav__item<?= $active === 'about' ? ' is-active' : '' ?>"><a class="nav__link" href="<?= e(url('about')) ?>"><?= e(L('nav_about')) ?></a></li>
        <li class="nav__item nav__item--m<?= $active === 'contact' ? ' is-active' : '' ?>"><a class="nav__link" href="<?= e(url('contact')) ?>"><?= e(L('nav_contact')) ?></a></li>
        <li class="nav__item nav__item--m<?= $active === 'careers' ? ' is-active' : '' ?>"><a class="nav__link" href="<?= e(url('careers')) ?>"><?= e(L('nav_join')) ?></a></li>
      </ul>
      <div class="nav__foot">
        <a href="tel:<?= e($s['phone_link']) ?>"><?= icon('phone') ?> <bdi dir="ltr"><?= e($s['phone_display']) ?></bdi></a>
        <a href="mailto:<?= e($s['email']) ?>"><?= icon('mail') ?> <?= e($s['email']) ?></a>
      </div>
    </nav>

    <div class="hdr__actions">
      <a class="btn btn--sm btn--primary hdr__cta<?= $active === 'contact' ? ' is-active' : '' ?>" href="<?= e(url('contact')) ?>"><?= e(L('nav_contact')) ?></a>
      <a class="hdr__join<?= $active === 'careers' ? ' is-active' : '' ?>" href="<?= e(url('careers')) ?>"><?= e(L('nav_join')) ?></a>
      <div class="lang" data-lang>
        <button class="lang__btn" type="button" aria-haspopup="true" aria-expanded="false"><?= icon('globe') ?><span><?= strtoupper(lang()) ?></span><?= icon('chev', 'lang__chev') ?></button>
        <ul class="lang__menu" role="menu">
          <li><a role="menuitem" href="<?= e(url($cur, $curSlug, 'en')) ?>" lang="en" class="<?= lang() === 'en' ? 'is-on' : '' ?>"><b>EN</b> English</a></li>
          <li><a role="menuitem" href="<?= e(url($cur, $curSlug, 'ar')) ?>" lang="ar" dir="rtl" class="<?= lang() === 'ar' ? 'is-on' : '' ?>"><b>AR</b> العربية</a></li>
        </ul>
      </div>
      <button class="burger" type="button" data-open-nav aria-label="<?= e(L('menu')) ?>" aria-controls="nav"><?= icon('menu') ?></button>
    </div>
  </div>
</header>
<div class="nav-overlay" data-close-nav></div>
<main id="main">
<?php
}

function page_end() {
    global $C;
    $s = $C['settings'];
    $mapUrl = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($s['map_query'] ?? 'Beirut');
    ?>
</main>
<footer class="ftr">
  <div class="wrap ftr__top">
    <div class="ftr__brand">
      <a class="ftr__logo" href="<?= e(url('home')) ?>"><?php $fl = ($s['logo_light'] ?? '') ?: ($s['logo'] ?? ''); if ($fl): ?><img src="<?= e(asset($fl)) ?>" alt="<?= e(t($s['company_name'])) ?>" loading="lazy"><?php endif; ?></a>
      <p><?= e(t($s['tagline'])) ?></p>
    </div>
    <div class="ftr__col">
      <h3><?= e(L('footer_explore')) ?></h3>
      <ul>
        <li><a href="<?= e(url('sectors')) ?>"><?= e(L('nav_sectors')) ?></a></li>
        <li><a href="<?= e(url('projects')) ?>"><?= e(L('nav_projects')) ?></a></li>
        <li><a href="<?= e(url('values')) ?>"><?= e(L('nav_values')) ?></a></li>
        <li><a href="<?= e(url('locations')) ?>"><?= e(L('nav_locations')) ?></a></li>
        <li><a href="<?= e(url('about')) ?>"><?= e(L('nav_about')) ?></a></li>
        <li><a href="<?= e(url('careers')) ?>"><?= e(L('nav_join')) ?></a></li>
      </ul>
    </div>
    <div class="ftr__col">
      <h3><?= e(L('nav_sectors')) ?></h3>
      <ul>
        <?php foreach (visible($C['sectors']) as $sec): ?><li><a href="<?= e(url('sectors', $sec['slug'])) ?>"><?= e(t($sec['title'])) ?></a></li><?php endforeach; ?>
      </ul>
    </div>
    <div class="ftr__col ftr__contact">
      <h3><?= e(L('footer_contact')) ?></h3>
      <ul>
        <li><?= icon('pin') ?><a href="<?= e($mapUrl) ?>" target="_blank" rel="noopener"><?= e(t($s['address'])) ?><br><?= e(t($s['city'])) ?></a></li>
        <li><?= icon('phone') ?><a href="tel:<?= e($s['phone_link']) ?>"><bdi dir="ltr"><?= e($s['phone_display']) ?></bdi></a></li>
        <li><?= icon('mail') ?><a href="mailto:<?= e($s['email']) ?>"><?= e($s['email']) ?></a></li>
        <li><?= icon('clock') ?><span><?= e(t($s['hours_week'])) ?><br><?= e(t($s['hours_sat'])) ?></span></li>
      </ul>
    </div>
  </div>
  <div class="ftr__bottom">
    <div class="wrap ftr__bottom-in">
      <span>© <?= date('Y') ?> <?= e(t($s['company_name'])) ?>. <?= e(L('footer_rights')) ?></span>
      <a href="<?= e(url($GLOBALS['PAGE']['page'] ?? 'home', $GLOBALS['PAGE']['slug'] ?? '', other_lang())) ?>" lang="<?= other_lang() ?>"><?= icon('globe') ?> <?= lang() === 'ar' ? 'English' : 'العربية' ?></a>
    </div>
  </div>
</footer>
<div class="lightbox" id="lightbox" hidden>
  <button class="lightbox__close" type="button" aria-label="<?= e(L('close')) ?>"><?= icon('close') ?></button>
  <button class="lightbox__nav lightbox__prev" type="button" aria-label="Previous"><?= icon('chevr', 'flip-r') ?></button>
  <figure><img alt=""><figcaption></figcaption></figure>
  <button class="lightbox__nav lightbox__next" type="button" aria-label="Next"><?= icon('chevr', 'flip') ?></button>
</div>
<script src="<?= e(asset_v('assets/js/site.js')) ?>" defer></script>
</body>
</html>
<?php
}
