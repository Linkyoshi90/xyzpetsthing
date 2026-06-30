(function () {

    function resolveChatActionBase() {
        const globalPath = window.appPaths && window.appPaths.chatAction;
        if (globalPath) {
            return globalPath;
        }
        return 'user_chat_action.php';
    }

    const POLL_INTERVAL_MS = 2000;
    const SCROLL_STICK_THRESHOLD = 60;

    function buildFetchUrl(friendId, afterId) {
        const base = resolveChatActionBase();
        try {
            // Resolve against the current document (not the origin) so a relative
            // endpoint stays inside the app folder on subfolder installs.
            const url = new URL(base, window.location.href);
            url.searchParams.set('action', 'fetch');
            url.searchParams.set('friend_id', friendId);
            if (afterId > 0) {
                url.searchParams.set('after_id', afterId);
            }
            return url.toString();
        } catch (error) {
            const separator = base.includes('?') ? '&' : '?';
            const afterParam = afterId > 0 ? `&after_id=${encodeURIComponent(afterId)}` : '';
            return `${base}${separator}action=fetch&friend_id=${encodeURIComponent(friendId)}${afterParam}`;
        }
    }

    function scrollHistory(historyEl) {
        if (!historyEl) return;
        historyEl.scrollTop = historyEl.scrollHeight;
    }

    function isNearBottom(historyEl) {
        if (!historyEl) return true;
        return historyEl.scrollHeight - historyEl.scrollTop - historyEl.clientHeight <= SCROLL_STICK_THRESHOLD;
    }

    function createMessageElement(data) {
        if (data.type === 'gift' && data.gift) {
            return createGiftElement(data);
        }

        const article = document.createElement('article');
        article.className = `chat-message ${data.direction}`;
        article.dataset.messageId = data.id;

        const body = document.createElement('p');
        body.className = 'chat-message-body';
        body.innerHTML = data.body;

        const time = document.createElement('span');
        time.className = 'chat-message-time';
        time.textContent = data.timestamp;

        article.append(body, time);
        return article;
    }

    // Mirrors gift_chat_card_html() in lib/gifts.php — keep the two in sync.
    function createGiftElement(data) {
        const g = data.gift || {};
        const article = document.createElement('article');
        article.className = `chat-message ${data.direction} chat-gift`;
        article.dataset.messageId = data.id;
        article.dataset.giftId = g.gift_id;
        article.dataset.giftState = g.state;

        const card = document.createElement('div');
        card.className = 'chat-gift__card';

        const icon = document.createElement('span');
        icon.className = 'chat-gift__icon';
        icon.setAttribute('aria-hidden', 'true');
        icon.textContent = '🎁';

        const info = document.createElement('div');
        info.className = 'chat-gift__info';
        const title = document.createElement('strong');
        title.className = 'chat-gift__title';
        title.textContent = g.item_name || 'Gift';
        const status = document.createElement('span');
        status.className = 'chat-gift__status';
        status.textContent = g.status_label || '';
        info.append(title, status);
        card.append(icon, info);

        if (g.can_act) {
            const actions = document.createElement('div');
            actions.className = 'chat-gift__actions';
            const accept = document.createElement('button');
            accept.type = 'button';
            accept.className = 'btn';
            accept.dataset.giftCardAction = 'accept_gift';
            accept.textContent = 'Accept';
            const decline = document.createElement('button');
            decline.type = 'button';
            decline.className = 'btn btn-ghost';
            decline.dataset.giftCardAction = 'decline_gift';
            decline.textContent = 'Decline';
            actions.append(accept, decline);
            card.append(actions);
        }

        const time = document.createElement('span');
        time.className = 'chat-message-time';
        time.textContent = data.timestamp;

        article.append(card, time);
        return article;
    }

    function showError(panel, message) {
        if (!panel) return;
        const error = panel.querySelector('.chat-error');
        if (!error) return;
        if (message) {
            error.textContent = message;
            error.hidden = false;
        } else {
            error.textContent = '';
            error.hidden = true;
        }
    }

    function sanitizeErrorMessage(message) {
        if (!message) return '';
        const div = document.createElement('div');
        div.innerHTML = message;
        return div.textContent || div.innerText || '';
    }

    function buildErrorMessage(prefix, detail) {
        const cleanDetail = sanitizeErrorMessage(detail);
        if (!cleanDetail) {
            return prefix;
        }
        return `${prefix} ${cleanDetail}`.trim();
    }

    async function readJsonResponse(response) {
        const text = await response.text();
        if (!text) {
            return { payload: null, raw: '' };
        }
        try {
            return { payload: JSON.parse(text), raw: text };
        } catch (error) {
            return { payload: null, raw: text };
        }
    }

    function createEmptyState(historyEl) {
        const empty = document.createElement('p');
        empty.className = 'chat-history-empty';
        empty.textContent = historyEl.dataset.empty || 'No messages yet.';
        return empty;
    }

    document.addEventListener('DOMContentLoaded', function () {
        const panel = document.querySelector('.chat-panel');
        if (!panel) return;

        const historyEl = document.getElementById('chat-history');
        const form = document.getElementById('chat-form');
        const friendInput = document.getElementById('chat-friend-id');
        const textarea = document.getElementById('chat-input');
        const headerTitle = panel.querySelector('.chat-header h2');
        const friendButtons = Array.from(document.querySelectorAll('.chat-friend-btn'));

        // Tracks which messages are already on screen so polling never double-renders,
        // and the highest id seen so each poll only asks for messages after it.
        const conversation = { renderedIds: new Set(), lastMessageId: 0 };
        let pollTimer = null;
        let polling = false;

        function trackMessageId(id) {
            const numericId = Number(id);
            if (!Number.isFinite(numericId)) return;
            conversation.renderedIds.add(numericId);
            if (numericId > conversation.lastMessageId) {
                conversation.lastMessageId = numericId;
            }
        }

        function activeFriendId() {
            return panel.dataset.activeFriend || '';
        }

        function appendNewMessages(messages, forceScroll) {
            if (!historyEl || !Array.isArray(messages) || messages.length === 0) return 0;
            const stick = forceScroll || isNearBottom(historyEl);
            let appended = 0;
            messages.forEach((msg) => {
                const id = Number(msg.id);
                if (!Number.isFinite(id) || conversation.renderedIds.has(id)) return;
                const emptyState = historyEl.querySelector('.chat-history-empty');
                if (emptyState) emptyState.remove();
                historyEl.append(createMessageElement(msg));
                trackMessageId(id);
                appended += 1;
            });
            if (appended > 0 && stick) scrollHistory(historyEl);
            return appended;
        }

        function renderConversation(messages) {
            if (!historyEl) return;
            historyEl.innerHTML = '';
            conversation.renderedIds.clear();
            conversation.lastMessageId = 0;
            if (!messages || messages.length === 0) {
                historyEl.append(createEmptyState(historyEl));
                return;
            }
            const fragment = document.createDocumentFragment();
            messages.forEach((msg) => {
                fragment.append(createMessageElement(msg));
                trackMessageId(msg.id);
            });
            historyEl.append(fragment);
            scrollHistory(historyEl);
        }

        // Seed dedup state from whatever the server already rendered into the page.
        function seedFromDom() {
            conversation.renderedIds.clear();
            conversation.lastMessageId = 0;
            if (!historyEl) return;
            historyEl.querySelectorAll('.chat-message[data-message-id]').forEach((el) => {
                trackMessageId(el.dataset.messageId);
            });
        }

        async function pollOnce() {
            const friendId = activeFriendId();
            if (!friendId || polling || document.hidden || !historyEl) return;
            polling = true;
            try {
                const response = await fetch(buildFetchUrl(friendId, conversation.lastMessageId), {
                    credentials: 'same-origin',
                });
                const { payload } = await readJsonResponse(response);
                if (payload && payload.ok) {
                    appendNewMessages(payload.messages);
                }
            } catch (error) {
                // Transient poll failures are ignored; the next tick retries.
            } finally {
                polling = false;
            }
        }

        function stopPolling() {
            if (pollTimer !== null) {
                window.clearInterval(pollTimer);
                pollTimer = null;
            }
        }

        function startPolling() {
            stopPolling();
            if (!activeFriendId() || !historyEl) return;
            pollTimer = window.setInterval(pollOnce, POLL_INTERVAL_MS);
        }

        seedFromDom();
        scrollHistory(historyEl);
        startPolling();

        // Don't hammer the server for a tab nobody is looking at; catch up on return.
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                stopPolling();
            } else {
                pollOnce();
                startPolling();
            }
        });

        friendButtons.forEach((btn) => {
            btn.addEventListener('click', (event) => {
                event.preventDefault();
                const friendId = btn.dataset.friendId;
                if (!friendId || !historyEl) return;
                if (panel.dataset.activeFriend === friendId) {
                    return;
                }

                friendButtons.forEach((b) => b.classList.remove('active'));
                btn.classList.add('active');
                panel.dataset.activeFriend = friendId;
                if (friendInput) {
                    friendInput.value = friendId;
                }
                if (headerTitle) {
                    headerTitle.textContent = `Chatting with ${btn.dataset.friendName || ''}`;
                }
                showError(panel, '');
                stopPolling();

                fetch(buildFetchUrl(friendId), {
                    credentials: 'same-origin',
                })
                    .then(async (response) => {
                        const { payload, raw } = await readJsonResponse(response);
                        if (!payload || !payload.ok) {
                            const detail = (payload && payload.error) || raw || `Server responded with status ${response.status}`;
                            throw new Error(detail);
                        }
                        renderConversation(payload.messages);
                    })
                    .catch((error) => {
                        showError(panel, buildErrorMessage('Unable to load messages.', error.message));
                    })
                    .finally(() => {
                        startPolling();
                    });
            });
        });

        if (form && textarea && friendInput) {
            textarea.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' && !event.shiftKey) {
                    event.preventDefault();
                    if (typeof form.requestSubmit === 'function') {
                        form.requestSubmit();
                    } else {
                        form.dispatchEvent(new Event('submit', { cancelable: true }));
                    }
                }
            });

            form.addEventListener('submit', (event) => {
                event.preventDefault();
                const friendId = friendInput.value;
                if (!friendId) {
                    showError(panel, 'Please select a friend to chat with.');
                    return;
                }
                const message = textarea.value.trim();
                if (!message) {
                    return;
                }
                // Bare "/gift" opens the item picker instead of sending literal text.
                if (/^\/gift\s*$/i.test(message)) {
                    openGiftModal();
                    return;
                }
                showError(panel, '');
                const formData = new FormData(form);
                // NB: read the attribute, not form.action — the hidden
                // <input name="action"> shadows the form's action property
                // (DOM clobbering), so form.action returns that element.
                const actionUrl = form.getAttribute('action') || resolveChatActionBase();
                fetch(actionUrl, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin',
                })
                    .then(async (response) => {
                        const { payload, raw } = await readJsonResponse(response);
                        if (!payload || !payload.ok) {
                            const detail = (payload && payload.error) || raw || `Server responded with status ${response.status}`;
                            throw new Error(detail);
                        }
                        textarea.value = '';
                        appendNewMessages([payload.message], true);
                    })
                    .catch((error) => {
                        showError(panel, buildErrorMessage('Failed to send message.', error.message));
                    });
            });
        }

        // ---- Gift picker + gift-card actions ----
        const giftModal = document.getElementById('gift-modal');
        const giftListEl = giftModal ? giftModal.querySelector('[data-gift-list]') : null;
        const giftStatusEl = giftModal ? giftModal.querySelector('[data-gift-status]') : null;
        const giftSendBtn = giftModal ? giftModal.querySelector('[data-gift-send]') : null;
        const giftNameEl = giftModal ? giftModal.querySelector('[data-gift-friend-name]') : null;
        let giftSelectedItemId = 0;

        function activeFriendName() {
            const activeBtn = document.querySelector('.chat-friend-btn.active');
            if (activeBtn && activeBtn.dataset.friendName) return activeBtn.dataset.friendName;
            return (giftNameEl && giftNameEl.textContent) || 'your friend';
        }

        function setGiftStatus(message, isError) {
            if (!giftStatusEl) return;
            giftStatusEl.textContent = message || '';
            giftStatusEl.hidden = !message;
            giftStatusEl.classList.toggle('is-error', !!isError);
        }

        function closeGiftModal() {
            if (!giftModal) return;
            giftModal.hidden = true;
            giftModal.setAttribute('aria-hidden', 'true');
        }

        function renderGiftItems(items) {
            if (!giftListEl) return;
            if (!items.length) {
                giftListEl.innerHTML = '<p class="muted">You have no tradable items to gift.</p>';
                return;
            }
            giftListEl.innerHTML = '';
            items.forEach((item) => {
                const row = document.createElement('button');
                row.type = 'button';
                row.className = 'gift-item';
                row.dataset.itemId = String(item.item_id);
                const name = document.createElement('span');
                name.className = 'gift-item__name';
                name.textContent = item.item_name;
                const meta = document.createElement('span');
                meta.className = 'gift-item__meta muted';
                meta.textContent = (item.category_name ? item.category_name + ' · ' : '') + 'x' + item.quantity;
                row.append(name, meta);
                row.addEventListener('click', () => {
                    giftSelectedItemId = item.item_id;
                    giftListEl.querySelectorAll('.gift-item').forEach((el) => el.classList.remove('is-selected'));
                    row.classList.add('is-selected');
                    if (giftSendBtn) giftSendBtn.disabled = false;
                });
                giftListEl.appendChild(row);
            });
        }

        function loadGiftInventory() {
            if (!giftListEl) return;
            fetch('gift_action.php?action=inventory', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                .then((r) => r.json())
                .then((data) => {
                    if (!data.ok) throw new Error(data.error || 'Could not load your bag.');
                    renderGiftItems(data.items || []);
                })
                .catch((err) => {
                    giftListEl.innerHTML = '';
                    setGiftStatus(err.message || 'Could not load your bag.', true);
                });
        }

        function openGiftModal() {
            if (!giftModal || !activeFriendId()) return;
            giftSelectedItemId = 0;
            if (giftNameEl) giftNameEl.textContent = activeFriendName();
            if (giftSendBtn) giftSendBtn.disabled = true;
            setGiftStatus('', false);
            giftListEl.innerHTML = '<p class="muted">Loading your bag…</p>';
            giftModal.hidden = false;
            giftModal.setAttribute('aria-hidden', 'false');
            loadGiftInventory();
        }

        // Picking an item simply composes the "/gift <id>" command and submits it
        // through the normal chat pipeline, so the server-side gift path is the only
        // one that ever runs.
        function confirmGiftSelection() {
            if (!giftSelectedItemId || !textarea || !form) return;
            textarea.value = '/gift ' + giftSelectedItemId;
            closeGiftModal();
            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
            } else {
                form.dispatchEvent(new Event('submit', { cancelable: true }));
            }
        }

        if (giftModal) {
            document.querySelectorAll('[data-gift-open]').forEach((btn) => {
                btn.addEventListener('click', openGiftModal);
            });
            giftModal.querySelectorAll('[data-gift-close]').forEach((el) => {
                el.addEventListener('click', closeGiftModal);
            });
            if (giftSendBtn) giftSendBtn.addEventListener('click', confirmGiftSelection);
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !giftModal.hidden) closeGiftModal();
            });
        }

        function resolveNotificationAction() {
            const base = (window.appPaths && window.appPaths.notificationAction) || 'notification_action.php';
            try {
                return new URL(base, window.location.href).toString();
            } catch (error) {
                return base;
            }
        }

        // Accept / Decline straight from a gift card in the thread.
        if (historyEl) {
            historyEl.addEventListener('click', (event) => {
                const btn = event.target.closest('[data-gift-card-action]');
                if (!btn) return;
                const card = btn.closest('.chat-gift');
                if (!card || card.dataset.giftBusy === '1') return;
                const giftId = card.dataset.giftId;
                const action = btn.getAttribute('data-gift-card-action');
                if (!giftId || (action !== 'accept_gift' && action !== 'decline_gift')) return;

                card.dataset.giftBusy = '1';
                card.querySelectorAll('button').forEach((b) => { b.disabled = true; });

                const body = new URLSearchParams({ action, gift_id: giftId });
                fetch(resolveNotificationAction(), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json' },
                    body: body.toString(),
                    credentials: 'same-origin',
                })
                    .then((r) => r.json())
                    .then((data) => {
                        if (!data.ok) throw new Error(data.error || 'The gift could not be handled.');
                        const accepted = action === 'accept_gift';
                        card.dataset.giftState = accepted ? '1' : '2';
                        const statusEl = card.querySelector('.chat-gift__status');
                        if (statusEl) statusEl.textContent = accepted ? 'Accepted ✓' : 'Declined ✗';
                        const actionsEl = card.querySelector('.chat-gift__actions');
                        if (actionsEl) actionsEl.remove();
                        // Best-effort sync of the header bell badge; full reconcile on next load.
                        const badge = document.querySelector('.notifications-count');
                        if (badge && typeof data.count === 'number') {
                            if (data.count > 0) {
                                badge.textContent = String(data.count);
                            } else {
                                badge.remove();
                            }
                        }
                    })
                    .catch((err) => {
                        card.dataset.giftBusy = '';
                        card.querySelectorAll('button').forEach((b) => { b.disabled = false; });
                        showError(panel, buildErrorMessage('Gift action failed.', err.message));
                    });
            });
        }
    });
})();