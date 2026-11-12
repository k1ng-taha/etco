<?php
$pg = $C['pages']['values'];
page_start(['page' => 'values', 'nav' => 'values', 'title' => t($pg['title']), 'desc' => t($pg['meta']), 'image' => $pg['image']]);
page_hero(t($pg['title']), t($pg['subtitle']), $pg['image'], [[t($pg['title']), url('values')]]);
?>
<section class="sec">
  <div class="wrap narrow center"><?= paras(t($pg['intro']), 'lead') ?></div>
  <div class="wrap">
    <div class="vgrid vgrid--3 mt-l">
      <?php foreach (visible($C['values']) as $i => $v): ?>
      <div class="vcard vcard--boxed">
        <span class="vcard__num"><?= sprintf('%02d', $i + 1) ?></span>
        <span class="vcard__ic"><?= icon($v['icon']) ?></span>
        <h3><?= e(t($v['title'])) ?></h3>
        <p><?= e(t($v['text'])) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="sec sec--navy">
  <div class="wrap">
    <?php sec_title(t($pg['commitments_title']), '', true, true); ?>
    <div class="commit">
      <?php foreach (visible($C['commitments']) as $i => $c): ?>
      <div class="commit__item">
        <span class="commit__n"><?= $i + 1 ?></span>
        <h3><?= e(t($c['title'])) ?></h3>
        <p><?= e(t($c['text'])) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php cta_band(); page_end();
