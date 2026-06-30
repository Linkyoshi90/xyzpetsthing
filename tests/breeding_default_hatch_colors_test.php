<?php
require_once __DIR__ . '/../lib/breeding.php';

$expectedColors = ['red', 'blue', 'yellow', 'purple', 'green'];

if (BREEDING_DEFAULT_HATCH_COLOR_NAMES !== $expectedColors) {
    fwrite(STDERR, "Breeding hatch colors must be limited to the five default colors.\n");
    exit(1);
}

echo "Breeding default hatch color test passed.\n";
