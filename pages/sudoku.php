<?php
require_once __DIR__.'/../auth.php';
require_login();
require_once __DIR__.'/../lib/input.php';

$uid = current_user()['id'];
$today = date('Y-m-d');

$dailyRow = q(
    'SELECT difficulty_percent, base_score, final_score, completed_at FROM daily_sudoku_runs WHERE user_id = ? AND run_date = ?',
    [$uid, $today]
)->fetch(PDO::FETCH_ASSOC);

if (!$dailyRow) {
    $difficultyPercent = random_int(1, 100);
    q(
        'INSERT INTO daily_sudoku_runs (user_id, run_date, difficulty_percent) VALUES (?, ?, ?)',
        [$uid, $today, $difficultyPercent]
    );
    $dailyRow = [
        'difficulty_percent' => $difficultyPercent,
        'base_score' => 0,
        'final_score' => 0,
        'completed_at' => null,
    ];
}

$difficultyPercent = (int)$dailyRow['difficulty_percent'];
$storedBaseScore = isset($dailyRow['base_score']) ? (int)$dailyRow['base_score'] : 0;
$storedFinalScore = isset($dailyRow['final_score']) ? (int)$dailyRow['final_score'] : 0;
$dailyCompleted = !empty($dailyRow['completed_at']);

if (!isset($_SESSION['sudoku_state'])
    || !is_array($_SESSION['sudoku_state'])
    || ($_SESSION['sudoku_state']['run_date'] ?? null) !== $today) {
    $_SESSION['sudoku_state'] = [
        'run_date' => $today,
        'difficulty_percent' => $difficultyPercent,
        'level' => 1,
        'score' => $dailyCompleted ? $storedBaseScore : 0,
        'correct_total' => 0,
        'incorrect_total' => 0,
        'board' => null,
        'solution' => null,
        'givens' => null,
        'last_result' => null,
        'last_message' => null,
        'game_over' => $dailyCompleted,
        'daily_completed' => $dailyCompleted,
        'quit_stats' => $dailyCompleted ? [
            'total' => 0,
            'correct' => 0,
            'incorrect' => 0,
            'accuracy' => 0,
            'false_ratio' => 0,
            'score' => $storedFinalScore,
            'base_score' => $storedBaseScore,
            'final_score' => $storedFinalScore,
            'difficulty_percent' => $difficultyPercent,
            'converted' => false,
        ] : null,
    ];
}

$state =& $_SESSION['sudoku_state'];
$state['run_date'] = $today;
$state['difficulty_percent'] = $difficultyPercent;
if (!isset($state['daily_completed'])) {
    $state['daily_completed'] = $dailyCompleted;
}

if ($dailyCompleted) {
    $state['score'] = $storedBaseScore;
    $state['game_over'] = true;
    $state['daily_completed'] = true;
    $state['board'] = null;
    $state['solution'] = null;
    $state['givens'] = null;
    $quitStats = $state['quit_stats'] ?? [];
    $quitStats['score'] = $storedFinalScore;
    $quitStats['base_score'] = $storedBaseScore;
    $quitStats['final_score'] = $storedFinalScore;
    $quitStats['difficulty_percent'] = $difficultyPercent;
    $quitStats['total'] = $quitStats['total'] ?? 0;
    $quitStats['correct'] = $quitStats['correct'] ?? 0;
    $quitStats['incorrect'] = $quitStats['incorrect'] ?? 0;
    $quitStats['accuracy'] = $quitStats['accuracy'] ?? 0;
    $quitStats['false_ratio'] = $quitStats['false_ratio'] ?? 0;
    if (!isset($quitStats['converted'])) {
        $quitStats['converted'] = false;
    }
    $state['quit_stats'] = $quitStats;
}

function sudoku_is_safe(array $board, int $row, int $col, int $num): bool {
    for ($i = 0; $i < 9; $i++) {
        if ($board[$row][$i] === $num || $board[$i][$col] === $num) {
            return false;
        }
    }
    $startRow = (int)($row / 3) * 3;
    $startCol = (int)($col / 3) * 3;
    for ($r = $startRow; $r < $startRow + 3; $r++) {
        for ($c = $startCol; $c < $startCol + 3; $c++) {
            if ($board[$r][$c] === $num) {
                return false;
            }
        }
    }
    return true;
}

function sudoku_fill_board(array &$board, int $row = 0, int $col = 0): bool {
    if ($row === 9) {
        return true;
    }
    $nextRow = $col === 8 ? $row + 1 : $row;
    $nextCol = $col === 8 ? 0 : $col + 1;
    $numbers = range(1, 9);
    shuffle($numbers);
    foreach ($numbers as $num) {
        if (sudoku_is_safe($board, $row, $col, $num)) {
            $board[$row][$col] = $num;
            if (sudoku_fill_board($board, $nextRow, $nextCol)) {
                return true;
            }
        }
    }
    $board[$row][$col] = 0;
    return false;
}

function sudoku_generate_solution(): array {
    $board = array_fill(0, 9, array_fill(0, 9, 0));
    sudoku_fill_board($board);
    return $board;
}

function sudoku_holes_for_level(int $level): int {
    $holes = 32 + ($level - 1) * 3;
    if ($holes > 60) {
        $holes = 60;
    }
    return $holes;
}

function sudoku_build_level(int $level): array {
    $solution = sudoku_generate_solution();
    $holes = sudoku_holes_for_level($level);
    $positions = [];
    for ($r = 0; $r < 9; $r++) {
        for ($c = 0; $c < 9; $c++) {
            $positions[] = [$r, $c];
        }
    }
    shuffle($positions);
    $puzzle = $solution;
    $givens = array_fill(0, 9, array_fill(0, 9, true));
    $removed = 0;
    foreach ($positions as [$r, $c]) {
        if ($removed >= $holes) {
            break;
        }
        $puzzle[$r][$c] = 0;
        $givens[$r][$c] = false;
        $removed++;
    }
    return [
        'board' => $puzzle,
        'solution' => $solution,
        'givens' => $givens,
    ];
}

if (!$state['game_over'] && (!is_array($state['board']) || !is_array($state['solution']))) {
    $levelData = sudoku_build_level($state['level']);
    $state['board'] = $levelData['board'];
    $state['solution'] = $levelData['solution'];
    $state['givens'] = $levelData['givens'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = input_string($_POST['action'] ?? '', 20);
    if ($action === 'submit' && !$state['game_over']) {
        $answers = $_POST['cell'] ?? [];
        $correct = 0;
        $totalBlanks = 0;
        $resultGrid = [];
        for ($r = 0; $r < 9; $r++) {
            $resultGrid[$r] = [];
            for ($c = 0; $c < 9; $c++) {
                $solutionValue = (int)$state['solution'][$r][$c];
                if (!empty($state['givens'][$r][$c])) {
                    $resultGrid[$r][$c] = ['value' => $solutionValue, 'status' => 'given'];
                    continue;
                }
                $totalBlanks++;
                $raw = $answers[$r][$c] ?? '';
                $value = input_int($raw);
                if ($value < 1 || $value > 9) {
                    $value = 0;
                }
                if ($value === $solutionValue) {
                    $correct++;
                    $status = 'correct';
                } elseif ($value === 0) {
                    $status = 'blank';
                } else {
                    $status = 'incorrect';
                }
                $resultGrid[$r][$c] = ['value' => $solutionValue, 'status' => $status];
            }
        }
        $incorrect = $totalBlanks - $correct;
        $state['correct_total'] += $correct;
        $state['incorrect_total'] += $incorrect;
        $earned = $correct * 10;
        $state['score'] += $earned;
        $state['last_result'] = [
            'level' => $state['level'],
            'correct' => $correct,
            'incorrect' => $incorrect,
            'total' => $totalBlanks,
            'earned' => $earned,
            'grid' => $resultGrid,
        ];
        $state['level']++;
        $state['last_message'] = null;
        $levelData = sudoku_build_level($state['level']);
        $state['board'] = $levelData['board'];
        $state['solution'] = $levelData['solution'];
        $state['givens'] = $levelData['givens'];
        header('Location: index.php?pg=sudoku');
        exit;
    }
    if ($action === 'quit') {
        if (!empty($state['daily_completed'])) {
            $state['last_message'] = 'Sudoku can only be completed once per day. Come back tomorrow for a new puzzle.';
            header('Location: index.php?pg=sudoku');
            exit;
        }
        $total = $state['correct_total'] + $state['incorrect_total'];
        if ($total <= 0) {
            $state['last_message'] = 'You need to finish at least one level before ending the run.';
        } else {
            $falseRatio = $total > 0 ? $state['incorrect_total'] / $total : 1;
            if ($falseRatio < 0.5) {
                $state['game_over'] = true;
                $accuracy = $total > 0 ? round(($state['correct_total'] / $total) * 100, 1) : 0;
                $baseScore = $state['score'];
                $finalDifficulty = (int)$state['difficulty_percent'];
                $finalScore = $baseScore * $finalDifficulty;
                $state['score'] = $baseScore;
                $state['quit_stats'] = [
                    'total' => $total,
                    'correct' => $state['correct_total'],
                    'incorrect' => $state['incorrect_total'],
                    'accuracy' => $accuracy,
                    'false_ratio' => $falseRatio,
                    'score' => $finalScore,
                    'base_score' => $baseScore,
                    'final_score' => $finalScore,
                    'difficulty_percent' => $finalDifficulty,
                    'converted' => $state['quit_stats']['converted'] ?? false,
                ];
                $state['board'] = null;
                $state['solution'] = null;
                $state['givens'] = null;
                $state['daily_completed'] = true;
                q(
                    'UPDATE daily_sudoku_runs SET base_score = ?, final_score = ?, completed_at = NOW() WHERE user_id = ? AND run_date = ?',
                    [$baseScore, $finalScore, $uid, $today]
                );
            } else {
                $state['last_message'] = 'Too many incorrect answers to finish the run. Keep practicing!';
            }
        }
        header('Location: index.php?pg=sudoku');
        exit;
    }
    if ($action === 'restart') {
        $state['last_message'] = 'Sudoku is limited to one run per day. A new puzzle will be available tomorrow.';
        header('Location: index.php?pg=sudoku');
        exit;
    }
}

$lastResult = $state['last_result'] ?? null;
if ($lastResult) {
    unset($_SESSION['sudoku_state']['last_result']);
}
$lastMessage = $state['last_message'] ?? null;
if ($lastMessage) {
    unset($_SESSION['sudoku_state']['last_message']);
}
$gameOver = !empty($state['game_over']);
$quitStats = $state['quit_stats'] ?? null;
$board = $state['board'];
$givens = $state['givens'];
$level = $state['level'];
$dailyDifficulty = (int)($state['difficulty_percent'] ?? $difficultyPercent);
$baseScoreDisplay = $gameOver && is_array($quitStats) && array_key_exists('base_score', $quitStats)
    ? (int)$quitStats['base_score']
    : (int)$state['score'];
$finalScoreDisplay = $gameOver && is_array($quitStats) && array_key_exists('final_score', $quitStats)
    ? (int)$quitStats['final_score']
    : $baseScoreDisplay * $dailyDifficulty;
$hasDetailedStats = is_array($quitStats) && !empty($quitStats['total']);
?>
<link rel="stylesheet" href="assets/css/sudoku.css">
<script defer src="assets/js/sudoku.js"></script>
<h1>Sudoku Sprint</h1>
<div class="sudoku-daily-info">
  <p><strong>Today's difficulty:</strong> <?php echo htmlspecialchars((string)$dailyDifficulty); ?>%</p>
  <p class="muted">Final score = Base score x Difficulty = <?php echo htmlspecialchars(number_format($baseScoreDisplay)); ?> x <?php echo htmlspecialchars((string)$dailyDifficulty); ?> = <?php echo htmlspecialchars(number_format($finalScoreDisplay)); ?></p>
</div>
<?php if ($lastResult): ?>
  <div class="sudoku-result">
    <h2>Level <?php echo htmlspecialchars((string)$lastResult['level']); ?> complete</h2>
    <p>You solved <?php echo htmlspecialchars((string)$lastResult['correct']); ?> of <?php echo htmlspecialchars((string)$lastResult['total']); ?> empty spaces correctly. Solutions are shown below.</p>
    <div class="sudoku-grid result-grid">
      <?php for ($r = 0; $r < 9; $r++): ?>
        <?php for ($c = 0; $c < 9; $c++):
          $cell = $lastResult['grid'][$r][$c];
          $classes = [];
          if (in_array($c, [2, 5], true)) {
              $classes[] = 'subgrid-border';
          }
          if (in_array($r, [2, 5], true)) {
              $classes[] = 'subgrid-border-bottom';
          }
          if (!empty($cell['status'])) {
              $classes[] = $cell['status'];
          }
          $classAttr = $classes ? ' class="'.implode(' ', $classes).'"' : '';
        ?>
          <span<?php echo $classAttr; ?>><?php echo htmlspecialchars((string)$cell['value']); ?></span>
        <?php endfor; ?>
      <?php endfor; ?>
    </div>
  </div>
<?php endif; ?>
<?php if ($lastMessage): ?>
  <div class="sudoku-message"><?php echo htmlspecialchars($lastMessage); ?></div>
<?php endif; ?>
<?php if ($gameOver && $quitStats): ?>
  <div class="game-over-card">
    <h2>Game Over</h2>
    <p>Your final performance:</p>
    <ul>
      <li>Base score: <strong><?php echo htmlspecialchars(number_format((int)($quitStats['base_score'] ?? 0))); ?></strong></li>
      <li>Difficulty multiplier: <?php echo htmlspecialchars((string)($quitStats['difficulty_percent'] ?? $dailyDifficulty)); ?>x (<?php echo htmlspecialchars((string)($quitStats['difficulty_percent'] ?? $dailyDifficulty)); ?>%)</li>
      <li>Final score: <strong><?php echo htmlspecialchars(number_format((int)($quitStats['final_score'] ?? $quitStats['score'] ?? 0))); ?></strong></li>
      <?php if ($hasDetailedStats): ?>
        <li>Correct answers: <?php echo htmlspecialchars((string)($quitStats['correct'] ?? 0)); ?></li>
        <li>Incorrect answers: <?php echo htmlspecialchars((string)($quitStats['incorrect'] ?? 0)); ?></li>
        <li>Accuracy: <?php echo htmlspecialchars((string)($quitStats['accuracy'] ?? 0)); ?>%</li>
      <?php endif; ?>
    </ul>
    <?php $finalScoreForExchange = (int)($quitStats['final_score'] ?? $quitStats['score'] ?? 0); ?>
    <?php if (empty($quitStats['converted']) && $finalScoreForExchange > 0): ?>
      <button class="btn" data-sudoku-exchange data-score="<?php echo htmlspecialchars((string)$finalScoreForExchange); ?>">Convert score</button>
      <div class="exchange-status" role="status"></div>
    <?php elseif (!empty($quitStats['converted'])): ?>
      <p class="muted">Score already converted. Enjoy your rewards!</p>
    <?php else: ?>
      <p class="muted">No score to convert this time.</p>
    <?php endif; ?>
    <p class="muted" style="margin-top:1.5rem;">Come back tomorrow for a new Sudoku challenge.</p>
  </div>
<?php elseif (!$gameOver && is_array($board) && is_array($givens)): ?>
  <div class="sudoku-wrapper">
    <form method="post">
      <input type="hidden" name="action" value="submit">
      <div class="sudoku-grid" aria-label="Sudoku board level <?php echo htmlspecialchars((string)$level); ?>">
        <?php for ($r = 0; $r < 9; $r++): ?>
          <?php for ($c = 0; $c < 9; $c++):
            $classes = [];
            if (in_array($c, [2, 5], true)) {
                $classes[] = 'subgrid-border';
            }
            if (in_array($r, [2, 5], true)) {
                $classes[] = 'subgrid-border-bottom';
            }
            if (!empty($givens[$r][$c])) {
                $classes[] = 'given';
            }
            $classAttr = $classes ? ' class="'.implode(' ', $classes).'"' : '';
          ?>
            <?php if (!empty($givens[$r][$c])): ?>
              <span<?php echo $classAttr; ?>><?php echo htmlspecialchars((string)$board[$r][$c]); ?></span>
            <?php else: ?>
              <input<?php echo $classAttr; ?> type="text" inputmode="numeric" pattern="[1-9]" maxlength="1" name="cell[<?php echo $r; ?>][<?php echo $c; ?>]" aria-label="Row <?php echo $r + 1; ?> column <?php echo $c + 1; ?>" autocomplete="off">
            <?php endif; ?>
          <?php endfor; ?>
        <?php endfor; ?>
      </div>
      <div class="sudoku-actions" style="margin-top:1rem;">
        <button type="submit" class="btn primary">Send answers</button>
      </div>
    </form>
    <div class="info-panel">
      <div class="sudoku-message">Fill in the empty spaces with numbers 1-9. When you're confident in your solution, send your answers to check them.</div>
      <form method="post" class="sudoku-actions">
        <input type="hidden" name="action" value="quit">
        <button type="submit" class="btn">Quit run</button>
      </form>
    </div>
  </div>
<?php endif; ?>
