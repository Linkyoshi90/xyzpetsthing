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
    stage: root.querySelector('.battle-stage'),
  };

  const wait = (ms) => new Promise((resolve) => window.setTimeout(resolve, ms));
  const clamp = (value, min, max) => Math.max(min, Math.min(max, value));
  const currentPlayer = () => state.playerTeam[state.playerIndex] || null;
  const currentNpc = () => state.trainerTeam[state.trainerIndex] || null;
  const firstLivingIndex = (team) => team.findIndex((creature) => creature.hp > 0);
  const currencyLabel = typeof data.currencyLabel === 'string' && data.currencyLabel ? data.currencyLabel : 'Dosh';
  const fallbackMove = { id: 0, key: 'tackle', name: 'Tackle', category: 'physical', contact: true, power: 40, elementId: 1, elementName: 'Vulgaris' };
  const isWildBattle = data.battleKind === 'wild';
  const elementPalettes = {
    1: { core: '#f8fafc', glow: 'rgba(255, 255, 255, 0.56)' },
    2: { core: '#ff8a3d', glow: 'rgba(255, 116, 48, 0.58)' },
    3: { core: '#4cc9f0', glow: 'rgba(76, 201, 240, 0.56)' },
    4: { core: '#ffe066', glow: 'rgba(255, 224, 102, 0.62)' },
    5: { core: '#63d471', glow: 'rgba(99, 212, 113, 0.55)' },
    6: { core: '#bde0fe', glow: 'rgba(189, 224, 254, 0.54)' },
    8: { core: '#b8a58f', glow: 'rgba(184, 165, 143, 0.52)' },
    9: { core: '#d6a4ff', glow: 'rgba(214, 164, 255, 0.56)' },
    11: { core: '#b8f06a', glow: 'rgba(184, 240, 106, 0.52)' },
    12: { core: '#ffcf99', glow: 'rgba(255, 207, 153, 0.55)' },
    13: { core: '#d7f7ff', glow: 'rgba(125, 226, 255, 0.64)' },
    15: { core: '#8388a8', glow: 'rgba(131, 136, 168, 0.55)' },
    17: { core: '#9ad0ff', glow: 'rgba(154, 208, 255, 0.56)' },
  };

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

  function prefersReducedMotion() {
    return window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  }

  async function runAnimation(node, keyframes, options) {
    if (!node || typeof node.animate !== 'function' || prefersReducedMotion()) {
      await wait(Number(options && options.duration) || 120);
      return;
    }

    const animation = node.animate(keyframes, options);
    try {
      await animation.finished;
    } catch (error) {
      // The node may be removed if the round ends early.
    }
  }

  function moveKey(move) {
    return String((move && (move.key || move.moveKey || move.name)) || '')
      .trim()
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, '_')
      .replace(/^_+|_+$/g, '');
  }

  function paletteForMove(move) {
    return elementPalettes[Number(move && move.elementId)] || elementPalettes[1];
  }

  function effectHost() {
    return el.stage || root;
  }

  function makeEffectNode(tagName) {
    const node = document.createElement(tagName || 'div');
    node.setAttribute('aria-hidden', 'true');
    Object.assign(node.style, {
      position: 'absolute',
      pointerEvents: 'none',
      zIndex: '15',
    });
    effectHost().appendChild(node);
    return node;
  }

  function combatPoint(side, role) {
    const host = side === 'player' ? el.player : el.npc;
    const shell = host ? host.querySelector('.battle-creature-shell') : null;
    const source = shell || host;
    const stageRect = effectHost().getBoundingClientRect();
    const rect = source.getBoundingClientRect();
    const sourceRatio = side === 'player' ? 0.72 : 0.28;
    const targetRatio = side === 'player' ? 0.58 : 0.42;
    const xRatio = role === 'source' ? sourceRatio : targetRatio;

    return {
      x: rect.left - stageRect.left + (rect.width * xRatio),
      y: rect.top - stageRect.top + (rect.height * 0.56),
    };
  }

  async function playSlashAnimation(targetSide, move) {
    const center = combatPoint(targetSide, 'target');
    const palette = paletteForMove(move);
    const angle = targetSide === 'player' ? -24 : 24;
    const slash = makeEffectNode('div');
    Object.assign(slash.style, {
      left: `${center.x - 42}px`,
      top: `${center.y - 8}px`,
      width: '84px',
      height: '14px',
      borderRadius: '999px',
      background: `linear-gradient(90deg, rgba(255, 255, 255, 0), ${palette.core}, rgba(255, 255, 255, 0))`,
      boxShadow: `0 0 22px ${palette.glow}`,
      transform: `rotate(${angle}deg) scaleX(0.2)`,
    });

    await runAnimation(slash, [
      { opacity: 0, transform: `rotate(${angle}deg) scaleX(0.2)` },
      { opacity: 1, transform: `rotate(${angle}deg) scaleX(1.12)` },
      { opacity: 0, transform: `rotate(${angle}deg) scaleX(0.72) translateY(-12px)` },
    ], { duration: 240, easing: 'cubic-bezier(0.2, 0.8, 0.25, 1)' });
    slash.remove();
  }

  async function playProjectileAnimation(attackerSide, targetSide, move) {
    const start = combatPoint(attackerSide, 'source');
    const end = combatPoint(targetSide, 'target');
    const palette = paletteForMove(move);
    const orb = makeEffectNode('div');
    Object.assign(orb.style, {
      left: `${start.x - 9}px`,
      top: `${start.y - 9}px`,
      width: '18px',
      height: '18px',
      borderRadius: '999px',
      background: `radial-gradient(circle at 35% 35%, #fff, ${palette.core} 48%, rgba(255, 255, 255, 0) 72%)`,
      boxShadow: `0 0 18px ${palette.glow}, 0 0 34px ${palette.glow}`,
    });

    await runAnimation(orb, [
      { opacity: 0, transform: 'translate3d(0, 0, 0) scale(0.45)' },
      { opacity: 1, transform: 'translate3d(0, 0, 0) scale(1)' },
      { opacity: 1, transform: `translate3d(${end.x - start.x}px, ${end.y - start.y}px, 0) scale(1.18)` },
      { opacity: 0, transform: `translate3d(${end.x - start.x}px, ${end.y - start.y}px, 0) scale(0.72)` },
    ], { duration: 360, easing: 'cubic-bezier(0.18, 0.84, 0.28, 1)' });
    orb.remove();
  }

  async function playIceBurst(targetSide) {
    const center = combatPoint(targetSide, 'target');
    const pieces = [];
    const ring = makeEffectNode('div');
    Object.assign(ring.style, {
      left: `${center.x - 48}px`,
      top: `${center.y - 48}px`,
      width: '96px',
      height: '96px',
      borderRadius: '999px',
      border: '2px solid rgba(204, 246, 255, 0.9)',
      background: 'radial-gradient(circle, rgba(230, 251, 255, 0.62), rgba(125, 226, 255, 0.18) 46%, rgba(125, 226, 255, 0) 72%)',
      boxShadow: '0 0 28px rgba(125, 226, 255, 0.52), inset 0 0 18px rgba(255, 255, 255, 0.72)',
    });
    pieces.push(runAnimation(ring, [
      { opacity: 0, transform: 'scale(0.35)' },
      { opacity: 1, transform: 'scale(0.85)' },
      { opacity: 0, transform: 'scale(1.5)' },
    ], { duration: 420, easing: 'ease-out' }).then(() => ring.remove()));

    for (let index = 0; index < 10; index += 1) {
      const angle = (Math.PI * 2 * index) / 10;
      const distance = 30 + ((index % 4) * 9);
      const shard = makeEffectNode('div');
      const size = 6 + ((index % 3) * 3);
      Object.assign(shard.style, {
        left: `${center.x - (size / 2)}px`,
        top: `${center.y - (size / 2)}px`,
        width: `${size}px`,
        height: `${size + 8}px`,
        borderRadius: '3px 3px 8px 8px',
        background: 'linear-gradient(180deg, #fff, #c7f5ff 55%, rgba(125, 226, 255, 0.36))',
        boxShadow: '0 0 14px rgba(125, 226, 255, 0.58)',
        transform: `rotate(${angle}rad) scale(0.45)`,
      });
      pieces.push(runAnimation(shard, [
        { opacity: 0, transform: `rotate(${angle}rad) translate3d(0, 0, 0) scale(0.35)` },
        { opacity: 1, transform: `rotate(${angle}rad) translate3d(${Math.cos(angle) * 14}px, ${Math.sin(angle) * 14}px, 0) scale(1)` },
        { opacity: 0, transform: `rotate(${angle}rad) translate3d(${Math.cos(angle) * distance}px, ${Math.sin(angle) * distance}px, 0) scale(0.35)` },
      ], { duration: 460, easing: 'cubic-bezier(0.18, 0.76, 0.28, 1)' }).then(() => shard.remove()));
    }

    await Promise.all(pieces);
  }

  async function playIceBeamAnimation(attackerSide, targetSide) {
    const start = combatPoint(attackerSide, 'source');
    const end = combatPoint(targetSide, 'target');
    const dx = end.x - start.x;
    const dy = end.y - start.y;
    const length = Math.max(24, Math.sqrt((dx * dx) + (dy * dy)));
    const angle = Math.atan2(dy, dx);
    const beam = makeEffectNode('div');
    Object.assign(beam.style, {
      left: `${start.x}px`,
      top: `${start.y - 7}px`,
      width: `${length}px`,
      height: '14px',
      borderRadius: '999px',
      transformOrigin: '0 50%',
      transform: `rotate(${angle}rad) scaleX(0)`,
      background: 'linear-gradient(90deg, rgba(255, 255, 255, 0), rgba(246, 253, 255, 0.96) 14%, rgba(158, 237, 255, 0.9) 58%, rgba(255, 255, 255, 0.78))',
      boxShadow: '0 0 16px rgba(125, 226, 255, 0.78), 0 0 34px rgba(125, 226, 255, 0.45)',
    });
    const beamAnimation = runAnimation(beam, [
      { opacity: 0, transform: `rotate(${angle}rad) scaleX(0)` },
      { opacity: 1, transform: `rotate(${angle}rad) scaleX(1)` },
      { opacity: 1, transform: `rotate(${angle}rad) scaleX(1)` },
      { opacity: 0, transform: `rotate(${angle}rad) scaleX(1)` },
    ], { duration: 420, easing: 'cubic-bezier(0.12, 0.82, 0.24, 1)' }).then(() => beam.remove());

    await wait(190);
    await Promise.all([beamAnimation, playIceBurst(targetSide)]);
  }

  async function playAttackAnimation(attackerSide, targetSide, move) {
    if (prefersReducedMotion()) {
      await wait(120);
      return;
    }

    if (moveKey(move) === 'ice_beam') {
      await playIceBeamAnimation(attackerSide, targetSide);
      return;
    }

    if (move && (move.contact || move.category === 'physical')) {
      await playSlashAnimation(targetSide, move);
      return;
    }

    await playProjectileAnimation(attackerSide, targetSide, move);
  }

  async function playDamageBlink(targetSide, move) {
    const image = targetSide === 'player' ? el.playerImage : el.npcImage;
    if (!image) {
      return;
    }

    const icy = moveKey(move) === 'ice_beam' || Number(move && move.elementId) === 13;
    const brightFrame = icy
      ? { opacity: 0.68, filter: 'brightness(1.9) saturate(0.55) drop-shadow(0 0 18px rgba(125, 226, 255, 0.9))' }
      : { opacity: 0.58, filter: 'brightness(1.85) saturate(0.72)' };

    await runAnimation(image, [
      { opacity: 1, filter: 'none' },
      brightFrame,
      { opacity: 1, filter: 'none' },
      brightFrame,
      { opacity: 1, filter: 'none' },
    ], { duration: 300, easing: 'steps(4, end)' });
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
    await wait(110);
    await playAttackAnimation(attackerSide, targetSide, move);
    attackerEl.classList.remove('is-acting');

    const result = calculateDamage(move, target);
    target.hp = Math.max(0, target.hp - result.totalDamage);

    spawnImpact(targetSide);
    spawnNumber(targetSide, result.totalDamage, result.totalDamage === 0 ? 'zero' : '');
    targetEl.classList.add('is-hit');
    const blink = playDamageBlink(targetSide, move);
    await updateHpDisplay(targetSide, target, true);
    await blink;
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
        addLog(isWildBattle ? `${incoming.name} presses forward.` : `${data.trainer.name} sent out ${incoming.name}.`);
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
        menuOption('No', 'Retreat from the encounter.', () => fleeBattle(isWildBattle ? 'You retreated from the wild encounter.' : 'You chose to retreat from the trainer battle.')),
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
    addLog(isWildBattle ? 'Your team has fallen. The wild encounter is over.' : 'Your team has fallen. The trainer battle is over.');
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
    addLog(isWildBattle ? data.trainer.defeatLine : `${data.trainer.displayName}: ${data.trainer.defeatLine}`);
    if (Number(data.trainer.defeatCurrency || 0) > 0) {
      addLog(`You received ${data.trainer.defeatCurrency} ${currencyLabel}.`);
    }
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
        ${escapeHtml(data.trainer.displayName || 'Opponent')} has been defeated.
        ${Number(data.trainer.defeatCurrency || 0) > 0 ? 'The reward has been added to your wallet and the encounter will close in a moment.' : 'The encounter will close in a moment.'}
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
    showAnnouncer(isWildBattle ? 'Wild encounter!' : 'Trainer encounter!');
    await wait(180);
    addLog(isWildBattle ? `${data.trainer.displayName} appears!` : `${data.trainer.displayName} steps into your path.`);
    addLog(data.trainer.encounterLine);

    playSummon('npc');
    await wait(220);
    if (currentNpc()) {
      addLog(isWildBattle ? `${currentNpc().name} takes the field.` : `${data.trainer.name} sent out ${currentNpc().name}.`);
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
    addLog(isWildBattle ? 'A wild creature battle is about to begin.' : 'A trainer battle is about to begin.');
    el.start.addEventListener('click', startEncounter);
    bindKeyboardNavigation();
  }

  boot();
})();
