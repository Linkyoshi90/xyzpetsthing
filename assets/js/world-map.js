document.addEventListener('DOMContentLoaded', function() {
  const img = document.querySelector('img.world-map');
  const map = document.querySelector('map[name="worldmap"]');
  if (!img || !map) return;

  const areas = Array.from(map.querySelectorAll('area'));
  const original = areas.map(a => a.dataset.coords.split(',').map(Number));

  function resize() {
    const w = img.offsetWidth;
    const h = img.offsetHeight;
    const wRatio = w / img.naturalWidth;
    const hRatio = h / img.naturalHeight;
    areas.forEach((area, i) => {
      const coords = original[i].map((c, idx) => (idx % 2 === 0 ? c * wRatio : c * hRatio));
      area.coords = coords.map(n => Math.round(n)).join(',');
    });
  }

  window.addEventListener('resize', resize);
  if (img.complete) {
    resize();
  } else {
    img.addEventListener('load', resize);
  }
});