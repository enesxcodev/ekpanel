import test from 'node:test';
import assert from 'node:assert/strict';
import { readdirSync } from 'node:fs';
import { initializeModuleAssets } from '../../resources/js/admin/module-assets.js';

test('downloaded modules receive the ready shared panel and initialize in order', async () => {
    const urls = [1, 2].map((index) => 'data:text/javascript,' + encodeURIComponent(
        `export default async function(panel) { panel.calls.push(${index}); }`,
    ));
    globalThis.document = { querySelector: () => ({ textContent: JSON.stringify(urls) }) };
    const panel = { calls: [] };
    try {
        await initializeModuleAssets(panel);

        assert.deepEqual(panel.calls, [1, 2]);
    } finally {
        delete globalThis.document;
    }
});

test('failed modules notify through the shared panel and do not prevent the next initializer', async () => {
    const urls = [
        'data:text/javascript,export default 42;',
        'data:text/javascript,export default function(panel) { panel.calls.push("ready"); }',
    ];
    globalThis.document = { querySelector: () => ({ textContent: JSON.stringify(urls) }) };
    const originalError = console.error;
    console.error = () => {};
    const panel = { calls: [], notify: (notification) => panel.calls.push(notification.type) };
    try {
        await initializeModuleAssets(panel);

        assert.deepEqual(panel.calls, ['error', 'ready']);
    } finally {
        console.error = originalError;
        delete globalThis.document;
    }
});

test('compiled Menus release starts with the shared panel without importing application sources', async () => {
    globalThis.document = { querySelector: () => null, querySelectorAll: () => [] };
    try {
        const module = await import('../../Modules/Menus/resources/dist/panel.js');

        assert.doesNotThrow(() => module.default({ notify() {} }));
    } finally {
        delete globalThis.document;
    }
});

test('module distribution does not include application public files', () => {
    assert.deepEqual(readdirSync(new URL('../../Modules/Menus/resources/dist/', import.meta.url)).sort(), ['panel.css', 'panel.js']);
});
