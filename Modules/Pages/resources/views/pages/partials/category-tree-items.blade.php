@foreach ($nodes as $node)
    <li role="treeitem" tabindex="-1" aria-level="{{ $node['depth'] + 1 }}" aria-selected="{{ (string) $selectedCategoryId === (string) $node['id'] ? 'true' : 'false' }}" data-category-tree-option data-category-tree-id="{{ $node['id'] }}" data-category-tree-path="{{ $node['path'] }}" style="--category-depth: {{ $node['depth'] }}">
        <span class="category-tree-option-label">{{ $node['name'] }}</span>
        <span class="category-tree-option-path">{{ $node['path'] }}</span>
        @if ($node['children'] !== [])
            <ul role="group" class="list-unstyled mb-0">
                @include('pages::pages.partials.category-tree-items', ['nodes' => $node['children']])
            </ul>
        @endif
    </li>
@endforeach
