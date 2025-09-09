<?php
require_once __DIR__.'/auth.php';
require_login();
$uid = current_user()['id'];
$data = json_decode(file_get_contents('php://input'), true) ?: [];
$lines = isset($data['lines']) ? (int)$data['lines'] : 0;
$reward = $lines * 5; // 5 coins per line
if ($reward > 0) {
  q('UPDATE users SET coins = COALESCE(coins,0) + ? WHERE id = ?', [$reward, $uid]);
}
$balance = q('SELECT COALESCE(coins,0) FROM users WHERE id = ?', [$uid])->fetchColumn();
$_SESSION['user']['cash'] = (int)$balance;
header('Content-Type: application/json');
echo json_encode(['coins' => (int)$balance]);
?>