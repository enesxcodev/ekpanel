import assert from 'node:assert/strict';
import test from 'node:test';

import initialize from '../../resources/assets/js/panel.js';

function createForm() {
    const formListeners = {};
    const linkListeners = {};
    const link = {
        addEventListener: (eventName, callback) => {
            linkListeners[eventName] = callback;
        },
    };
    const form = {
        addEventListener: (eventName, callback) => {
            formListeners[eventName] = callback;
        },
        querySelectorAll: (selector) => selector === '[data-branches-language-link]' ? [link] : [],
    };

    return { form, formListeners, linkListeners };
}

for (const formType of ['branch', 'category']) {
    test(`${formType} translation form guards language changes after editing`, () => {
        const { form, formListeners, linkListeners } = createForm();
        let allowNavigation = false;
        let confirmationCount = 0;
        globalThis.document = {
            querySelectorAll: (selector) => selector === '[data-branches-form]' ? [form] : [],
        };
        globalThis.window = {
            $: undefined,
            confirm: () => {
                confirmationCount += 1;

                return allowNavigation;
            },
        };

        initialize({});
        formListeners.input({ isTrusted: true, target: { name: 'name' } });
        let prevented = false;
        linkListeners.click({ preventDefault: () => { prevented = true; } });

        assert.equal(confirmationCount, 1);
        assert.equal(prevented, true);

        allowNavigation = true;
        prevented = false;
        linkListeners.click({ preventDefault: () => { prevented = true; } });

        assert.equal(confirmationCount, 2);
        assert.equal(prevented, false);
    });
}
