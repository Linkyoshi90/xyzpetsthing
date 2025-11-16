const getCurrencyLongName = () => (window.appCurrency && window.appCurrency.longName) || 'Cash-Dosh';

document.addEventListener('DOMContentLoaded', () => {
    const exchangeBtn = document.querySelector('[data-sudoku-exchange]');
    if (exchangeBtn) {
        exchangeBtn.addEventListener('click', async () => {
            if (exchangeBtn.disabled) return;
            const score = parseInt(exchangeBtn.dataset.score, 10) || 0;
            if (!score) return;
            exchangeBtn.disabled = true;
            const status = document.querySelector('.exchange-status');
            if (status) {
                status.textContent = 'Submitting score...';
            }
            try {
                const res = await fetch('score_exchange.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ game: 'sudoku', score })
                });
                if (!res.ok) {
                    const data = await res.json().catch(() => ({ error: 'Unable to submit score.' }));
                    throw new Error(data.error || 'Unable to submit score.');
                }
                const data = await res.json();
                if (status) {
                    status.textContent = `Score converted! New ${getCurrencyLongName()} balance: ${data.cash}`;
                }
                exchangeBtn.textContent = 'Score submitted';
            } catch (err) {
                if (status) {
                    status.textContent = err.message;
                }
                exchangeBtn.disabled = false;
            }
        });
    }
});