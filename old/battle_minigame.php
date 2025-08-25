<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Mini Battle — Pokémon‑style</title>
  <style>
    :root{
      --bg:#0b1226; /* deep night blue */
      --panel:#131b36;
      --panel-2:#10162b;
      --text:#e8ecff;
      --muted:#9a9fc0;
      --accent:#6ea8ff;
      --accent-2:#7cdaff;
      --danger:#ff6b6b;
      --success:#78e08f;
      --shadow:0 12px 30px rgba(0,0,0,.35);
      --ui-shadow:0 6px 18px rgba(0,0,0,.25);
    }
    *{box-sizing:border-box}
    html,body{height:100%}
    body{
      margin:0;
      font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji","Segoe UI Emoji";
      background: radial-gradient(1200px 600px at 50% -10%, #1a2a60 0%, transparent 60%) , var(--bg);
      color:var(--text);
      display:flex;
      align-items:center;
      justify-content:center;
      padding:16px;
    }
    .game{
      width:min(100%, 1100px);
      background:linear-gradient(180deg, #0f1937 0%, #0c142e 100%);
      border:1px solid #1f2a55;
      border-radius:20px;
      box-shadow:var(--shadow);
      overflow:hidden;
    }
    .battlefield{
      position:relative;
      height:420px;
      padding:24px;
      display:grid;
      grid-template-columns: 1fr 1fr;
      gap:16px;
      background: radial-gradient(1200px 480px at 50% 0%, #1a2d6a 0%, transparent 55%) , linear-gradient(180deg, #0e1834 0%, #0b1226 100%);
    }
    .side{
      position:relative;
      border-radius:16px;
      background: linear-gradient(180deg, var(--panel) 0%, var(--panel-2) 100%);
      border:1px solid #223064;
      box-shadow: var(--ui-shadow);
      padding:16px 16px 8px;
      overflow:hidden;
    }
    .side.right{ transform:translateY(12px); }
    .side.left{ transform:translateY(-12px); }

    .nameplate{
      display:flex; align-items:center; justify-content:space-between;
      gap:12px; margin-bottom:8px;
      font-weight:700;
    }
    .nameplate .labels{ display:flex; flex-direction:column; gap:2px; }
    .nameplate small{ color:var(--muted); font-weight:600; letter-spacing:.4px; }

    .hpbar{ position:relative; width:100%; height:12px; background:#0a0f22; border-radius:999px; overflow:hidden; border:1px solid #1d2a52; }
    .hpbar > .fill{ position:absolute; inset:0; width:100%; transform-origin:left center; background:linear-gradient(90deg, #6affc0, #78e08f); }
    .hpbar[data-state="warn"] .fill{ background:linear-gradient(90deg, #ffd66a, #ffaf6a); }
    .hpbar[data-state="danger"] .fill{ background:linear-gradient(90deg, #ff8b8b, #ff6b6b); }

    .arena{
      position:relative;
      height: 290px;
      border-radius:12px;
      background: radial-gradient(220px 40px at 30% 90%, rgba(120,160,255,.18), transparent 60%), radial-gradient(220px 40px at 70% 90%, rgba(120,160,255,.18), transparent 60%);
      overflow:hidden;
    }
    .platform{ position:absolute; width:48%; height:10px; left:6%; bottom:38px; border-radius:50%; background: radial-gradient(closest-side, rgba(0,0,0,.4), rgba(0,0,0,0)); filter:blur(2px); }
    .platform.enemy{ left:auto; right:6%; }

    .creature{
      position:absolute; bottom:60px; left:10%; width:40%; aspect-ratio:1/1; display:flex; align-items:end; justify-content:center;
      transform-origin:50% 100%;
      animation: breatheLeft 3.8s ease-in-out infinite;
    }
    .creature.enemy{ left:auto; right:10%; animation: breatheRight 3.3s ease-in-out infinite; }
    .sprite{
      width:100%; height:100%;
      image-rendering:auto;
      filter: drop-shadow(0 10px 12px rgba(0,0,0,.35));
      transform-origin:50% 100%;
    }

    @keyframes breatheLeft { 0%,100%{ transform: translateY(0) scale(1);} 50%{ transform: translateY(-3%) scale(1.03);} }
    @keyframes breatheRight { 0%,100%{ transform: translateY(0) scale(1);} 50%{ transform: translateY(-2%) scale(1.02);} }

    /* Hit shake */
    .hitshake{ animation: hitShake .25s linear 1; }
    @keyframes hitShake { 0%{ transform: translate(0,0);} 20%{ transform: translate(-6px,2px);} 40%{ transform: translate(5px,-2px);} 60%{ transform: translate(-4px,1px);} 80%{ transform: translate(3px,0);} 100%{ transform: translate(0,0);} }

    /* Floating damage text */
    .dmg{ position:absolute; font-weight:900; color:var(--danger); text-shadow:0 2px 0 rgba(0,0,0,.35); pointer-events:none; animation: floatUp .9s ease-out forwards; }
    .heal{ color:var(--success); }
    @keyframes floatUp { from{ transform: translate(-50%,0) scale(1); opacity:1;} to{ transform: translate(-50%,-80px) scale(1.1); opacity:0;} }

    /* Slash effect */
    .slash{ position:absolute; width:220px; height:36px; background:linear-gradient(90deg, rgba(255,255,255,.0) 0%, rgba(255,255,255,.9) 30%, rgba(255,255,255,.0) 100%);
      filter: drop-shadow(0 0 8px rgba(255,255,255,.6)); border-radius:40px; transform: rotate(-18deg) translateX(-260px); animation: slashAcross .45s ease-out forwards; }
    @keyframes slashAcross { to{ transform: rotate(-18deg) translateX(70px);} }

    /* Water droplets */
    .water-spray{ position:absolute; left:0; top:0; width:100%; height:100%; pointer-events:none; overflow:visible; }
    .droplet{ position:absolute; width:12px; height:12px; border-radius:50%; background: radial-gradient(circle at 30% 30%, #dfffff 5%, #8fe8ff 40%, #3dbbff 70%, #1aa8ff 100%); opacity:.95; filter:drop-shadow(0 2px 4px rgba(0,0,0,.35)); animation: dropletFly .5s cubic-bezier(.2,.7,.1,1) forwards; }
    @keyframes dropletFly { from{ transform:translate(0,0) scale(.9); opacity:.95;} to{ transform:translate(var(--dx), var(--dy)) scale(.85); opacity:.1;} }

    /* Faint (KO) */
    .faint{ animation: faint 600ms ease-in forwards; }
    @keyframes faint { to{ transform: translateY(40px) scale(.8); filter:grayscale(.7) brightness(.4) drop-shadow(0 4px 8px rgba(0,0,0,.4)); opacity:.0; } }

    /* UI: controls & log */
    .hud{ display:grid; grid-template-columns: 1.4fr .9fr; gap:16px; padding:16px; background:linear-gradient(180deg, #0b1226 0%, #0b1226 100%); border-top:1px solid #1d2a52; }
    .log{ background:linear-gradient(180deg, var(--panel) 0%, var(--panel-2) 100%); border:1px solid #223064; border-radius:12px; padding:12px; height:132px; overflow:auto; box-shadow:var(--ui-shadow); font-size:14px; line-height:1.35; }
    .log p{ margin:.35rem 0; color:#cfd6ff; }

    .controls{ display:grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap:10px; }
    button.move{ appearance:none; border:1px solid #233164; background:linear-gradient(180deg, #1b2550, #141c3c); color:var(--text); font-weight:800; border-radius:12px; padding:14px 12px; cursor:pointer; box-shadow: var(--ui-shadow); text-align:left; }
    button.move small{ display:block; font-weight:700; color:var(--muted); letter-spacing:.3px; }
    button.move:hover{ filter:brightness(1.1); outline: none; }
    button.move:disabled{ opacity:.55; cursor:not-allowed; }
    .row{ display:flex; gap:8px; align-items:center; justify-content:space-between; }

    .tiny{ font-size:12px; color:var(--muted); }

    @media (max-width: 860px){
      .battlefield{ height: 380px; }
      .hud{ grid-template-columns: 1fr; }
      .controls{ grid-template-columns: 1fr 1fr; }
    }
  </style>
</head>
<body>
  <div class="game" role="application" aria-label="Pokémon-style battle minigame">
    <div class="battlefield">
      <div class="side left" id="side-left">
        <div class="nameplate">
          <div class="labels">
            <div id="left-name">Aqua Pup</div>
            <small class="tiny">Player • Lv 5</small>
          </div>
          <div style="min-width:190px">
            <div class="row" style="margin-bottom:6px;">
              <span class="tiny">HP</span>
              <span class="tiny" id="left-hp-text">35/35</span>
            </div>
            <div class="hpbar" id="left-hp"><div class="fill" style="transform:scaleX(1)"></div></div>
          </div>
        </div>
        <div class="arena">
          <div class="platform"></div>
          <div class="creature" id="left-creature">
            <img class="sprite" id="left-sprite" alt="Player creature" />
          </div>
        </div>
      </div>

      <div class="side right" id="side-right">
        <div class="nameplate">
          <div class="labels">
            <div id="right-name">Cactus Cat</div>
            <small class="tiny">Enemy • Lv 5</small>
          </div>
          <div style="min-width:190px">
            <div class="row" style="margin-bottom:6px;">
              <span class="tiny">HP</span>
              <span class="tiny" id="right-hp-text">35/35</span>
            </div>
            <div class="hpbar" id="right-hp"><div class="fill" style="transform:scaleX(1)"></div></div>
          </div>
        </div>
        <div class="arena">
          <div class="platform enemy"></div>
          <div class="creature enemy" id="right-creature">
            <img class="sprite" id="right-sprite" alt="Enemy creature" />
          </div>
        </div>
      </div>
    </div>

    <div class="hud">
      <div class="log" id="log" aria-live="polite"></div>

      <div>
        <div class="controls" id="controls"></div>
        <div class="row" style="margin-top:8px;">
          <span class="tiny"><a href="index.php">Return to index</a></span>
          <button id="reset" class="move" style="padding:10px 12px; font-size:13px;">Reset</button>
        </div>
      </div>
    </div>
  </div>

  <script>
  /*
   * 🔧 QUICK CUSTOMIZATION (data-driven)
   * -----------------------------------
   * 1) If you define a global window.GAME_DATA before this script, it will be used.
   *    Shape:
   *    window.GAME_DATA = {
   *      creatures: {
   *        player: { name, level, maxHp, hp, spriteSrc },
   *        enemy:  { name, level, maxHp, hp, spriteSrc }
   *      },
   *      moves: {
   *        player: [ { name, power, type, effect, sfx }, ... ],
   *        enemy:  [ { name, power, type, effect, sfx }, ... ]
   *      }
   *    }
   *
   * 2) Replace sprites with your PNG paths later (e.g. "/assets/creatures/aqua-pup.png").
   *    If omitted, we show crisp smiley faces generated from inline SVG (so you can start right away).
   *
   * 3) SFX live at root-relative paths like:  sounds/sfx/hit.wav  (or .ogg)
   *    We'll try .wav first then .ogg. File names come from each move's {sfx}.
   *
   * 4) Hooking up to PHP/MySQL: simply echo a JSON blob that sets window.GAME_DATA
   *    before this script tag, then this code will pick it up automatically.
   */

  // Inline SVG smileys as default placeholders (easily swapped for PNGs)
  const SMILEY_BLUE = 'data:image/svg+xml;utf8,' + encodeURIComponent(`
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200">
      <defs>
        <radialGradient id="g" cx="50%" cy="40%" r="60%">
          <stop offset="0%" stop-color="#bfe6ff"/>
          <stop offset="70%" stop-color="#5dbaff"/>
          <stop offset="100%" stop-color="#2a8ef2"/>
        </radialGradient>
      </defs>
      <circle cx="100" cy="100" r="92" fill="url(#g)"/>
      <circle cx="70" cy="80" r="10" fill="#0b2038"/>
      <circle cx="130" cy="80" r="10" fill="#0b2038"/>
      <path d="M60 125q40 30 80 0" stroke="#0b2038" stroke-width="10" fill="none" stroke-linecap="round"/>
    </svg>`);

  const SMILEY_GREEN = 'data:image/svg+xml;utf8,' + encodeURIComponent(`
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200">
      <defs>
        <radialGradient id="g2" cx="50%" cy="40%" r="60%">
          <stop offset="0%" stop-color="#e6ffd9"/>
          <stop offset="70%" stop-color="#9def78"/>
          <stop offset="100%" stop-color="#4dc065"/>
        </radialGradient>
      </defs>
      <circle cx="100" cy="100" r="92" fill="url(#g2)"/>
      <circle cx="70" cy="80" r="10" fill="#08210f"/>
      <circle cx="130" cy="80" r="10" fill="#08210f"/>
      <path d="M60 130q40 22 80 0" stroke="#08210f" stroke-width="10" fill="none" stroke-linecap="round"/>
      <path d="M100 50q-8 10 0 20q8-10 0-20z" fill="#fff" fill-opacity="0.4"/>
    </svg>`);

  const DEFAULT_DATA = {
    creatures: {
      player: { name: 'Aqua Pup', level: 5, maxHp: 90, hp: 90, spriteSrc: SMILEY_BLUE },
      enemy:  { name: 'Cactus Cat', level: 5, maxHp: 90, hp: 90, spriteSrc: SMILEY_GREEN }
    },
    moves: {
      player: [
        { name:'Water Jet', power: 18, type:'water', effect:'water', sfx:'water' },
        { name:'Swipe',     power: 14, type:'physical', effect:'slash', sfx:'slash' },
        { name:'Focus',     power: 0,  type:'status', effect:'heal', sfx:'ui_confirm' },
        { name:'Tackle',    power: 12, type:'physical', effect:'slash', sfx:'hit' },
      ],
      enemy: [
        { name:'Prickle',   power: 12, type:'physical', effect:'slash', sfx:'hit' },
        { name:'Spit',      power: 15, type:'water',    effect:'water', sfx:'water' },
      ]
    }
  };

  const DATA = window.GAME_DATA ? window.GAME_DATA : DEFAULT_DATA;

  // DOM refs
  const leftName = document.getElementById('left-name');
  const rightName = document.getElementById('right-name');
  const leftHPBar = document.getElementById('left-hp');
  const rightHPBar = document.getElementById('right-hp');
  const leftHpText = document.getElementById('left-hp-text');
  const rightHpText = document.getElementById('right-hp-text');
  const leftSprite = document.getElementById('left-sprite');
  const rightSprite = document.getElementById('right-sprite');
  const sideLeft = document.getElementById('side-left');
  const sideRight = document.getElementById('side-right');
  const controls = document.getElementById('controls');
  const logBox = document.getElementById('log');
  const resetBtn = document.getElementById('reset');

  const AUDIO_PATH = 'sounds/sfx/'; // root-relative per your requirement

  let state;

  function init(){
    state = {
      player: { ...DATA.creatures.player },
      enemy:  { ...DATA.creatures.enemy },
      moves: { player: DATA.moves.player.map(m=>({ ...m })), enemy: DATA.moves.enemy.map(m=>({ ...m })) },
      turn: 'player',
      busy: false,
      started: false
    };

    // Names & levels
    leftName.textContent = `${state.player.name}`;
    rightName.textContent = `${state.enemy.name}`;

    // HP
    updateHP('player');
    updateHP('enemy');

    // Sprites
    setSprite('left', state.player.spriteSrc);
    setSprite('right', state.enemy.spriteSrc);

    // Moves UI
    renderMoves();

    logBox.innerHTML = '';
    say(`A wild <b>${escapeHtml(state.enemy.name)}</b> appeared!`);
    say(`Go! <b>${escapeHtml(state.player.name)}</b>!`);
  }

  function setSprite(side, src){
    const img = side === 'left' ? leftSprite : rightSprite;
    img.src = src || (side==='left'? SMILEY_BLUE : SMILEY_GREEN);
  }

  function updateHP(who){
    const c = who==='player' ? state.player : state.enemy;
    const hpBar = who==='player' ? leftHPBar : rightHPBar;
    const hpText = who==='player' ? leftHpText : rightHpText;
    const pct = Math.max(0, c.hp) / c.maxHp;
    hpBar.querySelector('.fill').style.transform = `scaleX(${pct})`;
    hpText.textContent = `${Math.max(0, Math.ceil(c.hp))}/${c.maxHp}`;
    hpBar.dataset.state = pct < 0.3 ? 'danger' : (pct < 0.6 ? 'warn' : 'ok');
  }

  function renderMoves(){
    controls.innerHTML = '';
    state.moves.player.forEach((mv, i)=>{
      const btn = document.createElement('button');
      btn.className = 'move';
      btn.innerHTML = `<div>${escapeHtml(mv.name)}</div><small>${escapeHtml(mv.type)} • PWR ${mv.power}</small>`;
      btn.onclick = ()=> handlePlayerMove(i);
      controls.appendChild(btn);
    });
  }

  function say(html){
    const p = document.createElement('p');
    p.innerHTML = html;
    logBox.appendChild(p);
    logBox.scrollTop = logBox.scrollHeight;
  }

  function escapeHtml(s){ return String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[m])); }

  function setBusy(b){
    state.busy = b; [...document.querySelectorAll('button.move')].forEach(btn=> btn.disabled = b);
  }

  function playSfx(name){
    if(!name) return;
    const tryPlay = (ext)=> new Promise(res=>{
      const a = new Audio(`${AUDIO_PATH}${name}.${ext}`);
      a.volume = 0.9;
      a.oncanplay = ()=>{}; // hint
      a.onended = ()=> res(true);
      a.onerror = ()=> res(false);
      a.play().catch(()=> res(false));
      // If autoplay policy blocks, resolve later and ignore.
      setTimeout(()=>res(true), 1200);
    });
    // Try wav first then ogg (non-blocking race, best-effort)
    tryPlay('wav').then(ok=>{ if(!ok) tryPlay('ogg'); });
  }

  function uiPulse(target){
    target.classList.remove('hitshake'); void target.offsetWidth; target.classList.add('hitshake');
  }

  function spawnDamage(targetSide, amount, isHeal=false){
    const sideEl = targetSide==='enemy'? sideRight : sideLeft;
    const rect = sideEl.getBoundingClientRect();
    const dmg = document.createElement('div');
    dmg.className = 'dmg' + (isHeal? ' heal' : '');
    dmg.textContent = (isHeal? '+' : '-') + Math.abs(Math.round(amount));
    dmg.style.left = (Math.random()*40 + 40) + '%';
    dmg.style.bottom = (Math.random()*30 + 70) + 'px';
    sideEl.appendChild(dmg);
    setTimeout(()=> dmg.remove(), 1000);
  }

  function slashEffect(targetSide){
    const sideEl = targetSide==='enemy'? sideRight : sideLeft;
    const slash = document.createElement('div');
    slash.className = 'slash';
    slash.style.top = (Math.random()*120 + 80) + 'px';
    slash.style.left = targetSide==='enemy'? 'calc(100% - 280px)' : '60px';
    if(targetSide==='player') slash.style.transform = 'rotate(18deg) translateX(260px)';
    sideEl.appendChild(slash);
    setTimeout(()=> slash.remove(), 500);
  }

  function waterEffect(fromSide, toSide){
    const fromEl = fromSide==='player'? sideLeft : sideRight;
    const toEl   = toSide==='enemy'? sideRight : sideLeft;
    const spray = document.createElement('div');
    spray.className = 'water-spray';
    fromEl.appendChild(spray);

    const fromRect = fromEl.getBoundingClientRect();
    const toRect = toEl.getBoundingClientRect();

    const startX = fromSide==='player' ? fromRect.right - fromRect.left - 80 : 80;
    const startY = 160 + Math.random()*40;
    const dx = (toRect.left + (toRect.width*0.55)) - (fromRect.left + startX);
    const dy = ((toRect.top + toRect.height*0.45) - (fromRect.top + startY));

    for(let i=0;i<8;i++){
      const drop = document.createElement('div');
      drop.className = 'droplet';
      drop.style.left = startX + (Math.random()*20-10) + 'px';
      drop.style.top  = startY + (Math.random()*20-10) + 'px';
      const wobble = (Math.random()*40-20);
      drop.style.setProperty('--dx', (dx + wobble) + 'px');
      drop.style.setProperty('--dy', (dy + (Math.random()*20-10)) + 'px');
      drop.style.animationDuration = (0.45 + Math.random()*0.2) + 's';
      spray.appendChild(drop);
    }
    setTimeout(()=> spray.remove(), 600);
  }

  function clamp(n, min, max){ return Math.max(min, Math.min(max, n)); }
  const sleep = (ms)=> new Promise(r=> setTimeout(r, ms));

  async function handlePlayerMove(index){
    if(state.busy) return; state.started = true; setBusy(true);
    const mv = state.moves.player[index];
    say(`<b>${escapeHtml(state.player.name)}</b> used <b>${escapeHtml(mv.name)}</b>!`);

    await playerAttack(mv);

    if(state.enemy.hp <= 0){
      await onFaint('enemy');
      setBusy(false);
      return;
    }

    // Enemy turn
    await sleep(350);
    await enemyTurn();
    setBusy(false);
  }

  async function playerAttack(mv){
    // Visuals
    if(mv.effect === 'slash') slashEffect('enemy');
    if(mv.effect === 'water') waterEffect('player','enemy');
    playSfx(mv.sfx || 'hit');

    // Impact
    uiPulse(sideRight);
    await sleep(260);

    const dmg = calcDamage(state.player, state.enemy, mv);
    state.enemy.hp = clamp(state.enemy.hp - dmg, 0, state.enemy.maxHp);
    spawnDamage('enemy', dmg); updateHP('enemy');

    await sleep(250);
  }

  async function enemyTurn(){
    const mv = state.moves.enemy[Math.floor(Math.random()*state.moves.enemy.length)];
    say(`<b>${escapeHtml(state.enemy.name)}</b> used <b>${escapeHtml(mv.name)}</b>!`);

    if(mv.effect === 'slash') slashEffect('player');
    if(mv.effect === 'water') waterEffect('enemy','player');
    playSfx(mv.sfx || 'hit');

    uiPulse(sideLeft);
    await sleep(260);

    const dmg = calcDamage(state.enemy, state.player, mv);
    state.player.hp = clamp(state.player.hp - dmg, 0, state.player.maxHp);
    spawnDamage('player', dmg); updateHP('player');

    if(state.player.hp <= 0){ await onFaint('player'); }
  }

  async function onFaint(who){
    const sprite = who==='player' ? leftSprite : rightSprite;
    say(`<b>${escapeHtml(who==='player'? state.player.name : state.enemy.name)}</b> fainted!`);
    sprite.classList.add('faint');
    playSfx('faint');
    await sleep(650);
    if(who==='enemy') say('<b>You win!</b>'); else say('<b>You were defeated…</b>');
    // Disable moves
    [...document.querySelectorAll('button.move')].forEach(btn=> btn.disabled = true);
  }

  function calcDamage(attacker, defender, mv){
    if(mv.effect === 'heal'){
      const heal = Math.round(attacker.maxHp * 0.18 + Math.random()*4);
      attacker.hp = clamp(attacker.hp + heal, 0, attacker.maxHp);
      spawnDamage(attacker===state.player? 'player':'enemy', heal, true);
      updateHP(attacker===state.player? 'player':'enemy');
      return 0;
    }
    const variance = 0.85 + Math.random()*0.3; // 0.85–1.15
    const base = mv.power || 10;
    const dmg = Math.round(base * variance);
    return dmg;
  }

  // Reset
  resetBtn.addEventListener('click', ()=>{
    // remove lingering effect nodes
    document.querySelectorAll('.water-spray,.dmg,.slash').forEach(n=> n.remove());
    leftSprite.classList.remove('faint');
    rightSprite.classList.remove('faint');
    init();
  });

  // Initialize
  init();

  // EXPOSED HELPERS you can call from your PHP-templated script
  window.BattleAPI = {
    setSpriteLeft: (url)=> setSprite('left', url),
    setSpriteRight: (url)=> setSprite('right', url),
    setData: (dataObj)=>{ window.GAME_DATA = dataObj; Object.assign(DATA, dataObj); init(); },
    say: (text)=> say(escapeHtml(text)),
  };

  </script>
</body>
</html>
