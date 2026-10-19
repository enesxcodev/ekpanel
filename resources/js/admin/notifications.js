import DataTable from 'datatables.net-bs5';
import $ from 'jquery';
import { notify } from './crud-notification';

async function mutate(url, payload) {
    const response = await fetch(url, {
        method: 'PATCH',
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify(payload),
        signal: AbortSignal.timeout(5000),
    });
    const result = await response.json();
    if (!response.ok) {
        throw new Error(Object.values(result.errors ?? {}).flat()[0] ?? result.message ?? 'Bildirim güncellenemedi.');
    }
    return result;
}

export function initializeNotifications() {
    const header = document.querySelector('[data-notification-header]');
    const tableElement = document.getElementById('notifications-table');
    const table = tableElement && DataTable.isDataTable(tableElement) ? new DataTable(tableElement) : null;
    let headerRequest = 0;

    async function refreshHeader() {
        if (!header) return;
        const request = ++headerRequest;
        const response = await fetch(header.dataset.notificationHeader, { headers: { Accept: 'application/json' }, cache: 'no-store' });
        if (!response.ok) throw new Error('Bildirimler yenilenemedi.');
        const result = await response.json();
        if (request !== headerRequest) return;
        header.querySelector('#admin-notifications').innerHTML = result.html;
        const count = header.querySelector('[data-notification-count]');
        count.textContent = result.count;
        count.hidden = result.count === 0;
        header.querySelector('#admin-notifications-toggle').setAttribute('aria-label', `Bildirimler, ${result.count} okunmamış`);
    }

    header?.addEventListener('show.bs.dropdown', () => {
        refreshHeader().catch(() => notify({ type: 'error', message: 'Bildirimler yenilenemedi. Tekrar açarak deneyebilirsiniz.' }));
    });

    document.addEventListener('click', (event) => {
        const link = event.target.closest('[data-notification-open]');
        if (!link || event.defaultPrevented || event.button !== 0) return;
        const read = mutate(link.dataset.readUrl, {}).catch(() => {});
        if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        event.preventDefault();
        read.finally(() => window.location.assign(link.href));
    });

    document.querySelectorAll('[data-notification-read-url]').forEach((element) => {
        mutate(element.dataset.notificationReadUrl, {})
            .then(() => refreshHeader())
            .catch(() => notify({ type: 'error', message: 'Okundu durumu kaydedilemedi. Sayfayı yenileyerek tekrar deneyebilirsiniz.' }));
    });

    document.addEventListener('submit', async (event) => {
        const form = event.target.closest('[data-notification-action]');
        if (!form) return;
        event.preventDefault();
        const button = form.querySelector('button');
        button.disabled = true;
        try {
            const input = new FormData(form);
            const result = await mutate(form.getAttribute('action'), { action: input.get('action'), scope: 'all' });
            notify({ type: 'success', message: result.message });
            table?.ajax.reload();
            await refreshHeader();
        } catch (error) {
            notify({ type: 'error', message: error.message });
        } finally {
            button.disabled = false;
        }
    });

    if (!table) return;
    const page = document.querySelector('[data-notifications-page]');
    const selectPage = page.querySelector('[data-notification-select-page]');
    const bulk = page.querySelector('[data-notification-bulk]');
    const filter = page.querySelector('[data-table-filter="filter"]');
    let loading = false;
    const checkboxes = () => [...tableElement.querySelectorAll('[data-notification-select]')];
    const selected = () => checkboxes().filter((input) => input.checked).map((input) => input.value);

    function updateSelection() {
        const count = selected().length;
        const size = checkboxes().length;
        selectPage.checked = size > 0 && count === size;
        selectPage.indeterminate = count > 0 && count < size;
        selectPage.disabled = loading || size === 0;
        page.querySelector('[data-notification-selection-status]').textContent = `${count} bildirim seçildi. Seçim yalnız mevcut sayfayı kapsar.`;
        bulk.querySelectorAll('button').forEach((button) => { button.disabled = loading || count === 0; });
    }

    function clearSelection() {
        checkboxes().forEach((input) => { input.checked = false; });
        updateSelection();
    }

    $(tableElement).on('preXhr.dt', () => {
        loading = true;
        checkboxes().forEach((input) => { input.disabled = true; });
        clearSelection();
    }).on('draw.dt', () => {
        loading = false;
        checkboxes().forEach((input) => { input.disabled = false; });
        clearSelection();
    });
    selectPage.addEventListener('change', () => {
        checkboxes().forEach((input) => { input.checked = selectPage.checked; });
        updateSelection();
    });
    tableElement.addEventListener('change', updateSelection);
    filter.addEventListener('change', () => {
        clearSelection();
        page.querySelector('[data-notification-archive]').hidden = filter.value === 'archive';
        page.querySelector('[data-notification-restore]').hidden = filter.value !== 'archive';
        const url = new URL(window.location.href);
        url.searchParams.set('filter', filter.value);
        history.replaceState(null, '', url);
    });
    bulk.addEventListener('submit', async (event) => {
        event.preventDefault();
        const ids = selected();
        if (!ids.length || !event.submitter) return;
        bulk.querySelectorAll('button').forEach((button) => { button.disabled = true; });
        try {
            const result = await mutate(bulk.getAttribute('action'), { action: event.submitter.value, scope: 'selected', ids });
            notify({ type: 'success', message: result.message });
            clearSelection();
            table.ajax.reload();
            await refreshHeader();
        } catch (error) {
            notify({ type: 'error', message: error.message });
        } finally {
            updateSelection();
        }
    });
    updateSelection();
}
