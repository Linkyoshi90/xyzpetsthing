const canvas = document.getElementById('tetris');
const context = canvas.getContext('2d');
context.scale(20, 20);
const spritePath = 'images/games/';
const spriteFiles = {
    1: 'tetris1.png',
    2: 'tetris2.png',
    3: 'tetris3.png',
    4: 'tetris4.png',
    5: 'tetris5.png',
    6: 'tetris6.png',
    7: 'tetris7.png'
};
const sprites = {};
for (const [key, file] of Object.entries(spriteFiles)) {
    const img = new Image();
    img.src = spritePath + file;
    img.onload = () => sprites[key] = img;
    img.onerror = () => sprites[key] = null;
}
const praisePath = 'images/games/';
const praiseFiles = {
    1: 'goodjob.png',
    2: 'great.png',
    3: 'fantastic.png',
    4: 'extraordinary.png',
};
const praiseSprites = {};
for (const [key, file] of Object.entries(praiseFiles)) {
    const img = new Image();
    img.src = praisePath + file;
    img.onload = () => praiseSprites[key] = img;
    img.onerror = () => praiseSprites[key] = null;
}
let gameOverFlag = false;
let clearing = false;
let rowsToClear = [];
let blinkTimer = 0;
let blinkIterations = 0;
let blinkState = false;
let message = '';
let messageTimer = 0;
let messageSprite = null;

function arenaSweep() {
    let rowCount = 1;
    let lines = 0;
    rowsToClear = [];
    outer: for (let y = arena.length - 1; y >= 0; --y) {
        for (let x = 0; x < arena[y].length; ++x) {
            if (arena[y][x] === 0) {
                continue outer;
            }
        }
        const row = arena.splice(y, 1)[0].fill(0);
        arena.unshift(row);
        ++y;
        player.score += rowCount * 10;
        rowCount *= 2;
        lines++;
        rowsToClear.push(y);
    }
    if (rowsToClear.length) {
        clearing = true;
        blinkTimer = 0;
        blinkIterations = 0;
        blinkState = false;
    }
    return rowsToClear.length;
}

function collide(arena, player) {
    const [m, o] = [player.matrix, player.pos];
    for (let y = 0; y < m.length; ++y) {
        for (let x = 0; x < m[y].length; ++x) {
            if (m[y][x] !== 0 && (arena[y + o.y] && arena[y + o.y][x + o.x]) !== 0) {
                return true;
            }
        }
    }
    return false;
}

function createMatrix(w, h) {
    const matrix = [];
    while (h--) {
        matrix.push(new Array(w).fill(0));
    }
    return matrix;
}

function createPiece(type) {
    if (type === 'T') {
        return [
            [0, 0, 0],
            [1, 1, 1],
            [0, 1, 0],
        ];
    } else if (type === 'O') {
        return [
            [2, 2],
            [2, 2],
        ];
    } else if (type === 'L') {
        return [
            [0, 3, 0],
            [0, 3, 0],
            [0, 3, 3],
        ];
    } else if (type === 'J') {
        return [
            [0, 4, 0],
            [0, 4, 0],
            [4, 4, 0],
        ];
    } else if (type === 'I') {
        return [
            [0, 5, 0, 0],
            [0, 5, 0, 0],
            [0, 5, 0, 0],
            [0, 5, 0, 0],
        ];
    } else if (type === 'S') {
        return [
            [0, 6, 6],
            [6, 6, 0],
            [0, 0, 0],
        ];
    } else if (type === 'Z') {
        return [
            [7, 7, 0],
            [0, 7, 7],
            [0, 0, 0],
        ];
    }
}

function drawMatrix(matrix, offset) {
    matrix.forEach((row, y) => {
        row.forEach((value, x) => {
            if (value !== 0) {
                const sprite = sprites[value];
                if (sprite) {
                    context.drawImage(sprite, x + offset.x, y + offset.y, 1, 1);
                } else {
                    context.fillStyle = colors[value];
                    context.fillRect(x + offset.x, y + offset.y, 1, 1);
                }
            }
        });
    });
}

function draw() {
    context.clearRect(0, 0, canvas.width, canvas.height);
    drawMatrix(arena, { x: 0, y: 0 });
    drawMatrix(player.matrix, player.pos);
    if (nextPiece) {
        drawMatrix(nextPiece, { x: 11, y: 4 });
    }
    context.fillStyle = '#fff';
    context.font = '1px monospace';
    context.fillText('Score', 11, 1);
    context.fillText(player.score, 11, 2);
    if (messageTimer > 0 && (messageSprite || message)) {
        if (messageSprite) {
            const w = messageSprite.width / 20;
            const h = messageSprite.height / 20;
            context.drawImage(messageSprite, 11, 8 - h, w, h);
        } else {
            context.fillText(message, 11, 8);
        }
    }
}

function merge(arena, player) {
    player.matrix.forEach((row, y) => {
        row.forEach((value, x) => {
            if (value !== 0) {
                arena[y + player.pos.y][x + player.pos.x] = value;
            }
        });
    });
}

function playerDrop() {
    player.pos.y++;
    if (collide(arena, player)) {
        player.pos.y--;
        merge(arena, player);
        arenaSweep();
        if (!clearing) {
            playerReset();
        } else {
            player.matrix = [[0]];
        }
    }
    dropCounter = 0;
}

function playerMove(dir) {
    player.pos.x += dir;
    if (collide(arena, player)) {
        player.pos.x -= dir;
    }
}

function gameOver() {
    if (gameOverFlag) return;
    gameOverFlag = true;
    const submit = confirm('Submit score for rewards?');
    const redirect = () => { window.location.href = 'index.php?pg=games'; };
    if (submit) {
        fetch('score_exchange.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ game: 'tetris', score: player.score })
        })
            .then(r => r.json())
            .then(data => {
                if (window.updateCurrencyDisplay && data && typeof data === 'object' && data.cash !== undefined) {
                    window.updateCurrencyDisplay({ cash: data.cash });
                } else if (data && data.error) {
                    alert(data.error);
                }
            })
            .finally(redirect);
    } else {
        redirect();
    }
}

function playerReset() {
    player.matrix = nextPiece;
    nextPiece = createPiece(randomPiece());
    player.pos.y = 0;
    player.pos.x = ((arena[0].length / 2) | 0) - ((player.matrix[0].length / 2) | 0);
    if (collide(arena, player)) {
        gameOver();
    }
}

function playerRotate() {
    rotate(player.matrix);
    const pos = player.pos.x;
    let offset = 1;
    while (collide(arena, player)) {
        player.pos.x += offset;
        offset = -(offset + (offset > 0 ? 1 : -1));
        if (offset > player.matrix[0].length) {
            rotate(player.matrix);
            player.pos.x = pos;
            return;
        }
    }
}

function rotate(matrix) {
    for (let y = 0; y < matrix.length; ++y) {
        for (let x = 0; x < y; ++x) {
            [matrix[x][y], matrix[y][x]] = [matrix[y][x], matrix[x][y]];
        }
    }
    matrix.forEach(row => row.reverse());
}

function updateScore() {
    // Score is drawn directly on the canvas
}

function processClearing(delta) {
    blinkTimer += delta;
    if (blinkTimer > 100) {
        blinkTimer = 0;
        blinkState = !blinkState;
        rowsToClear.forEach(y => arena[y].fill(blinkState ? 0 : 8));
        blinkIterations++;
        if (blinkIterations >= 6) {
            let rowCount = 1;
            rowsToClear.sort((a, b) => b - a).forEach(y => {
                arena.splice(y, 1);
                arena.unshift(new Array(arena[0].length).fill(0));
                player.score += rowCount * 10;
                rowCount *= 2;
            });
            updateScore();
            const msgs = { 1: 'good job', 2: 'great', 3: 'fantastic', 4: 'extraordinary' };
            message = msgs[rowsToClear.length] || '';
            messageSprite = praiseSprites[rowsToClear.length] || null;
            messageTimer = 2000;
            rowsToClear = [];
            clearing = false;
            blinkIterations = 0;
            playerReset();
        }
    }
}

let dropCounter = 0;
let dropInterval = 1000;
let lastTime = 0;

function update(time = 0) {
    if (gameOverFlag) return;
    const delta = time - lastTime;
    lastTime = time;
    dropCounter += delta;
    if (!clearing) {
        dropCounter += delta;
        if (dropCounter > dropInterval) {
            playerDrop();
        }
    } else {
        processClearing(delta);
    }
    if (messageTimer > 0) {
        messageTimer -= delta;
        if (messageTimer <= 0) {
            message = '';
            messageSprite = null;
            messageTimer = 0;
        }
    }
    draw();
    requestAnimationFrame(update);
}

const colors = [
    null,
    '#FF0D72',
    '#0DC2FF',
    '#0DFF72',
    '#F538FF',
    '#FF8E0D',
    '#FFE138',
    '#3877FF',
    '#FFFFFF'
];

function randomPiece() {
    const pieces = 'TJLOSZI';
    return pieces[(pieces.length * Math.random()) | 0];
}

const arena = createMatrix(10, 20);
const player = {
    pos: { x: 0, y: 0 },
    matrix: null,
    score: 0,
};

let nextPiece = createPiece(randomPiece());

document.addEventListener('keydown', event => {
    if (event.key === 'a' || event.key === 'A') {
        playerMove(-1);
    } else if (event.key === 'd' || event.key === 'D') {
        playerMove(1);
    } else if (event.key === 's' || event.key === 'S') {
        playerDrop();
    } else if (event.key === 'w' || event.key === 'W') {
        playerRotate();
    }
});

playerReset();
update();