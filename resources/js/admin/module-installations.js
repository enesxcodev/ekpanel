const statusLabels = {
    queued: 'Sırada',
    running: 'İşleniyor',
    succeeded: 'Tamamlandı',
    failed: 'Başarısız',
    skipped: 'Atlandı',
};

export function initializeModuleInstallations() {
    const card = document.querySelector('[data-module-installation]');
    if (!card) return;

    const element = (name) => card.querySelector(`[data-installation-${name}]`);
    const active = () => ['queued', 'running'].includes(card.dataset.status);
    let timer;
    let checking = false;

    function render(installation) {
        card.dataset.status = installation.status;
        element('status').textContent = statusLabels[installation.status] ?? installation.status;
        element('stage').textContent = installation.stage ?? '';
        const numericPercentage = Number(installation.percentage);
        const percentage = Math.max(0, Math.min(installation.status === 'succeeded' ? 100 : 99, Number.isFinite(numericPercentage) ? numericPercentage : 0));
        element('percentage').textContent = `%${percentage}`;
        element('bar').style.width = `${percentage}%`;
        element('progress').setAttribute('aria-valuenow', String(percentage));
        element('wait').hidden = installation.status !== 'queued';
        element('error').textContent = installation.error ?? '';
        element('error').hidden = !installation.error;
        element('updated').textContent = installation.updated_at ?? '';

        const rows = (installation.results ?? []).map((result) => {
            const row = document.createElement('div');
            row.className = 'list-group-item';
            const name = document.createElement('strong');
            name.textContent = result.name;
            const status = document.createElement('span');
            status.className = 'ms-2';
            status.textContent = statusLabels[result.status] ?? result.status;
            const stage = document.createElement('div');
            stage.className = 'text-secondary';
            stage.textContent = result.stage ?? '';
            row.append(name, status, stage);
            if (result.error) {
                const error = document.createElement('div');
                error.className = 'text-danger';
                error.textContent = result.error;
                row.append(error);
            }
            return row;
        });
        element('results').replaceChildren(...rows);
    }

    async function check() {
        if (checking || !active()) return;
        clearTimeout(timer);
        checking = true;
        try {
            const response = await fetch(card.dataset.statusUrl, {
                headers: { Accept: 'application/json' },
                cache: 'no-store',
                signal: AbortSignal.timeout(15000),
            });
            if (!response.ok) throw new Error('Kurulum durumu alınamadı.');
            render(await response.json());
            element('network').hidden = true;
            element('retry').hidden = true;
        } catch {
            element('network').textContent = 'Güncel durum alınamadı. Son bilinen durum gösteriliyor; 2 saniye sonra yeniden kontrol edilecek.';
            element('network').hidden = false;
            element('retry').hidden = false;
        } finally {
            checking = false;
            if (active()) timer = setTimeout(check, 2000);
        }
    }

    element('retry').addEventListener('click', check);
    if (active()) timer = setTimeout(check, 2000);
}
