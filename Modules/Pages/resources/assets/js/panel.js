export function guardLanguageSwitches(form, links, confirmChange, snapshot = () => JSON.stringify([...new FormData(form)].map(([key, value]) => [key, value instanceof File ? `${value.name}:${value.size}:${value.type}` : value]))) {
    if (!form || links.length === 0) return;

    let changed = false;
    form.addEventListener('input', () => { changed = true; });
    form.addEventListener('change', () => { changed = true; });
    const initial = snapshot();

    links.forEach((link) => link.addEventListener('click', (event) => {
        if ((changed || snapshot() !== initial) && !confirmChange('Kaydedilmemiş değişiklikleriniz var. Dil değiştirmek istiyor musunuz?')) {
            event.preventDefault();
        }
    }));
}

export default function initialize() {
    const links = [...document.querySelectorAll('[data-pages-language-link]')];
    const form = links[0]?.closest('form');
    guardLanguageSwitches(form, links, (message) => window.confirm(message));
}
