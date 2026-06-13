(function () {
    const config = window.kidPuzzleConfig || {};
    const canvas = document.getElementById('kid-puzzle-board');
    const boardWrap = canvas ? canvas.closest('.kid-puzzle-board-wrap') : null;
    const message = document.getElementById('kid-puzzle-message');
    const movesEl = document.getElementById('kid-puzzle-moves');
    const countEl = document.getElementById('kid-puzzle-piece-count');
    const bestEl = document.getElementById('kid-puzzle-best');
    const shuffleButton = document.getElementById('kid-puzzle-shuffle');
    const previewButton = document.getElementById('kid-puzzle-preview-toggle');
    const previewPanel = document.getElementById('kid-puzzle-preview');
    const previewImage = document.getElementById('kid-puzzle-preview-image');
    const pieceButtons = Array.from(document.querySelectorAll('[data-kid-puzzle-pieces]'));
    const musicToggle = document.getElementById('kid-puzzle-music-toggle');
    const volumeInput = document.getElementById('kid-puzzle-volume');
    const volumeValue = document.getElementById('kid-puzzle-volume-value');

    if (!canvas || !boardWrap || !movesEl || !countEl || !bestEl) {
        return;
    }

    const ctx = canvas.getContext('2d');
    const PIECE_OPTIONS = Array.isArray(config.pieces) && config.pieces.length
        ? config.pieces.map((value) => parseInt(value, 10)).filter(Boolean)
        : [6, 12, 36, 80, 200, 1600];
    const GRID_BY_PIECES = {
        6: { cols: 3, rows: 2 },
        12: { cols: 4, rows: 3 },
        36: { cols: 6, rows: 6 },
        80: { cols: 10, rows: 8 },
        200: { cols: 20, rows: 10 },
        1600: { cols: 40, rows: 40 }
    };
    const DEFAULT_PIECES = PIECE_OPTIONS.includes(parseInt(config.defaultPieces, 10))
        ? parseInt(config.defaultPieces, 10)
        : PIECE_OPTIONS[0];
    const VOLUME_KEY = 'kid-puzzle-music-volume';
    const DEFAULT_VOLUME = 0.45;
    const APP_BASE_PATH = getAppBasePath();
    const imageSources = assetCandidates(config.image || '/images/games/kid.webp');
    let imageSourceIndex = 0;
    const musicTracks = Array.isArray(config.music) ? config.music.filter(Boolean) : [
        '/assets/music/kid1.wav',
        '/assets/music/kid2.wav',
        '/assets/music/kid3.wav',
        '/assets/music/kid4.wav'
    ];
    const musicSources = shuffleArray(musicTracks).flatMap(assetCandidates);

    let imageLoaded = false;
    let currentPieces = DEFAULT_PIECES;
    let grid = GRID_BY_PIECES[currentPieces] || deriveGrid(currentPieces);
    let tiles = [];
    let selectedIndex = null;
    let moves = 0;
    let solved = false;
    let animationFrame = 0;
    let music = null;
    let musicSourceIndex = 0;
    let musicEnabled = true;
    let musicVolume = getStoredVolume();

    const image = new Image();
    image.onload = () => {
        imageLoaded = true;
        boardWrap.style.setProperty('--kid-puzzle-aspect', `${image.naturalWidth} / ${image.naturalHeight}`);
        if (previewImage) {
            previewImage.src = image.src;
        }
        startPuzzle(currentPieces);
    };
    image.onerror = () => {
        if (imageSourceIndex < imageSources.length - 1) {
            imageSourceIndex += 1;
            image.src = imageSources[imageSourceIndex];
            return;
        }
        imageLoaded = false;
        setMessage('Image unavailable');
        drawPlaceholder();
    };
    image.src = imageSources[imageSourceIndex];

    function clamp(value, min, max) {
        return Math.max(min, Math.min(max, value));
    }

    function shuffleArray(items) {
        const shuffled = items.slice();
        for (let index = shuffled.length - 1; index > 0; index -= 1) {
            const swapIndex = Math.floor(Math.random() * (index + 1));
            [shuffled[index], shuffled[swapIndex]] = [shuffled[swapIndex], shuffled[index]];
        }
        return shuffled;
    }

    function deriveGrid(pieceCount) {
        const root = Math.sqrt(pieceCount);
        let rows = Math.floor(root);
        while (rows > 1 && pieceCount % rows !== 0) {
            rows -= 1;
        }
        return { cols: pieceCount / rows, rows };
    }

    function getAppBasePath() {
        const script = document.currentScript || document.querySelector('script[src*="kid-puzzle.js"]');
        if (!script || !script.src) {
            return '';
        }
        try {
            const scriptPath = new URL(script.src, window.location.href).pathname;
            const marker = '/assets/js/kid-puzzle.js';
            const markerIndex = scriptPath.indexOf(marker);
            return markerIndex > 0 ? scriptPath.slice(0, markerIndex) : '';
        } catch {
            return '';
        }
    }

    function assetCandidates(source) {
        const raw = String(source || '').trim();
        if (!raw) {
            return [];
        }
        if (/^(?:https?:|data:|blob:)/i.test(raw)) {
            return [raw];
        }
        const candidates = [];
        if (raw.charAt(0) === '/') {
            if (APP_BASE_PATH) {
                candidates.push(APP_BASE_PATH + raw);
            }
            candidates.push(raw);
            candidates.push(raw.slice(1));
        } else {
            candidates.push(raw);
            if (APP_BASE_PATH) {
                candidates.push(APP_BASE_PATH + '/' + raw.replace(/^\/+/, ''));
            }
        }
        return candidates.filter((candidate, index, list) => candidate && list.indexOf(candidate) === index);
    }

    function bestKey(pieceCount) {
        return `kid-puzzle-best-${pieceCount}`;
    }

    function getBest(pieceCount) {
        try {
            const stored = window.localStorage.getItem(bestKey(pieceCount));
            const parsed = parseInt(stored || '', 10);
            return Number.isFinite(parsed) && parsed > 0 ? parsed : null;
        } catch {
            return null;
        }
    }

    function setBest(pieceCount, value) {
        try {
            window.localStorage.setItem(bestKey(pieceCount), String(value));
        } catch {
            return;
        }
    }

    function getStoredVolume() {
        try {
            const stored = window.localStorage.getItem(VOLUME_KEY);
            if (stored === null) {
                return DEFAULT_VOLUME;
            }
            const parsed = parseFloat(stored);
            return Number.isFinite(parsed) ? clamp(parsed, 0, 1) : DEFAULT_VOLUME;
        } catch {
            return DEFAULT_VOLUME;
        }
    }

    function storeVolume(value) {
        try {
            window.localStorage.setItem(VOLUME_KEY, String(value));
        } catch {
            return;
        }
    }

    function setMessage(text) {
        if (!message) {
            return;
        }
        message.textContent = text;
        message.hidden = !text;
    }

    function updateStats() {
        const best = getBest(currentPieces);
        movesEl.textContent = String(moves);
        countEl.textContent = String(currentPieces);
        bestEl.textContent = best === null ? '-' : String(best);
    }

    function updatePieceButtons() {
        pieceButtons.forEach((button) => {
            const pieces = parseInt(button.dataset.kidPuzzlePieces || '', 10);
            button.setAttribute('aria-pressed', pieces === currentPieces ? 'true' : 'false');
        });
    }

    function createTiles(pieceCount) {
        return Array.from({ length: pieceCount }, (_, index) => index);
    }

    function shuffleTiles() {
        tiles = shuffleArray(createTiles(currentPieces));
        if (currentPieces > 1 && isSolved()) {
            [tiles[0], tiles[1]] = [tiles[1], tiles[0]];
        }
        selectedIndex = null;
        moves = 0;
        solved = false;
        updateStats();
        setMessage('');
        requestDraw();
    }

    function startPuzzle(pieceCount) {
        currentPieces = PIECE_OPTIONS.includes(pieceCount) ? pieceCount : DEFAULT_PIECES;
        grid = GRID_BY_PIECES[currentPieces] || deriveGrid(currentPieces);
        shuffleTiles();
        updatePieceButtons();
    }

    function resizeCanvas() {
        const rect = canvas.getBoundingClientRect();
        const width = Math.max(1, Math.round(rect.width));
        const height = Math.max(1, Math.round(rect.height));
        const dpr = clamp(window.devicePixelRatio || 1, 1, 2);
        const targetWidth = Math.round(width * dpr);
        const targetHeight = Math.round(height * dpr);
        if (canvas.width !== targetWidth || canvas.height !== targetHeight) {
            canvas.width = targetWidth;
            canvas.height = targetHeight;
        }
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        return { width, height };
    }

    function requestDraw() {
        if (animationFrame) {
            return;
        }
        animationFrame = window.requestAnimationFrame(() => {
            animationFrame = 0;
            draw();
        });
    }

    function drawPlaceholder() {
        const { width, height } = resizeCanvas();
        ctx.clearRect(0, 0, width, height);
        ctx.fillStyle = '#d8efe6';
        ctx.fillRect(0, 0, width, height);
        ctx.fillStyle = '#1c3b44';
        ctx.font = '700 18px sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText('Image unavailable', width / 2, height / 2);
    }

    function draw() {
        if (!imageLoaded || !tiles.length) {
            drawPlaceholder();
            return;
        }

        const { width, height } = resizeCanvas();
        const tileWidth = width / grid.cols;
        const tileHeight = height / grid.rows;
        const sourceWidth = image.naturalWidth / grid.cols;
        const sourceHeight = image.naturalHeight / grid.rows;
        ctx.clearRect(0, 0, width, height);

        for (let cellIndex = 0; cellIndex < tiles.length; cellIndex += 1) {
            const sourceIndex = tiles[cellIndex];
            const cellCol = cellIndex % grid.cols;
            const cellRow = Math.floor(cellIndex / grid.cols);
            const sourceCol = sourceIndex % grid.cols;
            const sourceRow = Math.floor(sourceIndex / grid.cols);
            ctx.drawImage(
                image,
                sourceCol * sourceWidth,
                sourceRow * sourceHeight,
                sourceWidth,
                sourceHeight,
                cellCol * tileWidth,
                cellRow * tileHeight,
                tileWidth + 0.5,
                tileHeight + 0.5
            );
        }

        drawGrid(width, height, tileWidth, tileHeight);
        if (selectedIndex !== null) {
            drawSelection(tileWidth, tileHeight);
        }
    }

    function drawGrid(width, height, tileWidth, tileHeight) {
        const alpha = currentPieces >= 1600 ? 0.18 : currentPieces >= 200 ? 0.28 : 0.42;
        ctx.save();
        ctx.strokeStyle = `rgba(9, 24, 31, ${alpha})`;
        ctx.lineWidth = currentPieces >= 200 ? 0.75 : 1.2;
        ctx.beginPath();
        for (let col = 1; col < grid.cols; col += 1) {
            const x = col * tileWidth;
            ctx.moveTo(x, 0);
            ctx.lineTo(x, height);
        }
        for (let row = 1; row < grid.rows; row += 1) {
            const y = row * tileHeight;
            ctx.moveTo(0, y);
            ctx.lineTo(width, y);
        }
        ctx.stroke();
        ctx.restore();
    }

    function drawSelection(tileWidth, tileHeight) {
        const col = selectedIndex % grid.cols;
        const row = Math.floor(selectedIndex / grid.cols);
        const inset = currentPieces >= 200 ? 1 : 3;
        ctx.save();
        ctx.strokeStyle = '#fff4a5';
        ctx.lineWidth = currentPieces >= 200 ? 2 : 4;
        ctx.strokeRect(
            col * tileWidth + inset,
            row * tileHeight + inset,
            Math.max(1, tileWidth - inset * 2),
            Math.max(1, tileHeight - inset * 2)
        );
        ctx.strokeStyle = '#153943';
        ctx.lineWidth = currentPieces >= 200 ? 1 : 2;
        ctx.strokeRect(
            col * tileWidth + inset + 2,
            row * tileHeight + inset + 2,
            Math.max(1, tileWidth - inset * 2 - 4),
            Math.max(1, tileHeight - inset * 2 - 4)
        );
        ctx.restore();
    }

    function isSolved() {
        return tiles.every((tile, index) => tile === index);
    }

    function cellFromEvent(event) {
        const rect = canvas.getBoundingClientRect();
        const x = event.clientX - rect.left;
        const y = event.clientY - rect.top;
        const col = clamp(Math.floor((x / Math.max(rect.width, 1)) * grid.cols), 0, grid.cols - 1);
        const row = clamp(Math.floor((y / Math.max(rect.height, 1)) * grid.rows), 0, grid.rows - 1);
        return row * grid.cols + col;
    }

    function handleCell(cellIndex) {
        if (!imageLoaded || solved || cellIndex < 0 || cellIndex >= tiles.length) {
            return;
        }
        if (selectedIndex === null) {
            selectedIndex = cellIndex;
            requestDraw();
            return;
        }
        if (selectedIndex === cellIndex) {
            selectedIndex = null;
            requestDraw();
            return;
        }

        [tiles[selectedIndex], tiles[cellIndex]] = [tiles[cellIndex], tiles[selectedIndex]];
        selectedIndex = null;
        moves += 1;
        if (isSolved()) {
            solved = true;
            const best = getBest(currentPieces);
            if (best === null || moves < best) {
                setBest(currentPieces, moves);
            }
            setMessage('Puzzle complete');
        }
        updateStats();
        requestDraw();
    }

    function setVolume(value, persist) {
        musicVolume = Number.isFinite(value) ? clamp(value, 0, 1) : DEFAULT_VOLUME;
        if (music) {
            music.volume = musicVolume;
        }
        if (volumeInput) {
            volumeInput.value = String(Math.round(musicVolume * 100));
        }
        if (volumeValue) {
            volumeValue.textContent = Math.round(musicVolume * 100) + '%';
        }
        if (persist) {
            storeVolume(musicVolume);
        }
    }

    function updateMusicToggle() {
        if (!musicToggle) {
            return;
        }
        musicToggle.textContent = musicEnabled ? 'Music On' : 'Music Off';
        musicToggle.setAttribute('aria-pressed', musicEnabled ? 'true' : 'false');
    }

    function createMusic() {
        if (!musicSources.length || music) {
            return;
        }
        music = new Audio(musicSources[musicSourceIndex]);
        music.loop = true;
        music.preload = 'auto';
        music.volume = musicVolume;
        music.addEventListener('error', () => {
            if (musicSourceIndex >= musicSources.length - 1) {
                return;
            }
            musicSourceIndex += 1;
            music.src = musicSources[musicSourceIndex];
            music.volume = musicVolume;
            if (musicEnabled) {
                music.play().catch(() => {});
            }
        });
    }

    function playMusic() {
        if (!musicEnabled || !musicSources.length) {
            return;
        }
        createMusic();
        if (!music) {
            return;
        }
        music.volume = musicVolume;
        music.play().catch(() => {});
    }

    function pauseMusic() {
        if (music) {
            music.pause();
        }
    }

    function unlockMusic() {
        playMusic();
    }

    canvas.addEventListener('pointerdown', (event) => {
        event.preventDefault();
        unlockMusic();
        handleCell(cellFromEvent(event));
        canvas.focus({ preventScroll: true });
    });

    pieceButtons.forEach((button) => {
        button.addEventListener('click', () => {
            unlockMusic();
            const pieces = parseInt(button.dataset.kidPuzzlePieces || '', 10);
            if (PIECE_OPTIONS.includes(pieces)) {
                startPuzzle(pieces);
            }
        });
    });

    if (shuffleButton) {
        shuffleButton.addEventListener('click', () => {
            unlockMusic();
            shuffleTiles();
        });
    }

    if (previewButton && previewPanel) {
        previewButton.addEventListener('click', () => {
            unlockMusic();
            const shouldShow = previewPanel.hidden;
            previewPanel.hidden = !shouldShow;
            previewButton.setAttribute('aria-pressed', shouldShow ? 'true' : 'false');
        });
    }

    if (musicToggle) {
        musicToggle.addEventListener('click', () => {
            musicEnabled = !musicEnabled;
            updateMusicToggle();
            if (musicEnabled) {
                playMusic();
            } else {
                pauseMusic();
            }
        });
    }

    if (volumeInput) {
        volumeInput.addEventListener('input', () => {
            setVolume((parseInt(volumeInput.value, 10) || 0) / 100, true);
            if (musicEnabled) {
                playMusic();
            }
        });
    }

    window.addEventListener('resize', requestDraw);
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && selectedIndex !== null) {
            selectedIndex = null;
            requestDraw();
        }
    });

    setVolume(musicVolume, false);
    updateMusicToggle();
    updatePieceButtons();
    updateStats();
    setMessage('Loading...');
    requestDraw();
})();
