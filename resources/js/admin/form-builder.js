import { toSlug } from './slug';

export function initializeFormBuilder() {
    document.querySelectorAll('[data-form-builder]').forEach((form) => {
        const list = form.querySelector('[data-field-list]');
        const editor = form.querySelector('[data-field-editor]');
        const preview = form.querySelector('[data-form-preview]');
        const hidden = form.querySelector('[data-schema-value]');
        let fields = [];
        try { fields = JSON.parse(form.dataset.fields || '[]'); } catch {}
        if (!Array.isArray(fields)) fields = [];
        let selected = fields.length ? 0 : -1;
        let dragged = null;
        let blockedDrag = false;
        const types = { text: 'Metin', email: 'E-posta', tel: 'Telefon', textarea: 'Uzun metin', number: 'Sayı', select: 'Seçim listesi', radio: 'Radio', boolean: 'Onay kutusu', file: 'Dosya' };
        const node = (tag, cls, text) => {
            const el = document.createElement(tag);
            if (cls) el.className = cls;
            if (text !== undefined) el.textContent = text;
            return el;
        };
        const autoKeys = new WeakSet();
        const fresh = (key, label = 'Yeni alan', type = 'text', role = '') => {
            const field = {
            key, label, input_type: type, required: false, placeholder: '', description: '', width: 'full',
            css_class: '', input_class: '', role, options: [], max_length: null, max_size: 5120, extensions: 'pdf,docx',
            };
            if (/^alan_[0-9]+$/.test(key)) autoKeys.add(field);
            return field;
        };
        const persist = () => { hidden.value = JSON.stringify(fields); };
        const renderPreview = () => {
            preview.replaceChildren();
            const grid = node('div', 'ek-form__fields');
            fields.forEach((field) => {
                const wrap = node('div', 'ek-form__field ek-form__field--' + field.width);
                wrap.append(node('label', 'ek-form__label', field.label + (field.required ? ' *' : '')));
                if (field.input_type === 'radio') {
                    field.options.forEach((option) => {
                        const label = node('label', 'ek-form__choice');
                        const input = node('input'); input.type = 'radio'; input.disabled = true;
                        label.append(input, document.createTextNode(' ' + option.label)); wrap.append(label);
                    });
                } else {
                    const tag = field.input_type === 'textarea' ? 'textarea' : field.input_type === 'select' ? 'select' : 'input';
                    const input = node(tag, 'ek-form__input');
                    if (tag === 'input') input.type = field.input_type === 'boolean' ? 'checkbox' : field.input_type;
                    if (tag === 'textarea') input.rows = 4;
                    if (tag === 'select') {
                        input.add(new Option(field.placeholder || 'Seçin', ''));
                        field.options.forEach(option => input.add(new Option(option.label, option.value)));
                    }
                    input.placeholder = field.placeholder;
                    input.disabled = true;
                    wrap.append(input);
                }
                if (field.description) wrap.append(node('small', 'ek-form__help', field.description));
                grid.append(wrap);
            });
            if (!fields.length) grid.append(node('p', 'text-secondary', 'Önizlemek için bir alan ekleyin veya başlangıç şablonu seçin.'));
            const submit = node('button', 'ek-form__submit', form.querySelector('[data-submit-label]').value || 'Gönder');
            submit.type = 'button';
            preview.append(grid, submit);
        };
        const renderList = () => {
            list.replaceChildren();
            fields.forEach((field, index) => {
                const row = node('div', 'fb-field-row' + (index === selected ? ' is-selected' : ''));
                row.dataset.fieldIndex = index; row.draggable = true;
                const choose = node('button', 'fb-field-choose', '⠿  ' + field.label);
                choose.type = 'button'; choose.dataset.fieldSelect = index;
                const badge = node('span', 'badge bg-blue-lt', types[field.input_type]);
                const required = node('span', field.required ? 'badge bg-red-lt' : 'badge bg-secondary-lt', field.required ? 'Zorunlu' : 'İsteğe bağlı');
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
            if (!field) return;
            editor.querySelectorAll('[data-field-prop]').forEach(input => {
                const value = field[input.dataset.fieldProp];
                if (input.type === 'checkbox') input.checked = Boolean(value);
                else input.value = value ?? '';
            });
            editor.querySelector('[data-field-options]').value = (field.options || []).map(option => option.value + '|' + option.label).join('\n');
            settingsVisibility();
        };
        const settingsVisibility = () => {
            const field = fields[selected]; if (!field) return;
            editor.querySelector('[data-options-setting]').hidden = !['select', 'radio'].includes(field.input_type);
            editor.querySelector('[data-length-setting]').hidden = !['text', 'email', 'tel', 'textarea'].includes(field.input_type);
            editor.querySelectorAll('[data-file-setting]').forEach(el => { el.hidden = field.input_type !== 'file'; });
        };
        const render = () => { persist(); renderList(); renderPreview(); showEditor(); };
        form.querySelector('[data-field-add]').addEventListener('click', () => {
            if (fields.length >= 50) { window.Panel.notify({ type: 'warning', message: 'En fazla 50 alan eklenebilir.' }); return; }
            let number = fields.length + 1;
            while (fields.some(field => field.key === 'alan_' + number)) number++;
            fields.push(fresh('alan_' + number));
            selected = fields.length - 1; render();
            editor.querySelector('[data-field-prop="label"]').focus();
        });
        list.addEventListener('click', (event) => {
            const choose = event.target.closest('[data-field-select]');
            if (choose) { selected = Number(choose.dataset.fieldSelect); renderList(); showEditor(); return; }
            const button = event.target.closest('[data-field-action]');
            if (!button) return;
            const index = Number(button.dataset.index);
            const action = button.dataset.fieldAction;
            if (action === 'remove') {
                if (!window.confirm('Bu alanı formdan kaldırmak istediğinizden emin misiniz? Eski başvurular korunur.')) return;
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
                field[key] = input.type === 'checkbox' ? input.checked : input.type === 'number' ? (input.value === '' ? null : Number(input.value)) : input.value;
                if (key === 'key') autoKeys.delete(field);
                if (key === 'label' && autoKeys.has(field)) {
                    field.key = toSlug(input.value).replaceAll('-', '_') || field.key;
                    editor.querySelector('[data-field-prop="key"]').value = field.key;
                }
                if (key === 'input_type') {
                    if (field.input_type === 'email') field.role = fields.some((entry, index) => index !== selected && entry.role === 'email') ? '' : 'email';
                    else if (field.role === 'email') field.role = '';
                    editor.querySelector('[data-field-prop="role"]').value = field.role;
                }
            }
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
        form.querySelector('[data-submit-label]').addEventListener('input', renderPreview);
        form.querySelector('[data-import-fields]').addEventListener('click', async (event) => {
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
        form.addEventListener('submit', persist);
        render();
    });
}
