/* ETCO admin panel helpers */
(function () {
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };

  /* Confirm before deleting */
  $$('form[data-confirm]').forEach(function (f) {
    f.addEventListener('submit', function (e) { if (!confirm(f.getAttribute('data-confirm'))) e.preventDefault(); });
  });

  /* Warn about unsaved changes */
  $$('form[data-dirty]').forEach(function (f) {
    var dirty = false, bar = f.querySelector('.savebar');
    var mark = function () { if (!dirty) { dirty = true; bar && bar.classList.add('is-dirty'); } };
    f.addEventListener('input', mark); f.addEventListener('change', mark);
    f.addEventListener('submit', function () { dirty = false; var b = f.querySelector('.savebar .btn--primary'); if (b) { b.disabled = false; b.innerHTML = 'Saving…'; } });
    window.addEventListener('beforeunload', function (e) { if (dirty) { e.preventDefault(); e.returnValue = ''; } });
  });

  /* Auto-growing text areas */
  var grow = function (t) { t.style.height = 'auto'; t.style.height = Math.min(t.scrollHeight + 2, 700) + 'px'; };
  $$('textarea[data-autogrow]').forEach(function (t) { grow(t); t.addEventListener('input', function () { grow(t); }); });

  /* Live search in lists */
  $$('[data-search]').forEach(function (inp) {
    inp.addEventListener('input', function () {
      var q = inp.value.toLowerCase().trim();
      $$(inp.getAttribute('data-search')).forEach(function (row) {
        var txt = (row.getAttribute('data-text') || row.textContent).toLowerCase();
        row.style.display = !q || txt.indexOf(q) > -1 ? '' : 'none';
      });
    });
  });

  /* Shrink big photos in the browser before uploading (keeps uploads fast and under server limits) */
  var MAX = 2400;
  function shrink(file) {
    return new Promise(function (resolve) {
      if (!/^image\/(jpeg|png|webp)$/.test(file.type) || file.size < 900 * 1024) return resolve(file);
      var img = new Image(), url = URL.createObjectURL(file);
      img.onload = function () {
        var k = Math.min(1, MAX / Math.max(img.width, img.height));
        if (k === 1 && file.size < 2.5 * 1024 * 1024) { URL.revokeObjectURL(url); return resolve(file); }
        var c = document.createElement('canvas'); c.width = Math.round(img.width * k); c.height = Math.round(img.height * k);
        var ctx = c.getContext('2d');
        if (file.type === 'image/png') { ctx.clearRect(0, 0, c.width, c.height); } else { ctx.fillStyle = '#fff'; ctx.fillRect(0, 0, c.width, c.height); }
        ctx.drawImage(img, 0, 0, c.width, c.height);
        var type = file.type === 'image/png' ? 'image/png' : 'image/jpeg';
        c.toBlob(function (b) {
          URL.revokeObjectURL(url);
          if (!b || b.size >= file.size) return resolve(file);
          var name = file.name.replace(/\.(png|webp|jpe?g)$/i, '') + (type === 'image/png' ? '.png' : '.jpg');
          resolve(new File([b], name, { type: type }));
        }, type, 0.85);
      };
      img.onerror = function () { resolve(file); };
      img.src = url;
    });
  }

  $$('input[type=file]').forEach(function (inp) {
    inp.addEventListener('change', function () {
      var files = Array.prototype.slice.call(inp.files || []);
      var box = inp.closest('[data-imgf]'), wrap = inp.closest('.fl__in, .bulk__row');
      var nameEl = (box || wrap || document).querySelector('[data-name]');
      if (!files.length) return;
      if (nameEl) nameEl.textContent = files.length > 1 ? files.length + ' files selected' : files[0].name;
      if (box && /^image\//.test(files[0].type)) {
        var prev = box.querySelector('[data-prev]');
        prev.innerHTML = '<span class="pv"><img alt=""></span>';
        prev.querySelector('img').src = URL.createObjectURL(files[0]);
        box.classList.add('is-new');
      }
      if (wrap && inp.hasAttribute('data-multi')) {
        var list = wrap.querySelector('[data-mimgs]');
        if (list) files.forEach(function (f) { if (!/^image\//.test(f.type)) return; var d = document.createElement('div'); d.className = 'mimg is-pending'; d.innerHTML = '<span class="pv"><img alt=""></span>'; d.querySelector('img').src = URL.createObjectURL(f); d.title = 'Will be added when you save'; list.appendChild(d); });
      }
      if (inp.hasAttribute('data-resize') && window.DataTransfer) {
        Promise.all(files.map(shrink)).then(function (out) {
          try { var dt = new DataTransfer(); out.forEach(function (f) { dt.items.add(f); }); inp.files = dt.files; } catch (e) {}
        });
      }
    });
  });

  /* Multi-photo field: remove + drag to reorder */
  $$('[data-mimgs]').forEach(function (list) {
    list.addEventListener('click', function (e) {
      var x = e.target.closest('[data-mimg-del]');
      if (x) { x.closest('.mimg').remove(); list.dispatchEvent(new Event('change', { bubbles: true })); }
    });
    dragSort(list, '.mimg', function () { list.dispatchEvent(new Event('change', { bubbles: true })); });
  });

  /* Gallery: drag to reorder, then save automatically */
  $$('[data-sortable]').forEach(function (grid) {
    dragSort(grid, '.gtile', function () {
      var ids = $$('.gtile', grid).map(function (t) { return t.getAttribute('data-id'); });
      var fd = new FormData(); fd.append('_csrf', grid.getAttribute('data-csrf')); fd.append('order', ids.join(','));
      fetch(grid.getAttribute('data-sort-url'), { method: 'POST', body: fd, credentials: 'same-origin' }).then(function () { toast('Order saved'); });
    });
  });

  function dragSort(container, sel, done) {
    var dragged = null;
    container.addEventListener('dragstart', function (e) { var it = e.target.closest(sel); if (!it) return; dragged = it; it.classList.add('is-drag'); e.dataTransfer.effectAllowed = 'move'; try { e.dataTransfer.setData('text/plain', ''); } catch (x) {} });
    container.addEventListener('dragover', function (e) {
      var over = e.target.closest(sel); if (!dragged || !over || over === dragged) return; e.preventDefault();
      var r = over.getBoundingClientRect(), after = (e.clientX - r.left) > r.width / 2;
      container.insertBefore(dragged, after ? over.nextSibling : over);
    });
    container.addEventListener('dragend', function () { if (dragged) { dragged.classList.remove('is-drag'); dragged = null; done && done(); } });
  }

  function toast(msg) {
    var t = document.createElement('div'); t.className = 'flash flash--ok'; t.textContent = msg;
    t.style.cssText = 'position:fixed;bottom:20px;right:20px;z-index:99;box-shadow:0 10px 30px rgba(0,0,0,.15)';
    document.body.appendChild(t); setTimeout(function () { t.remove(); }, 2200);
  }
})();
