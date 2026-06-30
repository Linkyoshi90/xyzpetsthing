<?php
require_login();
require_once __DIR__.'/../lib/input.php';
require_once __DIR__.'/../lib/temp_user.php';

$sortOptions = [
    'name_asc' => [
        'label' => 'Name (Asc)',
        'order' => 'i.item_name ASC, i.item_id ASC',
    ],
    'name_desc' => [
        'label' => 'Name (Desc)',
        'order' => 'i.item_name DESC, i.item_id DESC',
    ],
    'date_asc' => [
        'label' => 'Date (Asc)',
        'order' => 'ui.acquired_at ASC, i.item_name ASC, i.item_id ASC',
    ],
    'date_desc' => [
        'label' => 'Date (Desc)',
        'order' => 'ui.acquired_at DESC, i.item_name ASC, i.item_id ASC',
    ],
    'quant_asc' => [
        'label' => 'Quant (Asc)',
        'order' => 'ui.quantity ASC, i.item_name ASC, i.item_id ASC',
    ],
    'quant_desc' => [
        'label' => 'Quant (Desc)',
        'order' => 'ui.quantity DESC, i.item_name ASC, i.item_id ASC',
    ],
    'type_asc' => [
        'label' => 'Type Alphabetic (Asc)',
        'order' => "COALESCE(ic.category_name, '') ASC, i.item_name ASC, i.item_id ASC",
    ],
    'type_desc' => [
        'label' => 'Type Alphabetic (Desc)',
        'order' => "COALESCE(ic.category_name, '') DESC, i.item_name ASC, i.item_id ASC",
    ],
];

$sortKey = input_string($_GET['sort'] ?? 'name_asc', 32);
if (!isset($sortOptions[$sortKey])) {
    $sortKey = 'name_asc';
}

function inventory_compare_text($left, $right): int {
    return strnatcasecmp((string)$left, (string)$right);
}

function inventory_compare_number($left, $right): int {
    return (int)$left <=> (int)$right;
}

function inventory_timestamp_or_null($value): ?int {
    $timestamp = strtotime((string)$value);
    return $timestamp === false ? null : $timestamp;
}

function inventory_compare_date($left, $right, bool $descending = false): int {
    $leftTime = inventory_timestamp_or_null($left);
    $rightTime = inventory_timestamp_or_null($right);

    if ($leftTime === null && $rightTime === null) {
        return 0;
    }
    if ($leftTime === null) {
        return 1;
    }
    if ($rightTime === null) {
        return -1;
    }

    return $descending ? $rightTime <=> $leftTime : $leftTime <=> $rightTime;
}

function inventory_compare_original_order(array $left, array $right): int {
    return ((int)($left['_inventory_order'] ?? 0)) <=> ((int)($right['_inventory_order'] ?? 0));
}

function inventory_compare_rows(array $left, array $right, string $sortKey): int {
    switch ($sortKey) {
        case 'name_desc':
            return inventory_compare_text($right['item_name'] ?? '', $left['item_name'] ?? '')
                ?: inventory_compare_original_order($left, $right);
        case 'date_asc':
            return inventory_compare_date($left['acquired_at'] ?? '', $right['acquired_at'] ?? '')
                ?: inventory_compare_text($left['item_name'] ?? '', $right['item_name'] ?? '')
                ?: inventory_compare_original_order($left, $right);
        case 'date_desc':
            return inventory_compare_date($left['acquired_at'] ?? '', $right['acquired_at'] ?? '', true)
                ?: inventory_compare_text($left['item_name'] ?? '', $right['item_name'] ?? '')
                ?: inventory_compare_original_order($left, $right);
        case 'quant_asc':
            return inventory_compare_number($left['quantity'] ?? 0, $right['quantity'] ?? 0)
                ?: inventory_compare_text($left['item_name'] ?? '', $right['item_name'] ?? '')
                ?: inventory_compare_original_order($left, $right);
        case 'quant_desc':
            return inventory_compare_number($right['quantity'] ?? 0, $left['quantity'] ?? 0)
                ?: inventory_compare_text($left['item_name'] ?? '', $right['item_name'] ?? '')
                ?: inventory_compare_original_order($left, $right);
        case 'type_asc':
            return inventory_compare_text($left['category_name'] ?? '', $right['category_name'] ?? '')
                ?: inventory_compare_text($left['item_name'] ?? '', $right['item_name'] ?? '')
                ?: inventory_compare_original_order($left, $right);
        case 'type_desc':
            return inventory_compare_text($right['category_name'] ?? '', $left['category_name'] ?? '')
                ?: inventory_compare_text($left['item_name'] ?? '', $right['item_name'] ?? '')
                ?: inventory_compare_original_order($left, $right);
        case 'name_asc':
        default:
            return inventory_compare_text($left['item_name'] ?? '', $right['item_name'] ?? '')
                ?: inventory_compare_original_order($left, $right);
    }
}

function inventory_sort_rows(array $rows, string $sortKey): array {
    foreach ($rows as $index => &$row) {
        $row['_inventory_order'] = $index;
    }
    unset($row);

    usort($rows, static function (array $left, array $right) use ($sortKey): int {
        return inventory_compare_rows($left, $right, $sortKey);
    });

    return $rows;
}

$uid = current_user()['id'];
if ($uid === 0) {
    $rows = inventory_sort_rows(temp_user_inventory_rows(), $sortKey);
} else {
    $rows = q(
        "SELECT i.item_name, ic.category_name, ui.quantity, ui.acquired_at"
        ." FROM user_inventory ui"
        ." JOIN items i ON i.item_id = ui.item_id"
        ." LEFT JOIN item_categories ic ON ic.category_id = i.category_id"
        ." WHERE ui.user_id = ?"
        ." ORDER BY ".$sortOptions[$sortKey]['order'],
        [$uid]
    )->fetchAll(PDO::FETCH_ASSOC);
}
?>
<h1>Inventory</h1>

<form class="inventory-sort" method="get">
  <input type="hidden" name="pg" value="inventory">
  <label for="inventory-sort-select">Sort inventory</label>
  <div class="inventory-sort__controls">
    <select id="inventory-sort-select" name="sort">
      <?php foreach ($sortOptions as $optionKey => $option): ?>
        <option value="<?= htmlspecialchars($optionKey, ENT_QUOTES, 'UTF-8') ?>"<?= $optionKey === $sortKey ? ' selected' : '' ?>>
          <?= htmlspecialchars($option['label'], ENT_QUOTES, 'UTF-8') ?>
        </option>
      <?php endforeach; ?>
    </select>
    <button class="btn" type="submit">Sort</button>
  </div>
</form>

<div class="grid three">
<?php foreach($rows as $r): ?>
<?php
    $itemName = $r['item_name'];
    $imageBase = 'images/items/' . $itemName;
    $pngPath = $imageBase . '.png';
    $webpPath = $imageBase . '.webp';
    $pngFile = __DIR__ . '/../' . $pngPath;
    $webpFile = __DIR__ . '/../' . $webpPath;
    $imageSrc = $pngPath;
    if (!file_exists($pngFile) && file_exists($webpFile)) {
        $imageSrc = $webpPath;
    }
  ?>
  <div class="card glass">
    <img class="icon" src="<?= htmlspecialchars($imageSrc) ?>">
    <h3><?= htmlspecialchars($r['item_name']) ?></h3>
    <p class="muted"><?= htmlspecialchars($r['category_name'] ?? '') ?>  x<?= (int)$r['quantity'] ?></p>
    <!-- future: Use item on a pet -->
  </div>
<?php endforeach; ?>
<?php if(!$rows): ?><p>Your bag is empty.</p><?php endif; ?>
</div>
