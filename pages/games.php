<?php
require_login();
if (!isset($_SESSION['exchange_counter_date']) || $_SESSION['exchange_counter_date'] !== date('Y-m-d')) {
    $_SESSION['exchange_counter'] = 0;
    $_SESSION['exchange_counter_date'] = date('Y-m-d');
}
$exchanges = $_SESSION['exchange_counter'] ?? 0;
?>
<h1>Games</h1>
<p class="muted">Today's exchanges: <?php echo $exchanges; ?>/3</p>
<div class="grid two">
  <div class="card glass">
    <h3>Fruitstack</h3>
    <a class="btn" href="?pg=fruitstack">Play</a>
  </div>
  <div class="card glass">
    <h3>Garden Invaderz</h3>
    <a class="btn" href="?pg=garden-invaderz">Play</a>
  </div>
</div>