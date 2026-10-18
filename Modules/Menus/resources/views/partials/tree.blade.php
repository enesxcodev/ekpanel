<ul class="list-unstyled mb-0">
    @foreach ($nodes as $node)
        <li class="border rounded p-3 mb-2">
            <div class="d-flex flex-wrap gap-3 align-items-start">
                <div class="flex-fill text-break">
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        @if ($node['item']->icon)<x-admin.icon :name="$node['item']->icon" />@endif
                        <strong>{{ $node['item']->label }}</strong>
                        <span class="badge {{ $node['item']->is_active ? 'bg-green-lt' : 'bg-secondary-lt' }}">{{ $node['item']->is_active ? 'Var' : 'Yok' }}</span>
                        <span class="badge bg-secondary-lt">Sıra: {{ $node['item']->sort_order }}</span>
                        @if ($node['item']->new_tab)<span class="badge bg-blue-lt">Yeni sekme</span>@endif
                    </div>
                    @if ($node['item']->target_type === 'group')
                        <div class="text-secondary mt-1">Grup başlığı</div>
                    @elseif ($node['target']['error'])
                        <div class="text-warning mt-1">{{ $node['target']['error'] }}</div>
                    @elseif ($node['target']['url'] === null)
                        <div class="text-secondary mt-1">İçerik grubu (bağlantısız)</div>
                    @else
                        <div class="text-secondary mt-1">{{ $node['target']['url'] }}</div>
                    @endif
                    @if ($node['item']->dynamic_children)
                        <div class="text-secondary small mt-1">Doğrudan kategoriye bağlı yayımlanmış sayfalar otomatik eklenir.</div>
                    @endif
                </div>
                @can('menus.update')
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.menus.items.edit', [$menu, $node['item']]) }}" class="btn btn-sm btn-outline-primary">Düzenle</a>
                        <form method="POST" action="{{ route('admin.menus.items.destroy', [$menu, $node['item']]) }}" data-confirm="Bu öğeyi silmek istediğinizden emin misiniz? Alt öğeler varsa önce taşıyın veya silin.">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Sil</button>
                        </form>
                    </div>
                @endcan
            </div>
            @if (count($node['children']) > 0)
                <div class="mt-3 ms-3 border-start ps-3">
                    @include('menus::partials.tree', ['nodes' => $node['children']])
                </div>
            @endif
        </li>
    @endforeach
</ul>
