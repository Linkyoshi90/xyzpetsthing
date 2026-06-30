(() => {
  'use strict';

  const parentEntriesFor = (pet, pets) => {
    const entries = [];
    if (pet.motherId && pets.has(Number(pet.motherId))) {
      entries.push({ role: 'Mother', petId: Number(pet.motherId), stallion: false });
    }
    if (pet.fatherId && pets.has(Number(pet.fatherId))) {
      entries.push({ role: 'Father', petId: Number(pet.fatherId), stallion: false });
    } else if (!pet.fatherId && pet.parentageRecorded) {
      entries.push({ role: 'Father', petId: null, stallion: true });
    }
    return entries;
  };

  const occurrenceKind = (seenIds, petId) => (
    seenIds.has(Number(petId)) ? 'repeat' : 'primary'
  );

  if (typeof module === 'object' && module.exports) {
    module.exports = { parentEntriesFor, occurrenceKind };
  }
  if (typeof document === 'undefined') return;

  const treeElement = document.getElementById('lineage-tree');
  const dataElement = document.getElementById('lineage-data');
  if (!treeElement || !dataElement) return;

  let lineage;
  try {
    lineage = JSON.parse(dataElement.textContent);
  } catch (error) {
    treeElement.textContent = 'The family tree could not be loaded.';
    return;
  }

  const pets = new Map(
    (lineage.pets || []).map((pet) => [Number(pet.id), pet]),
  );
  const primaryCards = new Map();
  const returnLinks = [];

  const createMemberCard = (pet, role, repeat = false) => {
    const card = document.createElement('article');
    card.className = `lineage-member card glass${repeat ? ' lineage-member--repeat' : ''}`;

    const roleLabel = document.createElement('span');
    roleLabel.className = 'lineage-role';
    roleLabel.textContent = repeat ? `${role} · same ancestor` : role;

    const image = document.createElement('img');
    image.className = 'lineage-portrait';
    image.src = pet.image;
    image.alt = '';
    image.loading = 'lazy';

    const name = document.createElement('h2');
    name.textContent = pet.name;

    const details = document.createElement('p');
    details.className = 'mini';
    details.textContent = repeat
      ? 'This branch returns to the same family member.'
      : (pet.color ? `${pet.species} · ${pet.color}` : pet.species);

    card.append(roleLabel, image, name, details);
    return card;
  };

  const createStallionCard = () => {
    const card = document.createElement('article');
    card.className = 'lineage-member lineage-member--stallion card glass';

    const roleLabel = document.createElement('span');
    roleLabel.className = 'lineage-role';
    roleLabel.textContent = 'Father';

    const placeholder = document.createElement('div');
    placeholder.className = 'lineage-portrait lineage-stallion-portrait';
    placeholder.textContent = 'Daycare';
    placeholder.setAttribute('aria-hidden', 'true');

    const name = document.createElement('h2');
    name.textContent = 'Breeding stallion';

    const details = document.createElement('p');
    details.className = 'mini';
    details.textContent = 'No individual father was recorded.';

    card.append(roleLabel, placeholder, name, details);
    return card;
  };

  const createBranch = (petId, role) => {
    const numericId = Number(petId);
    const pet = pets.get(numericId);
    if (!pet) return null;

    const branch = document.createElement('li');
    branch.className = 'lineage-branch';

    if (occurrenceKind(primaryCards, numericId) === 'repeat') {
      const repeatCard = createMemberCard(pet, role, true);
      branch.classList.add('lineage-branch--return');
      branch.append(repeatCard);
      returnLinks.push({ from: repeatCard, to: primaryCards.get(numericId) });
      return branch;
    }

    const card = createMemberCard(pet, role);
    primaryCards.set(numericId, card);
    branch.append(card);

    const recordedParents = parentEntriesFor(pet, pets);
    if (recordedParents.length) {
      const parentList = document.createElement('ul');
      parentList.className = 'lineage-parents';
      recordedParents.forEach((parent) => {
        if (parent.stallion) {
          const stallionBranch = document.createElement('li');
          stallionBranch.className = 'lineage-branch lineage-branch--stallion';
          stallionBranch.append(createStallionCard());
          parentList.append(stallionBranch);
          return;
        }

        const parentBranch = createBranch(parent.petId, parent.role);
        if (parentBranch) parentList.append(parentBranch);
      });
      branch.append(parentList);
    }

    return branch;
  };

  const drawReturnLinks = () => {
    treeElement.querySelector('.lineage-return-lines')?.remove();
    if (!returnLinks.length) return;

    const namespace = 'http://www.w3.org/2000/svg';
    const svg = document.createElementNS(namespace, 'svg');
    svg.classList.add('lineage-return-lines');
    svg.setAttribute('aria-hidden', 'true');
    svg.setAttribute('width', String(treeElement.scrollWidth));
    svg.setAttribute('height', String(treeElement.scrollHeight));
    svg.setAttribute('viewBox', `0 0 ${treeElement.scrollWidth} ${treeElement.scrollHeight}`);

    const defs = document.createElementNS(namespace, 'defs');
    const marker = document.createElementNS(namespace, 'marker');
    marker.setAttribute('id', 'lineage-return-arrow');
    marker.setAttribute('markerWidth', '8');
    marker.setAttribute('markerHeight', '8');
    marker.setAttribute('refX', '7');
    marker.setAttribute('refY', '4');
    marker.setAttribute('orient', 'auto');
    marker.setAttribute('markerUnits', 'strokeWidth');
    const arrow = document.createElementNS(namespace, 'path');
    arrow.setAttribute('d', 'M 0 0 L 8 4 L 0 8 z');
    arrow.classList.add('lineage-return-arrow');
    marker.append(arrow);
    defs.append(marker);
    svg.append(defs);

    const treeRect = treeElement.getBoundingClientRect();
    returnLinks.forEach((link) => {
      if (!link.from?.isConnected || !link.to?.isConnected) return;
      const fromRect = link.from.getBoundingClientRect();
      const toRect = link.to.getBoundingClientRect();
      const startX = fromRect.left - treeRect.left + treeElement.scrollLeft + (fromRect.width / 2);
      const startY = fromRect.top - treeRect.top + treeElement.scrollTop;
      const endX = toRect.left - treeRect.left + treeElement.scrollLeft + (toRect.width / 2);
      const endY = toRect.top - treeRect.top + treeElement.scrollTop;
      const rise = Math.min(90, Math.max(40, Math.abs(endX - startX) * 0.18));
      const controlY = Math.max(8, Math.min(startY, endY) - rise);

      const path = document.createElementNS(namespace, 'path');
      path.classList.add('lineage-return-path');
      path.setAttribute(
        'd',
        `M ${startX} ${startY} C ${startX} ${controlY}, ${endX} ${controlY}, ${endX} ${endY}`,
      );
      path.setAttribute('marker-end', 'url(#lineage-return-arrow)');
      svg.append(path);
    });

    treeElement.prepend(svg);
  };

  const rootBranch = createBranch(lineage.rootId, 'Selected pet');
  if (!rootBranch) {
    treeElement.textContent = 'No lineage information is available for this pet.';
    return;
  }

  const rootList = document.createElement('ul');
  rootList.className = 'lineage-root';
  rootList.append(rootBranch);
  treeElement.replaceChildren(rootList);

  const scheduleReturnLinks = () => window.requestAnimationFrame(drawReturnLinks);
  scheduleReturnLinks();
  treeElement.querySelectorAll('img').forEach((image) => {
    if (!image.complete) image.addEventListener('load', scheduleReturnLinks, { once: true });
  });
  window.addEventListener('resize', scheduleReturnLinks);
})();
