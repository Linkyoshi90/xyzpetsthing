(function () {
    const data = window.wantedAliveData || {};
    const variants = data.variants || {};

    const board = document.getElementById('hunt-board');
    const startButton = document.getElementById('hunt-start');
    const roundInfo = document.getElementById('hunt-round');
    const statusText = document.getElementById('hunt-status');
    const wantedImage = document.getElementById('wanted-image');
    const wantedName = document.getElementById('wanted-name');
    const timerLabel = document.getElementById('hunt-timer-label');
    const timerProgress = document.getElementById('hunt-timer-progress');
    const overlay = document.getElementById('hunt-overlay');
    const overlayText = overlay ? overlay.querySelector('span') : null;
    const poster = document.querySelector('.wanted-poster');

    if (!board || !startButton || !roundInfo || !statusText || !wantedImage) {
        return;
    }

    const creatureNames = Object.keys(variants).filter(function (name) {
        return Array.isArray(variants[name]) && variants[name].length > 0;
    });

    if (creatureNames.length === 0) {
        statusText.textContent = 'No creature portraits available.';
        startButton.disabled = true;
        if (timerLabel) {
            timerLabel.textContent = '--:--';
        }
        return;
    }

    const selectedVariants = {};
    creatureNames.forEach(function (name) {
        const choices = variants[name];
        selectedVariants[name] = choices[Math.floor(Math.random() * choices.length)];
    });

    const targetName = creatureNames[Math.floor(Math.random() * creatureNames.length)];
    const targetImage = selectedVariants[targetName];

    wantedImage.src = targetImage;
    wantedImage.alt = targetName + ' portrait';
    if (wantedName) {
        wantedName.textContent = targetName;
    }
    if (timerLabel) {
        timerLabel.textContent = '--:--';
    }
    if (timerProgress) {
        timerProgress.style.width = '100%';
    }

    const rounds = [
        { total: 4, duration: 20, speed: 0, diagonal: false },
        { total: 8, duration: 40, speed: 32, diagonal: false },
        { total: 12, duration: 60, speed: 60, diagonal: true }
    ];

    const totalRounds = rounds.length;
    let currentRoundIndex = -1;
    let roundDuration = 0;
    let timerInterval = null;
    let timeRemaining = 0;
    let roundActive = false;
    let animationFrameId = null;
    let lastFrameTime = null;
    let movers = [];
    let state = 'ready'; // ready, playing, next, restart

    startButton.textContent = 'Start Hunt';
    roundInfo.textContent = 'Round 0 / ' + totalRounds;
    statusText.textContent = 'Press start to begin the chase.';

    startButton.addEventListener('click', function () {
        if (state === 'ready' || state === 'restart') {
            startGame();
        } else if (state === 'next') {
            startNextRound();
        }
    });

    function startGame() {
        cleanupRound();
        state = 'playing';
        currentRoundIndex = -1;
        roundInfo.textContent = 'Round 0 / ' + totalRounds;
        statusText.textContent = 'Spot ' + targetName + ' before time runs out!';
        startButton.disabled = true;
        startButton.textContent = 'Hunting...';
        showOverlay('Round 1', 900);
        setTimeout(function () {
            startRound(0);
        }, 900);
    }

    function startNextRound() {
        state = 'playing';
        startButton.disabled = true;
        startButton.textContent = 'Hunting...';
        showOverlay('Round ' + (currentRoundIndex + 2), 900);
        setTimeout(function () {
            startRound(currentRoundIndex + 1);
        }, 900);
    }

    function startRound(index) {
        cleanupRound();
        currentRoundIndex = index;
        const round = rounds[index];
        roundDuration = round.duration;
        roundInfo.textContent = 'Round ' + (index + 1) + ' / ' + totalRounds;
        timeRemaining = round.duration;
        updateTimerDisplay();

        const participants = buildParticipants(round.total);
        const tokens = placeParticipants(participants);
        configureMovement(tokens, round);

        roundActive = true;
        state = 'playing';
        statusText.textContent = 'Find ' + targetName + ' before ' + formatTime(round.duration) + ' expires!';

        if (timerInterval) {
            clearInterval(timerInterval);
        }
        timerInterval = window.setInterval(function () {
            timeRemaining -= 0.1;
            if (timeRemaining <= 0) {
                timeRemaining = 0;
                updateTimerDisplay();
                clearInterval(timerInterval);
                timerInterval = null;
                onTimeUp();
            } else {
                updateTimerDisplay();
            }
        }, 100);
    }

    function buildParticipants(total) {
        const participants = [];
        const pool = creatureNames.filter(function (name) {
            return name !== targetName;
        });
        const shuffled = shuffle(pool.slice());
        const neededDecoys = Math.min(total - 1, shuffled.length);
        for (let i = 0; i < neededDecoys; i += 1) {
            participants.push({ name: shuffled[i], target: false });
        }
        participants.push({ name: targetName, target: true });
        return shuffle(participants);
    }

    function placeParticipants(participants) {
        clearBoard();
        const boardWidth = board.clientWidth;
        const boardHeight = board.clientHeight;
        const safeMargin = 12;
        const posterWidth = poster ? poster.offsetWidth : 0;
        const posterHeight = poster ? poster.offsetHeight : 0;
        const posterPadding = 24;
        const tokens = [];

        participants.forEach(function (participant) {
            const token = document.createElement('img');
            token.className = 'creature-token';
            token.src = selectedVariants[participant.name];
            token.alt = participant.name + ' portrait';
            token.setAttribute('data-name', participant.name);
            if (participant.target) {
                token.setAttribute('data-target', 'true');
            }
            board.appendChild(token);

            const tokenWidth = token.offsetWidth || Math.max(70, boardWidth * 0.12);
            const tokenHeight = token.offsetHeight || Math.max(70, boardWidth * 0.12);
            const restrictedX = boardWidth - posterWidth - posterPadding;
            const restrictedY = posterHeight + posterPadding;

            let attempts = 0;
            let pos = { x: 0, y: 0 };
            do {
                pos.x = safeMargin + Math.random() * Math.max(1, boardWidth - tokenWidth - safeMargin * 2);
                pos.y = safeMargin + Math.random() * Math.max(1, boardHeight - tokenHeight - safeMargin * 2);
                attempts += 1;
            } while (
                attempts < 50 &&
                ((poster && pos.x + tokenWidth > restrictedX && pos.y < restrictedY) || overlapsExisting(tokens, pos.x, pos.y, tokenWidth, tokenHeight))
            );

            token.style.left = pos.x + 'px';
            token.style.top = pos.y + 'px';

            const record = {
                el: token,
                x: pos.x,
                y: pos.y,
                width: tokenWidth,
                height: tokenHeight,
                target: participant.target
            };
            tokens.push(record);

            if (participant.target) {
                token.addEventListener('click', onTargetClick);
            } else {
                token.addEventListener('click', onDecoyClick);
            }
        });

        return tokens;
    }

    function configureMovement(tokens, round) {
        movers = [];
        if (!round || round.speed <= 0) {
            stopMovement();
            return;
        }

        const maxSpeed = round.speed;
        const diagonal = !!round.diagonal;
        movers = tokens.map(function (token) {
            const baseVy = (Math.random() * 0.6 + 0.4) * maxSpeed;
            const vx = diagonal ? (Math.random() - 0.5) * maxSpeed : (Math.random() < 0.35 ? (Math.random() - 0.5) * maxSpeed * 0.4 : 0);
            const vy = baseVy;
            return {
                el: token.el,
                x: token.x,
                y: token.y,
                vx: vx,
                vy: vy,
                width: token.width,
                height: token.height
            };
        });

        if (animationFrameId) {
            cancelAnimationFrame(animationFrameId);
        }
        lastFrameTime = null;
        animationFrameId = requestAnimationFrame(stepMovement);
    }

    function stepMovement(timestamp) {
        if (!roundActive) {
            animationFrameId = null;
            return;
        }
        if (!lastFrameTime) {
            lastFrameTime = timestamp;
        }
        const delta = (timestamp - lastFrameTime) / 1000;
        lastFrameTime = timestamp;

        const boardWidth = board.clientWidth;
        const boardHeight = board.clientHeight;
        const padding = 4;

        movers.forEach(function (mover) {
            mover.x += mover.vx * delta;
            mover.y += mover.vy * delta;

            if (mover.x <= padding) {
                mover.x = padding;
                mover.vx = Math.abs(mover.vx);
            } else if (mover.x + mover.width >= boardWidth - padding) {
                mover.x = boardWidth - mover.width - padding;
                mover.vx = -Math.abs(mover.vx);
            }

            if (mover.y <= padding) {
                mover.y = padding;
                mover.vy = Math.abs(mover.vy);
            } else if (mover.y + mover.height >= boardHeight - padding) {
                mover.y = boardHeight - mover.height - padding;
                mover.vy = -Math.abs(mover.vy);
            }

            mover.el.style.left = mover.x + 'px';
            mover.el.style.top = mover.y + 'px';
        });

        animationFrameId = requestAnimationFrame(stepMovement);
    }

    function onTargetClick(event) {
        if (!roundActive) {
            return;
        }
        roundActive = false;
        const el = event.currentTarget;
        el.classList.add('found');
        celebrateSuccess();
    }

    function onDecoyClick(event) {
        if (!roundActive) {
            return;
        }
        const el = event.currentTarget;
        const name = el.getAttribute('data-name') || 'That one';
        el.classList.remove('wrong');
        void el.offsetWidth; // restart animation
        el.classList.add('wrong');
        statusText.textContent = name + ' is not the one on the poster!';
        window.setTimeout(function () {
            el.classList.remove('wrong');
        }, 400);
    }

    function celebrateSuccess() {
        stopMovement();
        if (timerInterval) {
            clearInterval(timerInterval);
            timerInterval = null;
        }
        updateTimerDisplay();

        if (currentRoundIndex === totalRounds - 1) {
            statusText.textContent = 'You tracked down ' + targetName + ' in every round!';
            showOverlay('All rounds cleared!', 1500);
            state = 'restart';
            startButton.disabled = false;
            startButton.textContent = 'Play again';
            startButton.focus();
        } else {
            statusText.textContent = 'Great eye! Get ready for the next round.';
            showOverlay('Nice find!', 1200);
            state = 'next';
            startButton.disabled = false;
            startButton.textContent = 'Next round';
            startButton.focus();
        }
    }

    function onTimeUp() {
        if (!roundActive) {
            return;
        }
        roundActive = false;
        stopMovement();
        statusText.textContent = 'Time\'s up! ' + targetName + ' slipped away.';
        showOverlay('Time\'s up!', 1500);
        state = 'restart';
        startButton.disabled = false;
        startButton.textContent = 'Try again';
        startButton.focus();
    }

    function stopMovement() {
        if (animationFrameId) {
            cancelAnimationFrame(animationFrameId);
            animationFrameId = null;
        }
        movers = [];
        lastFrameTime = null;
    }

    function cleanupRound() {
        roundActive = false;
        stopMovement();
        if (timerInterval) {
            clearInterval(timerInterval);
            timerInterval = null;
        }
        clearBoard();
        if (timerProgress) {
            timerProgress.style.width = '100%';
        }
    }

    function clearBoard() {
        const tokens = board.querySelectorAll('.creature-token');
        tokens.forEach(function (token) {
            token.remove();
        });
    }

    function updateTimerDisplay() {
        if (timerLabel) {
            timerLabel.textContent = formatTime(timeRemaining);
        }
        if (timerProgress) {
            const fraction = roundDuration > 0 ? Math.max(0, Math.min(1, timeRemaining / roundDuration)) : 0;
            timerProgress.style.width = fraction * 100 + '%';
        }
    }

    function formatTime(value) {
        if (typeof value !== 'number' || !isFinite(value)) {
            return '--:--';
        }
        const totalSeconds = Math.max(0, Math.ceil(value));
        const minutes = Math.floor(totalSeconds / 60);
        const seconds = totalSeconds % 60;
        return minutes + ':' + String(seconds).padStart(2, '0');
    }

    function overlapsExisting(existing, x, y, width, height) {
        const buffer = 18;
        return existing.some(function (item) {
            return !(
                x + width + buffer < item.x ||
                x > item.x + item.width + buffer ||
                y + height + buffer < item.y ||
                y > item.y + item.height + buffer
            );
        });
    }

    function shuffle(array) {
        for (let i = array.length - 1; i > 0; i -= 1) {
            const j = Math.floor(Math.random() * (i + 1));
            const temp = array[i];
            array[i] = array[j];
            array[j] = temp;
        }
        return array;
    }

    function showOverlay(message, duration) {
        if (!overlay || !overlayText) {
            return;
        }
        overlayText.textContent = message;
        overlay.classList.remove('hidden');
        if (duration && duration > 0) {
            window.setTimeout(function () {
                hideOverlay();
            }, duration);
        }
    }

    function hideOverlay() {
        if (!overlay) {
            return;
        }
        overlay.classList.add('hidden');
    }
})();