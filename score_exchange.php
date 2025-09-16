<?php
require_once __DIR__.'/auth.php';
require_login();
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true) ?: [];
$game = $data['game'] ?? '';
$score = isset($data['score']) ? (int)$data['score'] : 0;
$uid = current_user()['id'];

$rates = [
    'gardeninvaderz' => 0.5,
    'fruitstack' => 1.2,
    'runngunner' => 0.8,
    'wantedalive' => 0.75,
];

if (!isset($rates[$game]) || $score <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid game or score']);
    exit;
}

$todayCount = q(
    "SELECT COUNT(*) FROM currency_ledger WHERE user_id = ? AND reason LIKE 'score_exchange_%' AND DATE(created_at) = CURDATE()",
    [$uid]
)->fetchColumn();
if ($todayCount >= 3) {
    http_response_code(400);
    echo json_encode(['error' => 'Daily exchange limit reached']);
    exit;
}

$amount = round($score * $rates[$game], 2);
q("INSERT INTO user_balances (user_id, currency_id, balance) VALUES (?,1,?) ON DUPLICATE KEY UPDATE balance = balance + VALUES(balance)",
  [$uid, $amount]);
q(
    "INSERT INTO currency_ledger (user_id, currency_id, amount_delta, reason, metadata) VALUES (?,?,?,?,?)",
    [$uid, 1, $amount, 'score_exchange_'.$game, json_encode(['score' => $score])]
);
$balance = q("SELECT balance FROM user_balances WHERE user_id = ? AND currency_id = 1", [$uid])->fetchColumn();
$_SESSION['user']['cash'] = (int)$balance;
if (!isset($_SESSION['exchange_counter_date']) || $_SESSION['exchange_counter_date'] !== date('Y-m-d')) {
    $_SESSION['exchange_counter'] = 0;
    $_SESSION['exchange_counter_date'] = date('Y-m-d');
}
$_SESSION['exchange_counter'] = ($_SESSION['exchange_counter'] ?? 0) + 1;

echo json_encode(['cash' => (int)$balance]);