import { Modal } from '@tabler/core';

let showConfirmationModal = null;

export function confirmWithModal(options) {
    if (!showConfirmationModal) {
        return Promise.resolve(window.confirm(options.message));
    }

    return showConfirmationModal(options);
}

export function initializeFormConfirmation() {
    const modalElement = document.querySelector('[data-confirm-modal-dialog]');

    if (modalElement instanceof HTMLElement) {
        const modal = Modal.getOrCreateInstance(modalElement);
        const titleElement = modalElement.querySelector('[data-confirm-modal-title]');
        const messageElement = modalElement.querySelector('[data-confirm-modal-message]');
        const cancelButton = modalElement.querySelector('[data-confirm-modal-cancel]');
        const confirmButton = modalElement.querySelector('[data-confirm-modal-submit]');
        let pendingForm = null;
        let confirmedForm = null;
        let resolveConfirmation = null;

        const updateModal = (options) => {
            if (titleElement) {
                titleElement.textContent = options.title || 'İşlemi onayla';
            }

            if (messageElement) {
                messageElement.textContent = options.message || '';
            }

            if (cancelButton instanceof HTMLButtonElement) {
                cancelButton.textContent = options.cancelLabel || 'Vazgeç';
            }

            if (confirmButton instanceof HTMLButtonElement) {
                confirmButton.textContent = options.confirmLabel || 'Onayla';
                confirmButton.className = `btn ${options.confirmClass || 'btn-danger'}`;
            }
        };

        showConfirmationModal = (options) => new Promise((resolve) => {
            if (resolveConfirmation) {
                resolveConfirmation(false);
            }

            resolveConfirmation = resolve;
            pendingForm = null;
            updateModal(options);
            modal.show();
        });

        document.addEventListener('submit', (event) => {
            const form = event.target;

            if (!(form instanceof HTMLFormElement)) {
                return;
            }

            if (form === confirmedForm) {
                confirmedForm = null;
                return;
            }

            if (!form.hasAttribute('data-confirm-modal')) {
                return;
            }

            event.preventDefault();
            pendingForm = form;
            resolveConfirmation = null;
            updateModal({
                title: form.dataset.confirmTitle,
                message: form.dataset.confirmMessage,
                confirmLabel: form.dataset.confirmButton,
                cancelLabel: form.dataset.confirmCancelButton,
                confirmClass: form.dataset.confirmButtonClass,
            });
            modal.show();
        });

        confirmButton?.addEventListener('click', () => {
            if (pendingForm) {
                const form = pendingForm;
                pendingForm = null;
                confirmedForm = form;
                form.requestSubmit();
                modal.hide();
                return;
            }

            const resolve = resolveConfirmation;
            resolveConfirmation = null;
            resolve?.(true);
            modal.hide();
        });

        modalElement.addEventListener('hidden.bs.modal', () => {
            pendingForm = null;
            const resolve = resolveConfirmation;
            resolveConfirmation = null;
            resolve?.(false);
        });
    }

    document.addEventListener('submit', (event) => {
        const form = event.target;

        if (form instanceof HTMLFormElement && form.dataset.confirm && !window.confirm(form.dataset.confirm)) {
            event.preventDefault();
        }
    });
}
