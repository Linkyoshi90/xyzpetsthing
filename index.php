<?php
require_once __DIR__.'/lib/errors.php';
require_once __DIR__.'/lib/polyfills.php';
require_once __DIR__.'/auth.php';
require_once __DIR__.'/lib/bank.php';
require_once __DIR__.'/lib/input.php';
require_once __DIR__.'/lib/user_settings.php';
require_once __DIR__.'/lib/pets.php';
$pg = input_string($_GET['pg'] ?? '', 50);
if ($pg === '') {
  $pg = current_user() ? 'main' : 'login';
}
if ($pg === 'options') {
  $pg = 'settings';
}
$allowed = ['login','register','logout','main','pet','lineage','create_pet','inventory','petting','dress',
    'petting2','map','vote','games','friends','bank','user-chat','paint_shack','gacha',
    'settings','user-guide','encyclopedia','petting_fullscreen','pettingBla',
    // Games
    'wheel-of-fate','fruitstack','harmonflap','harmontide-milking-minigame','kid-puzzle','garden-invaderz','runngunner',
    'wanted-alive','blackjack','cups-and-balls','paddle-panic','sudoku',
    'fishing','minigolf','battle_minigame','drop_game','harmonflap','bombertide',
    // Continents
    'auronia','borealia','dawnmarch','gulfbelt','moana_crown',
    'orienthem','saharene','tundria','uluru','verdania',
    // Countries
    'aa','aeonstep','baharamandal','bretonreach','cc',
    'esd','esl','fom','fom-fishing','gc','hammurabia',
    'ie','kemet','ldk','nornheim','pelagora','rsc',
    'rheinland','rt','sie','sc',
    'stap','srl',
    'urb','stillwater-hollow','xochimex','yamanokubo','yn',
    // country subsections
    'aa-adventure','aa-pizza','aa-library','aa_paint_shack','aa-wof','aest-shop','aest-emberfen-grill',
    'bm_paint_shack',
    'bm_paint_shack','bm_pt',
    'br_paint_shack','br-everything-store',
    'cc_paint_shack','cc-apothecary',
    'esd_paint_shack',
    'esl_paint_shack',
    'gc_paint_shack',
    'h_paint_shack',
    'ie_paint_shack',
    'k_paint_shack','k_shelter','k-adventure',
    'ldk_paint_shack','ldk_breeding',
    'nh_paint_shack','nh-adventure',
    'pelagora-shop','pelagora-fishing','pelagora-library','pelagora-divers',
    'rsc_paint_shack','rsc-wof',
    'rl_paint_shack','rl_ff', 'rl-kiosk', 
    'rt_paint_shack',
    'sie_paint_shack',
    'sc_paint_shack',
    'stap_paint_shack','stap-starpath-gym',
    'srl_paint_shack',
    'urb_paint_shack','urb-adventure',
    'urb_paint_shack','urb-adventure','urb-adventure2','stcr-adventure',
    'xm_paint_shack',
    'ynk_paint_shack','ynk-adventure','ynk-adventure2','ynk-ramen',
    'yn_paint_shack',
    // regional shops
    'bm-market','cc-souq','esd-feather-flint','esl-olive-lamp','fom-lockside-shop',
    'gc-plaza-kiosk','h-ledger-house','ie-canopy-relic','k-bazaar-tent','k-bazaar-goods',
    'ldk-tea-trinkets','nh-frostmarket','rsc-roadhouse','rt-winter-pantry',
    'sc-ice-cache','sie-sun-terrace','srl-spice-dock','stap-trading-blanket',
    'urb-corner-mart','stcr-shop','xm-flower-market','yn-keeping-place-shop',
];
if(!in_array($pg,$allowed,true)) $pg = 'login';
if($pg === 'petting' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  require __DIR__.'/pages/petting.php';
  exit;
}
if($pg === 'pet' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  require_login();
  require __DIR__.'/pages/pet.php';
  exit;
}
if($pg === 'settings' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  require_login();
  user_settings_set_nsfw_enabled(isset($_POST['nsfw_mode']) && (string)$_POST['nsfw_mode'] === '1');
  header('Location: index.php?pg=settings&saved=1');
  exit;
}
if($pg === 'settings') {
  require_login();
}
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
if($pg === 'cups-and-balls' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  require_login();
  require __DIR__.'/pages/cups-and-balls.php';
  exit;
}
if($pg === 'battle_minigame' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  require_login();
  require __DIR__.'/pages/battle_minigame.php';
  exit;
}
if($pg === 'harmontide-milking-minigame' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  require_login();
  require __DIR__.'/pages/harmontide-milking-minigame.php';
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
if($pg === 'aest-emberfen-grill' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  require_login();
  require __DIR__.'/pages/aest-emberfen-grill.php';
  exit;
}
if($pg === 'ynk-ramen' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  require_login();
  require __DIR__.'/pages/ynk-ramen.php';
  exit;
}
$basket_shop_pages = [
  'aa-library','rl-kiosk','pelagora-shop','pelagora-library',
  'bm-market','cc-souq','cc-apothecary','esd-feather-flint','esl-olive-lamp','fom-lockside-shop',
  'gc-plaza-kiosk','h-ledger-house','ie-canopy-relic','k-bazaar-goods',
  'ldk-tea-trinkets','nh-frostmarket','rsc-roadhouse','rt-winter-pantry',
  'sc-ice-cache','sie-sun-terrace','srl-spice-dock','stap-trading-blanket',
  'urb-corner-mart','stcr-shop','xm-flower-market','yn-keeping-place-shop',
];
if(in_array($pg, $basket_shop_pages, true) && $_SERVER['REQUEST_METHOD'] === 'POST') {
  require_login();
  require __DIR__.'/pages/'.$pg.'.php';
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
if(current_user() && $pg === 'user-chat') {
  // Clear a conversation's unread badge before the header builds its notifications,
  // so opening a chat from a notification removes it on the same load.
  require_once __DIR__.'/lib/chat.php';
  $chatReadFriend = input_int($_GET['friend'] ?? 0, 1);
  if ($chatReadFriend > 0) {
    mark_conversation_read((int)current_user()['id'], $chatReadFriend);
  }
}
include __DIR__.'/layout/header.php';

try {
  include __DIR__.'/pages/'.$pg.'.php';
} catch (Throwable $e) {
  app_add_error_from_exception($e, 'Page rendering failed:');
  echo '<div class="content-error" role="alert">We ran into a problem loading this page. Please try again later.</div>';
}

include __DIR__.'/layout/footer.php';
