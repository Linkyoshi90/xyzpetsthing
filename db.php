<?php
require_once __DIR__.'/lib/errors.php';
require_once __DIR__.'/config.php';

class NullPDOStatement {
  public function execute($params = []) { return false; }
  public function fetch($mode = null) { return false; }
  public function fetchAll($mode = null) { return []; }
  public function fetchColumn($column = 0) { return false; }
  public function bindValue($param, $value, $type = null) { return false; }
}
function db() {
  static $pdo;
  static $connection_failed = false;

  if ($connection_failed) {
    return null;
  }

  if (!$pdo) {
    try {
      $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
                     DB_USER, DB_PASS, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    } catch (PDOException $e) {
      $connection_failed = true;
      app_add_error('Database connection failed: '.$e->getMessage());
      return null;
    }
  }
  return $pdo;
}
function q($sql,$params=[]){
  $pdo = db();
  if (!$pdo) {
    return new NullPDOStatement();
  }

  try {
    $st=$pdo->prepare($sql);
    $st->execute($params);
    return $st;
  } catch (PDOException $e) {
    app_add_error('Database query failed: '.$e->getMessage());
    return new NullPDOStatement();
  }
}
