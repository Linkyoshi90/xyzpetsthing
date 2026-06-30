'use strict';

const { parentEntriesFor, occurrenceKind } = require('../assets/js/lineage.js');

const pets = new Map([
  [1, { id: 1 }],
  [2, { id: 2 }],
]);

const stallionParents = parentEntriesFor({
  motherId: 1,
  fatherId: null,
  parentageRecorded: true,
}, pets);
if (stallionParents.length !== 2
  || stallionParents[0].role !== 'Mother'
  || !stallionParents[1].stallion) {
  throw new Error('A recorded null father must produce a Breeding stallion branch.');
}

const founderParents = parentEntriesFor({
  motherId: null,
  fatherId: null,
  parentageRecorded: false,
}, pets);
if (founderParents.length !== 0) {
  throw new Error('A pet without a parentage row must not be labeled as stallion-bred.');
}

const knownParents = parentEntriesFor({
  motherId: 1,
  fatherId: 2,
  parentageRecorded: true,
}, pets);
if (knownParents.length !== 2 || knownParents.some((parent) => parent.stallion)) {
  throw new Error('A known father must remain an ordinary father branch.');
}

const seen = new Set([12]);
if (occurrenceKind(seen, 12) !== 'repeat' || occurrenceKind(seen, 13) !== 'primary') {
  throw new Error('Repeated ancestors must be classified as return branches.');
}

console.log('Lineage relationship test passed.');
