const canvas = document.getElementById('pong');
const ctx = canvas.getContext('2d');

const paddle = {
    x: 24,
    y: canvas.height / 2 - 60,
    w: 18,
    h: 120,
    speed: 6,
    maxHeight: canvas.height * 0.85,
    minHeight: 60,
    minSpeed: 3,
    maxSpeed: 12
};

let balls = [];
let upgrades = [];
let score = 0;
let hits = 0;
let misses = 0;
let hitsUntilUpgrade = 5;
let gameOver = false;
let lastSpawn = 0;
let missLimit = 10;
let sizeUpgradeCount = 0;
let ballSpeedMultiplier = 1;

const scoreEl = document.getElementById('scoreVal');
const hitEl = document.getElementById('hitVal');
const missEl = document.getElementById('missVal');
const missLimitEl = document.getElementById('missLimitVal');
const upgradeEl = document.getElementById('upgradeProgress');
const finalScoreEl = document.getElementById('finalScoreVal');
const gameOverNotice = document.getElementById('gameOverNotice');
const modifierNotice = document.getElementById('modifierNotice');

const upgradeTypes = [
    'multiball',
    'paddle_size_up',
    'paddle_size_down',
    'paddle_speed_up',
    'paddle_speed_down',
    'ball_speed_up',
    'ball_speed_down',
    'miss_limit_up',
    'miss_limit_down'
];

const upgradeVisuals = {
    multiball: { color: 'rgba(244, 114, 182, 0.9)', label: 'x2' },
    paddle_size_up: { color: 'rgba(34, 197, 94, 0.9)', label: 'P+' },
    paddle_size_down: { color: 'rgba(220, 38, 38, 0.9)', label: 'P-' },
    paddle_speed_up: { color: 'rgba(59, 130, 246, 0.9)', label: 'S+' },
    paddle_speed_down: { color: 'rgba(30, 64, 175, 0.9)', label: 'S-' },
    ball_speed_up: { color: 'rgba(250, 204, 21, 0.9)', label: 'B+' },
    ball_speed_down: { color: 'rgba(202, 138, 4, 0.9)', label: 'B-' },
    miss_limit_up: { color: 'rgba(236, 72, 153, 0.9)', label: 'L+' },
    miss_limit_down: { color: 'rgba(190, 24, 93, 0.9)', label: 'L-' }
};

const upgradeNames = {
    multiball: 'Multiball',
    paddle_size_up: 'Paddle Size Up',
    paddle_size_down: 'Paddle Size Down',
    paddle_speed_up: 'Paddle Speed Up',
    paddle_speed_down: 'Paddle Speed Down',
    ball_speed_up: 'Ball Speed Up',
    ball_speed_down: 'Ball Speed Down',
    miss_limit_up: 'Miss Limit Up',
    miss_limit_down: 'Miss Limit Down'
};

const keys = {};
let audioCtx = null;
let masterGain = null;

function initAudioContext(forceResume = false) {
    const AudioContextClass = window.AudioContext || window.webkitAudioContext;
    if (!AudioContextClass) {
        return null;
    }

    if (!audioCtx) {
        audioCtx = new AudioContextClass();
        masterGain = audioCtx.createGain();
        masterGain.gain.value = 0.18;
        masterGain.connect(audioCtx.destination);
    }

    if (forceResume && audioCtx.state === 'suspended') {
        audioCtx.resume();
    }

    return audioCtx;
}

function resumeAudioOnInteraction() {
    const ctx = initAudioContext(true);
    if (ctx && ctx.state === 'running') {
        window.removeEventListener('pointerdown', resumeAudioOnInteraction);
        window.removeEventListener('keydown', resumeAudioOnInteraction);
    }
}

window.addEventListener('pointerdown', resumeAudioOnInteraction);
window.addEventListener('keydown', resumeAudioOnInteraction);

function createTone({ type = 'sine', startFrequency, endFrequency, duration = 0.2, gainPeak = 0.2 }) {
    const ctx = initAudioContext();
    if (!ctx || !masterGain || !startFrequency) {
        return;
    }

    const oscillator = ctx.createOscillator();
    const gainNode = ctx.createGain();

    oscillator.type = type;
    const now = ctx.currentTime;
    oscillator.frequency.setValueAtTime(startFrequency, now);
    if (endFrequency && endFrequency !== startFrequency) {
        oscillator.frequency.exponentialRampToValueAtTime(Math.max(endFrequency, 1), now + duration);
    }

    gainNode.gain.setValueAtTime(0.0001, now);
    gainNode.gain.exponentialRampToValueAtTime(Math.max(gainPeak, 0.0001), now + 0.015);
    gainNode.gain.exponentialRampToValueAtTime(0.0001, now + duration);

    oscillator.connect(gainNode);
    gainNode.connect(masterGain);

    oscillator.start(now);
    oscillator.stop(now + duration);
}

function playHitSound() {
    createTone({
        type: 'square',
        startFrequency: 420,
        endFrequency: 760,
        duration: 0.18,
        gainPeak: 0.22
    });
}

function playMissSound() {
    createTone({
        type: 'sawtooth',
        startFrequency: 180,
        endFrequency: 90,
        duration: 0.4,
        gainPeak: 0.28
    });
}

function playUpgradeSound() {
    createTone({
        type: 'triangle',
        startFrequency: 520,
        endFrequency: 680,
        duration: 0.25,
        gainPeak: 0.24
    });
    createTone({
        type: 'sine',
        startFrequency: 780,
        endFrequency: 1040,
        duration: 0.22,
        gainPeak: 0.18
    });
}
document.addEventListener('keydown', e => {
    keys[e.code] = true;
    if (['ArrowUp', 'ArrowDown', 'KeyW', 'KeyS'].includes(e.code)) {
        e.preventDefault();
    }
});
document.addEventListener('keyup', e => keys[e.code] = false);

let modifierTimeout = null;

function spawnBall(direction = -1, isOriginal = true) {
    const baseSpeed = 4 + Math.random() * 1.5;
    const angle = Math.random() * Math.PI / 3 - Math.PI / 6;
    const speed = baseSpeed * ballSpeedMultiplier;
    const speedX = Math.cos(angle) * speed * direction;
    const speedY = Math.sin(angle) * speed;
    balls.push({
        x: canvas.width - 80,
        y: Math.random() * (canvas.height - 160) + 80,
        vx: speedX,
        vy: speedY,
        radius: 10,
        baseSpeed,
        isOriginal
    });
    lastSpawn = performance.now();
}

function spawnUpgrade() {
    const type = upgradeTypes[Math.floor(Math.random() * upgradeTypes.length)];
    upgrades.push({
        x: canvas.width - 40,
        y: Math.random() * (canvas.height - 60) + 30,
        w: 26,
        h: 26,
        speed: 2.2,
        type
    });
}

function applyUpgrade(upgrade) {
    announceModifier(upgrade.type);
    playUpgradeSound();
    if (upgrade.type === 'multiball') {
        const clones = [];
        balls.forEach(ball => {
            const newAngle = Math.atan2(ball.vy, ball.vx) + (Math.random() * 0.6 - 0.3);
            const baseSpeed = ball.baseSpeed;
            const speed = baseSpeed * ballSpeedMultiplier;
            clones.push({
                x: ball.x,
                y: ball.y,
                vx: Math.cos(newAngle) * speed,
                vy: Math.sin(newAngle) * speed,
                radius: ball.radius,
                baseSpeed,
                isOriginal: false
            });
        });
        balls.push(...clones);
        return;
    }

    if (upgrade.type === 'paddle_size_up') {
        const oldHeight = paddle.h;
        const newHeight = Math.min(paddle.h * 1.3, paddle.maxHeight);
        if (sizeUpgradeCount < 3 && newHeight > oldHeight + 0.5) {
            paddle.h = newHeight;
            sizeUpgradeCount += 1;
            repositionPaddleAfterResize(oldHeight);
        } else {
            score += 15;
        }
        return;
    }

    if (upgrade.type === 'paddle_size_down') {
        const oldHeight = paddle.h;
        const newHeight = Math.max(paddle.h * 0.8, paddle.minHeight);
        if (newHeight < oldHeight - 0.5) {
            paddle.h = newHeight;
            sizeUpgradeCount = Math.max(0, sizeUpgradeCount - 1);
            repositionPaddleAfterResize(oldHeight);
        }
        return;
    }

    if (upgrade.type === 'paddle_speed_up') {
        paddle.speed = Math.min(paddle.speed + 1, paddle.maxSpeed);
        return;
    }

    if (upgrade.type === 'paddle_speed_down') {
        paddle.speed = Math.max(paddle.speed - 1, paddle.minSpeed);
        return;
    }

    if (upgrade.type === 'ball_speed_up') {
        ballSpeedMultiplier = Math.min(ballSpeedMultiplier + 0.15, 2.5);
        updateBallSpeeds();
        return;
    }

    if (upgrade.type === 'ball_speed_down') {
        ballSpeedMultiplier = Math.max(ballSpeedMultiplier - 0.15, 0.5);
        updateBallSpeeds();
        return;
    }

    if (upgrade.type === 'miss_limit_up') {
        missLimit = Math.min(missLimit + 2, 20);
        return;
    }

    if (upgrade.type === 'miss_limit_down') {
        missLimit = Math.max(missLimit - 1, 3);
        if (misses >= missLimit) {
            endGame();
        }
        return;
    }
}

function updateBallSpeeds() {
    balls.forEach(ball => {
        const angle = Math.atan2(ball.vy, ball.vx);
        const speed = ball.baseSpeed * ballSpeedMultiplier;
        ball.vx = Math.cos(angle) * speed;
        ball.vy = Math.sin(angle) * speed;
    });
}

function repositionPaddleAfterResize(previousHeight) {
    const center = paddle.y + previousHeight / 2;
    paddle.y = center - paddle.h / 2;
    clampPaddlePosition();
}

function updateHUD() {
    scoreEl.textContent = Math.floor(score);
    hitEl.textContent = hits;
    missEl.textContent = misses;
    missLimitEl.textContent = missLimit;
    upgradeEl.textContent = upgrades.length > 0 ? 'Ready!' : hitsUntilUpgrade;
}

function announceModifier(type) {
    if (!modifierNotice) {
        return;
    }
    const label = upgradeNames[type] || 'Upgrade';
    modifierNotice.textContent = label;
    modifierNotice.classList.remove('show');
    // Force reflow so the animation can restart when collecting modifiers rapidly
    void modifierNotice.offsetWidth;
    modifierNotice.classList.add('show');
    if (modifierTimeout) {
        clearTimeout(modifierTimeout);
    }
    modifierTimeout = setTimeout(() => {
        modifierNotice.classList.remove('show');
    }, 1600);
}

function reset() {
    balls = [];
    upgrades = [];
    score = 0;
    hits = 0;
    misses = 0;
    hitsUntilUpgrade = 5;
    gameOver = false;
    missLimit = 10;
    sizeUpgradeCount = 0;
    ballSpeedMultiplier = 1;
    paddle.speed = 6;
    paddle.h = 120;
    paddle.y = canvas.height / 2 - paddle.h / 2;
    clampPaddlePosition();
    if (modifierTimeout) {
        clearTimeout(modifierTimeout);
        modifierTimeout = null;
    }
    if (modifierNotice) {
        modifierNotice.classList.remove('show');
        modifierNotice.textContent = '';
    }
    updateHUD();
    spawnBall(-1, true);
}

function endGame() {
    if (gameOver) return;
    gameOver = true;
    finalScoreEl.textContent = Math.floor(score);
    gameOverNotice.classList.add('show');
    const submit = confirm(`You let through ${missLimit} balls! Submit score of ${Math.floor(score)}?`);
    const redirect = () => { window.location.href = 'index.php?pg=games'; };
    if (submit) {
        fetch('score_exchange.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ game: 'paddlepanic', score: Math.floor(score) })
        })
            .then(r => r.json())
            .then(data => {
                if (window.updateCurrencyDisplay && data && typeof data === 'object' && data.cash !== undefined) {
                    window.updateCurrencyDisplay({ cash: data.cash });
                } else if (data && data.error) {
                    alert(data.error);
                }
            })
            .finally(redirect);
    } else {
        redirect();
    }
}

function clampPaddlePosition() {
    if (paddle.y < 10) paddle.y = 10;
    if (paddle.y + paddle.h > canvas.height - 10) {
        paddle.y = canvas.height - 10 - paddle.h;
    }
}

function handlePaddleMovement() {
    if (keys.KeyW || keys.ArrowUp) {
        paddle.y -= paddle.speed;
    }
    if (keys.KeyS || keys.ArrowDown) {
        paddle.y += paddle.speed;
    }
    clampPaddlePosition();
}

function registerMiss(index) {
    if (index < 0 || index >= balls.length) {
        return;
    }
    const [missedBall] = balls.splice(index, 1);
    if (!missedBall) {
        return;
    }
    if (!missedBall.isOriginal) {
        updateHUD();
        return;
    }
    balls = [];
    playMissSound();
    misses += 1;
    updateHUD();
    if (misses >= missLimit) {
        endGame();
    }
}

function handleBallPhysics(ball, index) {
    ball.x += ball.vx;
    ball.y += ball.vy;

    if (ball.y - ball.radius <= 0 && ball.vy < 0) {
        ball.y = ball.radius;
        ball.vy *= -1;
    }
    if (ball.y + ball.radius >= canvas.height && ball.vy > 0) {
        ball.y = canvas.height - ball.radius;
        ball.vy *= -1;
    }

    if (ball.x + ball.radius >= canvas.width && ball.vx > 0) {
        ball.x = canvas.width - ball.radius;
        ball.vx *= -1;
    }

    if (ball.vx < 0 && ball.x - ball.radius <= paddle.x + paddle.w) {
        if (ball.y + ball.radius >= paddle.y && ball.y - ball.radius <= paddle.y + paddle.h) {
            const offset = (ball.y - (paddle.y + paddle.h / 2)) / (paddle.h / 2);
            const bounceAngle = offset * (Math.PI / 3);
            const speed = ball.baseSpeed * ballSpeedMultiplier;
            ball.vx = Math.cos(bounceAngle) * speed;
            ball.vy = Math.sin(bounceAngle) * speed;
            if (ball.vx < 0) {
                ball.vx = Math.abs(ball.vx);
            }
            ball.x = paddle.x + paddle.w + ball.radius + 1;
            playHitSound();
            hits += 1;
            score += 1;
            hitsUntilUpgrade -= 1;
            if (hitsUntilUpgrade <= 0) {
                hitsUntilUpgrade = 5;
                spawnUpgrade();
            }
            updateHUD();
            return;
        }
    }

    if (ball.x + ball.radius < 0 && !gameOver) {
        registerMiss(index);
    }
}

function updateUpgrades() {
    for (let i = upgrades.length - 1; i >= 0; i--) {
        const upgrade = upgrades[i];
        upgrade.x -= upgrade.speed;
        if (upgrade.x + upgrade.w < 0) {
            upgrades.splice(i, 1);
            updateHUD();
            continue;
        }
        if (
            upgrade.x < paddle.x + paddle.w &&
            upgrade.x + upgrade.w > paddle.x &&
            upgrade.y < paddle.y + paddle.h &&
            upgrade.y + upgrade.h > paddle.y
        ) {
            applyUpgrade(upgrade);
            upgrades.splice(i, 1);
            updateHUD();
        }
    }
}

function drawCourt() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.fillStyle = 'rgba(15, 23, 42, 0.85)';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    ctx.strokeStyle = 'rgba(148, 163, 184, 0.3)';
    ctx.setLineDash([12, 12]);
    ctx.beginPath();
    ctx.moveTo(canvas.width / 2, 0);
    ctx.lineTo(canvas.width / 2, canvas.height);
    ctx.stroke();
    ctx.setLineDash([]);

    ctx.fillStyle = '#22d3ee';
    ctx.fillRect(paddle.x, paddle.y, paddle.w, paddle.h);

    balls.forEach(ball => {
        const gradient = ctx.createRadialGradient(ball.x - 3, ball.y - 3, 2, ball.x, ball.y, ball.radius);
        if (ball.isOriginal) {
            gradient.addColorStop(0, 'rgba(220, 252, 231, 0.95)');
            gradient.addColorStop(1, 'rgba(22, 163, 74, 0.9)');
        } else {
            gradient.addColorStop(0, 'rgba(254, 249, 195, 0.95)');
            gradient.addColorStop(1, 'rgba(250, 204, 21, 0.8)');
        }
        ctx.fillStyle = gradient;
        ctx.beginPath();
        ctx.arc(ball.x, ball.y, ball.radius, 0, Math.PI * 2);
        ctx.fill();
    });

    upgrades.forEach(up => {
        const visual = upgradeVisuals[up.type] || { color: 'rgba(148, 163, 184, 0.9)', label: '?' };
        ctx.fillStyle = visual.color;
        ctx.fillRect(up.x, up.y, up.w, up.h);
        ctx.fillStyle = 'rgba(15, 23, 42, 0.85)';
        ctx.font = '16px sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(visual.label, up.x + up.w / 2, up.y + up.h / 2);
    });
}

function gameLoop() {
    if (!gameOver) {
        handlePaddleMovement();
        for (let i = balls.length - 1; i >= 0; i--) {
            const ball = balls[i];
            if (!ball) {
                continue;
            }
            handleBallPhysics(ball, i);
        }
        updateUpgrades();
        if (balls.length === 0 && !gameOver && performance.now() - lastSpawn > 300) {
            spawnBall(-1, true);
        }
    }
    drawCourt();
    requestAnimationFrame(gameLoop);
}

reset();
updateHUD();
requestAnimationFrame(gameLoop);