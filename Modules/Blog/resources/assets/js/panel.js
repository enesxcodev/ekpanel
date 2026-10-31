export default function initialize() {
    const links = document.querySelectorAll('[data-blog-language-link]');
    const form = links[0]?.closest('form');
    if (!form) return;
    let changed = false;
    form.addEventListener('input', () => { changed = true; });
    form.addEventListener('change', () => { changed = true; });
    const snapshot = () => JSON.stringify([...new FormData(form)].map(([key, value]) => [key, value instanceof File ? `${value.name}:${value.size}:${value.type}` : value]));
    const initial = snapshot();
    links.forEach((link) => link.addEventListener('click', (event) => {
        if ((changed || snapshot() !== initial) && !window.confirm('Kaydedilmemiş değişiklikleriniz var. Dil değiştirmek istiyor musunuz?')) event.preventDefault();
    }));
}
