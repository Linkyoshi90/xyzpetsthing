const canvas = document.getElementById('game');
const ctx = canvas.getContext('2d');

const player = { x: canvas.width / 2 - 15, y: canvas.height - 30, w: 30, h: 20, speed: 5, cooldown: 0 };
let bullets = [];
let enemyBullets = [];
let enemies = [];
let powerups = [];
let activePowers = { rapid: 0, spread: 0, shield: 0 };
let clones = [];
let score = 0;
let level = 1;
let enemyDir = 1;
let bonusShip = null;
let bonusShipTimer = 0;
let gameOverFlag = false;
let explosions = [];

const keys = {};
document.addEventListener('keydown', e => keys[e.code] = true);
document.addEventListener('keyup', e => keys[e.code] = false);
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

function playSound(sound) {
    if (sound && !sound._failed) {
        sound.currentTime = 0;
        sound.play().catch(() => { });
    }
}

const sprites = {
    player: loadImage('images/games/player.png'),
    enemy: {
        green: loadImage('images/centaur_f_green.webp'),
        blue: loadImage('images/centaur_f_blue.webp'),
        red: loadImage('images/centaur_f_red.webp'),
        purple: loadImage('images/centaur_f_purple.webp'),
        yellow: loadImage('images/yuki_onna_f_yellow.webp')
    },
    bonus: loadImage('images/games/bonus.png'),
    powerup: {
        rapid: loadImage('images/games/powerupmachinegun.png'),
        spread: loadImage('images/games/powerup3shot.png'),
        shield: loadImage('images/games/powerupshield.png'),
        clone: loadImage('images/games/powerupclone.png')
    }
};

const sounds = {
    playerfire: loadSound('assets/sfx/playerfire.mp3'),
    playerhit: loadSound('assets/sfx/playerhit.wav'),
    powerup3shot: loadSound('assets/sfx/powerup3shot.wav'),
    powerupmachinegun: loadSound('assets/sfx/powerupmachinegun.wav'),
    powerupshield: loadSound('assets/sfx/powerupshield.wav'),
    enemyfire: loadSound('assets/sfx/enemyfire.wav'),
    specialship: loadSound('assets/sfx/specialship.wav'),
    specialshiphit: loadSound('assets/sfx/specialshiphit.wav')
};

const powerSoundMap = {
    rapid: sounds.powerupmachinegun,
    spread: sounds.powerup3shot,
    shield: sounds.powerupshield
};

function isColliding(a, b) {
    return a.x < b.x + b.w && a.x + a.w > b.x && a.y < b.y + b.h && a.y + a.h > b.y;
}

const ENEMY_TYPES = {
    green: { hp: 1, color: 'green', value: 10 },
    blue: { hp: 2, color: 'blue', value: 20 },
    red: { hp: 3, color: 'red', value: 30 },
    purple: { hp: 5, color: 'purple', value: 50 }
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
    const types = ['rapid', 'spread', 'shield', 'clone'];
    const type = types[Math.floor(Math.random() * types.length)];
    powerups.push({ x: x - 7, y: y - 7, w: 14, h: 14, type, speed: 2 });
}

function spawnBonusShip() {
    bonusShip = { x: -40, y: 20, w: 40, h: 20, speed: 2, baseY: 20, jerkTimer: 0 };
    playSound(sounds.specialship);
}

function scheduleBonusShip() {
    bonusShipTimer = 300 + Math.random() * 300;
}


function spawnClone() {
    if (clones.length >= 3) return;
    const offset = 40 * (clones.length + 1);
    const dir = clones.length % 2 === 0 ? -1 : 1;
    clones.push({ x: player.x, y: player.y, w: player.w, h: player.h, offset: dir * offset });
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
scheduleBonusShip();
function gameOver() {
    if (gameOverFlag) return;
    gameOverFlag = true;
    const submit = confirm('Submit score for rewards?');
    const redirect = () => { window.location.href = 'index.php?pg=games'; };
    if (submit) {
        fetch('score_exchange.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ game: 'gardeninvaderz', score })
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

function fireFrom(entity) {
    if (activePowers.spread > 0) {
        bullets.push({ x: entity.x + entity.w / 2 - 10, y: entity.y, w: 4, h: 10, speed: 6 });
        bullets.push({ x: entity.x + entity.w / 2 - 2, y: entity.y, w: 4, h: 10, speed: 6 });
        bullets.push({ x: entity.x + entity.w / 2 + 6, y: entity.y, w: 4, h: 10, speed: 6 });
    } else {
        bullets.push({ x: entity.x + entity.w / 2 - 2, y: entity.y, w: 4, h: 10, speed: 6 });
    }
}

function shoot() {
    fireFrom(player);
    clones.forEach(c => fireFrom(c));
    playSound(sounds.playerfire);
}

function reset() {
    score = 0;
    level = 1;
    enemyDir = 1;
    bullets = [];
    enemyBullets = [];
    powerups = [];
    activePowers = { rapid: 0, spread: 0, shield: 0 };
    clones = [];
    bonusShip = null;
    bonusShipTimer = 0;
    player.x = canvas.width / 2 - player.w / 2;
    generateWave(level);
    scheduleBonusShip();
}

function update() {
    // player movement
    if (keys.KeyA) player.x -= player.speed;
    if (keys.KeyD) player.x += player.speed;
    player.x = Math.max(0, Math.min(canvas.width - player.w, player.x));
    clones.forEach(c => {
        c.x = player.x + c.offset;
        c.y = player.y;
    });
    // shooting
    if (keys.KeyW && player.cooldown <= 0) {
        shoot();
        player.cooldown = activePowers.rapid > 0 ? 5 : 15;
    }
    if (player.cooldown > 0) player.cooldown--;

    // bullets
    bullets.forEach(b => b.y -= b.speed);
    bullets = bullets.filter(b => b.y + b.h > 0);

    enemyBullets.forEach(b => b.y += b.speed);
    for (let i = enemyBullets.length - 1; i >= 0; i--) {
        const b = enemyBullets[i];
        if (isColliding(b, player)) {
            if (activePowers.shield > 0) {
                enemyBullets.splice(i, 1);
                continue;
            }
            playSound(sounds.playerhit);
            gameOver();
            return;
        }
        let hit = false;
        for (let ci = clones.length - 1; ci >= 0; ci--) {
            if (isColliding(b, clones[ci])) {
                if (activePowers.shield > 0) {
                    enemyBullets.splice(i, 1);
                } else {
                    clones.splice(ci, 1);
                    enemyBullets.splice(i, 1);
                }
                hit = true;
                break;
            }
        }
        if (hit) continue;
        if (b.y > canvas.height) enemyBullets.splice(i, 1);
    }

    if (!bonusShip && bonusShipTimer > 0) {
        bonusShipTimer--;
        if (bonusShipTimer <= 0) spawnBonusShip();
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
                const bx = bonusShip.x + bonusShip.w / 2;
                const by = bonusShip.y + bonusShip.h / 2;
                bullets.splice(bi, 1);
                for (const t in activePowers) activePowers[t] = 0;
                activePowers.shield = 300;
                enemies.forEach(e => score += e.value);
                enemies = [];
                enemyBullets = [];
                bonusShip = null;
                explosions.push({ x: bx, y: by, timer: 20 });
                playSound(sounds.specialshiphit);
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
            if (Math.random() < fireChance && enemyBullets.length < 10) {
                enemyBullets.push({ x: e.x + e.w / 2 - 2, y: e.y + e.h, w: 4, h: 10, speed: 3 });
                playSound(sounds.enemyfire);
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
                    const ex = e.x + e.w / 2;
                    const ey = e.y + e.h / 2;
                    enemies.splice(ei, 1);
                    score += e.value;
                    explosions.push({ x: ex, y: ey, timer: 20 });
                    if (Math.random() < 0.1) spawnPowerUp(ex, ey);
                }
                break;
            }
        }
    }

    // powerups
    for (let i = powerups.length - 1; i >= 0; i--) {
        const p = powerups[i];
        p.y += p.speed;
        let collected = isColliding(p, player);
        if (!collected) {
            for (const c of clones) {
                if (isColliding(p, c)) { collected = true; break; }
            }
        }
        if (collected) {
            if (p.type === 'clone') {
                spawnClone();
            } else {
                const duration = p.type === 'shield' ? 300 : 600;
                for (const t in activePowers) activePowers[t] = 0;
                activePowers[p.type] = duration;
                playSound(powerSoundMap[p.type]);
            }
            powerups.splice(i, 1);
        } else if (p.y > canvas.height) {
            powerups.splice(i, 1);
        }
    }

    // active power timers
    for (const type in activePowers) {
        if (activePowers[type] > 0) activePowers[type]--;
    }

    // explosions
    explosions.forEach(ex => ex.timer--);
    explosions = explosions.filter(ex => ex.timer > 0);

    // enemy-player collisions
    for (const e of enemies) {
        if (isColliding(e, player) || e.y + e.h >= canvas.height) {
            gameOver();
            return;
        }
        for (let ci = clones.length - 1; ci >= 0; ci--) {
            if (isColliding(e, clones[ci])) {
                clones.splice(ci, 1);
            }
        }
    }

    // next wave
    if (enemies.length === 0) {
        level++;
        generateWave(level);
        bonusShip = null;
        scheduleBonusShip();
    }

    draw();
    requestAnimationFrame(update);
}

function draw() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // player and clones
    const playerSprite = sprites.player;
    const drawEntity = (ent) => {
        if (playerSprite && playerSprite.complete && !playerSprite._failed) {
            ctx.drawImage(playerSprite, ent.x, ent.y, ent.w, ent.h);
        } else {
            ctx.fillStyle = 'white';
            ctx.fillRect(ent.x, ent.y, ent.w, ent.h);
        }
    };
    drawEntity(player);
    clones.forEach(c => drawEntity(c));

    if (activePowers.shield > 0) {
        ctx.strokeStyle = 'magenta';
        ctx.lineWidth = 2;
        [player, ...clones].forEach(ent => {
            ctx.beginPath();
            ctx.arc(ent.x + ent.w / 2, ent.y + ent.h / 2, ent.w, 0, Math.PI * 2);
            ctx.stroke();
        });
    }

    // bullets
    ctx.fillStyle = 'red';
    bullets.forEach(b => ctx.fillRect(b.x, b.y, b.w, b.h));

    // enemy bullets
    ctx.fillStyle = 'orange';
    enemyBullets.forEach(b => ctx.fillRect(b.x, b.y, b.w, b.h));

    // bonus ship
    if (bonusShip) {
        const bonusSprite = sprites.bonus;
        if (bonusSprite && bonusSprite.complete && !bonusSprite._failed) {
            ctx.drawImage(bonusSprite, bonusShip.x, bonusShip.y, bonusShip.w, bonusShip.h);
        } else {
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
    }

    // enemies
    enemies.forEach(e => {
        const enemySprite = sprites.enemy[e.color];
        if (enemySprite && enemySprite.complete && !enemySprite._failed) {
            ctx.drawImage(enemySprite, e.x, e.y, e.w, e.h);
        } else {
            ctx.fillStyle = e.color;
            ctx.fillRect(e.x, e.y, e.w, e.h);
        }
    });

    // powerups
    powerups.forEach(p => {
        const sp = sprites.powerup[p.type];
        if (sp && sp.complete && !sp._failed) {
            ctx.drawImage(sp, p.x, p.y, p.w, p.h);
        } else if (p.type === 'rapid') {
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
        } else if (p.type === 'shield') {
            ctx.fillStyle = 'magenta';
            ctx.font = '16px sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText('S', p.x + p.w / 2, p.y + p.h / 2);
        } else {
            ctx.fillStyle = 'white';
            ctx.font = '16px sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText('C', p.x + p.w / 2, p.y + p.h / 2);
        }
    });

    // explosions
    explosions.forEach(ex => {
        const r = 20 - ex.timer;
        ctx.fillStyle = 'orange';
        ctx.beginPath();
        ctx.arc(ex.x, ex.y, r, 0, Math.PI * 2);
        ctx.fill();
    });

    document.getElementById('scoreVal').textContent = score;
}

update();