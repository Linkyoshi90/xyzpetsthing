<?php
require_login();
require_once __DIR__.'/../lib/input.php';
require_once __DIR__.'/../lib/user_settings.php';

$nsfwEnabled = user_settings_nsfw_enabled();
$saved = input_string($_GET['saved'] ?? '', 5) === '1';
?>
<style>
  .settings-page {
    display: grid;
    gap: 16px;
    padding: 18px 0 24px;
  }

  .settings-page h1 {
    margin: 0;
  }

  .settings-panel {
    display: grid;
    gap: 18px;
    padding: 18px;
    border: 1px solid rgba(255, 255, 255, 0.78);
    border-radius: 10px;
    background:
      linear-gradient(135deg, rgba(255, 255, 255, 0.76), rgba(221, 249, 255, 0.46) 46%, rgba(232, 255, 236, 0.42)),
      var(--glass-bg);
    box-shadow: var(--shadow), inset 0 1px 0 rgba(255, 255, 255, 0.88);
  }

  .settings-toggle-row {
    position: relative;
    display: grid;
    grid-template-columns: auto minmax(0, 1fr) auto;
    align-items: center;
    gap: 14px;
    margin: 0;
    cursor: pointer;
  }

  .settings-toggle-input {
    position: absolute;
    width: 1px;
    height: 1px;
    min-height: 0;
    padding: 0;
    margin: 0;
    border: 0;
    opacity: 0;
    box-shadow: none;
  }

  .settings-toggle-track {
    position: relative;
    width: 58px;
    height: 32px;
    border: 1px solid rgba(255, 255, 255, 0.86);
    border-radius: 999px;
    background: linear-gradient(rgba(255, 255, 255, 0.9), rgba(198, 214, 229, 0.74));
    box-shadow: inset 0 2px 5px rgba(28, 72, 100, 0.18);
    transition: background 160ms ease, box-shadow 160ms ease;
  }

  .settings-toggle-track::after {
    content: "";
    position: absolute;
    top: 3px;
    left: 3px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: linear-gradient(#fff, #d9f8ff);
    box-shadow: 0 4px 10px rgba(20, 84, 120, 0.22);
    transition: transform 160ms ease;
  }

  .settings-toggle-input:checked + .settings-toggle-track {
    background: linear-gradient(rgba(255, 255, 255, 0.94), rgba(136, 244, 184, 0.76));
    box-shadow: inset 0 2px 5px rgba(10, 96, 58, 0.18), 0 0 0 4px rgba(136, 244, 184, 0.18);
  }

  .settings-toggle-input:checked + .settings-toggle-track::after {
    transform: translateX(26px);
  }

  .settings-toggle-input:focus-visible + .settings-toggle-track {
    outline: 2px solid rgba(30, 134, 255, 0.56);
    outline-offset: 3px;
  }

  .settings-toggle-copy {
    display: grid;
    gap: 4px;
    min-width: 0;
  }

  .settings-toggle-copy strong {
    font-size: 1.05rem;
  }

  .settings-toggle-copy span,
  .settings-saved {
    color: var(--muted);
  }

  .settings-state {
    justify-self: end;
    min-width: 54px;
    padding: 4px 10px;
    border: 1px solid rgba(255, 255, 255, 0.72);
    border-radius: 999px;
    color: <?= $nsfwEnabled ? '#0a4d38' : '#6b5261' ?>;
    background: <?= $nsfwEnabled ? 'rgba(136, 244, 184, 0.44)' : 'rgba(255, 255, 255, 0.42)' ?>;
    text-align: center;
    font-weight: 900;
  }

  .settings-actions {
    display: flex;
    justify-content: flex-end;
  }

  @media (max-width: 640px) {
    .settings-toggle-row {
      grid-template-columns: auto minmax(0, 1fr);
    }

    .settings-state {
      grid-column: 2;
      justify-self: start;
    }
  }
</style>

<div class="settings-page">
  <h1>Settings</h1>

  <?php if ($saved): ?>
    <p class="settings-saved">Settings saved.</p>
  <?php endif; ?>

  <form class="settings-panel" method="post" action="?pg=settings">
    <label class="settings-toggle-row">
      <input
        class="settings-toggle-input"
        type="checkbox"
        name="nsfw_mode"
        value="1"
        <?= $nsfwEnabled ? 'checked' : '' ?>
      >
      <span class="settings-toggle-track" aria-hidden="true"></span>
      <span class="settings-toggle-copy">
        <strong>NSFW mode</strong>
        <span>Allow creature art from images/creatures/nsfw concepts.</span>
      </span>
      <span class="settings-state"><?= $nsfwEnabled ? 'On' : 'Off' ?></span>
    </label>

    <div class="settings-actions">
      <button class="btn aero-primary" type="submit">Save settings</button>
    </div>
  </form>
</div>

<script>
document.querySelector('.settings-toggle-input')?.addEventListener('change', (event) => {
  const form = event.currentTarget.form;
  if (!form) return;
  if (typeof form.requestSubmit === 'function') {
    form.requestSubmit();
    return;
  }
  form.submit();
});
</script>
