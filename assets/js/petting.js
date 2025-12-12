(() => {
  const data = window.pettingData;
  if (!data) return;

  const viewport = document.getElementById('petting-viewport');
  const stage = document.getElementById('petting-stage');
  const petImg = document.getElementById('active-pet');
  const petShadow = document.querySelector('.pet-shadow');
  const heartLayer = document.getElementById('heart-layer');
  const crumbLayer = document.getElementById('crumb-layer');
  const dustCloud = document.getElementById('dust-cloud');
  const inventoryBanner = document.getElementById('inventory-banner');
  const inventoryList = document.getElementById('inventory-list');
  const inventoryToggle = document.getElementById('inventory-toggle');
  const inventoryClose = document.getElementById('inventory-close');
  const petToggle = document.getElementById('pet-switch-toggle');
  const petBanner = document.getElementById('pet-banner');
  const petBannerClose = document.getElementById('pet-banner-close');
  const petList = document.getElementById('pet-list');
  const hungerMeter = document.getElementById('hunger-meter');
  const hungerFill = hungerMeter?.querySelector('.fill');
  const hungerValue = hungerMeter?.querySelector('.value');
  const fullBanner = document.getElementById('full-banner');

  let activePetId = data.activePetId;
  let draggingFood = null;
  let dragProxy = null;
  let idleTimer = null;
  let fullTimer = null;
  const hungerMax = data.hungerMax ?? 100;

  const hopSound = data.assets?.hop ? new Audio(data.assets.hop) : null;
  const eatSound = data.assets?.eat ? new Audio(data.assets.eat) : null;

    dustCloud.style.backgroundImage = data.assets?.dust ? `url(${data.assets.dust})` : 'none';

    function clamp(value, min, max) {
        return Math.min(Math.max(value, min), max);
    }

    function getActivePet() {
        return data.pets.find((p) => p.id === activePetId);
    }

    function updateHungerDisplay() {
        const pet = getActivePet();
        if (!pet || !hungerMeter) return;
        const percent = Math.round(clamp(pet.hunger ?? 0, 0, hungerMax));
        if (hungerFill) hungerFill.style.width = `${(percent / hungerMax) * 100}%`;
        if (hungerValue) hungerValue.textContent = `${percent}/${hungerMax}`;
    }

    function setHunger(value) {
        const pet = getActivePet();
        if (!pet) return;
        pet.hunger = clamp(value, 0, hungerMax);
        updateHungerDisplay();
    }

    function showFullBanner() {
        if (!fullBanner) return;
        fullBanner.classList.add('visible');
        if (fullTimer) clearTimeout(fullTimer);
        fullTimer = setTimeout(() => fullBanner.classList.remove('visible'), 10000);
    }

    function setPetPosition(leftPx, bottomPx) {
        petImg.style.left = `${leftPx}px`;
        petImg.style.bottom = `${bottomPx}px`;
        if (petShadow) {
            const shadowBottom = Math.max(8, bottomPx - 45);
            petShadow.style.left = `${leftPx}px`;
            petShadow.style.bottom = `${shadowBottom}px`;
        }
    }

    function scatterDust(x, y) {
        if (!dustCloud) return;
        dustCloud.style.left = `${x}px`;
        dustCloud.style.top = `${y}px`;
        dustCloud.classList.remove('active');
        void dustCloud.offsetWidth;
        dustCloud.classList.add('active');
    }

    function syncShadowWithPet(petRect = null, stageRect = null) {
        const rect = petRect || petImg.getBoundingClientRect();
        const stageBox = stageRect || stage.getBoundingClientRect();
        const centerX = rect.left - stageBox.left + rect.width / 2;
        const bottom = stageBox.bottom - rect.bottom;
        setPetPosition(centerX, bottom);
    }

  function setActivePet(petId) {
    const pet = data.pets.find((p) => p.id === petId);
    if (!pet) return;
    activePetId = petId;
    petImg.src = pet.image;
    petImg.alt = pet.name;
    closePetBanner();
      renderFood();
      updateHungerDisplay();
      const stageRect = stage.getBoundingClientRect();
      const petRect = petImg.getBoundingClientRect();
      scatterDust(
          petRect.left - stageRect.left + petRect.width / 2,
          petRect.bottom - stageRect.top
      );
      syncShadowWithPet(petRect, stageRect);
  }

  function renderPets() {
    petList.innerHTML = '';
    data.pets.forEach((pet) => {
      const card = document.createElement('button');
      card.className = 'pet-card';
      card.type = 'button';
      card.setAttribute('data-id', pet.id);
      card.innerHTML = `
        <img src="${pet.image}" alt="${pet.name}">
        <div class="name">${pet.name}</div>
      `;
      card.addEventListener('click', () => swapPet(pet.id));
      petList.appendChild(card);
    });
  }

  function renderFood() {
    inventoryList.innerHTML = '';
    if (!data.food.length) {
      const empty = document.createElement('div');
      empty.className = 'empty-message';
      empty.textContent = 'No food in your inventory.';
      inventoryList.appendChild(empty);
      return;
    }

    data.food.forEach((item) => {
      if (item.quantity < 1) return;
      const el = document.createElement('div');
      el.className = 'food-item';
      el.setAttribute('data-id', item.id);
      el.setAttribute('draggable', 'false');
      const pref = preferenceFor(item.id);
      el.innerHTML = `
        <img src="${item.image}" alt="${item.name}">
        <div class="name">${item.name}</div>
        <div class="quantity">x${item.quantity}${pref ? ` • ${'❤'.repeat(Math.max(1, Math.min(3, pref)))}` : ''}</div>
      `;
      el.addEventListener('pointerdown', (ev) => startDrag(ev, item));
      inventoryList.appendChild(el);
    });
  }

  function preferenceFor(itemId) {
    const pet = data.pets.find((p) => p.id === activePetId);
    return pet?.preferences?.[itemId] ?? null;
  }

  function toggleInventory(open) {
    const shouldOpen = open ?? !inventoryBanner.classList.contains('active');
    inventoryBanner.classList.toggle('active', shouldOpen);
  }

  function togglePetBanner(open) {
    const shouldOpen = open ?? !petBanner.classList.contains('active');
    petBanner.classList.toggle('active', shouldOpen);
  }

  function closeInventory() {
    inventoryBanner.classList.remove('active');
  }

  function closePetBanner() {
    petBanner.classList.remove('active');
  }

  inventoryToggle.addEventListener('click', () => toggleInventory(true));
  inventoryClose.addEventListener('click', closeInventory);

  petToggle.addEventListener('click', () => togglePetBanner(true));
  petBannerClose.addEventListener('click', closePetBanner);

  function startDrag(ev, item) {
    ev.preventDefault();
    if (draggingFood) return;
    draggingFood = { item };
    dragProxy = document.createElement('div');
    dragProxy.className = 'drag-proxy';
    dragProxy.innerHTML = `<img src="${item.image}" alt="${item.name}">`;
    document.body.appendChild(dragProxy);
    moveDrag(ev.clientX, ev.clientY);
    closeInventory();
    window.addEventListener('pointermove', handleDragMove);
    window.addEventListener('pointerup', handleDragEnd, { once: true });
  }

  function moveDrag(x, y) {
    if (dragProxy) {
      dragProxy.style.left = `${x}px`;
      dragProxy.style.top = `${y}px`;
    }
  }

  function handleDragMove(ev) {
    if (!draggingFood) return;
    moveDrag(ev.clientX, ev.clientY);
  }

  function handleDragEnd(ev) {
    window.removeEventListener('pointermove', handleDragMove);
    const droppedOnPet = overPet(ev.clientX, ev.clientY);
    if (draggingFood && draggingFood.item) {
      const item = draggingFood.item;
      if (droppedOnPet) {
        feedPet(item, ev.clientX, ev.clientY);
      } else {
        dropCrumbs(ev.clientX, ev.clientY);
      }
    }
    if (dragProxy) dragProxy.remove();
    draggingFood = null;
    dragProxy = null;
  }

  function overPet(x, y) {
    const rect = petImg.getBoundingClientRect();
    return x >= rect.left && x <= rect.right && y >= rect.top && y <= rect.bottom;
  }

  function feedPet(item, x, y) {
    const pet = getActivePet();
    if (pet && (pet.hunger ?? 0) >= hungerMax) {
      showFullBanner();
      return;
      }
      const previousHunger = pet?.hunger ?? 0;
      const previousQuantity = item.quantity;
      const optimisticRemaining = Math.max(0, previousQuantity - 1);
      const optimisticHunger = previousHunger + (item.replenish ?? 0);
      updateFoodCount(item.id, optimisticRemaining);
      setHunger(optimisticHunger);
    const petRect = petImg.getBoundingClientRect();
    const formData = new FormData();
    formData.append('action', 'feed_pet');
    formData.append('pet_id', activePetId);
    formData.append('item_id', item.id);

    animateEating(item.image, x, y, petRect);

    fetch('?pg=petting', {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: formData,
      credentials: 'same-origin'
    })
      .then((res) => res.json())
      .then((res) => {
        if (!res.ok) {
          console.warn(res.message || 'Could not feed pet');
            if (res.full) showFullBanner();
            updateFoodCount(item.id, previousQuantity);
            setHunger(previousHunger);
          return;
        }
        if (eatSound) {
          eatSound.currentTime = 0;
          eatSound.play().catch(() => {});
        }
        dropCrumbs(petRect.left + petRect.width / 2, petRect.bottom - 10, 6);
          showHearts(res.hearts || 2);
          updateFoodCount(item.id, res.remaining ?? optimisticRemaining);
        if (typeof res.hunger === 'number') {
          setHunger(res.hunger);
        }
        if (res.full) {
          showFullBanner();
        }
      })
        .catch((err) => {
            console.error(err);
            updateFoodCount(item.id, previousQuantity);
            setHunger(previousHunger);
        });
  }

  function animateEating(image, startX, startY, petRect) {
    const anim = document.createElement('div');
    anim.className = 'eat-animation';
    anim.style.left = `${startX}px`;
    anim.style.top = `${startY}px`;
    anim.innerHTML = `<img src="${image}" alt="">`;
    document.body.appendChild(anim);
    requestAnimationFrame(() => {
      anim.style.left = `${petRect.left + petRect.width / 2}px`;
      anim.style.top = `${petRect.top + petRect.height / 2}px`;
    });
    setTimeout(() => anim.remove(), 800);
  }

  function dropCrumbs(x, y, count = 4) {
    const stageRect = stage.getBoundingClientRect();
    for (let i = 0; i < count; i++) {
      const crumb = document.createElement('div');
      crumb.className = 'crumb';
      crumb.style.left = `${x - stageRect.left + (Math.random() * 20 - 10)}px`;
      crumb.style.top = `${y - stageRect.top}px`;
      if (data.assets?.crumbs) {
        crumb.style.backgroundImage = `url(${data.assets.crumbs})`;
      } else {
        crumb.style.backgroundColor = '#d8994f';
      }
      crumbLayer.appendChild(crumb);
      setTimeout(() => crumb.remove(), 900);
    }
  }

  function showHearts(count) {
    const petRect = petImg.getBoundingClientRect();
    for (let i = 0; i < count; i++) {
      const heart = document.createElement('div');
      heart.className = 'heart';
      heart.style.left = `${petRect.left + petRect.width / 2 + (i - (count - 1) / 2) * 26}px`;
      heart.style.top = `${petRect.top - 10}px`;
      if (data.assets?.heart) {
        heart.style.backgroundImage = `url(${data.assets.heart})`;
      } else {
        heart.textContent = '❤';
        heart.style.color = '#f45c84';
      }
      heartLayer.appendChild(heart);
      setTimeout(() => heart.remove(), 1200);
    }
  }

  function updateFoodCount(itemId, remaining) {
    const target = data.food.find((f) => f.id === itemId);
    if (!target) return;
    target.quantity = remaining;
    renderFood();
    }
    function hopTo(leftPx, bottomPx, { playSound = true } = {}) {
        setPetPosition(leftPx, bottomPx);
        scatterDust(leftPx, stage.getBoundingClientRect().height - bottomPx);
        if (playSound && hopSound) {
            hopSound.currentTime = 0;
            hopSound.play().catch(() => { });
        }
    }

    function hopToPoint(clientX, clientY, options = {}) {
        const rect = stage.getBoundingClientRect();
        const halfWidth = petImg.clientWidth / 2;
        const clampedLeft = clamp(clientX - rect.left, halfWidth, rect.width - halfWidth);
        const clampedTop = clamp(clientY - rect.top, 0, rect.height);
        const bottom = clamp(rect.height - clampedTop, 8, rect.height - 10);
        hopTo(clampedLeft, bottom, options);
    }


  function swapPet(petId) {
      if (petId === activePetId) return;
      const stageRect = stage.getBoundingClientRect();
      const currentBottom = parseFloat(getComputedStyle(petImg).bottom) || stageRect.height * 0.18;
      setPetPosition(-120, currentBottom);
    petImg.classList.add('running');
    setTimeout(() => {
        setActivePet(petId);
        setPetPosition(stageRect.width + 120, currentBottom);
      requestAnimationFrame(() => {
          petImg.classList.remove('running');
          setPetPosition(stageRect.width * 0.4, currentBottom);
      });
    }, 300);
  }

    function idleHop() {
        const rect = stage.getBoundingClientRect();
        const targetLeft = Math.random() * (rect.width - 200) + 100;
        const minBottom = rect.height * 0.12;
        const targetBottom = Math.random() * (rect.height * 0.2) + minBottom;
        hopTo(targetLeft, targetBottom);
  }

    function disappearAndReturn() {
        const rect = stage.getBoundingClientRect();
        const currentBottom = parseFloat(getComputedStyle(petImg).bottom) || rect.height * 0.18;
        setPetPosition(-30, currentBottom);
    petImg.style.opacity = '0';
        setTimeout(() => {
            setPetPosition(rect.width + 30, currentBottom);
      setTimeout(() => {
          petImg.style.opacity = '1';
          hopTo(rect.width * 0.4, currentBottom, { playSound: false });
      }, 300);
    }, 600);
  }

  function resetIdleTimer() {
    if (idleTimer) clearTimeout(idleTimer);
    idleTimer = setTimeout(() => {
      Math.random() > 0.6 ? disappearAndReturn() : idleHop();
      resetIdleTimer();
    }, 4500);
  }

    stage.addEventListener('pointerdown', (ev) => {
        if (draggingFood) return;
        if (ev.button !== 0) return;
        if (!stage.contains(ev.target)) return;
        hopToPoint(ev.clientX, ev.clientY);
    resetIdleTimer();
  });

  viewport.addEventListener('pointerdown', () => resetIdleTimer());

    syncShadowWithPet();
  renderPets();
  renderFood();
  updateHungerDisplay();
  resetIdleTimer();
})();
