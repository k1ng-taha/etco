/* ETCO – public website interactions (no libraries needed) */
(function () {
  document.documentElement.classList.add('js');
  var $ = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };

  /* Header turns solid when scrolling over the hero */
  var hdr = $('#hdr');
  if (hdr && hdr.classList.contains('hdr--over')) {
    var onScroll = function () { hdr.classList.toggle('is-solid', window.scrollY > 30); };
    onScroll(); window.addEventListener('scroll', onScroll, { passive: true });
  }

  /* Mobile menu */
  var nav = $('#nav'), overlay = $('.nav-overlay');
  function setNav(open) { if (!nav) return; nav.classList.toggle('is-open', open); overlay && overlay.classList.toggle('is-open', open); document.body.style.overflow = open ? 'hidden' : ''; }
  $$('[data-open-nav]').forEach(function (b) { b.addEventListener('click', function () { setNav(true); }); });
  $$('[data-close-nav]').forEach(function (b) { b.addEventListener('click', function () { setNav(false); }); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { setNav(false); closeLang(); closeLb(); } });
  $$('.has-sub > .nav__link').forEach(function (a) {
    a.addEventListener('click', function (e) {
      if (window.matchMedia('(max-width:1024px)').matches) { e.preventDefault(); a.parentNode.classList.toggle('is-open'); }
    });
  });

  /* Language dropdown */
  var lang = $('[data-lang]');
  function closeLang() { if (lang) { lang.classList.remove('is-open'); $('button', lang).setAttribute('aria-expanded', 'false'); } }
  if (lang) {
    $('button', lang).addEventListener('click', function (e) { e.stopPropagation(); var o = !lang.classList.contains('is-open'); lang.classList.toggle('is-open', o); this.setAttribute('aria-expanded', o); });
    document.addEventListener('click', function (e) { if (!lang.contains(e.target)) closeLang(); });
  }

  /* Hero slideshow */
  var hero = $('[data-hero]');
  if (hero) {
    var slides = $$('.hero__slide', hero), texts = $$('[data-slide-text]', hero), dots = $$('.hero__dot', hero), cur = 0, timer;
    var go = function (i) {
      if (!slides.length) return;
      cur = (i + slides.length) % slides.length;
      [slides, texts, dots].forEach(function (list) { list.forEach(function (el, k) { el.classList.toggle('is-on', k === cur); if (k === cur) { el.style.animation = 'none'; el.offsetHeight; el.style.animation = ''; } }); });
      clearTimeout(timer); timer = setTimeout(function () { go(cur + 1); }, 7000);
    };
    dots.forEach(function (d) { d.addEventListener('click', function () { go(+d.dataset.go); }); });
    if (slides.length > 1) timer = setTimeout(function () { go(1); }, 7000);
  }

  /* Featured projects rail */
  var rail = $('[data-rail]');
  if (rail) {
    var step = function (dir) { var card = $('.pcard', rail); var w = card ? card.getBoundingClientRect().width + 24 : 380; var rtl = document.dir === 'rtl' ? -1 : 1; rail.scrollBy({ left: dir * w * rtl, behavior: 'smooth' }); };
    var p = $('[data-rail-prev]'), n = $('[data-rail-next]');
    p && p.addEventListener('click', function () { step(-1); });
    n && n.addEventListener('click', function () { step(1); });
  }

  /* Tabs (Projects / Photo gallery) */
  $$('[data-tabs]').forEach(function (tabs) {
    var btns = $$('[data-tab]', tabs);
    var show = function (name) {
      btns.forEach(function (b) { b.classList.toggle('is-on', b.dataset.tab === name); });
      $$('[data-pane]').forEach(function (p) { p.classList.toggle('is-on', p.dataset.pane === name); });
    };
    btns.forEach(function (b) { b.addEventListener('click', function () { show(b.dataset.tab); history.replaceState(null, '', b.dataset.tab === 'gallery' ? '#gallery' : location.pathname + location.search); }); });
    if (location.hash === '#gallery') show('gallery');
  });

  /* Filter chips */
  $$('[data-filter]').forEach(function (bar) {
    var sel = bar.getAttribute('data-filter');
    $$('.chip', bar).forEach(function (c) {
      c.addEventListener('click', function () {
        $$('.chip', bar).forEach(function (x) { x.classList.toggle('is-on', x === c); });
        var f = c.dataset.f;
        $$(sel).forEach(function (it) { it.classList.toggle('is-hidden', f !== '*' && it.dataset.cat !== f); });
      });
    });
  });

  /* Lightbox */
  var lb = $('#lightbox'), lbItems = [], lbIdx = 0;
  function openLb(items, i) { lbItems = items; lbIdx = i; renderLb(); lb.hidden = false; document.body.style.overflow = 'hidden'; }
  function renderLb() { var a = lbItems[lbIdx]; $('img', lb).src = a.getAttribute('href'); $('img', lb).alt = a.dataset.caption || ''; $('figcaption', lb).textContent = a.dataset.caption || ''; }
  function closeLb() { if (lb && !lb.hidden) { lb.hidden = true; document.body.style.overflow = ''; } }
  function moveLb(d) { var vis = lbItems; lbIdx = (lbIdx + d + vis.length) % vis.length; renderLb(); }
  if (lb) {
    document.addEventListener('click', function (e) {
      var a = e.target.closest && e.target.closest('[data-lightbox]');
      if (!a) return;
      e.preventDefault();
      var group = a.closest('[data-lightbox-group]');
      var items = group ? $$('[data-lightbox]', group).filter(function (x) { return !x.classList.contains('is-hidden'); }) : [a];
      openLb(items, Math.max(0, items.indexOf(a)));
    });
    $('.lightbox__close', lb).addEventListener('click', closeLb);
    $('.lightbox__prev', lb).addEventListener('click', function () { moveLb(document.dir === 'rtl' ? 1 : -1); });
    $('.lightbox__next', lb).addEventListener('click', function () { moveLb(document.dir === 'rtl' ? -1 : 1); });
    lb.addEventListener('click', function (e) { if (e.target === lb) closeLb(); });
    document.addEventListener('keydown', function (e) { if (lb.hidden) return; if (e.key === 'ArrowRight') moveLb(1); if (e.key === 'ArrowLeft') moveLb(-1); });
  }

  /* "Apply" buttons fill in the position field */
  $$('[data-apply]').forEach(function (b) { b.addEventListener('click', function () { var f = $('input[name="position"]'); if (f) f.value = b.dataset.apply; }); });

  /* Count-up numbers */
  var counters = $$('[data-count]');
  var animateCount = function (el) {
    var raw = el.textContent.trim(), num = parseInt(raw.replace(/[^0-9]/g, ''), 10);
    if (!num || num < 10 || !/^[0-9,]+$/.test(raw)) return;
    var comma = raw.indexOf(',') > -1, t0 = null, dur = 1400;
    var fmt = function (v) { return comma ? v.toLocaleString('en-US') : String(v); };
    var tick = function (ts) { if (!t0) t0 = ts; var k = Math.min(1, (ts - t0) / dur); el.textContent = fmt(Math.round(num * (1 - Math.pow(1 - k, 3)))); if (k < 1) requestAnimationFrame(tick); };
    requestAnimationFrame(tick);
  };

  /* Reveal on scroll */
  var rvTargets = $$('.stitle, .scard, .pgrid .pcard, .vcard, .srow, .region, .stat, .mv__item, .commit__item, .story__img, .intro__grid > *');
  if ('IntersectionObserver' in window) {
    rvTargets.forEach(function (el) { el.classList.add('rv'); });
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (!en.isIntersecting) return;
        en.target.classList.add('is-in');
        var c = en.target.querySelector && en.target.querySelector('[data-count]');
        if (c) animateCount(c);
        io.unobserve(en.target);
      });
    }, { rootMargin: '0px 0px -8% 0px' });
    rvTargets.forEach(function (el) { io.observe(el); });
  }
})();
