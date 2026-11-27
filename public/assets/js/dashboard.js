(function () {
  'use strict';
  var $ = function (s, el) { return (el || document).querySelector(s); };
  var $$ = function (s, el) { return Array.prototype.slice.call((el || document).querySelectorAll(s)); };
  var clamp = function (v) { return Math.max(0, Math.min(1, v)); };
  var out = function (t) { return 1 - Math.pow(1 - clamp(t), 3); };
  var capture = /[?&]capture=1/.test(location.search);
  var calm = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  var holos = $$('canvas.holo').map(function (c) { return { el: c, moto: new NovaMoto(c) }; });
  var timer = $('#timer'), jobbar = $('#jobbar'), booted = false;
  var customerDashboard = $('.customer-dashboard[data-status-url]');
  var statusCheckPending = false;

  $$('.customer-payment-form').forEach(function (form) {
    $$('input[name="metode_pembayaran"]', form).forEach(function (input) {
      input.addEventListener('change', function () {
        if (input.checked) form.requestSubmit();
      });
    });
  });

  function checkCustomerStatus() {
    if (!customerDashboard || document.hidden || statusCheckPending) return;
    statusCheckPending = true;
    fetch(customerDashboard.dataset.statusUrl, {
      credentials: 'same-origin',
      headers: { Accept: 'application/json' },
      cache: 'no-store'
    }).then(function (response) {
      if (!response.ok) throw new Error('Status servis gagal diperiksa: HTTP ' + response.status);
      return response.json();
    }).then(function (status) {
      var bookingId = status.bookingId == null ? '' : String(status.bookingId);
      var bookingStatus = status.status || '';
      var updatedAt = status.updatedAt == null ? '' : String(status.updatedAt);
      var estimateBookingId = status.estimateBookingId == null ? '' : String(status.estimateBookingId);
      var estimateUpdatedAt = status.estimateUpdatedAt == null ? '' : String(status.estimateUpdatedAt);
      if (bookingId !== customerDashboard.dataset.currentBookingId ||
          bookingStatus !== customerDashboard.dataset.currentBookingStatus ||
          updatedAt !== customerDashboard.dataset.currentBookingUpdatedAt ||
          estimateBookingId !== customerDashboard.dataset.estimateBookingId ||
          estimateUpdatedAt !== customerDashboard.dataset.estimateBookingUpdatedAt) {
        window.location.reload();
      }
    }).catch(function (error) {
      console.error(error);
    }).then(function () {
      statusCheckPending = false;
    });
  }

  if (customerDashboard && !capture) {
    window.setInterval(checkCustomerStatus, 10000);
    document.addEventListener('visibilitychange', function () {
      if (!document.hidden) checkCustomerStatus();
    });
  }

  function drawHolos(t) {
    holos.forEach(function (h) {
      var w = h.moto.w, ht = h.moto.h, hl = {};
      hl[h.el.dataset.part] = 1;
      h.moto.draw({ yaw: (-160 + (calm ? 0 : Math.sin(t * .5) * 14)) * Math.PI / 180, pitch: 10 * Math.PI / 180,
        scale: Math.min(w / 5.1, ht / 3.05), cx: w / 2, cy: ht * .54, tx: 0, ty: 1.15, tz: 0, hl: hl, spin: -t * .6, scan: null }, t);
    });
  }

  /* animasi masuk halaman, dihitung dari waktu (detik) */
  function frame(t) {
    var T = calm ? 9 : t;
    $$('.tile, .panel').forEach(function (el, i) {
      var e = out((T - i * .09) / .7);
      el.style.opacity = e; el.style.transform = e < 1 ? 'translateY(' + (1 - e) * 22 + 'px)' : 'none';
    });
    $$('[data-count]').forEach(function (el, i) {
      var n = el.dataset.count * out((T - .15 - i * .07) / 1.2), d = +(el.dataset.dec || 0);
      if (!el.dataset.fixed) el.textContent = (el.dataset.prefix || '') + n.toLocaleString('id-ID', { minimumFractionDigits: d, maximumFractionDigits: d }) + (el.dataset.suffix || '');
    });
    var g = out((T - .5) / 1.3);
    $$('[data-h]').forEach(function (el, i) { el.style.height = el.dataset.h * out((T - .45 - i * .08) / 1) + '%'; });
    $$('[data-w]').forEach(function (el) { if (el !== jobbar || !booted) el.style.width = el.dataset.w * g + '%'; });
    if (T > 2) booted = true;
    if (timer) {
      var s = Math.floor(+timer.dataset.start + t), two = function (n) { return (n < 10 ? '0' : '') + n; };
      if (!timer.dataset.stop) timer.textContent = two(Math.floor(s / 3600)) + ':' + two(Math.floor(s / 60) % 60) + ':' + two(s % 60);
    }
    drawHolos(t);
  }

  /* interaksi contoh (belum tersambung ke database) */
  var toastTimer;
  function toast(msg) {
    var el = $('.toast'); el.textContent = msg; el.classList.add('show');
    clearTimeout(toastTimer); if (!capture) toastTimer = setTimeout(function () { el.classList.remove('show'); }, 2600);
  }
  $$('[data-toast]').forEach(function (b) { b.addEventListener('click', function () { toast(b.dataset.toast); }); });
  $$('[data-assign]').forEach(function (b) {
    b.addEventListener('click', function () {
      var row = b.closest('tr'), name = b.dataset.assign, plat = $('td b', row).textContent;
      b.parentNode.textContent = name;
      row.lastElementChild.innerHTML = '<span class="status run"><i>▸</i>Ditugaskan</span>';
      var w = $('#waiting'); w.dataset.count = Math.max(0, w.dataset.count - 1); w.textContent = w.dataset.count;
      toast(plat + ' ditugaskan ke ' + name);
    });
  });
  var checks = $$('.checks input');
  function progress() {
    var n = checks.filter(function (c) { return c.checked; }).length;
    jobbar.style.width = (n / checks.length * 100) + '%';
    $('#jobtext').textContent = n + ' dari ' + checks.length + ' langkah';
  }
  checks.forEach(function (c) { c.addEventListener('change', progress); });
  var finish = $('#finish');
  if (finish) finish.addEventListener('click', function () {
    var left = checks.filter(function (c) { return !c.checked; }).length;
    if (left) return toast('Selesaikan ' + left + ' langkah lagi sebelum menandai selesai');
    timer.dataset.stop = 1; finish.textContent = 'Selesai'; finish.disabled = true;
    $('.job .status').outerHTML = '<span class="status good"><i>✓</i>Selesai</span>';
    toast('Pekerjaan ditandai selesai');
  });
  var tableBtn = $('[data-table]');
  if (tableBtn) tableBtn.addEventListener('click', function () {
    var alt = $('.alt'), show = alt.hidden;
    alt.hidden = !show; $('.columns').style.display = show ? 'none' : '';
    tableBtn.textContent = show ? 'Lihat grafik' : 'Lihat tabel'; tableBtn.setAttribute('aria-pressed', show);
  });
  var tip = document.createElement('div'); tip.className = 'tip'; document.body.appendChild(tip);
  $$('[data-tip]').forEach(function (el) {
    function show() {
      var m = $('i', el).getBoundingClientRect();
      tip.textContent = el.dataset.tip; tip.style.left = Math.min(window.innerWidth - 120, Math.max(120, m.left + m.width / 2)) + 'px'; tip.style.top = m.top + 'px'; tip.classList.add('show');
    }
    function hide() { tip.classList.remove('show'); }
    el.addEventListener('mouseenter', show); el.addEventListener('focus', show);
    el.addEventListener('mouseleave', hide); el.addEventListener('blur', hide);
  });

  document.fonts.ready.then(function () {
    window.addEventListener('resize', function () { holos.forEach(function (h) { h.moto.resize(); }); });
    holos.forEach(function (h) { h.moto.resize(); });
    if (capture) { window.NV = { frame: frame, click: function (s, i) { $$(s)[i || 0].click(); }, toast: toast }; frame(0); return; }
    var start = null;
    (function loop(now) {
      if (now !== undefined) { if (start === null) start = now; frame((now - start) / 1000); }
      requestAnimationFrame(loop);
    })();
  });
})();
