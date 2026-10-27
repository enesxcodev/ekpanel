import test from 'node:test';
import assert from 'node:assert/strict';
import { featureDocumentState, fieldProperty, updateFieldProperty, defaultValue, featureValueTypes } from '../../resources/js/admin/form-builder-state.js';

const field = (key, overrides = {}) => ({ key, label: key, input_type: 'text', value_type: 'string', ...overrides });

test('switching and editing module form groups preserves nested definitions and false zero and null defaults', () => {
    const original = { schema_version: 1, forms: {
        service: { label: 'Hizmet', fields: [field('caption', { attributes: { placeholder: 'Başlık', rows: 3 }, validation: { max: 180 } }), field('active', { input_type: 'boolean', value_type: 'boolean', default_value: false })] },
        category: { fields: [field('count', { input_type: 'number', value_type: 'integer', default_value: 0 }), field('photo', { input_type: 'image', value_type: 'media', default_value: null, validation: { extensions: ['png'], max_size: 4096, min_width: 100 } })] },
    } };
    const state = featureDocumentState(original);
    updateFieldProperty(state.fields[0], 'label', 'Yeni başlık', true);
    state.select('category');
    state.fields.reverse();
    state.replaceFields(state.fields);
    state.select('service');
    assert.equal(state.fields[0].label, 'Yeni başlık');
    assert.equal(original.forms.service.fields[0].label, 'caption');
    const saved = JSON.parse(JSON.stringify(state.document));
    assert.deepEqual(saved.forms.service.fields[0].validation, { max: 180 });
    assert.deepEqual(saved.forms.service.fields[0].attributes, { placeholder: 'Başlık', rows: 3 });
    assert.equal(saved.forms.service.fields[1].default_value, false);
    assert.equal(saved.forms.category.fields[1].default_value, 0);
    assert.deepEqual(saved.forms.category.fields[0], original.forms.category.fields[1]);
});

test('adding and removing form groups produces an object and cannot overwrite another group', () => {
    const state = featureDocumentState({ schema_version: 1, forms: [] });
    assert.equal(state.selected, null);
    assert.equal(state.add('../outside', 'Invalid'), false);
    assert.equal(state.add('service', 'Hizmet'), true);
    state.replaceFields([field('caption')]);
    assert.equal(state.add('service', 'Duplicate'), false);
    assert.equal(state.add('category', 'Kategori'), true);
    state.remove();
    assert.equal(state.selected, 'service');
    assert.equal(state.select('missing'), false);
    assert.deepEqual(state.fields, [field('caption')]);
    state.remove();
    assert.equal(JSON.stringify(state.document), '{"schema_version":1,"forms":{}}');
});

test('feature property editing keeps nested constraints and chooses compatible value types', () => {
    const definition = field('count', { validation: { required: false, max: 100 }, default_value: '0' });
    updateFieldProperty(definition, 'validation.required', true, true);
    updateFieldProperty(definition, 'input_type', 'number', true);
    updateFieldProperty(definition, 'value_type', 'integer', true);
    assert.equal(definition.value_type, 'integer');
    assert.equal(definition.default_value, 0);
    assert.equal(fieldProperty(definition, 'validation.required'), true);
    assert.equal(definition.validation.max, 100);
    updateFieldProperty(definition, 'validation.max', null, true);
    assert.equal(Object.hasOwn(definition.validation, 'max'), false);
    updateFieldProperty(definition, 'input_type', 'image', true);
    assert.equal(definition.default_value, null);
    assert.deepEqual(featureValueTypes('image'), ['file', 'media']);
    assert.equal(defaultValue('false', 'boolean'), false);
    assert.equal(defaultValue('', 'string'), null);
    assert.equal(defaultValue('invalid', 'integer'), 'invalid');
});

test('visitor form properties keep their flat payload and explicit null values', () => {
    const definition = { key: 'email', input_type: 'email', role: 'email', width: 'half', options: [], required: false, max_length: 100 };
    updateFieldProperty(definition, 'required', true);
    updateFieldProperty(definition, 'max_length', null);
    updateFieldProperty(definition, 'label', 'E-posta');
    assert.deepEqual(definition, { key: 'email', input_type: 'email', role: 'email', width: 'half', options: [], required: true, max_length: null, label: 'E-posta' });
    assert.equal(Object.hasOwn(definition, 'value_type'), false);
    assert.equal(Object.hasOwn(definition, 'validation'), false);
});
