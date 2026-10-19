import test from 'node:test';
import assert from 'node:assert/strict';
import { initializeSlugs } from '../../resources/js/admin/slug.js';

function initializeForm(titleValue, slugValue) {
    const title = new EventTarget();
    const slug = new EventTarget();
    title.value = titleValue;
    slug.value = slugValue;
    title.closest = () => ({ querySelector: () => slug });
    globalThis.document = { querySelectorAll: () => [title] };
    initializeSlugs();
    delete globalThis.document;

    return { title, slug };
}

test('editing an existing title updates its populated slug with Turkish transliteration', () => {
    const { title, slug } = initializeForm('Eski başlık', 'eski-baslik');
    assert.equal(slug.value, 'eski-baslik');
    title.value = 'İçerik: Çağrı, Şölen & Ürün';
    title.dispatchEvent(new Event('input'));
    assert.equal(slug.value, 'icerik-cagri-solen-urun');
});

test('a manually edited slug is preserved across subsequent title edits', () => {
    const { title, slug } = initializeForm('Başlık', 'baslik');
    slug.value = 'ozel-adres';
    slug.dispatchEvent(new Event('input'));
    title.value = 'Yeni başlık';
    title.dispatchEvent(new Event('input'));
    assert.equal(slug.value, 'ozel-adres');

    slug.value = '';
    slug.dispatchEvent(new Event('input'));
    title.dispatchEvent(new Event('input'));
    assert.equal(slug.value, 'yeni-baslik');
});

test('new forms generate a slug initially and on input including pasted titles', () => {
    const { title, slug } = initializeForm('Yeni sayfa', '');
    assert.equal(slug.value, 'yeni-sayfa');
    title.value = 'Güncel Hizmet';
    title.dispatchEvent(new Event('input'));
    assert.equal(slug.value, 'guncel-hizmet');
});
