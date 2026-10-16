<ul class="flex flex-wrap gap-4">
    @foreach ($items as $item)
        <li>
            @if ($item['url'] !== null)
                <a href="{{ $item['url'] }}" @if ($item['new_tab']) target="_blank" rel="noopener noreferrer" @endif>{{ $item['label'] }}</a>
            @else
                <span>{{ $item['label'] }}</span>
            @endif
            @if ($item['children'] !== [])
                @include('menus::public.items', ['items' => $item['children']])
            @endif
        </li>
    @endforeach
</ul>
