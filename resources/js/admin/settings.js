const valueTypesByInput = {
    number: ['integer', 'float'],
    range: ['integer', 'float'],
    boolean: ['boolean'],
    file: ['file'],
    image: ['file'],
};

function initializeDefinitionForm(form) {
    const sectionInput = form.querySelector('[name="section_id"]');
    const groupInput = form.querySelector('[data-setting-group]');
    const groupOptions = form.querySelectorAll('#setting-group-options option');
    groupInput?.addEventListener('change', () => {
        const option = [...groupOptions].find((item) => item.dataset.section === sectionInput.value && item.value === groupInput.value);
        if (option) {
            form.querySelector('[name="attributes[columns]"]').value = option.dataset.columns;
            form.querySelector('[name="attributes[group_order]"]').value = option.dataset.order;
        }
    });
    const refreshGroups = () => {
        groupOptions.forEach((option) => { option.disabled = option.dataset.section !== sectionInput.value; });
    };
    sectionInput?.addEventListener('change', refreshGroups);
    refreshGroups();
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
    document.querySelectorAll('[data-language-status]').forEach((input) => {
        input.addEventListener('change', () => input.form.requestSubmit());
    });
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
