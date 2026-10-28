import { toSlug } from './slug.js';
import { featureDocumentState, featureValueTypes, fieldProperty, updateFieldProperty, defaultValue } from './form-builder-state.js';

export function initializeFormBuilder() {
    document.querySelectorAll('[data-form-builder]').forEach((form) => {
        if (form.dataset.builderInitialized) return;
        form.dataset.builderInitialized = 'true';
        const features = form.dataset.builderMode === 'features';
        let moduleState = null;
        const list = form.querySelector('[data-field-list]');
        const editor = form.querySelector('[data-field-editor]');
        const preview = form.querySelector('[data-form-preview]');
        const hidden = form.querySelector('[data-schema-value]');
        let fields = [];
        try { fields = JSON.parse(form.dataset.fields || '[]'); } catch {}
        if (!Array.isArray(fields)) fields = [];
        if (features) {
            let document = JSON.parse(form.dataset.document);
            if (typeof document === 'string') document = JSON.parse(document);
            moduleState = featureDocumentState(document);
            fields = moduleState.fields;
        }
        const maximumFields = features ? 100 : 50;
        const isRequired = field => Boolean(fieldProperty(field, features ? 'validation.required' : 'required'));
        const placeholder = field => fieldProperty(field, features ? 'attributes.placeholder' : 'placeholder') || '';
        let selected = fields.length ? 0 : -1;
        let dragged = null;
        let blockedDrag = false;
        const types = { text: 'Metin', email: 'E-posta', tel: 'Telefon', textarea: 'Uzun metin', number: 'Sayı', select: 'Seçim listesi', radio: 'Radio', boolean: 'Onay kutusu', file: 'Dosya', image: 'Görsel', range: 'Aralık' };
        const node = (tag, cls, text) => {
            const el = document.createElement(tag);
            if (cls) el.className = cls;
            if (text !== undefined) el.textContent = text;
            return el;
        };
        const autoKeys = new WeakSet();
        const fresh = (key, label = 'Yeni alan', type = 'text', role = '') => {
            const field = features ? { key, label, input_type: type, value_type: featureValueTypes(type)[0], options: [], validation: {} } : {
            key, label, input_type: type, required: false, placeholder: '', description: '', width: 'full',
            css_class: '', input_class: '', role, options: [], max_length: null, max_size: 5120, extensions: 'pdf,docx',
            };
            if (/^alan_[0-9]+$/.test(key)) autoKeys.add(field);
            return field;
        };
        const persist = () => {
            if (features) {
                moduleState.replaceFields(fields);
                hidden.value = JSON.stringify(moduleState.document);
                form.querySelector('[data-feature-json]').textContent = JSON.stringify(moduleState.document, null, 4);
            } else hidden.value = JSON.stringify(fields);
        };
        const renderPreview = () => {
            preview.replaceChildren();
            const grid = node('div', 'ek-form__fields');
            fields.forEach((field) => {
                const wrap = node('div', 'ek-form__field ek-form__field--' + (field.width || 'full'));
                wrap.append(node('label', 'ek-form__label', field.label + (isRequired(field) ? ' *' : '')));
                if (field.input_type === 'radio') {
                    (field.options || []).forEach((option) => {
                        const label = node('label', 'ek-form__choice');
                        const input = node('input'); input.type = 'radio'; input.disabled = true;
                        if (features) input.checked = field.default_value === option.value;
                        label.append(input, document.createTextNode(' ' + option.label)); wrap.append(label);
                    });
                } else {
                    const tag = field.input_type === 'textarea' ? 'textarea' : field.input_type === 'select' ? 'select' : 'input';
                    const input = node(tag, 'ek-form__input');
                    if (tag === 'input') input.type = field.input_type === 'boolean' ? 'checkbox' : field.input_type === 'image' ? 'file' : field.input_type;
                    if (tag === 'textarea') input.rows = features ? (field.attributes?.rows ?? 4) : 4;
                    if (features && ['number', 'range'].includes(field.input_type)) {
                        for (const bound of ['min', 'max']) if (field.validation?.[bound] != null) input[bound] = field.validation[bound];
                        input.step = field.attributes?.step ?? (field.value_type === 'float' ? 'any' : 1);
                    }
                    if (tag === 'select') {
                        input.add(new Option(placeholder(field) || 'Seçin', ''));
                        (field.options || []).forEach(option => input.add(new Option(option.label, option.value)));
                    }
                    input.placeholder = placeholder(field);
                    if (features && field.default_value != null && !['file', 'image'].includes(field.input_type)) {
                        if (field.input_type === 'boolean') input.checked = Boolean(field.default_value);
                        else input.value = field.default_value;
                    }
                    input.disabled = true;
                    wrap.append(input);
                }
                if (field.description) wrap.append(node('small', 'ek-form__help', field.description));
                grid.append(wrap);
            });
            if (!fields.length) grid.append(node('p', 'text-secondary', features ? 'Önizlemek için bir alan ekleyin.' : 'Önizlemek için bir alan ekleyin veya başlangıç şablonu seçin.'));
            preview.append(grid);
            if (!features) {
                const submit = node('button', 'ek-form__submit', form.querySelector('[data-submit-label]').value || 'Gönder');
                submit.type = 'button';
                preview.append(submit);
            }
        };
        const renderList = () => {
            list.replaceChildren();
            fields.forEach((field, index) => {
                const row = node('div', 'fb-field-row' + (index === selected ? ' is-selected' : ''));
                row.dataset.fieldIndex = index; row.draggable = true;
                const choose = node('button', 'fb-field-choose', '⠿  ' + field.label);
                choose.type = 'button'; choose.dataset.fieldSelect = index;
                const badge = node('span', 'badge bg-blue-lt', types[field.input_type]);
                const required = node('span', isRequired(field) ? 'badge bg-red-lt' : 'badge bg-secondary-lt', isRequired(field) ? 'Zorunlu' : 'İsteğe bağlı');
                row.append(choose, badge, required);
                [['up', '↑', 'Yukarı taşı'], ['down', '↓', 'Aşağı taşı'], ['remove', '×', 'Alanı sil']].forEach(([action, label, title]) => {
                    const button = node('button', 'btn btn-sm btn-ghost-secondary', label);
                    button.type = 'button'; button.dataset.fieldAction = action; button.dataset.index = index; button.setAttribute('aria-label', title);
                    button.disabled = (action === 'up' && index === 0) || (action === 'down' && index === fields.length - 1);
                    row.append(button);
                });
                list.append(row);
            });
        };
        const showEditor = () => {
            const field = fields[selected];
            editor.hidden = !field;
            if (!field) {
                editor.querySelectorAll('input, select, textarea').forEach(input => { input.disabled = true; });
                return;
            }
            editor.querySelectorAll('[data-field-prop]').forEach(input => {
                const value = fieldProperty(field, input.dataset.fieldProp);
                if (features && input.dataset.fieldProp === 'value_type') {
                    [...input.options].forEach(option => { option.hidden = !featureValueTypes(field.input_type).includes(option.value); });
                }
                if (input.type === 'checkbox') input.checked = Boolean(value);
                else input.value = Array.isArray(value) ? value.join(',') : value ?? '';
            });
            editor.querySelector('[data-field-options]').value = (field.options || []).map(option => option.value + '|' + option.label).join('\n');
            settingsVisibility();
        };
        const settingsVisibility = () => {
            const field = fields[selected]; if (!field) return;
            editor.querySelector('[data-options-setting]').hidden = !['select', 'radio'].includes(field.input_type);
            editor.querySelector('[data-length-setting]').hidden = !['text', 'email', 'tel', 'textarea'].includes(field.input_type);
            editor.querySelectorAll('[data-file-setting]').forEach(el => { el.hidden = !['file', 'image'].includes(field.input_type); });
            if (features) {
                editor.querySelector('[data-length-setting]').hidden = true;
                const visibility = {
                    'bound': ['text', 'email', 'textarea', 'number', 'range'].includes(field.input_type),
                    'number': ['number', 'range'].includes(field.input_type),
                    'textarea': field.input_type === 'textarea',
                    'radio': field.input_type === 'radio',
                    'image': ['file', 'image'].includes(field.input_type),
                    'default': !['file', 'image'].includes(field.input_type),
                };
                Object.entries(visibility).forEach(([key, visible]) => editor.querySelectorAll(`[data-${key}-setting]`).forEach(el => { el.hidden = !visible; }));
            }
            editor.querySelectorAll('input, select, textarea').forEach(input => { input.disabled = Boolean(input.closest('[hidden]')); });
        };
        const render = () => { persist(); renderList(); renderPreview(); showEditor(); };
        form.querySelector('[data-field-add]').addEventListener('click', () => {
            if (features && moduleState.selected === null) return;
            if (fields.length >= maximumFields) { window.Panel.notify({ type: 'warning', message: `En fazla ${maximumFields} alan eklenebilir.` }); return; }
            let number = fields.length + 1;
            while (fields.some(field => field.key === 'alan_' + number)) number++;
            fields.push(fresh('alan_' + number));
            selected = fields.length - 1; render();
            editor.querySelector('[data-field-prop="label"]').focus();
        });
        list.addEventListener('click', async (event) => {
            const choose = event.target.closest('[data-field-select]');
            if (choose) { selected = Number(choose.dataset.fieldSelect); renderList(); showEditor(); return; }
            const button = event.target.closest('[data-field-action]');
            if (!button) return;
            const index = Number(button.dataset.index);
            const action = button.dataset.fieldAction;
            if (action === 'remove') {
                const confirmed = await window.Panel.confirm({
                    title: 'Form alanını kaldır',
                    message: features ? 'Bu alan formdan kaldırılacak. Kayıtlı içerik değerleri korunur.' : 'Bu alanı formdan kaldırmak istediğinizden emin misiniz? Eski başvurular korunur.',
                    confirmLabel: 'Kaldır',
                });
                if (!confirmed) return;
                fields.splice(index, 1); selected = Math.min(index, fields.length - 1);
            } else {
                const target = index + (action === 'up' ? -1 : 1);
                [fields[index], fields[target]] = [fields[target], fields[index]];
                selected = target;
            }
            render();
        });
        editor.addEventListener('input', (event) => {
            const field = fields[selected]; if (!field) return;
            const input = event.target;
            if (input.matches('[data-field-options]')) {
                field.options = input.value.split('\n').filter(line => line.trim()).map(line => {
                    const divider = line.indexOf('|');
                    const label = divider < 0 ? line.trim() : line.slice(divider + 1).trim();
                    return { value: divider < 0 ? toSlug(label) : line.slice(0, divider).trim(), label };
                });
            } else if (input.dataset.fieldProp) {
                const key = input.dataset.fieldProp;
                let value = input.type === 'checkbox' ? input.checked : input.type === 'number' ? (input.value === '' ? null : Number(input.value)) : input.value;
                if (input.hasAttribute('data-field-list-value')) value = input.value ? input.value.split(',').map(item => item.trim()).filter(Boolean) : null;
                if (input.hasAttribute('data-field-default')) value = defaultValue(input.value, field.value_type);
                if (features && ['attributes.layout', 'attributes.variant'].includes(key) && value === '') value = null;
                updateFieldProperty(field, key, value, features);
                if (key === 'key') autoKeys.delete(field);
                if (key === 'label' && autoKeys.has(field)) {
                    field.key = toSlug(input.value).replaceAll('-', '_') || field.key;
                    editor.querySelector('[data-field-prop="key"]').value = field.key;
                }
                if (key === 'input_type' && !features) {
                    if (field.input_type === 'email') field.role = fields.some((entry, index) => index !== selected && entry.role === 'email') ? '' : 'email';
                    else if (field.role === 'email') field.role = '';
                    editor.querySelector('[data-field-prop="role"]').value = field.role;
                }
            }
            if (features && ['input_type', 'value_type'].includes(input.dataset.fieldProp)) showEditor();
            persist(); renderList(); renderPreview(); settingsVisibility();
        });
        list.addEventListener('pointerdown', event => { blockedDrag = Boolean(event.target.closest('[data-field-action]')); });
        list.addEventListener('dragstart', event => {
            const row = event.target.closest('[data-field-index]');
            if (!row || blockedDrag) { event.preventDefault(); return; }
            dragged = Number(row.dataset.fieldIndex); event.dataTransfer.setData('text/plain', String(dragged));
        });
        list.addEventListener('dragover', event => { if (dragged !== null) event.preventDefault(); });
        list.addEventListener('drop', event => {
            event.preventDefault(); const row = event.target.closest('[data-field-index]');
            if (!row || dragged === null) return;
            const target = Number(row.dataset.fieldIndex);
            const [field] = fields.splice(dragged, 1); fields.splice(target, 0, field);
            selected = target; dragged = null; render();
        });
        list.addEventListener('dragend', () => { dragged = null; });
        form.querySelectorAll('[data-form-preset]').forEach(button => button.addEventListener('click', () => {
            if (fields.length && !window.confirm('Mevcut alanlar başlangıç şablonuyla değiştirilsin mi?')) return;
            const name = fresh('full_name', 'Ad soyad', 'text', 'name'); name.required = true; name.width = 'half';
            const email = fresh('email', 'E-posta', 'email', 'email'); email.required = true; email.width = 'half';
            const phone = fresh('phone', 'Telefon', 'tel', 'phone');
            fields = button.dataset.formPreset === 'callback' ? [name, phone] : [name, email, phone];
            if (button.dataset.formPreset === 'application') fields.push(fresh('cv', 'Özgeçmiş', 'file'));
            else if (button.dataset.formPreset !== 'callback') { const message = fresh('message', 'Mesaj', 'textarea'); message.required = true; fields.push(message); }
            selected = 0; render();
        }));
        form.querySelector('[data-submit-label]')?.addEventListener('input', renderPreview);
        form.querySelector('[data-import-fields]')?.addEventListener('click', async (event) => {
            if (fields.length && !window.confirm('Mevcut alanlar içeri alınan alanlarla değiştirilsin mi?')) return;
            const button = event.target; button.disabled = true;
            const message = form.querySelector('[data-import-message]');
            try {
                const response = await fetch(form.dataset.importUrl, {
                    method: 'POST', credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': form.querySelector('[name="_token"]').value },
                    body: JSON.stringify({ html: form.querySelector('[data-import-html]').value }),
                });
                const data = await response.json();
                if (!response.ok) throw new Error(data.errors ? Object.values(data.errors).flat().join(' ') : data.message);
                fields = data.fields; selected = fields.length ? 0 : -1; render();
                message.textContent = fields.length + ' alan alındı. ' + data.warnings.join(' ');
            } catch (error) { message.textContent = error.message || 'HTML içeri alınamadı.'; }
            finally { button.disabled = false; }
        });
        if (features) {
            const select = form.querySelector('[data-feature-form]');
            const label = form.querySelector('[data-feature-label]');
            const refreshForms = () => {
                select.replaceChildren();
                Object.entries(moduleState.document.forms).forEach(([key, definition]) => select.add(new Option((definition.label || 'Ek alanlar') + ' (' + key + ')', key)));
                select.value = moduleState.selected ?? '';
                label.value = moduleState.selected === null ? '' : moduleState.document.forms[moduleState.selected].label ?? '';
                select.disabled = label.disabled = form.querySelector('[data-feature-remove]').disabled = moduleState.selected === null;
                form.querySelector('[data-feature-workspace]').hidden = moduleState.selected === null;
                fields = moduleState.fields;
                selected = fields.length ? 0 : -1;
                render();
            };
            select.addEventListener('change', () => { moduleState.select(select.value); refreshForms(); });
            label.addEventListener('input', () => {
                if (moduleState.selected === null) return;
                moduleState.document.forms[moduleState.selected].label = label.value;
                select.selectedOptions[0].textContent = (label.value || 'Ek alanlar') + ' (' + moduleState.selected + ')';
                persist();
            });
            form.querySelector('[data-feature-add]').addEventListener('click', () => {
                const key = form.querySelector('[data-feature-new-key]');
                const title = form.querySelector('[data-feature-new-label]');
                const message = form.querySelector('[data-feature-message]');
                if (!moduleState.add(key.value.trim(), title.value.trim())) {
                    message.textContent = 'Benzersiz bir form anahtarı girin. Küçük harfle başlayın; yalnız küçük harf, sayı ve alt çizgi kullanın.';
                    key.focus();
                    return;
                }
                key.value = title.value = message.textContent = '';
                refreshForms();
            });
            form.querySelector('[data-feature-remove]').addEventListener('click', async () => {
                if (moduleState.selected === null) return;
                if (!await window.Panel.confirm({ title: 'Ek alan formunu kaldır', message: 'Bu formun alanları ekleme ve düzenleme ekranlarından kaldırılır. Kayıtlı içerik değerleri korunur.', confirmLabel: 'Kaldır' })) return;
                moduleState.remove(); refreshForms();
            });
            refreshForms();
        }
        form.addEventListener('submit', persist);
        render();
    });
}
