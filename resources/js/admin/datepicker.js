import { Datepicker } from '@tabler/core';
import VanillaCalendarPro from '@tabler/core/dist/libs/vanilla-calendar-pro/index.js';

window.VanillaCalendarPro = VanillaCalendarPro;

const formatIsoDate = (date) => [
    date.getFullYear(),
    String(date.getMonth() + 1).padStart(2, '0'),
    String(date.getDate()).padStart(2, '0'),
].join('-');

export function initializeDatepickers() {
    document.querySelectorAll('input[type="date"], input[type="datetime-local"]').forEach((input) => {
        const includesTime = input.type === 'datetime-local';
        const initialValue = input.value;
        let dateInput = input;
        let timeInput;

        if (includesTime) {
            const group = document.createElement('div');
            group.className = 'input-group';

            dateInput = document.createElement('input');
            dateInput.type = 'text';
            dateInput.className = input.className;
            dateInput.id = `${input.id}-date`;
            dateInput.value = initialValue.split('T')[0];
            dateInput.required = input.required;
            dateInput.disabled = input.disabled;
            dateInput.readOnly = input.readOnly;

            for (const attribute of ['aria-invalid', 'aria-describedby']) {
                if (input.hasAttribute(attribute)) {
                    dateInput.setAttribute(attribute, input.getAttribute(attribute));
                }
            }

            const label = Array.from(input.labels ?? [])[0];
            if (label) {
                label.htmlFor = dateInput.id;
            }

            timeInput = document.createElement('input');
            timeInput.type = 'time';
            timeInput.className = 'form-control panel-datepicker-time';
            timeInput.value = initialValue.split('T')[1]?.slice(0, 5) || '';
            timeInput.disabled = input.disabled;
            timeInput.readOnly = input.readOnly;
            timeInput.setAttribute('aria-label', `${label?.textContent.trim() || 'Yayın tarihi'} saati`);

            input.type = 'hidden';
            input.after(group);
            group.append(dateInput, timeInput);
        } else {
            input.type = 'text';
        }

        dateInput.placeholder = 'YYYY-AA-GG';
        dateInput.autocomplete = 'off';
        dateInput.setAttribute('data-bs-toggle', 'datepicker');
        const picker = Datepicker.getOrCreateInstance(dateInput, {
            locale: 'tr-TR',
            firstWeekday: 1,
            dateFormat: formatIsoDate,
            selectedDates: initialValue ? [initialValue.split('T')[0]] : [],
            dateMin: input.min?.split('T')[0] || null,
            dateMax: input.max?.split('T')[0] || null,
        });

        const syncValue = () => {
            if (includesTime) {
                input.value = dateInput.value ? `${dateInput.value}T${timeInput.value || '00:00'}` : '';
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
        };

        dateInput.addEventListener('change.bs.datepicker', () => {
            dateInput.dispatchEvent(new Event('change', { bubbles: true }));
        });
        dateInput.addEventListener('change', syncValue);
        timeInput?.addEventListener('change', syncValue);

        input.form?.addEventListener('reset', () => {
            window.setTimeout(() => {
                dateInput.value = initialValue.split('T')[0];
                if (timeInput) {
                    timeInput.value = initialValue.split('T')[1]?.slice(0, 5) || '';
                }
                picker.setSelectedDates(initialValue ? [initialValue.split('T')[0]] : []);
                syncValue();
            });
        });
    });
}
