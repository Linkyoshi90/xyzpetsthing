<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../lib/input.php';

require_login();

$uid = (int)(current_user()['id'] ?? 0);

function petting2_image_path(string $species_name, ?string $color_name): string {
    $species_slug = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $species_name));
    $color_slug = $color_name ? strtolower(preg_replace('/[^a-z0-9]+/i', '_', $color_name)) : 'blue';
    $path = "images/{$species_slug}_f_{$color_slug}.webp";
    if (!file_exists(__DIR__ . '/../' . $path)) {
        return 'images/tengu_f_blue.png';
    }
    return $path;
}

function petting2_clamp_int(int $value, int $min, int $max): int {
    return max($min, min($max, $value));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = json_decode(file_get_contents('php://input'), true);
    $payload = is_array($payload) ? $payload : [];
    $action = input_string($payload['action'] ?? ($_POST['action'] ?? ''), 20);

    if ($action === 'save_stats') {
        header('Content-Type: application/json');
        $pets_payload = $payload['pets'] ?? [];
        $updated = 0;

        foreach ($pets_payload as $state) {
            $pet_id = input_int($state['id'] ?? 0, 1);
            if ($pet_id <= 0) {
                continue;
            }

            $row = q(
                "SELECT hp_current, hp_max FROM pet_instances WHERE pet_instance_id = ? AND owner_user_id = ?",
                [$pet_id, $uid]
            )->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                continue;
            }

            $hp_max = isset($row['hp_max']) ? (int)$row['hp_max'] : (int)($row['hp_current'] ?? 0);
            $hp_max = max(1, $hp_max);

            $hunger = petting2_clamp_int(input_int($state['hunger'] ?? 0), 0, 100);
            $happiness = petting2_clamp_int(input_int($state['happiness'] ?? 0), 0, 100);
            $intelligence = max(0, input_int($state['intelligence'] ?? 0));
            $hp_current = petting2_clamp_int(input_int($state['hp'] ?? 0), 0, $hp_max);

            q(
                "UPDATE pet_instances"
                . "   SET hunger = ?, happiness = ?, intelligence = ?, hp_current = ?"
                . " WHERE pet_instance_id = ? AND owner_user_id = ?",
                [$hunger, $happiness, $intelligence, $hp_current, $pet_id, $uid]
            );
            $updated++;
        }

        echo json_encode(['ok' => true, 'updated' => $updated]);
        exit;
    }
}

$pets = q(
    "SELECT pi.pet_instance_id,
            pi.nickname,
            pi.gender,
            pi.hunger,
            pi.happiness,
            pi.intelligence,
            pi.hp_current,
            pi.hp_max,
            ps.species_name,
            pc.color_name
       FROM pet_instances pi
       JOIN pet_species ps ON ps.species_id = pi.species_id
       LEFT JOIN pet_colors pc ON pc.color_id = pi.color_id
      WHERE pi.owner_user_id = ? AND COALESCE(pi.inactive, 0) = 0",
    [$uid]
)->fetchAll(PDO::FETCH_ASSOC);

$pet_payload = [];
foreach ($pets as $pet) {
    $name = $pet['nickname'] ?: $pet['species_name'];
    $species_slug = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $pet['species_name']));
    $pet_payload[] = [
        'id' => (int)$pet['pet_instance_id'],
        'name' => $name,
        'species' => $pet['species_name'],
        'slug' => $species_slug,
        'gender' => $pet['gender'] ?: 'f',
        'image' => petting2_image_path($pet['species_name'], $pet['color_name'] ?? null),
        'hunger' => (int)($pet['hunger'] ?? 0),
        'happiness' => (int)($pet['happiness'] ?? 0),
        'intelligence' => (int)($pet['intelligence'] ?? 0),
        'hp' => (int)($pet['hp_current'] ?? 0),
        'hpMax' => (int)($pet['hp_max'] ?? $pet['hp_current'] ?? 1),
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Petting Grove</title>
  <style>
    :root {
      color-scheme: dark;
      font-family: "Trebuchet MS", "Segoe UI", sans-serif;
    }

    body {
      margin: 0;
      background: radial-gradient(circle at top, #2f4060 0%, #1b2233 55%, #0f121d 100%);
      color: #f4f6ff;
      min-height: 100vh;
      overflow: hidden;
    }

    .stage {
      position: relative;
      height: 100vh;
      width: 100vw;
      display: flex;
      flex-direction: column;
    }

    .top-bar {
      position: absolute;
      top: 16px;
      left: 16px;
      right: 16px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      z-index: 10;
      pointer-events: none;
    }

    .top-bar button {
      pointer-events: auto;
      border: none;
      background: rgba(30, 40, 60, 0.75);
      color: #f4f6ff;
      padding: 10px 14px;
      border-radius: 10px;
      font-weight: 600;
      cursor: pointer;
      transition: transform 0.2s ease, background 0.2s ease;
    }

    .top-bar button:hover {
      background: rgba(62, 86, 120, 0.9);
      transform: translateY(-1px);
    }

    .menu-toggle {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .menu-panel {
      position: absolute;
      top: 64px;
      left: 16px;
      width: 240px;
      max-height: 60vh;
      background: rgba(20, 26, 40, 0.9);
      border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: 16px;
      padding: 16px;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.35);
      opacity: 0;
      transform: translateY(-10px);
      pointer-events: none;
      transition: opacity 0.2s ease, transform 0.2s ease;
      z-index: 8;
    }

    .menu-panel.open {
      opacity: 1;
      transform: translateY(0);
      pointer-events: auto;
    }

    .menu-panel h3 {
      margin: 0 0 10px;
      font-size: 1rem;
    }

    .creature-list {
      list-style: none;
      margin: 0;
      padding: 0;
      max-height: 45vh;
      overflow-y: auto;
      display: grid;
      gap: 8px;
    }

    .creature-list button {
      width: 100%;
      border: none;
      border-radius: 10px;
      padding: 8px 10px;
      text-align: left;
      color: #f4f6ff;
      background: rgba(44, 58, 86, 0.8);
      cursor: pointer;
      transition: background 0.2s ease;
    }

    .creature-list button.active {
      background: rgba(105, 164, 255, 0.9);
      color: #0d1320;
      font-weight: 700;
    }

    .creature-list button:hover {
      background: rgba(88, 118, 170, 0.9);
    }

    .creature-zone {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
    }

    .creature-card {
      position: relative;
      width: min(60vw, 480px);
      aspect-ratio: 1 / 1;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgba(255, 255, 255, 0.06);
      border-radius: 24px;
      border: 1px solid rgba(255, 255, 255, 0.2);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.35);
      transition: transform 0.35s ease, box-shadow 0.35s ease;
      cursor: pointer;
      overflow: hidden;
    }

    .creature-card.focused {
      transform: scale(1.15);
      box-shadow: 0 28px 60px rgba(0, 0, 0, 0.5);
    }

    .creature-card.exit {
      animation: dash-out 0.8s forwards;
    }

    .creature-card.enter {
      animation: dash-in 0.8s forwards;
    }

    @keyframes dash-out {
      0% { transform: translateX(0) scale(1); opacity: 1; }
      100% { transform: translateX(-120%) scale(0.8); opacity: 0; }
    }

    @keyframes dash-in {
      0% { transform: translateX(120%) scale(0.8); opacity: 0; }
      100% { transform: translateX(0) scale(1); opacity: 1; }
    }

    .creature-image {
      width: 100%;
      height: 100%;
      object-fit: contain;
      filter: drop-shadow(0 12px 20px rgba(0, 0, 0, 0.5));
      pointer-events: none;
    }

    .nameplate {
      position: absolute;
      bottom: 16px;
      left: 50%;
      transform: translateX(-50%);
      background: rgba(0, 0, 0, 0.6);
      padding: 6px 14px;
      border-radius: 999px;
      font-weight: 600;
      font-size: 0.95rem;
    }

    .item-dock {
      position: absolute;
      bottom: 20px;
      left: 20px;
      display: flex;
      flex-direction: column;
      gap: 8px;
      background: rgba(15, 20, 30, 0.7);
      padding: 12px;
      border-radius: 16px;
      border: 1px solid rgba(255, 255, 255, 0.1);
      z-index: 9;
    }

    .item {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 8px 12px;
      border-radius: 12px;
      background: rgba(70, 90, 120, 0.75);
      cursor: grab;
      user-select: none;
      font-weight: 600;
    }

    .item:active {
      cursor: grabbing;
    }

    .emoji-pop {
      position: absolute;
      font-size: 2rem;
      animation: float-up 1.2s ease forwards;
      pointer-events: none;
    }

    @keyframes float-up {
      0% { opacity: 0; transform: translate(-50%, 0) scale(0.8); }
      15% { opacity: 1; }
      100% { opacity: 0; transform: translate(-50%, -80px) scale(1.2); }
    }

    .hint {
      position: absolute;
      bottom: 20px;
      right: 20px;
      background: rgba(0, 0, 0, 0.4);
      padding: 10px 16px;
      border-radius: 12px;
      font-size: 0.85rem;
      max-width: 260px;
    }

    .empty-state {
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
      font-size: 1.1rem;
    }
  </style>
</head>
<body>
<?php if (!$pet_payload): ?>
  <div class="empty-state">No creatures yet. <a href="/index.php" style="color:#9fc2ff;">Return home</a>.</div>
<?php else: ?>
  <div class="stage" id="stage">
    <div class="top-bar">
      <button class="menu-toggle" id="menuToggle">☰ Creatures</button>
      <button id="exitButton">Exit</button>
    </div>

    <aside class="menu-panel" id="menuPanel">
      <h3>Your creatures</h3>
      <ul class="creature-list" id="creatureList"></ul>
    </aside>

    <div class="creature-zone" id="creatureZone">
      <div class="creature-card" id="creatureCard">
        <img class="creature-image" id="creatureImage" src="" alt="">
        <div class="nameplate" id="creatureName"></div>
      </div>
    </div>

    <div class="item-dock">
      <div class="item" draggable="true" data-item="food">🍎 Food</div>
      <div class="item" draggable="true" data-item="book">📘 Book</div>
      <div class="item" draggable="true" data-item="potion">🧪 Potion</div>
    </div>

    <div class="hint">Click your creature to focus. Drag items to interact. Pet while focused to raise happiness.</div>
  </div>

  <audio id="soundPet" src="/assets/sfx/pet.wav"></audio>
  <audio id="soundBoop" src="/assets/sfx/boop.wav"></audio>
  <audio id="soundFocus" src="/assets/sfx/focus.wav"></audio>
  <audio id="soundUnfocus" src="/assets/sfx/unfocus.wav"></audio>
  <audio id="soundDrop" src="/assets/sfx/drop.wav"></audio>
  <audio id="soundLose" src="/assets/sfx/lose.wav"></audio>

  <script>
    const pets = <?php echo json_encode($pet_payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
    const creatureList = document.getElementById('creatureList');
    const creatureCard = document.getElementById('creatureCard');
    const creatureImage = document.getElementById('creatureImage');
    const creatureName = document.getElementById('creatureName');
    const creatureZone = document.getElementById('creatureZone');
    const menuToggle = document.getElementById('menuToggle');
    const menuPanel = document.getElementById('menuPanel');
    const exitButton = document.getElementById('exitButton');
    const soundPet = document.getElementById('soundPet');
    const soundBoop = document.getElementById('soundBoop');
    const soundFocus = document.getElementById('soundFocus');
    const soundUnfocus = document.getElementById('soundUnfocus');
    const soundDrop = document.getElementById('soundDrop');
    const soundLose = document.getElementById('soundLose');

    let activeIndex = 0;
    let focused = false;
    let petting = false;
    let pettingInterval = null;
    let idleTimer = null;

    const petStates = pets.map((pet) => ({
      id: pet.id,
      hunger: pet.hunger,
      happiness: pet.happiness,
      intelligence: pet.intelligence,
      hp: pet.hp,
      hpMax: pet.hpMax || 1,
    }));

    function clamp(value, min, max) {
      return Math.max(min, Math.min(max, value));
    }

    function playSound(audio) {
      if (!audio) return;
      audio.currentTime = 0;
      audio.play().catch(() => {});
    }

    function showEmoji(emoji) {
      const marker = document.createElement('div');
      marker.className = 'emoji-pop';
      marker.textContent = emoji;
      const rect = creatureCard.getBoundingClientRect();
      marker.style.left = `${rect.left + rect.width / 2}px`;
      marker.style.top = `${rect.top + rect.height / 2}px`;
      document.body.appendChild(marker);
      setTimeout(() => marker.remove(), 1200);
    }

    function setActivePet(index, animate = true) {
      const pet = pets[index];
      if (!pet) return;
      activeIndex = index;
      creatureName.textContent = pet.name;
      creatureImage.src = pet.image;
      creatureImage.alt = pet.name;
      creatureImage.onerror = () => {
        creatureImage.src = 'images/tengu_f_blue.png';
      };

      document.querySelectorAll('.creature-list button').forEach((btn, idx) => {
        btn.classList.toggle('active', idx === index);
      });

      if (animate) {
        creatureCard.classList.remove('enter');
        creatureCard.classList.add('exit');
        focused = false;
        creatureCard.classList.remove('focused');
        setTimeout(() => {
          creatureCard.classList.remove('exit');
          creatureCard.classList.add('enter');
          setTimeout(() => creatureCard.classList.remove('enter'), 800);
        }, 300);
      }

      restartIdleTimer();
    }

    function buildCreatureList() {
      creatureList.innerHTML = '';
      pets.forEach((pet, index) => {
        const button = document.createElement('button');
        button.textContent = pet.name;
        button.addEventListener('click', () => {
          if (index !== activeIndex) {
            setActivePet(index, true);
          }
        });
        creatureList.appendChild(button);
      });
    }

    function focusCreature() {
      if (focused) return;
      focused = true;
      creatureCard.classList.add('focused');
      playSound(soundFocus);
      restartIdleTimer();
    }

    function unfocusCreature() {
      if (!focused) return;
      focused = false;
      creatureCard.classList.remove('focused');
      playSound(soundUnfocus);
      stopPetting();
      restartIdleTimer();
    }

    function startPetting() {
      if (!focused) return;
      petting = true;
      if (!pettingInterval) {
        pettingInterval = setInterval(() => {
          if (!petting || !focused) return;
          const state = petStates[activeIndex];
          state.happiness = clamp(state.happiness + 1, 0, 100);
          playSound(soundPet);
          showEmoji('💖');
        }, 600);
      }
    }

    function stopPetting() {
      petting = false;
      if (pettingInterval) {
        clearInterval(pettingInterval);
        pettingInterval = null;
      }
    }

    function boopCreature() {
      if (!focused) return;
      const state = petStates[activeIndex];
      state.happiness = clamp(state.happiness + 1, 0, 100);
      playSound(soundBoop);
      showEmoji('✨');
      restartIdleTimer();
    }

    function restartIdleTimer() {
      if (idleTimer) {
        clearTimeout(idleTimer);
      }
      idleTimer = setTimeout(() => {
        if (petting || focused) {
          restartIdleTimer();
          return;
        }
        const pet = pets[activeIndex];
        const roll = Math.floor(Math.random() * 3) + 1;
        const idleAudio = new Audio(`/assets/sfx/${pet.slug}_${pet.gender}_idle${roll}.wav`);
        idleAudio.play().catch(() => {});
        restartIdleTimer();
      }, 7000 + Math.random() * 5000);
    }

    function handleItemDrop(itemType, droppedOnPet) {
      if (!droppedOnPet) {
        playSound(soundLose);
        return;
      }

      const state = petStates[activeIndex];
      if (itemType === 'food') {
        state.hunger = clamp(state.hunger - 10, 0, 100);
        showEmoji('😋');
      } else if (itemType === 'book') {
        state.intelligence += 1;
        showEmoji('🧠');
      } else if (itemType === 'potion') {
        state.hp = clamp(state.hp + 5, 0, state.hpMax || 1);
        showEmoji('❤️');
      }
      playSound(soundDrop);
      restartIdleTimer();
    }

    function handleExit() {
      const payload = {
        action: 'save_stats',
        pets: petStates.map((state) => ({
          id: state.id,
          hunger: state.hunger,
          happiness: state.happiness,
          intelligence: state.intelligence,
          hp: state.hp,
        })),
      };

      fetch('petting2.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(payload),
      }).finally(() => {
        window.location.href = '/index.php';
      });
    }

    menuToggle.addEventListener('click', () => {
      menuPanel.classList.toggle('open');
    });

    exitButton.addEventListener('click', handleExit);

    creatureCard.addEventListener('click', (event) => {
      if (!focused) {
        focusCreature();
      } else {
        boopCreature();
      }
      event.stopPropagation();
    });

    creatureCard.addEventListener('pointerdown', (event) => {
      if (!focused) return;
      creatureCard.setPointerCapture(event.pointerId);
      startPetting();
    });

    creatureCard.addEventListener('pointermove', () => {
      if (petting) {
        startPetting();
      }
    });

    creatureCard.addEventListener('pointerup', stopPetting);
    creatureCard.addEventListener('pointerleave', stopPetting);

    document.addEventListener('click', (event) => {
      if (event.target.closest('.creature-card') || event.target.closest('.menu-panel') || event.target.closest('.menu-toggle')) {
        return;
      }
      unfocusCreature();
    });

    document.querySelectorAll('.item').forEach((item) => {
      item.addEventListener('dragstart', (event) => {
        event.dataTransfer.setData('text/plain', item.dataset.item);
      });
    });

    creatureZone.addEventListener('dragover', (event) => {
      event.preventDefault();
    });

    creatureZone.addEventListener('drop', (event) => {
      event.preventDefault();
      const itemType = event.dataTransfer.getData('text/plain');
      const element = document.elementFromPoint(event.clientX, event.clientY);
      const droppedOnPet = element && element.closest('.creature-card');
      handleItemDrop(itemType, !!droppedOnPet);
    });

    buildCreatureList();
    setActivePet(0, false);
    restartIdleTimer();
  </script>
<?php endif; ?>
</body>
</html>
