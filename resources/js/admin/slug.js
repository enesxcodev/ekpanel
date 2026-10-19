export function toSlug(value) {
    const transliteration = {
        'ç': 'c', 'ğ': 'g', 'ı': 'i', 'ö': 'o', 'ş': 's', 'ü': 'u',
        'Ç': 'c', 'Ğ': 'g', 'İ': 'i', 'I': 'i', 'Ö': 'o', 'Ş': 's', 'Ü': 'u',
    };

    return [...value]
        .map((character) => transliteration[character] ?? character)
        .join('')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

export function initializeSlugs() {
    document.querySelectorAll('[data-slug-title]').forEach((title) => {
        const form = title.closest('form');
        const slug = form?.querySelector('[data-slug-field]');
        if (!slug) {
            return;
        }

        let manuallyEdited = false;
        title.addEventListener('input', () => {
            if (!manuallyEdited) {
                slug.value = toSlug(title.value);
            }
        });
        slug.addEventListener('input', () => {
            manuallyEdited = slug.value !== '';
        });
        if (slug.value === '' && title.value !== '') {
            slug.value = toSlug(title.value);
        }
    });
}
