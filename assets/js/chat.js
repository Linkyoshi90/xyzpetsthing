
(function () {
    function scrollHistory(historyEl) {
        if (!historyEl) return;
        historyEl.scrollTop = historyEl.scrollHeight;
    }

    function createMessageElement(data) {
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

    function renderMessages(historyEl, messages) {
        if (!historyEl) return;
        historyEl.innerHTML = '';
        if (!messages || messages.length === 0) {
            const emptyText = historyEl.dataset.empty || 'No messages yet.';
            const empty = document.createElement('p');
            empty.className = 'chat-history-empty';
            empty.textContent = emptyText;
            historyEl.append(empty);
            return;
        }
        const fragment = document.createDocumentFragment();
        messages.forEach((msg) => {
            fragment.append(createMessageElement(msg));
        });
        historyEl.append(fragment);
        scrollHistory(historyEl);
    }

    function appendMessage(historyEl, data) {
        if (!historyEl) return;
        const emptyState = historyEl.querySelector('.chat-history-empty');
        if (emptyState) {
            emptyState.remove();
        }
        historyEl.append(createMessageElement(data));
        scrollHistory(historyEl);
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

        scrollHistory(historyEl);

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

                fetch(`user_chat_action.php?action=fetch&friend_id=${encodeURIComponent(friendId)}`, {
                    credentials: 'same-origin',
                })
                    .then(async (response) => {
                        const { payload, raw } = await readJsonResponse(response);
                        if (!payload || !payload.ok) {
                            const detail = (payload && payload.error) || raw || `Server responded with status ${response.status}`;
                            throw new Error(detail);
                        }
                        renderMessages(historyEl, payload.messages);
                    })
                    .catch((error) => {
                        showError(panel, buildErrorMessage('Unable to load messages.', error.message));
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
                showError(panel, '');
                const formData = new FormData(form);
                fetch(form.action, {
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
                        appendMessage(historyEl, payload.message);
                    })
                    .catch((error) => {
                        showError(panel, buildErrorMessage('Failed to send message.', error.message));
                    });
            });
        }
    });
})();