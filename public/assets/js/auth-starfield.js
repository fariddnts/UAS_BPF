(function () {
  'use strict';

  var canvas = document.querySelector('.auth-starfield-canvas');
  if (!canvas) return;

  var context = canvas.getContext('2d');
  var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var stars = [];
  var width = 0;
  var height = 0;
  var pixelRatio = 1;
  var previousTime = 0;
  var elapsed = 0;

  function randomBetween(min, max) {
    return min + Math.random() * (max - min);
  }

  function makeStar() {
    var size = Math.random() < 0.08 ? randomBetween(10, 16) : randomBetween(3.2, 8.5);

    return {
      x: Math.random() * width,
      y: Math.random() * height,
      size: size,
      opacity: randomBetween(0.18, 0.58),
      driftX: randomBetween(-5, 5),
      driftY: randomBetween(-13, -4),
      rotation: randomBetween(0, Math.PI * 2),
      rotationSpeed: randomBetween(-0.18, 0.18),
      twinkleSpeed: randomBetween(0.7, 2.2),
      phase: randomBetween(0, Math.PI * 2)
    };
  }

  function resize() {
    width = window.innerWidth;
    height = window.innerHeight;
    pixelRatio = Math.min(window.devicePixelRatio || 1, 2);
    canvas.width = Math.round(width * pixelRatio);
    canvas.height = Math.round(height * pixelRatio);
    context.setTransform(pixelRatio, 0, 0, pixelRatio, 0, 0);

    var count = Math.max(125, Math.min(360, Math.round(width * height / 4000)));
    stars = Array.from({ length: count }, makeStar);
    previousTime = 0;

    if (reducedMotion) draw(0);
  }

  function drawLogo(star, glow) {
    var size = star.size;
    var radius = size * 0.72;
    var alpha = star.opacity * (0.62 + glow * 0.38);

    context.save();
    context.translate(star.x, star.y);
    context.rotate(star.rotation);
    context.globalAlpha = alpha;
    context.strokeStyle = '#52e0ff';
    context.fillStyle = '#8aebff';
    context.lineWidth = Math.max(0.7, size * 0.085);
    context.lineCap = 'round';
    context.shadowColor = '#52e0ff';
    context.shadowBlur = size * (0.35 + glow * 0.9);

    context.beginPath();
    context.arc(0, 0, radius, 0, Math.PI * 2);
    context.stroke();

    context.beginPath();
    context.moveTo(0, -radius);
    context.lineTo(0, -size * 0.24);
    context.moveTo(radius, 0);
    context.lineTo(size * 0.24, 0);
    context.moveTo(0, radius);
    context.lineTo(0, size * 0.24);
    context.moveTo(-radius, 0);
    context.lineTo(-size * 0.24, 0);
    context.stroke();

    context.beginPath();
    context.arc(0, 0, size * 0.2, 0, Math.PI * 2);
    context.fill();

    if (glow > 0.88) {
      context.globalAlpha = alpha * 0.42;
      context.lineWidth = Math.max(0.5, size * 0.04);
      context.beginPath();
      context.moveTo(-size * 1.15, 0);
      context.lineTo(size * 1.15, 0);
      context.moveTo(0, -size * 1.15);
      context.lineTo(0, size * 1.15);
      context.stroke();
    }

    context.restore();
  }

  function draw(timestamp) {
    var delta = previousTime ? Math.min((timestamp - previousTime) / 1000, 0.05) : 0;
    previousTime = timestamp;
    elapsed += delta;
    context.clearRect(0, 0, width, height);

    stars.forEach(function (star) {
      if (!reducedMotion) {
        star.x += star.driftX * delta;
        star.y += star.driftY * delta;
        star.rotation += star.rotationSpeed * delta;

        if (star.x < -20) star.x = width + 20;
        if (star.x > width + 20) star.x = -20;
        if (star.y < -20) star.y = height + 20;
        if (star.y > height + 20) star.y = -20;
      }

      var glow = (Math.sin(elapsed * star.twinkleSpeed + star.phase) + 1) / 2;
      drawLogo(star, glow);
    });

    if (!reducedMotion) window.requestAnimationFrame(draw);
  }

  window.addEventListener('resize', resize);
  resize();
  if (!reducedMotion) window.requestAnimationFrame(draw);
})();
