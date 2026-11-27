/*
 * Hologram motor 3D di <canvas>, tanpa library.
 * Pemakaian: const moto = new NovaMoto(canvas); moto.draw(view, waktuDetik);
 * view = { yaw, pitch, scale, cx, cy, tx, ty, tz, hl:{mesin,rem,rembelakang,kaki,listrik}, good:{mesin:true}, scan, spin, floor, alpha }
 * Sumbu: x = depan, y = atas, z = kanan pengendara.
 */
(function () {
  'use strict';

  var CYAN = [82, 224, 255], ICE = [200, 220, 255], AMBER = [251, 179, 21], GREEN = [70, 240, 140];
  var TAU = Math.PI * 2, R = .63, FX = 1.4, RX = -1.4;
  function rgba(c, a) { return 'rgba(' + c[0] + ',' + c[1] + ',' + c[2] + ',' + Math.max(0, Math.min(1, a)).toFixed(3) + ')'; }
  function lerp(a, b, t) { return a + (b - a) * t; }
  function mid(a, b, t) { return [lerp(a[0], b[0], t), lerp(a[1], b[1], t), lerp(a[2], b[2], t)]; }
  function closed(r) { return r.concat([r[0]]); }

  // interpolasi halus (Catmull-Rom) dari titik kontrol [x, nilai]
  function curve(pts) {
    return function (x) {
      var n = pts.length, i = 0;
      if (x <= pts[0][0]) return pts[0][1];
      if (x >= pts[n - 1][0]) return pts[n - 1][1];
      while (x > pts[i + 1][0]) i++;
      var p1 = pts[i], p2 = pts[i + 1], p0 = pts[i - 1] || p1, p3 = pts[i + 2] || p2;
      var h = p2[0] - p1[0], t = (x - p1[0]) / h, t2 = t * t, t3 = t2 * t;
      var m1 = (p2[1] - p0[1]) / (p2[0] - p0[0]) * h, m2 = (p3[1] - p1[1]) / (p3[0] - p1[0]) * h;
      return (2 * t3 - 3 * t2 + 1) * p1[1] + (t3 - 2 * t2 + t) * m1 + (-2 * t3 + 3 * t2) * p2[1] + (t3 - t2) * m2;
    };
  }

  // lingkaran pada bidang x-y
  function circle(cx, cy, cz, r, n, a0, a1) {
    var out = [], s = a0 || 0, e = a1 === undefined ? TAU : a1;
    for (var i = 0; i <= n; i++) { var a = s + (e - s) * i / n; out.push([cx + r * Math.cos(a), cy + r * Math.sin(a), cz]); }
    return out;
  }
  function box(x0, x1, y0, y1, z0, z1) {
    var a = [x0, y0, z0], b = [x1, y0, z0], c = [x1, y0, z1], d = [x0, y0, z1],
        e = [x0, y1, z0], f = [x1, y1, z0], g = [x1, y1, z1], h = [x0, y1, z1];
    return [[a, b, c, d, a], [e, f, g, h, e], [a, e], [b, f], [c, g], [d, h]];
  }
  // dua vektor tegak lurus terhadap sumbu p0 -> p1
  function frame(p0, p1) {
    var d = [p1[0] - p0[0], p1[1] - p0[1], p1[2] - p0[2]], l = Math.hypot(d[0], d[1], d[2]);
    d = d.map(function (v) { return v / l; });
    var u = Math.abs(d[2]) > .95 ? [1, 0, 0] : [d[1], -d[0], 0], ul = Math.hypot(u[0], u[1], u[2]);
    u = u.map(function (v) { return v / ul; });
    var w = [d[1] * u[2] - d[2] * u[1], d[2] * u[0] - d[0] * u[2], d[0] * u[1] - d[1] * u[0]];
    return { u: u, w: w };
  }
  function around(p, f, r, a) {
    return [p[0] + r * (Math.cos(a) * f.u[0] + Math.sin(a) * f.w[0]), p[1] + r * (Math.cos(a) * f.u[1] + Math.sin(a) * f.w[1]), p[2] + r * (Math.cos(a) * f.u[2] + Math.sin(a) * f.w[2])];
  }
  function tube(p0, p1, r0, r1, n) {
    var f = frame(p0, p1), A = [], B = [], lines, faces = [], k;
    for (k = 0; k < n; k++) { A.push(around(p0, f, r0, k / n * TAU)); B.push(around(p1, f, r1, k / n * TAU)); }
    lines = [closed(A), closed(B)];
    for (k = 0; k < n; k++) {
      if (k % Math.ceil(n / 6) === 0) lines.push([A[k], B[k]]);
      faces.push([A[k], B[k], B[(k + 1) % n], A[(k + 1) % n]]);
    }
    return { lines: lines, faces: faces };
  }
  function helix(p0, p1, r, turns) {
    var f = frame(p0, p1), out = [], n = turns * 16;
    for (var i = 0; i <= n; i++) out.push(around(mid(p0, p1, i / n), f, r, i / n * turns * TAU));
    return out;
  }
  function extrude(profile, z0, z1) {
    var A = profile.map(function (p) { return [p[0], p[1], z0]; }), B = profile.map(function (p) { return [p[0], p[1], z1]; });
    var lines = [closed(A), closed(B)], faces = [A, B];
    A.forEach(function (a, i) {
      var j = (i + 1) % A.length;
      lines.push([a, B[i]]); faces.push([a, A[j], B[j], B[i]]);
    });
    return { lines: lines, faces: faces };
  }
  // cangkang bodi: potongan superelips di sepanjang sumbu x
  function shell(x0, x1, step, top, bot, wide) {
    var xs = [], rings = [], faces = [], x, i, k, N = 12;
    var T = curve(top), B = curve(bot), W = curve(wide);
    for (x = x0; x < x1 - step * .4; x += step) xs.push(x);
    xs.push(x1);
    xs.forEach(function (x) {
      var m = (T(x) + B(x)) / 2, h = Math.max(.005, (T(x) - B(x)) / 2), w = W(x), ring = [];
      for (k = 0; k < N; k++) {
        var a = k / N * TAU, c = Math.cos(a), s = Math.sin(a);
        ring.push([x, m + h * (s < 0 ? -1 : 1) * Math.pow(Math.abs(s), .7), w * (c < 0 ? -1 : 1) * Math.pow(Math.abs(c), .7)]);
      }
      rings.push(ring);
    });
    for (i = 0; i < rings.length - 1; i++) for (k = 0; k < N; k++) faces.push([rings[i][k], rings[i + 1][k], rings[i + 1][(k + 1) % N], rings[i][(k + 1) % N]]);
    return { xs: xs, rings: rings, faces: faces };
  }
  function torus(cx, rt, nu, nv) {
    var faces = [], Rm = R - rt;
    function p(i, j) { var u = i / nu * TAU, v = j / nv * TAU, r = Rm + rt * Math.cos(v); return [cx + r * Math.cos(u), R + r * Math.sin(u), rt * Math.sin(v)]; }
    for (var i = 0; i < nu; i++) for (var j = 0; j < nv; j++) faces.push([p(i, j), p(i + 1, j), p(i + 1, j + 1), p(i, j + 1)]);
    return faces;
  }
  function arcStrip(cx, r, a0, a1, n, hw) {
    var cols = [[-hw, r - .04], [-hw * .5, r], [0, r + .015], [hw * .5, r], [hw, r - .04]], rows = [], faces = [];
    for (var i = 0; i <= n; i++) {
      var a = lerp(a0, a1, i / n), taper = .65 + .35 * Math.sin(Math.PI * i / n);
      rows.push(cols.map(function (c) { return [cx + c[1] * Math.cos(a), R + c[1] * Math.sin(a), c[0] * taper]; }));
    }
    for (i = 0; i < n; i++) for (var k = 0; k < 4; k++) faces.push([rows[i][k], rows[i + 1][k], rows[i + 1][k + 1], rows[i][k + 1]]);
    var col = function (k) { return rows.map(function (row) { return row[k]; }); };
    return { faces: faces, lines: [col(0), col(4), rows[0], rows[n]], ridge: col(2) };
  }

  function NovaMoto(canvas) {
    this.canvas = canvas;
    this.ctx = canvas.getContext('2d');
    this.build();
    this.resize();
  }

  NovaMoto.prototype.resize = function () {
    var dpr = Math.min(window.devicePixelRatio || 1, 2);
    var r = this.canvas.getBoundingClientRect();
    this.w = r.width; this.h = r.height;
    this.canvas.width = Math.round(r.width * dpr);
    this.canvas.height = Math.round(r.height * dpr);
    this.dpr = dpr;
  };

  /* geometri yang tidak berputar */
  NovaMoto.prototype.build = function () {
    var p = this.parts = { mesin: [], rem: [], rembelakang: [], kaki: [], listrik: [], sasis: [] };
    var faces = this.faces = [], self = this;
    function addFaces(list, tone) { list.forEach(function (f) { faces.push({ q: f, tone: tone }); }); }
    function solid(group, obj, tone) { p[group] = p[group].concat(obj.lines); if (tone) addFaces(obj.faces, tone); }

    /* bodi */
    this.shells = [
      shell(-.22, .88, .075, [[-.22, 1.52], [.05, 1.76], [.38, 1.93], [.66, 1.88], [.88, 1.66]], [[-.22, 1.40], [.15, 1.27], [.6, 1.31], [.88, 1.54]], [[-.22, .13], [.1, .30], [.42, .36], [.7, .29], [.88, .15]]),
      shell(-1.82, -.2, .09, [[-1.82, 1.80], [-1.42, 1.72], [-.95, 1.55], [-.55, 1.50], [-.2, 1.56]], [[-1.82, 1.73], [-1.4, 1.52], [-.9, 1.39], [-.5, 1.33], [-.2, 1.40]], [[-1.82, .04], [-1.4, .18], [-.9, .25], [-.5, .23], [-.2, .13]]),
      shell(.97, 1.31, .055, [[.97, 1.84], [1.11, 1.83], [1.23, 1.74], [1.31, 1.63]], [[.97, 1.50], [1.11, 1.39], [1.23, 1.43], [1.31, 1.55]], [[.97, .17], [1.1, .22], [1.23, .17], [1.31, .07]])
    ];
    this.shells.forEach(function (s) { addFaces(s.faces, 1.55); });
    var fender = arcStrip(FX, R + .085, .55, 2.3, 18, .19), hugger = arcStrip(RX, R + .09, .95, 2.15, 12, .2);
    addFaces(fender.faces, 1.3); addFaces(hugger.faces, 1);
    this.panels = [fender, hugger];
    this.shroud = [];
    [1, -1].forEach(function (s) {
      var q = [[.97, 1.56, .30 * s], [.56, 1.52, .40 * s], [.08, 1.26, .34 * s], [.30, .99, .36 * s], [.72, 1.10, .37 * s]];
      self.shroud.push(q); addFaces([q], 1.2);
    });
    addFaces(torus(FX, .125, 44, 8), .75); addFaces(torus(RX, .165, 44, 8), .75);

    /* mesin */
    solid('mesin', extrude([[-.36, .62], [-.14, .45], [.40, .43], [.66, .60], [.60, .90], [.30, 1.02], [-.10, .98], [-.36, .84]], -.2, .2), .55);
    [1, -1].forEach(function (s) {
      p.mesin.push(circle(.03, .71, .235 * s, .215, 32), circle(.03, .71, .26 * s, .135, 26), circle(.03, .71, .265 * s, .045, 12), circle(.42, .62, .225 * s, .09, 16));
      for (var k = 0; k < 6; k++) p.mesin.push(circle(.03 + .175 * Math.cos(k * TAU / 6), .71 + .175 * Math.sin(k * TAU / 6), .25 * s, .018, 8));
    });
    var c0 = [.26, .98], c1 = [.50, 1.38], ax = [c1[0] - c0[0], c1[1] - c0[1]], al = Math.hypot(ax[0], ax[1]), nx = ax[1] / al, ny = -ax[0] / al;
    for (var k = 0; k <= 7; k++) {
      var cx = lerp(c0[0], c1[0], k / 7), cy = lerp(c0[1], c1[1], k / 7), hw = k % 2 ? .15 : .19, zz = k % 2 ? .15 : .19;
      p.mesin.push(closed([[cx + nx * hw, cy + ny * hw, zz], [cx - nx * hw, cy - ny * hw, zz], [cx - nx * hw, cy - ny * hw, -zz], [cx + nx * hw, cy + ny * hw, -zz]]));
    }
    solid('mesin', extrude([[.34, 1.38], [.62, 1.26], [.68, 1.38], [.44, 1.50]], -.16, .16), .55);
    p.mesin = p.mesin.concat(box(.76, .82, .98, 1.30, -.22, .22), box(-.16, .12, 1.06, 1.22, -.13, .13));
    for (k = 1; k < 6; k++) p.mesin.push([[.82, .98 + k * .053, -.22], [.82, .98 + k * .053, .22]]);
    var header = [[.60, 1.27, .08], [.80, 1.12, .13], [.87, .86, .18], [.82, .62, .21], [.62, .45, .23], [.2, .37, .26], [-.3, .42, .29], [-.62, .56, .32], [-.86, .66, .34]];
    p.mesin.push(header, header.map(function (q) { return [q[0] - .045, q[1] + .045, q[2]]; }));

    /* knalpot */
    solid('sasis', tube([-.86, .68, .34], [-1.22, .82, .36], .06, .12, 14), .55);
    solid('sasis', tube([-1.22, .82, .36], [-1.88, 1.07, .38], .12, .135, 14), .7);

    /* rangka */
    [1, -1].forEach(function (s) {
      var z = function (x, y, w) { return [x, y, w * s]; };
      p.sasis.push([z(.88, 1.74, .08), z(.34, 1.32, .19), z(-.34, 1.04, .19), z(-.32, .66, .17)]);
      p.sasis.push([z(.92, 1.58, .07), z(.72, 1.0, .17), z(.50, .50, .18), z(-.28, .52, .17)]);
      p.sasis.push([z(.88, 1.74, .08), z(.72, 1.0, .17), z(.34, 1.32, .19), z(.12, 1.04, .19), z(-.34, 1.04, .19)]);
      p.sasis.push([z(-.2, 1.25, .16), z(-1.58, 1.68, .07)], [z(-.34, 1.04, .17), z(-1.22, 1.52, .1), z(-.75, 1.38, .13), z(-.34, 1.04, .17)]);
      p.sasis.push([z(-.38, .80, .2), z(-.52, .72, .26), z(-.52, .72, .52)], [z(.38, .48, .22), z(.42, .45, .46)]);
    });
    p.sasis.push([[.82, 1.86, 0], [.95, 1.54, 0]]);

    /* suspensi, lengan ayun, as roda */
    [1, -1].forEach(function (s) {
      var axle = [FX, R, .215 * s], head = [.83, 1.84, .215 * s], join = mid(axle, head, .46);
      solid('kaki', tube(axle, join, .068, .068, 10), .9);
      solid('kaki', tube(join, head, .046, .046, 10), .9);
      p.kaki.push(helix(mid(axle, head, .48), mid(axle, head, .80), .075, 9));
      solid('kaki', extrude([[-.30, .90], [-.78, .86], [-1.46, .70], [-1.46, .56], [-.76, .62], [-.30, .70]], .215 * s, .27 * s), .6);
      p.kaki.push([[-.55, .86, .24 * s], [-.95, .62, .24 * s]]);
    });
    [.42, .80, .98].forEach(function (t) { var q = mid([FX, R, .215], [.83, 1.84, .215], t); p.kaki.push([q, [q[0], q[1], -q[2]]]); });
    p.kaki.push([[FX, R, -.27], [FX, R, .27]], [[RX, R, -.3], [RX, R, .3]]);
    p.kaki.push(helix([-.52, .82, 0], [-.86, 1.40, 0], .11, 8), [[-.50, .79, 0], [-.88, 1.44, 0]]);

    /* lintasan rantai: dua roda gigi dan garis singgungnya (sisi kiri) */
    var f = [-.24, .73], b = [RX, R], r1 = .105, r2 = .265, th = Math.atan2(b[1] - f[1], b[0] - f[0]), L = Math.hypot(b[0] - f[0], b[1] - f[1]), al2 = Math.acos((r1 - r2) / L);
    function chain(off) {
      var pts = [], i, a;
      for (i = 0; i <= 24; i++) { a = lerp(th - al2, th + al2, i / 24); pts.push([b[0] + (r2 + off) * Math.cos(a), b[1] + (r2 + off) * Math.sin(a), -.32]); }
      for (i = 0; i <= 14; i++) { a = lerp(th + al2, th - al2 + TAU, i / 14); pts.push([f[0] + (r1 + off) * Math.cos(a), f[1] + (r1 + off) * Math.sin(a), -.32]); }
      return closed(pts);
    }
    this.chainPath = chain(0);
    p.kaki.push(chain(.022), chain(-.022));
    this.sprockets = [[f[0], f[1], r1 - .03, 10], [b[0], b[1], r2 - .035, 22]];

    /* rem: selang depan */
    p.rem.push([[.72, 2.02, .52], [.9, 1.8, .24], [1.0, 1.5, .26], [1.12, 1.02, .24], [1.14, .86, .2]], [[1.0, 1.5, .26], [1.02, 1.32, -.24], [1.14, .86, -.2]]);

    /* kelistrikan */
    p.listrik = p.listrik.concat(box(-.62, -.32, 1.08, 1.28, -.16, .16), box(-1.18, -.94, 1.46, 1.55, -.11, .11), box(.50, .62, 1.54, 1.64, -.06, .06));
    p.listrik.push([[-.56, 1.28, -.09], [-.56, 1.33, -.09]], [[-.38, 1.28, .09], [-.38, 1.33, .09]], [[.62, 1.31, -.19], [.70, 1.34, -.25]], circle(-.02, .66, -.285, .075, 16));
    this.wires = [
      [[-.4, 1.30, -.1], [-.08, 1.34, -.24], [.4, 1.50, -.2], [.84, 1.72, -.12], [1.08, 1.66, -.05], [1.28, 1.61, 0]],
      [[-.5, 1.30, 0], [-.74, 1.36, 0], [-1.06, 1.50, 0], [-1.45, 1.66, 0], [-1.8, 1.76, 0]],
      [[.56, 1.54, 0], [.70, 1.48, -.14], [.70, 1.34, -.25]],
      [[-.46, 1.08, -.14], [-.40, .96, -.28], [-.20, .86, -.29], [-.02, .72, -.29]],
      [[.84, 1.72, -.12], [.82, 1.94, -.24], [.70, 2.02, -.52]],
      [[.84, 1.72, .12], [.9, 1.88, .08], [.92, 1.96, 0]],
      [[.50, 1.58, .04], [.2, 1.48, .2], [-.32, 1.24, .16]],
      [[1.08, 1.66, -.05], [1.1, 1.58, -.4]],
      [[-1.45, 1.66, 0], [-1.84, 1.46, -.26]]
    ];
    p.listrik = p.listrik.concat(this.wires);
  };

  NovaMoto.prototype.cam = function (q) {
    var v = this.v, x = q[0] - v.tx, y = q[1] - v.ty, z = q[2] - v.tz;
    var x1 = x * this.ca + z * this.sa, z1 = -x * this.sa + z * this.ca;
    return [x1, y * this.cb - z1 * this.sb, y * this.sb + z1 * this.cb];
  };
  NovaMoto.prototype.screen = function (c) {
    var f = 11 / (11 - c[2]);
    return [this.v.cx + c[0] * f * this.v.scale, this.v.cy - c[1] * f * this.v.scale, c[2]];
  };
  NovaMoto.prototype.project = function (q) { return this.screen(this.cam(q)); };
  NovaMoto.prototype.path = function (pts) {
    var c = this.ctx;
    for (var i = 0; i < pts.length; i++) {
      var s = this.project(pts[i]);
      if (i) c.lineTo(s[0], s[1]); else c.moveTo(s[0], s[1]);
    }
  };
  NovaMoto.prototype.stroke = function (lines, color, alpha, width) {
    var c = this.ctx;
    c.beginPath();
    for (var i = 0; i < lines.length; i++) { this.path(lines[i]); if (this.collect) this.seen.push(lines[i]); }
    c.strokeStyle = rgba(color, alpha); c.lineWidth = width; c.stroke();
  };
  NovaMoto.prototype.dot = function (q, r, color, alpha) {
    var s = this.project(q), c = this.ctx;
    c.beginPath(); c.arc(s[0], s[1], r, 0, TAU); c.fillStyle = rgba(color, alpha); c.fill();
  };

  NovaMoto.prototype.drawFloor = function (t, a) {
    var c = this.ctx, i, k;
    var centre = this.project([0, 0, 0]);
    var g = c.createRadialGradient(centre[0], centre[1], 0, centre[0], centre[1], this.v.scale * 3.4);
    g.addColorStop(0, rgba([38, 120, 255], .26 * a)); g.addColorStop(1, rgba([38, 120, 255], 0));
    c.fillStyle = g; c.fillRect(0, 0, this.w, this.h);
    for (i = -12; i <= 12; i++) {
      k = i * .5;
      var fade = (1 - Math.abs(i) / 13) * .2 * a;
      this.stroke([[[k, 0, -4], [k, 0, 4]]], ICE, fade * .7, 1);
      if (Math.abs(i) <= 8) this.stroke([[[-6, 0, k], [6, 0, k]]], ICE, fade * .7, 1);
    }
    var flat = function (pts) { return pts.map(function (q) { return [q[0], 0, q[1]]; }); };
    this.stroke([flat(circle(0, 0, 0, 2.55, 110))], CYAN, .55 * a, 1.5);
    this.stroke([flat(circle(0, 0, 0, 2.72, 110))], CYAN, .18 * a, 1);
    var ticks = [];
    for (i = 0; i < 96; i++) {
      var th = i / 96 * TAU + t * .12, r1 = i % 8 === 0 ? 2.94 : 2.82;
      ticks.push([[2.72 * Math.cos(th), 0, 2.72 * Math.sin(th)], [r1 * Math.cos(th), 0, r1 * Math.sin(th)]]);
    }
    this.stroke(ticks, CYAN, .32 * a, 1);
    for (i = 0; i < 3; i++) {
      var s0 = -t * .35 + i * TAU / 3;
      this.stroke([flat(circle(0, 0, 0, 3.12, 30, s0, s0 + .9))], CYAN, .5 * a, 2.5);
    }
  };

  NovaMoto.prototype.drawFaces = function (a, ghost, scan) {
    var c = this.ctx, list = [], i, j;
    for (i = 0; i < this.faces.length; i++) {
      var f = this.faces[i], cam = [], n = f.q.length, nx = 0, ny = 0, nz = 0, d = 0, xm = 0;
      for (j = 0; j < n; j++) { cam.push(this.cam(f.q[j])); xm += f.q[j][0] / n; }
      for (j = 0; j < n; j++) {
        var u = cam[j], w = cam[(j + 1) % n];
        nx += (u[1] - w[1]) * (u[2] + w[2]); ny += (u[2] - w[2]) * (u[0] + w[0]); nz += (u[0] - w[0]) * (u[1] + w[1]); d += u[2] / n;
      }
      var len = Math.hypot(nx, ny, nz) || 1;
      list.push({ cam: cam, d: d, rim: 1 - Math.abs(nz / len), up: Math.abs(ny / len), x: xm, tone: f.tone });
    }
    list.sort(function (m, k) { return m.d - k.d; });
    for (i = 0; i < list.length; i++) {
      var g = list[i], near = scan === null ? 0 : Math.max(0, 1 - Math.abs(g.x - scan) / .26);
      var fill = [14 + 40 * g.rim + 20 * g.up + 40 * near, 40 + 135 * g.rim * g.rim + 40 * g.up + 150 * near, 110 + 140 * g.rim + 30 * g.up];
      c.beginPath();
      for (j = 0; j < g.cam.length; j++) { var s = this.screen(g.cam[j]); if (j) c.lineTo(s[0], s[1]); else c.moveTo(s[0], s[1]); }
      c.closePath();
      c.fillStyle = rgba(fill.map(function (v) { return Math.min(255, Math.round(v)); }), (.07 + .24 * g.rim * g.rim + .3 * near) * g.tone * a * (1 - ghost * .55));
      c.fill();
    }
  };

  NovaMoto.prototype.drawBodyLines = function (a, ghost) {
    var dim = a * (1 - ghost * .6), self = this;
    this.shells.forEach(function (s, i) {
      var col = function (k) { return s.rings.map(function (r) { return r[k]; }); };
      self.stroke([col(3)], ICE, .85 * dim, 1.4);
      self.stroke([col(0), col(6)], CYAN, .95 * dim, 1.7);
      self.stroke([col(9)], ICE, .6 * dim, 1.2);
      self.stroke([col(1), col(5), col(2), col(4)], ICE, .17 * dim, 1);
      var loops = [];
      for (var n = 2; n < s.rings.length - 1; n += 3) loops.push(closed(s.rings[n]));
      self.stroke(loops, CYAN, .16 * dim, 1);
      self.stroke([closed(s.rings[0]), closed(s.rings[s.rings.length - 1])], ICE, (i === 2 ? .85 : .6) * dim, 1.2);
    });
    this.panels.forEach(function (p) {
      self.stroke(p.lines, ICE, .7 * dim, 1.2);
      self.stroke([p.ridge], CYAN, .85 * dim, 1.5);
    });
    this.shroud.forEach(function (q) {
      self.stroke([closed(q)], CYAN, .85 * dim, 1.5);
      self.stroke([[q[1], q[3]], [mid(q[0], q[4], .5), q[2]]], ICE, .3 * dim, 1);
    });
  };

  NovaMoto.prototype.drawDetails = function (a, ghost) {
    var dim = a * (1 - ghost * .55), self = this;
    // setang, grip, spion
    this.stroke([[[.70, 2.02, -.70], [.73, 2.01, -.34], [.82, 1.91, -.16], [.84, 1.85, 0], [.82, 1.91, .16], [.73, 2.01, .34], [.70, 2.02, .70]]], ICE, .95 * dim, 2.6);
    [1, -1].forEach(function (s) {
      self.stroke(tube([.70, 2.02, .72 * s], [.71, 2.02, .48 * s], .052, .052, 10).lines, CYAN, .9 * dim, 1.2);
      self.stroke([[[.76, 2.03, .46 * s], [.86, 2.01, .52 * s], [.83, 2.01, .72 * s]]], ICE, .8 * dim, 1.4);
      self.stroke([[[.77, 2.01, .38 * s], [.86, 2.2, .50 * s], [.87, 2.27, .60 * s]]], ICE, .75 * dim, 1.4);
      self.stroke([closed([[.87, 2.27, .58 * s], [.86, 2.38, .64 * s], [.85, 2.37, .84 * s], [.86, 2.29, .86 * s], [.87, 2.23, .72 * s]])], CYAN, .9 * dim, 1.4);
      // lampu depan, sein, lampu belakang
      self.stroke([[[1.22, 1.745, .15 * s], [1.305, 1.665, .07 * s], [1.315, 1.64, 0]]], [225, 248, 255], .98 * dim, 3.4);
      self.stroke([[[1.08, 1.60, .22 * s], [1.1, 1.58, .40 * s]]], ICE, .6 * dim, 1.2);
      self.stroke([[[1.1, 1.58, .38 * s], [1.07, 1.575, .50 * s]]], AMBER, .85 * dim, 4);
      self.stroke([[[-1.84, 1.46, .26 * s], [-1.9, 1.45, .36 * s]]], AMBER, .8 * dim, 4);
      self.stroke([[[-1.82, 1.79, 0], [-1.79, 1.765, .07 * s], [-1.70, 1.75, .12 * s]]], [255, 103, 103], .95 * dim, 3.4);
    });
    // spidbor, tutup tangki, jahitan jok, dudukan pelat
    this.stroke([closed([[.86, 1.91, -.13], [.97, 2.0, -.1], [.97, 2.0, .1], [.86, 1.91, .13]])], CYAN, .9 * dim, 1.3);
    this.stroke([circle(0, 0, 0, .085, 20).map(function (q) { return [.36 + q[0], 1.935, q[1]]; })], ICE, .85 * dim, 1.3);
    this.stroke([[[-1.55, 1.50, .1], [-1.9, 1.40, .1], [-2.03, 1.26, .1]], [[-1.55, 1.50, -.1], [-1.9, 1.40, -.1], [-2.03, 1.26, -.1]]], ICE, .6 * dim, 1.2);
    this.stroke([closed([[-2.02, 1.27, -.17], [-2.08, 1.07, -.17], [-2.08, 1.07, .17], [-2.02, 1.27, .17]])], ICE, .75 * dim, 1.2);
  };

  NovaMoto.prototype.drawWheel = function (cx, rt, spin, a, hot) {
    var rim = R - rt * 1.75, k, lines = [], fade = 1 - hot * .5;
    this.stroke([circle(cx, R, 0, R, 60)], ICE, .85 * a * fade, 1.5);
    this.stroke([circle(cx, R, rt, R - rt, 52), circle(cx, R, -rt, R - rt, 52)], ICE, .42 * a * fade, 1);
    this.stroke([circle(cx, R, rt * .7, rim + .045, 52), circle(cx, R, -rt * .7, rim + .045, 52)], ICE, .45 * a * fade, 1);
    this.stroke([circle(cx, R, .075, rim, 48), circle(cx, R, -.075, rim, 48)], CYAN, .85 * a * fade, 1.4);
    for (k = 0; k < 30; k++) {
      var t = k / 30 * TAU + spin, tread = [];
      for (var j = -2; j <= 2; j++) { var v = j * .5, r = R - rt + rt * Math.cos(v), tt = t + Math.abs(j) * .035; tread.push([cx + r * Math.cos(tt), R + r * Math.sin(tt), rt * Math.sin(v)]); }
      lines.push(tread);
    }
    this.stroke(lines, ICE, .22 * a * fade, 1);
    lines = [];
    for (k = 0; k < 6; k++) {
      var m = k / 6 * TAU + spin;
      [-.13, .13].forEach(function (d) {
        [.06, -.06].forEach(function (z) { lines.push([[cx + .085 * Math.cos(m), R + .085 * Math.sin(m), z], [cx + rim * Math.cos(m + d), R + rim * Math.sin(m + d), z]]); });
      });
    }
    this.stroke(lines, CYAN, .55 * a * (1 - hot * .65), 1.2);
    this.stroke([circle(cx, R, .15, .09, 18), circle(cx, R, -.15, .09, 18), circle(cx, R, .17, .035, 10), circle(cx, R, -.17, .035, 10)], CYAN, .8 * a * fade, 1.2);
  };

  /* cakram dan kaliper; ikut berputar */
  NovaMoto.prototype.brakeLines = function (cx, sides, r, a0, spin) {
    var lines = [];
    sides.forEach(function (z) {
      lines.push(circle(cx, R, z, r, 44), circle(cx, R, z, r * .66, 36));
      for (var k = 0; k < 14; k++) { var a = k / 14 * TAU + spin; lines.push(circle(cx + Math.cos(a) * r * .83, R + Math.sin(a) * r * .83, z, .017, 8)); }
      for (k = 0; k < 6; k++) { var m = k / 6 * TAU + spin; lines.push([[cx + .09 * Math.cos(m), R + .09 * Math.sin(m), z], [cx + r * .66 * Math.cos(m + .35), R + r * .66 * Math.sin(m + .35), z]]); }
      var zc = z * 1.3, o = circle(cx, R, zc, r * 1.13, 12, a0, a0 + .85), i = circle(cx, R, zc, r * .66, 12, a0, a0 + .85).reverse();
      lines.push(o.concat(i, [o[0]]), circle(cx, R, zc, r * .9, 8, a0 + .12, a0 + .73));
    });
    return lines;
  };

  NovaMoto.prototype.drawPart = function (name, heat, others, a, t) {
    var HOT = (this.v.good || {})[name] ? GREEN : AMBER, spin = this.v.spin || 0;
    var lines = this.parts[name], c = this.ctx, k;
    if (name === 'rem') lines = lines.concat(this.brakeLines(FX, [-.17, .17], .33, 2.2, spin));
    if (name === 'rembelakang') lines = this.brakeLines(RX, [.22], .24, .45, spin);
    if (name === 'kaki') {
      lines = lines.slice();
      this.sprockets.forEach(function (s) {
        lines.push(circle(s[0], s[1], -.32, s[2], 32), circle(s[0], s[1], -.32, s[2] * .45, 18));
        for (var n = 0; n < s[3]; n++) { var q = n / s[3] * TAU + spin * (s[3] < 15 ? 2.2 : 1); lines.push([[s[0] + s[2] * Math.cos(q), s[1] + s[2] * Math.sin(q), -.32], [s[0] + (s[2] + .035) * Math.cos(q + .06), s[1] + (s[2] + .035) * Math.sin(q + .06), -.32]]); }
      });
    }
    if (heat > .01) {
      c.save(); c.globalCompositeOperation = 'lighter';
      this.stroke(lines, HOT, .09 * heat * a, 11);
      this.stroke(lines, HOT, .2 * heat * a, 4.5);
      c.restore();
    }
    var base = name === 'sasis' ? ICE : CYAN;
    var col = [lerp(base[0], HOT[0], heat), lerp(base[1], HOT[1], heat), lerp(base[2], HOT[2], heat)].map(Math.round);
    var idle = { mesin: .46, rem: .5, rembelakang: .45, kaki: .52, listrik: .12, sasis: .4 }[name];
    this.stroke(lines, col, lerp(idle * (1 - others * .75), 1, heat) * a, 1.05 + heat * .75);

    if (name === 'kaki') {
      // mata rantai yang bergerak
      var path = this.chainPath, len = path.length - 1;
      for (k = 0; k < 42; k++) {
        var u = ((k / 42 - spin * .05) % 1 + 1) % 1 * len, i = Math.floor(u);
        this.dot(mid(path[i], path[i + 1], u - i), 1.6 + heat, col, lerp(.6 * (1 - others * .7), 1, heat) * a);
      }
    }
    if (name === 'listrik' && heat > .05) {
      this.wires.forEach(function (w, wi) {
        for (var n = 0; n < 2; n++) {
          var u = ((t * .55 + wi * .17 + n * .5) % 1) * (w.length - 1), i = Math.floor(u);
          this.dot(mid(w[i], w[i + 1], u - i), 3, [255, 236, 190], heat * a);
        }
      }, this);
      var flash = Math.max(0, Math.sin(t * 9)), tip = [.70, 1.34, -.25];
      this.dot(tip, 4 + 8 * flash, HOT, .35 * flash * heat * a);
    }
  };

  NovaMoto.prototype.drawScan = function (x, a) {
    var c = this.ctx;
    var fr = [[x, .02, -1.05], [x, .02, 1.05], [x, 2.55, 1.05], [x, 2.55, -1.05], [x, .02, -1.05]];
    c.save(); c.globalCompositeOperation = 'lighter';
    c.beginPath(); this.path(fr); c.fillStyle = rgba(CYAN, .055 * a); c.fill();
    this.stroke([fr], CYAN, .55 * a, 1.2);
    this.shells.forEach(function (s) {
      var xs = s.xs;
      for (var i = 0; i < xs.length - 1; i++) {
        if (x < xs[i] || x > xs[i + 1]) continue;
        var f = (x - xs[i]) / (xs[i + 1] - xs[i]);
        var ring = closed(s.rings[i].map(function (q, k) { return mid(q, s.rings[i + 1][k], f); }));
        this.stroke([ring], CYAN, .28 * a, 7);
        this.stroke([ring], [220, 250, 255], .95 * a, 1.6);
      }
    }, this);
    // titik potong bidang pindai dengan semua garis motor
    this.seen.forEach(function (line) {
      for (var i = 0; i < line.length - 1; i++) {
        var p = line[i], q = line[i + 1];
        if ((p[0] - x) * (q[0] - x) < 0) {
          var h = mid(p, q, (x - p[0]) / (q[0] - p[0]));
          this.dot(h, 4.5, CYAN, .16 * a); this.dot(h, 1.5, [225, 252, 255], .9 * a);
        }
      }
    }, this);
    c.restore();
  };

  NovaMoto.prototype.draw = function (v, t) {
    var c = this.ctx, a = v.alpha === undefined ? 1 : v.alpha;
    this.v = v;
    this.ca = Math.cos(v.yaw); this.sa = Math.sin(v.yaw);
    this.cb = Math.cos(v.pitch); this.sb = Math.sin(v.pitch);
    c.setTransform(this.dpr, 0, 0, this.dpr, 0, 0);
    c.clearRect(0, 0, this.w, this.h);
    if (a <= .005) return;
    c.lineJoin = 'round'; c.lineCap = 'round';
    var hl = v.hl || {}, hm = hl.mesin || 0, hr = hl.rem || 0, hb = hl.rembelakang || 0, hk = hl.kaki || 0, hs = hl.listrik || 0;
    var ghost = Math.max(hm, hr, hk, hs), scan = v.scan === undefined ? null : v.scan;
    if (v.floor !== 0) this.drawFloor(t, a * (v.floor === undefined ? 1 : v.floor));

    this.seen = []; this.collect = scan !== null;
    this.drawFaces(a, ghost, scan);
    this.drawPart('sasis', 0, ghost, a, t);
    this.drawWheel(FX, .125, v.spin || 0, a * (1 - hk * .35), Math.max(hr, ghost * .5));
    this.drawWheel(RX, .165, v.spin || 0, a * (1 - hk * .35), Math.max(hb, ghost * .5));
    this.drawBodyLines(a, ghost);
    this.drawDetails(a, ghost);
    this.drawPart('mesin', hm, ghost, a, t);
    this.drawPart('kaki', hk, ghost, a, t);
    this.drawPart('rembelakang', hb, ghost, a, t);
    this.drawPart('rem', hr, ghost, a, t);
    this.drawPart('listrik', hs, ghost, a, t);
    this.collect = false;
    if (scan !== null) this.drawScan(scan, a);
  };

  window.NovaMoto = NovaMoto;
})();
