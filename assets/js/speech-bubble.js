(function () {
    function pickLine(dialogues, categories) {
        if (!dialogues) return null;
        for (const category of categories) {
            const entries = dialogues[category];
            if (Array.isArray(entries) && entries.length > 0) {
                return entries[Math.floor(Math.random() * entries.length)];
            }
        }
        return null;
    }

    function buildFallback(creature, location, isHome) {
        const creatureName = creature?.species_name || creature?.species || 'Your companion';
        const cityName = location?.city || location?.nation || 'this place';
        if (isHome) {
            return `${creatureName} looks thrilled to be back in ${cityName}.`;
        }
        return `${creatureName} takes in the sights of ${cityName}.`;
    }

    function preferenceToCategories(preferenceValue, isHome) {
        const baseOrder = isHome
            ? ['Love', 'Like', 'Dislike', 'Hate']
            : ['Like', 'Dislike', 'Hate', 'Love'];
        const categories = [];

        if (Number.isFinite(preferenceValue)) {
            let primary = 'Hate';
            if (preferenceValue >= 4) {
                primary = 'Love';
            } else if (preferenceValue >= 3) {
                primary = 'Like';
            } else if (preferenceValue === 2) {
                primary = 'Dislike';
            }
            categories.push(primary);
        } else if (isHome) {
            categories.push('Love', 'Like');
        }

        for (const cat of baseOrder) {
            if (!categories.includes(cat)) {
                categories.push(cat);
            }
        }

        return categories;
    }

    document.addEventListener('DOMContentLoaded', () => {
        const bubble = document.getElementById('pet-speech-bubble');
        const locationData = window.appLocation;
        const creature = window.appActiveCreature;
        if (!bubble || !locationData || !creature) return;

        const locationKey = locationData.key || null;
        const storage = window.localStorage;
        const lastKey = storage ? storage.getItem('lastVisitedCity') : null;
        if (locationKey && storage) {
            storage.setItem('lastVisitedCity', locationKey);
        }
        if (locationKey && lastKey && lastKey === locationKey) {
            return;
        }

        const dialogues = window.appSpeechDialogues || {};
        const creatureDialogues = dialogues[creature.species_name] || dialogues[creature.species] || null;
        const isHome = Boolean(
            creature?.region_name &&
            locationData?.nation &&
            creature.region_name.toLowerCase() === locationData.nation.toLowerCase()
        );

        const rawPreference = window.appPetLocationPreference;
        const numericPreference =
            typeof rawPreference === 'number'
                ? rawPreference
                : Number.parseInt(rawPreference, 10);

        const preferredCategories = preferenceToCategories(
            Number.isFinite(numericPreference) ? numericPreference : null,
            isHome
        );
        const chosen =
            pickLine(creatureDialogues, preferredCategories) || buildFallback(creature, locationData, isHome);

        bubble.textContent = chosen;
        bubble.hidden = false;
        bubble.classList.add('visible');

        window.setTimeout(() => {
            bubble.classList.remove('visible');
            bubble.setAttribute('hidden', 'hidden');
        }, 10000);
    });
})();