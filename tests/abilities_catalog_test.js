'use strict';

const fs = require('fs');
const path = require('path');

const root = path.resolve(__dirname, '..');
const catalog = JSON.parse(fs.readFileSync(path.join(root, 'data', 'abilities.json'), 'utf8'));
const lore = JSON.parse(fs.readFileSync(path.join(root, 'data', 'creatures-lore.json'), 'utf8'));
const abilities = Array.isArray(catalog.abilities) ? catalog.abilities : [];
const creatures = Array.isArray(lore.creatures) ? lore.creatures : [];

const ids = new Set();
const bySpecies = new Map();
for (const ability of abilities) {
  if (!Number.isInteger(ability.id) || ability.id <= 0 || ids.has(ability.id)) {
    throw new Error(`Ability ID must be a unique positive integer: ${ability.id}`);
  }
  ids.add(ability.id);

  if (!ability.name || !ability.description || !ability.battle?.summary) {
    throw new Error(`Ability ${ability.id} is missing readable text.`);
  }
  if (!Array.isArray(ability.battle.instructions) || ability.battle.instructions.length === 0) {
    throw new Error(`Ability ${ability.id} has no battle instructions.`);
  }

  const speciesId = Number(ability.species?.id || 0);
  bySpecies.set(speciesId, (bySpecies.get(speciesId) || 0) + 1);
}

for (const creature of creatures) {
  if ((bySpecies.get(Number(creature.species_id)) || 0) < 2) {
    throw new Error(`${creature.name} needs at least two ability templates.`);
  }
}

const namesFor = (species) => abilities
  .filter((ability) => ability.species?.name === species)
  .map((ability) => ability.name);

if (!namesFor('Bullywug').includes('Spring-Loaded Legs') || !namesFor('Bullywug').includes('Tongue Snare')) {
  throw new Error('Bullywug is missing its frog-specific abilities.');
}
if (!namesFor('Death').includes('Final Appointment') || !namesFor('Death').includes("Death's Door")) {
  throw new Error('Death is missing its threshold-specific abilities.');
}

console.log(`Ability catalog test passed (${abilities.length} abilities, ${bySpecies.size} species).`);
