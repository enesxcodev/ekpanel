export default function initializeGaleriler() {
    document.querySelectorAll('[data-galeriler-form]').forEach((form) => {
        const type = form.querySelector('[data-galeriler-type]');
        const update = () => {
            const photo = type.value === 'photo';
            form.querySelector('[data-galeriler-photos]').hidden = !photo;
            form.querySelector('[data-galeriler-video]').hidden = photo;
            form.querySelector('[name="image_media_ids"]').disabled = !photo;
            form.querySelector('[name="video_url"]').disabled = photo;
        };
        type.addEventListener('change', update);
        update();
    });
    const grid = document.querySelector('[data-galeriler-grid]');
    if (!grid) return;
    document.querySelector('[data-galeriler-select-all]')?.addEventListener('change', (event) => {
        grid.querySelectorAll('[name="ids[]"]').forEach((input) => { input.checked = event.target.checked; });
    });
    if (!grid.dataset.galerilerReorder) return;
    let dragged = null;
    let saving = false;
    let interactiveDrag = false;
    const ids = () => [...grid.querySelectorAll('[data-galeriler-card]')].map(card => Number(card.dataset.galerilerCard));
    let previous = ids();
    grid.addEventListener('pointerdown', (event) => {
        interactiveDrag = Boolean(event.target.closest('a, button, input, select, textarea, label, iframe'));
    });
    grid.addEventListener('dragstart', (event) => {
        const card = event.target.closest('[data-galeriler-card]');
        if (saving || interactiveDrag || !card || event.target.closest('a, button, input, select, textarea, label, iframe')) {
            event.preventDefault();
            return;
        }
        dragged = card;
        previous = ids();
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', dragged.dataset.galerilerCard);
    });
    grid.addEventListener('dragover', (event) => {
        if (dragged && !saving) event.preventDefault();
    });
    grid.addEventListener('drop', async (event) => {
        event.preventDefault();
        const target = event.target.closest('[data-galeriler-card]');
        if (!dragged || !target || target === dragged || saving) return;
        const cards = [...grid.children];
        if (cards.indexOf(dragged) < cards.indexOf(target)) target.after(dragged);
        else target.before(dragged);
        dragged = null;
        saving = true;
        grid.setAttribute('aria-busy', 'true');
        try {
            const response = await fetch(grid.dataset.galerilerReorder, {
                method: 'PUT', credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ category_id: Number(grid.dataset.galerilerCategory), ids: ids() }),
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.errors ? Object.values(data.errors).flat().join(' ') : data.message || 'Sıralama kaydedilemedi.');
            window.Panel.notify({ type: 'success', message: data.message });
        } catch (error) {
            previous.forEach(id => grid.append(grid.querySelector('[data-galeriler-card="' + id + '"]')));
            window.Panel.notify({ type: 'error', message: error.message });
        } finally {
            saving = false;
            grid.removeAttribute('aria-busy');
        }
    });
    grid.addEventListener('dragend', () => { dragged = null; });
}
