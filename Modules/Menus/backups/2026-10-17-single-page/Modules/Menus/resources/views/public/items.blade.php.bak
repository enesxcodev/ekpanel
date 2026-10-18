<ul class="flex flex-wrap gap-4">
    @foreach ($items as $item)
        <li>
            @if ($item['url'] !== null)
                <a href="{{ $item['url'] }}" @if ($item['new_tab']) target="_blank" rel="noopener noreferrer" @endif>@if ($item['icon'] ?? null)<x-admin.icon :name="$item['icon']" class="inline-block h-4 w-4" />@endif{{ $item['label'] }}</a>
            @else
                <span>@if ($item['icon'] ?? null)<x-admin.icon :name="$item['icon']" class="inline-block h-4 w-4" />@endif{{ $item['label'] }}</span>
            @endif
            @if ($item['children'] !== [])
                @include('menus::public.items', ['items' => $item['children']])
            @endif
        </li>
    @endforeach
</ul>
