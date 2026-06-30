<?php
require_once __DIR__ . '/../lib/breeding.php';

$stats = [
    'hp_current' => 12,
    'hp_max' => 12,
    'atk' => 20,
    'def' => 15,
    'initiative' => 8,
];

$firstGeneration = breeding_apply_inbreeding_stat_modifier($stats, 1);
if ($firstGeneration['stat'] !== 'atk' || $firstGeneration['stats']['atk'] !== 30) {
    fwrite(STDERR, "Generation 1 must increase the highest stat by 50%.\n");
    exit(1);
}

$laterGeneration = breeding_apply_inbreeding_stat_modifier($stats, 2);
if ($laterGeneration['stat'] !== 'atk' || $laterGeneration['stats']['atk'] !== 10) {
    fwrite(STDERR, "Generation 2 and later must decrease the highest stat by 50%.\n");
    exit(1);
}

$hpStats = [
    'hp_current' => 21,
    'hp_max' => 21,
    'atk' => 10,
    'def' => 9,
    'initiative' => 8,
];
$hpResult = breeding_apply_inbreeding_stat_modifier($hpStats, 1);
if ($hpResult['stats']['hp_max'] !== 32 || $hpResult['stats']['hp_current'] !== 32) {
    fwrite(STDERR, "Changing maximum HP must keep current HP synchronized.\n");
    exit(1);
}

$ordinary = breeding_apply_inbreeding_stat_modifier($stats, 0);
if ($ordinary['stats'] !== $stats || $ordinary['stat'] !== null) {
    fwrite(STDERR, "Unrelated hatchlings must not receive an inbreeding modifier.\n");
    exit(1);
}

$firstMessage = breeding_inbreeding_hatch_message('Test Pet', 1, $firstGeneration);
if (strpos($firstMessage, 'Selective breeding strengthened') === false
    || strpos($firstMessage, 'further close breeding will weaken') === false) {
    fwrite(STDERR, "Generation 1 messaging must balance its benefit with the future risk.\n");
    exit(1);
}

$laterMessage = breeding_inbreeding_hatch_message('Test Pet', 3, $laterGeneration);
if (strpos($laterMessage, 'weakened') === false || strpos($laterMessage, 'genetic diversity') === false) {
    fwrite(STDERR, "Later-generation messaging must encourage genetic diversity.\n");
    exit(1);
}

echo "Breeding inbreeding modifier test passed.\n";
