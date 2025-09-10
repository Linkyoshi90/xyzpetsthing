<?php
require_once __DIR__.'/../auth.php';
require_login();
require_once __DIR__.'/../lib/bank.php';

$uid = current_user()['id'];
$acct = get_bank_account($uid);
?>
<h1>Bank</h1>
<?php if(!$acct): ?>
<form method="post">
    <button class="btn" type="submit" name="create" value="1">Open Bank Account</button>
</form>
<?php else: ?>
<p>Bank balance: <?= number_format($acct['balance'], 2) ?></p>
<form method="post">
    <input type="number" step="0.01" name="amount" placeholder="Amount" required>
    <button class="btn" type="submit" name="deposit" value="1">Deposit</button>
    <button class="btn" type="submit" name="withdraw" value="1">Withdraw</button>
</form>
<?php endif; ?>