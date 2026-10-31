import test from 'node:test';
import assert from 'node:assert/strict';
import { guardLanguageSwitches } from '../../resources/assets/js/panel.js';

function fixture() {
    const formHandlers = new Map();
    const linkHandlers = new Map();
    const form = { addEventListener: (type, callback) => formHandlers.set(type, callback) };
    const link = { addEventListener: (type, callback) => linkHandlers.set(type, callback) };
    let value = 'initial';
    let confirmedMessage = null;
    guardLanguageSwitches(form, [link], (message) => {
        confirmedMessage = message;
        return false;
    }, () => value);

    return { formHandlers, linkHandlers, setValue: (next) => { value = next; }, confirmed: () => confirmedMessage };
}

test('blocks switching language after an unsaved change', () => {
    const state = fixture();
    state.formHandlers.get('input')();
    let prevented = false;
    state.linkHandlers.get('click')({ preventDefault: () => { prevented = true; } });

    assert.equal(prevented, true);
    assert.equal(state.confirmed(), 'Kaydedilmemiş değişiklikleriniz var. Dil değiştirmek istiyor musunuz?');
});

test('blocks switching language when the form value changed without an input event', () => {
    const state = fixture();
    state.setValue('changed');
    let prevented = false;
    state.linkHandlers.get('click')({ preventDefault: () => { prevented = true; } });

    assert.equal(prevented, true);
});
