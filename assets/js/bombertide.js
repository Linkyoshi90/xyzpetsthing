/* Bombertide — a Bomberman-style round against 3 NPC creatures.
 * Tile-based board (movement is smooth/pixel, everything else snaps to tiles).
 * Blocks & explosions are CSS; bombs are <img>; characters are bobbing creature
 * sprites. Player is the first creature in their party; NPCs are random creatures.
 */
(function () {
  const wrap = document.getElementById('bt-wrap');
  const board = document.getElementById('bt-board');
  if (!wrap || !board) return;

  const CONFIG = JSON.parse(wrap.dataset.config || '{}');

  // ---- Board dimensions ----
  const COLS = 15, ROWS = 13, TILE = 40;
  const FUSE = 2500;        // bomb fuse, ms
  const EXP_TTL = 500;      // explosion lifetime, ms
  const HB = 28;            // character hitbox size (centered in TILE sprite)
  const HB_OFF = (TILE - HB) / 2;
  const PLAYER_SPEED = 0.16;  // px per ms
  const NPC_SPEED = 0.115;
  const NPC_THINK = 140;      // ms between AI decisions
  const BLOCK_DROP_CHANCE = 0.32;
  const NPC_BOMB_CHANCE = 0.7; // how often a "good" bomb opportunity is taken
  const POINTS_BLOCK = 10;     // x tier
  const POINTS_KILL = 40;      // fixed NPC-kill reward (the x4 factor vs a tier-1 block)
  const MAX_STAT = 8;

  wrap.style.setProperty('--bt-tile', TILE + 'px');
  board.style.width = COLS * TILE + 'px';
  board.style.height = ROWS * TILE + 'px';

  const RING = ['#3aa0ff', '#ff5d5d', '#ffd24a', '#7CFC76']; // player, npc1..3

  // ---- Game state ----
  let grid;            // grid[r][c] = {type:'empty'|'wall'|'block', hp, el, item, itemEl}
  let chars;           // [player, npc, npc, npc]
  let bombs;           // active bombs
  let explosions;      // active explosion flashes
  const input = { left: false, right: false, up: false, down: false };
  let score = 0;
  let level = 1;
  let running = false;
  let lastTs = 0;
  let rafId = 0;

  const scoreEl = document.getElementById('bt-score');
  const levelEl = document.getElementById('bt-level');
  const bombsEl = document.getElementById('bt-bombs');
  const rangeEl = document.getElementById('bt-range');

  const key = (c, r) => c + ',' + r;
  const inBounds = (c, r) => c >= 0 && c < COLS && r >= 0 && r < ROWS;
  const cellOf = (ch) => ({
    c: Math.floor((ch.x + TILE / 2) / TILE),
    r: Math.floor((ch.y + TILE / 2) / TILE),
  });

  // ---------- Level generation ----------
  function cornerSafeCells() {
    // Keep an L of 3 cells clear at each corner so spawns survive the opening.
    const safe = new Set();
    const corners = [
      [1, 1, 1, 1], [COLS - 2, 1, -1, 1],
      [1, ROWS - 2, 1, -1], [COLS - 2, ROWS - 2, -1, -1],
    ];
    for (const [c, r, dc, dr] of corners) {
      safe.add(key(c, r));
      safe.add(key(c + dc, r));
      safe.add(key(c, r + dr));
    }
    return safe;
  }

  function blankGrid() {
    const g = [];
    for (let r = 0; r < ROWS; r++) {
      g.push([]);
      for (let c = 0; c < COLS; c++) {
        const border = r === 0 || c === 0 || r === ROWS - 1 || c === COLS - 1;
        g[r].push({ type: border ? 'wall' : 'empty', hp: 0, el: null, item: null, itemEl: null });
      }
    }
    return g;
  }

  function setWall(g, c, r, safe) {
    if (!inBounds(c, r) || safe.has(key(c, r))) return;
    if (c <= 0 || r <= 0 || c >= COLS - 1 || r >= ROWS - 1) return;
    g[r][c].type = 'wall';
  }

  function buildLevel(layout) {
    const safe = cornerSafeCells();
    const g = blankGrid();

    if (layout === 1) {
      // Almost no indestructible blocks — open meadow.
      // (just the border)
    } else if (layout === 2) {
      // Flower of indestructible blocks in the centre.
      const cc = (COLS - 1) / 2 | 0, cr = (ROWS - 1) / 2 | 0;
      const petals = [
        [0, 0], [-1, -1], [1, -1], [-1, 1], [1, 1],
        [0, -2], [0, 2], [-2, 0], [2, 0],
      ];
      for (const [dc, dr] of petals) setWall(g, cc + dc, cr + dr, safe);
    } else if (layout === 3) {
      // A giant H of indestructible blocks.
      const left = 4, right = COLS - 5, mid = (ROWS - 1) / 2 | 0;
      for (let r = 2; r <= ROWS - 3; r++) { setWall(g, left, r, safe); setWall(g, right, r, safe); }
      for (let c = left; c <= right; c++) setWall(g, c, mid, safe);
    } else {
      // Random indestructible placements.
      for (let r = 1; r < ROWS - 1; r++) {
        for (let c = 1; c < COLS - 1; c++) {
          if (safe.has(key(c, r))) continue;
          if (Math.random() < 0.12) g[r][c].type = 'wall';
        }
      }
    }

    // Fill the rest with destructible blocks (tiered).
    const fill = layout === 1 ? 0.5 : 0.58;
    for (let r = 1; r < ROWS - 1; r++) {
      for (let c = 1; c < COLS - 1; c++) {
        const cell = g[r][c];
        if (cell.type !== 'empty') continue;
        if (safe.has(key(c, r))) continue;
        if (Math.random() < fill) {
          cell.type = 'block';
          const roll = Math.random();
          cell.hp = roll < 0.62 ? 1 : roll < 0.87 ? 2 : 3;
        }
      }
    }
    return g;
  }

  // ---------- Rendering helpers ----------
  function placeCell(el, c, r) {
    el.style.left = c * TILE + 'px';
    el.style.top = r * TILE + 'px';
  }

  function renderGrid() {
    // Clear existing tile/bomb/explosion/char DOM, keep nothing.
    board.innerHTML = '';
    board.className = 'bt-level-' + ((level - 1) % 4 + 1);
    for (let r = 0; r < ROWS; r++) {
      for (let c = 0; c < COLS; c++) {
        const cell = grid[r][c];
        if (cell.type === 'wall') {
          const el = document.createElement('div');
          el.className = 'bt-cell bt-wall';
          placeCell(el, c, r);
          board.appendChild(el);
          cell.el = el;
        } else if (cell.type === 'block') {
          cell.el = makeBlockEl(c, r, cell.hp);
        }
      }
    }
  }

  function makeBlockEl(c, r, hp) {
    const el = document.createElement('div');
    el.className = 'bt-cell bt-block tier-' + hp;
    if (hp > 1) el.textContent = hp;
    placeCell(el, c, r);
    board.appendChild(el);
    return el;
  }

  // ---------- Characters ----------
  function makeChar(img, spawnC, spawnR, isPlayer, idx) {
    const el = document.createElement('div');
    el.className = 'bt-char';
    el.style.width = TILE + 'px';
    el.style.height = TILE + 'px';
    el.style.backgroundImage = `url("${img}")`;
    el.style.setProperty('--bt-ring', RING[idx]);
    board.appendChild(el);
    const ch = {
      el, isPlayer, idx,
      x: spawnC * TILE, y: spawnR * TILE,
      maxBombs: 1, range: 1, speed: isPlayer ? PLAYER_SPEED : NPC_SPEED,
      activeBombs: 0, alive: true, facing: 1, moving: false,
      passBombs: new Set(),
      think: Math.random() * NPC_THINK, target: null,
    };
    syncChar(ch);
    return ch;
  }

  function syncChar(ch) {
    ch.el.style.left = ch.x + 'px';
    ch.el.style.top = ch.y + 'px';
    ch.el.classList.toggle('moving', ch.moving && ch.alive);
    ch.el.classList.toggle('facing-left', ch.facing < 0);
  }

  // ---------- Collision ----------
  function solidAt(c, r, ch) {
    if (!inBounds(c, r)) return true;
    const t = grid[r][c].type;
    if (t === 'wall' || t === 'block') return true;
    const b = bombAt(c, r);
    if (b) {
      // A bomb you just placed (and still stand on) is passable until you leave.
      if (ch && ch.passBombs.has(key(c, r))) return false;
      return true;
    }
    return false;
  }

  function hbSolid(x, y, ch) {
    const x0 = x + HB_OFF, y0 = y + HB_OFF, x1 = x0 + HB - 1, y1 = y0 + HB - 1;
    const c0 = Math.floor(x0 / TILE), c1 = Math.floor(x1 / TILE);
    const r0 = Math.floor(y0 / TILE), r1 = Math.floor(y1 / TILE);
    for (let r = r0; r <= r1; r++)
      for (let c = c0; c <= c1; c++)
        if (solidAt(c, r, ch)) return true;
    return false;
  }

  // Move one axis with collision; returns true if it actually moved.
  function moveAxis(ch, dx, dy) {
    const nx = ch.x + dx, ny = ch.y + dy;
    if (!hbSolid(nx, ny, ch)) { ch.x = nx; ch.y = ny; return true; }
    return false;
  }

  // Slide the off-axis toward the nearest corridor centre so turning corners works.
  function alignToward(ch, axis, dt) {
    const cur = axis === 'x' ? ch.x : ch.y;
    const center = Math.round(cur / TILE) * TILE;
    const diff = center - cur;
    if (Math.abs(diff) < 0.5) { if (axis === 'x') ch.x = center; else ch.y = center; return; }
    const step = Math.min(Math.abs(diff), ch.speed * dt) * Math.sign(diff);
    if (axis === 'x') moveAxis(ch, step, 0); else moveAxis(ch, 0, step);
  }

  function updatePassBombs(ch) {
    if (!ch.passBombs.size) return;
    const x0 = ch.x + HB_OFF, y0 = ch.y + HB_OFF, x1 = x0 + HB - 1, y1 = y0 + HB - 1;
    for (const k of [...ch.passBombs]) {
      const [c, r] = k.split(',').map(Number);
      const bx0 = c * TILE, by0 = r * TILE, bx1 = bx0 + TILE, by1 = by0 + TILE;
      const overlap = x0 < bx1 && x1 >= bx0 && y0 < by1 && y1 >= by0;
      if (!overlap) ch.passBombs.delete(k);
    }
  }

  // ---------- Bombs & explosions ----------
  function bombAt(c, r) { return bombs.find(b => b.c === c && b.r === r && !b.exploded); }

  function placeBomb(ch) {
    if (ch.activeBombs >= ch.maxBombs) return;
    const { c, r } = cellOf(ch);
    if (bombAt(c, r)) return;
    const el = document.createElement('img');
    el.className = 'bt-bomb';
    el.src = CONFIG.bomb || 'images/games/bomb1.png';
    placeCell(el, c, r);
    board.appendChild(el);
    const bomb = { c, r, owner: ch, range: ch.range, timer: FUSE, el, exploded: false };
    bombs.push(bomb);
    ch.activeBombs++;
    ch.passBombs.add(key(c, r));
  }

  function detonate(bomb) {
    if (bomb.exploded) return;
    bomb.exploded = true;
    if (bomb.el) bomb.el.remove();
    if (bomb.owner) bomb.owner.activeBombs = Math.max(0, bomb.owner.activeBombs - 1);

    const cells = [{ c: bomb.c, r: bomb.r }];
    const dirs = [[1, 0], [-1, 0], [0, 1], [0, -1]];
    for (const [dc, dr] of dirs) {
      for (let i = 1; i <= bomb.range; i++) {
        const c = bomb.c + dc * i, r = bomb.r + dr * i;
        if (!inBounds(c, r)) break;
        const cell = grid[r][c];
        if (cell.type === 'wall') break;
        if (cell.type === 'block') { damageBlock(c, r, bomb.owner); break; }
        cells.push({ c, r });
        // Chain-detonate any bomb caught in the blast.
        const other = bombAt(c, r);
        if (other && other !== bomb) detonate(other);
      }
    }

    // Render explosion flashes.
    const cellSet = new Set();
    for (const { c, r } of cells) {
      cellSet.add(key(c, r));
      const ex = document.createElement('div');
      ex.className = 'bt-exp';
      placeCell(ex, c, r);
      board.appendChild(ex);
      setTimeout(() => ex.remove(), EXP_TTL);
    }
    explosions.push({ cells: cellSet, owner: bomb.owner, ttl: EXP_TTL });
    // Immediate kill check (catch chars standing in the blast right now).
    checkExplosionKills();
  }

  function damageBlock(c, r, owner) {
    const cell = grid[r][c];
    if (cell.type !== 'block') return;
    cell.hp--;
    if (cell.hp <= 0) {
      const el = cell.el;
      if (el) { el.classList.add('bt-break'); setTimeout(() => el.remove(), 250); }
      const tier = el ? (el.classList.contains('tier-3') ? 3 : el.classList.contains('tier-2') ? 2 : 1) : 1;
      cell.type = 'empty'; cell.el = null;
      if (owner && owner.isPlayer) addScore(POINTS_BLOCK * tier);
      maybeDropItem(c, r);
    } else {
      if (cell.el) {
        cell.el.textContent = cell.hp;
        cell.el.classList.add('bt-hit');
        setTimeout(() => cell.el && cell.el.classList.remove('bt-hit'), 180);
      }
    }
  }

  // ---------- Items ----------
  const ITEM_TYPES = ['simb-up', 'simb-down', 'exprad-up', 'exprad-down'];
  const ITEM_GLYPH = { 'simb-up': '+💣', 'simb-down': '−💣', 'exprad-up': '+🔥', 'exprad-down': '−🔥' };

  function maybeDropItem(c, r) {
    if (Math.random() > BLOCK_DROP_CHANCE) return;
    const type = ITEM_TYPES[Math.floor(Math.random() * ITEM_TYPES.length)];
    const el = document.createElement('div');
    el.className = 'bt-cell bt-item ' + type;
    const url = CONFIG.items && CONFIG.items[type];
    if (url) el.style.backgroundImage = `url("${url}")`;
    el.textContent = ITEM_GLYPH[type]; // shown if the art is missing
    el.dataset.fallback = '1';
    // Hide the glyph if the image actually loads.
    if (url) {
      const probe = new Image();
      probe.onload = () => { el.textContent = ''; };
      probe.src = url;
    }
    placeCell(el, c, r);
    board.appendChild(el);
    grid[r][c].item = type;
    grid[r][c].itemEl = el;
  }

  function pickupItems(ch) {
    if (!ch.alive) return;
    const { c, r } = cellOf(ch);
    const cell = grid[r][c];
    if (!cell.item) return;
    applyItem(ch, cell.item);
    if (cell.itemEl) cell.itemEl.remove();
    cell.item = null; cell.itemEl = null;
  }

  function applyItem(ch, type) {
    switch (type) {
      case 'simb-up': ch.maxBombs = Math.min(MAX_STAT, ch.maxBombs + 1); break;
      case 'simb-down': ch.maxBombs = Math.max(1, ch.maxBombs - 1); break;
      case 'exprad-up': ch.range = Math.min(MAX_STAT, ch.range + 1); break;
      case 'exprad-down': ch.range = Math.max(1, ch.range - 1); break;
    }
    if (ch.isPlayer) updateHud();
  }

  // ---------- Explosion kills ----------
  function checkExplosionKills() {
    for (const ex of explosions) {
      for (const ch of chars) {
        if (!ch.alive) continue;
        const { c, r } = cellOf(ch);
        if (ex.cells.has(key(c, r))) killChar(ch, ex.owner);
      }
    }
  }

  function killChar(ch, owner) {
    if (!ch.alive) return;
    ch.alive = false;
    ch.moving = false;
    ch.el.classList.remove('moving');
    ch.el.classList.add('bt-dead');
    if (!ch.isPlayer && owner && owner.isPlayer) addScore(POINTS_KILL);
  }

  // ---------- Score / HUD ----------
  function addScore(n) { score += n; if (scoreEl) scoreEl.textContent = score; }
  function updateHud() {
    const p = chars[0];
    if (bombsEl) bombsEl.textContent = p.maxBombs;
    if (rangeEl) rangeEl.textContent = p.range;
    if (levelEl) levelEl.textContent = level;
    if (scoreEl) scoreEl.textContent = score;
  }

  // ---------- NPC AI ----------
  function dangerMap() {
    const danger = new Set();
    const add = (c, r) => danger.add(key(c, r));
    for (const ex of explosions) for (const k of ex.cells) danger.add(k);
    for (const b of bombs) {
      if (b.exploded) continue;
      add(b.c, b.r);
      for (const [dc, dr] of [[1, 0], [-1, 0], [0, 1], [0, -1]]) {
        for (let i = 1; i <= b.range; i++) {
          const c = b.c + dc * i, r = b.r + dr * i;
          if (!inBounds(c, r)) break;
          const t = grid[r][c].type;
          if (t === 'wall') break;
          add(c, r);
          if (t === 'block') break;
        }
      }
    }
    return danger;
  }

  function passable(c, r) {
    if (!inBounds(c, r)) return false;
    const t = grid[r][c].type;
    if (t === 'wall' || t === 'block') return false;
    if (bombAt(c, r)) return false;
    return true;
  }

  // BFS; returns the first step (adjacent cell) toward the nearest goal cell.
  function bfsStep(sc, sr, isGoal, avoid) {
    const q = [[sc, sr]];
    const seen = new Set([key(sc, sr)]);
    const parent = {};
    while (q.length) {
      const [c, r] = q.shift();
      if ((c !== sc || r !== sr) && isGoal(c, r)) {
        // walk back to the first step
        let cur = key(c, r), prev = parent[cur];
        while (prev && prev !== key(sc, sr)) { cur = prev; prev = parent[cur]; }
        const [fc, fr] = cur.split(',').map(Number);
        return { c: fc, r: fr };
      }
      for (const [dc, dr] of [[1, 0], [-1, 0], [0, 1], [0, -1]]) {
        const nc = c + dc, nr = r + dr, k = key(nc, nr);
        if (seen.has(k) || !passable(nc, nr)) continue;
        if (avoid && avoid.has(k)) continue;
        seen.add(k); parent[k] = key(c, r); q.push([nc, nr]);
      }
    }
    return null;
  }

  function adjacentToBlock(c, r) {
    return [[1, 0], [-1, 0], [0, 1], [0, -1]].some(([dc, dr]) =>
      inBounds(c + dc, r + dr) && grid[r + dr][c + dc].type === 'block');
  }

  function playerInBlastLine(c, r, range) {
    const p = chars[0];
    if (!p.alive) return false;
    const pc = cellOf(p);
    for (const [dc, dr] of [[1, 0], [-1, 0], [0, 1], [0, -1]]) {
      for (let i = 1; i <= range; i++) {
        const nc = c + dc * i, nr = r + dr * i;
        if (!inBounds(nc, nr)) break;
        const t = grid[nr][nc].type;
        if (t === 'wall' || t === 'block') break;
        if (pc.c === nc && pc.r === nr) return true;
      }
    }
    return false;
  }

  // Would placing a bomb here still leave the NPC an escape?
  function hasEscapeAfterBomb(c, r, range) {
    const blast = new Set([key(c, r)]);
    for (const [dc, dr] of [[1, 0], [-1, 0], [0, 1], [0, -1]]) {
      for (let i = 1; i <= range; i++) {
        const nc = c + dc * i, nr = r + dr * i;
        if (!inBounds(nc, nr)) break;
        const t = grid[nr][nc].type;
        if (t === 'wall') break;
        blast.add(key(nc, nr));
        if (t === 'block') break;
      }
    }
    return bfsStep(c, r, (gc, gr) => !blast.has(key(gc, gr)), null) !== null;
  }

  function npcThink(ch) {
    const { c, r } = cellOf(ch);
    const danger = dangerMap();

    // 1. Flee active danger.
    if (danger.has(key(c, r))) {
      const step = bfsStep(c, r, (gc, gr) => !danger.has(key(gc, gr)), null);
      ch.target = step;
      return;
    }

    // 2. Drop a bomb on a worthwhile target if we can escape afterwards.
    if (ch.activeBombs < ch.maxBombs && Math.random() < NPC_BOMB_CHANCE) {
      if ((adjacentToBlock(c, r) || playerInBlastLine(c, r, ch.range)) &&
        hasEscapeAfterBomb(c, r, ch.range)) {
        placeBomb(ch);
        const d2 = dangerMap();
        ch.target = bfsStep(c, r, (gc, gr) => !d2.has(key(gc, gr)), null);
        return;
      }
    }

    // 3. Hunt: head toward the nearest destructible block, else toward the player.
    let step = bfsStep(c, r, (gc, gr) => adjacentToBlock(gc, gr), danger);
    if (!step) {
      const p = cellOf(chars[0]);
      step = bfsStep(c, r, (gc, gr) => gc === p.c && gr === p.r, danger);
    }
    if (!step) {
      // wander
      const opts = [[1, 0], [-1, 0], [0, 1], [0, -1]]
        .map(([dc, dr]) => ({ c: c + dc, r: r + dr }))
        .filter(o => passable(o.c, o.r) && !danger.has(key(o.c, o.r)));
      step = opts.length ? opts[Math.floor(Math.random() * opts.length)] : null;
    }
    ch.target = step;
  }

  function moveNpc(ch, dt) {
    if (!ch.target) { ch.moving = false; return; }
    const tx = ch.target.c * TILE, ty = ch.target.r * TILE;
    const dist = ch.speed * dt;
    let moved = false;
    if (Math.abs(tx - ch.x) > 0.5) {
      const dir = Math.sign(tx - ch.x);
      const step = Math.min(Math.abs(tx - ch.x), dist) * dir;
      if (moveAxis(ch, step, 0)) { ch.facing = dir; moved = true; }
      else ch.target = null;
    } else if (Math.abs(ty - ch.y) > 0.5) {
      const dir = Math.sign(ty - ch.y);
      const step = Math.min(Math.abs(ty - ch.y), dist) * dir;
      if (moveAxis(ch, 0, step)) moved = true;
      else ch.target = null;
    } else {
      ch.x = tx; ch.y = ty; ch.target = null;
    }
    ch.moving = moved;
  }

  // ---------- Player input ----------
  function handlePlayer(ch, dt) {
    if (!ch.alive) { ch.moving = false; return; }
    let dx = 0, dy = 0;
    if (input.left) dx = -1; else if (input.right) dx = 1;
    if (input.up) dy = -1; else if (input.down) dy = 1;
    // Prefer one axis at a time for crisp grid feel; horizontal wins ties.
    if (dx && dy) dy = 0;
    const dist = ch.speed * dt;
    let moved = false;
    if (dx) {
      ch.facing = dx;
      if (moveAxis(ch, dx * dist, 0)) moved = true;
      alignToward(ch, 'y', dt);
    } else if (dy) {
      if (moveAxis(ch, 0, dy * dist)) moved = true;
      alignToward(ch, 'x', dt);
    }
    ch.moving = moved;
  }

  // ---------- Main loop ----------
  function tick(ts) {
    if (!running) return;
    const dt = Math.min(40, ts - lastTs || 16);
    lastTs = ts;

    // Bomb fuses
    for (const b of bombs) {
      if (b.exploded) continue;
      b.timer -= dt;
      if (b.timer <= 0) detonate(b);
    }
    bombs = bombs.filter(b => !b.exploded);

    // Explosions lifetime + lingering kills
    for (const ex of explosions) ex.ttl -= dt;
    explosions = explosions.filter(ex => ex.ttl > 0);
    checkExplosionKills();

    // Characters
    handlePlayer(chars[0], dt);
    for (let i = 1; i < chars.length; i++) {
      const ch = chars[i];
      if (!ch.alive) { ch.moving = false; continue; }
      ch.think -= dt;
      if (ch.think <= 0) { ch.think = NPC_THINK; npcThink(ch); }
      moveNpc(ch, dt);
    }
    for (const ch of chars) {
      if (!ch.alive) continue;
      updatePassBombs(ch);
      pickupItems(ch);
      syncChar(ch);
    }

    // Win / lose
    if (!chars[0].alive) return endRound(false);
    if (chars.slice(1).every(n => !n.alive)) return endRound(true);

    rafId = requestAnimationFrame(tick);
  }

  // ---------- Round / level flow ----------
  function startLevel() {
    running = false;
    cancelAnimationFrame(rafId);
    bombs = []; explosions = [];
    input.left = input.right = input.up = input.down = false;

    const layout = Math.floor(Math.random() * 4) + 1;
    grid = buildLevel(layout);
    renderGrid();

    const spawns = [
      [1, 1], [COLS - 2, 1], [1, ROWS - 2], [COLS - 2, ROWS - 2],
    ];
    // Keep player power-ups across won rounds; rebuild the roster otherwise.
    const prev = chars && chars[0] && chars[0].alive ? chars[0] : null;
    chars = [];
    chars.push(makeChar(CONFIG.player, spawns[0][0], spawns[0][1], true, 0));
    if (prev) { chars[0].maxBombs = prev.maxBombs; chars[0].range = prev.range; }
    const npcImgs = CONFIG.npcs || [];
    for (let i = 0; i < 3; i++) {
      const [c, r] = spawns[i + 1];
      chars.push(makeChar(npcImgs[i] || CONFIG.player, c, r, false, i + 1));
    }

    updateHud();
    board.focus();
    running = true;
    lastTs = performance.now();
    rafId = requestAnimationFrame(tick);
  }

  function endRound(won) {
    running = false;
    cancelAnimationFrame(rafId);
    if (won) {
      level++;
      updateHud();
      // Brief beat, then the next random level.
      setTimeout(startLevel, 900);
      return;
    }
    showGameOver();
  }

  // ---------- Game over overlay ----------
  const overlay = document.getElementById('bt-overlay');
  function showGameOver() {
    document.getElementById('bt-overlay-title').textContent = 'You were eliminated!';
    document.getElementById('bt-overlay-msg').textContent =
      'Retry for a fresh run, or send your score to the exchange.';
    document.getElementById('bt-final-score').textContent = score;
    overlay.classList.remove('hidden');
  }

  document.getElementById('bt-retry').addEventListener('click', () => {
    overlay.classList.add('hidden');
    score = 0; level = 1; chars = null;
    startLevel();
  });

  document.getElementById('bt-send').addEventListener('click', () => {
    const btn = document.getElementById('bt-send');
    if (score <= 0) { window.location.href = 'index.php?pg=games'; return; }
    btn.disabled = true; btn.textContent = 'Sending…';
    fetch('score_exchange.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ game: 'bombertide', score }),
    })
      .then(r => r.json())
      .then(data => {
        if (window.updateCurrencyDisplay && data && data.cash !== undefined) {
          window.updateCurrencyDisplay({ cash: data.cash });
        } else if (data && data.error) {
          alert(data.error);
        }
      })
      .catch(() => { })
      .finally(() => { window.location.href = 'index.php?pg=games'; });
  });

  // ---------- Input wiring ----------
  // Arrow keys and WASD both move; Space (or the on-screen button) drops a bomb.
  const KEY_DIRS = {
    arrowleft: 'left', a: 'left',
    arrowright: 'right', d: 'right',
    arrowup: 'up', w: 'up',
    arrowdown: 'down', s: 'down',
  };
  const normKey = e => (e.key.length === 1 ? e.key.toLowerCase() : e.key.toLowerCase());

  function pressBomb() {
    if (running && chars && chars[0] && chars[0].alive) placeBomb(chars[0]);
  }

  document.addEventListener('keydown', e => {
    if (!running) return;
    const k = normKey(e);
    if (k === ' ' || k === 'spacebar') { e.preventDefault(); pressBomb(); return; }
    const dir = KEY_DIRS[k];
    if (dir) { e.preventDefault(); input[dir] = true; }
  });
  document.addEventListener('keyup', e => {
    const dir = KEY_DIRS[normKey(e)];
    if (dir) input[dir] = false;
  });

  // On-screen D-pad (touch + mouse via pointer events).
  function bindDpad() {
    const dpad = document.getElementById('bt-dpad');
    if (!dpad) return;
    dpad.querySelectorAll('[data-dir]').forEach(btn => {
      const dir = btn.dataset.dir;
      const press = e => { e.preventDefault(); input[dir] = true; btn.classList.add('active'); };
      const release = () => { input[dir] = false; btn.classList.remove('active'); };
      btn.addEventListener('pointerdown', press);
      btn.addEventListener('pointerup', release);
      btn.addEventListener('pointercancel', release);
      btn.addEventListener('pointerleave', release);
    });
    const bombBtn = dpad.querySelector('[data-bomb]');
    if (bombBtn) {
      bombBtn.addEventListener('pointerdown', e => {
        e.preventDefault(); pressBomb();
        bombBtn.classList.add('active');
      });
      const off = () => bombBtn.classList.remove('active');
      bombBtn.addEventListener('pointerup', off);
      bombBtn.addEventListener('pointercancel', off);
      bombBtn.addEventListener('pointerleave', off);
    }
  }

  bindDpad();
  startLevel();
})();
