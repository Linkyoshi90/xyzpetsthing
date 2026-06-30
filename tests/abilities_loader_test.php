<?php
require_once __DIR__ . '/../lib/abilities.php';

$catalog = abilities_catalog();
if (count($catalog['by_species']) !== 210 || count($catalog['by_id']) !== 420) {
    fwrite(STDERR, "Ability catalog must contain two templates for all 210 encyclopedia species.\n");
    exit(1);
}

$bullywug = ability_for_pet(['species_id' => 263, 'ability_id' => 2631]);
if (($bullywug['name'] ?? '') !== 'Spring-Loaded Legs') {
    fwrite(STDERR, "Stored ability IDs must resolve through the JSON catalog.\n");
    exit(1);
}

$fallback = ability_for_pet(['species_id' => 248, 'ability_id' => 0]);
if (($fallback['name'] ?? '') !== 'Final Appointment') {
    fwrite(STDERR, "Pets without a stored selection must receive their species default.\n");
    exit(1);
}

echo "Ability loader test passed.\n";
