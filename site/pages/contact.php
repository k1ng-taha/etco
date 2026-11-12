<?php
require_once ROOT . '/inc/forms.php';
$pg = $C['pages']['contact'];
$s = $C['settings'];
$r = ['fields' => []];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $r = form_handle_contact();
    if ($r['ok']) { flash_set('contact', 1); header('Location: ' . url('contact', '', null, 'form'), true, 303); exit; }
}
$sent = flash_get('contact');
page_start(['page' => 'contact', 'nav' => 'contact', 'title' => t($pg['title']), 'desc' => t($pg['meta']), 'image' => $pg['image']]);
page_hero(t($pg['title']), t($pg['subtitle']), $pg['image'], [[t($pg['title']), url('contact')]]);
$mapQ = rawurlencode($s['map_query'] ?? 'Beirut');
?>
<section class="sec">
  <div class="wrap contact">
    <div class="contact__form" id="form">
      <h2 class="h-m"><?= e(L('form_title')) ?></h2>
      <?= paras(t($pg['intro'])) ?>
      <?php if ($sent): ?><div class="alert alert--ok" role="status"><?= icon('check') ?> <?= e(L('form_success')) ?></div><?php endif; ?>
      <?php if (!empty($r['errors'])): ?><div class="alert alert--err" role="alert"><?= e(L('form_error')) ?></div><?php endif; ?>
      <form method="post" action="<?= e(url('contact', '', null, 'form')) ?>" class="form" novalidate>
        <?= csrf_field() ?>
        <div class="hp" aria-hidden="true"><label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
        <div class="form__row">
          <label class="fld<?= field_err($r, 'name') ?>"><span><?= e(L('form_name')) ?> *</span><input type="text" name="name" required autocomplete="name" value="<?= field_val($r, 'name') ?>"></label>
          <label class="fld<?= field_err($r, 'email') ?>"><span><?= e(L('form_email')) ?> *</span><input type="email" name="email" required autocomplete="email" dir="ltr" value="<?= field_val($r, 'email') ?>"></label>
        </div>
        <div class="form__row">
          <label class="fld"><span><?= e(L('form_phone')) ?></span><input type="tel" name="phone" autocomplete="tel" dir="ltr" value="<?= field_val($r, 'phone') ?>"></label>
          <label class="fld"><span><?= e(L('form_subject')) ?></span><input type="text" name="subject" value="<?= field_val($r, 'subject') ?>"></label>
        </div>
        <label class="fld<?= field_err($r, 'message') ?>"><span><?= e(L('form_message')) ?> *</span><textarea name="message" rows="6" required><?= field_val($r, 'message') ?></textarea></label>
        <button class="btn btn--primary" type="submit"><?= e(L('form_send')) ?> <?= icon('arrow', 'flip') ?></button>
      </form>
    </div>
    <aside class="contact__side">
      <div class="boxcard boxcard--navy">
        <ul class="clist clist--light">
          <li><?= icon('phone') ?><span><b><?= e(L('call_us')) ?></b><a href="tel:<?= e($s['phone_link']) ?>"><bdi dir="ltr"><?= e($s['phone_display']) ?></bdi></a></span></li>
          <li><?= icon('mail') ?><span><b><?= e(L('email_us')) ?></b><a href="mailto:<?= e($s['email']) ?>"><?= e($s['email']) ?></a></span></li>
          <li><?= icon('pin') ?><span><b><?= e(L('address')) ?></b><?= e(t($s['address'])) ?><br><?= e(t($s['city'])) ?></span></li>
          <li><?= icon('clock') ?><span><b><?= e(L('working_hours')) ?></b><?= e(t($s['hours_week'])) ?><br><?= e(t($s['hours_sat'])) ?></span></li>
        </ul>
      </div>
      <div class="contact__map"><iframe title="Map" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="https://maps.google.com/maps?q=<?= $mapQ ?>&z=15&hl=<?= lang() ?>&output=embed"></iframe></div>
    </aside>
  </div>
</section>
<?php page_end();
