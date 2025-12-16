(() => {
  const data = window.gachaData || {};
  const items = Array.isArray(data.items) ? data.items : [];
  const cost = data.cost ?? 100;

  const spinButton = document.getElementById('gacha-spin');
  const machine = document.getElementById('gacha-machine');
  const coin = document.getElementById('gacha-coin');
  const capsuleArea = document.getElementById('capsule-area');

  if (!spinButton || !machine || !coin || !capsuleArea) return;

  let busy = false;

  const clearCapsules = () => {
    const existing = capsuleArea.querySelector('.gacha-capsule');
    if (existing) existing.remove();
  };

  const formatPrice = (price) => {
    if (price === null || price === undefined || price === '') return 'No price set';
    const numberPrice = Number(price);
    if (Number.isNaN(numberPrice)) return 'No price set';
    return `${Math.round(numberPrice).toLocaleString()} Dosh`;
  };

  const revealCapsule = (capsule, item) => {
    const label = capsule.querySelector('.capsule-label');
    const priceTag = capsule.querySelector('.capsule-price');
    if (!label || !priceTag) return;
    label.hidden = true;
    priceTag.hidden = false;
    priceTag.innerHTML = `<strong>${item.item_name || 'Mystery item'}</strong><small>${formatPrice(item.base_price)}</small>`;
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
    capsule.addEventListener('click', () => revealCapsule(capsule, item));
    return capsule;
  };

  const animateCoin = () => {
    coin.classList.remove('drop');
    void coin.offsetWidth; // force reflow
    coin.classList.add('drop');
  };

  const animateMachine = () => {
    machine.classList.remove('wiggle');
    void machine.offsetWidth;
    machine.classList.add('wiggle');
  };

  const spin = () => {
    if (busy) return;
    if (!items.length) {
      alert('No items loaded from the database yet.');
      return;
    }

    busy = true;
    clearCapsules();
    animateCoin();
    animateMachine();

    setTimeout(() => {
      const roll = items[Math.floor(Math.random() * items.length)];
      const capsule = buildCapsule(roll);
      capsuleArea.appendChild(capsule);
      capsule.focus({ preventScroll: true });
      busy = false;
    }, 1200);
  };

  spinButton.addEventListener('click', spin);

  // Update cost label if a data attribute is present
  const costBadges = document.querySelectorAll('.gacha-cost, .gacha-cost .currency-label');
  if (costBadges.length && cost) {
    const badge = document.querySelector('.gacha-cost');
    if (badge) {
      badge.firstChild.nodeValue = `${Math.round(cost)}`;
    }
  }
})();
