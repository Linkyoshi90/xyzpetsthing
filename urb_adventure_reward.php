<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/lib/temp_user.php';
require_once __DIR__ . '/lib/input.php';

header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$user = current_user();
if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$input = is_array($input) ? $input : [];
$itemId = input_int($input['item_id'] ?? 0, 1);
$quantity = 1;

if ($itemId !== 1) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid item request']);
    exit;
}

if ((int) $user['id'] === 0) {
    temp_user_add_inventory_item($itemId, $quantity);
    echo json_encode(['message' => 'You receive a curious keepsake from the farm.']);
    exit;
}

$itemRow = q(
    'SELECT item_name FROM items WHERE item_id = ? LIMIT 1',
    [$itemId]
)->fetch(PDO::FETCH_ASSOC);
$itemName = $itemRow['item_name'] ?? 'Farm keepsake';

q(
    'INSERT INTO user_inventory (user_id, item_id, quantity) VALUES (?, ?, ?)
     ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)',
    [$user['id'], $itemId, $quantity]
);

echo json_encode([
    'message' => "You receive {$itemName} from your night wander."
]);
