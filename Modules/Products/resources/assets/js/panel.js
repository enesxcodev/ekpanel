export default function initializeProducts() {
    document.querySelectorAll('[data-product-gallery]').forEach((gallery) => {
        const input = gallery.querySelector('[name="image_media_ids"]');
        const list = gallery.querySelector('[data-product-gallery-order]');
        const names = new Map([...list.querySelectorAll('[data-media-id]')].map(item => [Number(item.dataset.mediaId), item.dataset.mediaName]));
        const read = () => {
            try {
                const ids = JSON.parse(input.value || '[]');
                return Array.isArray(ids) ? ids : [];
            } catch { return []; }
        };
        const render = () => {
            const ids = read();
            list.replaceChildren();
            ids.forEach((id, index) => {
                const item = document.createElement('li');
                item.className = 'list-group-item d-flex align-items-center gap-2';
                const label = document.createElement('span');
                label.className = 'me-auto';
                label.textContent = names.get(Number(id)) || 'Görsel #' + id;
                item.append(label);
                [['Yukarı', -1], ['Aşağı', 1]].forEach(([text, step]) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'btn btn-sm';
                    button.textContent = text;
                    button.setAttribute('aria-label', label.textContent + ': ' + text);
                    button.disabled = index + step < 0 || index + step >= ids.length;
                    button.addEventListener('click', () => {
                        [ids[index], ids[index + step]] = [ids[index + step], ids[index]];
                        input.value = JSON.stringify(ids);
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                    });
                    item.append(button);
                });
                list.append(item);
            });
        };
        input.addEventListener('change', render);
        gallery.addEventListener('media:selected', event => {
            event.detail.files.forEach(file => names.set(Number(file.id), file.name));
            render();
        });
        render();
    });
}
