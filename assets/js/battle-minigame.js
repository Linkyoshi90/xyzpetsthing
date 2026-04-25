(function () {
  const root = document.getElementById('battle-app');
  const payloadEl = document.getElementById('battle-payload');
  if (!root || !payloadEl) {
    return;
  }

  let data;
  try {
    data = JSON.parse(payloadEl.textContent || '{}');
  } catch (error) {
    return;
  }

  if (!data.ready) {
    return;
  }

  const cloneTeam = (team) => (Array.isArray(team) ? team : []).map((pet) => ({
    ...pet,
    hp: Number(pet.hp || 0),
    maxHp: Number(pet.maxHp || 0),
    attack: Number(pet.attack || 0),
    defense: Number(pet.defense || 0),
    speed: Number(pet.speed || 0),
    elements: Array.isArray(pet.elements) ? pet.elements.map((value) => Number(value)) : [],
    elementNames: Array.isArray(pet.elementNames) ? pet.elementNames.slice() : [],
    moves: Array.isArray(pet.moves) ? pet.moves.map((move) => ({ ...move })) : [],
    fainted: false,
  }));

  const state = {
    playerTeam: cloneTeam(data.playerTeam),
    trainerTeam: cloneTeam(data.trainerTeam),
    items: Array.isArray(data.items) ? data.items.map((item) => ({ ...item, quantity: Number(item.quantity || 0) })) : [],
    playerIndex: 0,
    trainerIndex: 0,
    locked: true,
    battleEnded: false,
    awarding: false,
    menuKey: 'root',
    forceSwitch: false,
  };

  const el = {
    intro: document.getElementById('battle-intro'),
    start: document.getElementById('intro-start'),
    banner: document.getElementById('battle-banner'),
    announcer: document.getElementById('battle-announcer'),
    log: document.getElementById('battle-log'),
    turnIndicator: document.getElementById('battle-turn-indicator'),
    menuKicker: document.getElementById('battle-menu-kicker'),
    menuTitle: document.getElementById('battle-menu-title'),
    menu: document.getElementById('battle-menu'),
    detail: document.getElementById('battle-detail-card'),
    npc: document.getElementById('npc-combatant'),
    player: document.getElementById('player-combatant'),
    npcName: document.getElementById('npc-name'),
    npcLevel: document.getElementById('npc-level'),
    npcElements: document.getElementById('npc-elements'),
    npcHpText: document.getElementById('npc-hp-text'),
    npcHpFill: document.getElementById('npc-hp-fill'),
    npcImage: document.getElementById('npc-image'),
    playerName: document.getElementById('player-name'),
    playerLevel: document.getElementById('player-level'),
    playerElements: document.getElementById('player-elements'),
    playerHpText: document.getElementById('player-hp-text'),
    playerHpFill: document.getElementById('player-hp-fill'),
    playerImage: document.getElementById('player-image'),
  };

  const wait = (ms) => new Promise((resolve) => window.setTimeout(resolve, ms));
  const clamp = (value, min, max) => Math.max(min, Math.min(max, value));
  const currentPlayer = () => state.playerTeam[state.playerIndex] || null;
  const currentNpc = () => state.trainerTeam[state.trainerIndex] || null;
  const firstLivingIndex = (team) => team.findIndex((creature) => creature.hp > 0);
  const currencyLabel = typeof data.currencyLabel === 'string' && data.currencyLabel ? data.currencyLabel : 'Dosh';
  const fallbackMove = { id: 0, name: 'Tackle', power: 40, elementId: 1, elementName: 'Vulgaris' };

  function escapeHtml(value) {
    return String(value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function setTurnIndicator(text) {
    el.turnIndicator.textContent = text;
    el.turnIndicator.classList.add('battle-turn-live');
  }

  let announcerTimer = 0;
  function showAnnouncer(text) {
    window.clearTimeout(announcerTimer);
    el.announcer.textContent = text;
    el.announcer.classList.add('is-visible');
    announcerTimer = window.setTimeout(() => {
      el.announcer.classList.remove('is-visible');
    }, 1050);
  }

  function addLog(message) {
    const entry = document.createElement('div');
    entry.className = 'battle-log-entry';
    entry.textContent = message;
    el.log.appendChild(entry);
    while (el.log.children.length > 6) {
      el.log.removeChild(el.log.firstElementChild);
    }
  }

  function renderElementChips(container, names) {
    container.innerHTML = '';
    const list = Array.isArray(names) && names.length ? names : ['Neutral'];
    list.forEach((name) => {
      const chip = document.createElement('span');
      chip.className = 'battle-element-chip';
      chip.textContent = name;
      container.appendChild(chip);
    });
  }

  function hpColor(fill, pct) {
    if (pct <= 18) {
      fill.style.background = 'linear-gradient(90deg, #df5050, #b11d32)';
      return;
    }
    if (pct <= 42) {
      fill.style.background = 'linear-gradient(90deg, #e5b24f, #d77c24)';
      return;
    }
    fill.style.background = 'linear-gradient(90deg, #58d35a, #32a463)';
  }

  function setDetail(html) {
    el.detail.innerHTML = html;
  }

  function defaultDetailHtml() {
    const player = currentPlayer();
    const npc = currentNpc();
    if (!player || !npc) {
      return '<p class="battle-detail-empty">The field is waiting for two creatures to face off.</p>';
    }

    return `
      <h3 class="battle-detail-title">${escapeHtml(player.name)} vs ${escapeHtml(npc.name)}</h3>
      <p class="battle-detail-empty">
        ${escapeHtml(player.name)} is ready on your side of the field. ${escapeHtml(npc.name)} is staring back with
        ${escapeHtml((npc.elementNames || []).join(' / ') || 'Neutral')} energy.
      </p>
      <div class="battle-detail-stats">
        <div class="battle-detail-stat"><strong>${escapeHtml(player.name)}</strong><br>HP ${player.hp}/${player.maxHp} | SPD ${player.speed}</div>
        <div class="battle-detail-stat"><strong>${escapeHtml(npc.name)}</strong><br>HP ${npc.hp}/${npc.maxHp} | SPD ${npc.speed}</div>
      </div>
    `;
  }

  function creatureDetailHtml(creature, isActive) {
    const moves = Array.isArray(creature.moves) && creature.moves.length
      ? creature.moves.map((move) => `
          <span class="battle-detail-move">${escapeHtml(move.name)} - ${escapeHtml(move.elementName || 'Neutral')} - ${move.power} power</span>
        `).join('')
      : '<span class="battle-detail-move">No moves assigned</span>';

    return `
      <h3 class="battle-detail-title">${escapeHtml(creature.name)}${isActive ? ' <span class="battle-feed-label">Active</span>' : ''}</h3>
      <p class="battle-detail-empty">${escapeHtml(creature.species || 'Creature')} - ${escapeHtml((creature.elementNames || []).join(' / ') || 'Neutral')}</p>
      <div class="battle-detail-stats">
        <div class="battle-detail-stat"><strong>HP</strong><br>${creature.hp}/${creature.maxHp}</div>
        <div class="battle-detail-stat"><strong>Attack</strong><br>${creature.attack}</div>
        <div class="battle-detail-stat"><strong>Defense</strong><br>${creature.defense}</div>
        <div class="battle-detail-stat"><strong>Speed</strong><br>${creature.speed}</div>
      </div>
      <div class="battle-detail-moves">${moves}</div>
    `;
  }

  function moveDetailHtml(move) {
    const player = currentPlayer();
    const npc = currentNpc();
    const breakdown = npc ? calculateDamage(move, npc) : { totalDamage: move.power, summary: '' };

    return `
      <h3 class="battle-detail-title">${escapeHtml(move.name)}</h3>
      <p class="battle-detail-empty">
        ${escapeHtml(move.elementName || 'Neutral')} move. Base power ${move.power}.
        ${breakdown.summary ? escapeHtml(breakdown.summary) : ''}
      </p>
      <div class="battle-detail-stats">
        <div class="battle-detail-stat"><strong>Projected damage</strong><br>${breakdown.totalDamage}</div>
        <div class="battle-detail-stat"><strong>Your speed</strong><br>${player ? player.speed : 0}</div>
      </div>
    `;
  }

  function itemDetailHtml(item) {
    return `
      <h3 class="battle-detail-title">${escapeHtml(item.name)}</h3>
      <p class="battle-detail-empty">${escapeHtml(item.description || 'Battle item')}</p>
      <div class="battle-detail-stats">
        <div class="battle-detail-stat"><strong>Healing</strong><br>${item.heal} HP</div>
        <div class="battle-detail-stat"><strong>Remaining</strong><br>${item.quantity}</div>
      </div>
    `;
  }

  function syncCreatureCard(side, creature) {
    const nameEl = side === 'player' ? el.playerName : el.npcName;
    const levelEl = side === 'player' ? el.playerLevel : el.npcLevel;
    const imageEl = side === 'player' ? el.playerImage : el.npcImage;
    const elementsEl = side === 'player' ? el.playerElements : el.npcElements;

    nameEl.textContent = creature.name;
    levelEl.textContent = `Lv. ${creature.level}`;
    imageEl.src = creature.image;
    imageEl.alt = creature.name;
    renderElementChips(elementsEl, creature.elementNames || []);
  }

  function updateHpDisplay(side, creature, animate) {
    const textEl = side === 'player' ? el.playerHpText : el.npcHpText;
    const fillEl = side === 'player' ? el.playerHpFill : el.npcHpFill;
    const start = Number(textEl.dataset.hp || creature.maxHp);
    const end = clamp(Number(creature.hp || 0), 0, creature.maxHp || 0);

    if (!animate) {
      textEl.dataset.hp = String(end);
      textEl.textContent = `${end}/${creature.maxHp}`;
      fillEl.style.width = `${creature.maxHp > 0 ? (end / creature.maxHp) * 100 : 0}%`;
      hpColor(fillEl, creature.maxHp > 0 ? (end / creature.maxHp) * 100 : 0);
      return Promise.resolve();
    }

    const duration = 340;
    const startedAt = performance.now();
    textEl.classList.add('is-ticking');

    return new Promise((resolve) => {
      function tick(now) {
        const progress = clamp((now - startedAt) / duration, 0, 1);
        const value = Math.round(start + (end - start) * progress);
        textEl.textContent = `${value}/${creature.maxHp}`;
        textEl.dataset.hp = String(value);
        const pct = creature.maxHp > 0 ? (value / creature.maxHp) * 100 : 0;
        fillEl.style.width = `${pct}%`;
        hpColor(fillEl, pct);

        if (progress < 1) {
          window.requestAnimationFrame(tick);
          return;
        }

        textEl.dataset.hp = String(end);
        textEl.textContent = `${end}/${creature.maxHp}`;
        textEl.classList.remove('is-ticking');
        resolve();
      }

      window.requestAnimationFrame(tick);
    });
  }

  function syncField() {
    const player = currentPlayer();
    const npc = currentNpc();
    if (!player || !npc) {
      return;
    }

    syncCreatureCard('player', player);
    syncCreatureCard('npc', npc);
    updateHpDisplay('player', player, false);
    updateHpDisplay('npc', npc, false);
  }

  function playSummon(side) {
    const host = side === 'player' ? el.player : el.npc;
    host.classList.remove('is-fainted');
    host.classList.remove('is-summoned');
    void host.offsetWidth;
    host.classList.add('is-summoned');
  }

  function spawnImpact(side) {
    const host = side === 'player' ? el.player : el.npc;
    const pulse = document.createElement('div');
    pulse.className = 'battle-impact';
    host.appendChild(pulse);
    pulse.addEventListener('animationend', () => pulse.remove(), { once: true });
  }

  function spawnNumber(side, value, kind) {
    const host = side === 'player' ? el.player : el.npc;
    const bubble = document.createElement('div');
    bubble.className = `battle-pop${kind ? ` ${kind}` : ''}`;
    bubble.textContent = kind === 'heal' ? `+${value}` : `-${value}`;
    if (Number(value) === 0) {
      bubble.textContent = '0';
      bubble.classList.add('zero');
    }
    host.appendChild(bubble);
    bubble.addEventListener('animationend', () => bubble.remove(), { once: true });
  }

  function multiplierFor(attackElementId, targetElementId) {
    const key = `${attackElementId}:${targetElementId}`;
    return Number(data.effectiveness[key] || 1);
  }

  function calculateDamage(move, target) {
    const targetElements = Array.isArray(target.elements) ? target.elements : [];
    let scaled = move.power;
    const applied = [];

    if (targetElements.length >= 1) {
      const firstMultiplier = multiplierFor(move.elementId, targetElements[0]);
      applied.push(firstMultiplier);
      scaled = move.power * firstMultiplier;
    }

    if (targetElements.length >= 2) {
      const secondMultiplier = multiplierFor(move.elementId, targetElements[1]);
      applied.push(secondMultiplier);
      scaled += move.power * secondMultiplier;
    }

    if (targetElements.length === 0) {
      applied.push(1);
    }

    const totalDamage = Math.max(0, Math.round(scaled - target.defense));
    const factor = move.power > 0 ? scaled / move.power : 1;

    let summary = '';
    if (applied.some((value) => value === 0)) {
      summary = 'The target shrugs the element off.';
    } else if (factor >= 1.5) {
      summary = 'Super effective against the target element mix.';
    } else if (factor <= 0.75) {
      summary = 'The target resists that element matchup.';
    }

    return { totalDamage, summary };
  }

  function pickNpcMove() {
    const npc = currentNpc();
    const moves = npc && Array.isArray(npc.moves) && npc.moves.length ? npc.moves : [fallbackMove];
    return moves[Math.floor(Math.random() * moves.length)] || fallbackMove;
  }

  async function performAttack(attacker, target, move, attackerSide) {
    const attackerEl = attackerSide === 'player' ? el.player : el.npc;
    const targetSide = attackerSide === 'player' ? 'npc' : 'player';
    const targetEl = targetSide === 'player' ? el.player : el.npc;

    setTurnIndicator(`${attacker.name} attacks`);
    showAnnouncer(`${attacker.name} uses ${move.name}`);
    addLog(`${attacker.name} used ${move.name}.`);

    attackerEl.classList.add('is-acting');
    await wait(140);
    attackerEl.classList.remove('is-acting');

    const result = calculateDamage(move, target);
    target.hp = Math.max(0, target.hp - result.totalDamage);

    spawnImpact(targetSide);
    spawnNumber(targetSide, result.totalDamage, result.totalDamage === 0 ? 'zero' : '');
    targetEl.classList.add('is-hit');
    await updateHpDisplay(targetSide, target, true);
    await wait(90);
    targetEl.classList.remove('is-hit');

    if (result.summary) {
      addLog(result.summary);
    }

    if (target.hp <= 0) {
      target.fainted = true;
      addLog(`${target.name} dropped to 0 HP.`);
      await handleFaint(targetSide);
      return true;
    }

    return false;
  }

  function menuOption(label, description, action, extra) {
    return {
      label,
      description,
      action,
      onFocus: extra && extra.onFocus ? extra.onFocus : null,
      quit: Boolean(extra && extra.quit),
      disabled: Boolean(extra && extra.disabled),
    };
  }

  function selectButton(button) {
    const buttons = el.menu.querySelectorAll('.battle-option');
    buttons.forEach((item) => item.classList.remove('is-selected'));
    if (button) {
      button.classList.add('is-selected');
    }
  }

  function renderMenu(config) {
    const options = Array.isArray(config.options) ? config.options : [];
    state.menuKey = config.key || 'menu';
    el.menuKicker.textContent = config.kicker || 'Battle Menu';
    el.menuTitle.textContent = config.title || 'Choose a command';
    el.menu.innerHTML = '';

    const wrap = document.createElement('div');
    wrap.className = config.layout === 'list' ? 'battle-menu-list' : 'battle-menu-grid';

    options.forEach((option, index) => {
      const button = document.createElement('button');
      button.type = 'button';
      button.className = `battle-option${option.quit ? ' quit' : ''}${option.disabled ? ' is-disabled' : ''}`;
      button.innerHTML = `
        <span class="battle-option-title">${escapeHtml(option.label)}</span>
        <span class="battle-option-desc">${escapeHtml(option.description || '')}</span>
      `;
      if (option.disabled) {
        button.disabled = true;
      }

      const focusOption = () => {
        selectButton(button);
        if (typeof option.onFocus === 'function') {
          option.onFocus();
        }
      };

      button.addEventListener('focus', focusOption);
      button.addEventListener('mouseenter', focusOption);
      button.addEventListener('click', () => {
        if (state.locked || option.disabled || typeof option.action !== 'function') {
          return;
        }
        option.action();
      });

      wrap.appendChild(button);
      if (index === 0) {
        window.setTimeout(() => {
          button.focus();
          focusOption();
        }, 0);
      }
    });

    el.menu.appendChild(wrap);
    state.locked = false;
  }

  function renderRootMenu() {
    state.forceSwitch = false;
    setDetail(defaultDetailHtml());
    renderMenu({
      key: 'root',
      kicker: 'Battle Menu',
      title: 'Choose a command',
      options: [
        menuOption('Fight', 'Open your active creature move set.', openFightMenu),
        menuOption('Item', 'Use a healing item from your battle bag.', openItemsMenu),
        menuOption('Creatures', 'Inspect stats or switch your active creature.', () => openCreaturesMenu(false)),
        menuOption('Flee', 'End the encounter and return to the games hall.', () => fleeBattle('You fled the encounter.')),
      ],
    });
  }

  function openFightMenu() {
    const player = currentPlayer();
    const moves = player && Array.isArray(player.moves) && player.moves.length ? player.moves : [fallbackMove];
    setDetail(moveDetailHtml(moves[0]));
    renderMenu({
      key: 'fight',
      kicker: 'Fight',
      title: 'Pick an attack',
      options: [
        ...moves.slice(0, 4).map((move) => menuOption(
          move.name,
          `${move.elementName || 'Neutral'} - ${move.power} power`,
          () => resolveRound(move),
          { onFocus: () => setDetail(moveDetailHtml(move)) }
        )),
        menuOption('Quit', 'Return to the main battle menu.', renderRootMenu, { quit: true }),
      ],
    });
  }

  function openItemsMenu() {
    const usableItems = state.items.filter((item) => item.quantity > 0);
    if (!usableItems.length) {
      setDetail('<p class="battle-detail-empty">You do not have any healing items available for this fight.</p>');
      renderMenu({
        key: 'items',
        kicker: 'Items',
        title: 'Battle bag',
        layout: 'list',
        options: [
          menuOption('No battle items', 'Nothing usable right now.', null, { disabled: true }),
          menuOption('Quit', 'Return to the main battle menu.', renderRootMenu, { quit: true }),
        ],
      });
      return;
    }

    setDetail(itemDetailHtml(usableItems[0]));
    renderMenu({
      key: 'items',
      kicker: 'Items',
      title: 'Battle bag',
      layout: 'list',
      options: [
        ...usableItems.map((item) => menuOption(
          item.name,
          `${item.quantity} left - heals ${item.heal} HP`,
          () => useItem(item),
          { onFocus: () => setDetail(itemDetailHtml(item)) }
        )),
        menuOption('Quit', 'Return to the main battle menu.', renderRootMenu, { quit: true }),
      ],
    });
  }

  function openCreaturesMenu(forceSwitch) {
    const team = state.playerTeam;
    const firstCreature = team[0];
    if (firstCreature) {
      setDetail(creatureDetailHtml(firstCreature, state.playerIndex === 0));
    }

    renderMenu({
      key: forceSwitch ? 'force-creatures' : 'creatures',
      kicker: 'Creatures',
      title: forceSwitch ? 'Choose your next creature' : 'Inspect or switch creatures',
      layout: 'list',
      options: [
        ...team.map((creature, index) => {
          const current = index === state.playerIndex;
          const hpText = creature.hp > 0 ? `${creature.hp}/${creature.maxHp} HP` : 'Unable to battle';
          return menuOption(
            `${creature.name}${current ? ' (active)' : ''}`,
            `${hpText} - SPD ${creature.speed}`,
            () => openCreatureChoice(index, forceSwitch),
            { onFocus: () => setDetail(creatureDetailHtml(creature, current)) }
          );
        }),
        menuOption(
          'Quit',
          forceSwitch ? 'Retreat from the battle.' : 'Return to the main battle menu.',
          forceSwitch ? () => fleeBattle('You left the battle instead of choosing a replacement.') : renderRootMenu,
          { quit: true }
        ),
      ],
    });
  }

  function openCreatureChoice(index, forceSwitch) {
    const creature = state.playerTeam[index];
    const isActive = index === state.playerIndex;
    setDetail(creatureDetailHtml(creature, isActive));

    const options = [];
    if (creature.hp <= 0) {
      options.push(menuOption('Unable to battle', 'This creature has no HP left.', null, { disabled: true }));
    } else if (isActive && !forceSwitch) {
      options.push(menuOption('Already active', 'This creature is already on the field.', null, { disabled: true }));
    } else {
      options.push(menuOption('Switch in', 'Send this creature onto the field.', () => switchCreature(index, forceSwitch)));
    }

    options.push(menuOption('Back', 'Return to your creature list.', () => openCreaturesMenu(forceSwitch)));
    options.push(menuOption(
      'Quit',
      forceSwitch ? 'Retreat from the battle.' : 'Return to the main battle menu.',
      forceSwitch ? () => fleeBattle('You left the battle instead of choosing a replacement.') : renderRootMenu,
      { quit: true }
    ));

    renderMenu({
      key: 'creature-choice',
      kicker: 'Creatures',
      title: creature.name,
      layout: 'list',
      options,
    });
  }

  function openPrompt(title, text, options) {
    setDetail(`<h3 class="battle-detail-title">${escapeHtml(title)}</h3><p class="battle-detail-empty">${escapeHtml(text)}</p>`);
    renderMenu({
      key: 'prompt',
      kicker: 'Decision',
      title,
      layout: 'list',
      options,
    });
  }

  async function postBattleAction(params) {
    const response = await fetch(window.location.href, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams(params),
    });
    return response.json();
  }

  async function useItem(item) {
    const target = currentPlayer();
    if (!target) {
      return;
    }
    if (target.hp >= target.maxHp) {
      addLog(`${target.name} already has full HP.`);
      openItemsMenu();
      return;
    }

    state.locked = true;
    setTurnIndicator('Using item');
    showAnnouncer(`Using ${item.name}`);

    let result;
    try {
      result = await postBattleAction({
        battle_action: 'use_item',
        item_id: String(item.id),
        battle_token: String(data.token || ''),
      });
    } catch (error) {
      addLog('The item action failed to sync. Try again.');
      openItemsMenu();
      return;
    }

    if (!result || !result.ok) {
      addLog(result && result.message ? result.message : 'The item could not be used.');
      openItemsMenu();
      return;
    }

    item.quantity = Number(result.quantity || 0);
    target.hp = Math.min(target.maxHp, target.hp + item.heal);
    addLog(`You used ${item.name} on ${target.name}.`);
    spawnNumber('player', item.heal, 'heal');
    await updateHpDisplay('player', target, true);
    await wait(120);

    if (!state.battleEnded && currentNpc() && currentNpc().hp > 0) {
      const npcMove = pickNpcMove();
      await performAttack(currentNpc(), target, npcMove, 'npc');
    }

    if (!state.battleEnded && !state.forceSwitch && currentPlayer() && currentPlayer().hp > 0 && currentNpc() && currentNpc().hp > 0) {
      renderRootMenu();
    }
  }

  async function switchCreature(index, forceSwitch) {
    const creature = state.playerTeam[index];
    if (!creature || creature.hp <= 0) {
      addLog('That creature cannot battle right now.');
      openCreaturesMenu(forceSwitch);
      return;
    }

    state.locked = true;
    state.forceSwitch = false;
    state.playerIndex = index;
    syncField();
    playSummon('player');
    showAnnouncer(`${creature.name}, to the front!`);
    addLog(`You sent out ${creature.name}.`);
    await wait(250);

    if (forceSwitch) {
      renderRootMenu();
      return;
    }

    if (!state.battleEnded && currentNpc() && currentNpc().hp > 0) {
      const npcMove = pickNpcMove();
      await performAttack(currentNpc(), creature, npcMove, 'npc');
    }

    if (!state.battleEnded && currentPlayer() && currentPlayer().hp > 0 && currentNpc() && currentNpc().hp > 0 && !state.forceSwitch) {
      renderRootMenu();
    }
  }

  async function handleFaint(side) {
    const host = side === 'player' ? el.player : el.npc;
    host.classList.add('is-fainted');
    await wait(220);

    if (side === 'npc') {
      const nextIndex = firstLivingIndex(state.trainerTeam);
      if (nextIndex === -1) {
        await winBattle();
        return;
      }

      state.trainerIndex = nextIndex;
      syncField();
      playSummon('npc');
      const incoming = currentNpc();
      if (incoming) {
        addLog(`${data.trainer.name} sent out ${incoming.name}.`);
        showAnnouncer(`${incoming.name} enters the field!`);
      }
      await wait(220);
      if (!state.battleEnded) {
        renderRootMenu();
      }
      return;
    }

    const nextIndex = firstLivingIndex(state.playerTeam);
    if (nextIndex === -1) {
      await loseBattle();
      return;
    }

    state.forceSwitch = true;
    const fallen = state.playerTeam[state.playerIndex];
    const fallenName = fallen ? fallen.name : 'Your creature';
    addLog(`${fallenName} can no longer fight.`);
    openPrompt(
      'Choose your next creature',
      `${fallenName} is down. Do you want to send out another creature?`,
      [
        menuOption('Yes', 'Open the creature roster.', () => openCreaturesMenu(true)),
        menuOption('No', 'Retreat from the encounter.', () => fleeBattle('You chose to retreat from the trainer battle.')),
      ]
    );
  }

  async function resolveRound(playerMove) {
    const player = currentPlayer();
    const npc = currentNpc();
    if (!player || !npc || state.locked || state.battleEnded) {
      return;
    }

    state.locked = true;
    const npcMove = pickNpcMove();
    const playerActsFirst = player.speed >= npc.speed;
    const turnOrder = playerActsFirst
      ? [
          { side: 'player', move: playerMove },
          { side: 'npc', move: npcMove },
        ]
      : [
          { side: 'npc', move: npcMove },
          { side: 'player', move: playerMove },
        ];

    for (let index = 0; index < turnOrder.length; index += 1) {
      if (state.battleEnded || state.forceSwitch) {
        return;
      }

      const step = turnOrder[index];
      const attacker = step.side === 'player' ? currentPlayer() : currentNpc();
      const target = step.side === 'player' ? currentNpc() : currentPlayer();

      if (!attacker || !target || attacker.hp <= 0 || target.hp <= 0) {
        continue;
      }

      const fainted = await performAttack(attacker, target, step.move, step.side);
      if (fainted) {
        return;
      }
    }

    if (!state.battleEnded && !state.forceSwitch && currentPlayer() && currentPlayer().hp > 0 && currentNpc() && currentNpc().hp > 0) {
      renderRootMenu();
    }
  }

  async function fleeBattle(message) {
    if (state.battleEnded) {
      return;
    }
    state.battleEnded = true;
    state.locked = true;
    setTurnIndicator('Retreating');
    addLog(message || 'You fled the encounter.');
    showAnnouncer('Battle over');
    await wait(900);
    window.location.href = data.returnUrl || 'index.php?pg=games';
  }

  async function loseBattle() {
    if (state.battleEnded) {
      return;
    }
    state.battleEnded = true;
    state.locked = true;
    setTurnIndicator('Defeat');
    addLog('Your team has fallen. The trainer battle is over.');
    showAnnouncer('Defeat');
    await wait(1500);
    window.location.href = data.returnUrl || 'index.php?pg=games';
  }

  async function winBattle() {
    if (state.battleEnded) {
      return;
    }

    state.battleEnded = true;
    state.locked = true;
    setTurnIndicator('Victory');
    addLog(`${data.trainer.displayName}: ${data.trainer.defeatLine}`);
    addLog(`You received ${data.trainer.defeatCurrency} ${currencyLabel}.`);
    showAnnouncer('Victory!');

    if (!state.awarding) {
      state.awarding = true;
      try {
        const result = await postBattleAction({
          battle_action: 'award_victory',
          trainer_id: String(data.trainer.id),
          battle_token: String(data.token || ''),
        });
        if (result && result.ok && typeof window.updateCurrencyDisplay === 'function') {
          window.updateCurrencyDisplay({ cash: Number(result.cash || 0) });
        }
      } catch (error) {
        addLog('The reward sync failed. Refresh if the wallet did not update.');
      }
    }

    setDetail(`
      <h3 class="battle-detail-title">Victory</h3>
      <p class="battle-detail-empty">
        ${escapeHtml(data.trainer.displayName || 'Trainer')} has been defeated. The reward has been added to your wallet
        and the encounter will close in a moment.
      </p>
    `);

    renderMenu({
      key: 'victory',
      kicker: 'Victory',
      title: 'Battle complete',
      layout: 'list',
      options: [
        menuOption('Return to Games', 'Leave the battlefield now.', () => {
          window.location.href = data.returnUrl || 'index.php?pg=games';
        }),
      ],
    });

    window.setTimeout(() => {
      window.location.href = data.returnUrl || 'index.php?pg=games';
    }, 2400);
  }

  function bindKeyboardNavigation() {
    document.addEventListener('keydown', (event) => {
      if (!root.contains(document.activeElement) && !el.menu.contains(document.activeElement)) {
        return;
      }

      if (state.locked || state.battleEnded) {
        return;
      }

      const buttons = Array.from(el.menu.querySelectorAll('.battle-option:not(.is-disabled)'));
      if (!buttons.length) {
        return;
      }

      const currentIndex = buttons.indexOf(document.activeElement);
      if (event.key === 'ArrowRight' || event.key === 'ArrowDown') {
        event.preventDefault();
        const nextIndex = currentIndex >= 0 ? (currentIndex + 1) % buttons.length : 0;
        buttons[nextIndex].focus();
        return;
      }

      if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') {
        event.preventDefault();
        const nextIndex = currentIndex >= 0 ? (currentIndex - 1 + buttons.length) % buttons.length : buttons.length - 1;
        buttons[nextIndex].focus();
        return;
      }

      if (event.key === 'Escape') {
        if (!state.forceSwitch && (state.menuKey === 'fight' || state.menuKey === 'items' || state.menuKey === 'creatures' || state.menuKey === 'creature-choice')) {
          event.preventDefault();
          renderRootMenu();
        }
      }
    });
  }

  async function startEncounter() {
    if (state.menuKey === 'started') {
      return;
    }

    state.menuKey = 'started';
    el.start.disabled = true;
    state.locked = true;
    syncField();
    el.banner.classList.add('is-live');
    el.intro.classList.add('is-hidden');
    showAnnouncer('Trainer encounter!');
    await wait(180);
    addLog(`${data.trainer.displayName} steps into your path.`);
    addLog(data.trainer.encounterLine);

    playSummon('npc');
    await wait(220);
    if (currentNpc()) {
      addLog(`${data.trainer.name} sent out ${currentNpc().name}.`);
    }

    playSummon('player');
    await wait(240);
    if (currentPlayer()) {
      addLog(`Go, ${currentPlayer().name}!`);
    }

    setDetail(defaultDetailHtml());
    renderRootMenu();
  }

  function boot() {
    syncField();
    setTurnIndicator('Awaiting clash');
    setDetail(defaultDetailHtml());
    addLog('A trainer battle is about to begin.');
    el.start.addEventListener('click', startEncounter);
    bindKeyboardNavigation();
  }

  boot();
})();
