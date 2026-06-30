(() => {
    document.querySelectorAll('[data-unfriend-form]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const friendName = form.dataset.friendName || 'this user';
            if (!window.confirm(`Remove ${friendName} from your friends list?`)) {
                event.preventDefault();
                return;
            }

            const confirmedInput = form.querySelector('[data-unfriend-confirmed]');
            if (confirmedInput) {
                confirmedInput.value = '1';
            }
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'Removing...';
            }
        });
    });

    const modal = document.getElementById('friend-pets-modal');
    const dialog = modal ? modal.querySelector('[role="dialog"]') : null;
    const ownerEl = modal ? modal.querySelector('[data-friend-pets-owner]') : null;
    const statusEl = modal ? modal.querySelector('[data-friend-pets-status]') : null;
    const gridEl = modal ? modal.querySelector('[data-friend-pets-grid]') : null;
    const closeButton = modal ? modal.querySelector('.gift-modal__close') : null;
    const triggers = document.querySelectorAll('[data-friend-pets]');

    if (!modal || !dialog || !ownerEl || !statusEl || !gridEl || !closeButton || !triggers.length) {
        return;
    }

    const cache = new Map();
    let activeTrigger = null;
    let requestController = null;

    const showStatus = (message, isError = false) => {
        statusEl.textContent = message;
        statusEl.classList.toggle('is-error', isError);
        statusEl.hidden = false;
        gridEl.hidden = true;
    };

    const renderPets = (data) => {
        gridEl.replaceChildren();
        ownerEl.textContent = data.friend.username;

        if (!data.pets.length) {
            showStatus(`${data.friend.username} doesn't have any pets yet.`);
            return;
        }

        data.pets.forEach((pet) => {
            const card = document.createElement('article');
            card.className = 'card glass friend-pets-modal__pet';

            const thumbnail = document.createElement('div');
            thumbnail.className = 'friend-pets-modal__thumbnail';
            const imageTemplate = document.createElement('template');
            imageTemplate.innerHTML = pet.thumbnail_html;
            thumbnail.append(imageTemplate.content);

            const name = document.createElement('h3');
            name.textContent = pet.name;

            const details = document.createElement('p');
            details.className = 'muted';
            details.textContent = pet.details;

            card.append(thumbnail, name, details);
            gridEl.append(card);
        });

        statusEl.hidden = true;
        gridEl.hidden = false;
    };

    const closeModal = () => {
        if (modal.hidden) return;
        requestController?.abort();
        requestController = null;
        modal.hidden = true;
        modal.setAttribute('aria-hidden', 'true');
        activeTrigger?.focus();
        activeTrigger = null;
    };

    const openModal = async (trigger) => {
        const friendId = trigger.dataset.friendId;
        const friendName = trigger.dataset.friendName || 'Friend';
        if (!friendId) return;

        activeTrigger = trigger;
        ownerEl.textContent = friendName;
        gridEl.replaceChildren();
        showStatus('Loading pets…');
        modal.hidden = false;
        modal.setAttribute('aria-hidden', 'false');
        closeButton.focus();

        if (cache.has(friendId)) {
            renderPets(cache.get(friendId));
            return;
        }

        requestController?.abort();
        const controller = new AbortController();
        requestController = controller;

        try {
            const response = await fetch(`friend_pets_action.php?friend_id=${encodeURIComponent(friendId)}`, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
                signal: controller.signal,
            });
            const data = await response.json();
            if (!response.ok || !data.ok) {
                throw new Error(data.error || 'Could not load these pets.');
            }
            cache.set(friendId, data);
            renderPets(data);
        } catch (error) {
            if (error.name !== 'AbortError') {
                showStatus(error.message || 'Could not load these pets.', true);
            }
        } finally {
            if (requestController === controller) {
                requestController = null;
            }
        }
    };

    triggers.forEach((trigger) => {
        trigger.addEventListener('click', () => openModal(trigger));
    });

    modal.querySelectorAll('[data-friend-pets-close]').forEach((element) => {
        element.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', (event) => {
        if (!modal.hidden && event.key === 'Escape') {
            event.preventDefault();
            closeModal();
        }
    });

    dialog.addEventListener('keydown', (event) => {
        if (event.key !== 'Tab') return;
        event.preventDefault();
        closeButton.focus();
    });
})();
