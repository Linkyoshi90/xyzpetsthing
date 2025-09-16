<?php
require_once __DIR__.'/auth.php';
require_once __DIR__.'/lib/bank.php';
$pg = $_GET['pg'] ?? (current_user() ? 'main' : 'login');
$allowed = ['login','register','logout','main','pet','create_pet','inventory',
    'map','vote','games','friends','bank',
    'wheel-of-fate','fruitstack','garden-invaderz','runngunner',
    'wanted-alive','blackjack',
    'auronia','borealia','dawnmarch','gulfbelt','moana_crown',
    'orienthem','saharene','tundria','uluru','verdania'];
if(current_user()) {
  apply_daily_interest(current_user()['id']);
}
if($pg === 'bank' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  require_login();
  $uid = current_user()['id'];
  if(isset($_POST['create'])) {
    create_bank_account($uid);
  } elseif(isset($_POST['deposit'])) {
    $amt = floatval($_POST['amount'] ?? 0);
    deposit_to_bank($uid, $amt);
  } elseif(isset($_POST['withdraw'])) {
    $amt = floatval($_POST['amount'] ?? 0);
    withdraw_from_bank($uid, $amt);
  }
  header('Location: index.php?pg=bank');
  exit;
}
if($pg === 'blackjack' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  require_login();
  require __DIR__.'/pages/blackjack_action.php';
  exit;
}
if(current_user()) {
  apply_daily_interest(current_user()['id']);
}
if(!in_array($pg,$allowed)) $pg = 'login';
include __DIR__.'/layout/header.php';
include __DIR__.'/pages/'.$pg.'.php';
include __DIR__.'/layout/footer.php';
