<?php
require_once __DIR__.'/db.php';

function current_user(){ return $_SESSION['user'] ?? null; }
function require_login(){
  if(!current_user()){ header('Location: ?pg=login'); exit; }
}

function login($email,$pass){
  $st = q("SELECT * FROM users WHERE email = ?", [$email]);
  $u = $st->fetch(PDO::FETCH_ASSOC);
  if($u && password_verify($pass,$u['pass_hash'])){
    $_SESSION['user'] = ['id'=>$u['id'],'username'=>$u['username']];
    return true;
  }
  return false;
}
function register($email,$username,$pass){
  $hash = password_hash($pass, PASSWORD_DEFAULT);
  q("INSERT INTO users(email,username,pass_hash) VALUES(?,?,?)",[$email,$username,$hash]);
  return login($email,$pass);
}
function logout(){ $_SESSION=[]; session_destroy(); }
