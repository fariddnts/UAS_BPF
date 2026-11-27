/* Semua animasi halaman depan dihitung dari posisi scroll + waktu. */
(function () {
  'use strict';
  var $ = function (s, el) { return (el || document).querySelector(s); };
  var $$ = function (s, el) { return Array.prototype.slice.call((el || document).querySelectorAll(s)); };
  var clamp = function (v, a, b) { return Math.max(a === undefined ? 0 : a, Math.min(b === undefined ? 1 : b, v)); };
  var lerp = function (a, b, t) { return a + (b - a) * t; };
  var ease = function (t) { t = clamp(t); return t * t * (3 - 2 * t); };
  var out = function (t) { return 1 - Math.pow(1 - clamp(t), 3); };
  var RAD = Math.PI / 180;

  var capture = /[?&]capture=1/.test(location.search);
  var calm = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var moto = new NovaMoto($('#moto'));
  var nav = $('.nav'), bar = $('.nav-progress i');
  var scan = $('.scan'), flow = $('.flow'), finale = $('.finale');
  var stages = $$('.stages li'), reads = $$('.readings article'), pin = $('.pin'), intro = $('.scan-intro');
  var stageBox = $('.stages'), readBox = $('.readings');
  var track = $('.track'), flowI = $('.flow-line i'), flowB = $('.flow-line b'), flowLine = $('.flow-line');
  var steps = $$('.step');
  var lines = $$('.ln > span'), fades = $$('.hero .fade');
  var M = {};

  function measure() {
    var y = window.scrollY;
    M.vw = document.documentElement.clientWidth; M.vh = window.innerHeight;
    var top = function (el) { return el.getBoundingClientRect().top + y; };
    M.scanTop = top(scan); M.scanLen = scan.offsetHeight - M.vh;
    M.flowTop = top(flow); M.flowLen = flow.offsetHeight - M.vh;
    M.finTop = top(finale); M.finLen = finale.offsetHeight - M.vh;
    M.max = document.documentElement.scrollHeight - M.vh;
    M.trackX = Math.max(0, track.scrollWidth - M.vw);
    moto.resize();
  }

  /* posisi kamera: [yaw, pitch, zoom, tx, ty, cx, cy] */
  function keys() {
    var m = M.vw < 760, wide = !m && M.vw / M.vh > 1.15;
    var S = m ? M.vw / 5 : Math.min(M.vw / 8.4, M.vh / 5.1);
    return {
      S: S,
      k: [
        [-150, 9, wide ? 1.09 : .96, 0, 1.15, m ? .5 : .715, m ? .76 : .6],
        [-180, 4, m ? .98 : 1.1, 0, 1.15, .5, m ? .42 : .6],
        [-163, 12, m ? 1.45 : 1.36, .2, .95, .5, m ? .37 : .66],
        [-166, 7, m ? 1.45 : 1.34, 1.1, .9, .5, m ? .37 : .66],
        [-208, 12, m ? 1.02 : 1.14, -.15, 1.0, .5, m ? .37 : .61],
        [-232, 32, m ? 1.0 : 1.08, -.1, 1.1, .5, m ? .38 : .62]
      ]
    };
  }
  function mix(a, b, t) { return a.map(function (v, i) { return lerp(v, b[i], t); }); }

  var PARTS = ['mesin', 'rem', 'kaki', 'listrik'];
  var INTRO = .12, SEG = (1 - INTRO) / 4;

  function render(y, t) {
    var K = keys(), vw = M.vw, vh = M.vh;
    nav.classList.toggle('solid', y > 30);
    bar.style.transform = 'scaleX(' + clamp(y / M.max) + ')';

    /* hero: animasi masuk */
    var boot = calm ? 1 : clamp(t / 2.2);
    lines.forEach(function (el, i) { el.style.transform = 'translateY(' + (1 - out((t - .15 - i * .14) / .9)) * 112 + '%)'; });
    fades.forEach(function (el, i) { var e = calm ? 1 : ease((t - .8 - i * .18) / .8); el.style.opacity = e * (1 - clamp(y / (vh * .35))); });
    $('.hero h1').style.opacity = 1 - clamp((y - vh * .2) / (vh * .45));

    /* motor: hero -> pindai */
    var toScan = ease(y / M.scanTop);
    var p = clamp((y - M.scanTop) / M.scanLen);
    var cam = mix(K.k[0], K.k[1], toScan);
    var hl = { mesin: 0, rem: 0, kaki: 0, listrik: 0 };
    var active = -1, local = 0;
    if (p > INTRO) {
      active = Math.min(3, Math.floor((p - INTRO) / SEG));
      local = (p - INTRO - active * SEG) / SEG;
      var move = ease(local / .42);
      cam = mix(K.k[1 + active], K.k[2 + active], move);
      hl[PARTS[active]] = ease((local - .12) / .3);
      if (active > 0) hl[PARTS[active - 1]] = 1 - ease(local / .22);
    }

    hl.rembelakang = hl.rem;
    var idle = calm ? 0 : Math.sin(t * .45) * 5 * (1 - toScan);
    var scanX = null;
    if (y < M.scanTop * .85 && t > 1.4 && !calm) { var c = ((t - 1.4) % 5.5) / 2.6; if (c < 1) scanX = lerp(2.5, -2.5, ease(c)); }
    if (p > .002 && p < INTRO) scanX = lerp(2.5, -2.5, p / INTRO);

    var view = {
      yaw: (cam[0] + idle - (1 - out(boot)) * 55) * RAD, pitch: (cam[1] + (1 - out(boot)) * 16) * RAD,
      scale: K.S * cam[2] * (.86 + .14 * out(boot)), tx: cam[3], ty: cam[4], tz: 0,
      cx: cam[5] * vw, cy: cam[6] * vh, hl: hl, good: { mesin: true, kaki: true, rembelakang: true }, scan: scanX,
      spin: calm ? 0 : -(t * .25 + y * .002), alpha: calm ? 1 : ease(t / 1.2), floor: 1
    };

    /* motor memudar saat keluar dari bagian pindai */
    var leave = clamp((y - (M.scanTop + M.scanLen)) / (vh * .7));
    view.alpha *= 1 - leave;

    /* penutup: motor melaju masuk */
    var fin = clamp((y - (M.finTop - vh * .55)) / (M.finLen + vh * .45));
    if (y > M.finTop - vh * .75) {
      var arrive = out(fin / .85);
      var cx = lerp(-.45, .5, arrive) * vw;
      view = {
        yaw: lerp(-8, -34, ease((fin - .45) / .5)) * RAD, pitch: 8 * RAD, scale: K.S,
        tx: 0, ty: 1.0, tz: 0, cx: cx, cy: vh * (M.vw < 760 ? .72 : .745), hl: {}, scan: null,
        spin: -(cx / (K.S * .63)), floor: ease((fin - .55) / .4), alpha: ease((y - (M.finTop - vh * .6)) / (vh * .4))
      };
    }
    moto.draw(view, calm ? 0 : t);

    /* teks bagian pindai */
    var show = ease((y - M.scanTop * .55) / (M.scanTop * .45)) * (1 - clamp(leave * 2));
    intro.style.opacity = show;
    intro.style.transform = 'translateY(' + (1 - show) * 30 + 'px)';
    stageBox.style.opacity = show;
    readBox.style.opacity = 1 - clamp(leave * 2);
    stages.forEach(function (li, i) {
      var done = i < active ? 1 : i === active ? local : 0;
      li.style.setProperty('--p', done.toFixed(3));
      li.style.opacity = i === active ? 1 : .6;
    });
    var pinOn = 0;
    reads.forEach(function (card, i) {
      var vis = 0;
      if (i === active) vis = ease((local - .2) / .22) * (i === 3 ? 1 : 1 - ease((local - .9) / .1));
      card.style.opacity = vis;
      card.style.visibility = vis > .01 ? 'visible' : 'hidden';
      card.style.transform = 'translateX(' + (1 - vis) * 36 + 'px)';
      if (i === active) {
        var n = out((local - .22) / .36);
        $$('dd', card).forEach(function (dd) {
          var dec = +(dd.dataset.dec || 0);
          dd.textContent = (dd.dataset.count * n).toLocaleString('id-ID', { minimumFractionDigits: dec, maximumFractionDigits: dec }) + (dd.dataset.suffix || '');
        });
        var a = card.dataset.anchor.split(',').map(Number), s = moto.project(a);
        pinOn = ease((local - .45) / .2) * (1 - ease((local - .9) / .1)) * (1 - leave);
        pin.style.transform = 'translate(' + s[0] + 'px,' + s[1] + 'px)';
      }
    });
    pin.style.opacity = pinOn;

    /* alur servis: geser horizontal */
    var f = clamp((y - M.flowTop) / M.flowLen);
    var fx = ease(clamp((f - .06) / .86)) * .35 + clamp((f - .06) / .86) * .65;
    track.style.transform = 'translate3d(' + (-fx * M.trackX) + 'px,0,0)';
    var enter = clamp((y - (M.flowTop - vh * .7)) / (vh * .7));
    var line = clamp(enter * .12 + f * .9);
    flowI.style.transform = 'scaleX(' + line + ')';
    flowB.style.left = (line * flowLine.offsetWidth) + 'px';
    steps.forEach(function (step) {
      var r = step.getBoundingClientRect();
      var cp = clamp((vw * 1.02 - r.left) / (Math.min(vw * .62, r.width * 1.4))) * enter;
      $$('[data-at]', step).forEach(function (el) {
        var e = ease((cp - el.dataset.at) / .16);
        el.style.opacity = e; el.style.transform = 'translateY(' + (1 - e) * 14 + 'px)';
      });
      var m = $('[data-fill]', step);
      if (m) m.style.width = (ease((cp - .15) / .75) * m.dataset.fill) + '%';
    });

  }

  var smooth = window.scrollY, start = null;
  function loop(now) {
    if (start === null) start = now;
    smooth = calm ? window.scrollY : lerp(smooth, window.scrollY, .14);
    if (Math.abs(smooth - window.scrollY) < .4) smooth = window.scrollY;
    render(smooth, (now - start) / 1000);
    requestAnimationFrame(loop);
  }

  document.fonts.ready.then(function () {
    measure();
    window.addEventListener('resize', measure);
    window.addEventListener('load', measure);
    if (capture) {
      document.documentElement.style.scrollBehavior = 'auto';
      window.NV = { measure: measure, info: function () { return M; }, frame: function (y, t) { window.scrollTo(0, y); render(y, t); } };
      window.NV.frame(0, 0);
    } else requestAnimationFrame(loop);
  });
})();
