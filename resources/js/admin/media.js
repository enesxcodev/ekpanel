import { Modal } from '@tabler/core';
import { notify } from './crud-notification';

const instances = new WeakMap();
const numberFormatter = new Intl.NumberFormat('tr-TR', { maximumFractionDigits: 1 });
const dateFormatter = new Intl.DateTimeFormat('tr-TR', { dateStyle: 'short' });
function sizeLabel(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1048576) return numberFormatter.format(bytes / 1024) + ' KB';
    return numberFormatter.format(bytes / 1048576) + ' MB';
}
function element(tag, className = '', text = '') {
    const node = document.createElement(tag);
    node.className = className; node.textContent = text;
    return node;
}
async function request(url, method = 'GET', data = null, signal = undefined) {
    const headers = { Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content };
    if (data !== null) headers['Content-Type'] = 'application/json';
    const response = await fetch(url, { method, headers, body: data === null ? null : JSON.stringify(data), signal, credentials: 'same-origin' });
    let result;
    try { result = await response.json(); } catch { throw new Error('Sunucu yanıtı okunamadı. Oturumunuzu ve yükleme sınırını kontrol edin.'); }
    if (!response.ok) throw new Error(result.errors ? Object.values(result.errors).flat().join(' ') : result.message || 'İşlem tamamlanamadı.');
    return result;
}
class MediaBrowser {
    constructor(root) {
        this.root = root;
        this.config = JSON.parse(root.dataset.config);
        this.isPicker = root.dataset.picker === 'true';
        this.state = { folder: null, view: 'all', kind: '', search: '', sort: 'created_at', direction: 'desc', page: 1, layout: 'grid' };
        this.files = []; this.folders = []; this.selected = new Map(); this.focused = null;
        this.multiple = !this.isPicker; this.allowedExtensions = [];
        this.get('[data-media-limit]').textContent = 'Dosya başına en fazla ' + sizeLabel(this.config.maxBytes);
        root.addEventListener('click', event => this.onClick(event));
        root.addEventListener('change', event => this.onChange(event));
        root.addEventListener('input', event => {
            if (!event.target.matches('[data-media-search]')) return;
            clearTimeout(this.searchTimer);
            this.searchTimer = setTimeout(() => this.navigate({ search: event.target.value }), 300);
        });
        this.get('[data-media-dialog-form]').addEventListener('submit', event => this.submitDialog(event));
        this.initializeDrop();
        document.addEventListener('media:changed', event => {
            if (event.detail !== this && (!this.isPicker || root.closest('.modal')?.classList.contains('show'))) this.load();
        });
        if (!this.isPicker) this.load();
    }
    get(selector) { return this.root.querySelector(selector); }
    all(selector) { return [...this.root.querySelectorAll(selector)]; }
    clone(name) { return this.get('[data-media-template="' + name + '"]').content.firstElementChild.cloneNode(true); }
    endpoint(name, id) { return this.config.endpoints[name].replace('__ID__', String(id)); }
    showError(message = '') {
        const box = this.get('[data-media-error]');
        box.textContent = message; box.classList.toggle('d-none', !message);
    }
    async load() {
        this.abort?.abort(); this.abort = new AbortController();
        const signal = this.abort.signal;
        const query = new URLSearchParams({ view: this.state.view, sort: this.state.sort, direction: this.state.direction, page: this.state.page });
        if (this.state.folder !== null && this.state.view === 'all') query.set('folder_id', this.state.folder);
        if (this.state.kind) query.set('kind', this.state.kind);
        if (this.state.search) query.set('search', this.state.search);
        this.allowedExtensions.forEach((ext, i) => query.set('extensions[' + i + ']', ext));
        this.get('[data-media-loading]').classList.remove('d-none');
        this.root.setAttribute('aria-busy', 'true'); this.showError();
        try {
            const data = await request(this.config.endpoints.browse + '?' + query, 'GET', null, signal);
            if (signal.aborted) return;
            if (data.pagination.page > data.pagination.last_page) { this.state.page = data.pagination.last_page; return this.load(); }
            this.files = data.files; this.folders = data.folders; this.pagination = data.pagination;
            this.render();
        } catch (error) { if (error.name !== 'AbortError') this.showError(error.message); }
        finally {
            if (!signal.aborted) {
                this.get('[data-media-loading]').classList.add('d-none');
                this.root.setAttribute('aria-busy', 'false');
                this.root.dispatchEvent(new CustomEvent('media:selection', { bubbles: true, detail: [...this.selected.values()] }));
            }
        }
    }
    navigate(changes = {}) {
        Object.assign(this.state, changes, { page: 1 });
        this.selected.clear(); this.focused = null; this.updateSelection(); this.load();
    }
    async onClick(event) {
        const button = event.target.closest('[data-action]');
        if (!button || !this.root.contains(button)) return;
        const action = button.dataset.action;
        const fileId = button.closest('[data-media-item]')?.dataset.id;
        const folderId = button.closest('[data-media-folder]')?.dataset.id;
        const file = fileId ? this.files.find(item => item.id === Number(fileId)) : this.focused;
        try {
            switch (action) {
                case 'upload': this.get('[data-media-upload]').click(); break;
                case 'refresh': await this.load(); break;
                case 'sync':
                    button.disabled = true;
                    try {
                        const result = await request(this.config.endpoints.sync, 'POST');
                        notify({ type: 'success', message: result.message }); await this.changed();
                    } finally { button.disabled = false; }
                    break;
                case 'view': this.navigate({ folder: null, view: button.dataset.value }); break;
                case 'open-folder':
                    event.preventDefault();
                    this.navigate({ folder: Number(button.dataset.id || folderId), view: 'all', search: '' });
                    this.get('[data-media-search]').value = ''; break;
                case 'root': this.navigate({ folder: null, view: 'all' }); break;
                case 'direction': this.state.direction = button.dataset.value; this.state.page = 1; await this.load(); break;
                case 'layout': this.state.layout = button.dataset.value; this.renderFiles(); this.renderControls(); break;
                case 'sort-column':
                    if (this.state.sort === button.dataset.value) this.state.direction = this.state.direction === 'asc' ? 'desc' : 'asc';
                    else { this.state.sort = button.dataset.value; this.state.direction = 'asc'; }
                    this.state.page = 1; await this.load(); break;
                case 'page': this.state.page = Number(button.dataset.value); await this.load(); break;
                case 'select-file': this.select(file, this.multiple && (event.ctrlKey || event.metaKey)); break;
                case 'clear-selection': this.selected.clear(); this.focused = null; this.updateSelection(); break;
                case 'close-detail': this.focused = null; this.renderDetail(); break;
                case 'favorite':
                    if (!file) return;
                    await request(this.endpoint('favorite', file.id), 'PUT', { favorite: !file.is_favorite }); await this.changed(); break;
                case 'copy':
                    if (!file) return;
                    try { await navigator.clipboard.writeText(new URL(file.url, window.location.origin).href); notify({ type: 'success', message: 'Dosya bağlantısı kopyalandı.' }); }
                    catch { this.openDialog('copy', file); }
                    break;
                case 'save-alt':
                    if (!file) return;
                    button.disabled = true;
                    try {
                        const result = await request(this.endpoint('file', file.id), 'PATCH', { alt_text: this.get('[data-media-alt]').value });
                        Object.assign(file, result.file, { is_favorite: file.is_favorite }); notify({ type: 'success', message: result.message });
                    } finally { button.disabled = false; } break;
                case 'new-folder': this.openDialog('new-folder'); break;
                case 'rename-folder': case 'move-folder': case 'delete-folder':
                    this.openDialog(action, this.folders.find(item => item.id === Number(folderId))); break;
                case 'rename': case 'move': case 'trash': if (file) this.openDialog(action, file); break;
                case 'trash-selected': this.openDialog('bulk-trash'); break;
                case 'cancel-dialog': this.get('[data-media-dialog]').close(); break;
            }
        } catch (error) { this.showError(error.message); }
    }
    onChange(event) {
        if (event.target.matches('[data-media-filter]')) this.navigate({ kind: event.target.value });
        if (event.target.matches('[data-media-sort]')) { this.state.sort = event.target.value; this.state.page = 1; this.load(); }
        if (event.target.matches('[data-media-check]')) {
            const id = Number(event.target.closest('[data-media-item]').dataset.id);
            this.select(this.files.find(file => file.id === id), this.multiple, true, event.target.checked);
        }
        if (event.target.matches('[data-media-upload]')) { this.uploadFiles([...event.target.files]); event.target.value = ''; }
    }
    select(file, additive = false, checkbox = false, checked = true) {
        if (!file || (this.isPicker && (file.trashed || (this.allowedExtensions.length && !this.allowedExtensions.includes(file.extension.toLowerCase()))))) return;
        if (!additive) this.selected.clear();
        if (checkbox ? !checked : additive && this.selected.has(file.id)) this.selected.delete(file.id);
        else this.selected.set(file.id, file);
        this.focused = file; this.updateSelection();
    }
    updateSelection() {
        this.all('[data-media-item]').forEach(node => {
            const active = this.selected.has(Number(node.dataset.id));
            node.classList.toggle('is-selected', active); node.querySelector('[data-media-check]').checked = active;
        });
        this.get('[data-media-selection-bar]').classList.toggle('d-none', !this.selected.size || this.isPicker);
        this.get('[data-media-selection-count]').textContent = this.selected.size + ' dosya seçildi';
        this.renderDetail();
        this.root.dispatchEvent(new CustomEvent('media:selection', { bubbles: true, detail: [...this.selected.values()] }));
    }
    render() {
        this.files.forEach(file => { if (this.selected.has(file.id)) this.selected.set(file.id, file); });
        if (this.focused) this.focused = this.files.find(file => file.id === this.focused.id) || null;
        this.renderTree(); this.renderControls(); this.renderFiles(); this.renderPagination(); this.updateSelection();
    }
    renderControls() {
        this.all('[data-action="view"]').forEach(button => {
            const active = button.dataset.value === this.state.view && this.state.folder === null;
            button.classList.toggle('active', active); button.setAttribute('aria-current', active ? 'page' : 'false');
            if (this.isPicker && button.dataset.value === 'trash') button.classList.add('d-none');
        });
        for (const action of ['direction', 'layout']) this.all('[data-action="' + action + '"]').forEach(button => {
            const active = button.dataset.value === this.state[action];
            button.classList.toggle('active', active); button.setAttribute('aria-pressed', String(active));
        });
        this.all('[data-sort-marker]').forEach(node => { node.textContent = this.state.sort === node.dataset.sortMarker ? (this.state.direction === 'asc' ? '↑' : '↓') : ''; });
        this.get('[data-media-sort]').value = this.state.sort; this.get('[data-media-filter]').value = this.state.kind;
        this.get('[data-media-filter]').disabled = Boolean(this.lockedKind);
        const breadcrumb = this.get('[data-media-breadcrumb]'); breadcrumb.replaceChildren();
        const home = element('button', '', 'Tüm dosyalar'); home.type = 'button'; home.dataset.action = 'root'; breadcrumb.append(home);
        const trail = []; const seen = new Set();
        let folder = this.folders.find(item => item.id === this.state.folder);
        while (folder && !seen.has(folder.id)) { seen.add(folder.id); trail.unshift(folder); folder = this.folders.find(item => item.id === folder.parent_id); }
        for (const item of trail) {
            const button = element('button', '', item.name); button.type = 'button'; button.dataset.action = 'open-folder'; button.dataset.id = item.id;
            breadcrumb.append(element('span', 'text-secondary', '/'), button);
        }
        if (this.state.view !== 'all') breadcrumb.append(element('span', 'text-secondary', '/'), element('span', 'fw-semibold', { recent: 'Son yüklenenler', favorites: 'Favoriler', trash: 'Çöp kutusu' }[this.state.view]));
    }
    renderTree() {
        const tree = this.get('[data-media-tree]');
        const closed = new Set([...tree.querySelectorAll('details:not([open])')].map(node => Number(node.dataset.id)));
        tree.replaceChildren();
        const renderChildren = (parent, target, ancestors = new Set()) => {
            for (const folder of this.folders.filter(item => item.parent_id === parent)) {
                if (ancestors.has(folder.id)) continue;
                const entry = element('details', 'media-tree-entry'); entry.dataset.id = folder.id; entry.open = !closed.has(folder.id);
                const summary = element('summary');
                const button = element('button', 'media-nav-item' + (this.state.folder === folder.id ? ' active' : ''));
                button.type = 'button'; button.dataset.action = 'open-folder'; button.dataset.id = folder.id; button.title = folder.name;
                button.append(this.clone('folder-icon'), element('span', '', folder.name)); summary.append(button); entry.append(summary);
                const children = element('div'); renderChildren(folder.id, children, new Set([...ancestors, folder.id]));
                if (children.children.length) entry.append(children);
                target.append(entry);
            }
        };
        renderChildren(null, tree);
        if (!tree.children.length) tree.append(element('p', 'text-secondary small px-2', 'Henüz klasör yok.'));
    }

    renderFiles() {
        const grid = this.get('[data-media-grid]'); const rows = this.get('[data-media-rows]');
        grid.replaceChildren(); rows.replaceChildren();
        grid.classList.toggle('d-none', this.state.layout !== 'grid'); this.get('[data-media-table]').classList.toggle('d-none', this.state.layout !== 'list');
        const folders = (this.state.view === 'all' && this.state.page === 1 ? this.folders.filter(folder => folder.parent_id === this.state.folder && folder.name.toLocaleLowerCase('tr').includes(this.state.search.toLocaleLowerCase('tr'))) : []).sort((a, b) => a.name.localeCompare(b.name, 'tr'));
        for (const folder of folders) {
            for (const [template, target] of [['folder', grid], ['folder-row', rows]]) {
                const node = this.clone(template); node.dataset.id = folder.id; node.querySelector('[data-media-name]').textContent = folder.name;
                const actions = [];
                if (this.config.permissions.update) actions.push(['rename-folder', 'Yeniden adlandır'], ['move-folder', 'Taşı']);
                if (this.config.permissions.delete) actions.push(['delete-folder', 'Boş klasörü sil']);
                this.renderMenu(node, actions); target.append(node);
            }
        }
        for (const file of this.files) {
            for (const [template, target] of [['card', grid], ['row', rows]]) {
                const node = this.clone(template); node.dataset.id = file.id;
                node.querySelector('[data-media-check]').setAttribute('aria-label', file.name + ' dosyasını seç');
                node.querySelector('[data-media-name]').textContent = file.name; node.querySelector('[data-media-name]').title = file.name;
                node.querySelector('[data-media-size]').textContent = sizeLabel(file.size);
                const badge = node.querySelector('[data-media-extension]'); badge.textContent = file.extension;
                badge.classList.add(file.kind === 'image' ? 'bg-blue-lt' : file.kind === 'video' ? 'bg-purple-lt' : file.extension === 'PDF' ? 'bg-red-lt' : 'bg-orange-lt');
                this.thumbnail(node.querySelector('[data-media-thumbnail]'), file);
                const date = node.querySelector('[data-media-date]'); if (date) date.textContent = dateFormatter.format(new Date(file.created_at));
                node.querySelector('.media-favorite-indicator')?.classList.toggle('d-none', !file.is_favorite);
                const actions = [['select-file', 'Seç / detayları göster']];
                if (!file.trashed) {
                    actions.push(['favorite', file.is_favorite ? 'Favorilerden kaldır' : 'Favorilere ekle'], ['copy', 'Bağlantıyı kopyala']);
                    if (this.config.permissions.update) actions.push(['rename', 'Yeniden adlandır'], ['move', 'Taşı']);
                }
                if (this.config.permissions.delete) actions.push(['trash', file.trashed ? 'Geri yükle' : 'Çöp kutusuna taşı']);
                this.renderMenu(node, actions); target.append(node);
            }
        }
        this.get('[data-media-empty]').classList.toggle('d-none', Boolean(this.files.length || folders.length));
        this.get('[data-media-empty-title]').textContent = this.state.search ? 'Sonuç bulunamadı' : this.state.view === 'trash' ? 'Çöp kutusu boş' : 'Bu görünümde dosya yok';
        this.get('[data-media-empty-text]').textContent = this.state.view === 'trash' ? 'Kaldırdığınız dosyalar burada görünür ve geri yüklenebilir.' : 'Başka bir klasör seçin veya yeni dosyalar yükleyin.';
        this.get('[data-media-total]').textContent = this.pagination.total + ' dosya' + (folders.length ? ' · ' + folders.length + ' klasör' : '');
        this.updateSelection();
    }
    renderMenu(node, actions) {
        const menu = node.querySelector('[data-media-menu]');
        for (const [action, label] of actions) {
            const button = element('button', 'dropdown-item' + (action.includes('trash') || action.includes('delete') ? ' text-danger' : ''), label);
            button.type = 'button'; button.dataset.action = action; menu.append(button);
        }
        if (!actions.length) menu.parentElement.classList.add('d-none');
    }
    thumbnail(container, file, large = false) {
        container.replaceChildren(); container.classList.toggle('is-transparent', file.extension === 'PNG');
        if (file.kind === 'image') {
            const image = element('img'); image.src = file.url; image.alt = file.alt_text || file.name; image.loading = 'lazy'; image.decoding = 'async';
            image.addEventListener('error', () => { image.remove(); container.append(element('span', 'text-secondary small', 'Önizleme yok')); }, { once: true });
            container.append(image);
        } else if (file.kind === 'video' && large) {
            const video = element('video'); video.controls = true; video.preload = 'metadata'; video.src = file.url; container.append(video);
        } else {
            const icon = element('span', 'media-document-thumb ' + (file.kind === 'video' ? 'text-purple' : file.extension === 'PDF' ? 'text-danger' : 'text-secondary'));
            icon.append(this.clone('file-icon'), element('strong', '', file.extension)); container.append(icon);
        }
    }
    renderDetail() {
        const file = this.focused;
        this.get('[data-media-detail-placeholder]').classList.toggle('d-none', Boolean(file)); this.get('[data-media-detail]').classList.toggle('d-none', !file);
        if (!file) { this.get('[data-media-preview]').replaceChildren(); return; }
        this.thumbnail(this.get('[data-media-preview]'), file, true);
        const values = { name: file.name, extension: file.extension, size: sizeLabel(file.size), dimensions: file.width ? file.width + ' × ' + file.height : '—', date: dateFormatter.format(new Date(file.created_at)) };
        for (const [key, value] of Object.entries(values)) this.get('[data-detail="' + key + '"]').textContent = value;
        this.get('[data-media-alt]').value = file.alt_text;
        this.get('[data-media-alt]').disabled = !this.config.permissions.update || file.trashed || file.kind !== 'image';
        this.get('[data-media-download]').href = file.download_url; this.get('[data-media-download]').classList.toggle('d-none', file.trashed);
        this.get('[data-media-favorite-label]').textContent = file.is_favorite ? 'Favorilerden kaldır' : 'Favorilere ekle';
        const usages = this.get('[data-media-usages]'); usages.replaceChildren();
        for (const usage of file.usages || []) {
            const link = element(usage.url ? 'a' : 'span', usage.url ? 'text-primary' : 'text-secondary', usage.label);
            if (usage.url) link.href = usage.url;
            usages.append(link);
        }
        if (!usages.children.length) usages.append(element('span', 'text-secondary', 'Kayıtlı kullanım bulunmuyor.'));
        this.get('[data-media-trash-label]')?.replaceChildren(document.createTextNode(file.trashed ? 'Geri yükle' : 'Çöp kutusuna taşı'));
        for (const action of ['save-alt', 'favorite', 'rename', 'move', 'copy']) this.get('[data-action="' + action + '"]')?.classList.toggle('d-none', file.trashed || (action === 'save-alt' && file.kind !== 'image'));
    }
    renderPagination() {
        const nav = this.get('[data-media-pagination]'); nav.replaceChildren();
        const { page, last_page: last } = this.pagination; if (last <= 1) return;
        const pages = [...new Set([1, ...Array.from({ length: 5 }, (_, i) => page - 2 + i).filter(value => value > 0 && value <= last), last])].sort((a, b) => a - b);
        for (const value of pages) {
            const button = element('button', 'btn btn-sm' + (value === page ? ' btn-primary' : ''), String(value));
            button.type = 'button'; button.dataset.action = 'page'; button.dataset.value = value; button.setAttribute('aria-label', 'Sayfa ' + value);
            if (value === page) button.setAttribute('aria-current', 'page'); nav.append(button);
        }
    }
    folderLabel(id, seen = new Set()) {
        if (!id || seen.has(id)) return '';
        const folder = this.folders.find(item => item.id === id); if (!folder) return '';
        const parent = this.folderLabel(folder.parent_id, new Set([...seen, id]));
        return (parent ? parent + ' / ' : '') + folder.name;
    }
    openDialog(action, item = null) {
        const titles = { 'new-folder': 'Yeni klasör', 'rename-folder': 'Klasörü yeniden adlandır', 'move-folder': 'Klasörü taşı', 'delete-folder': 'Boş klasörü sil', rename: 'Dosyayı yeniden adlandır', move: 'Dosyayı taşı', trash: item?.trashed ? 'Dosyayı geri yükle' : 'Çöp kutusuna taşı', 'bulk-trash': 'Seçilen dosyaları kaldır', copy: 'Dosya bağlantısı' };
        this.dialogAction = action; this.dialogItem = item; this.get('[data-media-dialog-title]').textContent = titles[action];
        const name = this.get('[data-media-dialog-name]');
        name.value = action === 'copy' ? new URL(item.url, window.location.origin).href : action === 'new-folder' ? '' : item?.name || '';
        name.readOnly = action === 'copy'; name.required = ['new-folder', 'rename-folder', 'rename'].includes(action);
        this.get('[data-media-dialog-name-label]').classList.toggle('d-none', !['new-folder', 'rename-folder', 'rename', 'copy'].includes(action));
        this.get('[data-media-dialog-folder-label]').classList.toggle('d-none', !['new-folder', 'move', 'move-folder'].includes(action));
        const folders = this.get('[data-media-dialog-folder]'); folders.replaceChildren();
        const root = element('option', '', 'Tüm dosyalar (kök)'); root.value = ''; folders.append(root);
        const excluded = new Set();
        if (action === 'move-folder') {
            const exclude = id => { excluded.add(id); this.folders.filter(folder => folder.parent_id === id && !excluded.has(folder.id)).forEach(folder => exclude(folder.id)); };
            exclude(item.id);
        }
        for (const folder of this.folders) {
            if (excluded.has(folder.id)) continue;
            const option = element('option', '', this.folderLabel(folder.id)); option.value = folder.id; folders.append(option);
        }
        folders.value = String(action === 'move' ? item.folder_id || '' : action === 'move-folder' ? item.parent_id || '' : this.state.folder || '');
        this.get('[data-media-dialog-description]').textContent = action === 'trash' || action === 'bulk-trash'
            ? 'Dosya kütüphanede gizlenir ve çöp kutusundan geri yüklenebilir. Mevcut içerik bağlantıları korunur.'
            : action === 'delete-folder' ? 'Yalnızca boş klasörler silinebilir.'
            : action === 'move' || action === 'move-folder' ? 'Taşıma işlemi dosyaların bağlantılarını değiştirmez.' : '';
        this.get('[data-media-dialog-error]').classList.add('d-none');
        this.get('[data-media-dialog-submit]').textContent = action === 'copy' ? 'Tamam' : action === 'trash' && item.trashed ? 'Geri yükle' : action.includes('trash') || action.includes('delete') ? 'Onayla' : 'Kaydet';
        this.get('[data-media-dialog]').showModal();
        if (action === 'copy') { name.focus(); name.select(); }
    }
    async submitDialog(event) {
        event.preventDefault();
        const action = this.dialogAction, item = this.dialogItem;
        const name = this.get('[data-media-dialog-name]').value, folderId = Number(this.get('[data-media-dialog-folder]').value) || null;
        const button = this.get('[data-media-dialog-submit]');
        if (action === 'copy') { this.get('[data-media-dialog]').close(); return; }
        button.disabled = true;
        try {
            let result;
            if (action === 'new-folder') result = await request(this.config.endpoints.folders, 'POST', { name, parent_id: folderId });
            if (action === 'rename-folder' || action === 'move-folder') result = await request(this.endpoint('folder', item.id), 'PATCH', { name: action === 'rename-folder' ? name : item.name, parent_id: action === 'move-folder' ? folderId : item.parent_id });
            if (action === 'delete-folder') result = await request(this.endpoint('folder', item.id), 'DELETE');
            if (action === 'rename' || action === 'move') result = await request(this.endpoint('file', item.id), 'PATCH', action === 'rename' ? { name } : { folder_id: folderId });
            if (action === 'trash') result = await request(this.endpoint(item.trashed ? 'restore' : 'file', item.id), item.trashed ? 'POST' : 'DELETE');
            if (action === 'bulk-trash') {
                let completed = 0;
                try {
                    for (const file of this.selected.values()) { await request(this.endpoint(file.trashed ? 'restore' : 'file', file.id), file.trashed ? 'POST' : 'DELETE'); completed++; }
                } finally { if (completed) { this.selected.clear(); this.focused = null; await this.changed(); } }
                result = { message: completed + ' dosya işlendi.' };
            }
            this.get('[data-media-dialog]').close(); this.selected.clear(); this.focused = null;
            if (result?.message) notify({ type: 'success', message: result.message });
            await this.changed();
        } catch (error) {
            const box = this.get('[data-media-dialog-error]'); box.textContent = error.message; box.classList.remove('d-none');
        } finally { button.disabled = false; }
    }
    async changed() {
        await this.load(); document.dispatchEvent(new CustomEvent('media:changed', { detail: this }));
    }
    initializeDrop() {
        const drop = this.get('[data-media-drop]'); let entered = 0;
        drop.addEventListener('dragenter', event => { if (!this.config.permissions.create || !event.dataTransfer.types.includes('Files')) return; event.preventDefault(); entered++; drop.classList.add('is-dragging'); });
        drop.addEventListener('dragover', event => { if (this.config.permissions.create) event.preventDefault(); });
        drop.addEventListener('dragleave', () => { if (--entered <= 0) { entered = 0; drop.classList.remove('is-dragging'); } });
        drop.addEventListener('drop', event => { event.preventDefault(); entered = 0; drop.classList.remove('is-dragging'); if (this.config.permissions.create) this.uploadFiles([...event.dataTransfer.files]); });
    }
    async uploadFiles(files) {
        if (!files.length) return;
        if (this.uploading) { notify({ type: 'warning', message: 'Devam eden yüklemenin tamamlanmasını bekleyin.' }); return; }
        this.uploading = true;
        const queue = this.get('[data-media-queue]'); queue.replaceChildren(); queue.classList.remove('d-none');
        const folder = this.state.folder; let completed = 0;
        for (const file of files) {
            const item = element('div', 'media-upload-item'), heading = element('div', 'd-flex justify-content-between gap-3 small'), status = element('span', 'text-secondary', 'Bekliyor');
            heading.append(element('span', 'text-truncate', file.name), status);
            const progress = element('div', 'progress'), bar = element('div', 'progress-bar'); bar.style.width = '0%'; progress.append(bar); item.append(heading, progress); queue.append(item);
            try {
                const extension = file.name.split('.').pop().toLowerCase();
                if (!this.config.extensions.includes(extension)) throw new Error('Bu dosya uzantısı kabul edilmiyor.');
                if (file.size > this.config.maxBytes) throw new Error('Dosya sunucu sınırını aşıyor: ' + sizeLabel(this.config.maxBytes));
                status.textContent = 'Yükleniyor';
                await new Promise((resolve, reject) => {
                    const xhr = new XMLHttpRequest(); xhr.open('POST', this.config.endpoints.upload);
                    xhr.setRequestHeader('Accept', 'application/json'); xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content); xhr.timeout = 180000;
                    xhr.upload.addEventListener('progress', event => {
                        if (!event.lengthComputable) return;
                        const value = Math.round(event.loaded / event.total * 100); bar.style.width = value + '%'; status.textContent = value === 100 ? 'Doğrulanıyor…' : 'Yükleniyor %' + value;
                    });
                    xhr.onload = () => {
                        let data;
                        try { data = JSON.parse(xhr.responseText); } catch { reject(new Error('Sunucu yanıtı okunamadı. Yükleme sınırını veya oturumunuzu kontrol edin.')); return; }
                        if (xhr.status >= 200 && xhr.status < 300) resolve(data);
                        else reject(new Error(data.errors ? Object.values(data.errors).flat().join(' ') : data.message || 'Yükleme başarısız.'));
                    };
                    xhr.onerror = () => reject(new Error('Sunucuya bağlanılamadı.')); xhr.ontimeout = () => reject(new Error('Yükleme zaman aşımına uğradı.'));
                    const body = new FormData(); body.append('file', file); if (folder !== null) body.append('folder_id', folder); xhr.send(body);
                });
                completed++; status.textContent = 'Yüklendi'; status.className = 'text-success'; bar.style.width = '100%'; bar.classList.add('bg-success');
            } catch (error) { status.textContent = error.message; status.className = 'text-danger'; }
        }
        this.uploading = false;
        if (completed) {
            Object.assign(this.state, { view: 'all', folder, search: '', page: 1, sort: 'created_at', direction: 'desc' });
            this.get('[data-media-search]').value = '';
            await this.changed();
        }
    }
}

export function initializeMedia() {
    document.querySelectorAll('[data-media-browser]').forEach(root => { if (!instances.has(root)) instances.set(root, new MediaBrowser(root)); });
    const modal = document.querySelector('[data-media-modal]');
    if (!modal || modal.dataset.initialized) return;
    modal.dataset.initialized = 'true';
    const root = modal.querySelector('[data-media-browser]'), browser = instances.get(root);
    let trigger = null, files = [];
    const renderField = (field, selectedFiles) => {
        const preview = field.querySelector('[data-media-field-preview]'); preview.replaceChildren();
        selectedFiles.forEach(file => {
            if (file.kind === 'image') {
                const image = element('img'); image.src = file.url; image.alt = file.alt_text || file.name; preview.append(image);
            } else preview.append(element('span', 'badge bg-blue-lt', file.name));
        });
        field.querySelector('[data-media-clear]').classList.toggle('d-none', !selectedFiles.length);
    };
    document.querySelectorAll('[data-media-field]').forEach(async field => {
        if (field.dataset.mediaDisabled === 'true') return;
        const value = field.querySelector('[data-media-value]').value;
        if (!value) return;
        try {
            const parsed = JSON.parse(value);
            const ids = (Array.isArray(parsed) ? parsed : [parsed]).filter(id => Number.isInteger(Number(id)) && Number(id) > 0).slice(0, 50);
            const results = await Promise.all(ids.map(id => request(field.dataset.mediaFileEndpoint.replace('__ID__', id))));
            renderField(field, results.map(result => result.file));
        } catch { notify({ type: 'warning', message: 'Kayıtlı medya önizlemesi yüklenemedi.' }); }
    });
    root.addEventListener('media:selection', event => {
        files = event.detail;
        modal.querySelector('[data-media-modal-count]').textContent = files.length ? files.length + (browser.lockedKind === 'image' ? ' görsel seçildi' : ' dosya seçildi') : 'Dosya seçilmedi';
        modal.querySelector('[data-media-confirm]').disabled = !files.length || root.getAttribute('aria-busy') === 'true';
    });
    document.addEventListener('click', event => {
        const opener = event.target.closest('[data-media-open]');
        if (opener) {
            trigger = opener; browser.multiple = opener.dataset.mediaMultiple === 'true'; browser.lockedKind = opener.dataset.mediaKind || '';
            browser.allowedExtensions = (opener.dataset.mediaExtensions || '').split(',').map(value => value.trim().replace(/^\./, '').toLowerCase()).filter(Boolean);
            modal.querySelector('#media-picker-title').textContent = browser.lockedKind === 'image' ? 'Görsel seç' : 'Medya seç';
            modal.querySelector('[data-media-confirm]').textContent = browser.lockedKind === 'image' ? 'Görseli kullan' : 'Seçimi kullan';
            browser.get('[data-media-upload]').accept = browser.allowedExtensions.length ? browser.allowedExtensions.map(value => '.' + value).join(',') : browser.lockedKind === 'image' ? '.jpg,.jpeg,.png,.webp' : browser.config.extensions.map(value => '.' + value).join(',');
            browser.navigate({ view: 'all', kind: browser.lockedKind, search: '' }); browser.get('[data-media-search]').value = '';
            Modal.getOrCreateInstance(modal).show();
        }
        const clear = event.target.closest('[data-media-clear]');
        if (clear) {
            const field = clear.closest('[data-media-field]'); field.querySelector('[data-media-value]').value = '';
            const remove = field.querySelector('[data-media-remove]'); if (remove) remove.value = '1';
            field.querySelector('[data-media-value]').dispatchEvent(new Event('change', { bubbles: true }));
            field.querySelector('[data-media-field-preview]').replaceChildren(); clear.classList.add('d-none');
            field.dispatchEvent(new CustomEvent('media:clear', { bubbles: true }));
        }
    });
    modal.querySelector('[data-media-confirm]').addEventListener('click', () => {
        if (!trigger || !files.length || root.getAttribute('aria-busy') === 'true') return;
        const field = trigger.closest('[data-media-field]');
        if (field) {
            const remove = field.querySelector('[data-media-remove]'); if (remove) remove.value = '0';
            const value = field.querySelector('[data-media-value]'); value.value = browser.multiple ? JSON.stringify(files.map(file => file.id)) : files[0].id;
            value.dispatchEvent(new Event('change', { bubbles: true }));
            renderField(field, files);
        }
        trigger.dispatchEvent(new CustomEvent('media:selected', { bubbles: true, detail: { files: [...files], ids: files.map(file => file.id) } }));
        Modal.getOrCreateInstance(modal).hide();
    });
    modal.addEventListener('hidden.bs.modal', () => { browser.get('[data-media-preview]').querySelector('video')?.pause(); trigger?.focus(); });
}

