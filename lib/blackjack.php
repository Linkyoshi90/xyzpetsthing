<?php
require_once __DIR__.'/../db.php';

const BLACKJACK_SESSION_KEY = 'blackjack_state';
const BLACKJACK_ERRORS_KEY = 'blackjack_errors';
const BLACKJACK_CURRENCY_ID = 1;

function blackjack_default_state(): array {
    return [
        'deck' => [],
        'player' => [],
        'dealer' => [],
        'bet' => 0,
        'status' => 'idle',
        'outcome' => null,
        'message' => 'Place your bet to begin.',
    ];
}

function blackjack_get_state(): array {
    if (!isset($_SESSION[BLACKJACK_SESSION_KEY]) || !is_array($_SESSION[BLACKJACK_SESSION_KEY])) {
        $_SESSION[BLACKJACK_SESSION_KEY] = blackjack_default_state();
    }
    return $_SESSION[BLACKJACK_SESSION_KEY];
}

function blackjack_save_state(array $state): void {
    $_SESSION[BLACKJACK_SESSION_KEY] = $state;
}

function blackjack_record_errors(array $errors): void {
    $_SESSION[BLACKJACK_ERRORS_KEY] = $errors;
}

function blackjack_pop_errors(): array {
    $errors = $_SESSION[BLACKJACK_ERRORS_KEY] ?? [];
    unset($_SESSION[BLACKJACK_ERRORS_KEY]);
    return $errors;
}

function blackjack_create_deck(): array {
    $ranks = ['A','2','3','4','5','6','7','8','9','10','J','Q','K'];
    $suits = ['♠','♥','♦','♣'];
    $deck = [];
    foreach ($suits as $suit) {
        foreach ($ranks as $rank) {
            $deck[] = ['rank' => $rank, 'suit' => $suit];
        }
    }
    return $deck;
}

function blackjack_draw_card(array &$deck): array {
    if (empty($deck)) {
        $deck = blackjack_create_deck();
        shuffle($deck);
    }
    return array_shift($deck);
}

function blackjack_hand_value(array $hand): int {
    $total = 0;
    $aces = 0;
    foreach ($hand as $card) {
        $rank = $card['rank'];
        if ($rank === 'A') {
            $total += 11;
            $aces++;
        } elseif (in_array($rank, ['K','Q','J'], true)) {
            $total += 10;
        } else {
            $total += (int)$rank;
        }
    }
    while ($total > 21 && $aces > 0) {
        $total -= 10;
        $aces--;
    }
    return $total;
}

function blackjack_is_blackjack(array $hand): bool {
    return count($hand) === 2 && blackjack_hand_value($hand) === 21;
}

function blackjack_ensure_balance_row(int $uid): void {
    q(
        'INSERT INTO user_balances (user_id, currency_id, balance) VALUES (?,?,0) ON DUPLICATE KEY UPDATE balance = balance',
        [$uid, BLACKJACK_CURRENCY_ID]
    );
}

function blackjack_get_cash_balance(int $uid): int {
    $balance = q(
        'SELECT balance FROM user_balances WHERE user_id = ? AND currency_id = ?',
        [$uid, BLACKJACK_CURRENCY_ID]
    )->fetchColumn();
    return $balance === false ? 0 : (int)$balance;
}

function blackjack_refresh_cash(int $uid): int {
    $balance = blackjack_get_cash_balance($uid);
    if (isset($_SESSION['user'])) {
        $_SESSION['user']['cash'] = (int)$balance;
    }
    return (int)$balance;
}

function blackjack_adjust_balance(int $uid, int $amount, string $reason, array $metadata = []): int {
    q(
        'UPDATE user_balances SET balance = balance + ? WHERE user_id = ? AND currency_id = ?',
        [$amount, $uid, BLACKJACK_CURRENCY_ID]
    );
    q(
        'INSERT INTO currency_ledger (user_id, currency_id, amount_delta, reason, metadata) VALUES (?,?,?,?,?)',
        [$uid, BLACKJACK_CURRENCY_ID, $amount, $reason, json_encode($metadata)]
    );
    return blackjack_refresh_cash($uid);
}

function blackjack_card_label(array $card): string {
    return $card['rank'].$card['suit'];
}