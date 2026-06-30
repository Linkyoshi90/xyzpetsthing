<?php
require_once __DIR__ . '/../lib/pet_parentage.php';

$daycareStallion = pet_parentage_insert_values(101, 12, null);
if ($daycareStallion !== [101, 12, null]) {
    fwrite(STDERR, "Daycare-stallion parentage must keep the father null.\n");
    exit(1);
}

$ownedParents = pet_parentage_insert_values(102, 12, 34);
if ($ownedParents !== [102, 12, 34]) {
    fwrite(STDERR, "Hatchling parentage must retain both selected parents.\n");
    exit(1);
}

$firstFamily = [12 => [], 4 => [], 2 => []];
$secondFamily = [18 => [], 7 => [], 2 => []];
if (!pet_parentage_maps_are_related($firstFamily, $secondFamily)) {
    fwrite(STDERR, "Pets with a shared recorded ancestor must be related.\n");
    exit(1);
}

if (pet_parentage_maps_are_related($firstFamily, [22 => [], 9 => []])) {
    fwrite(STDERR, "Pets with separate recorded ancestry must not overlap.\n");
    exit(1);
}

$generations = [
    1 => ['piid' => 1, 'motherid' => 10, 'fatherid' => 11],
    2 => ['piid' => 2, 'motherid' => 10, 'fatherid' => 11],
    10 => ['piid' => 10, 'motherid' => null, 'fatherid' => null],
    11 => ['piid' => 11, 'motherid' => null, 'fatherid' => null],
    20 => ['piid' => 20, 'motherid' => 1, 'fatherid' => 2],
    21 => ['piid' => 21, 'motherid' => 1, 'fatherid' => 2],
    30 => ['piid' => 30, 'motherid' => 20, 'fatherid' => 21],
    31 => ['piid' => 31, 'motherid' => 20, 'fatherid' => 21],
    40 => ['piid' => 40, 'motherid' => null, 'fatherid' => null],
];

if (pet_parentage_pair_inbreeding_generation_from_map(1, 2, $generations) !== 1) {
    fwrite(STDERR, "Full siblings must produce 1st-generation inbreeding.\n");
    exit(1);
}

if (pet_parentage_pair_inbreeding_generation_from_map(10, 1, $generations) !== 1) {
    fwrite(STDERR, "A parent/child pair must produce 1st-generation inbreeding.\n");
    exit(1);
}

if (pet_parentage_pair_inbreeding_generation_from_map(20, 21, $generations) !== 2) {
    fwrite(STDERR, "Two 1st-generation inbred siblings must produce generation 2.\n");
    exit(1);
}

if (pet_parentage_pair_inbreeding_generation_from_map(30, 31, $generations) !== 3) {
    fwrite(STDERR, "Continued close breeding must increment beyond generation 2.\n");
    exit(1);
}

if (pet_parentage_pair_inbreeding_generation_from_map(30, 40, $generations) !== 0) {
    fwrite(STDERR, "An unrelated pairing must reset the inbreeding generation.\n");
    exit(1);
}

echo "Pet parentage values test passed.\n";
