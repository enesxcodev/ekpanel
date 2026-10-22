function normalizedText(value) {
    return value.replace(/\s+/g, ' ').trim().toLocaleLowerCase('tr-TR');
}

function menuTree(menu) {
    return [...menu.children]
        .filter((item) => item.matches('.nav-item'))
        .map((item) => {
            const link = item.querySelector(':scope > .nav-link');
            const label = link?.querySelector('.nav-link-title')?.textContent?.trim() ?? '';
            const children = item.querySelector(':scope > .collapse > .navbar-nav');

            return {
                children: children ? menuTree(children) : [],
                icon: link?.querySelector('.nav-link-icon') ?? null,
                label,
                searchText: normalizedText(label),
                url: link?.getAttribute('href') ?? '',
            };
        })
        .filter((entry) => entry.label !== '');
}

function matchingEntries(entries, query) {
    return entries.flatMap((entry) => {
        const children = matchingEntries(entry.children, query);
        const isMatch = entry.searchText.includes(query);

        if (!isMatch && children.length === 0) {
            return [];
        }

        return [{
            ...entry,
            children: isMatch ? entry.children : children,
        }];
    });
}

export function initializeTopbarSearch() {
    const search = document.querySelector('[data-topbar-search]');
    const input = search?.querySelector('[data-topbar-search-input]');
    const results = search?.querySelector('[data-topbar-search-results]');

    if (!search || !input || !results) {
        return;
    }

    const sidebarMenu = document.querySelector('.navbar-vertical .navbar-nav');
    const entries = sidebarMenu ? menuTree(sidebarMenu) : [];

    function close() {
        results.hidden = true;
        results.replaceChildren();
        input.setAttribute('aria-expanded', 'false');
    }

    function appendIcon(container, icon) {
        if (!icon) {
            return;
        }

        const clonedIcon = icon.cloneNode(true);
        clonedIcon.removeAttribute('class');
        clonedIcon.classList.add('nav-link-icon');
        container.append(clonedIcon);
    }

    function createResult(entry, depth) {
        const result = document.createElement('a');
        result.className = 'dropdown-item admin-topbar-search-result';
        result.href = entry.url;
        result.role = 'option';
        result.dataset.depth = depth;
        result.style.setProperty('--admin-search-depth', depth);

        appendIcon(result, entry.icon);

        const label = document.createElement('span');
        label.textContent = entry.label;
        result.append(label);

        return result;
    }

    function appendResults(container, entries, depth = 0) {
        entries.forEach((entry) => {
            if (entry.children.length === 0) {
                container.append(createResult(entry, depth));
                return;
            }

            const group = document.createElement('div');
            group.className = 'admin-topbar-search-group';
            appendIcon(group, entry.icon);
            group.append(document.createTextNode(entry.label));
            container.append(group);
            appendResults(container, entry.children, depth + 1);
        });
    }

    function render() {
        const query = normalizedText(input.value);

        if (query === '') {
            close();
            return;
        }

        const matches = matchingEntries(entries, query);
        const fragment = document.createDocumentFragment();

        if (matches.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'admin-topbar-search-empty';
            empty.setAttribute('role', 'status');
            empty.textContent = 'İlgili başlık bulunamadı.';
            fragment.append(empty);
        } else {
            appendResults(fragment, matches);
        }

        results.replaceChildren(fragment);
        results.hidden = false;
        input.setAttribute('aria-expanded', 'true');
    }

    search.addEventListener('submit', (event) => {
        event.preventDefault();
        results.querySelector('a')?.click();
    });

    input.addEventListener('input', render);
    input.addEventListener('focus', () => {
        if (input.value.trim() !== '') {
            render();
        }
    });
    input.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            close();
            input.blur();
            return;
        }

        if (event.key === 'ArrowDown') {
            const firstResult = results.querySelector('a');
            if (firstResult) {
                event.preventDefault();
                firstResult.focus();
            }
        }
    });
    document.addEventListener('click', (event) => {
        if (!search.contains(event.target)) {
            close();
        }
    });
}
