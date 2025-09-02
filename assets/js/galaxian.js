const canvas = document.getElementById('game');
const ctx = canvas.getContext('2d');

const player = { x: canvas.width / 2 - 15, y: canvas.height - 30, w: 30, h: 20, speed: 5, cooldown: 0 };
let bullets = [];
let enemyBullets = [];
let enemies = [];
let powerups = [];
let activePower = { type: null, timer: 0 };
let score = 0;
let level = 1;
let enemyDir = 1;
let bonusShip = null;

const keys = {};
document.addEventListener('keydown', e => keys[e.code] = true);
document.addEventListener('keyup', e => keys[e.code] = false);

function isColliding(a, b) {
    return a.x < b.x + b.w && a.x + a.w > b.x && a.y < b.y + b.h && a.y + a.h > b.y;
}

const ENEMY_TYPES = {
    green: { hp: 1, color: 'green', value: 10 },
    blue: { hp: 2, color: 'blue', value: 20 },
    red: { hp: 5, color: 'red', value: 50 },
    purple: { hp: 10, color: 'purple', value: 100 }
};

function randomEnemyType(lv) {
    const bag = [];
    bag.push(...Array(Math.max(5 - (lv - 1), 1)).fill(ENEMY_TYPES.green));
    if (lv >= 2) bag.push(...Array(Math.min(lv - 1, 4)).fill(ENEMY_TYPES.blue));
    if (lv >= 3) bag.push(...Array(Math.min(lv - 2, 3)).fill(ENEMY_TYPES.red));
    if (lv >= 4) bag.push(...Array(lv - 3).fill(ENEMY_TYPES.purple));
    const base = bag[Math.floor(Math.random() * bag.length)];
    return { hp: base.hp, color: base.color, value: base.value };
}

function spawnPowerUp(x, y) {
    const types = ['rapid', 'spread', 'shield'];
    const type = types[Math.floor(Math.random() * types.length)];
    powerups.push({ x: x - 7, y: y - 7, w: 14, h: 14, type, speed: 2 });
}

function spawnBonusShip() {
    bonusShip = { x: -40, y: 20, w: 40, h: 20, speed: 2, baseY: 20, jerkTimer: 0 };
}

function generateWave(lv) {
    enemies = [];
    const rows = Math.min(3 + lv, 8);
    const cols = 6;
    const offsetX = 40;
    const offsetY = 40;
    for (let r = 0; r < rows; r++) {
        for (let c = 0; c < cols; c++) {
            const type = randomEnemyType(lv);
            enemies.push({
                x: offsetX + c * 50,
                y: offsetY + r * 30,
                w: 30,
                h: 20,
                speed: 1 + lv * 0.2,
                hp: type.hp,
                color: type.color,
                value: type.value
            });
        }
    }
}

generateWave(level);
spawnBonusShip();

function shoot() {
    if (activePower.type === 'spread') {
        bullets.push({ x: player.x + player.w / 2 - 10, y: player.y, w: 4, h: 10, speed: 6 });
        bullets.push({ x: player.x + player.w / 2 - 2, y: player.y, w: 4, h: 10, speed: 6 });
        bullets.push({ x: player.x + player.w / 2 + 6, y: player.y, w: 4, h: 10, speed: 6 });
    } else {
        bullets.push({ x: player.x + player.w / 2 - 2, y: player.y, w: 4, h: 10, speed: 6 });
    }
}

function reset() {
    score = 0;
    level = 1;
    enemyDir = 1;
    bullets = [];
    enemyBullets = [];
    powerups = [];
    activePower = { type: null, timer: 0 };
    player.x = canvas.width / 2 - player.w / 2;
    generateWave(level);
    spawnBonusShip();
}

function update() {
    // player movement
    if (keys.ArrowLeft) player.x -= player.speed;
    if (keys.ArrowRight) player.x += player.speed;
    player.x = Math.max(0, Math.min(canvas.width - player.w, player.x));

    // shooting
    if (keys.Space && player.cooldown <= 0) {
        shoot();
        player.cooldown = activePower.type === 'rapid' ? 5 : 15;
    }
    if (player.cooldown > 0) player.cooldown--;

    // bullets
    bullets.forEach(b => b.y -= b.speed);
    bullets = bullets.filter(b => b.y + b.h > 0);

    enemyBullets.forEach(b => b.y += b.speed);
    for (let i = enemyBullets.length - 1; i >= 0; i--) {
        const b = enemyBullets[i];
        if (isColliding(b, player)) {
            if (activePower.type === 'shield') {
                enemyBullets.splice(i, 1);
                continue;
            }
            alert('Game over!');
            reset();
            break;
        }
        if (b.y > canvas.height) enemyBullets.splice(i, 1);
    }

    // bonus ship movement
    if (bonusShip) {
        bonusShip.x += bonusShip.speed;
        bonusShip.jerkTimer--;
        if (bonusShip.jerkTimer <= 0) {
            bonusShip.jerkTimer = 20 + Math.random() * 20;
            bonusShip.speed = 1 + Math.random() * 3;
            bonusShip.y = bonusShip.baseY + (Math.random() * 20 - 10);
        }
        if (bonusShip.x > canvas.width + bonusShip.w) bonusShip = null;
    }

    // bullets hitting bonus ship
    if (bonusShip) {
        for (let bi = bullets.length - 1; bi >= 0; bi--) {
            if (isColliding(bullets[bi], bonusShip)) {
                bullets.splice(bi, 1);
                activePower = { type: 'shield', timer: 300 };
                enemies.forEach(e => score += e.value);
                enemies = [];
                enemyBullets = [];
                bonusShip = null;
                break;
            }
        }
    }

    // enemies movement
    let swap = false;
    enemies.forEach(e => {
        e.x += e.speed * enemyDir;
        if (e.x <= 0 || e.x + e.w >= canvas.width) swap = true;
        if (level > 1) {
            const fireChance = Math.min((level - 1) * 0.003, 0.03);
            if (Math.random() < fireChance) {
                enemyBullets.push({ x: e.x + e.w / 2 - 2, y: e.y + e.h, w: 4, h: 10, speed: 3 });
            }
        }
    });
    if (swap) {
        enemyDir *= -1;
        enemies.forEach(e => e.y += 20);
    }

    // bullet-enemy collisions
    for (let bi = bullets.length - 1; bi >= 0; bi--) {
        const b = bullets[bi];
        for (let ei = enemies.length - 1; ei >= 0; ei--) {
            const e = enemies[ei];
            if (isColliding(b, e)) {
                bullets.splice(bi, 1);
                e.hp--;
                if (e.hp <= 0) {
                    enemies.splice(ei, 1);
                    score += e.value;
                    if (Math.random() < 0.1) spawnPowerUp(e.x + e.w / 2, e.y + e.h / 2);
                }
                break;
            }
        }
    }

    // powerups
    for (let i = powerups.length - 1; i >= 0; i--) {
        const p = powerups[i];
        p.y += p.speed;
        if (isColliding(p, player)) {
            const duration = p.type === 'shield' ? 300 : 600;
            activePower = { type: p.type, timer: duration };
            powerups.splice(i, 1);
        } else if (p.y > canvas.height) {
            powerups.splice(i, 1);
        }
    }

    // active power timer
    if (activePower.timer > 0) {
        activePower.timer--;
        if (activePower.timer === 0) activePower.type = null;
    }

    // enemy-player collisions
    for (const e of enemies) {
        if (isColliding(e, player) || e.y + e.h >= canvas.height) {
            alert('Game over!');
            reset();
            break;
        }
    }

    // next wave
    if (enemies.length === 0) {
        level++;
        generateWave(level);
        spawnBonusShip();
    }

    draw();
    requestAnimationFrame(update);
}

function draw() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // player
    ctx.fillStyle = 'white';
    ctx.fillRect(player.x, player.y, player.w, player.h);

    if (activePower.type === 'shield') {
        ctx.strokeStyle = 'magenta';
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.arc(player.x + player.w / 2, player.y + player.h / 2, player.w, 0, Math.PI * 2);
        ctx.stroke();
    }

    // bullets
    ctx.fillStyle = 'red';
    bullets.forEach(b => ctx.fillRect(b.x, b.y, b.w, b.h));

    // enemy bullets
    ctx.fillStyle = 'orange';
    enemyBullets.forEach(b => ctx.fillRect(b.x, b.y, b.w, b.h));

    // bonus ship
    if (bonusShip) {
        ctx.fillStyle = 'pink';
        ctx.beginPath();
        ctx.moveTo(bonusShip.x, bonusShip.y + bonusShip.h);
        ctx.lineTo(bonusShip.x + bonusShip.w / 2, bonusShip.y);
        ctx.lineTo(bonusShip.x + bonusShip.w, bonusShip.y + bonusShip.h);
        ctx.lineTo(bonusShip.x + bonusShip.w * 0.75, bonusShip.y + bonusShip.h / 2);
        ctx.lineTo(bonusShip.x + bonusShip.w * 0.25, bonusShip.y + bonusShip.h / 2);
        ctx.closePath();
        ctx.fill();
    }

    // enemies
    enemies.forEach(e => {
        ctx.fillStyle = e.color;
        ctx.fillRect(e.x, e.y, e.w, e.h);
    });

    // powerups
    powerups.forEach(p => {
        if (p.type === 'rapid') {
            ctx.fillStyle = 'yellow';
            ctx.beginPath();
            ctx.arc(p.x + p.w / 2, p.y + p.h / 2, p.w / 2, 0, Math.PI * 2);
            ctx.fill();
        } else if (p.type === 'spread') {
            ctx.fillStyle = 'cyan';
            ctx.font = '16px sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText('3', p.x + p.w / 2, p.y + p.h / 2);
        } else {
            ctx.fillStyle = 'magenta';
            ctx.font = '16px sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText('S', p.x + p.w / 2, p.y + p.h / 2);
        }
    });

    document.getElementById('scoreVal').textContent = score;
}

update();