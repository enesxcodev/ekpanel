const valueTypesByInput = {
    number: ['integer', 'float'],
    range: ['integer', 'float'],
    boolean: ['boolean'],
    file: ['file'],
    image: ['file'],
};

function initializeDefinitionForm(form) {
    const inputType = form.querySelector('[data-setting-type]');
    const valueType = form.querySelector('[data-setting-value-type]');
    const options = form.querySelector('[data-setting-options]');
    const template = form.querySelector('[data-option-template]');
    let nextOptionIndex = [...options.querySelectorAll('input[name]')].reduce((next, input) => {
        const index = Number(input.name.match(/^options\[(\d+)\]/)?.[1] ?? -1);
        return Math.max(next, index + 1);
    }, 0);

    const refresh = () => {
        const type = inputType.value;
        const allowedTypes = valueTypesByInput[type] ?? ['string'];
        [...valueType.options].forEach((option) => {
            option.hidden = !allowedTypes.includes(option.value);
            option.disabled = option.hidden;
        });
        if (!allowedTypes.includes(valueType.value)) {
            valueType.value = allowedTypes[0];
        }
        form.querySelectorAll('[data-for-types]').forEach((group) => {
            const visible = group.dataset.forTypes.split(' ').includes(type);
            group.hidden = !visible;
            group.querySelectorAll('input, select, textarea, button').forEach((input) => {
                input.disabled = !visible;
            });
        });
    };

    inputType.addEventListener('change', refresh);
    form.querySelector('[data-add-option]').addEventListener('click', () => {
        const row = template.content.cloneNode(true);
        row.querySelectorAll('[name], [id], [for]').forEach((element) => {
            ['name', 'id', 'for'].forEach((attribute) => {
                if (element.hasAttribute(attribute)) {
                    element.setAttribute(attribute, element.getAttribute(attribute).replaceAll('__INDEX__', nextOptionIndex));
                }
            });
        });
        nextOptionIndex += 1;
        options.append(row);
    });
    options.addEventListener('click', (event) => {
        event.target.closest('[data-remove-option]')?.closest('[data-setting-option]')?.remove();
    });
    refresh();
}

export function initializeSettings() {
    document.querySelectorAll('[data-setting-definition]').forEach(initializeDefinitionForm);
    document.querySelectorAll('[data-clear-radio]').forEach((button) => {
        button.addEventListener('click', () => {
            button.closest('fieldset').querySelectorAll('input[type="radio"]').forEach((input) => {
                input.checked = false;
            });
        });
    });
    document.querySelectorAll('[data-settings-range]').forEach((input) => {
        const output = document.querySelector(`[data-range-output="${input.id}"]`);
        const refresh = () => { if (output) { output.value = input.value; } };
        input.addEventListener('input', refresh);
        refresh();
    });
}
