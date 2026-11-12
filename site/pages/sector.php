<?php
$s = find_by(visible($C['sectors']), 'slug', $slug);
if (!$s) { include ROOT . '/pages/404.php'; return; }
$pg = $C['pages']['sectors'];
page_start(['page' => 'sectors', 'slug' => $s['slug'], 'nav' => 'sectors', 'title' => t($s['title']), 'desc' => t($s['summary']), 'image' => $s['image']]);
$related = array_values(array_filter(visible($C['projects']), function ($p) use ($s) { return in_array($s['id'], (array) ($p['sectors'] ?? []), true); }));
?>
<section class="dhero">
  <div class="wrap dhero__grid">
    <div class="dhero__text">
      <nav class="crumbs crumbs--dark" aria-label="Breadcrumb">
        <a href="<?= e(url('home')) ?>"><?= e(L('nav_home')) ?></a><span>/</span><a href="<?= e(url('sectors')) ?>"><?= e(t($pg['title'])) ?></a>
      </nav>
      <span class="dhero__ic"><?= icon($s['icon']) ?></span>
      <h1 class="h-xl"><?= e(t($s['title'])) ?></h1>
      <p class="lead"><?= e(t($s['summary'])) ?></p>
      <a class="btn btn--primary" href="<?= e(url('contact')) ?>"><?= e(L('nav_contact')) ?> <?= icon('arrow', 'flip') ?></a>
    </div>
    <div class="dhero__img"><img src="<?= e(asset($s['image'])) ?>" alt="<?= e(t($s['title'])) ?>"></div>
  </div>
</section>

<section class="sec">
  <div class="wrap split">
    <div class="split__main prose"><?= paras(t($s['body'])) ?>
      <?php if (!empty($s['image2'])): ?><img class="prose__img" src="<?= e(asset($s['image2'])) ?>" alt="" loading="lazy"><?php endif; ?>
    </div>
    <aside class="split__side">
      <div class="boxcard">
        <h2 class="h-s"><?= e(L('what_we_do')) ?></h2>
        <ul class="ticks">
          <?php foreach ((array) $s['points'] as $pt): ?><li><?= icon('check') ?><?= e(t($pt)) ?></li><?php endforeach; ?>
        </ul>
      </div>
    </aside>
  </div>
</section>

<section class="sec sec--soft">
  <div class="wrap">
    <?php sec_title(L('related_projects')); ?>
    <?php if ($related): ?>
    <div class="pgrid">
      <?php foreach (array_slice($related, 0, 6) as $p) project_card($p); ?>
    </div>
    <?php if (count($related) > 6): ?><div class="center mt-l"><a class="btn btn--primary" href="<?= e(url('projects')) ?>"><?= e(L('all_projects')) ?> <?= icon('arrow', 'flip') ?></a></div><?php endif; ?>
    <?php else: ?>
      <p class="center lead"><?= e(L('sector_projects_none')) ?></p>
    <?php endif; ?>
  </div>
</section>

<section class="sec">
  <div class="wrap">
    <?php sec_title(L('other_sectors')); ?>
    <div class="chips chips--links">
      <?php foreach (visible($C['sectors']) as $o): if ($o['id'] === $s['id']) continue; ?>
        <a class="chip-link" href="<?= e(url('sectors', $o['slug'])) ?>"><?= icon($o['icon']) ?> <?= e(t($o['title'])) ?></a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php cta_band(); page_end();
