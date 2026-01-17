const canvas = document.createElement('canvas');
canvas.id = 'bubble-canvas';
document.body.appendChild(canvas);
const ctx = canvas.getContext('2d');
let width, height;

function resize() {
    width = canvas.width = window.innerWidth;
    height = canvas.height = window.innerHeight;
}
resize();
window.addEventListener('resize', resize);

function getColors() {
    const styles = getComputedStyle(document.documentElement);
    return [
        styles.getPropertyValue('--bubble1').trim(),
        styles.getPropertyValue('--bubble2').trim(),
        styles.getPropertyValue('--bubble3').trim(),
        styles.getPropertyValue('--bubble4').trim(),
        styles.getPropertyValue('--bubble5').trim()
    ].filter(Boolean);
}

let colors = getColors();

const bubbles = Array.from({ length: 30 }, () => {
    const r = Math.random() * 40 + 20;
    return {
        x: Math.random() * (width - r * 2) + r,
        y: Math.random() * (height - r * 2) + r,
        r,
        dx: (Math.random() * 0.5 + 0.2) * (Math.random() < 0.5 ? -1 : 1),
        dy: (Math.random() * 0.5 + 0.2) * (Math.random() < 0.5 ? -1 : 1),
        ci: Math.floor(Math.random() * colors.length)
    };
});

new MutationObserver(() => {
    colors = getColors();
}).observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });

function step() {
    ctx.clearRect(0, 0, width, height);
    if (colors.length === 0) {
        requestAnimationFrame(step);
        return;
    }
    for (const b of bubbles) {
        b.x += b.dx;
        b.y += b.dy;
        if (b.x - b.r < 0 || b.x + b.r > width) b.dx *= -1;
        if (b.y - b.r < 0 || b.y + b.r > height) b.dy *= -1;

        ctx.fillStyle = colors[b.ci % colors.length];
        ctx.beginPath();
        ctx.arc(b.x, b.y, b.r, 0, Math.PI * 2);
        ctx.fill();
    }
    requestAnimationFrame(step);
}
step();
