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
    <h3>Harmonflap</h3>
    <p class="muted">Slip a creature between scrolling tide pipes.</p>
    <a class="btn" href="?pg=harmonflap">Play</a>
  </div>
  <div class="card glass">
    <h3>Picture Puzzle</h3>
    <p class="muted">Swap picture tiles from easy to extremely tiny.</p>
    <a class="btn" href="?pg=kid-puzzle">Play</a>
  </div>
  <div class="card glass">
    <h3>Drop Game Collider Lab</h3>
    <p class="muted">Test Suica-style item drops with sprite-shaped physics colliders.</p>
    <a class="btn" href="?pg=drop_game">Play</a>
  </div>
  <div class="card glass">
    <h3>Garden Invaderz</h3>
    <a class="btn" href="?pg=garden-invaderz">Play</a>
  </div>
  <div class="card glass">
    <h3>Bombertide</h3>
    <p class="muted">Bomb-duel 3 rival creatures across four tile arenas — your lead party member fights for you.</p>
    <a class="btn" href="?pg=bombertide">Play</a>
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
    <h3>Cups and Balls</h3>
    <a class="btn" href="?pg=cups-and-balls">Play</a>
  </div>
  <div class="card glass">
    <h3>Wheel of Fate</h3>
    <a class="btn" href="?pg=wheel-of-fate">Spin</a>
  </div>
  <div class="card glass">
    <h3>Paddle Panic</h3>
    <a class="btn" href="?pg=paddle-panic">Play</a>
  </div>
  <div class="card glass">
    <h3>Sudoku Sprint</h3>
    <a class="btn" href="?pg=sudoku">Play</a>
  </div>
  <div class="card glass">
    <h3>Fishing</h3>
    <a class="btn" href="?pg=fishing">Play</a>
  </div>
  <div class="card glass">
    <h3>Minigolf</h3>
    <a class="btn" href="?pg=minigolf">Play</a>
  </div>
  <div class="card glass">
    <h3>Trainer Battle</h3>
    <p class="muted">Trigger a random trainer encounter and throw your creature team into a fast elemental duel.</p>
    <a class="btn" href="?pg=battle_minigame">Trigger Encounter</a>
  </div>
</div>
