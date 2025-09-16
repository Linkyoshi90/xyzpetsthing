<?php
require_once __DIR__.'/../auth.php';
require_once __DIR__.'/../lib/blackjack.php';
require_login();

$uid = current_user()['id'];
blackjack_ensure_balance_row($uid);
$state = blackjack_get_state();
$errors = [];
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'start':
        if ($state['status'] === 'player_turn') {
            $errors[] = 'Finish your current hand before starting a new one.';
            break;
        }
        $bet = isset($_POST['bet']) ? (int)$_POST['bet'] : 0;
        if ($bet < 1) {
            $errors[] = 'Bet at least 1 coin to start.';
            break;
        }
        $balance = blackjack_get_cash_balance($uid);
        if ($bet > $balance) {
            $errors[] = 'You do not have enough cash for that bet.';
            break;
        }
        $deck = blackjack_create_deck();
        shuffle($deck);
        $player = [blackjack_draw_card($deck), blackjack_draw_card($deck)];
        $dealer = [blackjack_draw_card($deck), blackjack_draw_card($deck)];
        blackjack_adjust_balance($uid, -$bet, 'blackjack_bet', ['bet' => $bet]);
        $state = [
            'deck' => $deck,
            'player' => $player,
            'dealer' => $dealer,
            'bet' => $bet,
            'status' => 'player_turn',
            'outcome' => null,
            'message' => 'Hit or stand?',
        ];
        $playerBlackjack = blackjack_is_blackjack($player);
        $dealerBlackjack = blackjack_is_blackjack($dealer);
        if ($playerBlackjack || $dealerBlackjack) {
            $state['status'] = 'round_over';
            if ($playerBlackjack && $dealerBlackjack) {
                blackjack_adjust_balance($uid, $bet, 'blackjack_push', ['bet' => $bet, 'result' => 'blackjack_push']);
                $state['outcome'] = 'push';
                $state['message'] = 'Push! You and the dealer both have blackjack.';
            } elseif ($playerBlackjack) {
                $payout = $bet * 2;
                blackjack_adjust_balance($uid, $payout, 'blackjack_win', ['bet' => $bet, 'result' => 'blackjack', 'payout' => $payout]);
                $state['outcome'] = 'win';
                $state['message'] = 'Blackjack! You win the hand immediately.';
            } else {
                $state['outcome'] = 'dealer_blackjack';
                $state['message'] = 'Dealer has blackjack. You lose the bet.';
            }
        }
        break;
    case 'hit':
        if ($state['status'] !== 'player_turn') {
            $errors[] = 'No active hand to hit.';
            break;
        }
        $card = blackjack_draw_card($state['deck']);
        $state['player'][] = $card;
        $total = blackjack_hand_value($state['player']);
        if ($total > 21) {
            $state['status'] = 'round_over';
            $state['outcome'] = 'bust';
            $state['message'] = 'Bust! You went over 21 and lose the bet.';
        } else {
            $state['message'] = 'You drew '.blackjack_card_label($card).'. Hit or stand?';
        }
        break;
    case 'stand':
        if ($state['status'] !== 'player_turn') {
            $errors[] = 'No active hand to stand on.';
            break;
        }
        while (blackjack_hand_value($state['dealer']) < 17) {
            $state['dealer'][] = blackjack_draw_card($state['deck']);
        }
        $playerTotal = blackjack_hand_value($state['player']);
        $dealerTotal = blackjack_hand_value($state['dealer']);
        $state['status'] = 'round_over';
        if ($dealerTotal > 21 || $playerTotal > $dealerTotal) {
            $payout = $state['bet'] * 2;
            blackjack_adjust_balance($uid, $payout, 'blackjack_win', [
                'bet' => $state['bet'],
                'result' => 'win',
                'player_total' => $playerTotal,
                'dealer_total' => $dealerTotal,
                'payout' => $payout,
            ]);
            $state['outcome'] = 'win';
            $state['message'] = 'You win! Dealer shows '.$dealerTotal.'.';
        } elseif ($dealerTotal === $playerTotal) {
            blackjack_adjust_balance($uid, $state['bet'], 'blackjack_push', [
                'bet' => $state['bet'],
                'result' => 'push',
                'player_total' => $playerTotal,
                'dealer_total' => $dealerTotal,
            ]);
            $state['outcome'] = 'push';
            $state['message'] = 'Push! Your bet is returned.';
        } else {
            $state['outcome'] = 'lose';
            $state['message'] = 'Dealer wins with '.$dealerTotal.'. You lose the bet.';
        }
        break;
    case 'reset':
        $state = blackjack_default_state();
        break;
    default:
        $errors[] = 'Unknown action.';
        break;
}

blackjack_save_state($state);
if ($errors) {
    blackjack_record_errors($errors);
}

header('Location: index.php?pg=blackjack');
exit;