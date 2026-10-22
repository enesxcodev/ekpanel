export default function initialize() {
    document.querySelectorAll('[data-branches-form]').forEach((form) => {
        const $ = window.$;
        const selects = [...form.querySelectorAll('[data-location-select]')];
        const parentsFor = (select) => Object.fromEntries((select.dataset.parents || '').split(',').filter(Boolean).map((key) => [key, form.querySelector(`[name="${key}"]`)?.value || '']));
        const ready = (select) => Object.values(parentsFor(select)).every(Boolean);
        const resetOptions = (select) => {
            select.replaceChildren(new Option(select.dataset.placeholder, ''));
            $(select).val(null).trigger('change.select2');
        };
        const syncAvailability = (select) => {
            const enabled = !select.dataset.parents || ready(select);
            select.disabled = !enabled;
            $(select).prop('disabled', !enabled).trigger('change.select2');
        };
        const loadOptions = async (select) => {
            if (!ready(select)) {
                return;
            }

            const query = new URLSearchParams({ entity: select.dataset.entity, ...parentsFor(select) });
            const response = await fetch(`${form.dataset.locationOptionsUrl}?${query}`);
            if (!response.ok) {
                return;
            }

            const payload = await response.json();
            resetOptions(select);
            payload.items.forEach((item) => select.add(new Option(item.name, item.id)));
            syncAvailability(select);
        };

        selects.forEach((select) => {
            $(select).select2({
                width: '100%',
                placeholder: select.dataset.placeholder,
                allowClear: true,
            });
            syncAvailability(select);
        });

        selects.forEach((select, index) => {
            $(select).on('change.branches', () => {
                selects.slice(index + 1).forEach((child) => {
                    resetOptions(child);
                    syncAvailability(child);
                });

                const nextSelect = selects[index + 1];
                if (nextSelect !== undefined) {
                    void loadOptions(nextSelect);
                }
            });
        });
    });
}
