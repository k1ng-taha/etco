<?php
require_once ROOT . '/inc/forms.php';
$pg = $C['pages']['careers'];
$r = ['fields' => []];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $r = form_handle_apply();
    if ($r['ok']) { flash_set('apply', 1); header('Location: ' . url('careers', '', null, 'apply'), true, 303); exit; }
}
$sent = flash_get('apply');
page_start(['page' => 'careers', 'nav' => 'careers', 'title' => t($pg['title']), 'desc' => t($pg['meta']), 'image' => $pg['image']]);
page_hero(t($pg['title']), t($pg['subtitle']), $pg['image'], [[t($pg['title']), url('careers')]]);
$positions = visible($C['positions']);
?>
<section class="sec">
  <div class="wrap narrow center"><?= paras(t($pg['intro']), 'lead') ?></div>
  <div class="wrap">
    <div class="vgrid vgrid--3 mt-l">
      <?php foreach (visible($C['join_reasons']) as $j): ?>
      <div class="vcard vcard--boxed"><span class="vcard__ic"><?= icon($j['icon']) ?></span><h3><?= e(t($j['title'])) ?></h3><p><?= e(t($j['text'])) ?></p></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="sec sec--soft">
  <div class="wrap split">
    <div class="split__main">
      <h2 class="h-m"><?= e(L('open_positions')) ?></h2>
      <?php if ($positions): ?>
        <div class="jobs">
        <?php foreach ($positions as $j): ?>
          <article class="job">
            <div>
              <h3><?= e(t($j['title'])) ?></h3>
              <p class="job__meta"><?= icon('pin') ?> <?= e(t($j['location'])) ?><?php if (t($j['type'])): ?> <span class="dot">·</span> <?= e(t($j['type'])) ?><?php endif; ?></p>
              <?= paras(t($j['description'])) ?>
            </div>
            <a class="btn btn--primary btn--sm" href="#apply" data-apply="<?= e(t($j['title'])) ?>"><?= e(L('apply')) ?></a>
          </article>
        <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="lead"><?= e(L('no_positions')) ?></p>
      <?php endif; ?>
    </div>
    <aside class="split__side">
      <div class="boxcard">
        <h2 class="h-s"><?= e(t($pg['profiles_title'])) ?></h2>
        <ul class="ticks"><?php foreach (visible($C['profiles']) as $pr): ?><li><?= icon('check') ?><?= e(t($pr['name'])) ?></li><?php endforeach; ?></ul>
      </div>
    </aside>
  </div>
</section>

<section class="sec" id="apply">
  <div class="wrap narrow">
    <h2 class="h-m"><?= e(L('apply_title')) ?></h2>
    <?php if ($sent): ?><div class="alert alert--ok" role="status"><?= icon('check') ?> <?= e(L('apply_success')) ?></div><?php endif; ?>
    <?php if (!empty($r['errors'])): ?><div class="alert alert--err" role="alert"><?= e(L('form_error')) ?></div><?php endif; ?>
    <form method="post" action="<?= e(url('careers', '', null, 'apply')) ?>" class="form" enctype="multipart/form-data" novalidate>
      <?= csrf_field() ?>
      <div class="hp" aria-hidden="true"><label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
      <div class="form__row">
        <label class="fld<?= field_err($r, 'name') ?>"><span><?= e(L('form_name')) ?> *</span><input type="text" name="name" required autocomplete="name" value="<?= field_val($r, 'name') ?>"></label>
        <label class="fld<?= field_err($r, 'email') ?>"><span><?= e(L('form_email')) ?> *</span><input type="email" name="email" required dir="ltr" autocomplete="email" value="<?= field_val($r, 'email') ?>"></label>
      </div>
      <div class="form__row">
        <label class="fld<?= field_err($r, 'phone') ?>"><span><?= e(L('form_phone')) ?> *</span><input type="tel" name="phone" required dir="ltr" autocomplete="tel" value="<?= field_val($r, 'phone') ?>"></label>
        <label class="fld"><span><?= e(L('form_position')) ?></span>
          <input type="text" name="position" list="positions" value="<?= field_val($r, 'position') ?: e(L('spontaneous')) ?>">
          <datalist id="positions"><option value="<?= e(L('spontaneous')) ?>"><?php foreach ($positions as $j): ?><option value="<?= e(t($j['title'])) ?>"><?php endforeach; ?><?php foreach (visible($C['profiles']) as $pr): ?><option value="<?= e(t($pr['name'])) ?>"><?php endforeach; ?></datalist>
        </label>
      </div>
      <label class="fld"><span><?= e(L('form_cover')) ?></span><textarea name="message" rows="5"><?= field_val($r, 'message') ?></textarea></label>
      <label class="fld fld--file<?= field_err($r, 'cv') ?>"><span><?= e(L('form_cv')) ?></span><input type="file" name="cv" accept=".pdf,.doc,.docx"></label>
      <button class="btn btn--primary" type="submit"><?= e(L('form_apply')) ?> <?= icon('arrow', 'flip') ?></button>
    </form>
  </div>
</section>
<?php page_end();
