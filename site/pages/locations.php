<?php
$pg = $C['pages']['locations'];
$s = $C['settings'];
page_start(['page' => 'locations', 'nav' => 'locations', 'title' => t($pg['title']), 'desc' => t($pg['meta']), 'image' => $pg['image']]);
page_hero(t($pg['title']), t($pg['subtitle']), $pg['image'], [[t($pg['title']), url('locations')]]);
$projects = visible($C['projects']);
$mapQ = rawurlencode($s['map_query'] ?? 'Beirut');
?>
<section class="sec">
  <div class="wrap narrow center"><?= paras(t($pg['intro']), 'lead') ?></div>
  <div class="wrap office mt-l">
    <div class="office__card">
      <p class="eyebrow"><?= e(L('our_office')) ?></p>
      <h2 class="h-m"><?= e(t($s['company_name'])) ?></h2>
      <ul class="clist">
        <li><?= icon('pin') ?><span><b><?= e(L('address')) ?></b><?= e(t($s['address'])) ?><br><?= e(t($s['city'])) ?></span></li>
        <li><?= icon('phone') ?><span><b><?= e(L('phone')) ?></b><a href="tel:<?= e($s['phone_link']) ?>"><bdi dir="ltr"><?= e($s['phone_display']) ?></bdi></a></span></li>
        <li><?= icon('mail') ?><span><b><?= e(L('email')) ?></b><a href="mailto:<?= e($s['email']) ?>"><?= e($s['email']) ?></a></span></li>
        <li><?= icon('clock') ?><span><b><?= e(L('working_hours')) ?></b><?= e(t($s['hours_week'])) ?><br><?= e(t($s['hours_sat'])) ?></span></li>
      </ul>
      <a class="btn btn--primary btn--sm" href="https://www.google.com/maps/dir/?api=1&destination=<?= $mapQ ?>" target="_blank" rel="noopener"><?= e(L('get_directions')) ?> <?= icon('arrow', 'flip') ?></a>
    </div>
    <div class="office__map">
      <iframe title="Map" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="https://maps.google.com/maps?q=<?= $mapQ ?>&z=15&hl=<?= lang() ?>&output=embed"></iframe>
    </div>
  </div>
</section>

<section class="sec sec--soft">
  <div class="wrap">
    <?php sec_title(L('where_worked')); ?>
    <div class="regions">
      <?php foreach (visible($C['regions']) as $r):
        $list = array_values(array_filter($projects, function ($p) use ($r) { return ($p['region'] ?? '') === $r['id']; }));
        if (!$list) continue; ?>
      <div class="region">
        <div class="region__head">
          <h3><?= icon('pin') ?><?= e(t($r['name'])) ?></h3>
          <span class="region__count"><b><?= count($list) ?></b> <?= e(count($list) === 1 ? L('project_word') : L('projects_word')) ?></span>
        </div>
        <p><?= e(t($r['text'])) ?></p>
        <ul class="region__list">
          <?php foreach ($list as $p): ?><li><a href="<?= e(url('projects', $p['slug'])) ?>"><?= e(t($p['title'])) ?></a></li><?php endforeach; ?>
        </ul>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php cta_band(); page_end();
