<?php
require_once __DIR__.'/../db.php';

const BANK_CURRENCY_ID = 1;
const BANK_INTEREST_RATE = 0.025; // 1% daily interest

function get_bank_account($uid) {
    $st = q("SELECT * FROM user_bank WHERE user_id = ? AND currency_id = ?", [$uid, BANK_CURRENCY_ID]);
    return $st->fetch(PDO::FETCH_ASSOC);
}

function create_bank_account($uid) {
    q("INSERT IGNORE INTO user_bank (user_id, currency_id, balance, interest) VALUES (?, ?, 0, 0)", [$uid, BANK_CURRENCY_ID]);
}

function apply_daily_interest($uid) {
    $acct = get_bank_account($uid);
    if(!$acct) return;
    $today = (int)date('Ymd');
    $last = (int)$acct['interest'];
    if($last >= $today) return; // already applied
    $interest = round($acct['balance'] * BANK_INTEREST_RATE, 2);
    if($interest <= 0) {
        q("UPDATE user_bank SET interest = ? WHERE user_id = ? AND currency_id = ?", [$today, $uid, BANK_CURRENCY_ID]);
        return;
    }
    q("UPDATE user_bank SET balance = balance + ?, interest = ? WHERE user_id = ? AND currency_id = ?", [$interest, $today, $uid, BANK_CURRENCY_ID]);
    q(
        "INSERT INTO currency_ledger (user_id, currency_id, amount_delta, reason, metadata) VALUES (?,?,?,?,?)",
        [$uid, BANK_CURRENCY_ID, $interest, 'bank_interest', json_encode(['rate' => BANK_INTEREST_RATE])]
    );
}

function deposit_to_bank($uid, $amount) {
    $amount = round($amount, 2);
    if($amount <= 0) return false;
    $balance = q("SELECT balance FROM user_balances WHERE user_id = ? AND currency_id = ?", [$uid, BANK_CURRENCY_ID])->fetchColumn();
    if($balance === false || $balance < $amount) return false;
    q("UPDATE user_balances SET balance = balance - ? WHERE user_id = ? AND currency_id = ?", [$amount, $uid, BANK_CURRENCY_ID]);
    q("INSERT INTO user_bank (user_id, currency_id, balance, interest) VALUES (?,?,?,0) ON DUPLICATE KEY UPDATE balance = balance + VALUES(balance)", [$uid, BANK_CURRENCY_ID, $amount]);
    $_SESSION['user']['cash'] = (int)($balance - $amount);
    return true;
}

function withdraw_from_bank($uid, $amount) {
    $amount = round($amount, 2);
    if($amount <= 0) return false;
    $acct = get_bank_account($uid);
    if(!$acct || $acct['balance'] < $amount) return false;
    q("UPDATE user_bank SET balance = balance - ? WHERE user_id = ? AND currency_id = ?", [$amount, $uid, BANK_CURRENCY_ID]);
    q("INSERT INTO user_balances (user_id, currency_id, balance) VALUES (?,?,?) ON DUPLICATE KEY UPDATE balance = balance + VALUES(balance)", [$uid, BANK_CURRENCY_ID, $amount]);
    $_SESSION['user']['cash'] = (int)($_SESSION['user']['cash'] + $amount);
    return true;
}
?>