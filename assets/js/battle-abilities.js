(function abilityModule(root, factory) {
  'use strict';

  const engine = factory();
  if (typeof module === 'object' && module.exports) {
    module.exports = engine;
  }
  if (root) {
    root.BattleAbilityEngine = engine;
  }
})(typeof window !== 'undefined' ? window : globalThis, () => {
  'use strict';

  const allowedStats = new Set(['attack', 'defense', 'speed', 'maxHp']);

  function instructionsFor(creature, trigger) {
    const instructions = creature?.ability?.battle?.instructions;
    if (!Array.isArray(instructions)) return [];
    return instructions.filter((instruction) => instruction?.trigger === trigger);
  }

  function runtimeFor(creature) {
    if (!creature.abilityRuntime || typeof creature.abilityRuntime !== 'object') {
      creature.abilityRuntime = { used: Object.create(null) };
    }
    if (!creature.abilityRuntime.used) {
      creature.abilityRuntime.used = Object.create(null);
    }
    return creature.abilityRuntime;
  }

  function usageKey(instruction, target) {
    const base = String(instruction.key || `${instruction.trigger}:${instruction.effect}`);
    return instruction.once_per_target ? `${base}:target:${target?.id ?? 'unknown'}` : base;
  }

  function wasUsed(creature, instruction, target) {
    if (!instruction.once_per_battle && !instruction.once_per_target) return false;
    return Boolean(runtimeFor(creature).used[usageKey(instruction, target)]);
  }

  function markUsed(creature, instruction, target, preview) {
    if (preview || (!instruction.once_per_battle && !instruction.once_per_target)) return;
    runtimeFor(creature).used[usageKey(instruction, target)] = true;
  }

  function normalizedList(value) {
    return Array.isArray(value) ? value.map((entry) => String(entry).toLowerCase()) : [];
  }

  function matches(instruction, context) {
    const conditions = instruction.conditions || {};
    const move = context.move || {};
    const self = context.self || {};
    const target = context.target || {};

    if (Array.isArray(conditions.move_categories)
      && !normalizedList(conditions.move_categories).includes(String(move.category || '').toLowerCase())) return false;
    if (typeof conditions.contact === 'boolean' && Boolean(move.contact) !== conditions.contact) return false;
    if (Array.isArray(conditions.element_names)
      && !normalizedList(conditions.element_names).includes(String(move.elementName || '').toLowerCase())) return false;
    if (Array.isArray(conditions.move_keys)
      && !normalizedList(conditions.move_keys).includes(String(move.key || move.moveKey || '').toLowerCase())) return false;
    if (Number.isFinite(Number(conditions.target_hp_at_or_below))) {
      const ratio = Number(target.maxHp) > 0 ? Number(target.hp) / Number(target.maxHp) : 1;
      if (ratio > Number(conditions.target_hp_at_or_below)) return false;
    }
    if (Number.isFinite(Number(conditions.self_hp_at_or_below))) {
      const ratio = Number(self.maxHp) > 0 ? Number(self.hp) / Number(self.maxHp) : 1;
      if (ratio > Number(conditions.self_hp_at_or_below)) return false;
    }
    if (conditions.damage_positive === true && Number(context.damage || 0) <= 0) return false;

    return true;
  }

  function messageFor(creature, target, instruction) {
    const template = String(instruction.message || '');
    if (!template) return '';
    return template
      .replaceAll('{self}', String(creature?.name || 'The creature'))
      .replaceAll('{target}', String(target?.name || 'the target'))
      .replaceAll('{ability}', String(creature?.ability?.name || 'Its ability'));
  }

  function prepareCreature(creature) {
    if (!creature) return [];
    runtimeFor(creature);
    const messages = [];

    for (const instruction of instructionsFor(creature, 'battle_start')) {
      if (instruction.effect !== 'multiply_stat' || !allowedStats.has(instruction.stat)) continue;
      if (!matches(instruction, { self: creature }) || wasUsed(creature, instruction)) continue;

      const before = Math.max(1, Number(creature[instruction.stat] || 1));
      const after = Math.max(1, Math.round(before * Number(instruction.value || 1)));
      creature[instruction.stat] = after;
      if (instruction.stat === 'maxHp') {
        creature.hp = Math.max(0, Math.min(after, Number(creature.hp || 0) + (after - before)));
      }
      markUsed(creature, instruction, null, false);
      const message = messageFor(creature, null, instruction);
      if (message) messages.push(message);
    }

    return messages;
  }

  function priorityFor(creature, move) {
    let priority = Number(move?.priority || 0);
    for (const instruction of instructionsFor(creature, 'turn_order')) {
      if (instruction.effect !== 'add_priority') continue;
      if (!matches(instruction, { self: creature, move })) continue;
      priority += Number(instruction.value || 0);
    }
    return priority;
  }

  function applyDamageInstructions(creature, target, move, damage, trigger, preview, messages) {
    let adjusted = damage;
    for (const instruction of instructionsFor(creature, trigger)) {
      if (instruction.effect !== 'multiply_damage') continue;
      const context = { self: creature, target, move, damage: adjusted };
      if (!matches(instruction, context) || wasUsed(creature, instruction, target)) continue;
      if (adjusted > 0) {
        adjusted = Math.max(1, Math.round(adjusted * Number(instruction.value || 1)));
      }
      markUsed(creature, instruction, target, preview);
      const message = messageFor(creature, target, instruction);
      if (message) messages.push(message);
    }
    return adjusted;
  }

  function modifyDamage({ attacker, target, move, damage, preview = false }) {
    const messages = [];
    let adjusted = Math.max(0, Math.round(Number(damage || 0)));
    adjusted = applyDamageInstructions(attacker, target, move, adjusted, 'outgoing_damage', preview, messages);
    adjusted = applyDamageInstructions(target, attacker, move, adjusted, 'incoming_damage', preview, messages);

    if (adjusted >= Number(target?.hp || 0) && Number(target?.hp || 0) > 0) {
      for (const instruction of instructionsFor(target, 'lethal_damage')) {
        if (instruction.effect !== 'survive_lethal') continue;
        if (!matches(instruction, { self: target, target: attacker, move, damage: adjusted })) continue;
        if (wasUsed(target, instruction, attacker)) continue;
        adjusted = Math.max(0, Number(target.hp) - 1);
        markUsed(target, instruction, attacker, preview);
        const message = messageFor(target, attacker, instruction);
        if (message) messages.push(message);
        break;
      }
    }

    return { damage: adjusted, messages };
  }

  function afterDamage({ attacker, target, move, damage, preview = false }) {
    const messages = [];
    let healing = 0;

    for (const instruction of instructionsFor(attacker, 'after_damage')) {
      const context = { self: attacker, target, move, damage };
      if (!matches(instruction, context) || wasUsed(attacker, instruction, target)) continue;

      if (instruction.effect === 'heal_damage_percent' && damage > 0) {
        const available = Math.max(0, Number(attacker.maxHp || 0) - Number(attacker.hp || 0));
        const amount = Math.min(available, Math.max(1, Math.round(damage * Number(instruction.value || 0))));
        if (amount > 0 && !preview) {
          attacker.hp += amount;
        }
        healing += amount;
      } else if (instruction.effect === 'multiply_target_stat' && allowedStats.has(instruction.stat)) {
        if (!preview) {
          const before = Math.max(1, Number(target[instruction.stat] || 1));
          target[instruction.stat] = Math.max(1, Math.round(before * Number(instruction.value || 1)));
          if (instruction.stat === 'maxHp') {
            target.hp = Math.min(target.hp, target.maxHp);
          }
        }
      } else {
        continue;
      }

      markUsed(attacker, instruction, target, preview);
      const message = messageFor(attacker, target, instruction);
      if (message) messages.push(message);
    }

    return { healing, messages };
  }

  return {
    prepareCreature,
    priorityFor,
    modifyDamage,
    afterDamage,
  };
});
