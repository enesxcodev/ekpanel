export function initializeMenus() {
    document.querySelectorAll('[data-menu-item-form]').forEach((form) => {
        const target = form.querySelector('[data-menu-target]');
        const source = form.querySelector('[data-menu-source]');
        const sourceId = form.querySelector('[data-menu-source-id]');
        const label = form.querySelector('[data-menu-label]');
        const dynamic = form.querySelector('[data-menu-dynamic]');
        const dynamicFields = form.querySelector('[data-menu-dynamic-fields]');
        const status = form.querySelector('[data-menu-source-status]');
        if (!target || !source || !sourceId || !label || !dynamic || !dynamicFields || !status) {
            return;
        }

        const sourceGroups = new Map(
            [...sourceId.querySelectorAll('[data-menu-source-group]')]
                .map((group) => [group.dataset.menuSourceGroup, group.cloneNode(true)]),
        );
        let currentSource = source.value;

        const updateDynamic = () => {
            const supported = target.value === 'content'
                && source.selectedOptions[0]?.dataset.supportsChildren === '1';
            dynamicFields.hidden = !supported;
            dynamic.disabled = !supported;
            if (!supported) {
                dynamic.checked = false;
            }
        };
        const updateTarget = () => {
            form.querySelectorAll('[data-menu-fields]').forEach((fields) => {
                const active = fields.dataset.menuFields.split(' ').includes(target.value);
                fields.hidden = !active;
                fields.querySelectorAll('[data-menu-required]').forEach((input) => {
                    const requiredTargets = input.dataset.menuRequired.split(' ').filter(Boolean);
                    input.required = active && (requiredTargets.length === 0 || requiredTargets.includes(target.value));
                });
            });
            updateDynamic();
        };
        const updateSource = () => {
            const selectedValue = source.value === currentSource ? sourceId.value : '';
            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = 'İçerik seçin';
            sourceId.replaceChildren(placeholder);
            const group = sourceGroups.get(source.value);
            if (group) {
                sourceId.append(group.cloneNode(true));
            }
            sourceId.value = selectedValue;
            if (sourceId.selectedIndex < 0) {
                sourceId.value = '';
            }
            const hasOptions = sourceId.options.length > 1;
            status.textContent = source.value === ''
                ? 'Önce bir içerik kaynağı seçin.'
                : hasOptions
                    ? 'Bağlantı adresi bulunmayan içerikler yayınlanan menüde bağlantıya dönüşmez.'
                    : 'Bu kaynakta seçilebilir kayıt yok. Kaynak modülün etkinliğini ve içeriklerini kontrol edin.';
            currentSource = source.value;
            updateDynamic();
        };

        target.addEventListener('change', updateTarget);
        source.addEventListener('change', updateSource);
        sourceId.addEventListener('change', () => {
            if (label.value.trim() === '' && sourceId.value !== '') {
                label.value = sourceId.selectedOptions[0]?.textContent.trim() ?? '';
            }
        });
        updateSource();
        updateTarget();
    });
}
