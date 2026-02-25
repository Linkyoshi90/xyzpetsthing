<?php
require_login();

$bestiaryPath = __DIR__ . '/bestiary.php';
$bestiaryHtml = '';

if (is_file($bestiaryPath)) {
    ob_start();
    include $bestiaryPath;
    $bestiaryHtml = (string)ob_get_clean();
}

if ($bestiaryHtml === '') {
    echo '<section class="encyclopedia"><p class="muted">Bestiary content is currently unavailable.</p></section>';
    return;
}

// Keep bestiary source unchanged, but patch runtime HTML for embedded mode.
$bestiaryHtml = str_replace(
    "const closeBook = document.getElementById('close-book');",
    "const closeBookButton = document.getElementById('close-book');",
    $bestiaryHtml
);
$bestiaryHtml = str_replace(
    "closeBook.addEventListener('click', closeBook);",
    "closeBookButton.addEventListener('click', closeBook);",
    $bestiaryHtml
);

$headContent = '';
$bodyAttributes = '';
$bodyInner = $bestiaryHtml;

if (preg_match('/<head[^>]*>(.*?)<\/head>/is', $bestiaryHtml, $headMatch)) {
    $headContent = $headMatch[1];
}

if (preg_match('/<body([^>]*)>(.*?)<\/body>/is', $bestiaryHtml, $bodyMatch)) {
    $bodyAttributes = $bodyMatch[1];
    $bodyInner = $bodyMatch[2];
}

$headAssets = '';
if ($headContent !== '') {
    preg_match_all('/<(script|style)\b[^>]*>[\s\S]*?<\/\1>/i', $headContent, $assetMatches);
    if (!empty($assetMatches[0])) {
        $headAssets = implode("\n", $assetMatches[0]);
    }
}

$bodyClass = 'min-h-screen';
if ($bodyAttributes !== '' && preg_match('/class=["\']([^"\']+)["\']/i', $bodyAttributes, $classMatch)) {
    $bodyClass = $classMatch[1];
}
?>

<section class="encyclopedia">
  <div class="encyclopedia__intro">
    <h1>Creature Encyclopedia</h1>
    <p class="muted">The standalone Bestiary now runs directly inside the encyclopedia page.</p>
  </div>
</section>

<?= $headAssets ?>

<section class="<?= htmlspecialchars($bodyClass) ?>">
  <?= $bodyInner ?>
</section>
