import test from 'node:test';
import assert from 'node:assert/strict';
import { synchronizeEditorForm } from '../../resources/js/admin/editor-form.js';

test('initialized rich text editors release hidden textarea validation and synchronize their submitted content', () => {
    const textarea = { required: true, value: '' };
    const handlers = new Map();
    let dirty = true;
    const editor = {
        getElement: () => textarea,
        on: (events, callback) => events.split(' ').forEach(event => handlers.set(event, callback)),
        save: (options) => {
            if (options?.set_dirty !== false) dirty = false;
            textarea.value = '<p>Yanıt içeriği</p>';
        },
    };
    synchronizeEditorForm(editor);
    assert.equal(textarea.required, true);
    handlers.get('init')();
    assert.equal(textarea.required, false);
    for (const event of ['input', 'change', 'undo', 'redo']) {
        textarea.value = '';
        handlers.get(event)();
        assert.equal(textarea.value, '<p>Yanıt içeriği</p>');
        assert.equal(dirty, true);
    }
    handlers.get('remove')();
    assert.equal(textarea.required, true);
});
