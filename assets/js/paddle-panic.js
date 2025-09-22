const canvas = document.getElementById('pong');
const ctx = canvas.getContext('2d');

const paddle = {
    x: 24,
    y: canvas.height / 2 - 60,
    w: 18,
    h: 120,
    speed: 6,
    maxHeight: canvas.height * 0.85
};

let balls = [];
let upgrades = [];
let score = 0;
let hits = 0;
let misses = 0;
let hitsUntilUpgrade = 5;
let gameOver = false;
let lastSpawn = 0;

const scoreEl = document.getElementById('scoreVal');
const hitEl = document.getElementById('hitVal');
const missEl = document.getElementById('missVal');
const upgradeEl = document.getElementById('upgradeProgress');
const finalScoreEl = document.getElementById('finalScoreVal');
const gameOverNotice = document.getElementById('gameOverNotice');

const keys = {};
document.addEventListener('keydown', e => {
    keys[e.code] = true;
    if (['ArrowUp', 'ArrowDown', 'KeyW', 'KeyS'].includes(e.code)) {
        e.preventDefault();
    }
});
document.addEventListener('keyup', e => keys[e.code] = false);

function spawnBall(direction = -1) {
    const baseSpeed = 4 + Math.random() * 1.5;
    const angle = Math.random() * Math.PI / 3 - Math.PI / 6;
    const speedX = Math.cos(angle) * baseSpeed * direction;
    const speedY = Math.sin(angle) * baseSpeed;
    balls.push({
        x: canvas.width - 80,
        y: Math.random() * (canvas.height - 160) + 80,
        vx: speedX,
        vy: speedY,
        radius: 10
    });
    lastSpawn = performance.now();
}

function spawnUpgrade() {
    const type = Math.random() < 0.5 ? 'multiball' : 'paddle';
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
    if (upgrade.type === 'multiball') {
        const clones = [];
        balls.forEach(ball => {
            const speed = Math.hypot(ball.vx, ball.vy);
            const newAngle = Math.atan2(ball.vy, ball.vx) + (Math.random() * 0.6 - 0.3);
            clones.push({
                x: ball.x,
                y: ball.y,
                vx: Math.cos(newAngle) * speed,
                vy: Math.sin(newAngle) * speed,
                radius: ball.radius
            });
        });
        balls.push(...clones);
    } else if (upgrade.type === 'paddle') {
        paddle.h = Math.min(paddle.h * 1.3, paddle.maxHeight);
    }
}

function updateHUD() {
    scoreEl.textContent = Math.floor(score);
    hitEl.textContent = hits;
    missEl.textContent = misses;
    upgradeEl.textContent = upgrades.length > 0 ? 'Ready!' : hitsUntilUpgrade;
}

function reset() {
    balls = [];
    upgrades = [];
    score = 0;
    hits = 0;
    misses = 0;
    hitsUntilUpgrade = 5;
    gameOver = false;
    paddle.h = 120;
    paddle.y = canvas.height / 2 - paddle.h / 2;
    updateHUD();
    spawnBall(-1);
}

function endGame() {
    if (gameOver) return;
    gameOver = true;
    finalScoreEl.textContent = Math.floor(score);
    gameOverNotice.classList.add('show');
    const submit = confirm(`You let through 10 balls! Submit score of ${Math.floor(score)}?`);
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

function handlePaddleMovement() {
    if (keys.KeyW || keys.ArrowUp) {
        paddle.y -= paddle.speed;
    }
    if (keys.KeyS || keys.ArrowDown) {
        paddle.y += paddle.speed;
    }
    if (paddle.y < 10) paddle.y = 10;
    if (paddle.y + paddle.h > canvas.height - 10) {
        paddle.y = canvas.height - 10 - paddle.h;
    }
}

function registerMiss(index) {
    if (index >= 0 && index < balls.length) {
        balls.splice(index, 1);
    }
    misses += 1;
    updateHUD();
    if (misses >= 10) {
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
            const speed = Math.hypot(ball.vx, ball.vy) * 1.1;
            ball.vx = Math.cos(bounceAngle) * speed;
            ball.vy = Math.sin(bounceAngle) * speed;
            if (ball.vx < 0.5) {
                ball.vx = Math.abs(ball.vx);
            }
            ball.x = paddle.x + paddle.w + ball.radius + 1;
            hits += 1;
            score += Math.max(100, Math.round(speed * 15));
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
        gradient.addColorStop(0, '#f8fafc');
        gradient.addColorStop(1, '#38bdf8');
        ctx.fillStyle = gradient;
        ctx.beginPath();
        ctx.arc(ball.x, ball.y, ball.radius, 0, Math.PI * 2);
        ctx.fill();
    });

    upgrades.forEach(up => {
        ctx.fillStyle = up.type === 'multiball' ? 'rgba(244, 114, 182, 0.9)' : 'rgba(34, 197, 94, 0.9)';
        ctx.fillRect(up.x, up.y, up.w, up.h);
        ctx.fillStyle = 'rgba(15, 23, 42, 0.85)';
        ctx.font = '16px sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(up.type === 'multiball' ? '×2' : '+', up.x + up.w / 2, up.y + up.h / 2);
    });
}

function gameLoop() {
    if (!gameOver) {
        handlePaddleMovement();
        for (let i = balls.length - 1; i >= 0; i--) {
            handleBallPhysics(balls[i], i);
        }
        updateUpgrades();
        if (balls.length === 0 && !gameOver && performance.now() - lastSpawn > 300) {
            spawnBall(-1);
        }
    }
    drawCourt();
    requestAnimationFrame(gameLoop);
}

reset();
updateHUD();
requestAnimationFrame(gameLoop);