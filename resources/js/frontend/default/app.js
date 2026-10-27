const menuToggle = document.querySelector('[data-menu-toggle]');
const mobileNavigation = document.getElementById('mobile-navigation');

function closeMobileNavigation() {
    if (!menuToggle || !mobileNavigation) return;
    mobileNavigation.hidden = true;
    menuToggle.setAttribute('aria-expanded', 'false');
    menuToggle.setAttribute('aria-label', 'Menüyü aç');
}

menuToggle?.addEventListener('click', () => {
    if (!mobileNavigation) return;
    const expanded = menuToggle.getAttribute('aria-expanded') !== 'true';
    mobileNavigation.hidden = !expanded;
    menuToggle.setAttribute('aria-expanded', String(expanded));
    menuToggle.setAttribute('aria-label', expanded ? 'Menüyü kapat' : 'Menüyü aç');
});

mobileNavigation?.addEventListener('click', (event) => {
    if (event.target.closest('a')) closeMobileNavigation();
});

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    if (menuToggle?.getAttribute('aria-expanded') === 'true') {
        closeMobileNavigation();
        menuToggle.focus();
    }
    document.querySelectorAll('.nav-dropdown[open]').forEach((dropdown) => {
        dropdown.open = false;
    });
});

document.addEventListener('click', (event) => {
    document.querySelectorAll('.nav-dropdown[open]').forEach((dropdown) => {
        if (!dropdown.contains(event.target)) dropdown.open = false;
    });
});

document.querySelectorAll('[data-product-filter]').forEach((section) => {
    const buttons = [...section.querySelectorAll('[data-category]')];
    const cards = [...section.querySelectorAll('[data-product-category]')];
    buttons.forEach((button) => button.addEventListener('click', () => {
        buttons.forEach((item) => item.setAttribute('aria-pressed', String(item === button)));
        let visibleCount = 0;
        cards.forEach((card) => {
            card.hidden = button.dataset.category !== 'all' && card.dataset.productCategory !== button.dataset.category;
            if (!card.hidden) visibleCount++;
        });
        section.querySelector('[data-filter-status]').textContent = `${visibleCount} ürün gösteriliyor.`;
    }));
});

document.querySelectorAll('[data-contact-subject]').forEach((link) => {
    link.addEventListener('click', () => {
        const message = document.querySelector('.default-contact-form textarea');
        if (!message || message.value.trim()) return;
        message.value = link.dataset.contactSubject;
    });
});

const formFeedback = document.querySelector('.default-contact-form [role="alert"], .default-contact-form [role="status"]');
if (formFeedback) {
    formFeedback.setAttribute('tabindex', '-1');
    formFeedback.focus({ preventScroll: true });
    formFeedback.scrollIntoView({ block: 'center' });
}
