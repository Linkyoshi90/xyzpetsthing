(function () {
    const root = document.getElementById('cups-game');
    if (!root) return;

    const endpoint = root.dataset.endpoint || 'index.php?pg=cups-and-balls';
    const currencyName = root.dataset.currency || 'Cash';
    const reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const slotOffsets = [-1, 0, 1];
    const roundConfig = {
        1: { moves: 5, moveMs: 520, pauseMs: 110, label: 'Round 1' },
        2: { moves: 7, moveMs: 430, pauseMs: 95, label: 'Round 2' },
        3: { moves: 11, moveMs: 315, pauseMs: 65, label: 'Round 3' },
    };

    let state = readJson(root.dataset.state, {
        status: 'idle',
        round: 0,
        bet: 0,
        ball: null,
        last_choice: null,
        last_correct: null,
        payout: 0,
        message: 'Place a bet to begin.',
    });
    let balance = Number(root.dataset.balance || 0);
    let positions = [0, 1, 2];
    let busy = false;
    let choosing = false;

    const stage = document.getElementById('cups-stage');
    const ball = document.getElementById('cups-ball');
    const statusEl = document.getElementById('cups-status');
    const balanceEl = document.getElementById('cups-balance');
    const currentBetEl = document.getElementById('cups-current-bet');
    const payoutEl = document.getElementById('cups-payout');
    const betForm = document.getElementById('cups-bet-form');
    const betInput = document.getElementById('cups-bet');
    const startButton = document.getElementById('cups-start');
    const nextButton = document.getElementById('cups-next');
    const resetButton = document.getElementById('cups-reset');
    const cups = Array.from(root.querySelectorAll('.cup-slot')).map((element) => ({
        element,
        id: Number(element.dataset.cup),
    }));
    const roundDots = Array.from(root.querySelectorAll('[data-round-dot]'));

    function readJson(value, fallback) {
        try {
            return JSON.parse(value);
        } catch {
            return fallback;
        }
    }

    function wait(ms) {
        return new Promise((resolve) => {
            window.setTimeout(resolve, reduceMotion ? Math.min(ms, 80) : ms);
        });
    }

    function setStatus(message) {
        statusEl.textContent = message || '';
    }

    function formatAmount(amount) {
        return String(Math.max(0, Number(amount) || 0));
    }

    function updateBalance(nextBalance) {
        balance = Math.max(0, Number(nextBalance) || 0);
        balanceEl.textContent = formatAmount(balance);
        betInput.max = String(Math.max(1, balance));
        if (window.updateCurrencyDisplay) {
            window.updateCurrencyDisplay({ cash: balance });
        }
    }

    function activeGame() {
        return state.status === 'awaiting_choice'
            || (state.status === 'reveal' && state.last_correct && state.round < 3);
    }

    function renderHud() {
        currentBetEl.textContent = formatAmount(state.bet || 0);
        payoutEl.textContent = formatAmount(state.bet ? state.bet * 2 : 0);
        roundDots.forEach((dot) => {
            const round = Number(dot.dataset.roundDot);
            dot.classList.toggle('is-active', state.round === round && state.status !== 'won' && state.status !== 'lost');
            dot.classList.toggle('is-complete', state.round > round || state.status === 'won');
        });
        nextButton.hidden = !(state.status === 'reveal' && state.last_correct && state.round < 3) || busy;
        resetButton.hidden = !(state.status === 'won' || state.status === 'lost') || busy;

        const locked = busy || activeGame();
        betInput.disabled = locked || balance < 1;
        startButton.disabled = locked || balance < 1;
        startButton.textContent = state.status === 'won' || state.status === 'lost' ? 'Play Again' : 'Start';
    }

    function renderPositions() {
        cups.forEach(({ element, id }) => {
            element.style.setProperty('--slot-x', slotOffsets[positions[id]]);
            element.style.zIndex = String(10 + positions[id]);
        });
    }

    function setBallUnder(cupId, visible) {
        const slot = cupId === null || cupId === undefined ? 1 : positions[Number(cupId)];
        ball.style.setProperty('--slot-x', slotOffsets[slot]);
        ball.classList.toggle('is-visible', Boolean(visible));
    }

    function clearCupMarks() {
        cups.forEach(({ element }) => {
            element.classList.remove('is-lifted', 'is-selected', 'is-correct', 'is-wrong');
            element.style.setProperty('--twist', '0deg');
            element.disabled = true;
        });
        choosing = false;
    }

    function liftCup(cupId, lifted) {
        const cup = cups.find((item) => item.id === Number(cupId));
        if (cup) {
            cup.element.classList.toggle('is-lifted', Boolean(lifted));
        }
    }

    function liftAll(lifted) {
        cups.forEach(({ element }) => {
            element.classList.toggle('is-lifted', Boolean(lifted));
        });
    }

    function setBusy(nextBusy) {
        busy = nextBusy;
        root.classList.toggle('is-busy', busy);
        renderHud();
    }

    async function postAction(action, payload) {
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(Object.assign({ action }, payload || {})),
        });
        let data = null;
        try {
            data = await response.json();
        } catch {
            throw new Error('The table could not answer that move.');
        }
        if (!response.ok || !data || data.ok === false) {
            throw new Error((data && data.error) || 'The table refused that move.');
        }
        if (data.state) {
            state = data.state;
        }
        if (data.balance !== undefined) {
            updateBalance(data.balance);
        }
        return data;
    }

    function randomPair() {
        const first = Math.floor(Math.random() * 3);
        let second = Math.floor(Math.random() * 3);
        while (second === first) {
            second = Math.floor(Math.random() * 3);
        }
        return [first, second];
    }

    async function shuffleCups(round) {
        const config = roundConfig[round] || roundConfig[1];
        root.style.setProperty('--move-ms', `${config.moveMs}ms`);
        for (let index = 0; index < config.moves; index++) {
            const [first, second] = randomPair();
            const direction = positions[first] < positions[second] ? 1 : -1;
            cups[first].element.style.setProperty('--twist', `${direction * 13}deg`);
            cups[second].element.style.setProperty('--twist', `${direction * -13}deg`);
            [positions[first], positions[second]] = [positions[second], positions[first]];
            renderPositions();
            await wait(config.moveMs);
            cups[first].element.style.setProperty('--twist', '0deg');
            cups[second].element.style.setProperty('--twist', '0deg');
            await wait(config.pauseMs);
        }
    }

    function enableChoosing() {
        choosing = true;
        cups.forEach(({ element }) => {
            element.disabled = false;
        });
        setStatus('Choose a cup.');
        renderHud();
    }

    async function playRoundIntro() {
        setBusy(true);
        choosing = false;
        positions = [0, 1, 2];
        clearCupMarks();
        renderPositions();
        setBallUnder(state.ball, true);
        liftAll(true);
        setStatus(roundConfig[state.round] ? roundConfig[state.round].label : `Round ${state.round}`);
        renderHud();

        await wait(900);
        liftAll(false);
        await wait(420);
        setBallUnder(state.ball, false);
        await wait(180);
        await shuffleCups(state.round);

        setBusy(false);
        enableChoosing();
    }

    async function revealResult() {
        setBusy(true);
        choosing = false;
        cups.forEach(({ element }) => {
            element.disabled = true;
        });

        const selected = Number(state.last_choice);
        const actual = Number(state.ball);
        const selectedCup = cups.find((cup) => cup.id === selected);
        if (selectedCup) {
            selectedCup.element.classList.add('is-selected');
        }

        setBallUnder(actual, true);
        await wait(180);
        liftCup(selected, true);

        if (selected !== actual) {
            if (selectedCup) {
                selectedCup.element.classList.add('is-wrong');
            }
            await wait(520);
            liftCup(actual, true);
        } else if (selectedCup) {
            selectedCup.element.classList.add('is-correct');
        }

        await wait(520);
        setStatus(state.message);
        setBusy(false);
        renderHud();
    }

    async function handleStart(event) {
        event.preventDefault();
        if (busy || activeGame()) return;

        const bet = Math.max(1, Number(betInput.value) || 0);
        if (bet > balance) {
            setStatus(`Not enough ${currencyName}.`);
            return;
        }

        try {
            setBusy(true);
            await postAction('start', { bet });
            await playRoundIntro();
        } catch (error) {
            setStatus(error.message);
            setBusy(false);
        }
    }

    async function handleChoice(event) {
        const cupButton = event.currentTarget;
        if (!choosing || busy || cupButton.disabled) return;

        try {
            choosing = false;
            cupButton.classList.add('is-selected');
            cups.forEach(({ element }) => {
                element.disabled = true;
            });
            setStatus('Revealing...');
            await postAction('choose', { choice: Number(cupButton.dataset.cup) });
            await revealResult();
        } catch (error) {
            setStatus(error.message);
            setBusy(false);
        }
    }

    async function handleNext() {
        if (busy) return;
        try {
            setBusy(true);
            await postAction('next');
            await playRoundIntro();
        } catch (error) {
            setStatus(error.message);
            setBusy(false);
        }
    }

    async function handleReset() {
        if (busy) return;
        try {
            setBusy(true);
            await postAction('reset');
            positions = [0, 1, 2];
            clearCupMarks();
            renderPositions();
            setBallUnder(null, false);
            setStatus(state.message);
            setBusy(false);
        } catch (error) {
            setStatus(error.message);
            setBusy(false);
        }
    }

    function changeBet(delta) {
        if (betInput.disabled) return;
        const next = Math.max(1, Math.min(balance || 1, (Number(betInput.value) || 1) + delta));
        betInput.value = String(next);
    }

    cups.forEach(({ element }) => {
        element.addEventListener('click', handleChoice);
    });
    betForm.addEventListener('submit', handleStart);
    nextButton.addEventListener('click', handleNext);
    resetButton.addEventListener('click', handleReset);
    root.querySelectorAll('[data-step]').forEach((button) => {
        button.addEventListener('click', () => changeBet(Number(button.dataset.step)));
    });

    renderPositions();
    updateBalance(balance);
    setBallUnder(state.ball, state.status === 'won' || state.status === 'lost');
    setStatus(state.message);
    renderHud();

    if (state.status === 'awaiting_choice') {
        playRoundIntro();
    } else if (state.status === 'reveal' || state.status === 'won' || state.status === 'lost') {
        clearCupMarks();
        renderPositions();
        if (state.last_choice !== null) {
            liftCup(state.last_choice, true);
        }
        if (state.ball !== null && state.ball !== state.last_choice) {
            liftCup(state.ball, true);
        }
        setBallUnder(state.ball, true);
        renderHud();
    }
})();
