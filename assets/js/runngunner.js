const canvas = document.getElementById('game');
const ctx = canvas.getContext('2d');
const scoreincrease = 10

// Utility loaders with fallbacks
function loadImage(src) {
    const img = new Image();
    img.src = src;
    img.onerror = () => { img._failed = true; };
    return img;
}

function loadSound(src) {
    const audio = new Audio(src);
    audio.onerror = () => { audio._failed = true; };
    return audio;
}

function playSound(snd) {
    if (snd && !snd._failed) {
        snd.currentTime = 0;
        snd.play().catch(() => { });
    }
}

const sprites = {
    player: loadImage('images/games/runngunner_player.png'),
    enemy: loadImage('images/games/runngunner_enemy.png'),
    power: {
        spread: loadImage('images/games/power_spread.png'),
        machine: loadImage('images/games/power_machine.png'),
        laser: loadImage('images/games/power_laser.png'),
        flame: loadImage('images/games/power_flame.png'),
        invincible: loadImage('images/games/power_invincible.png')
    }
};

const sounds = {
    shoot: loadSound('assets/sfx/runngunner_shoot.wav'),
    enemyShoot: loadSound('assets/sfx/runngunner_enemy_shoot.wav'),
    power: loadSound('assets/sfx/runngunner_power.wav')
};

const laneYs = [canvas.height * 0.25, canvas.height * 0.55, canvas.height * 0.85];
const laneHeight = 10;
const terrain = [[], [], []]; // segments per lane
const tileWidth = 120;
let scroll = 0;

const player = { x: 100, y: 0, w: 30, h: 40, lane: 1, cooldown: 0, weapon: 'normal', ammo: 0, invincible: 0, lives: 3 };
player.y = laneYs[player.lane] - player.h;

let bullets = [];
let enemyBullets = [];
let enemies = [];
let powerups = [];
let flames = [];
let score = 0;
let gameOverFlag = false;
let enemyTimer = 120;

function hitPlayer() {
    if (player.invincible > 0) return;
    player.lives--;
    player.invincible = 60;
    if (player.lives <= 0) {
        gameOver();
    }
}

const keys = {};
window.addEventListener('keydown', e => keys[e.code] = true);
window.addEventListener('keyup', e => keys[e.code] = false);

function rand(min, max) { return Math.random() * (max - min) + min; }

function laneHasGround(lane, x) {
    const arr = terrain[lane];
    for (const seg of arr) {
        if (x >= seg.x && x < seg.x + seg.w) return true;
    }
    return false;
}

function generateLane(lane) {
    const arr = terrain[lane];
    let last = arr.length ? arr[arr.length - 1].x + arr[arr.length - 1].w : 0;
    while (last < scroll + canvas.width + tileWidth * 3) {
        let type = 'flat';
        const opts = ['flat'];
        if (lane > 0) opts.push('up');
        if (lane < 2) opts.push('down');
        type = opts[Math.floor(Math.random() * opts.length)];
        const seg = { x: last, w: tileWidth, type };
        arr.push(seg);
        // also add counterpart for slopes
        if (type === 'up' && lane > 0) {
            terrain[lane - 1].push({ x: last, w: tileWidth, type: 'down' });
        } else if (type === 'down' && lane < 2) {
            terrain[lane + 1].push({ x: last, w: tileWidth, type: 'up' });
        }
        last += tileWidth;
        if (Math.random() < 0.1) last += tileWidth; // gap
    }
    // remove old segments
    while (arr.length && arr[0].x + arr[0].w < scroll - tileWidth) arr.shift();
}

function spawnEnemy() {
    const lane = Math.floor(Math.random() * 3);
    const enemy = { x: scroll + canvas.width + rand(0, 200), lane, y: laneYs[lane] - 40, w: 30, h: 40, hp: 1, shoot: rand(60, 180) };
    enemies.push(enemy);
}

function spawnPowerup(x, y) {
    const types = ['spread', 'machine', 'laser', 'flame', 'invincible'];
    const type = types[Math.floor(Math.random() * types.length)];
    powerups.push({ x, y, w: 20, h: 20, type });
}

function weaponCooldown() {
    switch (player.weapon) {
        case 'machine': return 5;
        case 'laser': return 20;
        case 'spread': return 15;
        default: return 15;
    }
}

function fireWeapon() {
    if (player.weapon === 'flame') {
        if (player.ammo <= 0) { player.weapon = 'normal'; return; }
        player.ammo--;
        const angle = keys['ArrowUp'] ? -Math.PI / 6 : keys['ArrowDown'] ? Math.PI / 6 : 0;
        flames.push({ x: scroll + player.x + player.w, y: player.y + player.h / 2, angle, life: 15, length: 80 });
        playSound(sounds.shoot);
        if (player.ammo <= 0) player.weapon = 'normal';
        return;
    }
    if (player.cooldown > 0) return;
    player.cooldown = weaponCooldown();
    const base = { x: scroll + player.x + player.w, y: player.y + player.h / 2, w: 8, h: 4 };
    if (player.weapon === 'spread') {
        for (let i = -2; i <= 2; i++) {
            const a = i * 5 * Math.PI / 180;
            bullets.push({ ...base, dx: 8 * Math.cos(a), dy: 8 * Math.sin(a) });
        }
        player.ammo--;
        if (player.ammo <= 0) player.weapon = 'normal';
    } else if (player.weapon === 'machine') {
        bullets.push({ ...base, dx: 8, dy: 0 });
        player.ammo--;
        if (player.ammo <= 0) player.weapon = 'normal';
    } else if (player.weapon === 'laser') {
        bullets.push({ x: base.x, y: base.y - 1, w: 20, h: 2, dx: 15, dy: 0, penetrate: true });
        player.ammo--;
        if (player.ammo <= 0) player.weapon = 'normal';
    } else {
        bullets.push({ ...base, dx: 8, dy: 0 });
    }
    playSound(sounds.shoot);
}

function applyPower(type) {
    player.weapon = 'normal';
    player.ammo = 0;
    if (type === 'invincible') {
        player.invincible = 600; // 10 seconds at 60fps
        return;
    }
    player.weapon = type;
    switch (type) {
        case 'spread': player.ammo = 10; break;
        case 'machine': player.ammo = 40; break;
        case 'laser': player.ammo = 5; break;
        case 'flame': player.ammo = 100; break;
    }
    playSound(sounds.power);
}

function isColliding(a, b) {
    return a.x < b.x + b.w && a.x + a.w > b.x && a.y < b.y + b.h && a.y + a.h > b.y;
}

function gameOver() {
    if (gameOverFlag) return;
    gameOverFlag = true;
    const submit = confirm('Submit score for rewards?');
    const redirect = () => { window.location.href = 'index.php?pg=games'; };
    if (submit) {
        fetch('score_exchange.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ game: 'runngunner', score })
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

function update() {
    scroll += 2;
    if (player.cooldown > 0) player.cooldown--;
    if (player.invincible > 0) player.invincible--;

    // lane changes
    const worldX = scroll + player.x;
    if (keys['KeyW'] && !keys._w && player.lane > 0 && laneHasGround(player.lane - 1, worldX)) {
        player.lane--;
        keys._w = true;
    }
    if (keys['KeyS'] && !keys._s && player.lane < 2 && laneHasGround(player.lane + 1, worldX)) {
        player.lane++;
        keys._s = true;
    }
    if (!keys['KeyW']) keys._w = false;
    if (!keys['KeyS']) keys._s = false;
    player.y = laneYs[player.lane] - player.h;

    if (keys['Space']) fireWeapon();

    // generate terrain
    for (let l = 0; l < 3; l++) generateLane(l);

    // update bullets
    bullets.forEach(b => { b.x += b.dx; b.y += b.dy; });
    bullets = bullets.filter(b => b.x - scroll < canvas.width && b.x + b.w > scroll);

    flames.forEach(f => {
        f.x += 4 * Math.cos(f.angle);
        f.y += 4 * Math.sin(f.angle);
        f.life--;
    });
    flames = flames.filter(f => f.life > 0);

    // update enemies
    enemies.forEach(e => {
        e.x -= 2;
        e.shoot--;
        if (e.shoot <= 0) {
            enemyBullets.push({ x: e.x, y: e.y + e.h / 2, w: 6, h: 3, dx: -5, dy: 0 });
            e.shoot = rand(120, 240);
            playSound(sounds.enemyShoot);
        }
    });
    enemies = enemies.filter(e => e.x + e.w > scroll);

    // update enemy bullets
    enemyBullets.forEach(b => { b.x += b.dx; b.y += b.dy; });
    enemyBullets = enemyBullets.filter(b => b.x + b.w > scroll && b.x - scroll < canvas.width && !b._remove);

    // update powerups
    powerups = powerups.filter(p => p.x + p.w > scroll);

    // collisions
    const playerRect = { x: scroll + player.x, y: player.y, w: player.w, h: player.h };
    for (const b of bullets) {
        for (const e of enemies) {
            if (isColliding({ x: b.x, y: b.y, w: b.w, h: b.h }, e)) {
                e.hp--;
                if (!b.penetrate) b._remove = true;
                if (e.hp <= 0) {
                    e._remove = true;
                    score += scoreincrease;
                    if (Math.random() < 0.1) spawnPowerup(e.x, e.y + 10);
                }
            }
        }
    }
    bullets = bullets.filter(b => !b._remove);
    enemies = enemies.filter(e => !e._remove);

    for (const f of flames) {
        for (const e of enemies) {
            const flameRect = { x: f.x, y: f.y, w: f.length, h: 20 };
            if (isColliding(flameRect, e)) {
                e._remove = true;
                score += 100;
                if (Math.random() < 0.1) spawnPowerup(e.x, e.y + 10);
            }
        }
    }
    enemies = enemies.filter(e => !e._remove);

    for (const b of enemyBullets) {
        if (isColliding({ x: b.x, y: b.y, w: b.w, h: b.h }, playerRect)) {
            hitPlayer();
            b._remove = true;
        }
    }

    for (const e of enemies) {
        if (isColliding(e, playerRect)) {
            hitPlayer();
            e._remove = true;
        }
    }

    for (const p of powerups) {
        if (isColliding(p, playerRect)) {
            applyPower(p.type);
            p._remove = true;
        }
    }
    powerups = powerups.filter(p => !p._remove);

    // spawn enemies
    enemyTimer--;
    if (enemyTimer <= 0) {
        spawnEnemy();
        enemyTimer = rand(90, 180);
    }

    draw();
    if (!gameOverFlag) requestAnimationFrame(update);
}

function drawTerrain() {
    ctx.fillStyle = '#444';
    for (let lane = 0; lane < 3; lane++) {
        const arr = terrain[lane];
        for (const seg of arr) {
            const x = seg.x - scroll;
            if (x > canvas.width || x + seg.w < -tileWidth) continue;
            const y1 = laneYs[lane];
            if (seg.type === 'flat') {
                ctx.fillRect(x, y1, seg.w, laneHeight);
            } else if (seg.type === 'up') {
                const y2 = laneYs[lane - 1];
                ctx.beginPath();
                ctx.moveTo(x, y1 + laneHeight);
                ctx.lineTo(x, y1);
                ctx.lineTo(x + seg.w, y2);
                ctx.lineTo(x + seg.w, y2 + laneHeight);
                ctx.fill();
            } else if (seg.type === 'down') {
                const y2 = laneYs[lane + 1];
                ctx.beginPath();
                ctx.moveTo(x, y1);
                ctx.lineTo(x + seg.w, y2);
                ctx.lineTo(x + seg.w, y2 + laneHeight);
                ctx.lineTo(x, y1 + laneHeight);
                ctx.fill();
            }
        }
    }
}

function draw() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    drawTerrain();

    // powerups
    for (const p of powerups) {
        const x = p.x - scroll;
        if (sprites.power[p.type] && !sprites.power[p.type]._failed) {
            ctx.drawImage(sprites.power[p.type], x, p.y, 20, 20);
        } else {
            ctx.fillStyle = '#0f0';
            ctx.fillRect(x, p.y, 20, 20);
        }
    }

    // enemies
    for (const e of enemies) {
        const x = e.x - scroll;
        if (sprites.enemy && !sprites.enemy._failed) {
            ctx.drawImage(sprites.enemy, x, e.y, e.w, e.h);
        } else {
            ctx.fillStyle = 'red';
            ctx.fillRect(x, e.y, e.w, e.h);
        }
    }

    // bullets
    ctx.fillStyle = 'yellow';
    for (const b of bullets) {
        ctx.fillRect(b.x - scroll, b.y, b.w, b.h);
    }
    ctx.fillStyle = 'purple';
    for (const b of enemyBullets) {
        ctx.fillRect(b.x - scroll, b.y, b.w, b.h);
    }

    // flames
    ctx.fillStyle = 'orange';
    for (const f of flames) {
        const x = f.x - scroll;
        ctx.save();
        ctx.translate(x, f.y);
        ctx.rotate(f.angle);
        ctx.fillRect(0, -10, f.length, 20);
        ctx.restore();
    }

    // player
    if (sprites.player && !sprites.player._failed) {
        ctx.drawImage(sprites.player, player.x, player.y, player.w, player.h);
    } else {
        ctx.fillStyle = 'blue';
        ctx.fillRect(player.x, player.y, player.w, player.h);
    }
    if (player.invincible > 0) {
        ctx.strokeStyle = 'yellow';
        ctx.lineWidth = 3;
        ctx.strokeRect(player.x - 2, player.y - 2, player.w + 4, player.h + 4);
    }

    document.getElementById('scoreVal').textContent = score;
    const livesEl = document.getElementById('livesVal');
    if (livesEl) livesEl.textContent = player.lives;
}

// init terrain
for (let l = 0; l < 3; l++) generateLane(l);
requestAnimationFrame(update);