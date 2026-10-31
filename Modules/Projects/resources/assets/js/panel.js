export default function initialize(panel) {
    document.querySelectorAll('[data-project-form]').forEach((form) => {
        const input = form.querySelector('[name="image_media_ids"]');
        const cover = form.querySelector('[data-project-cover]');
        let selected = cover.dataset.selected || cover.value;
        const names = new Map([...cover.options].filter(option => option.value).map(option => [Number(option.value), option.textContent]));
        const update = () => {
            let ids = [];
            try { ids = JSON.parse(input.value || '[]'); } catch { /* Validation reports malformed selections. */ }
            if (!Array.isArray(ids)) ids = [];
            selected = cover.value || selected;
            cover.replaceChildren(new Option('İlk seçilen görsel', ''));
            ids.forEach(id => cover.add(new Option(names.get(Number(id)) || 'Görsel #' + id, String(id))));
            cover.value = ids.some(id => String(id) === selected) ? selected : '';
            selected = cover.value;
        };
        input.addEventListener('change', update);
        form.addEventListener('media:selected', (event) => {
            if (event.target.closest('[data-media-field]')?.querySelector('[name="image_media_ids"]')) {
                event.detail.files.forEach(file => names.set(Number(file.id), file.name));
                update();
            }
        });
        update();
    });
}
