export function initializePages() {
    initializeCategoryTreeSelects();
}

function initializeCategoryTreeSelects() {
    document.querySelectorAll('[data-category-tree-select]').forEach((component) => {
        const trigger = component.querySelector('[data-category-tree-trigger]');
        const menu = component.querySelector('[data-category-tree-menu]');
        const search = component.querySelector('[data-category-tree-search]');
        const hidden = component.querySelector('[data-category-tree-value]');
        const label = component.querySelector('[data-category-tree-label]');
        const status = component.querySelector('[data-category-tree-status]');
        const options = [...component.querySelectorAll('[data-category-tree-option]')];
        if (!trigger || !menu || !search || !hidden || !label || !status) {
            return;
        }

        const setOpen = (open) => {
            menu.classList.toggle('show', open);
            trigger.setAttribute('aria-expanded', String(open));
            search.setAttribute('aria-expanded', String(open));
            if (open) {
                search.focus();
            }
        };
        const visibleOptions = () => options.filter((option) => !option.hidden);
        const filterOptions = () => {
            const term = search.value.trim().toLocaleLowerCase('tr');
            [...options].reverse().forEach((option) => {
                const matches = (option.dataset.categoryTreePath ?? '').toLocaleLowerCase('tr').includes(term);
                const hasMatchingDescendant = [...option.querySelectorAll('[data-category-tree-option]')].some((child) => !child.hidden);
                option.hidden = !matches && !hasMatchingDescendant;
                option.dataset.active = 'false';
            });
            const visible = visibleOptions();
            status.textContent = visible.length === 0 ? 'Eşleşen kategori bulunamadı.' : `${visible.length} kategori gösteriliyor.`;
            if (visible[0]) {
                visible[0].dataset.active = 'true';
                search.setAttribute('aria-activedescendant', visible[0].id || '');
            } else {
                search.removeAttribute('aria-activedescendant');
            }
        };
        const select = (option) => {
            hidden.value = option.dataset.categoryTreeId ?? '';
            label.textContent = option.dataset.categoryTreePath || 'Kategorisiz';
            options.forEach((item) => item.setAttribute('aria-selected', String(item === option)));
            hidden.dispatchEvent(new Event('change', { bubbles: true }));
            setOpen(false);
            trigger.focus();
        };

        trigger.addEventListener('click', () => {
            const open = trigger.getAttribute('aria-expanded') !== 'true';
            setOpen(open);
            if (open) {
                search.value = '';
                filterOptions();
            }
        });
        options.forEach((option, index) => {
            if (!option.id) {
                option.id = `page-category-option-${index}`;
            }
        });
        component.addEventListener('click', (event) => {
            if (!(event.target instanceof Element)) {
                return;
            }

            const option = event.target.closest('[data-category-tree-option]');
            if (option && component.contains(option)) {
                select(option);
            }
        });
        search.addEventListener('input', filterOptions);
        search.addEventListener('keydown', (event) => {
            const visible = visibleOptions();
            const activeIndex = visible.findIndex((option) => option.dataset.active === 'true');
            let nextIndex = activeIndex;
            if (event.key === 'ArrowDown') {
                nextIndex = Math.min(activeIndex + 1, visible.length - 1);
            } else if (event.key === 'ArrowUp') {
                nextIndex = Math.max(activeIndex - 1, 0);
            } else if (event.key === 'Enter' && visible[activeIndex]) {
                event.preventDefault();
                select(visible[activeIndex]);
                return;
            } else if (event.key === 'Escape') {
                setOpen(false);
                trigger.focus();
                return;
            } else {
                return;
            }
            event.preventDefault();
            visible.forEach((option, index) => {
                option.dataset.active = String(index === nextIndex);
            });
            if (visible[nextIndex]) {
                search.setAttribute('aria-activedescendant', visible[nextIndex].id);
                visible[nextIndex].scrollIntoView({ block: 'nearest' });
            }
        });
        document.addEventListener('click', (event) => {
            if (!component.contains(event.target)) {
                setOpen(false);
            }
        });

        const initialOption = options.find((option) => option.dataset.categoryTreeId === hidden.value);
        if (initialOption) {
            label.textContent = initialOption.dataset.categoryTreePath || 'Kategorisiz';
        }
    });
}

