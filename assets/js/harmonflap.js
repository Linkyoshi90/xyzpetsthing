(function () {
    const config = window.harmonflapConfig || {};
    const stage = document.getElementById('harmonflap-stage');
    const playerEl = document.getElementById('harmonflap-player');
    const creatureImg = document.getElementById('harmonflap-creature');
    const scoreEl = document.getElementById('harmonflap-score');
    const bestEl = document.getElementById('harmonflap-best');
    const overlay = document.getElementById('harmonflap-overlay');
    const overlayTitle = document.getElementById('harmonflap-overlay-title');
    const overlayCopy = document.getElementById('harmonflap-overlay-copy');
    const startButton = document.getElementById('harmonflap-start');
    const submitButton = document.getElementById('harmonflap-submit');
    const submitStatus = document.getElementById('harmonflap-submit-status');
    const pilot = document.getElementById('harmonflap-pilot');
    const volumeInput = document.getElementById('harmonflap-sfx-volume');
    const volumeValue = document.getElementById('harmonflap-sfx-volume-value');
    const sfxConfig = config.sfx || {};

    if (!stage || !playerEl || !creatureImg || !scoreEl || !bestEl || !overlay || !startButton) {
        return;
    }

    const wingEmoji = '\u{1FABD}';
    playerEl.querySelectorAll('.harmonflap-wing').forEach((wing) => {
        wing.textContent = wingEmoji;
    });

    if (config.backgroundImage) {
        const backgroundImage = String(config.backgroundImage).replace(/["\\\n\r]/g, '');
        stage.style.setProperty('--harmonflap-bg', `url("${backgroundImage}")`);
    }

    if (config.playerImage) {
        creatureImg.src = config.playerImage;
    }
    if (config.playerName) {
        creatureImg.alt = config.playerName;
        if (pilot) {
            pilot.textContent = config.playerName;
        }
    }
    creatureImg.onerror = () => {
        creatureImg.onerror = null;
        creatureImg.src = config.fallbackImage || 'images/creatures/tengu_f_blue.webp';
    };

    const BEST_KEY = 'harmonflap-best-score';
    const SFX_VOLUME_KEY = 'harmonflap-sfx-volume';
    const DEFAULT_SFX_VOLUME = 0.72;
    const clamp = (value, min, max) => Math.max(min, Math.min(max, value));
    const defaultSfx = {
        flap: ['assets/sfx/Flap.wav', 'assets/sfx/playerfire.mp3'],
        death: ['assets/sfx/death.wav', 'assets/sfx/enemyfire.wav'],
        pass: ['assets/sfx/pass.wav', 'assets/sfx/fruitlineclear.wav']
    };
    let sfxVolume = getStoredSfxVolume();
    const sounds = {
        flap: createSound(sfxConfig.flap, defaultSfx.flap),
        death: createSound(sfxConfig.death, defaultSfx.death),
        pass: createSound(sfxConfig.pass, defaultSfx.pass)
    };

    let mode = 'idle';
    let width = 0;
    let height = 0;
    let groundHeight = 0;
    let playerSize = 68;
    let playerX = 0;
    let playerY = 0;
    let velocity = 0;
    let score = 0;
    let finalScore = 0;
    let pipes = [];
    let spawnTimer = 0;
    let lastFrame = 0;
    let flapTimer = 0;
    let submitted = false;

    function normalizeSoundSources(sourceConfig, fallbacks) {
        const configured = Array.isArray(sourceConfig) ? sourceConfig : [sourceConfig];
        const sources = configured
            .concat(fallbacks || [])
            .filter((src, index, list) => src && list.indexOf(src) === index);
        return sources;
    }

    function createSound(sourceConfig, fallbacks) {
        const sources = normalizeSoundSources(sourceConfig, fallbacks);
        if (!sources.length) {
            return null;
        }
        const audio = new Audio(sources[0]);
        audio.preload = 'auto';
        audio.volume = sfxVolume;
        return { audio, sourceIndex: 0, sources };
    }

    function getStoredSfxVolume() {
        try {
            const stored = window.localStorage.getItem(SFX_VOLUME_KEY);
            if (stored === null) {
                return DEFAULT_SFX_VOLUME;
            }
            const storedVolume = parseFloat(stored);
            return Number.isFinite(storedVolume) ? clamp(storedVolume, 0, 1) : DEFAULT_SFX_VOLUME;
        } catch {
            return DEFAULT_SFX_VOLUME;
        }
    }

    function storeSfxVolume(value) {
        try {
            window.localStorage.setItem(SFX_VOLUME_KEY, String(value));
        } catch {
            return;
        }
    }

    function setSfxVolume(value, persist) {
        sfxVolume = Number.isFinite(value) ? clamp(value, 0, 1) : DEFAULT_SFX_VOLUME;
        Object.values(sounds).forEach((sound) => {
            if (sound && sound.audio) {
                sound.audio.volume = sfxVolume;
            }
        });
        if (volumeInput) {
            volumeInput.value = String(Math.round(sfxVolume * 100));
        }
        if (volumeValue) {
            volumeValue.textContent = Math.round(sfxVolume * 100) + '%';
        }
        if (persist) {
            storeSfxVolume(sfxVolume);
        }
    }

    function playSound(name) {
        const sound = sounds[name];
        if (!sound || !sound.audio) {
            return;
        }
        const { audio, sources } = sound;
        try {
            audio.currentTime = 0;
            const playAttempt = audio.play();
            if (playAttempt && typeof playAttempt.catch === 'function') {
                playAttempt.catch(() => {
                    if (sound.sourceIndex >= sources.length - 1) {
                        return;
                    }
                    sound.sourceIndex += 1;
                    audio.src = sources[sound.sourceIndex];
                    audio.volume = sfxVolume;
                    audio.currentTime = 0;
                    audio.play().catch(() => {});
                });
            }
        } catch {
            return;
        }
    }

    function getStoredBest() {
        try {
            return parseInt(window.localStorage.getItem(BEST_KEY) || '0', 10) || 0;
        } catch {
            return 0;
        }
    }

    function setStoredBest(value) {
        try {
            window.localStorage.setItem(BEST_KEY, String(value));
        } catch {
            return;
        }
    }

    let bestScore = getStoredBest();
    bestEl.textContent = String(bestScore);

    function measure() {
        const rect = stage.getBoundingClientRect();
        width = rect.width || 960;
        height = rect.height || 540;
        groundHeight = height * 0.1;
        playerSize = clamp(height * 0.14, 48, 78);
        playerEl.style.setProperty('--player-size', playerSize + 'px');
        if (mode !== 'running') {
            playerX = Math.max(34, width * 0.22);
            playerY = height * 0.42;
            velocity = 0;
        }
    }

    function updateScoreDisplay() {
        scoreEl.textContent = String(score);
        bestEl.textContent = String(bestScore);
    }

    function setWingFlap(value) {
        playerEl.dataset.wingFlap = value ? '1' : '0';
        if (flapTimer) {
            window.clearTimeout(flapTimer);
            flapTimer = 0;
        }
        if (value) {
            flapTimer = window.setTimeout(() => setWingFlap(0), 120);
        }
    }

    function playerTransform() {
        const rotation = clamp((velocity / Math.max(height, 1)) * 95, -24, 68);
        playerEl.style.transform = `translate3d(${playerX}px, ${playerY}px, 0) rotate(${rotation}deg)`;
    }

    function clearPipes() {
        pipes.forEach((pipe) => pipe.el.remove());
        pipes = [];
    }

    function setOverlay(title, copy, buttonLabel, allowSubmit) {
        overlayTitle.textContent = title;
        overlayCopy.textContent = copy;
        startButton.textContent = buttonLabel;
        if (submitButton) {
            submitButton.hidden = !allowSubmit;
            submitButton.disabled = false;
        }
        if (submitStatus) {
            submitStatus.textContent = '';
        }
        overlay.hidden = false;
    }

    function hideOverlay() {
        overlay.hidden = true;
        if (submitStatus) {
            submitStatus.textContent = '';
        }
    }

    function pipeSpeed() {
        return clamp(210 + score * 3, width * 0.3, width * 0.48);
    }

    function spawnPipe() {
        const pipeWidth = clamp(width * 0.058, 42, 62);
        const gapSize = clamp(height * (0.31 - Math.min(score, 22) * 0.002), 128, 178);
        const topMargin = Math.max(34, height * 0.08);
        const bottomMargin = groundHeight + Math.max(44, height * 0.08);
        const gapTopMax = Math.max(topMargin, height - bottomMargin - gapSize);
        const gapTop = topMargin + Math.random() * Math.max(1, gapTopMax - topMargin);

        const pair = document.createElement('div');
        pair.className = 'harmonflap-tree-pair';
        pair.style.setProperty('--tree-width', pipeWidth + 'px');
        pair.style.setProperty('--gap-top', gapTop + 'px');
        pair.style.setProperty('--gap-size', gapSize + 'px');
        pair.innerHTML = '<div class="harmonflap-tree harmonflap-tree--top"></div><div class="harmonflap-tree harmonflap-tree--bottom"></div>';
        stage.insertBefore(pair, overlay);

        const pipe = {
            el: pair,
            x: width + pipeWidth,
            width: pipeWidth,
            gapTop,
            gapSize,
            scored: false
        };
        pipes.push(pipe);
        renderPipe(pipe);
    }

    function renderPipe(pipe) {
        pipe.el.style.transform = `translate3d(${pipe.x}px, 0, 0)`;
    }

    function collisionBox() {
        return {
            x: playerX + playerSize * 0.2,
            y: playerY + playerSize * 0.16,
            w: playerSize * 0.6,
            h: playerSize * 0.68
        };
    }

    function collidesWithPipe(pipe, bird) {
        const overlapsX = bird.x < pipe.x + pipe.width && bird.x + bird.w > pipe.x;
        if (!overlapsX) {
            return false;
        }
        const capInset = Math.max(8, height * 0.018);
        return bird.y < pipe.gapTop + capInset || bird.y + bird.h > pipe.gapTop + pipe.gapSize - capInset;
    }

    function updatePipes(delta) {
        const speed = pipeSpeed();
        for (let index = pipes.length - 1; index >= 0; index -= 1) {
            const pipe = pipes[index];
            pipe.x -= speed * delta;
            renderPipe(pipe);

            if (!pipe.scored && pipe.x + pipe.width < playerX + playerSize * 0.35) {
                pipe.scored = true;
                score += 1;
                updateScoreDisplay();
                playSound('pass');
            }

            if (pipe.x + pipe.width < -24) {
                pipe.el.remove();
                pipes.splice(index, 1);
            }
        }
    }

    function endGame() {
        if (mode !== 'running') {
            return;
        }
        mode = 'gameover';
        finalScore = score;
        submitted = false;
        playSound('death');
        setWingFlap(0);
        if (score > bestScore) {
            bestScore = score;
            setStoredBest(bestScore);
            updateScoreDisplay();
        }
        setOverlay('Game Over', 'Score ' + score, 'Try Again', score > 0);
    }

    function update(delta) {
        const gravity = height * 2.35;
        velocity += gravity * delta;
        playerY += velocity * delta;

        spawnTimer -= delta;
        if (spawnTimer <= 0) {
            spawnPipe();
            spawnTimer = clamp(1.35 - Math.min(score, 24) * 0.008, 1.08, 1.35);
        }

        updatePipes(delta);

        const bird = collisionBox();
        if (bird.y <= 0 || bird.y + bird.h >= height - groundHeight) {
            endGame();
            return;
        }

        for (const pipe of pipes) {
            if (collidesWithPipe(pipe, bird)) {
                endGame();
                return;
            }
        }
    }

    function startGame() {
        measure();
        clearPipes();
        mode = 'running';
        score = 0;
        finalScore = 0;
        submitted = false;
        spawnTimer = 0.82;
        playerX = Math.max(34, width * 0.22);
        playerY = height * 0.42;
        velocity = -height * 0.74;
        updateScoreDisplay();
        hideOverlay();
        setWingFlap(1);
        playSound('flap');
        stage.focus({ preventScroll: true });
    }

    function flap() {
        if (mode !== 'running') {
            startGame();
            return;
        }
        velocity = -height * 0.78;
        playerY = Math.max(0, playerY - 3);
        setWingFlap(1);
        playSound('flap');
    }

    function submitScore() {
        if (!submitButton || submitted || finalScore <= 0) {
            return;
        }
        submitButton.disabled = true;
        if (submitStatus) {
            submitStatus.textContent = 'Submitting...';
        }
        fetch('score_exchange.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ game: 'harmonflap', score: finalScore })
        })
            .then((response) => response.json().then((data) => ({ ok: response.ok, data })))
            .then(({ ok, data }) => {
                if (!ok || (data && data.error)) {
                    throw new Error((data && data.error) || 'Unable to submit score.');
                }
                submitted = true;
                if (typeof data.cash !== 'undefined' && window.updateCurrencyDisplay) {
                    window.updateCurrencyDisplay({ cash: data.cash });
                }
                if (submitStatus) {
                    submitStatus.textContent = 'Score exchanged.';
                }
                submitButton.hidden = true;
            })
            .catch((error) => {
                submitButton.disabled = false;
                if (submitStatus) {
                    submitStatus.textContent = error.message || 'Unable to submit score.';
                }
            });
    }

    function loop(timestamp) {
        if (!lastFrame) {
            lastFrame = timestamp;
        }
        const delta = Math.min((timestamp - lastFrame) / 1000, 0.034);
        lastFrame = timestamp;

        if (mode === 'running') {
            update(delta);
        }
        playerTransform();
        window.requestAnimationFrame(loop);
    }

    document.addEventListener('keydown', (event) => {
        if (event.code !== 'Space') {
            return;
        }
        const target = event.target;
        if (target && ['INPUT', 'TEXTAREA', 'SELECT'].includes(target.tagName)) {
            return;
        }
        event.preventDefault();
        flap();
    });

    stage.addEventListener('pointerdown', (event) => {
        if (event.target && event.target.closest('button')) {
            return;
        }
        flap();
    });

    startButton.addEventListener('click', startGame);
    if (submitButton) {
        submitButton.addEventListener('click', submitScore);
    }
    if (volumeInput) {
        volumeInput.addEventListener('input', () => {
            setSfxVolume((parseInt(volumeInput.value, 10) || 0) / 100, true);
        });
    }

    window.addEventListener('resize', () => {
        measure();
        playerTransform();
    });

    setSfxVolume(sfxVolume, false);
    measure();
    updateScoreDisplay();
    setOverlay('Harmonflap', 'Guide your creature through the tide pipes.', 'Start Flight', false);
    playerTransform();
    window.requestAnimationFrame(loop);
})();
