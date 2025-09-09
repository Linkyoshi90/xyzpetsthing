<?php
require_once __DIR__.'/db.php';

function current_user(){ return $_SESSION['user'] ?? null; }
function require_login(){
  if(!current_user()){ header('Location: ?pg=login'); exit; }
}

function login($email,$pass){
  $st = q("SELECT user_id, username, password_hash FROM users WHERE email = ?", [$email]);
  $u = $st->fetch(PDO::FETCH_ASSOC);
  if($u && verify_password($pass,$u['password_hash'])){
    $_SESSION['user'] = ['id'=>$u['user_id'],'username'=>$u['username']];
    return true;
  }
  return false;
}
function register($email,$username,$pass){
  $hash = password_hash($pass, PASSWORD_DEFAULT);
  q("INSERT INTO users(username,email,password_hash) VALUES(?,?,?)",[$username,$email,$hash]);
  return login($email,$pass);
}
function verify_password($pass,$hash){
  if(password_get_info($hash)['algo'] !== 0){
    return password_verify($pass,$hash);
  }
  return hash('sha256',$pass,true) === $hash;
}
function temp_login(){
  $_SESSION['user'] = ['id'=>0,'username'=>'temp'];
}
function logout(){ $_SESSION=[]; session_destroy(); }
