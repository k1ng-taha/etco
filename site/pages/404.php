<?php
http_response_code(404);
page_start(['page' => 'home', 'title' => L('not_found_title')]);
?>
<section class="sec nf">
  <div class="wrap narrow center">
    <p class="nf__code">404</p>
    <h1 class="h-xl"><?= e(L('not_found_title')) ?></h1>
    <p class="lead"><?= e(L('not_found_text')) ?></p>
    <a class="btn btn--primary" href="<?= e(url('home')) ?>"><?= e(L('back_home')) ?></a>
  </div>
</section>
<?php page_end();
