(function () {
    const canvas = document.getElementById('bubble-canvas') || document.createElement('canvas');
    canvas.id = 'bubble-canvas';
    if (!canvas.parentNode) {
        document.body.appendChild(canvas);
    }

    const ctx = canvas.getContext('2d', { alpha: true });
    if (!ctx) {
        return;
    }

    const motionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
    const palette = {
        fills: [],
        rim: 'rgba(40, 166, 224, 0.42)',
        core: 'rgba(118, 215, 255, 0.18)',
        sheen: 'rgba(255, 255, 255, 0.78)',
        caustic: 'rgba(30, 134, 255, 0.22)'
    };
    const random = (min, max) => Math.random() * (max - min) + min;
    let width = 0;
    let height = 0;
    let bubbles = [];
    let frameId = 0;

    function cssVar(name, fallback) {
        const value = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
        return value || fallback;
    }

    function refreshPalette() {
        palette.fills = [
            cssVar('--bubble1', 'rgba(30,134,255,.22)'),
            cssVar('--bubble2', 'rgba(118,215,255,.22)'),
            cssVar('--bubble3', 'rgba(255,195,221,.18)'),
            cssVar('--bubble4', 'rgba(255,231,153,.18)'),
            cssVar('--bubble5', 'rgba(186,255,214,.18)')
        ].filter(Boolean);
        palette.rim = cssVar('--bubble-rim', palette.rim);
        palette.core = cssVar('--bubble-core', palette.core);
        palette.sheen = cssVar('--bubble-sheen', palette.sheen);
        palette.caustic = cssVar('--bubble-caustic', palette.caustic);
        renderFrame(performance.now(), false);
    }

    function makeBubble(index) {
        const radius = random(26, 96);
        return {
            x: random(radius, Math.max(radius + 1, width - radius)),
            y: random(radius, Math.max(radius + 1, height - radius)),
            radius,
            vx: random(0.16, 0.58) * (Math.random() < 0.5 ? -1 : 1),
            vy: random(0.12, 0.48) * (Math.random() < 0.5 ? -1 : 1),
            phase: random(0, Math.PI * 2),
            wobble: random(0.58, 1.34),
            ci: index,
            alpha: random(0.58, 0.92)
        };
    }

    function resize() {
        const dpr = Math.min(window.devicePixelRatio || 1, 2);
        width = window.innerWidth;
        height = window.innerHeight;
        canvas.width = Math.max(1, Math.round(width * dpr));
        canvas.height = Math.max(1, Math.round(height * dpr));
        canvas.style.width = `${width}px`;
        canvas.style.height = `${height}px`;
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

        const targetCount = Math.max(32, Math.min(52, Math.round((width * height) / 27000)));
        if (!bubbles.length) {
            bubbles = Array.from({ length: targetCount }, (_, index) => makeBubble(index));
        } else {
            while (bubbles.length < targetCount) {
                bubbles.push(makeBubble(bubbles.length));
            }
            bubbles = bubbles.slice(0, targetCount);
            bubbles.forEach((bubble) => {
                bubble.x = Math.min(Math.max(bubble.radius, bubble.x), Math.max(bubble.radius, width - bubble.radius));
                bubble.y = Math.min(Math.max(bubble.radius, bubble.y), Math.max(bubble.radius, height - bubble.radius));
            });
        }

        renderFrame(performance.now(), false);
    }

    function drawCaustics(time) {
        ctx.save();
        ctx.globalCompositeOperation = 'screen';
        ctx.lineCap = 'round';
        for (let index = 0; index < 6; index += 1) {
            const y = height * (0.16 + index * 0.14) + Math.sin(time * 0.00022 + index) * 22;
            const xShift = Math.cos(time * 0.00016 + index * 1.7) * 38;
            ctx.globalAlpha = 0.09 + index * 0.012;
            ctx.strokeStyle = palette.caustic;
            ctx.lineWidth = 1.2 + index * 0.22;
            ctx.beginPath();
            ctx.moveTo(-80 + xShift, y);
            ctx.bezierCurveTo(width * 0.22, y - 36, width * 0.42, y + 42, width * 0.66, y - 4);
            ctx.bezierCurveTo(width * 0.82, y - 34, width + 40, y + 18, width + 120, y - 20);
            ctx.stroke();
        }
        ctx.restore();
    }

    function moveBubble(bubble, time) {
        bubble.x += bubble.vx + Math.sin(time * 0.00034 * bubble.wobble + bubble.phase) * 0.12;
        bubble.y += bubble.vy + Math.cos(time * 0.00028 * bubble.wobble + bubble.phase) * 0.08;

        if (bubble.x - bubble.radius < 0) {
            bubble.x = bubble.radius;
            bubble.vx = Math.abs(bubble.vx);
        } else if (bubble.x + bubble.radius > width) {
            bubble.x = width - bubble.radius;
            bubble.vx = -Math.abs(bubble.vx);
        }

        if (bubble.y - bubble.radius < 0) {
            bubble.y = bubble.radius;
            bubble.vy = Math.abs(bubble.vy);
        } else if (bubble.y + bubble.radius > height) {
            bubble.y = height - bubble.radius;
            bubble.vy = -Math.abs(bubble.vy);
        }
    }

    function drawBubble(bubble, time) {
        const pulse = 1 + Math.sin(time * 0.001 * bubble.wobble + bubble.phase) * 0.045;
        const radius = bubble.radius * pulse;
        const fill = palette.fills[bubble.ci % palette.fills.length] || 'rgba(118, 215, 255, 0.22)';

        ctx.save();
        ctx.translate(bubble.x, bubble.y);
        ctx.rotate(Math.sin(time * 0.00024 + bubble.phase) * 0.09);
        ctx.globalAlpha = bubble.alpha;

        const glass = ctx.createRadialGradient(-radius * 0.34, -radius * 0.42, radius * 0.04, 0, 0, radius);
        glass.addColorStop(0, 'rgba(255, 255, 255, 0.92)');
        glass.addColorStop(0.16, palette.sheen);
        glass.addColorStop(0.42, fill);
        glass.addColorStop(0.78, palette.core);
        glass.addColorStop(1, 'rgba(255, 255, 255, 0.06)');

        ctx.fillStyle = glass;
        ctx.shadowColor = palette.caustic;
        ctx.shadowBlur = Math.max(8, radius * 0.14);
        ctx.beginPath();
        ctx.arc(0, 0, radius, 0, Math.PI * 2);
        ctx.fill();
        ctx.shadowBlur = 0;

        ctx.lineWidth = Math.max(1.1, radius * 0.026);
        ctx.strokeStyle = palette.rim;
        ctx.stroke();

        ctx.globalAlpha = bubble.alpha * 0.42;
        ctx.strokeStyle = fill;
        ctx.lineWidth = Math.max(1, radius * 0.014);
        ctx.beginPath();
        ctx.arc(0, 0, radius * 0.92, 1.75, 4.34);
        ctx.stroke();

        ctx.globalAlpha = bubble.alpha * 0.92;
        ctx.strokeStyle = palette.sheen;
        ctx.lineWidth = Math.max(1.4, radius * 0.035);
        ctx.beginPath();
        ctx.arc(0, 0, radius * 0.78, -2.35, -1.12);
        ctx.stroke();

        ctx.globalAlpha = bubble.alpha * 0.58;
        ctx.strokeStyle = palette.caustic;
        ctx.lineWidth = Math.max(1, radius * 0.018);
        ctx.beginPath();
        ctx.arc(radius * 0.08, radius * 0.04, radius * 0.54, 0.12, 1.34);
        ctx.stroke();

        ctx.globalAlpha = bubble.alpha * 0.72;
        ctx.fillStyle = 'rgba(255, 255, 255, 0.58)';
        ctx.beginPath();
        ctx.ellipse(-radius * 0.34, -radius * 0.42, radius * 0.18, radius * 0.08, -0.55, 0, Math.PI * 2);
        ctx.fill();

        ctx.globalAlpha = bubble.alpha * 0.28;
        ctx.fillStyle = 'rgba(255, 255, 255, 0.36)';
        ctx.beginPath();
        ctx.ellipse(radius * 0.28, radius * 0.31, radius * 0.12, radius * 0.06, -0.45, 0, Math.PI * 2);
        ctx.fill();

        ctx.restore();
    }

    function renderFrame(time, shouldMove) {
        ctx.clearRect(0, 0, width, height);
        drawCaustics(time);
        bubbles.forEach((bubble) => {
            if (shouldMove) {
                moveBubble(bubble, time);
            }
            drawBubble(bubble, time);
        });
    }

    function tick(time) {
        renderFrame(time, !motionQuery.matches);
        frameId = window.requestAnimationFrame(tick);
    }

    function restart() {
        window.cancelAnimationFrame(frameId);
        if (motionQuery.matches) {
            renderFrame(performance.now(), false);
            return;
        }
        frameId = window.requestAnimationFrame(tick);
    }

    window.addEventListener('resize', resize);
    if (motionQuery.addEventListener) {
        motionQuery.addEventListener('change', restart);
    }

    new MutationObserver(refreshPalette).observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['data-theme']
    });

    refreshPalette();
    resize();
    restart();
})();
