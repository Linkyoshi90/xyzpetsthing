'use strict';

const engine = require('../assets/js/battle-abilities.js');

function creature(name, instructions, overrides = {}) {
  return {
    id: overrides.id || name,
    name,
    hp: overrides.hp ?? 100,
    maxHp: overrides.maxHp ?? 100,
    attack: overrides.attack ?? 50,
    defense: overrides.defense ?? 40,
    speed: overrides.speed ?? 30,
    ability: {
      name: `${name} Ability`,
      battle: { instructions },
    },
    abilityRuntime: { used: Object.create(null) },
  };
}

const starter = creature('Starter', [
  { key: 'speed', trigger: 'battle_start', effect: 'multiply_stat', stat: 'speed', value: 1.2 },
]);
engine.prepareCreature(starter);
if (starter.speed !== 36) throw new Error('Battle-start stat multipliers must be applied.');

const striker = creature('Striker', [
  { key: 'contact', trigger: 'outgoing_damage', effect: 'multiply_damage', value: 1.2, conditions: { contact: true } },
  { key: 'priority', trigger: 'turn_order', effect: 'add_priority', value: 1, conditions: { contact: true } },
]);
const target = creature('Target', [], { id: 2 });
const contactMove = { key: 'tackle', contact: true, category: 'physical', elementName: 'Vulgaris' };
const boosted = engine.modifyDamage({ attacker: striker, target, move: contactMove, damage: 50 });
if (boosted.damage !== 60) throw new Error('Conditional outgoing damage must be interpreted.');
if (engine.priorityFor(striker, contactMove) !== 1) throw new Error('Ability move priority must be interpreted.');

const survivor = creature('Survivor', [
  { key: 'endure', trigger: 'lethal_damage', effect: 'survive_lethal', once_per_battle: true },
], { hp: 50, maxHp: 50 });
const plainAttacker = creature('Plain Attacker', []);
const firstLethal = engine.modifyDamage({ attacker: plainAttacker, target: survivor, move: contactMove, damage: 60 });
const secondLethal = engine.modifyDamage({ attacker: plainAttacker, target: survivor, move: contactMove, damage: 60 });
if (firstLethal.damage !== 49 || secondLethal.damage !== 60) {
  throw new Error('One-use lethal survival must trigger exactly once.');
}

const siphon = creature('Siphon', [
  { key: 'heal', trigger: 'after_damage', effect: 'heal_damage_percent', value: 0.15, conditions: { damage_positive: true } },
  { key: 'slow', trigger: 'after_damage', effect: 'multiply_target_stat', stat: 'speed', value: 0.9, once_per_target: true, conditions: { damage_positive: true } },
], { hp: 50, maxHp: 100 });
const victim = creature('Victim', [], { id: 3, speed: 40 });
const aftermath = engine.afterDamage({ attacker: siphon, target: victim, move: contactMove, damage: 20 });
engine.afterDamage({ attacker: siphon, target: victim, move: contactMove, damage: 20 });
if (aftermath.healing !== 3 || siphon.hp !== 56 || victim.speed !== 36) {
  throw new Error('Healing and once-per-target stat shifts must be interpreted correctly.');
}

console.log('Battle ability interpreter test passed.');
