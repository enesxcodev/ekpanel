export function initializeDashboardWidgets(panel) {
    const root = document.querySelector('[data-dashboard-widgets]');
    if (!root) return;
    const state = JSON.parse(root.querySelector('[data-widget-state]').textContent);
    const grid = root.querySelector('[data-widget-grid]');
    const manager = root.querySelector('#dashboard-widget-manager');
    const toggle = document.querySelector('[data-widget-toggle]');
    const save = root.querySelector('[data-widget-save]');
    const cancel = root.querySelector('[data-widget-cancel]');
    const error = root.querySelector('[data-widget-save-error]');
    const templates = new Map([...root.querySelectorAll('[data-widget-template]')].map(node => [node.dataset.widgetTemplate, node]));
    const html = new Map();
    const pending = new Set();
    let saved = [...state.selected];
    let draft = [...saved];
    let revision = state.revision;
    let editing = false;
    let saving = false;
    let dragged = null;
    let pointerDrag = null;
    const headers = { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content };
    const notify = (type, message) => panel?.notify?.({ type, message });
    const setEditing = value => {
        editing = value;
        root.dataset.editing = String(value);
        manager.hidden = !value;
        toggle.setAttribute('aria-expanded', String(value));
        render();
        if (value) root.querySelector('[data-widget-search]').focus();
    };
    const render = () => {
        const focused = document.activeElement;
        const focusedCardId = focused?.closest('[data-widget-card]')?.dataset.widgetCard;
        const focusedAction = focused?.hasAttribute('data-widget-move')
            ? '[data-widget-move="' + focused.dataset.widgetMove + '"]'
            : focused?.hasAttribute('data-widget-remove') ? '[data-widget-remove]' : '[data-widget-retry]';
        const fragment = document.createDocumentFragment();
        draft.forEach((id, index) => {
            const template = document.createElement('template');
            template.innerHTML = html.get(id) || templates.get(id).innerHTML;
            const card = template.content.firstElementChild;
            card.draggable = editing && !saving;
            card.querySelector('[data-widget-move="-1"]').disabled = index === 0 || saving;
            card.querySelector('[data-widget-move="1"]').disabled = index === draft.length - 1 || saving;
            card.querySelector('[data-widget-remove]').disabled = saving;
            fragment.append(card);
        });
        grid.replaceChildren(fragment);
        root.querySelector('[data-widget-empty]').hidden = draft.length > 0;
        root.querySelector('[data-widget-count]').textContent = draft.length + ' widget seçildi';
        root.querySelectorAll('[data-widget-checkbox]').forEach(input => {
            input.checked = draft.includes(input.value);
            input.disabled = saving;
        });
        if (focusedCardId) {
            const card = grid.querySelector('[data-widget-card="' + CSS.escape(focusedCardId) + '"]');
            const action = card?.querySelector(focusedAction);
            (action && !action.disabled ? action : card?.querySelector('button:not(:disabled)') || toggle).focus();
        }
    };
    const failureCard = id => {
        const fragment = templates.get(id).content.cloneNode(true);
        const value = fragment.querySelector('.dashboard-widget-value');
        value.textContent = 'Veri yüklenemedi';
        value.className = 'dashboard-widget-error';
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn btn-sm btn-outline-light mt-2';
        button.dataset.widgetRetry = '';
        button.textContent = 'Yeniden dene';
        value.after(button);
        fragment.querySelector('.visually-hidden')?.remove();
        return fragment.firstElementChild.outerHTML;
    };
    const load = async ids => {
        ids = ids.filter(id => !pending.has(id) && !html.has(id));
        if (!ids.length) return;
        ids.forEach(id => pending.add(id));
        try {
            const response = await fetch(root.dataset.dataUrl, { method: 'POST', headers, credentials: 'same-origin', body: JSON.stringify({ widget_ids: ids }) });
            if (!response.ok) throw new Error('Veri yüklenemedi');
            const result = await response.json();
            result.widgets.forEach(widget => {
                if (templates.has(widget.id) && ids.includes(widget.id)) html.set(widget.id, widget.html);
            });
            ids.forEach(id => { if (!html.has(id)) html.set(id, failureCard(id)); });
        } catch {
            ids.forEach(id => html.set(id, failureCard(id)));
        } finally {
            ids.forEach(id => pending.delete(id));
            // Render only the current draft; late requests cannot restore removed cards.
            render();
        }
    };
    toggle.addEventListener('click', () => {
        if (saving) return;
        setEditing(!editing);
    });
    root.addEventListener('change', event => {
        const input = event.target.closest('[data-widget-checkbox]');
        if (!input || !editing || saving) return;
        draft = input.checked ? [...draft, input.value] : draft.filter(id => id !== input.value);
        render();
        load(draft);
    });
    root.addEventListener('click', event => {
        const card = event.target.closest('[data-widget-card]');
        if (!card) return;
        const id = card.dataset.widgetCard;
        if (event.target.closest('[data-widget-retry]')) {
            html.delete(id);
            render();
            load([id]);
            return;
        }
        if (!editing || saving) return;
        if (event.target.closest('[data-widget-remove]')) {
            draft = draft.filter(value => value !== id);
            render();
            return;
        }
        const move = event.target.closest('[data-widget-move]');
        if (move) {
            const from = draft.indexOf(id);
            const to = from + Number(move.dataset.widgetMove);
            if (to < 0 || to >= draft.length) return;
            [draft[from], draft[to]] = [draft[to], draft[from]];
            render();
            const movedCard = grid.querySelector('[data-widget-card="' + CSS.escape(id) + '"]');
            const sameAction = movedCard.querySelector('[data-widget-move="' + move.dataset.widgetMove + '"]');
            (sameAction.disabled ? movedCard.querySelector('[data-widget-move]:not(:disabled)') : sameAction)?.focus();
        }
    });
    grid.addEventListener('pointerdown', event => {
        const handle = event.target.closest('.dashboard-widget-drag');
        if (!handle || !editing || saving || event.button !== 0) return;
        event.preventDefault();
        const card = handle.closest('[data-widget-card]');
        pointerDrag = { id: card.dataset.widgetCard, x: event.clientX, y: event.clientY, pointerId: event.pointerId, handle };
        handle.setPointerCapture(event.pointerId);
        card.dataset.dragging = 'true';
    });
    const finishPointerDrag = event => {
        if (!pointerDrag || event.pointerId !== pointerDrag.pointerId) return;
        const previous = pointerDrag;
        pointerDrag = null;
        if (previous.handle.hasPointerCapture(event.pointerId)) previous.handle.releasePointerCapture(event.pointerId);
        grid.querySelectorAll('[data-dragging]').forEach(card => delete card.dataset.dragging);
        if (event.type === 'pointercancel' || !editing || saving || Math.hypot(event.clientX - previous.x, event.clientY - previous.y) < 5) return;
        const target = document.elementFromPoint(event.clientX, event.clientY)?.closest('[data-widget-card]');
        if (!target || !grid.contains(target)) return;
        const from = draft.indexOf(previous.id), to = draft.indexOf(target.dataset.widgetCard);
        if (from < 0 || to < 0 || from === to) return;
        draft.splice(from, 1);
        draft.splice(to, 0, previous.id);
        render();
    };
    grid.addEventListener('pointerup', finishPointerDrag);
    grid.addEventListener('pointercancel', finishPointerDrag);
    grid.addEventListener('dragstart', event => {
        const card = event.target.closest('[data-widget-card]');
        if (!editing || saving || !card) { event.preventDefault(); return; }
        dragged = card.dataset.widgetCard;
        card.dataset.dragging = 'true';
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', dragged);
    });
    grid.addEventListener('dragover', event => { if (editing && dragged && !saving) event.preventDefault(); });
    grid.addEventListener('drop', event => {
        event.preventDefault();
        const target = event.target.closest('[data-widget-card]')?.dataset.widgetCard;
        if (!editing || saving || !dragged || !target || dragged === target) return;
        const from = draft.indexOf(dragged), to = draft.indexOf(target);
        if (from < 0 || to < 0) return;
        draft.splice(from, 1);
        draft.splice(to, 0, dragged);
        dragged = null;
        render();
    });
    grid.addEventListener('dragend', () => { dragged = null; grid.querySelectorAll('[data-dragging]').forEach(card => delete card.dataset.dragging); });
    cancel.addEventListener('click', () => {
        if (saving) return;
        draft = [...saved];
        error.hidden = true;
        setEditing(false);
        load(draft);
        toggle.focus();
    });
    save.addEventListener('click', async () => {
        if (saving) return;
        saving = true;
        save.disabled = cancel.disabled = toggle.disabled = true;
        save.textContent = 'Kaydediliyor…';
        error.hidden = true;
        render();
        try {
            const response = await fetch(root.dataset.preferenceUrl, { method: 'PUT', headers, credentials: 'same-origin', body: JSON.stringify({ widget_order: draft, revision }) });
            const result = await response.json();
            if (!response.ok) {
                const message = response.status === 409 ? 'Düzen başka bir sekmede değişti. Sayfayı yenileyip tekrar deneyin.' : (result.message || 'Düzen kaydedilemedi.');
                throw new Error(message);
            }
            saved = [...result.widget_order];
            draft = [...saved];
            revision = result.revision;
            setEditing(false);
            notify('success', 'Dashboard düzeniniz kaydedildi.');
            toggle.focus();
        } catch (exception) {
            error.textContent = exception.message || 'Düzen kaydedilemedi.';
            error.hidden = false;
            notify('error', error.textContent);
        } finally {
            saving = false;
            save.disabled = cancel.disabled = toggle.disabled = false;
            save.textContent = 'Düzeni Kaydet';
            render();
            if (!editing) toggle.focus();
        }
    });
    root.querySelector('[data-widget-search]').addEventListener('input', event => {
        const query = event.target.value.trim().toLocaleLowerCase('tr-TR');
        let count = 0;
        root.querySelectorAll('[data-widget-group]').forEach(group => {
            let visible = 0;
            group.querySelectorAll('[data-widget-option]').forEach(option => {
                option.hidden = !option.dataset.search.toLocaleLowerCase('tr-TR').includes(query);
                if (!option.hidden) visible++;
            });
            group.hidden = visible === 0;
            count += visible;
        });
        root.querySelector('[data-widget-search-empty]').hidden = count > 0;
    });
    root.dataset.editing = 'false';
    render();
    load(draft);
}
