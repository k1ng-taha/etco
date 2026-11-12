<?php
$pg = $C['pages']['sectors'];
page_start(['page' => 'sectors', 'nav' => 'sectors', 'title' => t($pg['title']), 'desc' => t($pg['meta']), 'image' => $pg['image']]);
page_hero(t($pg['title']), t($pg['subtitle']), $pg['image'], [[t($pg['title']), url('sectors')]]);
$projects = visible($C['projects']);
?>
<section class="sec">
  <div class="wrap narrow center">
    <?= paras(t($pg['intro']), 'lead') ?>
  </div>
  <div class="wrap slist">
    <?php foreach (visible($C['sectors']) as $i => $s):
      $count = count(array_filter($projects, function ($p) use ($s) { return in_array($s['id'], (array) ($p['sectors'] ?? []), true); })); ?>
    <article class="srow<?= $i % 2 ? ' srow--rev' : '' ?>">
      <a class="srow__img" href="<?= e(url('sectors', $s['slug'])) ?>"><img src="<?= e(asset($s['image'])) ?>" alt="<?= e(t($s['title'])) ?>" loading="lazy"></a>
      <div class="srow__body">
        <span class="srow__num"><?= sprintf('%02d', $i + 1) ?></span>
        <h2><span class="srow__ic"><?= icon($s['icon']) ?></span><?= e(t($s['title'])) ?></h2>
        <p class="lead"><?= e(t($s['summary'])) ?></p>
        <ul class="ticks">
          <?php foreach (array_slice((array) $s['points'], 0, 4) as $pt): ?><li><?= icon('check') ?><?= e(t($pt)) ?></li><?php endforeach; ?>
        </ul>
        <div class="srow__foot">
          <a class="btn btn--primary btn--sm" href="<?= e(url('sectors', $s['slug'])) ?>"><?= e(L('learn_more')) ?> <?= icon('arrow', 'flip') ?></a>
          <?php if ($count): ?><span class="muted"><?= $count ?> <?= e($count === 1 ? L('project_word') : L('projects_word')) ?></span><?php endif; ?>
        </div>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
</section>
<?php cta_band(); page_end();
