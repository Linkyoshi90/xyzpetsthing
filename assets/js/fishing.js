(() => {
    const GAME_WIDTH = 600;
    const GAME_HEIGHT = 600;
    const UI_WIDTH = 200;
    const WATER_LEVEL = 100; // Y position where water starts
    const FALLBACK_FISH_SIZE = 70;

    const KEYS = {
        W: false,
        A: false,
        S: false,
        D: false
    };

    const STATE = {
        IDLE: 0,
        CASTING_DOWN: 1,
        REELING_UP: 2,
        GAME_OVER: 3
    };

    const slugify = (str) => str.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '');

    class FishingGame {
        constructor() {
            this.gameCanvas = document.getElementById('fishing-game');
            this.uiCanvas = document.getElementById('fishing-ui');
            this.ctx = this.gameCanvas.getContext('2d');
            this.uiCtx = this.uiCanvas.getContext('2d');

            this.state = STATE.IDLE;
            this.bait = 20;
            this.score = 0;
            this.startTime = null;
            this.elapsedTime = "00:00";

            this.cameraY = 0;

            this.playerX = GAME_WIDTH / 2;
            this.hook = { x: GAME_WIDTH / 2, y: 60, vy: 0, caughtFish: [] };
            this.fishes = [];
            this.particles = [];
            
            this.creatures = [];
            this.spritePool = [];
            this.imageCache = new Map();
            this.gameOverFlag = false;
            this.exchangeButton = document.getElementById('fishing-exchange-btn');
            this.exchangeStatus = document.getElementById('fishing-finish-status');
            this.exchangeButton?.addEventListener('click', () => this.submitScore());

            this.bindInput();
            this.loadCreatures();
            this.gameLoop();
        }

        bindInput() {
            window.addEventListener('keydown', (e) => {
                const key = e.key.toUpperCase();
                if (KEYS.hasOwnProperty(key)) KEYS[key] = true;

                if ((key === 'S' || key === 'W') && this.state === STATE.IDLE && this.bait > 0) {
                    this.startCast();
                }
            });

            window.addEventListener('keyup', (e) => {
                const key = e.key.toUpperCase();
                if (KEYS.hasOwnProperty(key)) KEYS[key] = false;
            });
        }

        async loadCreatures() {
            try {
                const [nameRes, mapRes] = await Promise.all([
                    fetch('data-readonly/available_creatures.txt'),
                    fetch('assets/data/fishing_creatures.json')
                ]);

                if (!nameRes.ok || !mapRes.ok) return;

                const names = (await nameRes.text())
                    .split(/\r?\n/)
                    .map(n => n.trim())
                    .filter(Boolean);
                const allowedSlugs = new Set(names.map(slugify));

                const mapJson = await mapRes.json();
                this.creatures = (mapJson.creatures || [])
                    .filter(entry => allowedSlugs.has(entry.slug) && Array.isArray(entry.files))
                    .map(entry => ({
                        name: entry.name,
                        slug: entry.slug,
                        files: entry.files
                    }));

                this.spritePool = this.creatures.flatMap(creature =>
                    creature.files.map(file => this.registerSprite(file, creature.name))
                );

                // Ensure any already-spawned fish pick up sprites once loaded
                this.fishes.forEach(fish => {
                    if (!fish.sprite) {
                        const sprite = this.pickCreatureSprite();
                        if (sprite) this.applySpriteToFish(fish, sprite);
                    }
                });
            } catch (err) {
                console.error('Failed to load fishing sprites', err);
            }
        }

        registerSprite(file, creatureName) {
            if (this.imageCache.has(file)) return this.imageCache.get(file);

            const img = new Image();
            const sprite = {
                file,
                name: creatureName,
                img,
                loaded: false,
                width: FALLBACK_FISH_SIZE,
                height: FALLBACK_FISH_SIZE
            };

            img.onload = () => {
                sprite.loaded = true;
                const { width, height } = this.getSpriteDimensions(img);
                sprite.width = width;
                sprite.height = height;
            };
            img.onerror = () => {
                sprite.loaded = false;
            };
            img.src = `images/${file}`;

            this.imageCache.set(file, sprite);
            return sprite;
        }

        getSpriteDimensions(img) {
            if (img && img.naturalWidth && img.naturalHeight) {
                const aspect = img.naturalWidth / img.naturalHeight;
                const height = Math.min(Math.max(FALLBACK_FISH_SIZE, 48), 96);
                const width = Math.min(Math.max(height * aspect, 48), 140);
                return { width, height };
            }
            return { width: FALLBACK_FISH_SIZE, height: FALLBACK_FISH_SIZE };
        }

        pickCreatureSprite() {
            if (!this.spritePool.length) return null;
            const idx = Math.floor(Math.random() * this.spritePool.length);
            return this.spritePool[idx];
        }

        applySpriteToFish(fish, sprite) {
            fish.sprite = sprite;
            if (sprite.loaded) {
                fish.width = sprite.width;
                fish.height = sprite.height;
            } else {
                sprite.img.onload = () => {
                    const { width, height } = this.getSpriteDimensions(sprite.img);
                    sprite.width = width;
                    sprite.height = height;
                    fish.width = width;
                    fish.height = height;
                };
            }
        }

        startCast() {
            if (this.state === STATE.GAME_OVER || this.gameOverFlag) return;
            if (!this.startTime) this.startTime = Date.now();
            this.bait--;
            this.state = STATE.CASTING_DOWN;
            this.hook.y = WATER_LEVEL + 10;
            this.hook.caughtFish = [];
            this.hook.vy = 2;
        }
        
        endGame() {
            if (this.gameOverFlag) return;
            this.state = STATE.GAME_OVER;
            this.gameOverFlag = true;
            this.hook.caughtFish = [];
            this.fishes = [];
            this.cameraY = 0;

            if (this.exchangeStatus) {
                this.exchangeStatus.textContent = 'Out of bait! Submit your score to convert it to dosh.';
            }
            if (this.exchangeButton) {
                this.exchangeButton.hidden = false;
                this.exchangeButton.disabled = false;
            }
        }

        createConfetti(x, y) {
            for (let i = 0; i < 30; i++) {
                this.particles.push({
                    x: x,
                    y: y,
                    vx: (Math.random() - 0.5) * 10,
                    vy: (Math.random() - 0.5) * 10,
                    life: 1.0,
                    color: `hsl(${Math.random() * 360}, 100%, 50%)`
                });
            }
        }

        spawnFish() {
            const viewTop = this.cameraY;
            const viewBottom = this.cameraY + GAME_HEIGHT;
            const spawnDepth = viewTop + (Math.random() * (GAME_HEIGHT + 200));

            const isGold = Math.random() < 0.1;
            const hasBait = Math.random() < 0.0005;

            const direction = Math.random() > 0.5 ? 1 : -1;
            const startX = direction === 1 ? -100 : GAME_WIDTH + 100;
            
            const fish = {
                x: startX,
                y: Math.max(spawnDepth, WATER_LEVEL + 50),
                width: FALLBACK_FISH_SIZE,
                height: FALLBACK_FISH_SIZE,
                speed: (1 + Math.random() * 2) * direction,
                type: Math.floor(Math.random() * 3),
                isGold: isGold,
                hasBait: hasBait,
                caught: false,
                sprite: null
            };

            const sprite = this.pickCreatureSprite();
            if (sprite) this.applySpriteToFish(fish, sprite);

            this.fishes.push(fish);
        }

        update() {
            if (this.startTime) {
                const totalSeconds = Math.floor((Date.now() - this.startTime) / 1000);
                const m = Math.floor(totalSeconds / 60).toString().padStart(2, '0');
                const s = (totalSeconds % 60).toString().padStart(2, '0');
                this.elapsedTime = `${m}:${s}`;
            }
            if (!this.gameOverFlag && this.bait <= 0 && this.state === STATE.IDLE) {
                this.endGame();
            }

            for (let i = this.particles.length - 1; i >= 0; i--) {
                let p = this.particles[i];
                p.x += p.vx;
                p.y += p.vy;
                p.life -= 0.02;
                if (p.life <= 0) this.particles.splice(i, 1);
            }
            if (this.state === STATE.GAME_OVER) {
                this.cameraY = 0;
                return;
            }

            if (this.state === STATE.IDLE) {
                if (KEYS.A) this.playerX -= 3;
                if (KEYS.D) this.playerX += 3;
                this.playerX = Math.max(20, Math.min(GAME_WIDTH - 20, this.playerX));
                this.hook.x = this.playerX;
                this.hook.y = 85;
                this.cameraY = 0;
            } else {
                const baseSpeed = 3;
                const boost = 4;

                if (KEYS.A) this.hook.x -= 3;
                if (KEYS.D) this.hook.x += 3;
                this.hook.x = Math.max(0, Math.min(GAME_WIDTH, this.hook.x));

                if (this.state === STATE.CASTING_DOWN) {
                    let speed = baseSpeed;
                    if (KEYS.S) speed += boost;
                    if (KEYS.W) speed -= (boost / 1.5);
                    if (speed < 1) speed = 1;

                    this.hook.y += speed;

                    if (this.hook.y > 300) {
                        this.cameraY = this.hook.y - 300;
                    }
                } else if (this.state === STATE.REELING_UP) {
                    let speed = baseSpeed * 2;
                    if (KEYS.W) speed += boost;
                    if (KEYS.S) speed -= (boost / 1.5);
                    if (speed < 1) speed = 1;

                    this.hook.y -= speed;

                    if (this.hook.y > 300) {
                        this.cameraY = this.hook.y - 300;
                    } else {
                        this.cameraY = 0;
                    }

                    if (this.hook.y <= WATER_LEVEL) {
                        this.resolveCatch();
                    }
                }
            }

            const swimmingFishCount = this.fishes.filter(f => !f.caught).length;
            if (swimmingFishCount < 15) {
                this.spawnFish();
            }
            
            if (this.spritePool.length) {
                this.fishes.forEach(fish => {
                    if (!fish.sprite) {
                        const sprite = this.pickCreatureSprite();
                        if (sprite) this.applySpriteToFish(fish, sprite);
                    }
                });
            }

            for (let i = this.fishes.length - 1; i >= 0; i--) {
                let fish = this.fishes[i];

                if (fish.caught) {
                    const offset = this.hook.caughtFish.indexOf(fish) * 10;
                    fish.x = this.hook.x;
                    fish.y = this.hook.y + 20 + offset;
                    continue;
                }

                fish.x += fish.speed;

                if ((fish.speed > 0 && fish.x > GAME_WIDTH + 200) ||
                    (fish.speed < 0 && fish.x < -200)) {
                    this.fishes.splice(i, 1);
                    continue;
                }

                if (this.state !== STATE.IDLE) {
                    if (this.hook.x > fish.x - fish.width/2 &&
                        this.hook.x < fish.x + fish.width/2 &&
                        this.hook.y > fish.y - fish.height/2 &&
                        this.hook.y < fish.y + fish.height/2) {

                        fish.caught = true;
                        this.hook.caughtFish.push(fish);

                        if (this.state === STATE.CASTING_DOWN) {
                            this.state = STATE.REELING_UP;
                        }
                    }
                }
            }
        }

        resolveCatch() {
            let totalPoints = 0;
            let extraBait = 0;
            let triggerConfetti = false;

            this.hook.caughtFish.forEach(fish => {
                if (fish.isGold) {
                    totalPoints += 5;
                    triggerConfetti = true;
                } else {
                    totalPoints += 1;
                }

                if (fish.hasBait) {
                    extraBait += 2;
                    triggerConfetti = true;
                }
            });

            if (triggerConfetti) {
                this.createConfetti(GAME_WIDTH / 2, WATER_LEVEL);
            }

            this.score += totalPoints;
            this.bait += extraBait;

            this.fishes = this.fishes.filter(f => !f.caught);
            this.hook.caughtFish = [];

            this.state = STATE.IDLE;

            if (this.bait <= 0) {
                this.endGame();
            }
        }

        draw() {
            this.ctx.clearRect(0, 0, GAME_WIDTH, GAME_HEIGHT);
            this.uiCtx.clearRect(0, 0, UI_WIDTH, 600);

            this.drawUI();
            this.drawGame();
        }

        drawUI() {
            const ctx = this.uiCtx;
            ctx.fillStyle = "#0ea5e9";
            ctx.fillRect(0, 0, UI_WIDTH, 60);

            ctx.fillStyle = "white";
            ctx.font = "bold 20px sans-serif";
            ctx.textAlign = "center";
            ctx.fillText("Fishing Status", UI_WIDTH/2, 38);

            ctx.fillStyle = "#334155";
            ctx.textAlign = "left";
            ctx.font = "16px sans-serif";

            const xPad = 20;
            let yPos = 100;

            this.drawStatBox(ctx, xPad, yPos, "Bait Left", this.bait);
            yPos += 80;

            this.drawStatBox(ctx, xPad, yPos, "Score", this.score);
            yPos += 80;

            this.drawStatBox(ctx, xPad, yPos, "Time", this.elapsedTime);

            yPos += 100;
            ctx.strokeStyle = "#94a3b8";
            ctx.lineWidth = 2;
            ctx.beginPath();
            ctx.moveTo(UI_WIDTH/2, yPos);
            ctx.lineTo(UI_WIDTH/2, yPos + 50);
            ctx.arc(UI_WIDTH/2 - 10, yPos + 50, 10, 0, Math.PI, false);
            ctx.stroke();
            
            if (this.gameOverFlag) {
                ctx.fillStyle = "#dc2626";
                ctx.font = "bold 18px sans-serif";
                ctx.textAlign = "center";
                ctx.fillText("Out of bait!", UI_WIDTH / 2, yPos + 90);
                ctx.fillStyle = "#475569";
                ctx.font = "14px sans-serif";
                ctx.fillText("Submit your score", UI_WIDTH / 2, yPos + 112);
            }
        }

        drawStatBox(ctx, x, y, label, value) {
            ctx.fillStyle = "#e0f2fe";
            ctx.fillRect(x, y, UI_WIDTH - (x*2), 60);
            ctx.strokeStyle = "#bae6fd";
            ctx.strokeRect(x, y, UI_WIDTH - (x*2), 60);

            ctx.fillStyle = "#64748b";
            ctx.font = "12px sans-serif";
            ctx.fillText(label.toUpperCase(), x + 10, y + 20);

            ctx.fillStyle = "#0f172a";
            ctx.font = "bold 24px sans-serif";
            ctx.fillText(value, x + 10, y + 50);
        }

        drawGame() {
            const ctx = this.ctx;

            ctx.save();
            ctx.translate(0, -this.cameraY);

            ctx.fillStyle = "#bae6fd";
            ctx.fillRect(0, this.cameraY, GAME_WIDTH, WATER_LEVEL - this.cameraY);

            let gradient = ctx.createLinearGradient(0, WATER_LEVEL, 0, this.cameraY + GAME_HEIGHT);
            gradient.addColorStop(0, "#7dd3fc");
            gradient.addColorStop(1, "#0c4a6e");
            ctx.fillStyle = gradient;
            ctx.fillRect(0, WATER_LEVEL, GAME_WIDTH, this.cameraY + GAME_HEIGHT + 500);

            this.fishes.forEach(fish => this.drawFish(ctx, fish));

            ctx.strokeStyle = "#000";
            ctx.lineWidth = 1.5;
            ctx.beginPath();
            ctx.moveTo(this.playerX + 20, 80);
            ctx.lineTo(this.hook.x, this.hook.y);
            ctx.stroke();

            ctx.strokeStyle = "#475569";
            ctx.lineWidth = 3;
            ctx.beginPath();
            ctx.moveTo(this.hook.x, this.hook.y);
            ctx.arc(this.hook.x - 5, this.hook.y + 5, 5, 0, Math.PI, false);
            ctx.stroke();

            ctx.fillStyle = "#78350f";
            ctx.fillRect(0, 90, GAME_WIDTH, 20);
            for(let i=0; i<GAME_WIDTH; i+=60) {
                ctx.fillRect(i+10, 110, 10, GAME_HEIGHT + this.cameraY);
            }

            this.drawPlayer(ctx);

            this.particles.forEach(p => {
                ctx.fillStyle = p.color;
                ctx.fillRect(p.x, p.y, 5, 5);
            });

            ctx.restore();
        }

        drawPlayer(ctx) {
            const x = this.playerX;
            const y = 50;

            ctx.fillStyle = "#fbbf24";
            ctx.beginPath();
            ctx.ellipse(x, y, 20, 30, 0, 0, Math.PI * 2);
            ctx.fill();
            ctx.strokeStyle = "#b45309";
            ctx.lineWidth = 2;
            ctx.stroke();

            ctx.beginPath();
            ctx.moveTo(x - 15, y - 20);
            ctx.lineTo(x - 25, y - 40);
            ctx.lineTo(x - 5, y - 25);
            ctx.fill();
            ctx.stroke();

            ctx.beginPath();
            ctx.moveTo(x + 15, y - 20);
            ctx.lineTo(x + 25, y - 40);
            ctx.lineTo(x + 5, y - 25);
            ctx.fill();
            ctx.stroke();

            ctx.strokeStyle = "#5c2f06";
            ctx.lineWidth = 3;
            ctx.beginPath();
            ctx.moveTo(x + 10, y + 10);
            ctx.lineTo(x + 20, 80);
            ctx.stroke();

            ctx.fillStyle = "black";
            ctx.beginPath();
            ctx.arc(x - 7, y - 5, 3, 0, Math.PI*2);
            ctx.arc(x + 7, y - 5, 3, 0, Math.PI*2);
            ctx.fill();
        }

        drawFish(ctx, fish) {
            ctx.save();
            ctx.translate(fish.x, fish.y);
            if (fish.speed < 0) {
                ctx.scale(-1, 1);
            }
            
            if (fish.sprite && fish.sprite.img) {
                const drawWidth = fish.sprite.width || fish.width;
                const drawHeight = fish.sprite.height || fish.height;
                ctx.drawImage(
                    fish.sprite.img,
                    -drawWidth / 2,
                    -drawHeight / 2,
                    drawWidth,
                    drawHeight
                );
            } else {
                if (fish.isGold) {
                    ctx.shadowColor = "gold";
                    ctx.shadowBlur = 15;
                } else {
                    ctx.shadowBlur = 0;
                }
            
                if (fish.type === 0) ctx.fillStyle = fish.isGold ? "#fcd34d" : "#f87171";
                if (fish.type === 1) ctx.fillStyle = fish.isGold ? "#fcd34d" : "#4ade80";
                if (fish.type === 2) ctx.fillStyle = fish.isGold ? "#fcd34d" : "#c084fc";
            
                ctx.beginPath();
                ctx.ellipse(0, 0, 20, 12, 0, 0, Math.PI * 2);
                ctx.fill();
            
                ctx.beginPath();
                ctx.moveTo(-15, 0);
                ctx.lineTo(-30, -10);
                ctx.lineTo(-30, 10);
                ctx.fill();
            
                ctx.beginPath();
                ctx.moveTo(0, -10);
                ctx.lineTo(5, -18);
                ctx.lineTo(10, -10);
                ctx.fill();
                ctx.fillStyle = "white";
                ctx.beginPath();
                ctx.arc(10, -3, 4, 0, Math.PI*2);
                ctx.fill();
                ctx.fillStyle = "black";
                ctx.beginPath();
                ctx.arc(11, -3, 2, 0, Math.PI*2);
                ctx.fill();
            }

            if (fish.hasBait) {
                ctx.fillStyle = "white";
                ctx.font = "bold 10px sans-serif";
                ctx.shadowBlur = 0;
                ctx.scale(fish.speed < 0 ? -1 : 1, 1);
                ctx.fillText("+2", -5, -15);
            }

            ctx.restore();
        }
        async submitScore() {
            if (!this.exchangeButton) return;

            this.exchangeButton.disabled = true;
            if (this.exchangeStatus) {
                this.exchangeStatus.textContent = 'Submitting score...';
            }

            try {
                const response = await fetch('score_exchange.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ game: 'fishing', score: Math.max(0, Math.round(this.score)) })
                });
                const data = await response.json();

                if (!response.ok || data.error) {
                    const message = data?.error || 'Unable to submit score right now.';
                    if (this.exchangeStatus) this.exchangeStatus.textContent = message;
                    this.exchangeButton.disabled = false;
                    return;
                }

                if (window.updateCurrencyDisplay && data.cash !== undefined) {
                    window.updateCurrencyDisplay({ cash: data.cash });
                }

                if (this.exchangeStatus) {
                    this.exchangeStatus.textContent = 'Score converted! Redirecting to score exchange...';
                }

                setTimeout(() => {
                    window.location.href = '?pg=games';
                }, 750);
            } catch (err) {
                if (this.exchangeStatus) this.exchangeStatus.textContent = 'Unable to submit score right now.';
                this.exchangeButton.disabled = false;
            }
        }

        gameLoop() {
            this.update();
            this.draw();
            requestAnimationFrame(() => this.gameLoop());
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        new FishingGame();
    });
})();