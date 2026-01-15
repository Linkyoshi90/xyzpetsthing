<?php
require_once __DIR__.'/db.php';

const REMEMBER_COOKIE_NAME = 'xyzpets_remember';
const REMEMBER_COOKIE_TTL = 2592000;

function current_user(){ return $_SESSION['user'] ?? null; }

function remember_cookie_options(int $expires): array {
  return [
    'expires' => $expires,
    'path' => '/',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
  ];
}

function clear_remember_cookie(): void {
  setcookie(REMEMBER_COOKIE_NAME, '', remember_cookie_options(time() - 3600));
}

function delete_remember_token(?string $selector): void {
  if (!$selector) {
    return;
  }
  q("DELETE FROM user_remember_tokens WHERE selector = ?", [$selector]);
}

function create_remember_token(int $user_id): void {
  $selector = bin2hex(random_bytes(9));
  $token = bin2hex(random_bytes(32));
  $token_hash = hash('sha256', $token);
  $expires_ts = time() + REMEMBER_COOKIE_TTL;
  $expires_at = date('Y-m-d H:i:s', $expires_ts);
  q("DELETE FROM user_remember_tokens WHERE user_id = ?", [$user_id]);
  q(
    "INSERT INTO user_remember_tokens(user_id, selector, token_hash, expires_at)
     VALUES(?, ?, ?, ?)",
    [$user_id, $selector, $token_hash, $expires_at]
  );
  setcookie(REMEMBER_COOKIE_NAME, $selector.':'.$token, remember_cookie_options($expires_ts));
}

function restore_login_from_cookie(): void {
  if (current_user()) {
    return;
  }
  $cookie = $_COOKIE[REMEMBER_COOKIE_NAME] ?? '';
  if ($cookie === '' || strpos($cookie, ':') === false) {
    return;
  }
  [$selector, $token] = explode(':', $cookie, 2);
  if ($selector === '' || $token === '' || !ctype_xdigit($selector) || !ctype_xdigit($token)) {
    clear_remember_cookie();
    delete_remember_token($selector);
    return;
  }
  $st = q(
    "SELECT t.user_id, t.token_hash, t.expires_at,
            u.username,
            COALESCE(b1.balance,0) AS cash, COALESCE(b2.balance,0) AS gems
       FROM user_remember_tokens t
       JOIN users u ON u.user_id = t.user_id
       LEFT JOIN user_balances b1 ON (u.user_id = b1.user_id AND b1.currency_id = 1)
       LEFT JOIN user_balances b2 ON (u.user_id = b2.user_id AND b2.currency_id = 2)
      WHERE t.selector = ?",
    [$selector]
  );
  $u = $st->fetch(PDO::FETCH_ASSOC);
  if (!$u) {
    clear_remember_cookie();
    return;
  }
  if (strtotime($u['expires_at']) < time()) {
    delete_remember_token($selector);
    clear_remember_cookie();
    return;
  }
  $expected_hash = hash('sha256', $token);
  if (!hash_equals($u['token_hash'], $expected_hash)) {
    delete_remember_token($selector);
    clear_remember_cookie();
    return;
  }
  $_SESSION['user'] = [
    'id' => $u['user_id'],
    'username' => $u['username'],
    'cash' => (int)$u['cash'],
    'gems' => (int)$u['gems'],
  ];
  delete_remember_token($selector);
  create_remember_token((int)$u['user_id']);
}

function is_temp_user(): bool {
  $u = current_user();
  return $u && (int)($u['id'] ?? -1) === 0;
}

function temp_user_default_store(): array {
  return [
    'next_pet_id' => 1,
    'pets' => [],
    'inventory' => [],
    'balances' => ['cash' => 0.0, 'gems' => 0.0],
    'score_exchange' => ['date' => date('Y-m-d'), 'count' => 0],
    'bank' => ['has_account' => false, 'balance' => 0.0, 'interest' => 0],
  ];
}

function &temp_user_data(): array {
  if (!isset($_SESSION['temp_user_data']) || !is_array($_SESSION['temp_user_data'])) {
    $_SESSION['temp_user_data'] = temp_user_default_store();
  }
  return $_SESSION['temp_user_data'];
}

function require_login(){
  if(!current_user()){ header('Location: ?pg=login'); exit; }
}

function login($email,$pass, bool $remember = false){
  $st = q(
    "SELECT u.user_id AS id, u.username, u.password_hash,
            COALESCE(b1.balance,0) AS cash, COALESCE(b2.balance,0) AS gems
       FROM users u
       LEFT JOIN user_balances b1 ON (u.user_id = b1.user_id AND b1.currency_id = 1)
       LEFT JOIN user_balances b2 ON (u.user_id = b2.user_id AND b2.currency_id = 2)
       WHERE u.email = ?",
    [$email]
  );
  $u = $st->fetch(PDO::FETCH_ASSOC);
  if($u && verify_password($pass,$u['password_hash'])){
    $_SESSION['user'] = [
      'id'=>$u['id'],
      'username'=>$u['username'],
      'cash'=>(int)$u['cash'],
      'gems'=>(int)$u['gems'],
    ];
    if ($remember) {
      create_remember_token((int)$u['id']);
    }
    return true;
  }
  return false;
}
function register($email,$username,$pass){
  $hash = password_hash($pass, PASSWORD_DEFAULT);
  q("INSERT INTO users(username,email,password_hash) VALUES(?,?,?)",[$username,$email,$hash]);
  $uid = db()->lastInsertId();
  q("INSERT INTO user_balances(user_id,currency_id,balance) VALUES(?,?,?)",[$uid,1,3000]);
  return login($email,$pass);
}
function verify_password($pass,$hash){
  if(password_get_info($hash)['algo'] !== 0){
    return password_verify($pass,$hash);
  }
  return hash('sha256',$pass,true) === $hash;
}
function temp_login(){
  $_SESSION['temp_user_data'] = temp_user_default_store();
  $_SESSION['user'] = ['id'=>0,'username'=>'temp','cash'=>0,'gems'=>0];
}

function logout(){
  $selector = '';
  $cookie = $_COOKIE[REMEMBER_COOKIE_NAME] ?? '';
  if (strpos($cookie, ':') !== false) {
    [$selector] = explode(':', $cookie, 2);
  }
  delete_remember_token($selector);
  clear_remember_cookie();
  $_SESSION=[];
  session_destroy();
}

restore_login_from_cookie();
