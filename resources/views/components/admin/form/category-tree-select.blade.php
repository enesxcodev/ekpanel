@props([
    'tree' => [],
    'name',
    'id',
    'label' => 'Kategori',
    'selected' => '',
    'required' => false,
    'allowEmpty' => false,
    'emptyLabel' => 'Kategorisiz',
    'hint' => null,
    'treeLabel' => 'Kategoriler',
    'searchLabel' => 'Kategori yolu veya adı ara',
    'filterTarget' => null,
])
@php
    $findSelected = function (array $nodes) use (&$findSelected, $selected): ?array {
        foreach ($nodes as $node) {
            if ((string) $node['id'] === (string) $selected) {
                return $node;
            }
            $found = $findSelected($node['children']);
            if ($found !== null) {
                return $found;
            }
        }

        return null;
    };
    $selectedCategory = $findSelected($tree);
    $selectedLabel = $selectedCategory['path'] ?? ($allowEmpty ? $emptyLabel : 'Kategori seçin');
@endphp
<div class="" data-category-tree-select>
    <label class="form-label" for="{{ $id }}">{{ $label }}</label>
    <input type="hidden" name="{{ $name }}" value="{{ $selected }}" data-category-tree-value @if ($filterTarget) data-table-filter="category_id" data-table-target="{{ $filterTarget }}" @endif>
    <div class="dropdown w-100" data-category-tree-dropdown>
        <button class="form-select text-start" type="button" id="{{ $id }}" data-category-tree-trigger aria-haspopup="tree" aria-expanded="false" aria-controls="{{ $id }}-tree">
            <span data-category-tree-label>{{ $selectedLabel }}</span>
        </button>
        <div class="dropdown-menu p-2 w-100" data-category-tree-menu>
            <input class="form-control mb-2" type="search" placeholder="{{ $searchLabel }}" aria-label="{{ $searchLabel }}" autocomplete="off" role="combobox" aria-expanded="true" aria-controls="{{ $id }}-tree" aria-autocomplete="list" data-category-tree-search>
            @if ($hint)<div class="small text-secondary px-2 pb-2">{{ $hint }}</div>@endif
            <ul class="list-unstyled mb-0 category-tree-list" id="{{ $id }}-tree" role="tree" aria-label="{{ $treeLabel }}">
                @if ($allowEmpty)
                    <li role="treeitem" tabindex="-1" aria-level="1" aria-selected="{{ $selected === '' ? 'true' : 'false' }}" data-category-tree-option data-category-tree-id="" data-category-tree-path="{{ $emptyLabel }}"><span class="category-tree-option-label">{{ $emptyLabel }}</span></li>
                @endif
                @include('components.admin.form.category-tree-options', ['nodes' => $tree, 'selected' => $selected])
            </ul>
            <div class="small text-secondary px-2 py-2" role="status" aria-live="polite" data-category-tree-status></div>
        </div>
    </div>
    @error($name)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
