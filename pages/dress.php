<?php
require_login();
require_once __DIR__.'/../lib/input.php';
require_once __DIR__.'/../lib/pets.php';
require_once __DIR__.'/../lib/shops.php';

$uid = current_user()['id'];

function load_cosmetic_inventory(int $user_id): array {
    $rows = q(
        "SELECT ui.item_id, i.item_name\n"
        ."FROM user_inventory ui\n"
        ."JOIN items i ON i.item_id = ui.item_id\n"
        ."LEFT JOIN item_categories ic ON ic.category_id = i.category_id\n"
        ."WHERE ui.user_id = ? AND ic.category_name = 'Wearable'\n"
        ."ORDER BY i.item_name",
        [$user_id]
    )->fetchAll(PDO::FETCH_ASSOC);

    $items = [];
    foreach ($rows as $row) {
        $imageFile = shop_find_item_image($row['item_name']);
        $items[] = [
            'item_id' => (int) $row['item_id'],
            'name' => $row['item_name'],
            'image' => 'images/items/'.rawurlencode($imageFile),
        ];
    }

    return $items;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $payload = json_decode(file_get_contents('php://input'), true);
    if (!is_array($payload)) {
        $payload = $_POST;
    }

    $action = input_string($payload['action'] ?? '', 20);
    if ($action !== 'save') {
        echo json_encode(['ok' => false, 'message' => 'Invalid action.']);
        exit;
    }

    $pet_id = input_int($payload['pet_id'] ?? 0, 1);
    $pet = get_owned_pet($uid, $pet_id);
    if (!$pet) {
        echo json_encode(['ok' => false, 'message' => 'That pet is not available.']);
        exit;
    }

    $items = $payload['items'] ?? [];
    if (!is_array($items)) {
        $items = [];
    }

    $inventory = load_cosmetic_inventory($uid);
    $allowed = [];
    foreach ($inventory as $item) {
        $allowed[$item['item_id']] = true;
    }

    $to_save = [];
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }
        $item_id = input_int($item['item_id'] ?? 0, 1);
        if (!$item_id || empty($allowed[$item_id])) {
            continue;
        }
        $xcoord = input_int($item['x'] ?? 0, 0, 5000);
        $ycoord = input_int($item['y'] ?? 0, 0, 5000);
        $size = input_int($item['size'] ?? 100, 10, 300);
        $to_save[] = [
            'item_id' => $item_id,
            'x' => $xcoord,
            'y' => $ycoord,
            'size' => $size,
        ];
        if (count($to_save) >= 15) {
            break;
        }
    }

    $pdo = db();
    if (!$pdo) {
        echo json_encode(['ok' => false, 'message' => 'Database unavailable.']);
        exit;
    }

    try {
        $pdo->beginTransaction();
        q("DELETE FROM pet_cosmetics WHERE pet_instance_id = ?", [$pet_id]);
        if ($to_save) {
            $stmt = $pdo->prepare(
                "INSERT INTO pet_cosmetics (pet_instance_id, item_id, xcoord, ycoord, size)\n"
                ."VALUES (?, ?, ?, ?, ?)"
            );
            foreach ($to_save as $item) {
                $stmt->execute([$pet_id, $item['item_id'], $item['x'], $item['y'], $item['size']]);
            }
        }
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        echo json_encode(['ok' => false, 'message' => 'Unable to save cosmetics.']);
        exit;
    }

    echo json_encode(['ok' => true]);
    exit;
}

$pet_id = input_int($_GET['id'] ?? 0, 1);
$pet = get_owned_pet($uid, $pet_id);
if (!$pet) {
    echo '<p>That pet is not available.</p>';
    return;
}

$pet_name = $pet['nickname'] ?: $pet['species_name'];
$pet_image = pet_image_url($pet['species_name'], $pet['color_name']);
$inventory = load_cosmetic_inventory($uid);
$saved_cosmetics = get_pet_cosmetics($pet_id);
?>
<style>
  .dress-up {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 260px;
    gap: 20px;
    align-items: start;
  }

  .dress-canvas-wrap {
    background: rgba(255, 255, 255, 0.7);
    border-radius: 16px;
    padding: 16px;
  }

  #dress-canvas {
    width: 100%;
    max-width: 600px;
    height: auto;
    border-radius: 12px;
    background: #f6f6f6;
    cursor: grab;
  }

  .cosmetic-panel {
    background: rgba(255, 255, 255, 0.7);
    border-radius: 16px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  .cosmetic-items {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
    max-height: 420px;
    overflow-y: auto;
  }

  .cosmetic-item {
    background: white;
    border: 1px solid rgba(0, 0, 0, 0.1);
    border-radius: 12px;
    padding: 8px;
    text-align: center;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    gap: 6px;
    align-items: center;
  }

  .cosmetic-item img {
    width: 64px;
    height: 64px;
    object-fit: contain;
  }

  .selected-item {
    font-weight: 600;
  }

  .dress-actions {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }
</style>

<h1>Dress up <?= htmlspecialchars($pet_name) ?></h1>
<p>Drag items onto the canvas, then move them around. Select an item on the canvas to resize it.</p>
<div class="dress-up">
  <div class="dress-canvas-wrap">
    <canvas id="dress-canvas" width="500" height="500" aria-label="Dress up canvas"></canvas>
  </div>
  <aside class="cosmetic-panel">
    <div>
      <h2>Cosmetics</h2>
      <?php if ($inventory): ?>
        <div class="cosmetic-items">
          <?php foreach ($inventory as $item): ?>
            <button class="cosmetic-item" type="button" data-item-id="<?= (int) $item['item_id'] ?>" data-item-name="<?= htmlspecialchars($item['name']) ?>" data-item-image="<?= htmlspecialchars($item['image']) ?>">
              <img src="<?= htmlspecialchars($item['image']) ?>" alt="">
              <span><?= htmlspecialchars($item['name']) ?></span>
            </button>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p>You do not have any cosmetic items yet.</p>
      <?php endif; ?>
    </div>
    <div>
      <label for="size-control">Selected size (%):</label>
      <input id="size-control" type="range" min="10" max="200" value="100">
      <div class="selected-item" id="selected-item">No item selected</div>
    </div>
    <div class="dress-actions">
      <button class="btn" type="button" id="save-dress">Save &amp; exit</button>
      <a class="btn" href="?pg=pet&id=<?= (int) $pet_id ?>">Back without saving</a>
    </div>
  </aside>
</div>

<script>
(() => {
  const petImageUrl = <?= json_encode($pet_image, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
  const savedCosmetics = <?= json_encode($saved_cosmetics, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
  const maxCosmetics = 15;

  const canvas = document.getElementById('dress-canvas');
  const ctx = canvas.getContext('2d');
  const sizeControl = document.getElementById('size-control');
  const selectedItemLabel = document.getElementById('selected-item');
  const state = {
    items: [],
    draggingIndex: null,
    dragOffsetX: 0,
    dragOffsetY: 0,
    selectedIndex: null,
  };

  const petImage = new Image();
  petImage.src = petImageUrl;
  petImage.onload = () => {
    canvas.width = petImage.width;
    canvas.height = petImage.height;
    render();
  };

  function loadItemImage(item) {
    const img = new Image();
    img.src = item.image;
    item.img = img;
    img.onload = () => render();
  }

  function render() {
    if (!ctx) return;
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    if (petImage.complete) {
      ctx.drawImage(petImage, 0, 0, canvas.width, canvas.height);
    }
    state.items.forEach(item => {
      if (!item.img || !item.img.complete) return;
      const scale = item.size / 100;
      const width = item.img.width * scale;
      const height = item.img.height * scale;
      ctx.drawImage(item.img, item.x, item.y, width, height);
    });
  }

  function setSelected(index) {
    state.selectedIndex = index;
    if (index === null || index === undefined) {
      selectedItemLabel.textContent = 'No item selected';
      sizeControl.value = 100;
      return;
    }
    const item = state.items[index];
    selectedItemLabel.textContent = item?.name ? `Selected: ${item.name}` : 'Selected item';
    if (item) {
      sizeControl.value = item.size;
    }
  }

  function getMousePos(evt) {
    const rect = canvas.getBoundingClientRect();
    return {
      x: (evt.clientX - rect.left) * (canvas.width / rect.width),
      y: (evt.clientY - rect.top) * (canvas.height / rect.height),
    };
  }

  function findItemAt(x, y) {
    for (let i = state.items.length - 1; i >= 0; i -= 1) {
      const item = state.items[i];
      if (!item.img || !item.img.complete) continue;
      const scale = item.size / 100;
      const width = item.img.width * scale;
      const height = item.img.height * scale;
      if (x >= item.x && x <= item.x + width && y >= item.y && y <= item.y + height) {
        return i;
      }
    }
    return null;
  }

  canvas.addEventListener('mousedown', event => {
    const pos = getMousePos(event);
    const index = findItemAt(pos.x, pos.y);
    if (index !== null) {
      const item = state.items[index];
      state.draggingIndex = index;
      state.dragOffsetX = pos.x - item.x;
      state.dragOffsetY = pos.y - item.y;
      setSelected(index);
    } else {
      setSelected(null);
    }
  });

  canvas.addEventListener('mousemove', event => {
    if (state.draggingIndex === null) return;
    const pos = getMousePos(event);
    const item = state.items[state.draggingIndex];
    const scale = item.size / 100;
    const width = item.img.width * scale;
    const height = item.img.height * scale;
    item.x = Math.max(0, Math.min(canvas.width - width, pos.x - state.dragOffsetX));
    item.y = Math.max(0, Math.min(canvas.height - height, pos.y - state.dragOffsetY));
    render();
  });

  canvas.addEventListener('mouseup', () => {
    state.draggingIndex = null;
  });

  canvas.addEventListener('mouseleave', () => {
    state.draggingIndex = null;
  });

  sizeControl.addEventListener('input', () => {
    if (state.selectedIndex === null) return;
    const item = state.items[state.selectedIndex];
    item.size = parseInt(sizeControl.value, 10);
    render();
  });

  document.querySelectorAll('.cosmetic-item').forEach(btn => {
    btn.addEventListener('click', () => {
      if (state.items.length >= maxCosmetics) {
        alert('You can only add up to 15 cosmetics.');
        return;
      }
      const item = {
        item_id: parseInt(btn.dataset.itemId, 10),
        name: btn.dataset.itemName,
        image: btn.dataset.itemImage,
        x: 0,
        y: 0,
        size: 100,
        img: null,
      };
      loadItemImage(item);
      const tryPlace = () => {
        if (!item.img || !item.img.complete || !canvas.width) {
          requestAnimationFrame(tryPlace);
          return;
        }
        const scale = item.size / 100;
        const width = item.img.width * scale;
        const height = item.img.height * scale;
        item.x = Math.max(0, (canvas.width - width) / 2);
        item.y = Math.max(0, (canvas.height - height) / 2);
        state.items.push(item);
        setSelected(state.items.length - 1);
        render();
      };
      tryPlace();
    });
  });

  savedCosmetics.forEach(item => {
    const loaded = {
      item_id: item.item_id,
      name: item.name,
      image: item.image,
      x: item.x,
      y: item.y,
      size: item.size || 100,
      img: null,
    };
    loadItemImage(loaded);
    state.items.push(loaded);
  });

  document.getElementById('save-dress').addEventListener('click', async () => {
    const payload = {
      action: 'save',
      pet_id: <?= (int) $pet_id ?>,
      items: state.items.map(item => ({
        item_id: item.item_id,
        x: Math.round(item.x),
        y: Math.round(item.y),
        size: Math.round(item.size),
      })),
    };

    try {
      const response = await fetch(window.location.href, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      const data = await response.json();
      if (!data.ok) {
        alert(data.message || 'Unable to save right now.');
        return;
      }
      window.location.href = `?pg=pet&id=<?= (int) $pet_id ?>`;
    } catch (error) {
      alert('Unable to save right now.');
    }
  });
})();
</script>
