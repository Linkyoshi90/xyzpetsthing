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
  <div class="card glass">
    <h3>Run n Gunner</h3>
    <a class="btn" href="?pg=runngunner">Play</a>
  </div>
  <div class="card glass">
    <h3>Wanted Alive</h3>
    <a class="btn" href="?pg=wanted-alive">Play</a>
  </div>
  <div class="card glass">
    <h3>Blackjack</h3>
    <a class="btn" href="?pg=blackjack">Play</a>
  </div>
  <div class="card glass">
    <h3>Wheel of Fate</h3>
    <a class="btn" href="?pg=wheel-of-fate">Spin</a>
  </div>
</div>