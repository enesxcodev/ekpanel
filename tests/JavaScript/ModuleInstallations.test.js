import test from 'node:test';
import assert from 'node:assert/strict';
import { initializeModuleInstallations } from '../../resources/js/admin/module-installations.js';

function node() {
    return {
        textContent: '', hidden: true, style: {}, attributes: {}, children: [], listeners: {},
        set innerHTML(value) { throw new Error(`Unsafe HTML rendering: ${value}`); },
        setAttribute(name, value) { this.attributes[name] = value; },
        append(...children) { this.children.push(...children); },
        replaceChildren(...children) { this.children = children; },
        addEventListener(name, listener) { this.listeners[name] = listener; },
    };
}

function prepareInstallation(context, responses) {
    const elements = Object.fromEntries(['status', 'stage', 'percentage', 'bar', 'progress', 'wait', 'error', 'updated', 'results', 'network', 'retry'].map((name) => [name, node()]));
    elements.stage.textContent = 'Kuyruk çalışanı bekleniyor';
    elements.percentage.textContent = '%0';
    elements.bar.style.width = '0%';
    const card = {
        dataset: { status: 'queued', statusUrl: '/admin/modules/installations/example' },
        querySelector: (selector) => elements[selector.slice('[data-installation-'.length, -1)],
    };
    const timers = new Map();
    const requests = [];
    let timerId = 0;
    const replacements = {
        document: { querySelector: () => card, createElement: node },
        setTimeout(callback, delay) { timers.set(++timerId, { callback, delay }); return timerId; },
        clearTimeout(id) { timers.delete(id); },
        async fetch(url, options) {
            requests.push({ url, options });
            const response = responses.shift();
            if (response instanceof Error) throw response;
            assert.ok(response, 'No unexpected status request should be sent');
            return { ok: true, json: async () => response };
        },
    };
    for (const [name, replacement] of Object.entries(replacements)) {
        const descriptor = Object.getOwnPropertyDescriptor(globalThis, name);
        Object.defineProperty(globalThis, name, { value: replacement, configurable: true, writable: true });
        context.after(() => {
            if (descriptor) Object.defineProperty(globalThis, name, descriptor);
            else delete globalThis[name];
        });
    }
    return {
        card, elements, timers, requests,
        async tick() {
            assert.equal(timers.size, 1);
            const [id, timer] = timers.entries().next().value;
            assert.equal(timer.delay, 2000);
            timers.delete(id);
            await timer.callback();
        },
    };
}

test('queued installations show completion and stop requesting updates after success', async (context) => {
    const ui = prepareInstallation(context, [{
        status: 'succeeded', percentage: 100, stage: 'Tüm modüller kuruldu', error: null,
        results: [{ name: 'Sample', status: 'succeeded', stage: 'Tamamlandı' }], updated_at: '2026-10-18 15:00:00',
    }]);

    initializeModuleInstallations();
    await ui.tick();

    assert.equal(ui.card.dataset.status, 'succeeded');
    assert.equal(ui.elements.status.textContent, 'Tamamlandı');
    assert.equal(ui.elements.stage.textContent, 'Tüm modüller kuruldu');
    assert.equal(ui.elements.bar.style.width, '100%');
    assert.equal(ui.elements.progress.attributes['aria-valuenow'], '100');
    assert.equal(ui.elements.wait.hidden, true);
    assert.equal(ui.elements.results.children[0].children[0].textContent, 'Sample');
    assert.equal(ui.timers.size, 0);
    assert.equal(ui.requests.length, 1);
    assert.equal(ui.requests[0].url, '/admin/modules/installations/example');
    assert.equal(ui.requests[0].options.cache, 'no-store');
    await ui.elements.retry.listeners.click();
    assert.equal(ui.requests.length, 1);
});

test('network failures preserve the last known progress and allow another status check', async (context) => {
    const ui = prepareInstallation(context, [
        { status: 'running', percentage: 25, stage: 'Composer hazırlanıyor', results: [], updated_at: '2026-10-18 15:00:00' },
        new Error('Network disconnected'),
        { status: 'succeeded', percentage: 100, stage: 'Tamamlandı', results: [] },
    ]);
    initializeModuleInstallations();
    await ui.tick();

    await ui.tick();

    assert.equal(ui.card.dataset.status, 'running');
    assert.equal(ui.elements.stage.textContent, 'Composer hazırlanıyor');
    assert.equal(ui.elements.percentage.textContent, '%25');
    assert.equal(ui.elements.bar.style.width, '25%');
    assert.equal(ui.elements.network.hidden, false);
    assert.match(ui.elements.network.textContent, /Son bilinen durum/);
    assert.equal(ui.elements.retry.hidden, false);
    assert.equal(ui.timers.size, 1);

    await ui.elements.retry.listeners.click();

    assert.equal(ui.elements.network.hidden, true);
    assert.equal(ui.elements.retry.hidden, true);
    assert.equal(ui.card.dataset.status, 'succeeded');
    assert.equal(ui.timers.size, 0);
    assert.equal(ui.requests.length, 3);
});

test('failed installations never display full completion and render supplied markup as text', async (context) => {
    const markup = '<img src=x onerror=alert(1)>';
    const ui = prepareInstallation(context, [{
        status: 'failed', percentage: 100, stage: markup, error: markup,
        results: [{ name: markup, status: 'failed', stage: markup, error: markup }],
    }]);

    initializeModuleInstallations();
    await ui.tick();

    assert.equal(ui.elements.status.textContent, 'Başarısız');
    assert.equal(ui.elements.percentage.textContent, '%99');
    assert.equal(ui.elements.bar.style.width, '99%');
    assert.equal(ui.elements.stage.textContent, markup);
    assert.equal(ui.elements.error.textContent, markup);
    assert.equal(ui.elements.error.hidden, false);
    const row = ui.elements.results.children[0];
    assert.deepEqual(row.children.map((child) => child.textContent), [markup, 'Başarısız', markup, markup]);
    assert.equal(ui.timers.size, 0);
});
