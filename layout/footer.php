<?php if($pg !== 'map'): ?>
</main>
<?php
$runtime_errors = app_get_errors();

try {
    if (function_exists('db')) {
        $pdo = db();
        if ($pdo) {
            $pdo->query('SELECT 1');
        }
    }
} catch (Throwable $e) {
    app_add_error_from_exception($e, 'Runtime error:');
    $runtime_errors = app_get_errors();
}
?>
<footer class="foot">
  <div class="foot-status">
    <div><?= date('Y') ?> Harmontide</div>
    <?php if(!empty($runtime_errors)): ?>
    <div class="foot-errors" role="status" aria-live="polite">
      <div>⚠️</div>
      <div>
        <strong>Issues detected:</strong>
        <ul>
          <?php foreach ($runtime_errors as $err): ?>
          <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
    <?php else: ?>
    <div class="foot-errors" role="status" aria-live="polite">
      <div>✅</div>
      <div>All systems operational.</div>
    </div>
    <?php endif; ?>
    <div id="foot-client-errors" class="foot-errors" role="alert" aria-live="assertive" hidden>
      <div>⚠️</div>
      <div>
        <strong>Client issues detected:</strong>
        <ul id="foot-client-errors-list"></ul>
      </div>
    </div>
  </div>
</footer>
<script>
(function(){
  const clientErrors = document.getElementById('foot-client-errors');
  const clientList = document.getElementById('foot-client-errors-list');
  if (!clientErrors || !clientList) return;

  const seen = new Set();
  function addError(message) {
    if (!message || seen.has(message)) return;
    seen.add(message);
    const item = document.createElement('li');
    item.textContent = message;
    clientList.appendChild(item);
    clientErrors.hidden = false;
  }

  window.reportAppError = addError;

  window.addEventListener('error', (event) => {
    if (event && event.message) {
      addError(event.message);
    }
  });

  window.addEventListener('unhandledrejection', (event) => {
    const reason = event && event.reason;
    if (!reason) return;
    if (typeof reason === 'string') {
      addError(reason);
    } else if (reason && reason.message) {
      addError(reason.message);
    }
  });
})();
</script>
</body></html>
<?php endif; ?>