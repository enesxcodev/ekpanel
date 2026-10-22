function syncToggleLabel(toggle, isFolded) {
    const label = isFolded ? 'Menüyü genişlet' : 'Menüyü daralt';

    toggle.setAttribute('aria-label', label);
    toggle.setAttribute('aria-pressed', String(isFolded));
    toggle.setAttribute('title', label);
}

export function initializeSidebarEffect() {
    const toggle = document.querySelector('[data-bs-toggle="sidebar-folded"]');

    if (!toggle) {
        return;
    }

    syncToggleLabel(toggle, document.documentElement.dataset.bsSidebar === 'folded-hover');
    document.addEventListener('tabler:sidebar-folded', (event) => {
        syncToggleLabel(toggle, event.detail.folded);
    });
}
