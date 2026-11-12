<?php
$h = $C['home'];
$slides = visible($C['slides']);
page_start(['page' => 'home', 'nav' => 'home', 'transparent' => true, 'image' => $slides[0]['image'] ?? '']);
$featured = array_values(array_filter(visible($C['projects']), function ($p) { return !empty($p['featured']); }));
?>
<section class="hero" data-hero>
  <div class="hero__media">
    <?php if (!empty($h['hero_video'])): ?>
      <video class="hero__video" autoplay muted loop playsinline poster="<?= e(asset($slides[0]['image'] ?? '')) ?>"><source src="<?= e(asset($h['hero_video'])) ?>" type="video/mp4"></video>
    <?php endif; ?>
    <?php foreach ($slides as $i => $sl): ?>
      <div class="hero__slide<?= $i === 0 ? ' is-on' : '' ?>" style="background-image:url('<?= e(asset($sl['image'])) ?>')"></div>
    <?php endforeach; ?>
  </div>
  <div class="hero__shade"></div>
  <div class="wrap hero__in">
    <p class="hero__eyebrow"><span></span><?= e(L('since')) ?> <?= e($C['settings']['founded']) ?> · <?= e(t($C['settings']['city'])) ?></p>
    <?php foreach ($slides as $i => $sl): ?>
    <div class="hero__text<?= $i === 0 ? ' is-on' : '' ?>" data-slide-text>
      <h1 class="hero__title"><span><?= e(t($sl['title'])) ?></span> <em><?= e(t($sl['highlight'])) ?></em></h1>
      <p class="hero__lead"><?= e(t($sl['text'])) ?></p>
    </div>
    <?php endforeach; ?>
    <div class="hero__btns">
      <a class="btn btn--primary" href="<?= e(url('projects')) ?>"><?= e(L('discover_projects')) ?> <?= icon('arrow', 'flip') ?></a>
      <a class="btn btn--ghost" href="<?= e(url('contact')) ?>"><?= e(L('nav_contact')) ?></a>
    </div>
    <?php if (count($slides) > 1): ?>
    <div class="hero__dots" role="tablist">
      <?php foreach ($slides as $i => $sl): ?>
        <button type="button" class="hero__dot<?= $i === 0 ? ' is-on' : '' ?>" data-go="<?= $i ?>" aria-label="<?= $i + 1 ?>"><b><?= sprintf('%02d', $i + 1) ?></b><i></i></button>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
  <a class="hero__scroll" href="#intro" aria-label="<?= e(L('scroll')) ?>"><span></span></a>
</section>

<section class="intro" id="intro">
  <div class="wrap intro__grid">
    <div>
      <p class="eyebrow"><?= e(t($h['intro_eyebrow'])) ?></p>
      <h2 class="h-xl"><?= hl(t($h['intro_title'])) ?></h2>
    </div>
    <div class="intro__text">
      <?= paras(t($h['intro_text']), 'lead') ?>
      <a class="link-arrow" href="<?= e(url('about')) ?>"><?= e(L('nav_about')) ?> <?= icon('arrow', 'flip') ?></a>
    </div>
  </div>
  <div class="wrap"><?php stats_row(); ?></div>
</section>

<section class="sec sec--soft">
  <div class="wrap">
    <?php sec_title(t($h['sectors_title']), t($h['sectors_text'])); ?>
    <div class="sgrid">
      <?php foreach (visible($C['sectors']) as $s) sector_card($s); ?>
    </div>
  </div>
</section>

<?php if ($featured): ?>
<section class="sec sec--navy">
  <div class="wrap">
    <div class="sec__head">
      <?php sec_title(t($h['projects_title']), t($h['projects_text']), false, true); ?>
      <div class="rail-nav">
        <button type="button" class="rail-btn" data-rail-prev aria-label="Previous"><?= icon('chevr', 'flip-r') ?></button>
        <button type="button" class="rail-btn" data-rail-next aria-label="Next"><?= icon('chevr', 'flip') ?></button>
      </div>
    </div>
  </div>
  <div class="rail" data-rail>
    <?php foreach ($featured as $p) project_card($p, 'pcard--dark'); ?>
  </div>
  <div class="wrap center mt-l"><a class="btn btn--light" href="<?= e(url('projects')) ?>"><?= e(L('all_projects')) ?> <?= icon('arrow', 'flip') ?></a></div>
</section>
<?php endif; ?>

<section class="sec">
  <div class="wrap">
    <?php sec_title(t($h['values_title'])); ?>
    <div class="vgrid vgrid--4">
      <?php foreach (array_slice(visible($C['values']), 0, 4) as $v): ?>
      <div class="vcard">
        <span class="vcard__ic"><?= icon($v['icon']) ?></span>
        <h3><?= e(t($v['title'])) ?></h3>
        <p><?= e(t($v['text'])) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="center mt-l"><a class="link-arrow" href="<?= e(url('values')) ?>"><?= e(L('nav_values')) ?> <?= icon('arrow', 'flip') ?></a></div>
  </div>
</section>

<?php cta_band(); page_end();
