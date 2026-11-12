<?php
$pg = $C['pages']['projects'];
page_start(['page' => 'projects', 'nav' => 'projects', 'title' => t($pg['title']), 'desc' => t($pg['meta']), 'image' => $pg['image']]);
page_hero(t($pg['title']), t($pg['subtitle']), $pg['image'], [[t($pg['title']), url('projects')]]);
$projects = visible($C['projects']);
$gallery = visible($C['gallery']);
$counts = [];
foreach ($projects as $p) $counts[$p['category']] = ($counts[$p['category']] ?? 0) + 1;
$gcounts = [];
foreach ($gallery as $g) $gcounts[$g['category']] = ($gcounts[$g['category']] ?? 0) + 1;
?>
<section class="sec sec--tight">
  <div class="wrap">
    <div class="tabs" role="tablist" data-tabs>
      <button type="button" class="tab is-on" role="tab" data-tab="list"><?= e(L('tab_projects')) ?> <small><?= count($projects) ?></small></button>
      <?php if ($gallery): ?><button type="button" class="tab" role="tab" data-tab="gallery"><?= e(L('tab_gallery')) ?> <small><?= count($gallery) ?></small></button><?php endif; ?>
    </div>

    <div class="tabpane is-on" data-pane="list">
      <?php if (trim(t($pg['intro']))): ?><div class="narrow"><?= paras(t($pg['intro']), 'lead') ?></div><?php endif; ?>
      <div class="chips" data-filter=".pgrid .pcard">
        <button type="button" class="chip is-on" data-f="*"><?= e(L('filter_all')) ?> <small><?= count($projects) ?></small></button>
        <?php foreach (visible($C['categories']) as $cat): if (empty($counts[$cat['id']])) continue; ?>
          <button type="button" class="chip" data-f="<?= e($cat['id']) ?>"><?= e(t($cat['name'])) ?> <small><?= $counts[$cat['id']] ?></small></button>
        <?php endforeach; ?>
      </div>
      <div class="pgrid">
        <?php foreach ($projects as $p) project_card($p); ?>
      </div>
    </div>

    <?php if ($gallery): ?>
    <div class="tabpane" data-pane="gallery" id="gallery">
      <?php if (trim(t($pg['gallery_intro']))): ?><p class="lead narrow"><?= e(t($pg['gallery_intro'])) ?></p><?php endif; ?>
      <div class="chips" data-filter=".ggrid .gitem">
        <button type="button" class="chip is-on" data-f="*"><?= e(L('filter_all')) ?> <small><?= count($gallery) ?></small></button>
        <?php foreach (visible($C['gallery_categories']) as $gc): if (empty($gcounts[$gc['id']])) continue; ?>
          <button type="button" class="chip" data-f="<?= e($gc['id']) ?>"><?= e(t($gc['name'])) ?> <small><?= $gcounts[$gc['id']] ?></small></button>
        <?php endforeach; ?>
      </div>
      <div class="ggrid" data-lightbox-group>
        <?php foreach ($gallery as $g): ?>
          <a class="gitem" href="<?= e(asset($g['image'])) ?>" data-cat="<?= e($g['category']) ?>" data-lightbox data-caption="<?= e(t($g['caption'])) ?>">
            <img src="<?= e(asset($g['image'])) ?>" alt="<?= e(t($g['caption'])) ?>" loading="lazy">
          </a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php cta_band(); page_end();
