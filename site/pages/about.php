<?php
$pg = $C['pages']['about'];
page_start(['page' => 'about', 'nav' => 'about', 'title' => t($pg['title']), 'desc' => t($pg['meta']), 'image' => $pg['image']]);
page_hero(t($pg['title']), t($pg['subtitle']), $pg['image'], [[t($pg['title']), url('about')]]);
// milestones built automatically from project years
$years = [];
foreach (visible($C['projects']) as $p) { if (preg_match('/^\d{4}$/', (string) $p['year'])) $years[$p['year']][] = $p; }
ksort($years);
?>
<section class="sec">
  <div class="wrap story">
    <div class="story__text">
      <h2 class="h-xl"><?= hl(t($pg['story_title'])) ?></h2>
      <div class="prose"><?= paras(t($pg['story'])) ?></div>
    </div>
    <?php if (!empty($pg['story_image'])): ?>
    <div class="story__img"><img src="<?= e(asset($pg['story_image'])) ?>" alt="" loading="lazy"><span class="story__badge"><b><?= e($C['settings']['founded']) ?></b><?= e(L('since')) ?></span></div>
    <?php endif; ?>
  </div>
  <div class="wrap mt-xl"><?php stats_row(); ?></div>
</section>

<section class="sec sec--soft">
  <div class="wrap mv">
    <div class="mv__item"><span class="mv__ic"><?= icon('approach') ?></span><h3><?= e(L('mission')) ?></h3><p><?= e(t($pg['mission'])) ?></p></div>
    <div class="mv__item mv__item--dark"><span class="mv__ic"><?= icon('globe') ?></span><h3><?= e(L('vision')) ?></h3><p><?= e(t($pg['vision'])) ?></p></div>
  </div>
</section>

<section class="sec">
  <div class="wrap">
    <?php sec_title(t($pg['features_title'])); ?>
    <div class="vgrid vgrid--4">
      <?php foreach (visible($C['features']) as $f): ?>
      <div class="vcard"><span class="vcard__ic"><?= icon($f['icon']) ?></span><h3><?= e(t($f['title'])) ?></h3><p><?= e(t($f['text'])) ?></p></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php if ($years): ?>
<section class="sec sec--navy">
  <div class="wrap">
    <?php sec_title(L('milestones'), '', true, true); ?>
    <ol class="tline">
      <?php foreach ($years as $y => $list): ?>
      <li class="tline__item">
        <span class="tline__year"><?= e($y) ?></span>
        <span class="tline__dot"></span>
        <span class="tline__list"><?php foreach ($list as $k => $p): ?><a href="<?= e(url('projects', $p['slug'])) ?>"><?= e(t($p['title'])) ?></a><?php endforeach; ?></span>
      </li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>
<?php endif; ?>
<?php cta_band(); page_end();
