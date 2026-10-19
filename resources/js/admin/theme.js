export function initializeTheme() {
    const toggle = document.querySelector('[data-admin-theme-toggle]');
    if (!toggle) {
        return;
    }

    const updateToggle = () => {
        const isDark = document.documentElement.dataset.bsTheme === 'dark';
        const label = isDark ? 'Açık temaya geç' : 'Koyu temaya geç';
        toggle.setAttribute('aria-label', label);
        toggle.setAttribute('title', label);
        toggle.querySelector('[data-theme-icon="moon"]').hidden = isDark;
        toggle.querySelector('[data-theme-icon="sun"]').hidden = !isDark;
    };

    updateToggle();
    toggle.addEventListener('click', () => {
        const theme = document.documentElement.dataset.bsTheme === 'dark' ? 'light' : 'dark';
        document.documentElement.dataset.bsTheme = theme;
        try {
            localStorage.setItem('ekagent-admin-theme', theme);
        } catch {}
        updateToggle();
    });
}
