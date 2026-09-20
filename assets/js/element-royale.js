(function () {
  'use strict';

  var root = document.getElementById('er-app');
  if (!root) {
    return;
  }

  var payloadEl = document.getElementById('er-payload');
  var payload = {};
  try {
    payload = JSON.parse((payloadEl && payloadEl.textContent) || '{}') || {};
  } catch (err) {
    payload = {};
  }

  var CONFIG = {
    cols: 38,
    rows: 26,
    npcCount: 15,
    startPoints: 5,
    tickMs: 90,
    baseCooldown: 2,
    // Every this many points costs one extra tick per step, so mass slows you down.
    pointsPerCooldown: 5,
    sightRadius: 6,
    // Stand-in for the phase 2 closing ring: sight widens as the round drags, so the
    // last few survivors are forced together instead of wandering a big empty map.
    sightGrowthTicks: 250,
    fleeRadius: 3,
    // element_calc stores true immunities as 0.00. Taken literally a 3 point Iron
    // deletes a 20 point Venom, so this mode floors the multiplier instead.
    effFloor: 0.25,
    // Must stay above sightRadius, otherwise everyone opens the round already locked
    // onto a target and the first two seconds decide the whole match.
    minSpawnDistance: 9,
    // A small entity is faster than a big one, so a chase can in principle run forever.
    // The round is capped and settled on points held until the ring lands in phase 2.
    maxTicks: 2400,
    // Nobody can be eaten for the first few seconds. Without this the round can be
    // decided before the player has their hands on the keys.
    graceTicks: 30,
    pathBudget: 400
  };

  var TILE = { FLOOR: 0, ROCK: 1, TREE: 2, BUSH: 3, WATER: 4 };
  var DIRS = [[0, -1], [1, 0], [0, 1], [-1, 0]];

  var el = {
    setup: document.getElementById('er-setup'),
    picker: document.getElementById('er-element-picker'),
    board: document.getElementById('er-board'),
    seed: document.getElementById('er-seed'),
    reroll: document.getElementById('er-reroll'),
    start: document.getElementById('er-start'),
    points: document.getElementById('er-points'),
    element: document.getElementById('er-element'),
    alive: document.getElementById('er-alive'),
    speed: document.getElementById('er-speed'),
    feed: document.getElementById('er-feed'),
    overlay: document.getElementById('er-overlay'),
    overlayTitle: document.getElementById('er-overlay-title'),
    overlayBody: document.getElementById('er-overlay-body'),
    again: document.getElementById('er-again')
  };

  var elements = Array.isArray(payload.elements) && payload.elements.length
    ? payload.elements
    : fallbackElements();
  var matrix = payload.matrix && typeof payload.matrix === 'object' ? payload.matrix : {};

  var state = {
    seed: 0,
    rng: null,
    tiles: [],
    entities: [],
    player: null,
    tileNodes: [],
    running: false,
    timer: null,
    phase: 'setup',
    chosenElement: 0,
    spawn: null,
    heldDir: null,
    // A tap can begin and end between two ticks, so the last direction pressed is
    // remembered until it has actually been spent on a step.
    queuedDir: null,
    tick: 0
  };

  function fallbackElements() {
    var list = [];
    var all = window.ElementIcons ? window.ElementIcons.all : {};
    Object.keys(all).forEach(function (id) {
      list.push({ id: Number(id), name: all[id].name });
    });
    return list.sort(function (a, b) { return a.id - b.id; });
  }

  function elementName(id) {
    for (var i = 0; i < elements.length; i++) {
      if (elements[i].id === Number(id)) {
        return elements[i].name;
      }
    }
    return window.ElementIcons ? window.ElementIcons.name(id) : 'Element';
  }

  function elementColor(id) {
    return window.ElementIcons ? window.ElementIcons.color(id) : '#b9c6d6';
  }

  function elementGlyph(id, size) {
    return window.ElementIcons ? window.ElementIcons.svg(id, size || 14) : '';
  }

  // Raw table lookup. Missing rows mean "no database", which reads as neutral.
  function effectiveness(attacker, defender) {
    var row = matrix[attacker] || matrix[String(attacker)];
    if (!row) {
      return 1;
    }
    var value = row[defender];
    if (value === undefined) {
      value = row[String(defender)];
    }
    return value === undefined ? 1 : Number(value);
  }

  function scaled(attacker, defender) {
    return Math.max(effectiveness(attacker, defender), CONFIG.effFloor);
  }

  // A collision is mutual, so both directions of element_calc are consulted and each
  // side hits with its own number scaled by its own matchup. The mover wins ties.
  function predict(mover, target) {
    var moverPower = mover.points * scaled(mover.elementId, target.elementId);
    var targetPower = target.points * scaled(target.elementId, mover.elementId);
    return {
      moverPower: moverPower,
      targetPower: targetPower,
      moverWins: moverPower >= targetPower
    };
  }

  function mulberry32(seed) {
    var a = seed >>> 0;
    return function () {
      a += 0x6d2b79f5;
      var t = a;
      t = Math.imul(t ^ (t >>> 15), t | 1);
      t ^= t + Math.imul(t ^ (t >>> 7), t | 61);
      return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
    };
  }

  function idx(x, y) {
    return y * CONFIG.cols + x;
  }

  function inBounds(x, y) {
    return x >= 0 && y >= 0 && x < CONFIG.cols && y < CONFIG.rows;
  }

  function tileAt(x, y) {
    return inBounds(x, y) ? state.tiles[idx(x, y)] : TILE.ROCK;
  }

  function passable(x, y) {
    var t = tileAt(x, y);
    return t === TILE.FLOOR || t === TILE.BUSH;
  }

  function clamp(value, min, max) {
    return value < min ? min : (value > max ? max : value);
  }

  function randInt(max) {
    return Math.floor(state.rng() * max);
  }

  function generateMap() {
    var total = CONFIG.cols * CONFIG.rows;
    state.tiles = new Array(total);
    for (var i = 0; i < total; i++) {
      state.tiles[i] = TILE.FLOOR;
    }

    // Obstacles are drawn as short random walks, which clump into ridges and copses
    // instead of the even static that per-tile rolling produces.
    function blob(type, length) {
      var x = 1 + randInt(CONFIG.cols - 2);
      var y = 1 + randInt(CONFIG.rows - 2);
      for (var step = 0; step < length; step++) {
        state.tiles[idx(x, y)] = type;
        var dir = DIRS[randInt(4)];
        x = clamp(x + dir[0], 1, CONFIG.cols - 2);
        y = clamp(y + dir[1], 1, CONFIG.rows - 2);
      }
    }

    var pools = 2 + randInt(3);
    for (var p = 0; p < pools; p++) {
      blob(TILE.WATER, 12 + randInt(18));
    }
    for (var r = 0; r < 14; r++) {
      blob(TILE.ROCK, 3 + randInt(8));
    }
    for (var t = 0; t < 10; t++) {
      blob(TILE.TREE, 2 + randInt(6));
    }

    var bushes = Math.floor(total * 0.05);
    for (var b = 0; b < bushes; b++) {
      var bx = randInt(CONFIG.cols);
      var by = randInt(CONFIG.rows);
      if (state.tiles[idx(bx, by)] === TILE.FLOOR) {
        state.tiles[idx(bx, by)] = TILE.BUSH;
      }
    }

    sealIsolatedPockets();
  }

  // Keep only the largest walkable region. Anything cut off becomes rock, so nobody
  // ever spawns inside a sealed pocket and stalls the round.
  function sealIsolatedPockets() {
    var total = CONFIG.cols * CONFIG.rows;
    var seen = new Array(total).fill(false);
    var best = [];

    for (var start = 0; start < total; start++) {
      if (seen[start] || !passable(start % CONFIG.cols, Math.floor(start / CONFIG.cols))) {
        continue;
      }
      var region = [];
      var queue = [start];
      seen[start] = true;
      while (queue.length) {
        var cur = queue.pop();
        region.push(cur);
        var cx = cur % CONFIG.cols;
        var cy = Math.floor(cur / CONFIG.cols);
        for (var d = 0; d < 4; d++) {
          var nx = cx + DIRS[d][0];
          var ny = cy + DIRS[d][1];
          if (!inBounds(nx, ny) || seen[idx(nx, ny)] || !passable(nx, ny)) {
            continue;
          }
          seen[idx(nx, ny)] = true;
          queue.push(idx(nx, ny));
        }
      }
      if (region.length > best.length) {
        best = region;
      }
    }

    var keep = new Array(total).fill(false);
    best.forEach(function (cell) { keep[cell] = true; });
    for (var cell = 0; cell < total; cell++) {
      if (!keep[cell] && passable(cell % CONFIG.cols, Math.floor(cell / CONFIG.cols))) {
        state.tiles[cell] = TILE.ROCK;
      }
    }
  }

  function openTiles() {
    var list = [];
    for (var y = 0; y < CONFIG.rows; y++) {
      for (var x = 0; x < CONFIG.cols; x++) {
        if (passable(x, y)) {
          list.push({ x: x, y: y });
        }
      }
    }
    return list;
  }

  function entityAt(x, y) {
    for (var i = 0; i < state.entities.length; i++) {
      var e = state.entities[i];
      if (e.alive && e.x === x && e.y === y) {
        return e;
      }
    }
    return null;
  }

  function moveCooldown(entity) {
    return CONFIG.baseCooldown + Math.floor(entity.points / CONFIG.pointsPerCooldown);
  }

  function makeEntity(spec) {
    return {
      id: spec.id,
      x: spec.x,
      y: spec.y,
      elementId: spec.elementId,
      points: CONFIG.startPoints,
      alive: true,
      isPlayer: Boolean(spec.isPlayer),
      timer: 0,
      heading: DIRS[randInt(4)],
      node: null
    };
  }

  function spawnField() {
    state.entities = [];
    var open = openTiles();
    var taken = [];

    function farEnough(cell) {
      for (var i = 0; i < taken.length; i++) {
        if (Math.abs(taken[i].x - cell.x) + Math.abs(taken[i].y - cell.y) < CONFIG.minSpawnDistance) {
          return false;
        }
      }
      return true;
    }

    var player = makeEntity({
      id: 0,
      x: state.spawn.x,
      y: state.spawn.y,
      elementId: state.chosenElement,
      isPlayer: true
    });
    state.player = player;
    state.entities.push(player);
    taken.push({ x: player.x, y: player.y });

    var attempts = 0;
    while (state.entities.length <= CONFIG.npcCount && attempts < 4000) {
      attempts++;
      var cell = open[randInt(open.length)];
      if (!farEnough(cell) || entityAt(cell.x, cell.y)) {
        continue;
      }
      var pick = elements[randInt(elements.length)];
      state.entities.push(makeEntity({
        id: state.entities.length,
        x: cell.x,
        y: cell.y,
        elementId: pick.id
      }));
      taken.push(cell);
    }
  }

  function buildBoard() {
    el.board.style.setProperty('--er-cols', CONFIG.cols);
    el.board.style.setProperty('--er-rows', CONFIG.rows);
    el.board.innerHTML = '';
    state.tileNodes = [];

    var fragment = document.createDocumentFragment();
    for (var y = 0; y < CONFIG.rows; y++) {
      for (var x = 0; x < CONFIG.cols; x++) {
        var node = document.createElement('div');
        node.className = 'er-tile er-tile-' + state.tiles[idx(x, y)];
        if (state.phase === 'setup' && passable(x, y)) {
          node.classList.add('is-open');
          node.dataset.x = x;
          node.dataset.y = y;
        }
        state.tileNodes.push(node);
        fragment.appendChild(node);
      }
    }
    el.board.appendChild(fragment);
  }

  function markSpawn() {
    state.tileNodes.forEach(function (node) { node.classList.remove('is-spawn'); });
    if (state.spawn) {
      var node = state.tileNodes[idx(state.spawn.x, state.spawn.y)];
      if (node) {
        node.classList.add('is-spawn');
      }
    }
  }

  function drawEntities() {
    state.entities.forEach(function (entity) {
      if (!entity.alive) {
        return;
      }
      var node = document.createElement('div');
      node.className = 'er-entity' + (entity.isPlayer ? ' is-player' : '');
      node.style.color = elementColor(entity.elementId);
      node.innerHTML = elementGlyph(entity.elementId, 13) + '<span class="er-entity-points"></span>';
      entity.node = node;
      el.board.appendChild(node);
      positionEntity(entity);
    });
  }

  function positionEntity(entity) {
    if (!entity.node) {
      return;
    }
    entity.node.style.transform = 'translate(' + (entity.x * 100) + '%,' + (entity.y * 100) + '%)';
    entity.node.classList.toggle('is-hidden', tileAt(entity.x, entity.y) === TILE.BUSH);
    var label = entity.node.querySelector('.er-entity-points');
    if (label) {
      label.textContent = entity.points;
    }
  }

  function feed(message) {
    var item = document.createElement('li');
    item.textContent = message;
    el.feed.insertBefore(item, el.feed.firstChild);
    while (el.feed.children.length > 6) {
      el.feed.removeChild(el.feed.lastChild);
    }
  }

  function livingCount() {
    return state.entities.filter(function (e) { return e.alive; }).length;
  }

  function updateHud() {
    var player = state.player;
    el.points.textContent = player && player.alive ? player.points : 0;
    el.element.textContent = player ? elementName(player.elementId) : '-';
    el.alive.textContent = livingCount();
    el.speed.textContent = player && player.alive
      ? (moveCooldown(player) * CONFIG.tickMs) + 'ms'
      : '-';
  }

  function kill(entity, winner) {
    entity.alive = false;
    if (entity.node && entity.node.parentNode) {
      entity.node.parentNode.removeChild(entity.node);
    }
    entity.node = null;
    if (entity.isPlayer) {
      endRound(false, winner);
    }
  }

  function resolveCollision(mover, target) {
    var result = predict(mover, target);
    var winner = result.moverWins ? mover : target;
    var loser = result.moverWins ? target : mover;

    winner.points += loser.points;
    kill(loser, winner);
    positionEntity(winner);

    if (mover.isPlayer || target.isPlayer) {
      var youWon = winner.isPlayer;
      feed(youWon
        ? 'You absorbed ' + elementName(loser.elementId) + ' ' + loser.points + '. Now at ' + winner.points + '.'
        : elementName(winner.elementId) + ' ' + winner.points + ' absorbed you.');
    }

    return result.moverWins;
  }

  function tryMove(entity, dx, dy) {
    var nx = entity.x + dx;
    var ny = entity.y + dy;
    if (!inBounds(nx, ny) || !passable(nx, ny)) {
      return false;
    }

    var occupant = entityAt(nx, ny);
    if (occupant) {
      // During spawn protection an occupied tile is simply a wall.
      if (state.tick < CONFIG.graceTicks) {
        return false;
      }
      var moverWon = resolveCollision(entity, occupant);
      if (moverWon && entity.alive) {
        entity.x = nx;
        entity.y = ny;
        positionEntity(entity);
      }
      return true;
    }

    entity.x = nx;
    entity.y = ny;
    positionEntity(entity);
    return true;
  }

  function currentSight() {
    return CONFIG.sightRadius + Math.floor(state.tick / CONFIG.sightGrowthTicks);
  }

  // Entities standing in a bush cannot be seen, by the AI or by anyone else.
  function visibleTo(entity, other) {
    if (!other.alive || other === entity) {
      return false;
    }
    if (tileAt(other.x, other.y) === TILE.BUSH) {
      return false;
    }
    return manhattan(entity, other) <= currentSight();
  }

  function manhattan(a, b) {
    return Math.abs(a.x - b.x) + Math.abs(a.y - b.y);
  }

  // Breadth first search capped at a node budget, so a hunter walks around a ridge
  // instead of grinding its face against it.
  function stepToward(entity, target) {
    var startKey = idx(entity.x, entity.y);
    var cameFrom = {};
    var queue = [startKey];
    var seen = {};
    seen[startKey] = true;
    var visited = 0;

    while (queue.length && visited < CONFIG.pathBudget) {
      var cur = queue.shift();
      visited++;
      var cx = cur % CONFIG.cols;
      var cy = Math.floor(cur / CONFIG.cols);

      if (cx === target.x && cy === target.y) {
        var node = cur;
        while (cameFrom[node] !== undefined && cameFrom[node] !== startKey) {
          node = cameFrom[node];
        }
        if (cameFrom[node] === undefined) {
          return null;
        }
        return [node % CONFIG.cols - entity.x, Math.floor(node / CONFIG.cols) - entity.y];
      }

      for (var d = 0; d < 4; d++) {
        var nx = cx + DIRS[d][0];
        var ny = cy + DIRS[d][1];
        if (!inBounds(nx, ny) || !passable(nx, ny)) {
          continue;
        }
        var key = idx(nx, ny);
        if (seen[key]) {
          continue;
        }
        seen[key] = true;
        cameFrom[key] = cur;
        queue.push(key);
      }
    }
    return null;
  }

  function stepAwayFrom(entity, threat) {
    var best = null;
    var bestScore = -Infinity;
    for (var d = 0; d < 4; d++) {
      var nx = entity.x + DIRS[d][0];
      var ny = entity.y + DIRS[d][1];
      if (!inBounds(nx, ny) || !passable(nx, ny)) {
        continue;
      }
      var score = Math.abs(nx - threat.x) + Math.abs(ny - threat.y);
      // A bush breaks line of sight entirely, so it beats raw distance.
      if (tileAt(nx, ny) === TILE.BUSH) {
        score += 6;
      }
      if (score > bestScore) {
        bestScore = score;
        best = DIRS[d];
      }
    }
    return best;
  }

  function wanderStep(entity) {
    if (state.rng() < 0.25) {
      entity.heading = DIRS[randInt(4)];
    }
    for (var attempt = 0; attempt < 4; attempt++) {
      var nx = entity.x + entity.heading[0];
      var ny = entity.y + entity.heading[1];
      if (inBounds(nx, ny) && passable(nx, ny)) {
        return entity.heading;
      }
      entity.heading = DIRS[randInt(4)];
    }
    return null;
  }

  // Phase 1 ships a single personality: hunt anything the collision formula says it
  // beats, run from anything it does not.
  function hunterStep(entity) {
    // While nobody can be eaten, hunting would just pile everyone up against their
    // target and unleash a pile of simultaneous kills the moment grace lifts.
    if (state.tick < CONFIG.graceTicks) {
      return wanderStep(entity);
    }

    var prey = null;
    var preyDist = Infinity;
    var threat = null;
    var threatDist = Infinity;

    for (var i = 0; i < state.entities.length; i++) {
      var other = state.entities[i];
      if (!visibleTo(entity, other)) {
        continue;
      }
      var dist = manhattan(entity, other);
      var attacking = predict(entity, other);
      if (attacking.moverWins) {
        if (dist < preyDist) {
          preyDist = dist;
          prey = other;
        }
      } else if (dist < threatDist) {
        threatDist = dist;
        threat = other;
      }
    }

    if (threat && threatDist <= CONFIG.fleeRadius) {
      return stepAwayFrom(entity, threat) || wanderStep(entity);
    }
    if (prey) {
      return stepToward(entity, prey) || wanderStep(entity);
    }
    return wanderStep(entity);
  }

  function tick() {
    if (!state.running) {
      return;
    }
    state.tick++;

    state.entities.forEach(function (entity) {
      if (!entity.alive) {
        return;
      }
      entity.timer++;
      if (entity.timer < moveCooldown(entity)) {
        return;
      }

      var step = null;
      if (entity.isPlayer) {
        step = state.heldDir || state.queuedDir;
      } else {
        step = hunterStep(entity);
      }

      if (step && (step[0] !== 0 || step[1] !== 0)) {
        if (tryMove(entity, step[0], step[1])) {
          entity.timer = 0;
        }
        if (entity.isPlayer) {
          state.queuedDir = null;
        }
      }
    });

    updateHud();

    if (state.player && state.player.alive && livingCount() === 1) {
      endRound(true, null);
      return;
    }
    if (state.tick >= CONFIG.maxTicks) {
      endRound(false, null, true);
    }
  }

  function endRound(won, winner, timeout) {
    state.running = false;
    window.clearInterval(state.timer);
    state.timer = null;
    state.phase = 'over';

    var points = state.player ? state.player.points : 0;
    el.overlay.hidden = false;

    if (won) {
      el.overlayTitle.textContent = 'Last one standing';
      el.overlayBody.textContent = 'You finished the map with ' + points + ' points.';
    } else if (timeout) {
      el.overlayTitle.textContent = 'Time called';
      el.overlayBody.textContent = 'The round ran out with ' + livingCount() + ' entities still alive. ' +
        'You kept ' + points + ' points.';
    } else {
      el.overlayTitle.textContent = 'You were absorbed';
      el.overlayBody.textContent = 'A ' +
        (winner ? elementName(winner.elementId) + ' carrying ' + winner.points : 'rival') +
        ' ate you. You had ' + points + ' points, and ' + livingCount() + ' entities are still out there.';
    }
    updateHud();
  }

  function newMap(seed) {
    state.seed = seed === undefined ? Math.floor(Math.random() * 4294967296) : seed;
    state.rng = mulberry32(state.seed);
    state.phase = 'setup';
    state.spawn = null;
    state.entities = [];
    state.player = null;
    state.running = false;
    state.tick = 0;
    state.heldDir = null;
    state.queuedDir = null;
    el.seed.textContent = state.seed;
    el.overlay.hidden = true;
    el.board.classList.add('is-picking');
    el.feed.innerHTML = '';
    generateMap();
    buildBoard();
    refreshStartButton();
    updateHud();
  }

  function refreshStartButton() {
    el.start.disabled = !(state.chosenElement && state.spawn);
  }

  function buildPicker() {
    el.picker.innerHTML = '';
    elements.forEach(function (element) {
      var button = document.createElement('button');
      button.type = 'button';
      button.className = 'er-element-option';
      button.style.color = elementColor(element.id);
      button.innerHTML = elementGlyph(element.id, 22) +
        '<span class="er-element-name">' + element.name + '</span>';
      button.addEventListener('click', function () {
        state.chosenElement = element.id;
        Array.prototype.forEach.call(el.picker.children, function (child) {
          child.classList.remove('is-selected');
        });
        button.classList.add('is-selected');
        refreshStartButton();
      });
      el.picker.appendChild(button);
    });
  }

  function startRound() {
    if (!state.chosenElement || !state.spawn) {
      return;
    }
    state.phase = 'playing';
    el.board.classList.remove('is-picking');
    el.setup.hidden = true;
    buildBoard();
    spawnField();
    drawEntities();
    updateHud();
    feed('Round start. ' + livingCount() + ' entities, nobody can be eaten for ' +
      Math.round(CONFIG.graceTicks * CONFIG.tickMs / 1000) + ' seconds.');
    state.running = true;
    state.timer = window.setInterval(tick, CONFIG.tickMs);
  }

  el.board.addEventListener('click', function (event) {
    if (state.phase !== 'setup') {
      return;
    }
    var tile = event.target.closest ? event.target.closest('.er-tile.is-open') : null;
    if (!tile) {
      return;
    }
    state.spawn = { x: Number(tile.dataset.x), y: Number(tile.dataset.y) };
    markSpawn();
    refreshStartButton();
  });

  el.reroll.addEventListener('click', function () { newMap(); });
  el.start.addEventListener('click', startRound);
  el.again.addEventListener('click', function () {
    el.setup.hidden = false;
    newMap();
  });

  var KEYS = {
    ArrowUp: [0, -1], ArrowRight: [1, 0], ArrowDown: [0, 1], ArrowLeft: [-1, 0],
    w: [0, -1], d: [1, 0], s: [0, 1], a: [-1, 0],
    W: [0, -1], D: [1, 0], S: [0, 1], A: [-1, 0]
  };

  document.addEventListener('keydown', function (event) {
    var dir = KEYS[event.key];
    if (!dir || state.phase !== 'playing') {
      return;
    }
    event.preventDefault();
    state.heldDir = dir;
    state.queuedDir = dir;
  });

  document.addEventListener('keyup', function (event) {
    var dir = KEYS[event.key];
    if (!dir) {
      return;
    }
    if (state.heldDir && state.heldDir[0] === dir[0] && state.heldDir[1] === dir[1]) {
      state.heldDir = null;
    }
  });

  buildPicker();
  newMap();
})();
