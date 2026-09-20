(function () {
  'use strict';

  var payloadEl = document.getElementById('pkmn-quiz-payload');
  if (!payloadEl) return;
  var payload = JSON.parse(payloadEl.textContent);
  var configs = payload.configs || {};

  var el = {
    timer: document.getElementById('pkmn-quiz-timer'),
    progress: document.getElementById('pkmn-quiz-progress'),
    start: document.getElementById('pkmn-quiz-start'),
    cog: document.getElementById('pkmn-quiz-cog'),
    settings: document.getElementById('pkmn-quiz-settings'),
    configSelect: document.getElementById('pkmn-quiz-config'),
    langSelect: document.getElementById('pkmn-quiz-lang'),
    length: document.getElementById('pkmn-quiz-length'),
    lengthLabel: document.getElementById('pkmn-quiz-length-label'),
    tables: document.getElementById('pkmn-quiz-tables'),
    input: document.getElementById('pkmn-quiz-input'),
    overlay: document.getElementById('pkmn-quiz-overlay'),
    resultTitle: document.getElementById('pkmn-quiz-result-title'),
    resultText: document.getElementById('pkmn-quiz-result-text'),
    submitBlock: document.getElementById('pkmn-quiz-submit-block'),
    initials: document.getElementById('pkmn-quiz-initials'),
    submit: document.getElementById('pkmn-quiz-submit'),
    submitMsg: document.getElementById('pkmn-quiz-submit-msg'),
    again: document.getElementById('pkmn-quiz-again'),
    scoresBody: document.querySelector('#pkmn-quiz-scores tbody')
  };

  var state = {
    configName: payload.defaultConfig,
    lang: 'en',
    duration: 300,
    remaining: 300,
    running: false,
    intervalId: null,
    entries: [],
    matchMap: {},
    guessed: 0
  };

  // "Eisbär!" -> "eisbar": lowercase, drop accents, keep only a-z0-9.
  function normalize(s) {
    return String(s)
      .toLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .replace(/[^a-z0-9]+/g, '');
  }

  function localized(value, lang) {
    if (typeof value === 'string') return value;
    if (!value) return '';
    return value[lang] || value.en || value[Object.keys(value)[0]] || '';
  }

  function formatTime(seconds) {
    var m = Math.floor(seconds / 60);
    var s = seconds % 60;
    return m + ':' + (s < 10 ? '0' : '') + s;
  }

  function currentConfig() {
    return configs[state.configName];
  }

  function configLanguages(config) {
    var langs = (config.meta && config.meta.languages) || ['en'];
    return langs.length ? langs : ['en'];
  }

  // Turn a raw JSON entry (string or object) into a uniform record.
  // The server injects spriteUrl per entry (config prefix decides the path).
  function parseEntry(raw) {
    var names = {};
    var aliases = [];
    var spriteUrl = '';
    if (typeof raw === 'string') {
      names.en = raw;
    } else {
      for (var key in raw) {
        if (key === 'aliases') aliases = raw.aliases || [];
        else if (key === 'sprite') continue;
        else if (key === 'spriteUrl') spriteUrl = raw.spriteUrl || '';
        else names[key] = raw[key];
      }
    }
    return {
      names: names,
      aliases: aliases,
      spriteUrl: spriteUrl,
      guessed: false,
      cellEl: null,
      nameEl: null,
      dotEl: null
    };
  }

  function buildGame() {
    var config = currentConfig();
    if (!config) return;
    state.entries = [];
    state.matchMap = {};
    state.guessed = 0;
    el.tables.textContent = '';

    (config.categories || []).forEach(function (cat) {
      var section = document.createElement('div');
      section.className = 'pkmn-quiz-cat';
      var h = document.createElement('h3');
      h.textContent = localized(cat.name, state.lang);
      section.appendChild(h);
      var grid = document.createElement('div');
      grid.className = 'pkmn-quiz-grid';

      (cat.entries || []).forEach(function (raw) {
        var entry = parseEntry(raw);
        var idx = state.entries.length;
        state.entries.push(entry);

        for (var lang in entry.names) {
          addMatch(normalize(entry.names[lang]), idx);
        }
        entry.aliases.forEach(function (a) { addMatch(normalize(a), idx); });

        var cell = document.createElement('div');
        cell.className = 'pkmn-quiz-cell';
        var dot = document.createElement('span');
        dot.className = 'pkmn-quiz-dot';
        var name = document.createElement('span');
        name.className = 'pkmn-quiz-name';
        var display = localized(entry.names, state.lang);
        name.textContent = display.replace(/\S/g, '?');
        name.dataset.revealed = display;
        cell.appendChild(dot);
        cell.appendChild(name);
        grid.appendChild(cell);
        entry.cellEl = cell;
        entry.nameEl = name;
        entry.dotEl = dot;
      });

      section.appendChild(grid);
      el.tables.appendChild(section);
    });

    updateProgress();
    updateTimerDisplay();
  }

  function addMatch(key, idx) {
    if (!key) return;
    if (!state.matchMap[key]) state.matchMap[key] = [];
    state.matchMap[key].push(idx);
  }

  function updateProgress() {
    el.progress.textContent = state.guessed + ' / ' + state.entries.length;
  }

  function updateTimerDisplay() {
    el.timer.textContent = formatTime(state.running ? state.remaining : state.duration);
    el.timer.classList.toggle('low', state.running && state.remaining <= 30);
  }

  function revealEntry(entry) {
    entry.guessed = true;
    entry.cellEl.classList.add('cleared');
    entry.nameEl.textContent = entry.nameEl.dataset.revealed;
    if (!entry.spriteUrl) return; // no image known: just reveal the name
    var img = document.createElement('img');
    img.src = entry.spriteUrl;
    img.alt = entry.nameEl.dataset.revealed;
    img.width = 50;
    img.height = 50;
    entry.cellEl.replaceChild(img, entry.dotEl);
    entry.dotEl = img;
  }

  function handleGuess() {
    if (!state.running) return;
    var key = normalize(el.input.value);
    if (!key) return;
    var indices = state.matchMap[key];
    if (!indices) return;
    var hit = false;
    indices.forEach(function (idx) {
      var entry = state.entries[idx];
      if (entry.guessed) return; // already cleared: typing it again does nothing
      revealEntry(entry);
      state.guessed++;
      hit = true;
    });
    if (hit) {
      el.input.value = '';
      updateProgress();
      if (state.guessed >= state.entries.length) {
        endGame(true);
      }
    }
  }

  function startGame() {
    stopTimer();
    buildGame();
    state.remaining = state.duration;
    state.running = true;
    el.overlay.classList.remove('visible');
    el.settings.classList.remove('open');
    el.input.disabled = false;
    el.input.value = '';
    el.input.focus();
    el.start.textContent = 'Restart';
    updateTimerDisplay();
    state.intervalId = setInterval(function () {
      state.remaining--;
      updateTimerDisplay();
      if (state.remaining <= 0) {
        endGame(false);
      }
    }, 1000);
  }

  function stopTimer() {
    if (state.intervalId) {
      clearInterval(state.intervalId);
      state.intervalId = null;
    }
  }

  function endGame(allCleared) {
    stopTimer();
    state.running = false;
    el.input.disabled = true;
    el.resultTitle.textContent = allCleared ? 'All creatures named!' : "Time's up!";
    el.resultText.textContent = 'You named ' + state.guessed + ' of ' + state.entries.length + ' creatures.';
    el.submitBlock.style.display = '';
    el.submitMsg.textContent = '';
    el.overlay.classList.add('visible');
  }

  function submitScore() {
    var initials = el.initials.value.trim().toUpperCase().slice(0, 10);
    if (!initials) {
      el.submitMsg.textContent = 'Please enter your initials.';
      return;
    }
    el.submit.disabled = true;
    var body = new URLSearchParams();
    body.set('action', 'submit_score');
    body.set('initials', initials);
    body.set('score', String(state.guessed));
    body.set('timer', String(state.duration));
    body.set('config', state.configName);
    fetch('index.php?pg=pkmn-quiz', { method: 'POST', body: body })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data.ok) throw new Error(data.error || 'Submission failed.');
        renderScores(data.scores);
        el.submitBlock.style.display = 'none';
        el.submitMsg.textContent = 'Score saved!';
      })
      .catch(function (err) {
        el.submit.disabled = false;
        el.submitMsg.textContent = err.message;
      });
  }

  function renderScores(scores) {
    el.scoresBody.textContent = '';
    if (!scores || !scores.length) return;
    scores.forEach(function (row, i) {
      var tr = document.createElement('tr');
      [i + 1, row.initials, row.score, formatTime(parseInt(row.timer, 10) || 0)].forEach(function (v) {
        var td = document.createElement('td');
        td.textContent = String(v);
        tr.appendChild(td);
      });
      el.scoresBody.appendChild(tr);
    });
  }

  function populateSettings() {
    el.configSelect.textContent = '';
    Object.keys(configs).forEach(function (file) {
      var opt = document.createElement('option');
      opt.value = file;
      var title = localized(configs[file].meta && configs[file].meta.title, state.lang);
      opt.textContent = file + (title ? ' - ' + title : '');
      if (file === state.configName) opt.selected = true;
      el.configSelect.appendChild(opt);
    });
    populateLanguages();
    el.length.value = String(state.duration);
    el.lengthLabel.textContent = Math.round(state.duration / 60) + ' min';
  }

  function populateLanguages() {
    var langs = configLanguages(currentConfig() || {});
    if (langs.indexOf(state.lang) === -1) state.lang = langs[0];
    el.langSelect.textContent = '';
    langs.forEach(function (lang) {
      var opt = document.createElement('option');
      opt.value = lang;
      opt.textContent = lang;
      if (lang === state.lang) opt.selected = true;
      el.langSelect.appendChild(opt);
    });
  }

  el.cog.addEventListener('click', function () {
    el.settings.classList.toggle('open');
  });
  el.configSelect.addEventListener('change', function () {
    state.configName = el.configSelect.value;
    stopTimer();
    state.running = false;
    el.input.disabled = true;
    el.start.textContent = 'Start';
    populateLanguages();
    buildGame();
  });
  el.langSelect.addEventListener('change', function () {
    state.lang = el.langSelect.value;
    stopTimer();
    state.running = false;
    el.input.disabled = true;
    el.start.textContent = 'Start';
    buildGame();
  });
  el.length.addEventListener('input', function () {
    state.duration = parseInt(el.length.value, 10) || 300;
    el.lengthLabel.textContent = Math.round(state.duration / 60) + ' min';
    if (!state.running) updateTimerDisplay();
  });
  el.start.addEventListener('click', startGame);
  el.input.addEventListener('input', handleGuess);
  el.submit.addEventListener('click', submitScore);
  el.initials.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') submitScore();
  });
  el.again.addEventListener('click', function () {
    el.overlay.classList.remove('visible');
    startGame();
  });

  populateSettings();
  buildGame();
})();
