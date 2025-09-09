<?php
require_once __DIR__.'/../auth.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid = current_user()['id'];
    $data = json_decode(file_get_contents('php://input'), true) ?: [];
    $lines = isset($data['lines']) ? (int)$data['lines'] : 0;
    $reward = $lines * 5; // 5 coins per line
    if ($reward > 0) {
        q('UPDATE users SET coins = COALESCE(coins,0) + ? WHERE id = ?', [$reward, $uid]);
    }
    $balance = q('SELECT COALESCE(coins,0) FROM users WHERE id = ?', [$uid])->fetchColumn();
    header('Content-Type: application/json');
    echo json_encode(['coins' => (int)$balance]);
    exit;
}
?>
<link rel="stylesheet" href="assets/css/tetris.css">
<script defer src="assets/js/tetris.js"></script>
<h1>Tetris</h1>
<canvas id="tetris" width="200" height="400"></canvas>
    <p class="muted">Use W to rotate, A/D to move, S to drop.</p>
    <div id="score">Score: <span id="scoreVal">0</span></div>