export function initializeFormTabs() {
    document.querySelectorAll('form:has([data-form-tabs])').forEach((form) => {
        form.addEventListener('invalid', (event) => {
            const pane = event.target.closest('.tab-pane');
            if (pane && !pane.classList.contains('active')) {
                form.querySelector(`[data-bs-target="#${pane.id}"]`)?.click();
            }
        }, true);
    });
}
