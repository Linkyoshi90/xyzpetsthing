(() => {
  const data = window.pettingData;
  if (!data) return;

  const viewport = document.getElementById('petting-viewport');
  const stage = document.getElementById('petting-stage');
  const petImg = document.getElementById('active-pet');
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

  let activePetId = data.activePetId;
  let draggingFood = null;
  let dragProxy = null;
  let idleTimer = null;

  const hopSound = data.assets?.hop ? new Audio(data.assets.hop) : null;
  const eatSound = data.assets?.eat ? new Audio(data.assets.eat) : null;

  dustCloud.style.backgroundImage = data.assets?.dust ? `url(${data.assets.dust})` : 'none';

  function setActivePet(petId) {
    const pet = data.pets.find((p) => p.id === petId);
    if (!pet) return;
    activePetId = petId;
    petImg.src = pet.image;
    petImg.alt = pet.name;
    closePetBanner();
    renderFood();
    scatterDust(petImg.offsetLeft + petImg.clientWidth / 2, petImg.offsetTop + petImg.clientHeight);
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
          return;
        }
        if (eatSound) {
          eatSound.currentTime = 0;
          eatSound.play().catch(() => {});
        }
        dropCrumbs(petRect.left + petRect.width / 2, petRect.bottom - 10, 6);
        showHearts(res.hearts || 2);
        updateFoodCount(item.id, res.remaining ?? item.quantity - 1);
      })
      .catch((err) => console.error(err));
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

  function swapPet(petId) {
    if (petId === activePetId) return;
    const currentX = -120;
    petImg.style.left = `${currentX}px`;
    petImg.classList.add('running');
    setTimeout(() => {
      setActivePet(petId);
      petImg.style.left = '110%';
      requestAnimationFrame(() => {
        petImg.classList.remove('running');
        petImg.style.left = '40%';
      });
    }, 300);
  }

  function scatterDust(x, y) {
    if (!dustCloud) return;
    dustCloud.style.left = `${x}px`;
    dustCloud.style.top = `${y}px`;
    dustCloud.classList.remove('active');
    void dustCloud.offsetWidth;
    dustCloud.classList.add('active');
  }

  function idleHop() {
    const rect = viewport.getBoundingClientRect();
    const targetX = Math.random() * (rect.width - 200) + 100;
    const targetY = Math.random() * 80 + 16;
    petImg.style.left = `${targetX}px`;
    petImg.style.bottom = `${targetY}%`;
    scatterDust(targetX, rect.height * (1 - targetY / 100));
    if (hopSound) {
      hopSound.currentTime = 0;
      hopSound.play().catch(() => {});
    }
  }

  function disappearAndReturn() {
    petImg.style.left = '-30%';
    petImg.style.opacity = '0';
    setTimeout(() => {
      petImg.style.left = '120%';
      setTimeout(() => {
        petImg.style.opacity = '1';
        petImg.style.left = '40%';
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

  viewport.addEventListener('dblclick', () => {
    petImg.style.left = '50%';
    petImg.style.bottom = '18%';
    scatterDust(stage.clientWidth / 2, stage.clientHeight * 0.82);
    resetIdleTimer();
  });

  viewport.addEventListener('pointerdown', () => resetIdleTimer());

  renderPets();
  renderFood();
  resetIdleTimer();
})();
