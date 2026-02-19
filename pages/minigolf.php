<?php
/**
 * Mini Golf Game
 * A fully playable mini golf game with alphanumeric course code system
 * 
 * Course Code Format:
 * - Grid size: 20 columns x 15 rows = 300 characters
 * - Each character represents one tile (20x20 pixels each)
 * 
 * Tile Codes:
 * . = Fairway (normal grass)
 * W = Wall (solid obstacle)
 * H = Hole (target)
 * S = Start (ball spawn)
 * B = Bumper (bouncy obstacle)
 * w = Water (hazard - resets ball)
 * s = Sand (slows ball)
 * > < ^ v = Ramps (push ball in direction)
 * P Q = Portals (teleport between P and Q)
 */

require_once __DIR__.'/../auth.php';
require_login();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini Golf</title>
    <style>
/* Mini Golf Game Styles */

.minigolf-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #1a472a 0%, #2d5a3f 100%);
    min-height: 100vh;
    color: #fff;
}

.minigolf-container h1 {
    font-size: 2.5rem;
    text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.5);
    margin-bottom: 10px;
    color: #90EE90;
}

#game {
    border: 4px solid #8B4513;
    border-radius: 8px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
    cursor: crosshair;
    background: #228B22;
}

.game-info {
    display: flex;
    gap: 30px;
    margin: 15px 0;
    font-size: 1.2rem;
}

.game-info div {
    background: rgba(0, 0, 0, 0.4);
    padding: 8px 20px;
    border-radius: 20px;
    border: 2px solid #90EE90;
}

.game-info span {
    color: #FFD700;
    font-weight: bold;
}

.instructions {
    color: #90EE90;
    font-size: 0.95rem;
    margin-bottom: 10px;
    text-align: center;
}

/* Course Code Section */
.course-code-section {
    width: 100%;
    max-width: 800px;
    margin-top: 25px;
    background: rgba(0, 0, 0, 0.5);
    border-radius: 12px;
    padding: 20px;
    border: 2px solid #654321;
}

.course-code-section h2 {
    color: #90EE90;
    margin-bottom: 15px;
    font-size: 1.4rem;
    border-bottom: 2px solid #654321;
    padding-bottom: 10px;
}

.course-code-section h3 {
    color: #FFD700;
    margin: 15px 0 10px 0;
    font-size: 1.1rem;
}

.course-code-section p {
    line-height: 1.6;
}

#courseCode {
    width: 100%;
    height: 200px;
    font-family: 'Courier New', monospace;
    font-size: 12px;
    padding: 10px;
    border: 2px solid #654321;
    border-radius: 8px;
    background: #1a1a1a;
    color: #00ff00;
    resize: vertical;
    box-sizing: border-box;
}

.button-row {
    display: flex;
    gap: 10px;
    margin-top: 10px;
    flex-wrap: wrap;
}

.course-btn {
    padding: 10px 20px;
    font-size: 1rem;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    font-weight: bold;
}

.btn-load {
    background: #228B22;
    color: white;
}

.btn-load:hover {
    background: #2ea62e;
    transform: translateY(-2px);
}

.btn-reset {
    background: #dc3545;
    color: white;
}

.btn-reset:hover {
    background: #e4606d;
    transform: translateY(-2px);
}

.btn-example {
    background: #ffc107;
    color: #333;
}

.btn-example:hover {
    background: #ffca2c;
    transform: translateY(-2px);
}

.btn-random {
    background: #17a2b8;
    color: white;
}

.btn-random:hover {
    background: #1fc2da;
    transform: translateY(-2px);
}

/* Legend Table */
.legend-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
    font-size: 0.9rem;
}

.legend-table th,
.legend-table td {
    padding: 8px 12px;
    border: 1px solid #654321;
    text-align: left;
}

.legend-table th {
    background: #654321;
    color: #fff;
}

.legend-table td {
    background: rgba(0, 0, 0, 0.3);
}

.legend-table code {
    background: #333;
    padding: 2px 8px;
    border-radius: 4px;
    color: #00ff00;
    font-weight: bold;
}

/* Power meter */
.power-meter {
    width: 400px;
    height: 20px;
    background: #333;
    border-radius: 10px;
    margin: 10px 0;
    overflow: hidden;
    border: 2px solid #654321;
}

.power-fill {
    height: 100%;
    width: 0%;
    background: linear-gradient(90deg, #00ff00, #ffff00, #ff0000);
    transition: width 0.05s linear;
}

/* Message overlay */
.message-overlay {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(0, 0, 0, 0.9);
    padding: 30px 50px;
    border-radius: 15px;
    border: 3px solid #FFD700;
    text-align: center;
    z-index: 100;
    display: none;
}

.message-overlay.show {
    display: block;
    animation: popIn 0.3s ease;
}

@keyframes popIn {
    0% { transform: translate(-50%, -50%) scale(0.5); opacity: 0; }
    100% { transform: translate(-50%, -50%) scale(1); opacity: 1; }
}

.message-overlay h2 {
    color: #FFD700;
    font-size: 2rem;
    margin-bottom: 10px;
}

.message-overlay p {
    color: #90EE90;
    font-size: 1.2rem;
}

/* Status messages */
.status-msg {
    margin-top: 10px;
    padding: 8px 15px;
    border-radius: 5px;
    font-size: 0.9rem;
}

.status-success {
    background: rgba(40, 167, 69, 0.5);
    border: 1px solid #28a745;
}

.status-error {
    background: rgba(220, 53, 69, 0.5);
    border: 1px solid #dc3545;
}

/* Code example */
.code-example {
    background: #1a1a1a;
    padding: 10px;
    border-radius: 5px;
    font-size: 11px;
    color: #00ff00;
    overflow-x: auto;
    font-family: 'Courier New', monospace;
    white-space: pre;
}

/* Responsive */
@media (max-width: 500px) {
    #game {
        width: 100%;
        max-width: 380px;
    }
    
    .minigolf-container h1 {
        font-size: 1.8rem;
    }
    
    .game-info {
        flex-direction: column;
        gap: 10px;
    }
    
    .power-meter {
        width: 100%;
        max-width: 380px;
    }
    
    .button-row {
        justify-content: center;
    }
}
    </style>
</head>
<body class="minigolf-container">
    <h1>⛳ Mini Golf</h1>
    
    <div class="game-info">
        <div>Strokes: <span id="strokeVal">0</span></div>
    </div>
    
    <canvas id="game" width="400" height="300"></canvas>
    
    <div class="power-meter">
        <div class="power-fill"></div>
    </div>
    
    <p class="instructions">
        🖱️ Click and drag from the ball to aim and shoot<br>
        📱 Touch supported - drag to aim
    </p>
    
    <!-- Course Code Section -->
    <section class="course-code-section">
        <h2>🗺️ Course Designer</h2>
        
        <p>Enter a course code below to create custom holes. The code uses a 20×15 grid where each character represents one tile.</p>
        
        <h3>Course Code</h3>
        <textarea id="courseCode" placeholder="Enter course code here..."></textarea>
        
        <div class="button-row">
            <button id="loadBtn" class="course-btn btn-load">Load Course</button>
            <button id="resetBtn" class="course-btn btn-reset">Reset</button>
            <button id="randomBtn" class="course-btn btn-random">Random</button>
        </div>
        
        <h3>Quick Load Examples</h3>
        <div class="button-row">
            <button id="exampleSimple" class="course-btn btn-example">Simple</button>
            <button id="exampleBumper" class="course-btn btn-example">Bumpers</button>
            <button id="exampleHazard" class="course-btn btn-example">Hazards</button>
            <button id="examplePortal" class="course-btn btn-example">Portals</button>
            <button id="exampleRamp" class="course-btn btn-example">Ramps</button>
        </div>
        
        <h3>Tile Legend</h3>
        <table class="legend-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Tile</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>.</code></td>
                    <td>Fairway</td>
                    <td>Normal grass - standard playing surface</td>
                </tr>
                <tr>
                    <td><code>W</code></td>
                    <td>Wall</td>
                    <td>Solid obstacle - ball bounces off</td>
                </tr>
                <tr>
                    <td><code>H</code></td>
                    <td>Hole</td>
                    <td>Target - get the ball in here to complete!</td>
                </tr>
                <tr>
                    <td><code>S</code></td>
                    <td>Start</td>
                    <td>Ball starting position (one per course)</td>
                </tr>
                <tr>
                    <td><code>B</code></td>
                    <td>Bumper</td>
                    <td>Bouncy obstacle - ball rebounds with force</td>
                </tr>
                <tr>
                    <td><code>w</code></td>
                    <td>Water</td>
                    <td>Hazard - ball resets to start (+1 penalty stroke)</td>
                </tr>
                <tr>
                    <td><code>s</code></td>
                    <td>Sand</td>
                    <td>Slow zone - ball decelerates quickly</td>
                </tr>
                <tr>
                    <td><code>&gt;</code></td>
                    <td>Ramp Right</td>
                    <td>Pushes ball to the right</td>
                </tr>
                <tr>
                    <td><code>&lt;</code></td>
                    <td>Ramp Left</td>
                    <td>Pushes ball to the left</td>
                </tr>
                <tr>
                    <td><code>^</code></td>
                    <td>Ramp Up</td>
                    <td>Pushes ball upward</td>
                </tr>
                <tr>
                    <td><code>v</code></td>
                    <td>Ramp Down</td>
                    <td>Pushes ball downward</td>
                </tr>
                <tr>
                    <td><code>P</code></td>
                    <td>Portal A</td>
                    <td>Teleport entry/exit (pairs with Q)</td>
                </tr>
                <tr>
                    <td><code>Q</code></td>
                    <td>Portal B</td>
                    <td>Teleport entry/exit (pairs with P)</td>
                </tr>
            </tbody>
        </table>
        
        <h3>How to Create a Course</h3>
        <p>
            <strong>Format:</strong> A course code is exactly 300 characters (20 columns × 15 rows).<br>
            You can enter it as a single line or with line breaks for readability (newlines are ignored).
        </p>
        <p><strong>Example:</strong> A simple course with walls around the border and a hole:</p>
        <div class="code-example">WWWWWWWWWWWWWWWWWWWW
W..................W
W..S...............W
W..................W
W..................W
W..................W
W..................W
W..................W
W...............H..W
W..................W
W..................W
W..................W
W..................W
W..................W
WWWWWWWWWWWWWWWWWWWW</div>
    </section>
    
    <script>
/**
 * Mini Golf Game
 * A fully playable mini golf game with alphanumeric course code system
 */

// ==========================================
// COURSE CODE SYSTEM
// ==========================================
/**
 * Course Code Format:
 * - Grid size: 20 columns x 15 rows = 300 characters
 * - Each character represents one tile (20x20 pixels each)
 * 
 * Tile Codes:
 * . = Fairway (normal grass, plays normally)
 * W = Wall (solid obstacle, ball bounces)
 * H = Hole (target - get the ball in here!)
 * S = Start (ball starting position)
 * B = Bumper (bouncy obstacle, reflects ball with force)
 * w = Water (hazard - resets ball to start)
 * s = Sand (slows ball significantly)
 * > = Ramp Right (pushes ball to the right)
 * < = Ramp Left (pushes ball to the left)
 * ^ = Ramp Up (pushes ball upward)
 * v = Ramp Down (pushes ball downward)
 * P = Portal A (teleport entry/exit)
 * Q = Portal B (teleport entry/exit - pairs with P)
 */

const GRID_COLS = 20;
const GRID_ROWS = 15;
const TILE_SIZE = 20;

// Tile types with their properties
const TILES = {
    '.': { name: 'Fairway', color: '#228B22', friction: 0.98, solid: false },
    'W': { name: 'Wall', color: '#8B4513', friction: 1, solid: true },
    'H': { name: 'Hole', color: '#000000', friction: 0.95, solid: false, isHole: true },
    'S': { name: 'Start', color: '#32CD32', friction: 0.98, solid: false, isStart: true },
    'B': { name: 'Bumper', color: '#FF69B4', friction: 1, solid: true, isBumper: true },
    'w': { name: 'Water', color: '#1E90FF', friction: 0.99, solid: false, isWater: true },
    's': { name: 'Sand', color: '#F4A460', friction: 0.92, solid: false, isSand: true },
    '>': { name: 'Ramp Right', color: '#98FB98', friction: 0.97, solid: false, ramp: { x: 0.3, y: 0 } },
    '<': { name: 'Ramp Left', color: '#98FB98', friction: 0.97, solid: false, ramp: { x: -0.3, y: 0 } },
    '^': { name: 'Ramp Up', color: '#98FB98', friction: 0.97, solid: false, ramp: { x: 0, y: -0.3 } },
    'v': { name: 'Ramp Down', color: '#98FB98', friction: 0.97, solid: false, ramp: { x: 0, y: 0.3 } },
    'P': { name: 'Portal A', color: '#9400D3', friction: 0.98, solid: false, portal: 'A' },
    'Q': { name: 'Portal B', color: '#FF1493', friction: 0.98, solid: false, portal: 'B' }
};

// ==========================================
// GAME STATE
// ==========================================
let canvas, ctx;
let course = [];
let ball = { x: 0, y: 0, vx: 0, vy: 0, radius: 8 };
let startPos = { x: 0, y: 0 };
let holePos = { x: 0, y: 0 };
let portals = { A: null, B: null };

let isDragging = false;
let dragStart = { x: 0, y: 0 };
let dragEnd = { x: 0, y: 0 };
let power = 0;

let strokes = 0;
let gameWon = false;
let ballInMotion = false;

// Default course (a simple beginner hole)
const DEFAULT_COURSE_CODE = `
WWWWWWWWWWWWWWWWWWWW
W..................W
W..................W
W..................W
W.......WWWW.......W
W.......W..W.......W
W..S....W.HW.......W
W.......W..W.......W
W.......WWWW.......W
W..................W
W..................W
W..................W
W..................W
W..................W
WWWWWWWWWWWWWWWWWWWW
`;

// ==========================================
// COURSE CODE PARSING
// ==========================================

/**
 * Parse a course code string into a 2D grid
 * @param {string} code - The course code (can include newlines)
 * @returns {Array} 2D array of tile characters
 */
function parseCourseCode(code) {
    // Remove whitespace and newlines
    let cleaned = code.replace(/\s/g, '').toUpperCase();
    
    // Validate length
    const expectedLength = GRID_COLS * GRID_ROWS;
    if (cleaned.length < expectedLength) {
        // Pad with fairway if too short
        cleaned = cleaned.padEnd(expectedLength, '.');
    } else if (cleaned.length > expectedLength) {
        // Truncate if too long
        cleaned = cleaned.substring(0, expectedLength);
    }
    
    // Convert to 2D grid
    const grid = [];
    for (let row = 0; row < GRID_ROWS; row++) {
        const rowData = [];
        for (let col = 0; col < GRID_COLS; col++) {
            const char = cleaned[row * GRID_COLS + col];
            // Validate character
            rowData.push(TILES[char] ? char : '.');
        }
        grid.push(rowData);
    }
    
    return grid;
}

/**
 * Generate a course code string from the current grid
 * @returns {string} Formatted course code
 */
function generateCourseCode() {
    let code = '';
    for (let row = 0; row < GRID_ROWS; row++) {
        for (let col = 0; col < GRID_COLS; col++) {
            code += course[row][col];
        }
        code += '\n';
    }
    return code;
}

// ==========================================
// COURSE LOADING & INITIALIZATION
// ==========================================

/**
 * Load a course from a code string
 * @param {string} code - The course code
 */
function loadCourse(code) {
    course = parseCourseCode(code);
    
    // Find special tiles
    startPos = null;
    holePos = null;
    portals = { A: null, B: null };
    
    for (let row = 0; row < GRID_ROWS; row++) {
        for (let col = 0; col < GRID_COLS; col++) {
            const tile = course[row][col];
            const centerX = col * TILE_SIZE + TILE_SIZE / 2;
            const centerY = row * TILE_SIZE + TILE_SIZE / 2;
            
            if (tile === 'S') {
                startPos = { x: centerX, y: centerY };
            } else if (tile === 'H') {
                holePos = { x: centerX, y: centerY };
            } else if (tile === 'P') {
                portals.A = { x: centerX, y: centerY };
            } else if (tile === 'Q') {
                portals.B = { x: centerX, y: centerY };
            }
        }
    }
    
    // Default start position if not found
    if (!startPos) {
        startPos = { x: TILE_SIZE * 2, y: TILE_SIZE * 2 };
    }
    
    // Reset ball
    resetBall();
    
    // Reset game state
    strokes = 0;
    gameWon = false;
    updateUI();
}

/**
 * Reset ball to starting position
 */
function resetBall() {
    ball.x = startPos.x;
    ball.y = startPos.y;
    ball.vx = 0;
    ball.vy = 0;
    ballInMotion = false;
}

// ==========================================
// PHYSICS & COLLISION
// ==========================================

/**
 * Get the tile at a world position
 * @param {number} x - World X coordinate
 * @param {number} y - World Y coordinate
 * @returns {object} Tile properties
 */
function getTileAt(x, y) {
    const col = Math.floor(x / TILE_SIZE);
    const row = Math.floor(y / TILE_SIZE);
    
    if (row < 0 || row >= GRID_ROWS || col < 0 || col >= GRID_COLS) {
        return TILES['W']; // Treat out of bounds as wall
    }
    
    return TILES[course[row][col]];
}

/**
 * Check if ball collides with a solid tile
 */
function checkCollisions() {
    const nextX = ball.x + ball.vx;
    const nextY = ball.y + ball.vy;
    
    // Get tiles around the ball
    const tile = getTileAt(nextX, nextY);
    const tileLeft = getTileAt(nextX - ball.radius, ball.y);
    const tileRight = getTileAt(nextX + ball.radius, ball.y);
    const tileTop = getTileAt(ball.x, nextY - ball.radius);
    const tileBottom = getTileAt(ball.x, nextY + ball.radius);
    
    // Check for bumper collision (bounce with force)
    if (tileLeft.isBumper || tileRight.isBumper || tileTop.isBumper || tileBottom.isBumper) {
        // Bumper bounce - reverse velocity with boost
        if (tileLeft.isBumper || tileRight.isBumper) {
            ball.vx = -ball.vx * 1.5;
        }
        if (tileTop.isBumper || tileBottom.isBumper) {
            ball.vy = -ball.vy * 1.5;
        }
        return;
    }
    
    // Wall collision
    if (tileLeft.solid || tileRight.solid) {
        ball.vx = -ball.vx * 0.7;
        ball.x += ball.vx * 0.5; // Push out
    }
    if (tileTop.solid || tileBottom.solid) {
        ball.vy = -ball.vy * 0.7;
        ball.y += ball.vy * 0.5; // Push out
    }
}

/**
 * Apply physics updates
 */
function updatePhysics() {
    if (!ballInMotion || gameWon) return;
    
    const tile = getTileAt(ball.x, ball.y);
    
    // Apply ramp force
    if (tile.ramp) {
        ball.vx += tile.ramp.x;
        ball.vy += tile.ramp.y;
    }
    
    // Apply friction
    ball.vx *= tile.friction;
    ball.vy *= tile.friction;
    
    // Sand slows more
    if (tile.isSand) {
        ball.vx *= 0.95;
        ball.vy *= 0.95;
    }
    
    // Check water hazard
    if (tile.isWater) {
        strokes++; // Penalty stroke
        resetBall();
        updateUI();
        return;
    }
    
    // Check portal
    if (tile.portal && portals.A && portals.B) {
        const targetPortal = tile.portal === 'A' ? portals.B : portals.A;
        // Teleport with velocity preserved
        ball.x = targetPortal.x;
        ball.y = targetPortal.y;
    }
    
    // Check collisions
    checkCollisions();
    
    // Update position
    ball.x += ball.vx;
    ball.y += ball.vy;
    
    // Clamp to canvas bounds
    ball.x = Math.max(ball.radius, Math.min(canvas.width - ball.radius, ball.x));
    ball.y = Math.max(ball.radius, Math.min(canvas.height - ball.radius, ball.y));
    
    // Check if ball stopped
    const speed = Math.sqrt(ball.vx * ball.vx + ball.vy * ball.vy);
    if (speed < 0.1) {
        ball.vx = 0;
        ball.vy = 0;
        ballInMotion = false;
    }
    
    // Check hole
    if (holePos) {
        const distToHole = Math.sqrt(
            Math.pow(ball.x - holePos.x, 2) + 
            Math.pow(ball.y - holePos.y, 2)
        );
        
        // Ball must be slow enough to fall in
        if (distToHole < 12 && speed < 5) {
            gameWon = true;
            ballInMotion = false;
            showMessage('Hole in One!', `You completed the hole in ${strokes} stroke${strokes !== 1 ? 's' : ''}!`);
        }
    }
}

// ==========================================
// RENDERING
// ==========================================

/**
 * Draw the course
 */
function drawCourse() {
    for (let row = 0; row < GRID_ROWS; row++) {
        for (let col = 0; col < GRID_COLS; col++) {
            const tile = TILES[course[row][col]];
            const x = col * TILE_SIZE;
            const y = row * TILE_SIZE;
            
            // Draw tile background
            ctx.fillStyle = tile.color;
            ctx.fillRect(x, y, TILE_SIZE, TILE_SIZE);
            
            // Draw special tile effects
            if (course[row][col] === 'H') {
                // Draw hole (circle with gradient)
                ctx.beginPath();
                ctx.arc(x + TILE_SIZE/2, y + TILE_SIZE/2, 8, 0, Math.PI * 2);
                ctx.fillStyle = '#000';
                ctx.fill();
                ctx.beginPath();
                ctx.arc(x + TILE_SIZE/2, y + TILE_SIZE/2, 10, 0, Math.PI * 2);
                ctx.strokeStyle = '#444';
                ctx.lineWidth = 2;
                ctx.stroke();
            } else if (course[row][col] === 'B') {
                // Draw bumper
                ctx.beginPath();
                ctx.arc(x + TILE_SIZE/2, y + TILE_SIZE/2, 9, 0, Math.PI * 2);
                ctx.fillStyle = '#FF69B4';
                ctx.fill();
                ctx.strokeStyle = '#FF1493';
                ctx.lineWidth = 2;
                ctx.stroke();
            } else if (course[row][col] === 'w') {
                // Draw water pattern
                ctx.fillStyle = 'rgba(255, 255, 255, 0.2)';
                ctx.beginPath();
                ctx.arc(x + 5, y + 10, 3, 0, Math.PI * 2);
                ctx.arc(x + 15, y + 8, 2, 0, Math.PI * 2);
                ctx.fill();
            } else if (course[row][col] === 's') {
                // Draw sand dots
                ctx.fillStyle = 'rgba(139, 90, 43, 0.5)';
                for (let i = 0; i < 5; i++) {
                    ctx.beginPath();
                    ctx.arc(
                        x + Math.random() * TILE_SIZE,
                        y + Math.random() * TILE_SIZE,
                        1, 0, Math.PI * 2
                    );
                    ctx.fill();
                }
            } else if ('<>^v'.includes(course[row][col])) {
                // Draw ramp arrow
                ctx.fillStyle = 'rgba(0, 100, 0, 0.7)';
                ctx.font = '14px Arial';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText(course[row][col], x + TILE_SIZE/2, y + TILE_SIZE/2);
            } else if (course[row][col] === 'P' || course[row][col] === 'Q') {
                // Draw portal
                ctx.beginPath();
                ctx.arc(x + TILE_SIZE/2, y + TILE_SIZE/2, 8, 0, Math.PI * 2);
                ctx.fillStyle = tile.color;
                ctx.fill();
                ctx.strokeStyle = '#fff';
                ctx.lineWidth = 2;
                ctx.stroke();
            }
            
            // Draw grid lines for walls only
            if (tile.solid) {
                ctx.strokeStyle = '#5D3A1A';
                ctx.lineWidth = 1;
                ctx.strokeRect(x, y, TILE_SIZE, TILE_SIZE);
            }
        }
    }
}

/**
 * Draw the ball
 */
function drawBall() {
    // Ball shadow
    ctx.beginPath();
    ctx.arc(ball.x + 2, ball.y + 2, ball.radius, 0, Math.PI * 2);
    ctx.fillStyle = 'rgba(0, 0, 0, 0.3)';
    ctx.fill();
    
    // Ball
    ctx.beginPath();
    ctx.arc(ball.x, ball.y, ball.radius, 0, Math.PI * 2);
    ctx.fillStyle = '#fff';
    ctx.fill();
    ctx.strokeStyle = '#ccc';
    ctx.lineWidth = 1;
    ctx.stroke();
    
    // Highlight
    ctx.beginPath();
    ctx.arc(ball.x - 2, ball.y - 2, 3, 0, Math.PI * 2);
    ctx.fillStyle = 'rgba(255, 255, 255, 0.8)';
    ctx.fill();
}

/**
 * Draw aiming line when dragging
 */
function drawAimLine() {
    if (!isDragging || ballInMotion) return;
    
    const dx = dragStart.x - dragEnd.x;
    const dy = dragStart.y - dragEnd.y;
    const distance = Math.sqrt(dx * dx + dy * dy);
    
    // Limit power
    const maxPower = 150;
    power = Math.min(distance, maxPower);
    
    // Draw aim line
    ctx.beginPath();
    ctx.moveTo(ball.x, ball.y);
    ctx.lineTo(ball.x + dx, ball.y + dy);
    ctx.strokeStyle = `rgba(255, 255, 255, ${0.5 + power/maxPower * 0.5})`;
    ctx.lineWidth = 2;
    ctx.setLineDash([5, 5]);
    ctx.stroke();
    ctx.setLineDash([]);
    
    // Draw power indicator dot
    const dotX = ball.x + dx * 0.5;
    const dotY = ball.y + dy * 0.5;
    ctx.beginPath();
    ctx.arc(dotX, dotY, 4, 0, Math.PI * 2);
    ctx.fillStyle = `hsl(${120 - power/maxPower * 120}, 100%, 50%)`;
    ctx.fill();
    
    // Update power meter
    const powerFill = document.querySelector('.power-fill');
    if (powerFill) {
        powerFill.style.width = `${power / maxPower * 100}%`;
    }
}

/**
 * Main render function
 */
function render() {
    // Clear canvas
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    
    // Draw course
    drawCourse();
    
    // Draw aim line
    drawAimLine();
    
    // Draw ball
    drawBall();
    
    // Draw hole flag
    if (holePos) {
        ctx.fillStyle = '#FF0000';
        ctx.fillRect(holePos.x + 8, holePos.y - 20, 2, 20);
        ctx.beginPath();
        ctx.moveTo(holePos.x + 10, holePos.y - 20);
        ctx.lineTo(holePos.x + 22, holePos.y - 14);
        ctx.lineTo(holePos.x + 10, holePos.y - 8);
        ctx.closePath();
        ctx.fillStyle = '#FF0000';
        ctx.fill();
    }
}

// ==========================================
// GAME LOOP
// ==========================================

function gameLoop() {
    updatePhysics();
    render();
    requestAnimationFrame(gameLoop);
}

// ==========================================
// INPUT HANDLING
// ==========================================

function getMousePos(e) {
    const rect = canvas.getBoundingClientRect();
    return {
        x: (e.clientX - rect.left) * (canvas.width / rect.width),
        y: (e.clientY - rect.top) * (canvas.height / rect.height)
    };
}

function handleMouseDown(e) {
    if (gameWon || ballInMotion) return;
    
    const pos = getMousePos(e);
    const dist = Math.sqrt(
        Math.pow(pos.x - ball.x, 2) + 
        Math.pow(pos.y - ball.y, 2)
    );
    
    // Start dragging from ball
    if (dist < 30) {
        isDragging = true;
        dragStart = { x: ball.x, y: ball.y };
        dragEnd = pos;
    }
}

function handleMouseMove(e) {
    if (!isDragging) return;
    dragEnd = getMousePos(e);
}

function handleMouseUp(e) {
    if (!isDragging) return;
    
    isDragging = false;
    
    // Calculate shot
    const dx = dragStart.x - dragEnd.x;
    const dy = dragStart.y - dragEnd.y;
    const distance = Math.sqrt(dx * dx + dy * dy);
    
    if (distance > 5) {
        // Normalize and apply power
        const maxPower = 150;
        const power = Math.min(distance, maxPower);
        const speed = power * 0.15;
        
        ball.vx = (dx / distance) * speed;
        ball.vy = (dy / distance) * speed;
        
        ballInMotion = true;
        strokes++;
        updateUI();
    }
    
    // Reset power meter
    const powerFill = document.querySelector('.power-fill');
    if (powerFill) {
        powerFill.style.width = '0%';
    }
}

// Touch support
function handleTouchStart(e) {
    e.preventDefault();
    const touch = e.touches[0];
    handleMouseDown({ clientX: touch.clientX, clientY: touch.clientY });
}

function handleTouchMove(e) {
    e.preventDefault();
    const touch = e.touches[0];
    handleMouseMove({ clientX: touch.clientX, clientY: touch.clientY });
}

function handleTouchEnd(e) {
    e.preventDefault();
    handleMouseUp(e);
}

// ==========================================
// UI FUNCTIONS
// ==========================================

function updateUI() {
    const strokeEl = document.getElementById('strokeVal');
    if (strokeEl) {
        strokeEl.textContent = strokes;
    }
}

function showMessage(title, text) {
    let overlay = document.querySelector('.message-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'message-overlay';
        overlay.innerHTML = '<h2 id="msgTitle"></h2><p id="msgText"></p>';
        document.body.appendChild(overlay);
    }
    
    document.getElementById('msgTitle').textContent = title;
    document.getElementById('msgText').textContent = text;
    overlay.classList.add('show');
    
    // Hide on click
    overlay.onclick = () => {
        overlay.classList.remove('show');
    };
}

function setStatus(msg, isError = false) {
    let status = document.querySelector('.status-msg');
    if (!status) {
        status = document.createElement('div');
        status.className = 'status-msg';
        const section = document.querySelector('.course-code-section');
        if (section) {
            section.appendChild(status);
        }
    }
    
    status.textContent = msg;
    status.className = 'status-msg ' + (isError ? 'status-error' : 'status-success');
    
    // Clear after 3 seconds
    setTimeout(() => {
        status.textContent = '';
        status.className = 'status-msg';
    }, 3000);
}

// ==========================================
// EXAMPLE COURSES
// ==========================================

const EXAMPLE_COURSES = {
    simple: `
WWWWWWWWWWWWWWWWWWWW
W..................W
W..................W
W..S...............W
W..................W
W..................W
W..................W
W..................W
W...............H..W
W..................W
W..................W
W..................W
W..................W
W..................W
WWWWWWWWWWWWWWWWWWWW
    `,
    
    bumperMaze: `
WWWWWWWWWWWWWWWWWWWW
W.S................W
W..................W
W...B...B...B......W
W..................W
W..WWWWWWWWWW......W
W..W.......HW......W
W..W.........W.....W
W..WWWWWWWWWWW.....W
W..................W
W...B...B...B......W
W..................W
W..................W
W..................W
WWWWWWWWWWWWWWWWWWWW
    `,
    
    hazards: `
WWWWWWWWWWWWWWWWWWWW
W..S...............W
W..................W
W...wwwwwwwwwww....W
W...w.........w....W
W...w....H....w....W
W...w.........w....W
W...wwwwwwwwwww....W
W..................W
W......ssssss......W
W......s....s......W
W......s....s......W
W......ssssss......W
W..................W
WWWWWWWWWWWWWWWWWWWW
    `,
    
    portals: `
WWWWWWWWWWWWWWWWWWWW
W..S...............W
W..................W
W..WWWWWWWWWWWW....W
W..W........QW.....W
W..W........WW.....W
W..WWWWWWWWWWW.....W
W..................W
W.....WWWWWWWWWWWW.W
W.....WP.........W.W
W.....W..........W.W
W.....WWWWWWWWWWWWW.W
W..................W
W...............H..W
WWWWWWWWWWWWWWWWWWWW
    `,
    
    ramps: `
WWWWWWWWWWWWWWWWWWWW
W..................W
W..S...>>>>>.......W
W..................W
W...........^^^^...W
W...........W..W...W
W...........W.HW...W
W...........W..W...W
W...........WWWW...W
W..................W
W....<<<<<.........W
W..................W
W..................W
W..................W
WWWWWWWWWWWWWWWWWWWW
    `
};

function loadExampleCourse(name) {
    const code = EXAMPLE_COURSES[name];
    if (code) {
        const textarea = document.getElementById('courseCode');
        if (textarea) {
            textarea.value = code.trim();
        }
        loadCourse(code);
        setStatus(`Loaded: ${name} course`);
    }
}

function generateRandomCourse() {
    let code = '';
    
    // Borders
    for (let row = 0; row < GRID_ROWS; row++) {
        for (let col = 0; col < GRID_COLS; col++) {
            if (row === 0 || row === GRID_ROWS - 1 || col === 0 || col === GRID_COLS - 1) {
                code += 'W';
            } else {
                // Random tiles
                const rand = Math.random();
                if (rand < 0.03) code += 'B';
                else if (rand < 0.05) code += 's';
                else if (rand < 0.07) code += 'w';
                else code += '.';
            }
        }
    }
    
    // Place start and hole
    code = 'S' + code.substring(1);
    code = code.substring(0, GRID_COLS * (GRID_ROWS - 2) + GRID_COLS - 2) + 'H' + 
           code.substring(GRID_COLS * (GRID_ROWS - 2) + GRID_COLS - 1);
    
    const textarea = document.getElementById('courseCode');
    if (textarea) {
        textarea.value = code;
    }
    loadCourse(code);
    setStatus('Generated random course');
}

// ==========================================
// INITIALIZATION
// ==========================================

document.addEventListener('DOMContentLoaded', () => {
    canvas = document.getElementById('game');
    ctx = canvas.getContext('2d');
    
    // Set canvas size
    canvas.width = GRID_COLS * TILE_SIZE;
    canvas.height = GRID_ROWS * TILE_SIZE;
    
    // Event listeners for mouse
    canvas.addEventListener('mousedown', handleMouseDown);
    canvas.addEventListener('mousemove', handleMouseMove);
    canvas.addEventListener('mouseup', handleMouseUp);
    canvas.addEventListener('mouseleave', handleMouseUp);
    
    // Event listeners for touch
    canvas.addEventListener('touchstart', handleTouchStart);
    canvas.addEventListener('touchmove', handleTouchMove);
    canvas.addEventListener('touchend', handleTouchEnd);
    
    // Load default course
    loadCourse(DEFAULT_COURSE_CODE);
    
    // Set default code in textarea
    const textarea = document.getElementById('courseCode');
    if (textarea) {
        textarea.value = DEFAULT_COURSE_CODE.trim();
    }
    
    // Button handlers
    document.getElementById('loadBtn')?.addEventListener('click', () => {
        const code = textarea?.value || DEFAULT_COURSE_CODE;
        loadCourse(code);
        setStatus('Course loaded!');
    });
    
    document.getElementById('resetBtn')?.addEventListener('click', () => {
        loadCourse(DEFAULT_COURSE_CODE);
        if (textarea) textarea.value = DEFAULT_COURSE_CODE.trim();
        setStatus('Game reset!');
    });
    
    // Example course buttons
    document.getElementById('exampleSimple')?.addEventListener('click', () => loadExampleCourse('simple'));
    document.getElementById('exampleBumper')?.addEventListener('click', () => loadExampleCourse('bumperMaze'));
    document.getElementById('exampleHazard')?.addEventListener('click', () => loadExampleCourse('hazards'));
    document.getElementById('examplePortal')?.addEventListener('click', () => loadExampleCourse('portals'));
    document.getElementById('exampleRamp')?.addEventListener('click', () => loadExampleCourse('ramps'));
    document.getElementById('randomBtn')?.addEventListener('click', generateRandomCourse);
    
    // Start game loop
    gameLoop();
});
    </script>
</body>
</html>