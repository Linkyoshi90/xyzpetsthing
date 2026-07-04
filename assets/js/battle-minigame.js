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
    ability: pet.ability ? JSON.parse(JSON.stringify(pet.ability)) : null,
    abilityRuntime: { used: Object.create(null) },
    stages: { attack: 0, defense: 0, speed: 0, accuracy: 0 },
    status: null,
    fainted: false,
  }));

  const abilityEngine = window.BattleAbilityEngine || {
    prepareCreature: () => [],
    priorityFor: (creature, move) => Number(move?.priority || 0),
    modifyDamage: ({ damage }) => ({ damage, messages: [] }),
    afterDamage: () => ({ healing: 0, messages: [] }),
  };

  const state = {
    playerTeam: cloneTeam(data.playerTeam),
    trainerTeam: cloneTeam(data.trainerTeam),
    items: Array.isArray(data.items) ? data.items.map((item) => ({ ...item, quantity: Number(item.quantity || 0) })) : [],
    playerIndex: 0,
    trainerIndex: 0,
    locked: true,
    battleEnded: false,
    awarding: false,
    hpSyncing: null,
    hpSyncTimer: 0,
    hpVersion: 0,
    menuKey: 'root',
    forceSwitch: false,
    openingSelection: true,
  };

  const abilityStartMessages = [...state.playerTeam, ...state.trainerTeam]
    .flatMap((creature) => abilityEngine.prepareCreature(creature));

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
  const markPlayerHpChanged = () => {
    state.hpVersion += 1;
  };
  const firstLivingIndex = (team) => team.findIndex((creature) => creature.hp > 0);
  const currencyLabel = typeof data.currencyLabel === 'string' && data.currencyLabel ? data.currencyLabel : 'Dosh';
  const fallbackMove = { id: 0, key: 'tackle', name: 'Tackle', category: 'physical', contact: true, power: 40, elementId: 1, elementName: 'Vulgaris', accuracy: 100, effect: null, effectChance: 0 };
  const isWildBattle = data.battleKind === 'wild';
  const battlePostUrl = window.location.href;

  state.playerIndex = Math.max(0, firstLivingIndex(state.playerTeam));
  state.trainerIndex = Math.max(0, firstLivingIndex(state.trainerTeam));

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

  const statusInfo = {
    poison: { label: 'PSN', displayName: 'Poison', adjective: 'poisoned', color: '#a855f7', inflictMsg: '{name} was poisoned!' },
    venom: { label: 'VNM', displayName: 'Venom', adjective: 'envenomed', color: '#7c3aed', inflictMsg: '{name} was injected with venom!' },
    burn: { label: 'BRN', displayName: 'Burn', adjective: 'burned', color: '#f97316', inflictMsg: '{name} was burned!' },
    paralysis: { label: 'PAR', displayName: 'Paralysis', adjective: 'paralyzed', color: '#eab308', inflictMsg: '{name} is paralyzed! It may be unable to move!' },
    freeze: { label: 'FRZ', displayName: 'Freeze', adjective: 'frozen', color: '#38bdf8', inflictMsg: '{name} was frozen solid!' },
    sleep: { label: 'SLP', displayName: 'Sleep', adjective: 'asleep', color: '#94a3b8', inflictMsg: '{name} fell asleep!' },
    rage: { label: 'RGE', displayName: 'Rage', adjective: 'enraged', color: '#ef4444', inflictMsg: '{name} flew into a rage!' },
  };

  const statLabels = { attack: 'Attack', defense: 'Defense', speed: 'Speed', accuracy: 'accuracy' };

  const ailmentAliases = {
    poison: 'poison',
    venom: 'venom',
    burn: 'burn',
    paralyze: 'paralysis',
    paralysis: 'paralysis',
    freeze: 'freeze',
    sleep: 'sleep',
    rage: 'rage',
  };

  function creatureStages(creature) {
    if (!creature.stages || typeof creature.stages !== 'object') {
      creature.stages = { attack: 0, defense: 0, speed: 0, accuracy: 0 };
    }
    return creature.stages;
  }

  // Parses the moves.effect_key column into an actionable effect. Supported:
  // "curse", "<atk|def|spd|acc>_<up|down>_<stages>", and ailment names with an
  // optional legacy "_<percent>" suffix ("burn_10"). Chance comes from
  // effect_chance_percent; keys this build cannot act on return null.
  function parseMoveEffect(move) {
    const raw = String((move && move.effect) || '').trim().toLowerCase();
    if (!raw) {
      return null;
    }

    const chance = clamp(Number(move.effectChance || 0) || 100, 1, 100);
    if (raw === 'curse') {
      return {
        kind: 'stages',
        target: 'self',
        chance,
        changes: [
          { stat: 'attack', delta: 1 },
          { stat: 'defense', delta: 1 },
          { stat: 'speed', delta: -1 },
        ],
      };
    }

    const statMatch = raw.match(/^(atk|attack|def|defense|spd|speed|init|acc|accuracy)_(up|down)_(\d+)$/);
    if (statMatch) {
      const statNames = {
        atk: 'attack', attack: 'attack',
        def: 'defense', defense: 'defense',
        spd: 'speed', speed: 'speed', init: 'speed',
        acc: 'accuracy', accuracy: 'accuracy',
      };
      // Legacy keys encode the proc chance here ("speed_down_10"): anything
      // above 2 is not a stage count, so fall back to a single stage.
      const digits = Number(statMatch[3]);
      const magnitude = digits >= 1 && digits <= 2 ? digits : 1;
      return {
        kind: 'stages',
        target: statMatch[2] === 'down' ? 'enemy' : 'self',
        chance,
        changes: [{ stat: statNames[statMatch[1]], delta: statMatch[2] === 'down' ? -magnitude : magnitude }],
      };
    }

    const ailment = ailmentAliases[raw.replace(/_\d+$/, '')];
    if (ailment) {
      return { kind: 'ailment', target: 'enemy', chance, ailment };
    }

    return null;
  }

  function isStatusMove(move) {
    return String((move && move.category) || '') === 'status' || Number((move && move.power) || 0) <= 0;
  }

  function stageMultiplier(stage) {
    return stage >= 0 ? (2 + stage) / 2 : 2 / (2 - stage);
  }

  function accuracyStageMultiplier(stage) {
    return stage >= 0 ? (3 + stage) / 3 : 3 / (3 - stage);
  }

  // Battle-only view of a stat: base value scaled by stat stages plus status
  // modifiers. The underlying creature stats are never mutated, so nothing
  // here can leak out of the battle.
  function effectiveStat(creature, stat) {
    if (!creature) {
      return 0;
    }

    let value = Number(creature[stat] || 0) * stageMultiplier(Number(creatureStages(creature)[stat] || 0));
    const status = creature.status ? creature.status.key : null;
    if (stat === 'attack' && status === 'burn') {
      value *= 0.5;
    }
    if (stat === 'attack' && status === 'rage') {
      value *= 1.5;
    }
    if (stat === 'speed' && status === 'paralysis') {
      value *= 0.75;
    }

    return Math.max(1, Math.round(value));
  }

  function moveAccuracyFor(attacker, move) {
    const base = Number(move && move.accuracy) > 0 ? Number(move.accuracy) : 100;
    let chance = base * accuracyStageMultiplier(Number(creatureStages(attacker).accuracy || 0));
    if (attacker.status && attacker.status.key === 'rage') {
      chance *= 0.5;
    }
    return clamp(chance, 5, 100);
  }

  function applyStatStages(creature, changes) {
    const messages = [];
    const stages = creatureStages(creature);
    changes.forEach(({ stat, delta }) => {
      const before = Number(stages[stat] || 0);
      const after = clamp(before + delta, -6, 6);
      stages[stat] = after;
      const label = statLabels[stat] || stat;
      if (after === before) {
        messages.push(`${creature.name}'s ${label} can't go any ${delta > 0 ? 'higher' : 'lower'}!`);
      } else {
        messages.push(`${creature.name}'s ${label} ${delta > 0 ? 'rose' : 'fell'}${Math.abs(after - before) >= 2 ? ' sharply' : ''}!`);
      }
    });
    return messages;
  }

  function describeMoveEffect(move) {
    const effect = parseMoveEffect(move);
    if (!effect) {
      return '';
    }

    if (effect.kind === 'ailment') {
      const name = statusInfo[effect.ailment].displayName;
      return effect.chance >= 100 ? `Inflicts ${name}.` : `${Math.round(effect.chance)}% chance to inflict ${name}.`;
    }

    const parts = effect.changes.map(({ stat, delta }) => `${statLabels[stat] || stat} ${delta > 0 ? '+' : ''}${delta}`);
    return `${effect.target === 'self' ? 'User' : 'Target'}: ${parts.join(', ')}.`;
  }

  function ensureStatusBadge(side) {
    const levelEl = side === 'player' ? el.playerLevel : el.npcLevel;
    let badge = levelEl.parentElement.querySelector('.battle-status-tag');
    if (!badge) {
      badge = document.createElement('span');
      badge.className = 'battle-status-tag';
      Object.assign(badge.style, {
        marginLeft: '8px',
        padding: '1px 7px',
        borderRadius: '999px',
        fontSize: '11px',
        fontWeight: '700',
        letterSpacing: '0.08em',
        display: 'none',
        color: '#0b1120',
      });
      levelEl.parentElement.appendChild(badge);
    }
    return badge;
  }

  function updateStatusBadge(side, creature) {
    const badge = ensureStatusBadge(side);
    const key = creature && creature.status ? creature.status.key : null;
    if (!key || creature.hp <= 0) {
      badge.style.display = 'none';
      return;
    }
    badge.textContent = statusInfo[key].label;
    badge.style.background = statusInfo[key].color;
    badge.style.display = 'inline-block';
  }

  // Stat stages and rage do not survive leaving the field; persistent
  // ailments (poison, burn, sleep, ...) stay on the creature.
  function resetVolatileState(creature) {
    if (!creature) {
      return;
    }
    creature.stages = { attack: 0, defense: 0, speed: 0, accuracy: 0 };
    if (creature.status && creature.status.key === 'rage') {
      creature.status = null;
    }
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

  function statWithStage(creature, stat) {
    const effective = effectiveStat(creature, stat);
    const base = Number(creature[stat] || 0);
    if (effective === base) {
      return String(base);
    }
    return `${effective} <span class="battle-feed-label">(${base})</span>`;
  }

  function creatureDetailHtml(creature, isActive) {
    const moves = Array.isArray(creature.moves) && creature.moves.length
      ? creature.moves.map((move) => `
          <span class="battle-detail-move">${escapeHtml(move.name)} - ${escapeHtml(move.elementName || 'Neutral')} - ${isStatusMove(move) ? 'Status' : `${move.power} power`}</span>
        `).join('')
      : '<span class="battle-detail-move">No moves assigned</span>';

    const ability = creature.ability
      ? `<div class="battle-detail-ability"><strong>${escapeHtml(creature.ability.name)}</strong><br>${escapeHtml(creature.ability.description || creature.ability.battle?.summary || '')}</div>`
      : '<div class="battle-detail-ability"><strong>Ability</strong><br>None assigned</div>';

    const status = creature.status
      ? `<div class="battle-detail-ability"><strong>Status</strong><br>${escapeHtml(statusInfo[creature.status.key].displayName)}</div>`
      : '';

    return `
      <h3 class="battle-detail-title">${escapeHtml(creature.name)}${isActive ? ' <span class="battle-feed-label">Active</span>' : ''}</h3>
      <p class="battle-detail-empty">${escapeHtml(creature.species || 'Creature')} - ${escapeHtml((creature.elementNames || []).join(' / ') || 'Neutral')}</p>
      <div class="battle-detail-stats">
        <div class="battle-detail-stat"><strong>HP</strong><br>${creature.hp}/${creature.maxHp}</div>
        <div class="battle-detail-stat"><strong>Attack</strong><br>${statWithStage(creature, 'attack')}</div>
        <div class="battle-detail-stat"><strong>Defense</strong><br>${statWithStage(creature, 'defense')}</div>
        <div class="battle-detail-stat"><strong>Speed</strong><br>${statWithStage(creature, 'speed')}</div>
      </div>
      <div class="battle-detail-moves">${moves}</div>
      ${status}
      ${ability}
    `;
  }

  function moveDetailHtml(move) {
    const player = currentPlayer();
    const npc = currentNpc();
    const statusMove = isStatusMove(move);
    const breakdown = !statusMove && npc ? calculateDamage(move, npc, player, true) : { totalDamage: statusMove ? 0 : move.power, summary: '' };
    const effectText = describeMoveEffect(move);

    return `
      <h3 class="battle-detail-title">${escapeHtml(move.name)}</h3>
      <p class="battle-detail-empty">
        ${escapeHtml(move.elementName || 'Neutral')} ${statusMove ? 'status move.' : `move. Base power ${move.power}.`}
        ${effectText ? escapeHtml(effectText) : ''}
        ${breakdown.summary ? escapeHtml(breakdown.summary) : ''}
      </p>
      <div class="battle-detail-stats">
        <div class="battle-detail-stat"><strong>Projected damage</strong><br>${statusMove ? '—' : breakdown.totalDamage}</div>
        <div class="battle-detail-stat"><strong>Your speed</strong><br>${player ? effectiveStat(player, 'speed') : 0}</div>
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
    updateStatusBadge('player', player);
    updateStatusBadge('npc', npc);
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

  function calculateDamage(move, target, attacker, preview = false) {
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

    const attackContribution = Math.round((attacker ? effectiveStat(attacker, 'attack') : 0) * 0.5);
    const baseDamage = Math.max(0, Math.round(scaled + attackContribution - effectiveStat(target, 'defense')));
    const abilityResult = abilityEngine.modifyDamage({
      attacker,
      target,
      move,
      damage: baseDamage,
      preview,
    });
    const totalDamage = Math.max(0, Number(abilityResult.damage || 0));
    const factor = move.power > 0 ? scaled / move.power : 1;

    let summary = '';
    if (applied.some((value) => value === 0)) {
      summary = 'The target shrugs the element off.';
    } else if (factor >= 1.5) {
      summary = 'Super effective against the target element mix.';
    } else if (factor <= 0.75) {
      summary = 'The target resists that element matchup.';
    }

    return { totalDamage, summary, abilityMessages: abilityResult.messages || [] };
  }

  function pickNpcMove() {
    const npc = currentNpc();
    const moves = npc && Array.isArray(npc.moves) && npc.moves.length ? npc.moves : [fallbackMove];
    return moves[Math.floor(Math.random() * moves.length)] || fallbackMove;
  }

  // Returns true when the attacker's status stopped it from acting this turn.
  async function statusPreventsAction(attacker, attackerSide) {
    const status = attacker.status;
    if (!status) {
      return false;
    }

    if (status.key === 'paralysis') {
      if (Math.random() * 100 < 25) {
        setTurnIndicator(`${attacker.name} is paralyzed`);
        showAnnouncer(`${attacker.name} can't move!`);
        addLog(`${attacker.name} is paralyzed and can't move!`);
        await wait(420);
        return true;
      }
      return false;
    }

    if (status.key === 'freeze') {
      if (Math.random() * 100 < status.recoverChance) {
        attacker.status = null;
        updateStatusBadge(attackerSide, attacker);
        addLog(`${attacker.name} thawed out!`);
        return false;
      }
      status.recoverChance += 10;
      setTurnIndicator(`${attacker.name} is frozen`);
      showAnnouncer(`${attacker.name} is frozen solid!`);
      addLog(`${attacker.name} is frozen solid and can't move!`);
      await wait(420);
      return true;
    }

    if (status.key === 'sleep') {
      if (Math.random() * 100 < status.recoverChance) {
        attacker.status = null;
        updateStatusBadge(attackerSide, attacker);
        addLog(`${attacker.name} woke up!`);
        return false;
      }
      status.recoverChance += 10;
      setTurnIndicator(`${attacker.name} is asleep`);
      addLog(`${attacker.name} is fast asleep.`);
      const healing = Math.min(
        Math.max(0, attacker.maxHp - attacker.hp),
        Math.max(1, Math.round(attacker.maxHp * 0.05))
      );
      if (healing > 0) {
        attacker.hp += healing;
        if (attackerSide === 'player') {
          markPlayerHpChanged();
          queuePlayerHpSync();
        }
        addLog(`${attacker.name} recovers ${healing} HP in its sleep.`);
        spawnNumber(attackerSide, healing, 'heal');
        await updateHpDisplay(attackerSide, attacker, true);
      }
      await wait(300);
      return true;
    }

    return false;
  }

  async function applyMoveEffect(attacker, target, effect, attackerSide, targetSide, verbose) {
    const recipient = effect.target === 'self' ? attacker : target;
    const recipientSide = effect.target === 'self' ? attackerSide : targetSide;
    if (!recipient || recipient.hp <= 0) {
      return false;
    }

    if (effect.kind === 'stages') {
      applyStatStages(recipient, effect.changes).forEach((message) => addLog(message));
      await wait(260);
      return true;
    }

    if (effect.kind === 'ailment') {
      if (recipient.status) {
        if (verbose) {
          addLog(`${recipient.name} is already ${statusInfo[recipient.status.key].adjective}.`);
        }
        return false;
      }
      recipient.status = { key: effect.ailment, recoverChance: 10 };
      updateStatusBadge(recipientSide, recipient);
      addLog(statusInfo[effect.ailment].inflictMsg.replace('{name}', recipient.name));
      await wait(260);
      return true;
    }

    return false;
  }

  async function performAttack(attacker, target, move, attackerSide) {
    const attackerEl = attackerSide === 'player' ? el.player : el.npc;
    const targetSide = attackerSide === 'player' ? 'npc' : 'player';
    const targetEl = targetSide === 'player' ? el.player : el.npc;

    if (await statusPreventsAction(attacker, attackerSide)) {
      return false;
    }

    setTurnIndicator(`${attacker.name} attacks`);
    showAnnouncer(`${attacker.name} uses ${move.name}`);
    addLog(`${attacker.name} used ${move.name}.`);

    const effect = parseMoveEffect(move);
    const selfTargeted = isStatusMove(move) && effect && effect.target === 'self';

    if (!selfTargeted && Math.random() * 100 >= moveAccuracyFor(attacker, move)) {
      attackerEl.classList.add('is-acting');
      await wait(220);
      attackerEl.classList.remove('is-acting');
      addLog(`${attacker.name}'s attack missed!`);
      await wait(260);
      return false;
    }

    attackerEl.classList.add('is-acting');
    await wait(110);
    await playAttackAnimation(attackerSide, targetSide, move);
    attackerEl.classList.remove('is-acting');

    if (isStatusMove(move)) {
      const applied = effect
        ? await applyMoveEffect(attacker, target, effect, attackerSide, targetSide, true)
        : false;
      if (!applied) {
        addLog('But it failed!');
      }
      return false;
    }

    const result = calculateDamage(move, target, attacker, false);
    (result.abilityMessages || []).forEach((message) => addLog(message));
    const hpBefore = target.hp;
    target.hp = Math.max(0, target.hp - result.totalDamage);
    if (targetSide === 'player' && target.hp !== hpBefore) {
      markPlayerHpChanged();
      queuePlayerHpSync();
    }

    spawnImpact(targetSide);
    spawnNumber(targetSide, result.totalDamage, result.totalDamage === 0 ? 'zero' : '');
    targetEl.classList.add('is-hit');
    const blink = playDamageBlink(targetSide, move);
    await updateHpDisplay(targetSide, target, true);
    await blink;
    await wait(90);
    targetEl.classList.remove('is-hit');

    const aftermath = abilityEngine.afterDamage({
      attacker,
      target,
      move,
      damage: result.totalDamage,
      preview: false,
    });
    (aftermath.messages || []).forEach((message) => addLog(message));
    if (aftermath.healing > 0) {
      if (attackerSide === 'player') {
        markPlayerHpChanged();
        queuePlayerHpSync();
      }
      spawnNumber(attackerSide, aftermath.healing, 'heal');
      await updateHpDisplay(attackerSide, attacker, true);
    }

    if (result.summary) {
      addLog(result.summary);
    }

    // Burning moves loosen the ice: +50% thaw rate.
    if (target.status && target.status.key === 'freeze' && Number(move.elementId) === 2 && target.hp > 0) {
      target.status.recoverChance += 50;
      addLog(`The heat softens the ice around ${target.name}!`);
    }

    if (target.hp <= 0) {
      target.fainted = true;
      addLog(`${target.name} dropped to 0 HP.`);
      await handleFaint(targetSide);
      return true;
    }

    if (effect && Math.random() * 100 < effect.chance) {
      await applyMoveEffect(attacker, target, effect, attackerSide, targetSide, false);
    }

    return false;
  }

  function menuOption(label, description, action, extra) {
    return {
      label,
      description,
      action,
      onFocus: extra && extra.onFocus ? extra.onFocus : null,
      profile: extra && extra.profile ? extra.profile : null,
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
    wrap.className = config.layout === 'list'
      ? 'battle-menu-list'
      : config.layout === 'profiles'
        ? 'battle-creature-grid'
        : 'battle-menu-grid';

    const firstFocusableIndex = options.findIndex((option) => !option.disabled);

    options.forEach((option, index) => {
      const button = document.createElement('button');
      button.type = 'button';
      button.className = `battle-option${option.profile ? ' battle-option-profile' : ''}${option.quit ? ' quit' : ''}${option.disabled ? ' is-disabled' : ''}`;
      button.innerHTML = option.profile
        ? `
          <span class="battle-profile-portrait">
            <img src="${escapeHtml(option.profile.image || '')}" alt="">
          </span>
          <span class="battle-profile-copy">
            <span class="battle-option-title">${escapeHtml(option.label)}</span>
            <span class="battle-profile-species">${escapeHtml(option.profile.species || 'Creature')} · Lv. ${Number(option.profile.level || 1)}</span>
            <span class="battle-option-desc">${escapeHtml(option.description || '')}</span>
          </span>
        `
        : `
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
      if (index === firstFocusableIndex) {
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
    state.openingSelection = false;
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

  function openOpeningCreatureMenu() {
    const firstAvailable = state.playerTeam.find((creature) => creature.hp > 0);
    if (firstAvailable) {
      setDetail(creatureDetailHtml(firstAvailable, false));
    }

    renderMenu({
      key: 'opening-creatures',
      kicker: 'Your Party',
      title: 'Who will you send out?',
      layout: 'profiles',
      options: state.playerTeam.map((creature, index) => {
        const available = creature.hp > 0;
        return menuOption(
          creature.name,
          available ? `${creature.hp}/${creature.maxHp} HP · SPD ${creature.speed}` : 'Unable to battle',
          available ? () => sendOpeningCreature(index) : null,
          {
            disabled: !available,
            profile: creature,
            onFocus: () => setDetail(creatureDetailHtml(creature, false)),
          }
        );
      }),
    });
  }

  async function sendOpeningCreature(index) {
    const creature = state.playerTeam[index];
    if (!creature || creature.hp <= 0 || !state.openingSelection) {
      return;
    }

    state.locked = true;
    state.playerIndex = index;
    syncField();
    el.player.classList.remove('is-awaiting-choice');
    setTurnIndicator(`Sending out ${creature.name}`);
    playSummon('player');
    await wait(240);
    addLog(`Go, ${creature.name}!`);
    abilityStartMessages.forEach((message) => addLog(message));
    setDetail(defaultDetailHtml());
    renderRootMenu();
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
          `${move.elementName || 'Neutral'} - ${isStatusMove(move) ? (describeMoveEffect(move) || 'Status') : `${move.power} power`}`,
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

  async function postBattleAction(params, options) {
    const response = await fetch(battlePostUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams(params),
      keepalive: Boolean(options && options.keepalive),
    });
    return response.json();
  }

  function playerHpSnapshot() {
    return JSON.stringify(state.playerTeam
      .map((creature) => ({
        id: Number(creature && creature.id) || 0,
        hp: clamp(Math.round(Number(creature && creature.hp) || 0), 0, Math.max(0, Number(creature && creature.maxHp) || 0)),
        maxHp: Math.max(0, Math.round(Number(creature && creature.maxHp) || 0)),
      }))
      .filter((creature) => creature.id > 0));
  }

  function hpSyncParams() {
    return {
      battle_action: 'sync_hp',
      battle_token: String(data.token || ''),
      player_hp: playerHpSnapshot(),
      hp_version: String(state.hpVersion),
    };
  }

  function sendPlayerHpBeacon() {
    const params = hpSyncParams();
    if (navigator.sendBeacon) {
      const form = new FormData();
      Object.entries(params).forEach(([key, value]) => form.append(key, value));
      if (navigator.sendBeacon(battlePostUrl, form)) {
        return true;
      }
    }

    try {
      void postBattleAction(params, { keepalive: true }).catch(() => {});
    } catch (error) {
      // The normal exit sync still gets a chance when the page is not unloading.
    }

    return false;
  }

  async function syncPlayerHpForExit() {
    if (state.hpSyncing) {
      return state.hpSyncing;
    }

    state.hpSyncing = postBattleAction(hpSyncParams(), { keepalive: true }).then((result) => {
      if (!result || !result.ok) {
        throw new Error(result && result.message ? result.message : 'HP sync failed.');
      }
      return result;
    }).finally(() => {
      state.hpSyncing = null;
    });

    return state.hpSyncing;
  }

  async function syncPlayerHpQuietly(timeoutMs) {
    try {
      const sync = syncPlayerHpForExit();
      const timeout = Number(timeoutMs || 0);
      if (timeout > 0) {
        let completed = false;
        const trackedSync = sync.then((result) => {
          completed = true;
          return result;
        }).catch((error) => {
          completed = true;
          throw error;
        });
        sync.catch(() => {});
        await Promise.race([trackedSync, wait(timeout)]);
        if (!completed) {
          sendPlayerHpBeacon();
        }
        return;
      }

      await sync;
    } catch (error) {
      sendPlayerHpBeacon();
    }
  }

  function returnUrl() {
    return data.returnUrl || 'index.php?pg=games';
  }

  function leaveBattleNow() {
    window.clearTimeout(state.hpSyncTimer);
    sendPlayerHpBeacon();
    window.location.href = returnUrl();
  }

  function queuePlayerHpSync() {
    window.clearTimeout(state.hpSyncTimer);
    state.hpSyncTimer = window.setTimeout(() => {
      void postBattleAction(hpSyncParams()).catch(() => {});
    }, 350);
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

    const heal = Math.max(0, Number(result.heal || item.heal || 0));
    item.quantity = Number(result.quantity || 0);
    item.heal = heal || item.heal;
    const hpBefore = target.hp;
    target.hp = Math.min(target.maxHp, target.hp + heal);
    if (target.hp !== hpBefore) {
      markPlayerHpChanged();
      queuePlayerHpSync();
    }
    addLog(`You used ${item.name} on ${target.name}.`);
    spawnNumber('player', heal, 'heal');
    await updateHpDisplay('player', target, true);
    await wait(120);

    if (!state.battleEnded && currentNpc() && currentNpc().hp > 0) {
      const npcMove = pickNpcMove();
      await performAttack(currentNpc(), target, npcMove, 'npc');
    }

    if (!state.battleEnded && !state.forceSwitch) {
      await endOfRoundPhase();
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
    resetVolatileState(currentPlayer());
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

    if (!state.battleEnded && !state.forceSwitch) {
      await endOfRoundPhase();
    }

    if (!state.battleEnded && currentPlayer() && currentPlayer().hp > 0 && currentNpc() && currentNpc().hp > 0 && !state.forceSwitch) {
      renderRootMenu();
    }
  }

  async function handleFaint(side) {
    const host = side === 'player' ? el.player : el.npc;
    updateStatusBadge(side, side === 'player' ? currentPlayer() : currentNpc());
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

  async function applyEndOfTurnStatus(creature, side) {
    if (!creature || creature.hp <= 0 || !creature.status || state.battleEnded || state.forceSwitch) {
      return;
    }

    const key = creature.status.key;
    let fraction = 0;
    let floorHp = 0;
    if (key === 'poison' || key === 'burn') {
      fraction = 0.125;
      floorHp = 1; // poison and burn gnaw the creature down to 1 HP, never past it
    } else if (key === 'venom') {
      fraction = 0.25; // venom can finish the job
    } else {
      return;
    }

    const damage = Math.max(1, Math.round(creature.maxHp * fraction));
    const newHp = Math.max(floorHp, creature.hp - damage);
    const dealt = creature.hp - newHp;
    if (dealt <= 0) {
      addLog(`${creature.name} clings on at 1 HP despite the ${statusInfo[key].displayName.toLowerCase()}.`);
      return;
    }

    creature.hp = newHp;
    addLog(key === 'burn'
      ? `${creature.name} is hurt by its burn!`
      : `${creature.name} is hurt by ${key === 'venom' ? 'the venom' : 'poison'}!`);
    if (side === 'player') {
      markPlayerHpChanged();
      queuePlayerHpSync();
    }
    spawnNumber(side, dealt, '');
    await updateHpDisplay(side, creature, true);

    if (creature.hp <= 0) {
      creature.fainted = true;
      addLog(`${creature.name} dropped to 0 HP.`);
      await handleFaint(side);
    }
  }

  async function endOfRoundPhase() {
    await applyEndOfTurnStatus(currentPlayer(), 'player');
    await applyEndOfTurnStatus(currentNpc(), 'npc');
  }

  async function resolveRound(playerMove) {
    const player = currentPlayer();
    const npc = currentNpc();
    if (!player || !npc || state.locked || state.battleEnded) {
      return;
    }

    state.locked = true;
    const npcMove = pickNpcMove();
    const playerPriority = abilityEngine.priorityFor(player, playerMove);
    const npcPriority = abilityEngine.priorityFor(npc, npcMove);
    const playerActsFirst = playerPriority === npcPriority
      ? effectiveStat(player, 'speed') >= effectiveStat(npc, 'speed')
      : playerPriority > npcPriority;
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

    await endOfRoundPhase();

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
    await syncPlayerHpQuietly(900);
    await wait(900);
    leaveBattleNow();
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
    await syncPlayerHpQuietly(900);
    await wait(1500);
    leaveBattleNow();
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
      const winningCreature = currentPlayer();
      const defeatedCreature = currentNpc();
      try {
        const result = await postBattleAction({
          battle_action: 'award_victory',
          trainer_id: String(data.trainer.id),
          battle_token: String(data.token || ''),
          winning_pet_id: String((winningCreature && winningCreature.id) || 0),
          enemy_pet_id: String((defeatedCreature && defeatedCreature.id) || 0),
          player_hp: playerHpSnapshot(),
          hp_version: String(state.hpVersion),
        });
        if (result && result.ok && typeof window.updateCurrencyDisplay === 'function') {
          window.updateCurrencyDisplay({ cash: Number(result.cash || 0) });
        }
        if (!result || !result.ok) {
          sendPlayerHpBeacon();
          addLog(result && result.message ? result.message : 'The reward sync failed. Refresh if the wallet did not update.');
        }
        if (result && result.ok && result.experience && Number(result.experience.gained || 0) > 0) {
          addLog(`${result.experience.petName} gained ${result.experience.gained} XP.`);
          if (result.experience.leveledUp) {
            addLog(`${result.experience.petName} reached level ${result.experience.level}!`);
          }
        }
      } catch (error) {
        sendPlayerHpBeacon();
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
          leaveBattleNow();
        }),
      ],
    });

    window.setTimeout(() => {
      leaveBattleNow();
    }, 2400);
  }

  window.addEventListener('pagehide', sendPlayerHpBeacon);
  document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'hidden') {
      sendPlayerHpBeacon();
    }
  });

  function bindKeyboardNavigation() {
    document.addEventListener('keydown', (event) => {
      if (state.menuKey === 'opening-creatures' && event.key === 'Escape') {
        event.preventDefault();
        return;
      }

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

    setTurnIndicator('Choose your creature');
    openOpeningCreatureMenu();
  }

  function boot() {
    syncField();
    el.player.classList.add('is-awaiting-choice');
    setTurnIndicator('Awaiting clash');
    setDetail('<p class="battle-detail-empty">Start the battle, then choose which creature you want to send out first.</p>');
    addLog(isWildBattle ? 'A wild creature battle is about to begin.' : 'A trainer battle is about to begin.');
    el.start.addEventListener('click', startEncounter);
    bindKeyboardNavigation();
  }

  boot();
})();
