<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../lib/pets.php';

require_login();

$uid = (int)current_user()['id'];

function slugify_pet(string $name): string {
    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $name));
    return trim($slug, '_');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $payload = json_decode($_POST['payload'] ?? '', true);
    if (!is_array($payload)) {
        header('Location: index.php');
        exit;
    }

    $pets = get_user_pets($uid, true);
    $pet_lookup = [];
    foreach ($pets as $pet) {
        $pet_lookup[(int)$pet['pet_instance_id']] = $pet;
    }

    foreach ($payload as $entry) {
        if (!is_array($entry)) {
            continue;
        }
        $pet_id = (int)($entry['id'] ?? 0);
        if (!$pet_id || !isset($pet_lookup[$pet_id])) {
            continue;
        }
        $pet = $pet_lookup[$pet_id];
        $hunger = max(0, min(100, (int)($entry['hunger'] ?? $pet['hunger'])));
        $happiness = max(0, min(100, (int)($entry['happiness'] ?? $pet['happiness'])));
        $intelligence = max(0, (int)($entry['intelligence'] ?? $pet['intelligence']));
        $hp_max = isset($pet['hp_max']) ? (int)$pet['hp_max'] : (int)($pet['hp_current'] ?? 0);
        $hp_max = max(1, $hp_max);
        $hp_current = max(0, min($hp_max, (int)($entry['hpCurrent'] ?? $pet['hp_current'])));

        q(
            "UPDATE pet_instances
                SET hunger = ?,
                    happiness = ?,
                    intelligence = ?,
                    hp_current = ?
              WHERE pet_instance_id = ? AND owner_user_id = ?",
            [$hunger, $happiness, $intelligence, $hp_current, $pet_id, $uid]
        );
    }

    header('Location: index.php');
    exit;
}

$pets = get_user_pets($uid);
if (!$pets) {
    echo '<p>No pets yet. <a href="?pg=create_pet">Create one</a>.</p>';
    return;
}

$pets_payload = array_map(function (array $pet): array {
    $species = $pet['species_name'] ?? '';
    return [
        'id' => (int)$pet['pet_instance_id'],
        'name' => $pet['nickname'] ?: $species,
        'species' => $species,
        'gender' => $pet['gender'] ?? 'f',
        'soundSlug' => slugify_pet($species),
        'image' => pet_image_url($species, $pet['color_name'] ?? null),
        'hunger' => (int)$pet['hunger'],
        'happiness' => (int)$pet['happiness'],
        'intelligence' => (int)$pet['intelligence'],
        'hpCurrent' => (int)$pet['hp_current'],
        'hpMax' => $pet['hp_max'] !== null ? (int)$pet['hp_max'] : (int)$pet['hp_current'],
    ];
}, $pets);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Petting Grounds</title>
    <style>
        :root {
            color-scheme: light;
            font-family: 'Trebuchet MS', 'Segoe UI', sans-serif;
        }
        body {
            margin: 0;
            background: #0f172a;
            color: #f8fafc;
        }
        .petting-page {
            position: relative;
            min-height: 100vh;
            overflow: hidden;
            background: linear-gradient(180deg, #7dd3fc 0%, #bae6fd 45%, #22c55e 46%, #16a34a 100%);
        }
        .top-bar {
            position: absolute;
            top: 16px;
            left: 16px;
            right: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 4;
            pointer-events: none;
        }
        .top-bar button {
            pointer-events: auto;
        }
        .menu-toggle,
        .exit-button {
            background: rgba(15, 23, 42, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.25);
            color: #f8fafc;
            padding: 8px 12px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 700;
        }
        .menu-panel {
            position: absolute;
            top: 64px;
            left: 16px;
            width: 260px;
            max-height: 320px;
            background: rgba(15, 23, 42, 0.92);
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 12px;
            transform: translateX(-110%);
            transition: transform 0.25s ease;
            z-index: 4;
        }
        .menu-panel.open {
            transform: translateX(0);
        }
        .menu-panel h3 {
            margin: 0 0 8px;
            font-size: 16px;
        }
        .pet-list {
            display: grid;
            gap: 8px;
            max-height: 240px;
            overflow-y: auto;
            padding-right: 4px;
        }
        .pet-button {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            padding: 8px;
            border-radius: 12px;
            cursor: pointer;
            color: inherit;
            text-align: left;
        }
        .pet-button img {
            width: 42px;
            height: 42px;
            object-fit: contain;
        }
        .petting-stage {
            position: relative;
            width: 100%;
            height: 100vh;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            overflow: hidden;
        }
        .petting-stage.focused {
            background: radial-gradient(circle at 50% 60%, rgba(255, 255, 255, 0.3), transparent 60%);
        }
        .petting-stage.focused .pet-sprite {
            transform: scale(1.25);
        }
        .pet-sprite {
            position: absolute;
            bottom: 120px;
            width: 280px;
            height: auto;
            transition: transform 0.3s ease;
            animation: roam 7s ease-in-out infinite;
        }
        .pet-sprite.run-out {
            animation: run-out 1s ease forwards;
        }
        .pet-sprite.run-in {
            animation: run-in 1s ease forwards;
        }
        @keyframes roam {
            0% { transform: translateX(-80px) scale(1); }
            50% { transform: translateX(80px) scale(1); }
            100% { transform: translateX(-80px) scale(1); }
        }
        @keyframes run-out {
            0% { transform: translateX(0) scale(1); opacity: 1; }
            100% { transform: translateX(420px) scale(0.8); opacity: 0; }
        }
        @keyframes run-in {
            0% { transform: translateX(-420px) scale(0.8); opacity: 0; }
            100% { transform: translateX(0) scale(1); opacity: 1; }
        }
        .item-dock {
            position: absolute;
            left: 16px;
            bottom: 16px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            z-index: 4;
        }
        .item-dock button {
            background: rgba(15, 23, 42, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #f8fafc;
            padding: 8px 12px;
            border-radius: 12px;
            cursor: pointer;
        }
        .item-panel {
            display: none;
            flex-direction: column;
            gap: 8px;
            padding: 10px;
            background: rgba(15, 23, 42, 0.9);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .item-panel.open {
            display: flex;
        }
        .item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 10px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            cursor: grab;
            font-weight: 600;
        }
        .item span.icon {
            font-size: 20px;
        }
        .drag-ghost {
            position: fixed;
            pointer-events: none;
            z-index: 10;
            transform: translate(-50%, -50%);
        }
        .emoji-pop {
            position: absolute;
            font-size: 28px;
            animation: float-up 1.2s ease forwards;
        }
        @keyframes float-up {
            0% { opacity: 0; transform: translate(-50%, 0) scale(0.8); }
            20% { opacity: 1; }
            100% { opacity: 0; transform: translate(-50%, -80px) scale(1.2); }
        }
        .stat-panel {
            position: absolute;
            right: 24px;
            bottom: 24px;
            background: rgba(15, 23, 42, 0.85);
            padding: 12px 16px;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            min-width: 220px;
            z-index: 3;
        }
        .stat-panel h4 {
            margin: 0 0 8px;
            font-size: 16px;
        }
        .stat {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
            font-size: 14px;
        }
    </style>
</head>
<body>
<div class="petting-page" id="petting-page">
    <div class="top-bar">
        <button class="menu-toggle" id="menu-toggle">☰ Creatures</button>
        <form id="exit-form" method="post" action="">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="payload" id="exit-payload" value="">
            <button class="exit-button" id="exit-button" type="submit">Exit</button>
        </form>
    </div>
    <div class="menu-panel" id="menu-panel">
        <h3>Your Creatures</h3>
        <div class="pet-list" id="pet-list"></div>
    </div>

    <div class="petting-stage" id="petting-stage">
        <img class="pet-sprite" id="pet-sprite" src="" alt="Active creature">
    </div>

    <div class="item-dock">
        <button type="button" id="item-toggle">🎒 Items</button>
        <div class="item-panel" id="item-panel">
            <div class="item" data-item="food"><span class="icon">🍖</span>Food</div>
            <div class="item" data-item="book"><span class="icon">📘</span>Book</div>
            <div class="item" data-item="potion"><span class="icon">🧪</span>Potion</div>
        </div>
    </div>

    <div class="stat-panel" id="stat-panel">
        <h4 id="stat-name"></h4>
        <div class="stat"><span>Hunger</span><span id="stat-hunger"></span></div>
        <div class="stat"><span>HP</span><span id="stat-hp"></span></div>
        <div class="stat"><span>Intelligence</span><span id="stat-intelligence"></span></div>
        <div class="stat"><span>Happiness</span><span id="stat-happiness"></span></div>
    </div>
</div>

<script>
    const pettingData = <?php echo json_encode($pets_payload, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    const state = pettingData.map((pet) => ({
        ...pet,
        hunger: pet.hunger,
        happiness: pet.happiness,
        intelligence: pet.intelligence,
        hpCurrent: pet.hpCurrent,
    }));

    const stage = document.getElementById('petting-stage');
    const sprite = document.getElementById('pet-sprite');
    const menuToggle = document.getElementById('menu-toggle');
    const menuPanel = document.getElementById('menu-panel');
    const petList = document.getElementById('pet-list');
    const itemToggle = document.getElementById('item-toggle');
    const itemPanel = document.getElementById('item-panel');
    const statName = document.getElementById('stat-name');
    const statHunger = document.getElementById('stat-hunger');
    const statHp = document.getElementById('stat-hp');
    const statIntelligence = document.getElementById('stat-intelligence');
    const statHappiness = document.getElementById('stat-happiness');
    const exitPayload = document.getElementById('exit-payload');

    let activeIndex = 0;
    let focused = false;
    let draggingItem = null;
    let dragGhost = null;
    let petting = false;
    let lastPointer = null;
    let petDistance = 0;
    let petSoundTimer = null;
    let idleTimer = null;
    let lastInteraction = Date.now();

    const sounds = {
        pet: new Audio('/assets/sfx/pet.wav'),
        boop: new Audio('/assets/sfx/boop.wav'),
        focus: new Audio('/assets/sfx/focus.wav'),
        unfocus: new Audio('/assets/sfx/unfocus.wav'),
        drop: new Audio('/assets/sfx/drop.wav'),
        lose: new Audio('/assets/sfx/lose.wav'),
    };

    function playSound(sound) {
        if (!sound) return;
        sound.currentTime = 0;
        sound.play().catch(() => {});
    }

    function idleSound() {
        const pet = state[activeIndex];
        if (!pet) return;
        const idleIndex = Math.floor(Math.random() * 3) + 1;
        const soundPath = `/assets/sfx/${pet.soundSlug}_${pet.gender}_idle${idleIndex}.wav`;
        const audio = new Audio(soundPath);
        audio.play().catch(() => {});
    }

    function scheduleIdle() {
        if (idleTimer) clearTimeout(idleTimer);
        const delay = 7000 + Math.random() * 5000;
        idleTimer = setTimeout(() => {
            const idleFor = Date.now() - lastInteraction;
            if (idleFor > 5000) {
                idleSound();
            }
            scheduleIdle();
        }, delay);
    }

    function updateStats() {
        const pet = state[activeIndex];
        statName.textContent = pet.name;
        statHunger.textContent = pet.hunger;
        statHp.textContent = `${pet.hpCurrent}/${pet.hpMax}`;
        statIntelligence.textContent = pet.intelligence;
        statHappiness.textContent = pet.happiness;
    }

    function setActivePet(index) {
        if (index === activeIndex) return;
        const previous = activeIndex;
        activeIndex = index;
        sprite.classList.remove('run-in');
        sprite.classList.add('run-out');
        sprite.addEventListener('animationend', () => {
            sprite.classList.remove('run-out');
            sprite.src = state[activeIndex].image;
            sprite.classList.add('run-in');
            updateStats();
            setTimeout(() => sprite.classList.remove('run-in'), 1000);
        }, { once: true });
        lastInteraction = Date.now();
    }

    function renderPetList() {
        petList.innerHTML = '';
        state.forEach((pet, index) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'pet-button';
            button.innerHTML = `<img src="${pet.image}" alt="${pet.name}"><div>${pet.name}</div>`;
            button.addEventListener('click', () => setActivePet(index));
            petList.appendChild(button);
        });
    }

    function popEmoji(emoji) {
        const rect = sprite.getBoundingClientRect();
        const pop = document.createElement('div');
        pop.className = 'emoji-pop';
        pop.textContent = emoji;
        pop.style.left = `${rect.left + rect.width / 2}px`;
        pop.style.top = `${rect.top}px`;
        document.body.appendChild(pop);
        setTimeout(() => pop.remove(), 1200);
    }

    function applyItemEffect(type) {
        const pet = state[activeIndex];
        if (!pet) return;
        if (type === 'food') {
            pet.hunger = Math.max(0, pet.hunger - 10);
            pet.happiness = Math.min(100, pet.happiness + 3);
            popEmoji('😋');
        }
        if (type === 'book') {
            pet.intelligence += 1;
            pet.happiness = Math.min(100, pet.happiness + 1);
            popEmoji('🧠');
        }
        if (type === 'potion') {
            pet.hpCurrent = Math.min(pet.hpMax, pet.hpCurrent + 5);
            pet.happiness = Math.min(100, pet.happiness + 2);
            popEmoji('❤️');
        }
        updateStats();
        playSound(sounds.drop);
        lastInteraction = Date.now();
    }

    function isOverSprite(x, y) {
        const rect = sprite.getBoundingClientRect();
        return x >= rect.left && x <= rect.right && y >= rect.top && y <= rect.bottom;
    }

    function startPetting(event) {
        petting = true;
        lastPointer = { x: event.clientX, y: event.clientY };
        petDistance = 0;
        if (!petSoundTimer) {
            petSoundTimer = setInterval(() => playSound(sounds.pet), 400);
        }
    }

    function stopPetting() {
        petting = false;
        lastPointer = null;
        petDistance = 0;
        if (petSoundTimer) {
            clearInterval(petSoundTimer);
            petSoundTimer = null;
        }
    }

    menuToggle.addEventListener('click', () => {
        menuPanel.classList.toggle('open');
    });

    itemToggle.addEventListener('click', () => {
        itemPanel.classList.toggle('open');
    });

    document.querySelectorAll('.item').forEach((item) => {
        item.addEventListener('mousedown', (event) => {
            draggingItem = item.dataset.item;
            dragGhost = item.cloneNode(true);
            dragGhost.classList.add('drag-ghost');
            document.body.appendChild(dragGhost);
            dragGhost.style.left = `${event.clientX}px`;
            dragGhost.style.top = `${event.clientY}px`;
        });
    });

    window.addEventListener('mousemove', (event) => {
        if (dragGhost) {
            dragGhost.style.left = `${event.clientX}px`;
            dragGhost.style.top = `${event.clientY}px`;
        }
        if (petting && focused && isOverSprite(event.clientX, event.clientY)) {
            const distance = Math.hypot(event.clientX - lastPointer.x, event.clientY - lastPointer.y);
            petDistance += distance;
            lastPointer = { x: event.clientX, y: event.clientY };
            if (petDistance >= 30) {
                state[activeIndex].happiness = Math.min(100, state[activeIndex].happiness + 1);
                updateStats();
                petDistance = 0;
                lastInteraction = Date.now();
            }
        }
    });

    window.addEventListener('mouseup', (event) => {
        if (dragGhost) {
            const usedOnPet = isOverSprite(event.clientX, event.clientY);
            dragGhost.remove();
            dragGhost = null;
            if (usedOnPet) {
                applyItemEffect(draggingItem);
            } else {
                playSound(sounds.lose);
            }
            draggingItem = null;
        }
        if (petting) {
            stopPetting();
        }
    });

    sprite.addEventListener('mousedown', (event) => {
        if (!focused) {
            focused = true;
            stage.classList.add('focused');
            playSound(sounds.focus);
        } else {
            startPetting(event);
        }
    });

    sprite.addEventListener('click', () => {
        if (focused && !petting) {
            state[activeIndex].happiness = Math.min(100, state[activeIndex].happiness + 2);
            updateStats();
            playSound(sounds.boop);
            lastInteraction = Date.now();
        }
    });

    stage.addEventListener('click', (event) => {
        if (event.target !== sprite && focused) {
            focused = false;
            stage.classList.remove('focused');
            playSound(sounds.unfocus);
            lastInteraction = Date.now();
        }
    });

    stage.addEventListener('mouseleave', stopPetting);

    document.getElementById('exit-form').addEventListener('submit', (event) => {
        exitPayload.value = JSON.stringify(state.map((pet) => ({
            id: pet.id,
            hunger: pet.hunger,
            happiness: pet.happiness,
            intelligence: pet.intelligence,
            hpCurrent: pet.hpCurrent,
        })));
    });

    sprite.src = state[0].image;
    renderPetList();
    updateStats();
    scheduleIdle();
</script>
</body>
</html>
