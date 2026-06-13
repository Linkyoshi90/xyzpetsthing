<?php
require_once __DIR__.'/../auth.php';
require_once __DIR__.'/../db.php';
require_once __DIR__.'/input.php';
require_once __DIR__.'/temp_user.php';

const CUPS_SESSION_KEY = 'cups_and_balls_state';
const CUPS_CURRENCY_ID = 1;
const CUPS_FINAL_ROUND = 3;

function cups_default_state(): array {
    return [
        'status' => 'idle',
        'round' => 0,
        'bet' => 0,
        'ball' => null,
        'last_choice' => null,
        'last_correct' => null,
        'payout' => 0,
        'message' => 'Place a bet to begin.',
    ];
}

function cups_get_state(): array {
    if (!isset($_SESSION[CUPS_SESSION_KEY]) || !is_array($_SESSION[CUPS_SESSION_KEY])) {
        $_SESSION[CUPS_SESSION_KEY] = cups_default_state();
    }
    return array_merge(cups_default_state(), $_SESSION[CUPS_SESSION_KEY]);
}

function cups_save_state(array $state): void {
    $_SESSION[CUPS_SESSION_KEY] = array_merge(cups_default_state(), $state);
}

function cups_ensure_balance_row(int $uid): void {
    if ($uid === 0) {
        return;
    }
    q(
        'INSERT INTO user_balances (user_id, currency_id, balance) VALUES (?,?,0) ON DUPLICATE KEY UPDATE balance = balance',
        [$uid, CUPS_CURRENCY_ID]
    );
}

function cups_get_cash_balance(int $uid): int {
    if ($uid === 0) {
        return (int)round(temp_user_balance('cash'));
    }
    $balance = q(
        'SELECT balance FROM user_balances WHERE user_id = ? AND currency_id = ?',
        [$uid, CUPS_CURRENCY_ID]
    )->fetchColumn();
    return $balance === false ? 0 : (int)$balance;
}

function cups_refresh_cash(int $uid): int {
    $balance = cups_get_cash_balance($uid);
    if (isset($_SESSION['user'])) {
        $_SESSION['user']['cash'] = $balance;
    }
    return $balance;
}

function cups_adjust_balance(int $uid, int $amount, string $reason, array $metadata = []): int {
    if ($uid === 0) {
        return temp_user_adjust_balance('cash', $amount);
    }
    q(
        'UPDATE user_balances SET balance = balance + ? WHERE user_id = ? AND currency_id = ?',
        [$amount, $uid, CUPS_CURRENCY_ID]
    );
    q(
        'INSERT INTO currency_ledger (user_id, currency_id, amount_delta, reason, metadata) VALUES (?,?,?,?,?)',
        [$uid, CUPS_CURRENCY_ID, $amount, $reason, json_encode($metadata)]
    );
    return cups_refresh_cash($uid);
}

function cups_new_ball(): int {
    return random_int(0, 2);
}

function cups_public_state(array $state): array {
    return [
        'status' => (string)$state['status'],
        'round' => (int)$state['round'],
        'bet' => (int)$state['bet'],
        'ball' => $state['ball'] === null ? null : (int)$state['ball'],
        'last_choice' => $state['last_choice'] === null ? null : (int)$state['last_choice'],
        'last_correct' => $state['last_correct'] === null ? null : (bool)$state['last_correct'],
        'payout' => (int)$state['payout'],
        'message' => (string)$state['message'],
    ];
}

function cups_json_response(array $payload, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

function cups_error_response(string $message, int $status = 400): void {
    cups_json_response(['ok' => false, 'error' => $message], $status);
}

function cups_action_payload(): array {
    $raw = file_get_contents('php://input');
    $data = $raw ? json_decode($raw, true) : null;
    if (!is_array($data)) {
        $data = $_POST;
    }
    return is_array($data) ? $data : [];
}

function cups_handle_post(): void {
    require_login();

    $uid = (int)current_user()['id'];
    cups_ensure_balance_row($uid);
    $state = cups_get_state();
    $data = cups_action_payload();
    $action = input_string($data['action'] ?? '', 20);

    switch ($action) {
        case 'start':
            if ($state['status'] === 'awaiting_choice') {
                cups_error_response('Finish the current round before starting another bet.');
            }
            if ($state['status'] === 'reveal' && !empty($state['last_correct']) && (int)$state['round'] < CUPS_FINAL_ROUND) {
                cups_error_response('Advance to the next round before changing your bet.');
            }
            $bet = input_int($data['bet'] ?? 0, 1);
            if ($bet < 1) {
                cups_error_response('Bet at least 1 '.APP_CURRENCY_LONG_NAME.'.');
            }
            $balance = cups_get_cash_balance($uid);
            if ($bet > $balance) {
                cups_error_response('You do not have enough '.APP_CURRENCY_LONG_NAME.' for that bet.');
            }

            cups_adjust_balance($uid, -$bet, 'cups_and_balls_bet', ['bet' => $bet]);
            $state = [
                'status' => 'awaiting_choice',
                'round' => 1,
                'bet' => $bet,
                'ball' => cups_new_ball(),
                'last_choice' => null,
                'last_correct' => null,
                'payout' => 0,
                'message' => 'Round 1',
            ];
            cups_save_state($state);
            cups_json_response([
                'ok' => true,
                'state' => cups_public_state($state),
                'balance' => cups_refresh_cash($uid),
            ]);

        case 'choose':
            if ($state['status'] !== 'awaiting_choice') {
                cups_error_response('There is no active cup to choose.');
            }
            $choiceValue = $data['choice'] ?? null;
            $choice = filter_var($choiceValue, FILTER_VALIDATE_INT);
            if ($choice === false || $choice < 0 || $choice > 2) {
                cups_error_response('Choose one of the three cups.');
            }
            $choice = (int)$choice;

            $correct = $choice === (int)$state['ball'];
            $state['last_choice'] = $choice;
            $state['last_correct'] = $correct;
            $state['payout'] = 0;

            if (!$correct) {
                $state['status'] = 'lost';
                $state['message'] = 'Missed. The bet stays on the table.';
            } elseif ((int)$state['round'] >= CUPS_FINAL_ROUND) {
                $payout = (int)$state['bet'] * 2;
                cups_adjust_balance($uid, $payout, 'cups_and_balls_win', [
                    'bet' => (int)$state['bet'],
                    'rounds' => CUPS_FINAL_ROUND,
                    'payout' => $payout,
                ]);
                $state['status'] = 'won';
                $state['payout'] = $payout;
                $state['message'] = 'Three clean calls. Payout doubled.';
            } else {
                $state['status'] = 'reveal';
                $state['message'] = 'Correct. Round '.((int)$state['round'] + 1).' is ready.';
            }
            cups_save_state($state);
            cups_json_response([
                'ok' => true,
                'state' => cups_public_state($state),
                'balance' => cups_refresh_cash($uid),
            ]);

        case 'next':
            if ($state['status'] !== 'reveal' || empty($state['last_correct']) || (int)$state['round'] >= CUPS_FINAL_ROUND) {
                cups_error_response('There is no next round waiting.');
            }
            $nextRound = (int)$state['round'] + 1;
            $state['status'] = 'awaiting_choice';
            $state['round'] = $nextRound;
            $state['ball'] = cups_new_ball();
            $state['last_choice'] = null;
            $state['last_correct'] = null;
            $state['payout'] = 0;
            $state['message'] = 'Round '.$nextRound;
            cups_save_state($state);
            cups_json_response([
                'ok' => true,
                'state' => cups_public_state($state),
                'balance' => cups_refresh_cash($uid),
            ]);

        case 'reset':
            $state = cups_default_state();
            cups_save_state($state);
            cups_json_response([
                'ok' => true,
                'state' => cups_public_state($state),
                'balance' => cups_refresh_cash($uid),
            ]);

        default:
            cups_error_response('Unknown cups and balls action.');
    }
}
