@props(['name' => 'layout-grid'])

<svg {{ $attributes->class(['icon']) }} xmlns="http://www.w3.org/2000/svg" width="24" height="24"
     viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
    @switch($name)
        @case('users')
            <circle cx="9" cy="7" r="4" />
            <path d="M3 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2M16 3a4 4 0 0 1 0 8M21 21v-2a4 4 0 0 0-3-3.87" />
            @break
        @case('chevron-down')
            <path d="M6 9l6 6l6 -6" />
            @break
        @case('settings')
            <path d="M4 6h16M4 12h16M4 18h16" />
            <path d="M8 4v4M16 10v4M10 16v4" />
            @break
        @case('briefcase')
            <path d="M3 7h18v13H3z" />
            <path d="M8 7V5a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2M3 12a20 20 0 0 0 18 0M12 12v2" />
            @break
        @case('category')
            <path d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4z" />
            <circle cx="17" cy="17" r="3" />
            @break
        @default
            <path d="M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h7v7h-7z" />
    @endswitch
</svg>
