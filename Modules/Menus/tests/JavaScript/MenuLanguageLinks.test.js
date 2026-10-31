import test from 'node:test';
import assert from 'node:assert/strict';
import { languageMenuHref } from '../../resources/assets/js/workspace.js';

test('language links retain the currently selected menu', () => {
    assert.equal(languageMenuHref('/admin/menus?lang=en', 4), '/admin/menus?lang=en&menu=4');
});

test('language links clear a stale menu when no menu is selected', () => {
    assert.equal(languageMenuHref('/admin/menus?lang=en&menu=4', null), '/admin/menus?lang=en');
});
