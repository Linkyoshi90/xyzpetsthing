window.updateCurrencyDisplay = function (balances) {
    if (balances && typeof balances === 'object') {
        if (balances.cash !== undefined) {
            var cashEl = document.getElementById('cash-balance');
            if (cashEl) cashEl.textContent = balances.cash;
        }
        if (balances.gems !== undefined) {
            var gemEl = document.getElementById('gems-balance');
            if (gemEl) gemEl.textContent = balances.gems;
        }
    }
};