@foreach($items as $item)
    @if(!empty($item['children']))
        <details class="nav-dropdown relative">
            <summary class="nav-link flex cursor-pointer items-center gap-1.5">{{ $item['label'] }} <x-frontend.default.icon name="chevron-down" class="size-3.5" /></summary>
            <div class="nav-dropdown-panel">
                @if(!empty($item['url']))
                    <a href="{{ $item['url'] }}" class="nav-link" @if($item['new_tab'] ?? false) target="_blank" rel="noopener noreferrer" @endif>{{ $item['label'] }}</a>
                @endif
                @include('frontend.themes.default.partials.navigation', ['items' => $item['children']])
            </div>
        </details>
    @elseif(!empty($item['url']))
        <a href="{{ $item['url'] }}" class="nav-link" @if($item['new_tab'] ?? false) target="_blank" rel="noopener noreferrer" @endif>{{ $item['label'] }}</a>
    @else
        <span class="nav-link">{{ $item['label'] }}</span>
    @endif
@endforeach
