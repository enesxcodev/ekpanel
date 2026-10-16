import { Toast } from '@tabler/core';

const notificationTypes = Object.freeze({
    success: { title: 'Başarılı', color: 'success', autohide: true },
    error: { title: 'Hata', color: 'danger', autohide: false },
    warning: { title: 'Uyarı', color: 'warning', autohide: true },
    info: { title: 'Bilgi', color: 'info', autohide: true },
});

let initialized = false;

export function notify({ type = 'info', message } = {}) {
    const container = document.getElementById('panel-notifications');

    if (!container || typeof message !== 'string' || !message.trim()) {
        return;
    }

    const settings = Object.hasOwn(notificationTypes, type)
        ? notificationTypes[type]
        : notificationTypes.info;
    const element = document.createElement('div');
    element.className = 'toast';
    element.setAttribute('role', type === 'error' ? 'alert' : 'status');
    element.setAttribute('aria-live', type === 'error' ? 'assertive' : 'polite');
    element.setAttribute('aria-atomic', 'true');

    const header = document.createElement('div');
    header.className = 'toast-header';

    const indicator = document.createElement('span');
    indicator.className = `status-dot bg-${settings.color} me-2`;
    indicator.setAttribute('aria-hidden', 'true');

    const title = document.createElement('strong');
    title.className = 'me-auto';
    title.textContent = settings.title;

    const closeButton = document.createElement('button');
    closeButton.type = 'button';
    closeButton.className = 'btn-close';
    closeButton.setAttribute('aria-label', 'Bildirimi kapat');

    const body = document.createElement('div');
    body.className = 'toast-body';
    body.textContent = message;

    header.append(indicator, title, closeButton);
    element.append(header, body);
    container.append(element);

    const toast = new Toast(element, { delay: 5000, autohide: settings.autohide });

    closeButton.addEventListener('click', () => toast.hide());
    element.addEventListener('hidden.bs.toast', () => {
        toast.dispose();
        element.remove();
    }, { once: true });

    toast.show();
}

export function initializeCrudNotifications() {
    if (initialized) {
        return;
    }

    initialized = true;

    document.addEventListener('panel:notify', (event) => {
        if (event.detail && typeof event.detail === 'object') {
            notify(event.detail);
        }
    });

    const container = document.getElementById('panel-notifications');
    const notification = container?.dataset.notification;

    if (!notification) {
        return;
    }

    delete container.dataset.notification;

    try {
        const payload = JSON.parse(notification);

        if (payload && typeof payload === 'object') {
            notify(payload);
        }
    } catch {
        // A malformed optional flash notification must not stop other panel modules.
    }
}
