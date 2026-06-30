(function () {
    const userToggle = document.getElementById('user-menu-toggle');
    const userMenu = document.getElementById('user-menu');
    const notificationsToggle = document.getElementById('notifications-toggle');
    const notificationsPanel = document.getElementById('notifications-panel');
    const notificationsClear = document.getElementById('notifications-clear');
    const notificationsBody = notificationsPanel ? notificationsPanel.querySelector('.notifications-panel__body') : null;
    const notificationsStatus = notificationsPanel ? notificationsPanel.querySelector('.notifications-panel__status') : null;

    const positionNotifications = () => {
        if (!notificationsPanel || !notificationsToggle) return;
        const dock = notificationsToggle.closest('.notifications-dock');
        const dockRect = dock ? dock.getBoundingClientRect() : notificationsToggle.getBoundingClientRect();
        const panelRight = Math.max(12, window.innerWidth - dockRect.right);
        const panelBottom = Math.max(78, window.innerHeight - dockRect.top + 12);
        notificationsPanel.style.setProperty('--notifications-right', `${Math.ceil(panelRight)}px`);
        notificationsPanel.style.setProperty('--notifications-bottom', `${Math.ceil(panelBottom)}px`);
    };

    const closeUserMenu = () => {
        if (userMenu) {
            userMenu.classList.remove('show');
        }
    };

    const closeNotifications = () => {
        if (!notificationsToggle || !notificationsPanel) return;
        notificationsToggle.classList.remove('is-open');
        notificationsToggle.setAttribute('aria-expanded', 'false');
        notificationsToggle.setAttribute('aria-label', 'Open notifications');
        notificationsPanel.classList.remove('is-open');
        notificationsPanel.setAttribute('aria-hidden', 'true');
    };

    const setNotificationCount = (count) => {
        if (!notificationsToggle || !notificationsStatus) return;
        const safeCount = Math.max(0, Number.parseInt(count, 10) || 0);
        notificationsStatus.textContent = safeCount > 0 ? `${safeCount} logged` : 'All clear';
        notificationsStatus.classList.toggle('notifications-panel__status--active', safeCount > 0);

        let badge = notificationsToggle.querySelector('.notifications-count');
        if (safeCount > 0) {
            if (!badge) {
                badge = document.createElement('span');
                badge.className = 'notifications-count';
                badge.setAttribute('aria-hidden', 'true');
                notificationsToggle.appendChild(badge);
            }
            badge.textContent = String(safeCount);
        } else if (badge) {
            badge.remove();
        }
    };

    const updateNotificationsClearState = () => {
        if (!notificationsClear || !notificationsBody) return;
        notificationsClear.disabled = !notificationsBody.querySelector('[data-notification-dismiss]');
    };

    const ensureNotificationEmptyState = () => {
        if (!notificationsBody || notificationsBody.querySelector('.notification-item:not(.notification-item--empty)')) {
            updateNotificationsClearState();
            return;
        }
        if (notificationsBody.querySelector('.notification-item--empty')) {
            updateNotificationsClearState();
            return;
        }
        const item = document.createElement('article');
        item.className = 'notification-item notification-item--empty';
        item.setAttribute('role', 'listitem');
        item.innerHTML = [
            '<span class="notification-item__icon" aria-hidden="true">◌</span>',
            '<div class="notification-item__copy">',
            '<strong>No notifications yet</strong>',
            '<small>The harbor wire is quiet for now.</small>',
            '</div>'
        ].join('');
        notificationsBody.appendChild(item);
        updateNotificationsClearState();
    };

    const removeNotificationItem = (item, count) => {
        item.classList.add('is-dismissing');
        window.setTimeout(() => {
            item.remove();
            setNotificationCount(count);
            ensureNotificationEmptyState();
            updateNotificationsClearState();
        }, 180);
    };

    const findNotificationItemById = (id) => {
        if (!notificationsBody || !id) return null;
        return Array.from(notificationsBody.querySelectorAll('[data-notification-id]')).find((item) => (
            item.getAttribute('data-notification-id') === id
        )) || null;
    };

    const removeNotificationItemById = (id, count) => {
        const item = findNotificationItemById(id);
        if (item) {
            removeNotificationItem(item, count);
            return;
        }
        setNotificationCount(count);
        ensureNotificationEmptyState();
    };

    const postNotificationAction = async (body, fallbackMessage) => {
        const actionPath = (window.appPaths && window.appPaths.notificationAction) || 'notification_action.php';
        const response = await fetch(actionPath, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'Accept': 'application/json'
            },
            body
        });
        const data = await response.json();
        if (!response.ok || !data.ok) {
            throw new Error(data.error || fallbackMessage);
        }
        return data;
    };

    const sendDismissNotification = (id) => postNotificationAction(new URLSearchParams({
        action: 'dismiss',
        id
    }), 'Notification could not be dismissed.');

    const sendDismissAllNotifications = () => postNotificationAction(new URLSearchParams({
        action: 'dismiss_all'
    }), 'Notifications could not be cleared.');

    window.appDismissNotification = async (id) => {
        const notificationId = String(id || '');
        if (!notificationId) return null;
        const data = await sendDismissNotification(notificationId);
        removeNotificationItemById(notificationId, data.count);
        return data;
    };

    const dismissNotification = async (button) => {
        if (!button || button.disabled) return;
        const item = button.closest('.notification-item');
        const id = button.getAttribute('data-notification-dismiss');
        if (!item || !id) return;

        button.disabled = true;
        try {
            const data = await sendDismissNotification(id);
            removeNotificationItem(item, data.count);
        } catch (error) {
            button.disabled = false;
            if (typeof window.reportAppError === 'function') {
                window.reportAppError(error && error.message ? error.message : 'Notification could not be dismissed.');
            }
        }
    };

    const dismissAllNotifications = async () => {
        if (!notificationsClear || notificationsClear.disabled || !notificationsBody) return;
        const items = Array.from(notificationsBody.querySelectorAll('.notification-item')).filter((item) => (
            item.querySelector('[data-notification-dismiss]')
        ));
        if (!items.length) {
            updateNotificationsClearState();
            return;
        }

        notificationsClear.disabled = true;
        try {
            const data = await sendDismissAllNotifications();
            items.forEach((item) => {
                item.classList.add('is-dismissing');
            });
            window.setTimeout(() => {
                items.forEach((item) => {
                    item.remove();
                });
                setNotificationCount(data.count);
                ensureNotificationEmptyState();
                updateNotificationsClearState();
            }, 180);
        } catch (error) {
            notificationsClear.disabled = false;
            updateNotificationsClearState();
            if (typeof window.reportAppError === 'function') {
                window.reportAppError(error && error.message ? error.message : 'Notifications could not be cleared.');
            }
        }
    };

    const runNotificationAction = async (button) => {
        if (!button || button.disabled) return;
        const item = button.closest('.notification-item');
        const action = button.getAttribute('data-notification-action');
        if (!item || !action) return;

        const body = new URLSearchParams({ action });
        if (action === 'accept_friend_request') {
            const requestId = button.getAttribute('data-friend-request-id');
            if (!requestId) return;
            body.set('request_id', requestId);
        } else if (action === 'accept_gift' || action === 'decline_gift') {
            const giftId = button.getAttribute('data-gift-id');
            if (!giftId) return;
            body.set('gift_id', giftId);
        }

        const controls = item.querySelectorAll('button');
        controls.forEach((control) => {
            control.disabled = true;
        });

        try {
            const data = await postNotificationAction(body, 'Notification action could not be completed.');
            removeNotificationItem(item, data.count);
        } catch (error) {
            controls.forEach((control) => {
                control.disabled = false;
            });
            if (typeof window.reportAppError === 'function') {
                window.reportAppError(error && error.message ? error.message : 'Notification action could not be completed.');
            }
        }
    };

    const followDismissOnClickLink = async (link) => {
        if (!link || link.getAttribute('aria-disabled') === 'true') return;
        const id = link.getAttribute('data-notification-dismiss-on-click');
        const href = link.href;
        if (!id || !href) return;

        link.setAttribute('aria-disabled', 'true');
        try {
            await window.appDismissNotification(id);
            closeNotifications();
            window.location.assign(href);
        } catch (error) {
            link.removeAttribute('aria-disabled');
            if (typeof window.reportAppError === 'function') {
                window.reportAppError(error && error.message ? error.message : 'Notification could not be accepted.');
            }
        }
    };

    if (userToggle && userMenu) {
        userToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            const willOpen = !userMenu.classList.contains('show');
            closeNotifications();
            userMenu.classList.toggle('show', willOpen);
        });
    }

    if (notificationsToggle && notificationsPanel) {
        notificationsToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            const willOpen = !notificationsPanel.classList.contains('is-open');
            closeUserMenu();
            if (willOpen) {
                positionNotifications();
            }
            notificationsToggle.classList.toggle('is-open', willOpen);
            notificationsToggle.setAttribute('aria-expanded', String(willOpen));
            notificationsToggle.setAttribute('aria-label', willOpen ? 'Close notifications' : 'Open notifications');
            notificationsPanel.classList.toggle('is-open', willOpen);
            notificationsPanel.setAttribute('aria-hidden', String(!willOpen));
        });

        notificationsPanel.addEventListener('click', (e) => {
            e.stopPropagation();
            const dismissButton = e.target.closest('[data-notification-dismiss]');
            if (dismissButton) {
                e.preventDefault();
                dismissNotification(dismissButton);
                return;
            }

            const actionButton = e.target.closest('[data-notification-action]');
            if (actionButton) {
                e.preventDefault();
                runNotificationAction(actionButton);
                return;
            }

            const dismissOnClickLink = e.target.closest('[data-notification-dismiss-on-click]');
            if (dismissOnClickLink) {
                e.preventDefault();
                followDismissOnClickLink(dismissOnClickLink);
            }
        });
    }

    if (notificationsClear) {
        notificationsClear.addEventListener('click', (e) => {
            e.stopPropagation();
            dismissAllNotifications();
        });
    }

    updateNotificationsClearState();

    document.addEventListener('click', (e) => {
        if (userMenu && userToggle && !userMenu.contains(e.target) && !userToggle.contains(e.target)) {
            closeUserMenu();
        }
        if (
            notificationsPanel &&
            notificationsToggle &&
            !notificationsPanel.contains(e.target) &&
            !notificationsToggle.contains(e.target)
        ) {
            closeNotifications();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        closeUserMenu();
        closeNotifications();
    });

    window.addEventListener('resize', () => {
        if (notificationsPanel && notificationsPanel.classList.contains('is-open')) {
            positionNotifications();
        }
    });
})();
