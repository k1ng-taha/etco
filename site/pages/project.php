<?php
$all = visible($C['projects']);
$idx = null;
foreach ($all as $i => $pp) if ($pp['slug'] === $slug) { $idx = $i; break; }
if ($idx === null) { include ROOT . '/pages/404.php'; return; }
$p = $all[$idx];
$prev = $all[($idx - 1 + count($all)) % count($all)];
$next = $all[($idx + 1) % count($all)];
$pg = $C['pages']['projects'];
page_start(['page' => 'projects', 'slug' => $p['slug'], 'nav' => 'projects', 'title' => t($p['title']), 'desc' => mb_substr(t($p['description']), 0, 160), 'image' => $p['image']]);
$photos = array_values(array_filter(array_merge([$p['image']], (array) ($p['gallery'] ?? []))));
$secs = array_filter(array_map(function ($id) use ($C) { return find_by(visible($C['sectors']), 'id', $id); }, (array) ($p['sectors'] ?? [])));
?>
<section class="dhero dhero--project">
  <div class="wrap dhero__grid">
    <div class="dhero__text">
      <nav class="crumbs crumbs--dark" aria-label="Breadcrumb">
        <a href="<?= e(url('home')) ?>"><?= e(L('nav_home')) ?></a><span>/</span><a href="<?= e(url('projects')) ?>"><?= e(t($pg['title'])) ?></a>
      </nav>
      <span class="pcard__cat"><?= e(cat_name($p['category'])) ?></span>
      <h1 class="h-xl"><?= e(t($p['title'])) ?></h1>
      <dl class="facts">
        <div><dt><?= icon('pin') ?><?= e(L('location')) ?></dt><dd><?= e(t($p['location'])) ?></dd></div>
        <?php if (project_year($p)): ?><div><dt><?= icon('calendar') ?><?= e(L('year')) ?></dt><dd><?= e(project_year($p)) ?></dd></div><?php endif; ?>
        <div><dt><?= icon('tag') ?><?= e(L('type')) ?></dt><dd><?= e(t($p['type'])) ?></dd></div>
        <div><dt><?= icon('wrench') ?><?= e(L('scope')) ?></dt><dd><?= e(t($p['scope'])) ?></dd></div>
      </dl>
    </div>
    <a class="dhero__img<?= ($p['image_fit'] ?? '') === 'contain' ? ' is-contain' : '' ?>" href="<?= e(asset($p['image'])) ?>" data-lightbox data-caption="<?= e(t($p['title'])) ?>"><img src="<?= e(asset($p['image'])) ?>" alt="<?= e(t($p['title'])) ?>"></a>
  </div>
</section>

<section class="sec">
  <div class="wrap split">
    <div class="split__main prose"><?= paras(t($p['description'])) ?>
      <?php if ($secs): ?>
      <div class="chips chips--links mt-m">
        <?php foreach ($secs as $s): ?><a class="chip-link" href="<?= e(url('sectors', $s['slug'])) ?>"><?= icon($s['icon']) ?> <?= e(t($s['title'])) ?></a><?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
    <aside class="split__side">
      <div class="boxcard boxcard--blue">
        <h2 class="h-s"><?= e(t($C['home']['cta_contact_title'])) ?></h2>
        <p><?= e(t($C['home']['cta_contact_text'])) ?></p>
        <a class="btn btn--light btn--sm" href="<?= e(url('contact')) ?>"><?= e(L('nav_contact')) ?> <?= icon('arrow', 'flip') ?></a>
      </div>
    </aside>
  </div>
</section>

<?php if (count($photos) > 1): ?>
<section class="sec sec--soft sec--tight">
  <div class="wrap">
    <h2 class="h-m"><?= e(L('photos')) ?></h2>
    <div class="ggrid ggrid--sm" data-lightbox-group>
      <?php foreach (array_slice($photos, 1) as $ph): ?>
        <a class="gitem" href="<?= e(asset($ph)) ?>" data-lightbox data-caption="<?= e(t($p['title'])) ?>"><img src="<?= e(asset($ph)) ?>" alt="<?= e(t($p['title'])) ?>" loading="lazy"></a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="pnav">
  <div class="wrap pnav__in">
    <a class="pnav__link" href="<?= e(url('projects', $prev['slug'])) ?>"><?= icon('chevr', 'flip-r') ?><span><small><?= e(L('prev_project')) ?></small><?= e(t($prev['title'])) ?></span></a>
    <a class="pnav__all" href="<?= e(url('projects')) ?>"><?= e(L('back_projects')) ?></a>
    <a class="pnav__link pnav__link--next" href="<?= e(url('projects', $next['slug'])) ?>"><span><small><?= e(L('next_project')) ?></small><?= e(t($next['title'])) ?></span><?= icon('chevr', 'flip') ?></a>
  </div>
</section>
<?php page_end();
