const canvas = document.getElementById('login-animation');
const ctx = canvas?.getContext('2d');

if (canvas && ctx) {
  const bars = Array.from({ length: 24 }, (_, index) => ({
    x: Math.random(),
    y: Math.random(),
    h: 28 + Math.random() * 70,
    speed: 0.0015 + Math.random() * 0.0025,
    phase: index * 0.8
  }));

  function resize() {
    canvas.width = window.innerWidth * window.devicePixelRatio;
    canvas.height = window.innerHeight * window.devicePixelRatio;
    ctx.setTransform(window.devicePixelRatio, 0, 0, window.devicePixelRatio, 0, 0);
  }

  function draw(time) {
    const width = window.innerWidth;
    const height = window.innerHeight;
    ctx.clearRect(0, 0, width, height);

    bars.forEach((bar, index) => {
      const x = ((bar.x + time * bar.speed) % 1) * width;
      const wave = Math.sin(time * 0.002 + bar.phase);
      const y = bar.y * height + wave * 24;
      const barWidth = 10 + (index % 3) * 4;
      const gradient = ctx.createLinearGradient(x, y, x, y + bar.h);
      gradient.addColorStop(0, 'rgba(167, 139, 250, 0.06)');
      gradient.addColorStop(1, 'rgba(109, 40, 217, 0.20)');

      ctx.fillStyle = gradient;
      ctx.beginPath();
      if (ctx.roundRect) {
        ctx.roundRect(x, y, barWidth, bar.h, 8);
        ctx.fill();
      } else {
        ctx.fillRect(x, y, barWidth, bar.h);
      }
    });

    requestAnimationFrame(draw);
  }

  resize();
  window.addEventListener('resize', resize);
  requestAnimationFrame(draw);
}
