(() => {
  const data = window.gachaData || {};
  const items = Array.isArray(data.items) ? data.items : [];
  const cost = data.cost ?? 100;
  const endpoint = typeof data.endpoint === 'string' && data.endpoint ? data.endpoint : window.location.href;
  const currencyLabel = typeof data.currencyLabel === 'string' && data.currencyLabel ? data.currencyLabel : 'Dosh';

  const spinButton = document.getElementById('gacha-spin');
  const machine = document.getElementById('gacha-machine');
  const coin = document.getElementById('gacha-coin');
  const capsuleArea = document.getElementById('capsule-area');
  const status = document.getElementById('gacha-status');

  if (!spinButton || !machine || !coin || !capsuleArea) return;

  let busy = false;

  const setStatus = (message) => {
    if (status) {
      status.textContent = message || '';
    }
  };

  const clearCapsules = () => {
    const existing = capsuleArea.querySelector('.gacha-capsule');
    if (existing) existing.remove();
  };

  const formatPrice = (price) => {
    if (price === null || price === undefined || price === '') return 'No price set';
    const numberPrice = Number(price);
    if (Number.isNaN(numberPrice)) return 'No price set';
    return `${Math.round(numberPrice).toLocaleString()} ${currencyLabel}`;
  };

  const revealCapsule = (capsule, item) => {
    const label = capsule.querySelector('.capsule-label');
    const priceTag = capsule.querySelector('.capsule-price');
    if (!label || !priceTag) return;
    label.hidden = true;
    priceTag.hidden = false;
    priceTag.innerHTML = `<strong>${item.item_name || 'Mystery item'}</strong><small>${formatPrice(item.base_price)}</small>`;
    capsule.setAttribute('aria-label', `${item.item_name || 'Mystery item'} capsule reward`);
    setStatus(`Prize revealed: ${item.item_name || 'Mystery item'}.`);
  };

  const buildCapsule = (item) => {
    const capsule = document.createElement('button');
    capsule.type = 'button';
    capsule.className = 'gacha-capsule pop-out';
    capsule.innerHTML = `
      <span class="capsule-top"></span>
      <span class="capsule-bottom"></span>
      <span class="capsule-label">?</span>
      <span class="capsule-price" hidden></span>
    `;
    capsule.setAttribute('aria-label', 'Mystery capsule');
    capsule.addEventListener('click', () => revealCapsule(capsule, item), { once: true });
    return capsule;
  };

  const animateCoin = () => {
    coin.classList.remove('drop');
    void coin.offsetWidth;
    coin.classList.add('drop');
  };

  const animateMachine = () => {
    machine.classList.remove('wiggle');
    void machine.offsetWidth;
    machine.classList.add('wiggle');
  };

  const parseJsonResponse = async (response) => {
    const raw = await response.text();
    const normalized = raw.replace(/^﻿/, '');

    try {
      return normalized ? JSON.parse(normalized) : null;
    } catch (error) {
      const snippet = normalized.replace(/\s+/g, ' ').trim().slice(0, 180);
      throw new Error(snippet || `Server returned invalid JSON (HTTP ${response.status}).`);
    }
  };

  const spin = async () => {
    if (busy) return;
    if (!items.length) {
      alert('No items loaded from the database yet.');
      return;
    }

    busy = true;
    spinButton.disabled = true;
    clearCapsules();
    setStatus(`Spinning... Cost: ${Math.round(cost)} ${currencyLabel}.`);
    animateCoin();
    animateMachine();

    try {
      const response = await fetch(endpoint, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'fetch',
        },
        body: JSON.stringify({ action: 'spin' }),
      });
      const payload = await parseJsonResponse(response);
      if (!response.ok || !payload || payload.success !== true || !payload.item) {
        throw new Error((payload && payload.error) || 'Unable to complete the gacha spin.');
      }

      setTimeout(() => {
        const capsule = buildCapsule(payload.item);
        capsuleArea.appendChild(capsule);
        capsule.focus({ preventScroll: true });
        setStatus(payload.message || `You got ${payload.item.item_name || 'a mystery item'}! Click the capsule to reveal it.`);
      }, 1200);
    } catch (error) {
      setStatus(error && error.message ? error.message : 'Unable to complete the gacha spin.');
      alert(error && error.message ? error.message : 'Unable to complete the gacha spin.');
    } finally {
      setTimeout(() => {
        busy = false;
        spinButton.disabled = false;
      }, 1200);
    }
  };

  spinButton.addEventListener('click', spin);

  const badge = document.querySelector('.gacha-cost');
  if (badge && cost) {
    const firstNode = badge.firstChild;
    if (firstNode && firstNode.nodeType === Node.TEXT_NODE) {
      firstNode.nodeValue = `${Math.round(cost)}`;
    }
  }
})();
