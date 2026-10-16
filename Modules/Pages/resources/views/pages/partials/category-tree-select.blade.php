@php
    $findSelected = function (array $nodes) use (&$findSelected, $selectedCategoryId): ?array {
        foreach ($nodes as $node) {
            if ((string) $node['id'] === (string) $selectedCategoryId) {
                return $node;
            }
            $selected = $findSelected($node['children']);
            if ($selected !== null) {
                return $selected;
            }
        }

        return null;
    };
    $selectedCategory = $findSelected($categoryTree);
    $selectedCategoryLabel = $selectedCategory['path'] ?? 'Kategorisiz';
@endphp
<div class="mb-3" data-category-tree-select>
    <label class="form-label" for="page-category-search">Kategori (isteğe bağlı)</label>
    <input type="hidden" name="page_category_id" value="{{ $selectedCategoryId }}" data-category-tree-value>
    <div class="dropdown w-100" data-category-tree-dropdown>
        <button class="form-select text-start" type="button" id="page-category-search" data-category-tree-trigger aria-haspopup="tree" aria-expanded="false" aria-controls="page-category-tree">
            <span data-category-tree-label>{{ $selectedCategoryLabel }}</span>
        </button>
        <div class="dropdown-menu p-2 w-100" data-category-tree-menu>
            <input class="form-control mb-2" type="search" placeholder="Kategori yolu veya adı ara" aria-label="Kategori yolu veya adı ara" autocomplete="off" role="combobox" aria-expanded="true" aria-controls="page-category-tree" aria-autocomplete="list" data-category-tree-search>
            <div class="small text-secondary px-2 pb-2">Kategorisiz seçilirse sayfa adresi /{slug} olur.</div>
            <ul class="list-unstyled mb-0 category-tree-list" id="page-category-tree" role="tree" aria-label="Sayfa kategorileri">
                <li role="treeitem" tabindex="-1" aria-level="1" aria-selected="{{ $selectedCategoryId === '' ? 'true' : 'false' }}" data-category-tree-option data-category-tree-id="" data-category-tree-path="Kategorisiz">
                    <span class="category-tree-option-label">Kategorisiz</span>
                </li>
                @include('pages::pages.partials.category-tree-items', ['nodes' => $categoryTree])
            </ul>
            <div class="small text-secondary px-2 py-2" role="status" aria-live="polite" data-category-tree-status></div>
        </div>
    </div>
    @error('page_category_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
</div>
