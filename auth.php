<?php
require_once __DIR__.'/db.php';

function current_user(){ return $_SESSION['user'] ?? null; }

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

function login($email,$pass){
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
  $_SESSION=[];
  session_destroy();
}