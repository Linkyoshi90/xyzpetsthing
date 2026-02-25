<?php
require_once __DIR__.'/lib/errors.php';
require_once __DIR__.'/auth.php';
require_once __DIR__.'/lib/bank.php';
require_once __DIR__.'/lib/input.php';
require_once __DIR__.'/lib/pets.php';
$pg = input_string($_GET['pg'] ?? '', 50);
if ($pg === '') {
  $pg = current_user() ? 'main' : 'login';
}
$allowed = ['login','register','logout','main','pet','create_pet','inventory','petting','dress',
    'petting2','map','vote','games','friends','bank','user-chat','paint_shack','gacha',
    'user-guide','encyclopedia','petting_fullscreen','pettingBla',
    // Games
    'wheel-of-fate','fruitstack','garden-invaderz','runngunner',
    'wanted-alive','blackjack','paddle-panic','sudoku',
    'fishing','minigolf',
    // Continents
    'auronia','borealia','dawnmarch','gulfbelt','moana_crown',
    'orienthem','saharene','tundria','uluru','verdania',
    // Countries
    'aa','aeonstep','baharamandal','bretonreach','cc',
    'esd','esl','fom','fom-fishing','gc','hammurabia',
    'ie','kemet','ldk','nornheim','pelagora','rsc',
    'rheinland','rt','sie','sc',
    'stap','srl',
    'urb','xochimex','yamanokubo','yn',
    // country subsections
    'aa-adventure','aa-pizza','aa-library','aa_paint_shack','aa-wof','aest-shop',
    'bm_paint_shack',
    'bm_paint_shack','bm_pt',
    'br_paint_shack','br-everything-store',
    'cc_paint_shack',
    'esd_paint_shack',
    'esl_paint_shack',
    'gc_paint_shack',
    'h_paint_shack',
    'ie_paint_shack',
    'k_paint_shack','k_shelter','k-adventure',
    'ldk_paint_shack','ldk_breeding',
    'nh_paint_shack',
    'rsc_paint_shack','rsc-wof',
    'rl_paint_shack','rl_ff',
    'rt_paint_shack',
    'sie_paint_shack',
    'sc_paint_shack',
    'stap_paint_shack',
    'srl_paint_shack',
    'urb_paint_shack','urb-adventure',
    'urb_paint_shack','urb-adventure','urb-adventure2',
    'xm_paint_shack',
    'ynk_paint_shack','ynk-adventure','ynk-adventure2','ynk-ramen',
    'yn_paint_shack',
];
if(current_user()) {
  apply_daily_interest(current_user()['id']);
}
if($pg === 'bank' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  require_login();
  $uid = current_user()['id'];
  if(isset($_POST['create'])) {
    create_bank_account($uid);
  } elseif(isset($_POST['deposit'])) {
    $amt = input_float($_POST['amount'] ?? 0, 0.01);
    deposit_to_bank($uid, $amt);
  } elseif(isset($_POST['withdraw'])) {
    $amt = input_float($_POST['amount'] ?? 0, 0.01);
    withdraw_from_bank($uid, $amt);
  }
  header('Location: index.php?pg=bank');
  exit;
}
if($pg === 'wheel-of-fate' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  require __DIR__.'/pages/wheel-of-fate.php';
  exit;
}
if($pg === 'blackjack' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  require_login();
  require __DIR__.'/pages/blackjack_action.php';
  exit;
}
if($pg === 'aa-pizza' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  require_login();
  require __DIR__.'/pages/aa-pizza.php';
  exit;
}
if($pg === 'aest-shop' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  require_login();
  require __DIR__.'/pages/aest-shop.php';
  exit;
}
if($pg === 'ynk-ramen' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  require_login();
  require __DIR__.'/pages/ynk-ramen.php';
  exit;
}
if($pg === 'dress' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  require_login();
  require __DIR__.'/pages/dress.php';
  exit;
}
if(current_user()) {
  apply_daily_interest(current_user()['id']);
}
if(!in_array($pg,$allowed)) $pg = 'login';
include __DIR__.'/layout/header.php';

try {
  include __DIR__.'/pages/'.$pg.'.php';
} catch (Throwable $e) {
  app_add_error_from_exception($e, 'Page rendering failed:');
  echo '<div class="content-error" role="alert">We ran into a problem loading this page. Please try again later.</div>';
}

include __DIR__.'/layout/footer.php';
