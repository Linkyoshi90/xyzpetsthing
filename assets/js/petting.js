(function () {
    const data = window.pettingData;
    if (!data || !document.querySelector('.petting-viewport')) return;

    const state = {
        preferences: data.preferences || {},
        foods: data.foods || [],
        pets: data.pets || [],
        activePetId: data.activePetId,
        draggingItem: null,
        petAway: false,
    };

    const viewport = document.querySelector('.petting-viewport');
    const stage = viewport.querySelector('.petting-stage');
    const creature = stage.querySelector('.petting-creature');
    const inventoryToggle = viewport.querySelector('.inventory-toggle');
    const inventoryBanner = viewport.querySelector('.inventory-banner');
    const inventoryClose = viewport.querySelector('.inventory-close');
    const petToggle = viewport.querySelector('.petting-switch-toggle');
    const petSwitcher = viewport.querySelector('.petting-switcher');
    const petSwitcherClose = viewport.querySelector('.petting-switcher-close');
    const floorShadow = stage.querySelector('.petting-floor-shadow');

    const sounds = {
        eat: new Audio('assets/sfx/petting_eat.wav'),
        run: new Audio('assets/sfx/petting_run.wav'),
    };
    sounds.eat.volume = 0.4;
    sounds.run.volume = 0.2;

    function activePet() {
        return state.pets.find((p) => p.id === state.activePetId) || state.pets[0];
    }

    function toggleInventory(force) {
        const shouldOpen = force !== undefined ? force : !inventoryBanner.classList.contains('open');
        inventoryBanner.classList.toggle('open', shouldOpen);
    }

    function togglePetSwitcher(force) {
        const shouldOpen = force !== undefined ? force : !petSwitcher.classList.contains('open');
        petSwitcher.classList.toggle('open', shouldOpen);
    }

    function spawnDust(x, y) {
        const dust = document.createElement('img');
        dust.src = 'images/games/petting_dust.svg';
        dust.className = 'dust-cloud';
        dust.style.left = `${x - 24}px`;
        dust.style.top = `${y - 12}px`;
        stage.appendChild(dust);
        setTimeout(() => dust.remove(), 700);
    }

    function spawnCrumbs(x, y) {
        for (let i = 0; i < 6; i++) {
            const crumb = document.createElement('img');
            crumb.src = 'images/games/petting_crumb.svg';
            crumb.className = 'food-crumb';
            const offsetX = (Math.random() * 60) - 30;
            crumb.style.left = `${x + offsetX}px`;
            crumb.style.top = `${y}px`;
            stage.appendChild(crumb);
            setTimeout(() => crumb.remove(), 800);
        }
    }

    function spawnHearts(count, x, y) {
        const burst = document.createElement('div');
        burst.className = 'heart-burst';
        burst.style.left = `${x}px`;
        burst.style.top = `${y}px`;
        const heartPath = 'images/games/petting_heart.svg';
        for (let i = 0; i < count; i++) {
            const heart = document.createElement('img');
            heart.src = heartPath;
            heart.alt = '❤️';
            burst.appendChild(heart);
        }
        stage.appendChild(burst);
        setTimeout(() => burst.remove(), 1400);
    }

    function playEatSequence(itemId, point) {
        if (!creature) return;
        const img = document.createElement('img');
        img.src = state.foods.find((f) => f.item_id === itemId)?.image || 'images/games/petting_food_placeholder.svg';
        img.className = 'floating-food';
        img.style.left = `${point.x}px`;
        img.style.top = `${point.y}px`;
        stage.appendChild(img);

        spawnCrumbs(point.x, point.y + 10);
        sounds.eat.currentTime = 0;
        sounds.eat.play().catch(() => { });

        const pet = activePet();
        const likeScale = (state.preferences[pet?.species_id] || {})[itemId] || 2;
        setTimeout(() => {
            const rect = creature.getBoundingClientRect();
            const stageRect = stage.getBoundingClientRect();
            const cx = rect.left + rect.width / 2 - stageRect.left;
            const cy = rect.top - stageRect.top + 10;
            spawnHearts(Math.max(1, Math.min(3, likeScale)), cx, cy);
        }, 500);

        setTimeout(() => img.remove(), 900);
    }

    function moveCreatureRandom() {
        if (!creature) return;
        const stageRect = stage.getBoundingClientRect();
        const nextLeft = 10 + Math.random() * 80; // percent
        const nextBottom = 6 + Math.random() * 10; // percent
        creature.classList.add('running');
        creature.style.left = `${nextLeft}%`;
        creature.style.bottom = `${nextBottom}%`;
        floorShadow.style.left = `${nextLeft}%`;
        floorShadow.style.width = `${120 + Math.random() * 60}px`;
        spawnDust(stageRect.left + (nextLeft / 100) * stageRect.width, stageRect.bottom - (nextBottom / 100) * stageRect.height);
        sounds.run.currentTime = 0;
        sounds.run.play().catch(() => { });
        setTimeout(() => creature.classList.remove('running'), 1200);

        // Occasionally hop off-screen
        if (Math.random() > 0.8 && !state.petAway) {
            state.petAway = true;
            creature.classList.add('offscreen');
            const prompt = document.createElement('div');
            prompt.className = 'petting-prompt';
            prompt.textContent = 'Your pet wandered off! Double-tap to call them back.';
            prompt.dataset.pettingPrompt = 'call-back';
            stage.appendChild(prompt);
        }
    }

    function bringCreatureBack() {
        if (!creature) return;
        const prompt = stage.querySelector('[data-petting-prompt="call-back"]');
        if (prompt) prompt.remove();
        state.petAway = false;
        creature.classList.remove('offscreen');
        moveCreatureRandom();
    }

    function handleDrop(ev) {
        ev.preventDefault();
        if (!state.draggingItem || !creature) return;
        const creatureRect = creature.getBoundingClientRect();
        const withinPet = ev.clientX >= creatureRect.left && ev.clientX <= creatureRect.right && ev.clientY >= creatureRect.top && ev.clientY <= creatureRect.bottom;
        if (!withinPet) return;
        const stageRect = stage.getBoundingClientRect();
        const point = { x: ev.clientX - stageRect.left, y: ev.clientY - stageRect.top };
        playEatSequence(state.draggingItem.item_id, point);
        state.draggingItem = null;
    }

    function handleDragStart(ev) {
        const itemId = parseInt(ev.currentTarget.dataset.itemId, 10);
        const item = state.foods.find((f) => f.item_id === itemId);
        if (!item) return;
        state.draggingItem = item;
        ev.dataTransfer.effectAllowed = 'copy';
        ev.dataTransfer.setData('text/plain', String(itemId));
        toggleInventory(false);
    }

    function handleDragEnd() {
        state.draggingItem = null;
    }

    function bindInventoryItems() {
        document.querySelectorAll('.inventory-item').forEach((el) => {
            el.addEventListener('dragstart', handleDragStart);
            el.addEventListener('dragend', handleDragEnd);
        });
    }

    function switchPet(petId) {
        const pet = state.pets.find((p) => p.id === petId);
        if (!pet || !creature) return;
        creature.classList.add('offscreen');
        setTimeout(() => {
            creature.src = pet.img;
            creature.alt = pet.name;
            creature.dataset.petId = pet.id;
            state.activePetId = pet.id;
            creature.classList.remove('offscreen');
            moveCreatureRandom();
        }, 300);
    }

    function bindPetSwitcher() {
        petSwitcher.querySelectorAll('.petting-card').forEach((card) => {
            card.addEventListener('click', () => {
                const petId = parseInt(card.dataset.petId, 10);
                switchPet(petId);
                togglePetSwitcher(false);
            });
        });
    }

    function setupStage() {
        inventoryToggle?.addEventListener('click', () => toggleInventory());
        inventoryClose?.addEventListener('click', () => toggleInventory(false));
        petToggle?.addEventListener('click', () => togglePetSwitcher());
        petSwitcherClose?.addEventListener('click', () => togglePetSwitcher(false));

        stage.addEventListener('dragover', (ev) => ev.preventDefault());
        stage.addEventListener('drop', handleDrop);
        stage.addEventListener('dblclick', bringCreatureBack);

        bindInventoryItems();
        bindPetSwitcher();

        if (creature) {
            setInterval(() => {
                if (!state.petAway) moveCreatureRandom();
            }, 4200);
        }
    }

    document.addEventListener('DOMContentLoaded', setupStage);
})();