export function initializePermissionMatrix() {
    const matrix = document.querySelector('[data-permission-matrix]');
    if (!matrix) {
        return;
    }
    const rows = [...matrix.querySelectorAll('[data-permission-row]')];
    rows.forEach((row) => {
        const all = row.querySelector('[data-permission-select-row]');
        const inputs = [...row.querySelectorAll('[data-permission-checkbox]')];
        const refresh = () => {
            const checked = inputs.filter((input) => input.checked).length;
            all.checked = inputs.length > 0 && checked === inputs.length;
            all.indeterminate = checked > 0 && checked < inputs.length;
        };
        all.addEventListener('change', () => {
            inputs.filter((input) => !input.disabled).forEach((input) => { input.checked = all.checked; });
            refresh();
        });
        inputs.forEach((input) => input.addEventListener('change', refresh));
        refresh();
    });
    matrix.querySelector('[data-permission-search]')?.addEventListener('input', (event) => {
        const query = event.target.value.toLocaleLowerCase('tr');
        rows.forEach((row) => {
            row.hidden = !row.cells[0].textContent.toLocaleLowerCase('tr').includes(query);
        });
    });
}
