export function languageMenuHref(href, menuId) {
    const url = new URL(href, 'http://localhost');
    if (Number(menuId) > 0) url.searchParams.set('menu', String(menuId));
    else url.searchParams.delete('menu');

    return `${url.pathname}${url.search}${url.hash}`;
}

export function initializeMenuWorkspace({ notify, confirm }) {
    const root = document.querySelector('[data-menu-workspace]');
    if (!root || root.dataset.initialized) return;
    root.dataset.initialized = 'true';
    const config = JSON.parse(root.querySelector('[data-menu-configuration]').textContent);
    const find = (selector) => root.querySelector(selector);
    const selector = find('[data-menu-selector]');
    const menuForm = find('[data-menu-form]');
    const itemForm = find('[data-item-form]');
    const editor = find('[data-item-editor]');
    const tree = find('[data-menu-tree]');
    const globalError = find('[data-menu-error]');
    const icons = new Map([...root.querySelectorAll('[data-menu-icon]')].map((node) => [node.dataset.menuIcon, node]));
    let menus = config.menus;
    let state = null;
    let selectedId = null;
    let creatingMenu = false;
    let itemDirty = false;
    let menuDirty = false;
    let busy = false;
    let focusAfter = null;
    const collapsed = new Set();
    const types = { group: 'Bağlantısız grup', url: 'Özel URL', route: 'Site rotası', anchor: 'Sayfa içi bağlantı', content: 'İçerik' };

    function element(tag, className = '', text = '') {
        const node = document.createElement(tag);
        node.className = className;
        node.textContent = text;
        return node;
    }

    function icon(name) {
        return (icons.get(name) ?? icons.get('file')).content.cloneNode(true);
    }

    function announce(message) { find('[data-menu-live]').textContent = message; }

    function clearErrors(form) {
        const box = form?.querySelector('[data-form-errors]') ?? globalError;
        box.replaceChildren();
        box.hidden = true;
        form?.querySelectorAll('[aria-invalid]').forEach((input) => {
            input.removeAttribute('aria-invalid');
            input.classList.remove('is-invalid');
        });
    }

    function showError(error, form) {
        clearErrors(form);
        const box = form?.querySelector('[data-form-errors]') ?? globalError;
        const messages = error.fields ? Object.values(error.fields).flat() : [error.message];
        [...new Set(messages)].forEach((message) => box.append(element('div', '', message)));
        box.hidden = false;
        Object.keys(error.fields ?? {}).forEach((name) => {
            [...(form?.elements ?? [])].filter((input) => input.name === name && !input.disabled).forEach((input) => {
                input.classList.add('is-invalid');
                input.setAttribute('aria-invalid', 'true');
            });
        });
        box.scrollIntoView({ block: 'nearest', behavior: 'auto' });
    }

    async function request(url, method = 'GET', body) {
        const response = await fetch(url, {
            method,
            credentials: 'same-origin',
            headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '' },
            ...(body === undefined ? {} : { body: JSON.stringify(body) }),
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            const fallback = response.status === 419 ? 'Oturum süresi doldu. Sayfayı yenileyip tekrar deneyin.'
                : response.status === 403 ? 'Bu işlem için yetkiniz yok.'
                    : response.status === 404 ? 'Menü veya öğe bulunamadı. Listeyi yenileyin.' : 'İşlem tamamlanamadı. Yeniden deneyin.';
            const error = new Error(data.message ?? fallback);
            error.fields = data.errors;
            throw error;
        }
        return data;
    }

    async function perform(task, form = null) {
        if (busy) return;
        busy = true;
        clearErrors(form);
        root.classList.add('is-busy');
        root.setAttribute('aria-busy', 'true');
        const activeControl = document.activeElement;
        root.inert = true;
        try { await task(); }
        catch (error) { showError(error instanceof TypeError ? new Error('Sunucuya ulaşılamadı. Bağlantınızı kontrol edip yeniden deneyin.') : error, error.recorded ? null : form); }
        finally {
            root.inert = false;
            busy = false;
            root.classList.remove('is-busy');
            root.removeAttribute('aria-busy');
            if (focusAfter) { find(focusAfter)?.focus({ preventScroll: true }); focusAfter = null; }
            else if (activeControl?.isConnected && activeControl.checkVisibility?.()) activeControl.focus({ preventScroll: true });
        }
    }

    function discardChanges() {
        return !(itemDirty || menuDirty) || window.confirm('Kaydedilmemiş değişiklikler var. Değişikliklerden vazgeçilsin mi?');
    }

    function updateMenus(summary = null) {
        if (summary) {
            const index = menus.findIndex((menu) => menu.id === summary.id);
            if (index < 0) menus.push(summary);
            else menus[index] = summary;
        }
        selector.replaceChildren(new Option(menus.length ? 'Menü seçin' : 'Henüz menü yok', ''));
        menus.forEach((menu) => selector.add(new Option(`${menu.name}${menu.translation_exists ? '' : ' · Çeviri eklenmedi'} · ${menu.key}`, String(menu.id))));
        selector.value = String(state?.menu.id ?? '');
    }

    function updateLanguageLinks(menuId) {
        root.querySelectorAll('[data-menu-language]').forEach((link) => {
            link.href = languageMenuHref(link.href, menuId);
        });
    }

    function status(active) { return element('span', `menu-status${active ? ' is-active' : ''}`, active ? 'Aktif' : 'Pasif'); }

    function syncSummary() {
        const translated = Boolean(state?.menu.translation_exists);
        find('[data-menu-builder]').hidden = !state || !translated;
        find('[data-menu-empty]').hidden = Boolean(state);
        find('[data-menu-key]').textContent = state?.menu.key ?? '—';
        find('[data-menu-count]').textContent = String(state?.items.length ?? 0);
        find('[data-menu-status]').replaceChildren(state ? status(state.menu.is_active) : document.createTextNode('—'));
        if (find('[data-menu-edit]')) find('[data-menu-edit]').hidden = !state;
        if (state && !translated) {
            find('[data-menu-empty]').hidden = false;
            find('[data-menu-empty] .empty-title').textContent = 'Bu menünün seçili dilde çevirisi yok';
            find('[data-menu-empty] .empty-subtitle').textContent = 'Menü bilgilerini açıp bu dildeki adını kaydedin. Bu dil için yeni bir öğe ağacı oluşturulur.';
        } else if (!state) {
            find('[data-menu-empty] .empty-title').textContent = 'Bir menü seçin';
            find('[data-menu-empty] .empty-subtitle').textContent = 'Mevcut bir menüyü seçerek başlayın veya yeni menü oluşturun.';
        }
    }

    async function loadMenu(summary, itemId = null) {
        const data = await request(summary.workspace_url);
        state = data;
        selectedId = null;
        itemDirty = false;
        menuDirty = false;
        menuForm.hidden = true;
        if (editor) editor.hidden = true;
        updateMenus(data.menu);
        syncSummary();
        renderTree();
        if (itemId && state.items.some((item) => item.id === itemId)) selectItem(itemId, false);
        const url = new URL(window.location.href);
        url.searchParams.set('menu', data.menu.id);
        if (config.language?.code) url.searchParams.set('lang', config.language.code);
        url.searchParams.delete('new');
        url.searchParams.delete('item');
        window.history.replaceState(null, '', url);
        updateLanguageLinks(data.menu.id);
        announce(`${data.menu.name} yüklendi. ${data.items.length} öğe.`);
    }

    async function refreshSaved(summary, itemId = null) {
        itemDirty = false;
        menuDirty = false;
        try { await loadMenu(summary, itemId); }
        catch {
            const error = new Error('İşlem kaydedildi ancak güncel görünüm yüklenemedi. Menü seçicisinden menüyü tekrar seçerek yenileyin.');
            error.recorded = true;
            throw error;
        }
    }

    function descendants(id) {
        const result = new Set([id]);
        const pending = [id];
        while (pending.length) {
            const parent = pending.pop();
            state.items.filter((item) => item.parent_id === parent && !result.has(item.id)).forEach((item) => { result.add(item.id); pending.push(item.id); });
        }
        return result;
    }

    function depth(item) {
        const seen = new Set([item.id]);
        let count = 0;
        let parent = item.parent_id;
        while (parent !== null && !seen.has(parent)) {
            seen.add(parent);
            count++;
            parent = state.items.find((node) => node.id === parent)?.parent_id ?? null;
        }
        return count;
    }

    function renderTree() {
        tree.replaceChildren();
        find('[data-menu-tree-empty]').hidden = state.items.length > 0;
        const invisible = new Set();
        state.items.forEach((item) => {
            if (item.parent_id !== null && (collapsed.has(item.parent_id) || invisible.has(item.parent_id))) {
                invisible.add(item.id);
                return;
            }
            const row = element('div', `menu-row${item.id === selectedId ? ' is-selected' : ''}`);
            row.dataset.id = item.id;
            row.setAttribute('role', 'listitem');
            row.style.setProperty('--menu-depth', depth(item));
            if (config.permissions.update) {
                const handle = element('button', 'menu-drag-handle', '⠿');
                handle.type = 'button';
                handle.dataset.move = item.id;
                handle.setAttribute('aria-label', `${item.label} öğesini taşı`);
                handle.setAttribute('aria-describedby', 'menu-drag-help');
                row.append(handle);
            }
            row.append(icon(item.icon || (item.target_type === 'group' ? 'folder' : item.target_type === 'content' ? 'file' : 'link')));
            const label = element('div', 'menu-row-label');
            const title = element(config.permissions.update ? 'button' : 'span', 'menu-row-title btn-reset', item.label);
            if (config.permissions.update) { title.type = 'button'; title.dataset.edit = item.id; }
            const source = config.sources.find((entry) => entry.key === item.source);
            label.append(title, element('span', 'badge bg-secondary-lt', item.target_type === 'content' ? source?.label ?? 'İçerik' : types[item.target_type]));
            const target = element('span', `menu-row-target${item.target.error ? ' text-danger' : ''}`, item.target.error ?? item.target.url ?? (item.dynamic_children ? 'Otomatik alt içerikler' : ''));
            label.append(target);
            row.append(label, status(item.is_active));
            if (state.items.some((node) => node.parent_id === item.id)) {
                const toggle = element('button', 'btn btn-sm btn-ghost-secondary', collapsed.has(item.id) ? '›' : '⌄');
                toggle.type = 'button'; toggle.dataset.collapse = item.id;
                toggle.setAttribute('aria-expanded', String(!collapsed.has(item.id)));
                toggle.setAttribute('aria-label', `${item.label} alt öğelerini aç/kapat`);
                row.append(toggle);
            }
            if (config.permissions.update) {
                const edit = element('button', 'btn btn-sm btn-outline-secondary', '✎');
                edit.type = 'button'; edit.dataset.edit = item.id; edit.setAttribute('aria-label', `${item.label} öğesini düzenle`);
                const remove = element('button', 'btn btn-sm btn-outline-danger');
                remove.type = 'button'; remove.dataset.delete = item.id; remove.setAttribute('aria-label', `${item.label} öğesini sil`); remove.append(icon('trash'));
                row.append(edit, remove);
            }
            tree.append(row);
        });
    }

    function selectItem(id, focus = true) {
        if (!itemForm) return;
        if (selectedId === id && itemDirty) return;
        if (!discardChanges()) return;
        const item = state.items.find((node) => node.id === id);
        if (!item) return;
        selectedId = id;
        itemDirty = false;
        menuDirty = false;
        menuForm.hidden = true;
        itemForm.reset();
        clearErrors(itemForm);
        for (const name of ['label', 'icon', 'target_type', 'source', 'sort_order']) itemForm.elements.namedItem(name).value = item[name] ?? '';
        for (const name of ['is_active', 'new_tab']) itemForm.elements.namedItem(name).checked = Boolean(item[name]);
        root.querySelectorAll('[data-item-target]').forEach((fieldset) => {
            fieldset.hidden = fieldset.dataset.itemTarget !== item.target_type;
            fieldset.disabled = fieldset.hidden;
        });
        const fields = find(`[data-item-target="${item.target_type}"]`);
        for (const name of ['url', 'route_name', 'anchor', 'source_id']) {
            const input = fields.querySelector(`[name="${name}"]`);
            if (input && name !== 'source_id') input.value = item[name] ?? '';
        }
        const params = fields.querySelector('[name="route_parameters_json"]');
        if (params) params.value = item.route_parameters && Object.keys(item.route_parameters).length ? JSON.stringify(item.route_parameters, null, 2) : '';
        if (item.target_type === 'content') {
            const source = config.sources.find((entry) => entry.key === item.source);
            const input = fields.querySelector('[name="source_id"]');
            input.replaceChildren(new Option('İçerik seçin', ''));
            (source?.options ?? []).forEach((option) => input.add(new Option(option.label, String(option.value))));
            if (![...input.options].some((option) => option.value === String(item.source_id))) input.add(new Option(`${item.source_id} (kayıt kullanılamıyor)`, String(item.source_id)));
            input.value = String(item.source_id);
            fields.querySelector('[data-item-source-label]').textContent = `Bağlantı (${source?.label ?? 'Kullanılamayan kaynak'})`;
            fields.querySelector('[data-item-source-status]').textContent = item.target.error ?? 'Seçili içeriğin ziyaretçi bağlantısı kullanılır.';
            const dynamic = fields.querySelector('[name="dynamic_children"]');
            fields.querySelector('[data-item-dynamic]').hidden = !source?.supports_children;
            dynamic.disabled = !source?.supports_children;
            dynamic.checked = Boolean(item.dynamic_children);
        }
        const excluded = descendants(id);
        const parent = itemForm.elements.namedItem('parent_id');
        parent.replaceChildren(new Option('Ana öğe (en üst seviye)', ''));
        state.items.filter((node) => !excluded.has(node.id) && !node.dynamic_children).forEach((node) => parent.add(new Option(`${'— '.repeat(depth(node))}${node.label}`, String(node.id))));
        parent.value = String(item.parent_id ?? '');
        find('[data-item-unsaved]').hidden = true;
        editor.hidden = false;
        renderTree();
        if (focus) { editor.scrollIntoView({ block: 'nearest', behavior: 'auto' }); itemForm.elements.namedItem('label').focus({ preventScroll: true }); }
    }

    function openMenuForm(isNew) {
        if (!discardChanges()) return;
        creatingMenu = isNew;
        menuForm.reset();
        clearErrors(menuForm);
        menuDirty = false;
        itemDirty = false;
        if (editor) editor.hidden = true;
        find('[data-menu-form-title]').textContent = isNew ? 'Yeni menü oluştur' : 'Menü bilgileri';
        if (!isNew && state) {
            for (const name of ['name', 'key', 'icon']) menuForm.elements.namedItem(name).value = name === 'name' && !state.menu.translation_exists ? '' : (state.menu[name] ?? '');
            menuForm.elements.namedItem('is_active').checked = state.menu.is_active;
        }
        if (find('[data-menu-delete]')) find('[data-menu-delete]').hidden = isNew;
        menuForm.hidden = false;
        if (isNew) { find('[data-menu-builder]').hidden = true; find('[data-menu-empty]').hidden = true; }
        else syncSummary();
        if (busy) focusAfter = '#menu-name';
        menuForm.elements.namedItem('name').focus();
    }

    selector.addEventListener('change', () => {
        const summary = menus.find((menu) => menu.id === Number(selector.value));
        if (!summary || !discardChanges()) { selector.value = String(state?.menu.id ?? ''); return; }
        perform(async () => {
            try { await loadMenu(summary); }
            catch (error) { selector.value = String(state?.menu.id ?? ''); throw error; }
        });
    });
    find('[data-menu-new]')?.addEventListener('click', () => openMenuForm(true));
    find('[data-menu-edit]')?.addEventListener('click', () => openMenuForm(false));
    find('[data-menu-cancel]')?.addEventListener('click', () => {
        if (!discardChanges()) return;
        menuDirty = false; menuForm.hidden = true; syncSummary();
    });
    menuForm.addEventListener('input', () => { menuDirty = true; });
    menuForm.addEventListener('submit', (event) => {
        event.preventDefault();
        const data = Object.fromEntries(new FormData(menuForm));
        data.lang_id = config.language?.id;
        data.is_active = menuForm.elements.namedItem('is_active').checked;
        perform(async () => {
            const response = await request(creatingMenu ? config.storeUrl : state.urls.update, creatingMenu ? 'POST' : 'PUT', data);
            menuForm.hidden = true;
            updateMenus(response.menu);
            notify({ type: 'success', message: response.message });
            await refreshSaved(response.menu);
        }, menuForm);
    });
    find('[data-menu-delete]')?.addEventListener('click', async () => {
        if (!state || !await confirm({ title: 'Menüyü sil', message: 'Bu menü ve tüm öğeleri silinsin mi?', confirmLabel: 'Sil' })) return;
        perform(async () => {
            const response = await request(state.urls.delete, 'DELETE');
            menus = menus.filter((menu) => menu.id !== state.menu.id);
            state = null; selectedId = null; menuDirty = false; itemDirty = false; menuForm.hidden = true;
            if (editor) editor.hidden = true;
            if (menus.length) await loadMenu(menus[0]);
            else { updateMenus(); syncSummary(); const url = new URL(window.location.href); url.searchParams.delete('menu'); window.history.replaceState(null, '', url); }
            notify({ type: 'success', message: response.message });
        });
    });

    root.querySelectorAll('[data-source-search]').forEach((input) => input.addEventListener('input', () => {
        const form = input.closest('form');
        const search = input.value.toLocaleLowerCase('tr').trim();
        const options = [...form.querySelectorAll('.menu-source-option')];
        options.forEach((option) => {
            option.hidden = !option.textContent.toLocaleLowerCase('tr').includes(search);
            if (option.hidden) option.querySelector('input').checked = false;
        });
        form.querySelector('[data-source-no-results]').hidden = options.some((option) => !option.hidden);
    }));
    root.querySelectorAll('[data-menu-add]').forEach((form) => form.addEventListener('submit', (event) => {
        event.preventDefault();
        if (!state || !discardChanges()) return;
        const data = Object.fromEntries(new FormData(form));
        data.lang_id = config.language?.id;
        data.target_type = form.dataset.type;
        data.is_active = form.elements.namedItem('is_active')?.checked ?? true;
        data.new_tab = false;
        data.sort_order = Math.min(65535, Math.max(-1, ...state.items.filter((item) => item.parent_id === null).map((item) => item.sort_order)) + 1);
        if (data.target_type === 'content') {
            data.source = form.dataset.source;
            data.label = form.querySelector('input[name="source_id"]:checked')?.dataset.label ?? '';
        }
        perform(async () => {
            const response = await request(state.urls.add, 'POST', data);
            form.reset();
            form.querySelectorAll('.menu-source-option').forEach((option) => { option.hidden = false; });
            if (form.querySelector('[data-source-no-results]')) form.querySelector('[data-source-no-results]').hidden = true;
            notify({ type: 'success', message: response.message });
            await refreshSaved(state.menu, response.id);
        }, form);
    }));

    itemForm?.addEventListener('input', () => { itemDirty = true; find('[data-item-unsaved]').hidden = false; });
    itemForm?.addEventListener('change', () => { itemDirty = true; find('[data-item-unsaved]').hidden = false; });
    itemForm?.addEventListener('submit', (event) => {
        event.preventDefault();
        const item = state.items.find((node) => node.id === selectedId);
        const data = Object.fromEntries(new FormData(itemForm));
        data.lang_id = config.language?.id;
        data.is_active = itemForm.elements.namedItem('is_active').checked;
        data.new_tab = itemForm.elements.namedItem('new_tab').checked;
        data.dynamic_children = itemForm.querySelector('[name="dynamic_children"]').checked && !itemForm.querySelector('[name="dynamic_children"]').disabled;
        data.parent_id = data.parent_id ? Number(data.parent_id) : null;
        data.sort_order = Number(data.sort_order);
        perform(async () => {
            const response = await request(item.update_url, 'PUT', data);
            notify({ type: 'success', message: response.message });
            await refreshSaved(state.menu, item.id);
        }, itemForm);
    });
    find('[data-item-cancel]')?.addEventListener('click', () => {
        if (!discardChanges()) return;
        itemDirty = false;
        selectItem(selectedId, false);
    });

    async function deleteItem(id) {
        const item = state.items.find((node) => node.id === id);
        if (!item || !discardChanges() || !await confirm({ title: 'Menü öğesini sil', message: `“${item.label}” öğesi silinsin mi?`, confirmLabel: 'Sil' })) return;
        perform(async () => {
            const response = await request(item.delete_url, 'DELETE', { lang_id: config.language?.id });
            notify({ type: 'success', message: response.message });
            await refreshSaved(state.menu, selectedId === id ? null : selectedId);
        });
    }
    find('[data-item-delete]')?.addEventListener('click', () => deleteItem(selectedId));
    tree.addEventListener('click', (event) => {
        const edit = event.target.closest('[data-edit]');
        const remove = event.target.closest('[data-delete]');
        const toggle = event.target.closest('[data-collapse]');
        if (busy) return;
        if (edit) selectItem(Number(edit.dataset.edit));
        if (remove) deleteItem(Number(remove.dataset.delete));
        if (toggle) { const id = Number(toggle.dataset.collapse); if (collapsed.has(id)) collapsed.delete(id); else collapsed.add(id); renderTree(); }
    });
    find('[data-menu-expand]').addEventListener('click', () => { collapsed.clear(); renderTree(); });
    find('[data-menu-collapse]').addEventListener('click', () => { state.items.forEach((item) => collapsed.add(item.id)); renderTree(); });

    function reposition(id, targetId, placement) {
        if (busy || !discardChanges()) return;
        const subtree = descendants(id);
        const target = state.items.find((item) => item.id === targetId);
        if (!target || subtree.has(targetId) || (placement === 'inside' && target.dynamic_children)) return;
        const snapshot = structuredClone(state.items);
        const moving = state.items.filter((item) => subtree.has(item.id));
        const targetDescendants = descendants(targetId);
        const remaining = state.items.filter((item) => !subtree.has(item.id));
        let index = remaining.findIndex((item) => item.id === targetId);
        if (placement !== 'before') {
            index++;
            while (index < remaining.length && targetDescendants.has(remaining[index].id)) index++;
        }
        moving[0].parent_id = placement === 'inside' ? targetId : target.parent_id;
        state.items = [...remaining.slice(0, index), ...moving, ...remaining.slice(index)];
        const counters = new Map();
        state.items.forEach((item) => { const parent = item.parent_id; item.sort_order = counters.get(parent) ?? 0; counters.set(parent, item.sort_order + 1); });
        collapsed.delete(targetId);
        renderTree();
        perform(async () => {
            try {
            await request(state.urls.reorder, 'PUT', { lang_id: config.language?.id, items: state.items.map((item) => ({ id: item.id, parent_id: item.parent_id, sort_order: item.sort_order })) });
            } catch (error) { state.items = snapshot; renderTree(); throw error; }
            await refreshSaved(state.menu, selectedId);
            announce('Yeni sıra ve üst öğe ilişkisi kaydedildi.');
            focusAfter = `[data-move="${id}"]`;
        });
    }

    tree.addEventListener('keydown', (event) => {
        const handle = event.target.closest('[data-move]');
        if (!handle || !['ArrowUp', 'ArrowDown', 'ArrowRight', 'ArrowLeft'].includes(event.key) || busy) return;
        event.preventDefault();
        const id = Number(handle.dataset.move);
        const item = state.items.find((node) => node.id === id);
        const siblings = state.items.filter((node) => node.parent_id === item.parent_id);
        const index = siblings.findIndex((node) => node.id === id);
        if (event.key === 'ArrowUp' && index > 0) reposition(id, siblings[index - 1].id, 'before');
        if (event.key === 'ArrowDown' && index < siblings.length - 1) reposition(id, siblings[index + 1].id, 'after');
        if (event.key === 'ArrowRight' && index > 0) reposition(id, siblings[index - 1].id, 'inside');
        if (event.key === 'ArrowLeft' && item.parent_id !== null) reposition(id, item.parent_id, 'after');
    });

    let drag = null;
    let frame = null;
    function clearDrop() { tree.querySelectorAll('.drop-before, .drop-after, .drop-inside').forEach((node) => node.classList.remove('drop-before', 'drop-after', 'drop-inside')); }
    function updateDrop() {
        if (!drag?.started) return;
        clearDrop();
        drag.target = null;
        const row = document.elementFromPoint(drag.x, drag.y)?.closest('.menu-row');
        if (!row || !tree.contains(row)) return;
        const id = Number(row.dataset.id);
        if (descendants(drag.id).has(id)) return;
        const bounds = row.getBoundingClientRect();
        const ratio = (drag.y - bounds.top) / bounds.height;
        const placement = ratio < .27 ? 'before' : ratio > .73 ? 'after' : 'inside';
        if (placement === 'inside' && state.items.find((item) => item.id === id).dynamic_children) return;
        drag.target = { id, placement };
        let indicator = row;
        if (placement === 'after') {
            const branch = descendants(id);
            const visible = [...tree.querySelectorAll('.menu-row')].filter((node) => branch.has(Number(node.dataset.id)));
            indicator = visible[visible.length - 1] ?? row;
        }
        indicator.classList.add(`drop-${placement}`);
    }
    function scrollDrag() {
        if (!drag?.started) return;
        const direction = drag.y < 90 ? -9 : drag.y > window.innerHeight - 90 ? 9 : 0;
        if (direction) { window.scrollBy(0, direction); updateDrop(); }
        frame = requestAnimationFrame(scrollDrag);
    }
    function endDrag(event, cancel = false) {
        if (!drag || (event.pointerId !== undefined && event.pointerId !== drag.pointerId)) return;
        const current = drag;
        drag = null;
        if (frame) cancelAnimationFrame(frame);
        current.preview?.remove();
        current.handle.classList.remove('is-dragging');
        tree.querySelectorAll('.is-dragging').forEach((row) => row.classList.remove('is-dragging'));
        clearDrop();
        if (current.handle.hasPointerCapture(current.pointerId)) current.handle.releasePointerCapture(current.pointerId);
        if (!cancel && current.target) reposition(current.id, current.target.id, current.target.placement);
    }
    tree.addEventListener('pointerdown', (event) => {
        const handle = event.target.closest('[data-move]');
        if (!handle || busy || event.button !== 0) return;
        event.preventDefault();
        handle.focus({ preventScroll: true });
        drag = { id: Number(handle.dataset.move), pointerId: event.pointerId, startX: event.clientX, startY: event.clientY, x: event.clientX, y: event.clientY, handle, started: false };
        handle.setPointerCapture(event.pointerId);
    });
    tree.addEventListener('pointermove', (event) => {
        if (!drag || drag.pointerId !== event.pointerId) return;
        drag.x = event.clientX; drag.y = event.clientY;
        if (!drag.started && Math.hypot(drag.x - drag.startX, drag.y - drag.startY) < 6) return;
        if (!drag.started) {
            drag.started = true;
            drag.preview = element('div', 'menu-drag-preview', state.items.find((item) => item.id === drag.id).label);
            document.body.append(drag.preview);
            drag.handle.closest('.menu-row').classList.add('is-dragging');
            frame = requestAnimationFrame(scrollDrag);
        }
        drag.preview.style.left = `${drag.x + 12}px`;
        drag.preview.style.top = `${drag.y + 12}px`;
        updateDrop();
    });
    tree.addEventListener('pointerup', (event) => endDrag(event));
    tree.addEventListener('pointercancel', (event) => endDrag(event, true));
    tree.addEventListener('lostpointercapture', (event) => endDrag(event, true));
    document.addEventListener('keydown', (event) => { if (event.key === 'Escape' && drag) endDrag({}, true); });
    window.addEventListener('beforeunload', (event) => { if (itemDirty || menuDirty) { event.preventDefault(); event.returnValue = ''; } });

    root.querySelectorAll('[data-menu-language]').forEach((link) => link.addEventListener('click', (event) => {
        if (busy || !discardChanges()) { event.preventDefault(); return; }
    }));

    selector.replaceChildren(new Option(menus.length ? 'Menü seçin' : 'Henüz menü yok', ''));
    menus.forEach((menu) => selector.add(new Option(`${menu.name}${menu.translation_exists ? '' : ' · Çeviri eklenmedi'} · ${menu.key}`, String(menu.id))));
    syncSummary();
    const initial = menus.find((menu) => menu.id === config.initialMenu) ?? menus[0];
    if (initial) perform(async () => { await loadMenu(initial, config.initialItem); if (config.newMenu && config.permissions.create) openMenuForm(true); });
    else if (config.permissions.create) openMenuForm(true);
}
