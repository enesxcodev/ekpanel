export const featureValueTypes = (type) => {
    if (['number', 'range'].includes(type)) return ['integer', 'float'];
    if (type === 'boolean') return ['boolean'];
    if (['file', 'image'].includes(type)) return ['file', 'media'];
    return ['string'];
};

export function fieldProperty(field, path) {
    return path.split('.').reduce((value, key) => value?.[key], field);
}

export function updateFieldProperty(field, path, value, features = false) {
    const parts = path.split('.');
    let target = field;
    for (const part of parts.slice(0, -1)) target = target[part] ??= {};
    if (features && value === null && path !== 'default_value') delete target[parts.at(-1)];
    else target[parts.at(-1)] = value;
    if (features && path === 'input_type') {
        const allowed = featureValueTypes(value);
        if (!allowed.includes(field.value_type)) field.value_type = allowed[0];
        if (['file', 'image'].includes(value)) field.default_value = null;
    }
    if (features && path === 'value_type' && field.default_value != null) {
        field.default_value = defaultValue(field.default_value, value);
    }
}

export function defaultValue(value, type) {
    if (value === '') return null;
    if (type === 'boolean') {
        if (['true', '1', true].includes(value)) return true;
        if (['false', '0', false].includes(value)) return false;
    }
    if (['integer', 'float'].includes(type) && Number.isFinite(Number(value))) return Number(value);
    return value;
}

export function featureDocumentState(document) {
    const state = structuredClone(document);
    if (!state.forms || Array.isArray(state.forms)) state.forms = {};
    let selected = Object.keys(state.forms)[0] ?? null;
    return {
        document: state,
        get selected() { return selected; },
        get fields() { return selected === null ? [] : state.forms[selected].fields; },
        select(key) {
            if (!Object.hasOwn(state.forms, key)) return false;
            selected = key;
            return true;
        },
        add(key, label) {
            if (!/^[a-z][a-z0-9_]{0,99}$/.test(key) || Object.hasOwn(state.forms, key)) return false;
            state.forms[key] = { label: label || 'Ek alanlar', fields: [] };
            selected = key;
            return true;
        },
        remove() {
            if (selected !== null) delete state.forms[selected];
            selected = Object.keys(state.forms)[0] ?? null;
        },
        replaceFields(fields) {
            if (selected !== null) state.forms[selected].fields = fields;
        },
    };
}
