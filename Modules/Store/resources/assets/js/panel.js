export default function initialize(panel) {
    const form = document.querySelector('[data-store-form]');
    if (!form) return;
    const read = (el, fallback = []) => { try { return JSON.parse(el?.value || el?.textContent || '[]') || fallback; } catch { return fallback; } };
    const input = (name, value = '', type = 'text') => {
        const el = document.createElement('input'); el.name = name; el.value = value ?? ''; el.type = type; el.className = 'form-control'; return el;
    };
    const makeButton = (label, handler) => {
        const el = document.createElement('button'); el.type = 'button'; el.className = 'btn btn-sm btn-outline-secondary'; el.textContent = label; el.addEventListener('click', handler); return el;
    };
    const field = (label, el) => { const wrap = document.createElement('label'); wrap.className = 'd-block mb-2'; wrap.append(document.createTextNode(label), el); return wrap; };
    const tabs = [...form.querySelectorAll('[data-bs-toggle="tab"]')];
    const tabKey = 'store.form.tab.' + form.dataset.formKey;
    const showTab = (button) => {
        if (panel?.bootstrap?.Tab) { panel.bootstrap.Tab.getOrCreateInstance(button).show(); }
        else { button.click(); }
    };
    tabs.forEach(button => button.addEventListener('shown.bs.tab', () => sessionStorage.setItem(tabKey, button.dataset.bsTarget)));
    const error = form.querySelector('.is-invalid, [aria-invalid="true"]');
    const pane = error?.closest('.tab-pane');
    const target = form.dataset.errorTab ? '#store-' + form.dataset.errorTab : (pane ? '#' + pane.id : sessionStorage.getItem(tabKey));
    const chosen = tabs.find(button => button.dataset.bsTarget === target);
    if (chosen) showTab(chosen);
    form.closest('form').addEventListener('invalid', event => {
        const target = event.target.closest('.tab-pane');
        const tab = tabs.find(button => button.dataset.bsTarget === '#' + target?.id);
        if (tab) showTab(tab);
    }, true);

    const gallery = form.querySelector('[data-store-gallery]');
    const galleryInput = gallery.querySelector('[name="image_media_ids"]');
    const names = new Map([...gallery.querySelectorAll('[data-id]')].map(el => [Number(el.dataset.id), el.dataset.name]));
    const list = gallery.querySelector('[data-gallery-order]');
    const renderGallery = () => {
        const ids = read(galleryInput); list.replaceChildren();
        ids.forEach((id, index) => {
            const item = document.createElement('li'); item.className = 'list-group-item d-flex gap-2 align-items-center';
            const name = document.createElement('span'); name.className = 'me-auto'; name.textContent = names.get(Number(id)) || 'Görsel #' + id; item.append(name);
            [['Yukarı', -1], ['Aşağı', 1]].forEach(([label, step]) => {
                const button = makeButton(label, () => { [ids[index], ids[index + step]] = [ids[index + step], ids[index]]; galleryInput.value = JSON.stringify(ids); galleryInput.dispatchEvent(new Event('change', {bubbles: true})); });
                button.disabled = index + step < 0 || index + step >= ids.length; item.append(button);
            }); list.append(item);
        });
    };
    galleryInput.addEventListener('change', renderGallery);
    gallery.addEventListener('media:selected', event => { event.detail.files.forEach(file => names.set(Number(file.id), file.name)); renderGallery(); refreshImageOptions(); });
    renderGallery();

    const editor = form.querySelector('[data-variant-editor]');
    let groups = read(editor.querySelector('[data-groups-json]'));
    let rows = read(editor.querySelector('[data-variants-json]'));
    const groupList = editor.querySelector('[data-option-groups]');
    const rowList = editor.querySelector('[data-variant-rows]');
    const combinationsKey = values => JSON.stringify(Object.entries(values).sort(([a],[b]) => a.localeCompare(b)));
    const refreshImageOptions = () => {
        rowList.querySelectorAll('select[data-variant-image]').forEach(select => {
            const selected = select.value;
            const ids = [...new Set([...read(galleryInput), ...(selected ? [Number(selected)] : [])])];
            select.replaceChildren(new Option('Ürün görseli', ''));
            ids.forEach(id => select.add(new Option(names.get(Number(id)) || 'Görsel #' + id, id)));
            select.value = selected;
        });
    };
    const renderGroups = () => {
        groupList.replaceChildren();
        groups.forEach((group, index) => {
            const card = document.createElement('div'); card.className = 'border rounded p-3 mb-2';
            const name = input('option_groups[' + index + '][name]', group.name);
            name.addEventListener('input', () => group.name = name.value);
            const values = input('option_groups[' + index + '][values]', group.values);
            values.addEventListener('input', () => group.values = values.value);
            card.append(field('Grup adı', name), field('Değerler (virgülle ayırın)', values), makeButton('Grubu kaldır', () => { groups.splice(index, 1); renderGroups(); }));
            groupList.append(card);
        });
    };
    const renderRows = () => {
        rowList.replaceChildren();
        rows.forEach((row, index) => {
            const card = document.createElement('div'); card.className = 'border rounded p-3 mb-3';
            const title = document.createElement('h3'); title.className = 'h4'; title.textContent = Object.values(row.option_values).join(' / '); card.append(title);
            if (row.id) card.append(input('variants[' + index + '][id]', row.id, 'hidden'));
            Object.entries(row.option_values).forEach(([name,value]) => card.append(input('variants[' + index + '][option_values][' + name + ']', value, 'hidden')));
            [['sku','SKU','text'],['price','Fiyat (TL, boşsa temel fiyat)','text'],['stock_quantity','Stok','number']].forEach(([key,label,type]) => {
                const el = input('variants[' + index + '][' + key + ']', row[key], type); el.addEventListener('input', () => row[key] = el.value); card.append(field(label, el));
            });
            const active = document.createElement('select'); active.className = 'form-select'; active.name = 'variants[' + index + '][is_active]';
            active.add(new Option('Aktif','1')); active.add(new Option('Pasif','0')); active.value = String(row.is_active ?? 1);
            active.addEventListener('change', () => row.is_active = active.value); card.append(field('Durum', active));
            const image = document.createElement('select'); image.className = 'form-select'; image.name = 'variants[' + index + '][image_media_id]'; image.dataset.variantImage = '';
            image.add(new Option('Ürün görseli','')); if (row.image_media_id) image.add(new Option('Görsel #' + row.image_media_id, row.image_media_id));
            image.value = row.image_media_id || ''; image.addEventListener('change', () => row.image_media_id = image.value); card.append(field('Varyant görseli (galeriden)', image));
            const buttons = document.createElement('div'); buttons.className = 'd-flex gap-2';
            buttons.append(makeButton('Kaldır', () => { rows.splice(index,1); renderRows(); }));
            [-1,1].forEach(step => { const b = makeButton(step < 0 ? 'Yukarı' : 'Aşağı', () => { [rows[index],rows[index + step]] = [rows[index + step],rows[index]]; renderRows(); }); b.disabled = index + step < 0 || index + step >= rows.length; buttons.append(b); });
            card.append(buttons); rowList.append(card);
        });
        refreshImageOptions();
    };
    editor.querySelector('[data-add-group]').addEventListener('click', () => { if (groups.length < 3) { groups.push({name:'',values:''}); renderGroups(); } });
    editor.querySelector('[data-generate]').addEventListener('click', () => {
        if (!groups.length || groups.some(group => !group.name.trim() || !group.values.trim() || /[\[\]]/.test(group.name))) { alert('Grup adını ve değerlerini doldurun.'); return; }
        const combinations = groups.reduce((items,group) => items.flatMap(item => [...new Set(group.values.split(',').map(v=>v.trim()).filter(Boolean))].map(value => ({...item,[group.name.trim()]:value}))), [{}]);
        if (combinations.length > 100) { alert('En fazla 100 kombinasyon oluşturulabilir.'); return; }
        const existing = new Map(rows.map(row => [combinationsKey(row.option_values), row]));
        const baseSku = form.querySelector('[name="sku"]').value;
        rows = combinations.map((values,index) => existing.get(combinationsKey(values)) || {option_values:values,sku:baseSku + '-' + (index+1),price:'',stock_quantity:0,is_active:1});
        renderRows();
    });
    renderGroups(); renderRows();

    const catalog = form.querySelector('[data-store-catalog]');
    if (catalog) {
        let next = null;
        const results = catalog.querySelector('[data-results]');
        const nextButton = catalog.querySelector('[data-next]');
        const load = async (url) => {
            results.textContent = 'Aranıyor...';
            try {
                const response = await fetch(url, {headers:{Accept:'application/json'}});
                if (!response.ok) throw new Error('Katalog okunamadı.');
                const data = await response.json(); results.replaceChildren(); next = data.next; nextButton.hidden = !next;
                if (!data.items.length) results.textContent = 'Ürün bulunamadı.';
                data.items.forEach(product => {
                    const card = document.createElement('div'); card.className = 'border rounded p-3 mb-2';
                    const label = document.createElement('strong'); label.textContent = product.title; card.append(label);
                    if (product.image_url) { const img = document.createElement('img'); img.src = product.image_url; img.alt = product.title; img.width = 72; img.className = 'mx-2'; card.append(img); }
                    const summary = document.createElement('p'); summary.textContent = product.short_description || ''; card.append(summary);
                    const count = document.createElement('p'); count.textContent = 'Galeri: ' + product.images.length + ' görsel'; card.append(count);
                    if (product.existing_url) { const link = document.createElement('a'); link.href = product.existing_url; link.textContent = 'Daha önce aktarılmış ürünü aç'; card.append(link); }
                    else card.append(makeButton('İçeriği aktar', () => {
                        if (form.querySelector('[name="title"]').value && !confirm('Formdaki ürün içeriği katalog içeriğiyle değiştirilsin mi?')) return;
                        ['title','slug','short_description','content'].forEach(key => { const el = form.querySelector('[name="' + key + '"]'); el.value = product[key] || ''; if (key === 'content' && window.hugerte) window.hugerte.get(el.id)?.setContent(product.content || ''); });
                        form.querySelector('[name="source_product_id"]').value = product.id;
                        const cover = form.querySelector('[name="image_media_id"]'); cover.value = product.image_media_id || ''; cover.dispatchEvent(new Event('change',{bubbles:true}));
                        const removeCover = form.querySelector('[name="remove_image"]'); if (removeCover) removeCover.value = '0';
                        const coverField = cover.closest('[data-media-field]');
                        const coverPreview = coverField.querySelector('[data-media-field-preview]'); coverPreview.replaceChildren();
                        if (product.image_url) { const image = document.createElement('img'); image.src = product.image_url; image.alt = product.title; coverPreview.append(image); }
                        coverField.querySelector('[data-media-clear]').classList.toggle('d-none', !product.image_url);
                        product.images.forEach(image => names.set(Number(image.id),image.name));
                        galleryInput.value = JSON.stringify(product.images.map(image=>image.id)); galleryInput.dispatchEvent(new Event('change',{bubbles:true})); refreshImageOptions();
                        results.textContent = product.title + ': kapak ve galeri dahil içerik aktarıldı. SKU, fiyat ve stok bilgilerini doldurun.';
                    }));
                    results.append(card);
                });
            } catch(error) { results.textContent = error.message; }
        };
        catalog.querySelector('[data-search]').addEventListener('click', () => { const url = new URL(catalog.dataset.url,location.origin); url.searchParams.set('q',catalog.querySelector('input').value); load(url); });
        nextButton.addEventListener('click', () => { if(next) load(next); });
    }
}
