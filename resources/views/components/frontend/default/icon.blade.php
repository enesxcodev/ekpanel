@props(['name' => 'briefcase'])

@if(in_array($name, ['building', 'building-factory', 'device-desktop', 'shopping-bag', 'shopping-cart', 'box', 'shield-check', 'leaf', 'chart-bar', 'calendar', 'map-pin', 'phone', 'user'], true))
    <svg {{ $attributes->class(['icon']) }} xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
        @switch($name)
            @case('building')<path d="M4 21h16M6 21V3h12v18M9 6h1m4 0h1M9 9h1m4 0h1M9 12h1m4 0h1M10 21v-5h4v5" />@break
            @case('building-factory')<path d="M3 21h18V10l-6 3V9l-6 4V4H5v17M13 17h1m3 0h1" />@break
            @case('device-desktop')<rect x="3" y="4" width="18" height="13" rx="1" /><path d="M7 21h10M12 17v4" />@break
            @case('shopping-bag')<path d="M5 7h14l1 14H4L5 7zM9 7V5a3 3 0 0 1 6 0v2" />@break
            @case('shopping-cart')<path d="M3 3h2l3 12h10l3-9H6M9 20h.01M17 20h.01" /><circle cx="9" cy="20" r="1" /><circle cx="17" cy="20" r="1" />@break
            @case('box')<path d="m12 3 9 5-9 5-9-5 9-5zM3 8v9l9 5 9-5V8M12 13v9M7.5 5.5l9 5" />@break
            @case('shield-check')<path d="m12 3 8 3v6c0 5-8 9-8 9s-8-4-8-9V6l8-3zM8 12l3 3 5-6" />@break
            @case('leaf')<path d="M20 4c-9-1-16 3-16 9a7 7 0 0 0 7 7c6 0 10-7 9-16zM4 21l11-11" />@break
            @case('chart-bar')<path d="M3 21h18M5 17V9h3v8M11 17V5h3v12M17 17V2h3v15" />@break
            @case('calendar')<rect x="3" y="5" width="18" height="16" rx="2" /><path d="M16 3v4M8 3v4M3 11h18M8 15h2m4 0h2M8 18h2" />@break
            @case('map-pin')<path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0z" /><circle cx="12" cy="10" r="2.5" />@break
            @case('phone')<path d="m5 3 4 1 1 5-3 2a14 14 0 0 0 6 6l2-3 5 1 1 4c0 1-1 2-3 2A18 18 0 0 1 3 6c0-2 1-3 2-3z" />@break
            @case('user')<circle cx="12" cy="7" r="4" /><path d="M5 21v-2a7 7 0 0 1 14 0v2" />@break
        @endswitch
    </svg>
@else
    <x-admin.icon :name="$name" {{ $attributes }} />
@endif
