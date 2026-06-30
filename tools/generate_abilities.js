'use strict';

const fs = require('fs');
const path = require('path');

const ROOT = path.resolve(__dirname, '..');
const LORE_PATH = path.join(ROOT, 'data', 'creatures-lore.json');
const JSON_PATH = path.join(ROOT, 'data', 'abilities.json');
const SQL_UP_PATH = path.join(ROOT, 'sql', '20260629_pet_abilities_up.sql');
const SQL_DOWN_PATH = path.join(ROOT, 'sql', '20260629_pet_abilities_down.sql');

const lore = JSON.parse(fs.readFileSync(LORE_PATH, 'utf8'));
const creatures = Array.isArray(lore.creatures) ? lore.creatures : [];

const statMeta = {
  hp: { battleStat: 'maxHp', label: 'maximum HP' },
  atk: { battleStat: 'attack', label: 'attack' },
  def: { battleStat: 'defense', label: 'defense' },
  init: { battleStat: 'speed', label: 'speed' },
};

function keyFor(value) {
  return String(value)
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '_')
    .replace(/^_+|_+$/g, '');
}

function strongestStat(creature) {
  return Object.entries(creature.base_stats || {})
    .sort((left, right) => Number(right[1] || 0) - Number(left[1] || 0))[0]?.[0] || 'hp';
}

function instinctName(creature) {
  const summary = String(creature.lore?.summary || '').toLowerCase();
  if (summary.includes('waterbound')) return 'Tidewise Instinct';
  if (summary.includes('spirit-haunted')) return 'Veilborn Instinct';
  if (summary.includes('ancient survivor')) return 'Deep-Time Instinct';
  if (summary.includes('serpentine')) return 'Coiled Instinct';
  if (summary.includes('sky') || summary.includes('wing')) return 'Highwind Instinct';
  if (summary.includes('earthbound')) return 'Trailwise Instinct';
  return 'Native Instinct';
}

function genericAbilities(creature) {
  const species = creature.name;
  const stat = statMeta[strongestStat(creature)] || statMeta.hp;
  const affinity = creature.elemental_affinities?.[0] || '';
  const instinct = instinctName(creature);
  const first = {
    slot: 1,
    name: `${species}'s ${instinct}`,
    description: `${species} leans into the strongest part of its natural build, raising ${stat.label} by 12% when it enters battle.`,
    summary: `Raises ${stat.label} by 12% at battle start.`,
    instructions: [
      {
        key: 'native-stat',
        trigger: 'battle_start',
        effect: 'multiply_stat',
        stat: stat.battleStat,
        value: 1.12,
        message: `{self}'s ${instinct} raises its ${stat.label}.`,
      },
    ],
  };

  const second = affinity
    ? {
        slot: 2,
        name: `${species} ${affinity} Resonance`,
        description: `${species} channels its ${affinity} affinity with unusual precision, making matching attacks deal 18% more damage.`,
        summary: `${affinity} attacks deal 18% more damage.`,
        instructions: [
          {
            key: 'affinity-damage',
            trigger: 'outgoing_damage',
            effect: 'multiply_damage',
            value: 1.18,
            conditions: { element_names: [affinity] },
            message: `{ability} strengthens {self}'s ${affinity} attack.`,
          },
        ],
      }
    : {
        slot: 2,
        name: `${species} Fieldcraft`,
        description: `${species} turns practical survival habits into close-quarters technique, making contact attacks deal 15% more damage.`,
        summary: 'Contact attacks deal 15% more damage.',
        instructions: [
          {
            key: 'fieldcraft-contact',
            trigger: 'outgoing_damage',
            effect: 'multiply_damage',
            value: 1.15,
            conditions: { contact: true },
            message: `{ability} adds practiced force to {self}'s contact attack.`,
          },
        ],
      };

  return [first, second];
}

const bespoke = {
  Bullywug: [
    {
      slot: 1,
      name: 'Spring-Loaded Legs',
      description: 'Bullywug coils like a frog before the bell. Its speed rises by 20%, and its contact attacks gain one priority step.',
      summary: 'Raises speed by 20%; contact attacks gain +1 priority.',
      instructions: [
        { key: 'frog-speed', trigger: 'battle_start', effect: 'multiply_stat', stat: 'speed', value: 1.2, message: '{self} crouches low on Spring-Loaded Legs.' },
        { key: 'frog-priority', trigger: 'turn_order', effect: 'add_priority', value: 1, conditions: { contact: true } },
      ],
    },
    {
      slot: 2,
      name: 'Tongue Snare',
      description: 'Bullywug lashes out with a sticky tongue. Contact attacks deal 20% more damage and slow each target by 10% the first time they connect.',
      summary: 'Contact attacks deal 20% more damage and slow each target once.',
      instructions: [
        { key: 'tongue-impact', trigger: 'outgoing_damage', effect: 'multiply_damage', value: 1.2, conditions: { contact: true }, message: "{self}'s Tongue Snare hits with extra force." },
        { key: 'tongue-slow', trigger: 'after_damage', effect: 'multiply_target_stat', stat: 'speed', value: 0.9, once_per_target: true, conditions: { contact: true, damage_positive: true }, message: "Tongue Snare slows {target}." },
      ],
    },
  ],
  Death: [
    {
      slot: 1,
      name: 'Final Appointment',
      description: 'Death becomes inexorable when an opponent nears its end, dealing 40% more damage to targets at 35% HP or lower.',
      summary: 'Deals 40% more damage to targets at 35% HP or lower.',
      instructions: [
        { key: 'final-appointment', trigger: 'outgoing_damage', effect: 'multiply_damage', value: 1.4, conditions: { target_hp_at_or_below: 0.35 }, message: '{target} has reached its Final Appointment.' },
      ],
    },
    {
      slot: 2,
      name: "Death's Door",
      description: 'Once per battle, Death refuses a lethal blow and remains at 1 HP. The reprieve is brief, but absolute.',
      summary: 'Survives one lethal hit per battle at 1 HP.',
      instructions: [
        { key: 'deaths-door', trigger: 'lethal_damage', effect: 'survive_lethal', once_per_battle: true, message: "{self} waits at Death's Door and remains standing." },
      ],
    },
  ],
  Centaur: [
    {
      slot: 1,
      name: 'Full Gallop',
      description: 'Centaur commits its whole stride to the attack. Contact moves deal 20% more damage and gain one priority step.',
      summary: 'Contact attacks deal 20% more damage and gain +1 priority.',
      instructions: [
        { key: 'gallop-impact', trigger: 'outgoing_damage', effect: 'multiply_damage', value: 1.2, conditions: { contact: true }, message: '{self} crashes through at Full Gallop.' },
        { key: 'gallop-priority', trigger: 'turn_order', effect: 'add_priority', value: 1, conditions: { contact: true } },
      ],
    },
    {
      slot: 2,
      name: 'Four-Footed Balance',
      description: 'Centaur braces across four points of contact, reducing damage from contact attacks by 20%.',
      summary: 'Takes 20% less damage from contact attacks.',
      instructions: [
        { key: 'four-footed-balance', trigger: 'incoming_damage', effect: 'multiply_damage', value: 0.8, conditions: { contact: true }, message: "{self}'s Four-Footed Balance absorbs the collision." },
      ],
    },
  ],
  Kraken: [
    {
      slot: 1,
      name: 'Many-Armed Lock',
      description: "Kraken's limbs turn a close strike into a hold. Contact attacks deal 15% more damage and lower each target's attack by 10% once.",
      summary: "Contact attacks deal 15% more damage and lower each target's attack once.",
      instructions: [
        { key: 'many-arms-impact', trigger: 'outgoing_damage', effect: 'multiply_damage', value: 1.15, conditions: { contact: true }, message: '{self} closes a Many-Armed Lock.' },
        { key: 'many-arms-weaken', trigger: 'after_damage', effect: 'multiply_target_stat', stat: 'attack', value: 0.9, once_per_target: true, conditions: { contact: true, damage_positive: true }, message: '{target} loses leverage in the Many-Armed Lock.' },
      ],
    },
    {
      slot: 2,
      name: 'Abyssal Pressure',
      description: 'Kraken weaponizes deep-water pressure, making Kuro and Vai attacks deal 20% more damage.',
      summary: 'Kuro and Vai attacks deal 20% more damage.',
      instructions: [
        { key: 'abyssal-pressure', trigger: 'outgoing_damage', effect: 'multiply_damage', value: 1.2, conditions: { element_names: ['Kuro', 'Vai'] }, message: 'Abyssal Pressure bears down on {target}.' },
      ],
    },
  ],
  Lamia: [
    {
      slot: 1,
      name: 'Healing Venom',
      description: 'Lamia metabolizes the force of contact attacks, restoring HP equal to 15% of the damage dealt.',
      summary: 'Contact attacks heal 15% of damage dealt.',
      instructions: [
        { key: 'healing-venom', trigger: 'after_damage', effect: 'heal_damage_percent', value: 0.15, conditions: { contact: true, damage_positive: true }, message: "{self}'s Healing Venom restores its strength." },
      ],
    },
    {
      slot: 2,
      name: 'Sacred Coil',
      description: 'Lamia winds into a guarded ritual posture, raising defense by 18% when battle begins.',
      summary: 'Raises defense by 18% at battle start.',
      instructions: [
        { key: 'sacred-coil', trigger: 'battle_start', effect: 'multiply_stat', stat: 'defense', value: 1.18, message: '{self} settles into a Sacred Coil.' },
      ],
    },
  ],
  Pestilence: [
    {
      slot: 1,
      name: 'Lingering Malaise',
      description: "Pestilence leaves weakness behind after a damaging hit, lowering each target's attack by 12% once.",
      summary: "The first damaging hit on each target lowers its attack by 12%.",
      instructions: [
        { key: 'lingering-malaise', trigger: 'after_damage', effect: 'multiply_target_stat', stat: 'attack', value: 0.88, once_per_target: true, conditions: { damage_positive: true }, message: 'Lingering Malaise weakens {target}.' },
      ],
    },
    {
      slot: 2,
      name: 'Plague Wind',
      description: 'Pestilence spreads harm without touching its foe, making non-contact attacks deal 25% more damage.',
      summary: 'Non-contact attacks deal 25% more damage.',
      instructions: [
        { key: 'plague-wind', trigger: 'outgoing_damage', effect: 'multiply_damage', value: 1.25, conditions: { contact: false }, message: 'Plague Wind carries the attack into {target}.' },
      ],
    },
  ],
  Tengu: [
    {
      slot: 1,
      name: 'Mountain Gust',
      description: 'Tengu catches the mountain wind in its wings. Aer attacks deal 22% more damage and gain one priority step.',
      summary: 'Aer attacks deal 22% more damage and gain +1 priority.',
      instructions: [
        { key: 'mountain-gust-damage', trigger: 'outgoing_damage', effect: 'multiply_damage', value: 1.22, conditions: { element_names: ['Aer'] }, message: "{self}'s Mountain Gust drives the attack onward." },
        { key: 'mountain-gust-priority', trigger: 'turn_order', effect: 'add_priority', value: 1, conditions: { element_names: ['Aer'] } },
      ],
    },
    {
      slot: 2,
      name: 'Fan Parry',
      description: 'Tengu turns projectiles and sorcery aside with a war fan, reducing non-contact damage by 22%.',
      summary: 'Takes 22% less damage from non-contact attacks.',
      instructions: [
        { key: 'fan-parry', trigger: 'incoming_damage', effect: 'multiply_damage', value: 0.78, conditions: { contact: false }, message: "{self}'s Fan Parry turns part of the attack aside." },
      ],
    },
  ],
  'Sea Turtle': [
    {
      slot: 1,
      name: 'Ancient Carapace',
      description: "Sea Turtle's weathered shell raises defense by 25% when battle begins.",
      summary: 'Raises defense by 25% at battle start.',
      instructions: [
        { key: 'ancient-carapace', trigger: 'battle_start', effect: 'multiply_stat', stat: 'defense', value: 1.25, message: "{self}'s Ancient Carapace settles into place." },
      ],
    },
    {
      slot: 2,
      name: 'Shell Turn',
      description: 'Sea Turtle rolls with direct impacts, reducing contact damage by 25%.',
      summary: 'Takes 25% less damage from contact attacks.',
      instructions: [
        { key: 'shell-turn', trigger: 'incoming_damage', effect: 'multiply_damage', value: 0.75, conditions: { contact: true }, message: '{self} rolls with the blow using Shell Turn.' },
      ],
    },
  ],
  Thunderbird: [
    {
      slot: 1,
      name: 'Storm Conductor',
      description: 'Thunderbird gathers charge across its feathers, making Electra attacks deal 25% more damage.',
      summary: 'Electra attacks deal 25% more damage.',
      instructions: [
        { key: 'storm-conductor', trigger: 'outgoing_damage', effect: 'multiply_damage', value: 1.25, conditions: { element_names: ['Electra'] }, message: '{self} conducts the storm through its attack.' },
      ],
    },
    {
      slot: 2,
      name: 'Ground the Bolt',
      description: 'Thunderbird safely grounds hostile electricity, reducing incoming Electra damage by 50%.',
      summary: 'Takes 50% less damage from Electra attacks.',
      instructions: [
        { key: 'ground-bolt', trigger: 'incoming_damage', effect: 'multiply_damage', value: 0.5, conditions: { element_names: ['Electra'] }, message: '{self} grounds the hostile bolt.' },
      ],
    },
  ],
  'Will-o-Wisp': [
    {
      slot: 1,
      name: 'Unhanded Flame',
      description: "Will-o-Wisp has no solid body to seize, reducing damage from contact attacks by 35%.",
      summary: 'Takes 35% less damage from contact attacks.',
      instructions: [
        { key: 'unhanded-flame', trigger: 'incoming_damage', effect: 'multiply_damage', value: 0.65, conditions: { contact: true }, message: "The attack passes through {self}'s Unhanded Flame." },
      ],
    },
    {
      slot: 2,
      name: 'Lurelight',
      description: 'Will-o-Wisp draws foes toward a false light. Non-contact attacks deal 20% more damage and restore 10% of damage dealt.',
      summary: 'Non-contact attacks deal 20% more damage and heal 10% of damage dealt.',
      instructions: [
        { key: 'lurelight-damage', trigger: 'outgoing_damage', effect: 'multiply_damage', value: 1.2, conditions: { contact: false }, message: 'Lurelight draws {target} into the attack.' },
        { key: 'lurelight-heal', trigger: 'after_damage', effect: 'heal_damage_percent', value: 0.1, conditions: { contact: false, damage_positive: true }, message: '{self} feeds on the wandering light.' },
      ],
    },
  ],
};

const abilities = [];
for (const creature of creatures) {
  const definitions = bespoke[creature.name] || genericAbilities(creature);
  for (const definition of definitions) {
    abilities.push({
      id: (Number(creature.species_id) * 10) + Number(definition.slot),
      key: `${keyFor(creature.name)}_${keyFor(definition.name)}`,
      name: definition.name,
      species: {
        id: Number(creature.species_id),
        name: creature.name,
      },
      description: definition.description,
      battle: {
        summary: definition.summary,
        instructions: definition.instructions,
      },
    });
  }
}

const output = {
  schema_version: 1,
  generated_from: 'data/creatures-lore.json',
  instruction_schema: {
    triggers: ['battle_start', 'turn_order', 'outgoing_damage', 'incoming_damage', 'after_damage', 'lethal_damage'],
    effects: ['multiply_stat', 'add_priority', 'multiply_damage', 'heal_damage_percent', 'multiply_target_stat', 'survive_lethal'],
    condition_fields: ['move_categories', 'contact', 'element_names', 'move_keys', 'target_hp_at_or_below', 'self_hp_at_or_below', 'damage_positive'],
  },
  abilities,
};

fs.writeFileSync(JSON_PATH, `${JSON.stringify(output, null, 2)}\n`, 'utf8');

function sqlText(value) {
  return `'${String(value).replace(/'/g, "''")}'`;
}

const values = abilities.map((ability) => `(${ability.id}, ${ability.species.id}, ${sqlText(ability.name)}, ${sqlText(ability.description)})`);
const upSql = `-- Ability catalog and one ability slot per pet instance.\nCREATE TABLE IF NOT EXISTS pet_has_ability (\n  ability_id INT UNSIGNED NOT NULL,\n  species_id SMALLINT UNSIGNED NOT NULL,\n  ability_name VARCHAR(120) NOT NULL,\n  ability_desc TEXT NOT NULL,\n  PRIMARY KEY (ability_id),\n  KEY ix_pet_has_ability_species (species_id),\n  CONSTRAINT fk_pet_has_ability_species FOREIGN KEY (species_id)\n    REFERENCES pet_species(species_id) ON DELETE CASCADE\n) ENGINE=InnoDB;\n\nALTER TABLE pet_instances\n  ADD COLUMN ability_id INT UNSIGNED NULL AFTER initiative,\n  ADD KEY ix_pet_instances_ability (ability_id),\n  ADD CONSTRAINT fk_pet_instances_ability FOREIGN KEY (ability_id)\n    REFERENCES pet_has_ability(ability_id) ON DELETE SET NULL;\n\nINSERT INTO pet_has_ability (ability_id, species_id, ability_name, ability_desc) VALUES\n${values.join(',\n')}\nON DUPLICATE KEY UPDATE\n  species_id = VALUES(species_id),\n  ability_name = VALUES(ability_name),\n  ability_desc = VALUES(ability_desc);\n\n-- Give existing pets the first template for their species.\nUPDATE pet_instances pi\nJOIN (\n  SELECT species_id, MIN(ability_id) AS ability_id\n    FROM pet_has_ability\n   GROUP BY species_id\n) defaults ON defaults.species_id = pi.species_id\n   SET pi.ability_id = defaults.ability_id\n WHERE pi.ability_id IS NULL;\n`;

const downSql = `-- Roll back only the pet-ability migration.\nALTER TABLE pet_instances\n  DROP FOREIGN KEY fk_pet_instances_ability,\n  DROP INDEX ix_pet_instances_ability,\n  DROP COLUMN ability_id;\n\nDROP TABLE IF EXISTS pet_has_ability;\n`;

fs.writeFileSync(SQL_UP_PATH, upSql, 'utf8');
fs.writeFileSync(SQL_DOWN_PATH, downSql, 'utf8');

console.log(`Generated ${abilities.length} abilities for ${creatures.length} species.`);
