<?php
/** Reusable page pieces. */

function page_hero($title, $subtitle = '', $image = '', $crumbs = []) { ?>
<section class="phero">
  <?php if ($image): ?><div class="phero__bg" style="background-image:url('<?= e(asset($image)) ?>')"></div><?php endif; ?>
  <div class="wrap phero__in">
    <nav class="crumbs" aria-label="Breadcrumb">
      <a href="<?= e(url('home')) ?>"><?= e(L('nav_home')) ?></a>
      <?php foreach ($crumbs as $c): ?><span>/</span><a href="<?= e($c[1]) ?>"><?= e($c[0]) ?></a><?php endforeach; ?>
    </nav>
    <h1 class="phero__title"><?= hl($title) ?></h1>
    <?php if ($subtitle): ?><p class="phero__sub"><?= e($subtitle) ?></p><?php endif; ?>
  </div>
</section>
<?php }

function sec_title($title, $text = '', $center = true, $light = false) { ?>
  <div class="stitle<?= $center ? ' stitle--c' : '' ?><?= $light ? ' stitle--light' : '' ?>">
    <h2><?= hl($title) ?></h2>
    <span class="pill" aria-hidden="true"></span>
    <?php if ($text): ?><p><?= e($text) ?></p><?php endif; ?>
  </div>
<?php }

function cat_name($id) {
    global $C;
    $c = find_by($C['categories'], 'id', $id);
    return $c ? t($c['name']) : '';
}

function project_card($p, $extra = '') { ?>
  <a class="pcard<?= $extra ? ' ' . e($extra) : '' ?>" href="<?= e(url('projects', $p['slug'])) ?>" data-cat="<?= e($p['category']) ?>">
    <span class="pcard__img<?= ($p['image_fit'] ?? '') === 'contain' ? ' is-contain' : '' ?>"><img src="<?= e(asset($p['image'])) ?>" alt="<?= e(t($p['title'])) ?>" loading="lazy"></span>
    <span class="pcard__body">
      <span class="pcard__cat"><?= e(cat_name($p['category'])) ?></span>
      <span class="pcard__title"><?= e(t($p['title'])) ?></span>
      <span class="pcard__meta"><?= icon('pin') ?> <?= e(t($p['location'])) ?><?php if (project_year($p)): ?> <span class="dot">·</span> <?= e(project_year($p)) ?><?php endif; ?></span>
    </span>
  </a>
<?php }

function sector_card($s) { ?>
  <a class="scard" href="<?= e(url('sectors', $s['slug'])) ?>">
    <span class="scard__img" style="background-image:url('<?= e(asset($s['image'])) ?>')"></span>
    <span class="scard__shade"></span>
    <span class="scard__body">
      <span class="scard__ic"><?= icon($s['icon']) ?></span>
      <span class="scard__title"><?= e(t($s['title'])) ?></span>
      <span class="scard__text"><?= e(t($s['summary'])) ?></span>
      <span class="scard__more"><?= e(L('learn_more')) ?> <?= icon('arrow', 'flip') ?></span>
    </span>
  </a>
<?php }

function cta_band() {
    global $C;
    $h = $C['home']; ?>
<section class="cta2">
  <a class="cta2__item cta2__item--blue" href="<?= e(url('contact')) ?>">
    <?php if (!empty($h['cta_contact_image'])): ?><span class="cta2__bg" style="background-image:url('<?= e(asset($h['cta_contact_image'])) ?>')"></span><?php endif; ?>
    <span class="cta2__body">
      <span class="cta2__title"><?= e(t($h['cta_contact_title'])) ?></span>
      <span class="cta2__text"><?= e(t($h['cta_contact_text'])) ?></span>
      <span class="btn btn--light"><?= e(L('nav_contact')) ?> <?= icon('arrow', 'flip') ?></span>
    </span>
  </a>
  <a class="cta2__item cta2__item--navy" href="<?= e(url('careers')) ?>">
    <?php if (!empty($h['cta_join_image'])): ?><span class="cta2__bg" style="background-image:url('<?= e(asset($h['cta_join_image'])) ?>')"></span><?php endif; ?>
    <span class="cta2__body">
      <span class="cta2__title"><?= e(t($h['cta_join_title'])) ?></span>
      <span class="cta2__text"><?= e(t($h['cta_join_text'])) ?></span>
      <span class="btn btn--ghost"><?= e(L('nav_join')) ?> <?= icon('arrow', 'flip') ?></span>
    </span>
  </a>
</section>
<?php }

function stats_row($dark = false) {
    global $C; ?>
  <div class="stats<?= $dark ? ' stats--dark' : '' ?>">
    <?php foreach (visible($C['stats']) as $st): ?>
    <div class="stat">
      <span class="stat__num"><bdi dir="ltr"><span data-count><?= e(stat_value($st['value'])) ?></span><?php if ($st['suffix'] !== ''): ?><small><?= e($st['suffix']) ?></small><?php endif; ?></bdi></span>
      <span class="stat__label"><?= e(t($st['label'])) ?></span>
    </div>
    <?php endforeach; ?>
  </div>
<?php }

/* ---- Simple flash messages for the public forms ---- */
function flash_set($k, $v) { start_session(); $_SESSION['flash'][$k] = $v; }
function flash_get($k) { start_session(); $v = $_SESSION['flash'][$k] ?? null; unset($_SESSION['flash'][$k]); return $v; }
