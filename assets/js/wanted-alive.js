(function () {
    const data = window.wantedAliveData || {};
    const variants = data.variants || {};

    const board = document.getElementById('hunt-board');
    const startButton = document.getElementById('hunt-start');
    const roundInfo = document.getElementById('hunt-round');
    const scoreDisplay = document.getElementById('hunt-score');
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
        if (timerProgress) {
            timerProgress.style.width = '0%';
        }
        if (scoreDisplay) {
            scoreDisplay.textContent = 'Score: 0.0';
        }
        return;
    }

    let stageNumber = 0;
    let state = 'ready';
    let stageDuration = 0;
    let timeRemaining = 0;
    let stageActive = false;
    let timerInterval = null;
    let animationFrameId = null;
    let lastFrameTime = null;
    let movers = [];
    let totalScore = 0;
    let hasSubmittedScore = false;
    let stageVariants = {};
    let targetName = '';
    let targetImage = '';

    startButton.textContent = 'Start Hunt';
    roundInfo.textContent = 'Stage 0';
    statusText.textContent = 'Press start to begin the endless chase.';
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

    resetStageVariants();
    pickTarget();
    updateScoreDisplay();

    startButton.addEventListener('click', function () {
        if (state === 'ready' || state === 'restart') {
            startGame();
        } else if (state === 'next') {
            startNextStage();
        }
    });

    function startGame() {
        cleanupRound();
        state = 'playing';
        stageNumber = 0;
        totalScore = 0;
        hasSubmittedScore = false;
        updateScoreDisplay();
        statusText.textContent = 'Bank the leftover seconds to earn rewards.';
        startButton.disabled = true;
        startButton.textContent = 'Hunting...';
        showOverlay('Stage 1', 900);
        window.setTimeout(function () {
            startStage(1);
        }, 900);
    }

    function startNextStage() {
        state = 'playing';
        startButton.disabled = true;
        startButton.textContent = 'Hunting...';
        showOverlay('Stage ' + (stageNumber + 1), 900);
        window.setTimeout(function () {
            startStage(stageNumber + 1);
        }, 900);
    }

    function startStage(nextStage) {
        cleanupRound();
        stageNumber = nextStage;
        resetStageVariants();
        pickTarget();
        const config = getStageConfig(stageNumber);
        stageDuration = config.duration;
        timeRemaining = config.duration;
        roundInfo.textContent = 'Stage ' + stageNumber;
        updateTimerDisplay();

        const participants = buildParticipants(config.total);
        const tokens = placeParticipants(participants);
        configureMovement(tokens, config);

        stageActive = true;
        state = 'playing';
        statusText.textContent = 'Stage ' + stageNumber + ': Find ' + targetName + ' before time runs out!';

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
                triggerGameOver();
            } else {
                updateTimerDisplay();
            }
        }, 100);
    }

    function getStageConfig(stage) {
        const baseDuration = 25;
        const duration = Math.max(6, baseDuration - (stage - 1) * 1.4);
        let total;
        if (creatureNames.length <= 1) {
            total = creatureNames.length;
        } else {
            total = Math.min(creatureNames.length, Math.max(2, Math.floor(4 + (stage - 1) * 0.75)));
        }
        const speed = stage <= 1 ? 0 : Math.min(180, 20 + (stage - 2) * 12);
        const diagonal = stage >= 4;
        return { total: total, duration: duration, speed: speed, diagonal: diagonal };
    }

    function buildParticipants(total) {
        const participants = [];
        if (!targetName) {
            return participants;
        }
        const neededDecoys = Math.max(0, (total || 0) - 1);
        const pool = creatureNames.filter(function (name) {
            return name !== targetName;
        });
        if (pool.length > 0 && neededDecoys > 0) {
            const bag = shuffle(pool.slice());
            let index = 0;
            while (participants.length < neededDecoys) {
                if (index >= bag.length) {
                    index = 0;
                    shuffle(bag);
                }
                participants.push({ name: bag[index], target: false });
                index += 1;
            }
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
            const imgSrc = getImageFor(participant.name);
            if (imgSrc) {
                token.src = imgSrc;
            }
            token.alt = (participant.name || 'Creature') + ' portrait';
            token.setAttribute('data-name', participant.name || '');
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
                ((poster && pos.x + tokenWidth > restrictedX && pos.y < restrictedY) ||
                    overlapsExisting(tokens, pos.x, pos.y, tokenWidth, tokenHeight))
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

    function configureMovement(tokens, config) {
        movers = [];
        if (!config || config.speed <= 0) {
            stopMovement();
            return;
        }

        const maxSpeed = config.speed;
        const diagonal = !!config.diagonal;
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
        if (!stageActive) {
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
        if (!stageActive) {
            return;
        }
        stageActive = false;
        const el = event.currentTarget;
        el.classList.add('found');
        endStageSuccess();
    }

    function onDecoyClick(event) {
        if (!stageActive) {
            return;
        }
        const el = event.currentTarget;
        const name = el.getAttribute('data-name') || 'That one';
        el.classList.remove('wrong');
        void el.offsetWidth;
        el.classList.add('wrong');
        statusText.textContent = name + ' is not the one on the poster!';
        window.setTimeout(function () {
            el.classList.remove('wrong');
        }, 400);
    }

    function endStageSuccess() {
        stopMovement();
        if (timerInterval) {
            clearInterval(timerInterval);
            timerInterval = null;
        }
        const earned = Math.max(0, timeRemaining);
        const displayEarned = Math.round(earned * 10) / 10;
        totalScore += earned;
        timeRemaining = 0;
        updateTimerDisplay();
        updateScoreDisplay();

        statusText.textContent =
            'Stage ' + stageNumber + ' cleared! Banked ' + displayEarned.toFixed(1) + ' seconds. Total: ' + totalScore.toFixed(1) + '.';
        showOverlay('+' + displayEarned.toFixed(1) + 's', 1200);

        state = 'next';
        startButton.disabled = false;
        startButton.textContent = 'Next stage';
        startButton.focus();
    }

    function triggerGameOver() {
        stageActive = false;
        stopMovement();
        if (timerInterval) {
            clearInterval(timerInterval);
            timerInterval = null;
        }
        updateTimerDisplay();
        const baseMessage = targetName ? "Time's up! " + targetName + ' slipped away.' : "Time's up! The target slipped away.";
        statusText.textContent = baseMessage;
        showOverlay("Time's up!", 1500);
        state = 'restart';
        startButton.disabled = false;
        startButton.textContent = 'Try again';
        startButton.focus();
        promptScoreSubmission(baseMessage);
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
        stageActive = false;
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
            const fraction = stageDuration > 0 ? Math.max(0, Math.min(1, timeRemaining / stageDuration)) : 0;
            timerProgress.style.width = fraction * 100 + '%';
        }
    }

    function updateScoreDisplay() {
        if (scoreDisplay) {
            scoreDisplay.textContent = 'Score: ' + totalScore.toFixed(1);
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

    function promptScoreSubmission(baseMessage) {
        if (hasSubmittedScore) {
            return;
        }
        const finalScore = Math.max(0, Math.round(totalScore));
        hasSubmittedScore = true;
        if (!finalScore) {
            return;
        }
        const submit = window.confirm('Submit your score of ' + finalScore + ' to exchange for rewards?');
        if (!submit) {
            return;
        }
        fetch('score_exchange.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ game: 'wantedalive', score: finalScore })
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                if (!data) {
                    return;
                }
                if (data.error) {
                    window.alert(data.error);
                    if (statusText) {
                        statusText.textContent = baseMessage + ' (Exchange failed.)';
                    }
                    return;
                }
                if (typeof data.cash !== 'undefined' && window.updateCurrencyDisplay) {
                    window.updateCurrencyDisplay({ cash: data.cash });
                }
                if (statusText) {
                    statusText.textContent = baseMessage + ' Score exchanged for rewards!';
                }
            })
            .catch(function () {
                window.alert('Unable to submit score right now.');
            });
    }

    function resetStageVariants() {
        stageVariants = {};
    }

    function getImageFor(name) {
        if (!name) {
            return '';
        }
        if (!stageVariants[name]) {
            const choices = variants[name];
            if (Array.isArray(choices) && choices.length > 0) {
                stageVariants[name] = choices[Math.floor(Math.random() * choices.length)];
            } else {
                stageVariants[name] = '';
            }
        }
        return stageVariants[name];
    }

    function pickTarget() {
        if (creatureNames.length === 0) {
            targetName = '';
            targetImage = '';
            return;
        }
        const available = creatureNames.filter(function (name) {
            return !!getImageFor(name);
        });
        if (available.length > 0) {
            targetName = available[Math.floor(Math.random() * available.length)];
        } else {
            targetName = creatureNames[0];
        }
        targetImage = getImageFor(targetName);
        if (wantedImage) {
            wantedImage.src = targetImage || '';
            wantedImage.alt = (targetName ? targetName : 'Wanted creature') + ' portrait';
        }
        if (wantedName) {
            wantedName.textContent = targetName || '';
        }
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