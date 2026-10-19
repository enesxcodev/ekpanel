@props(['name' => 'layout-grid'])

<svg {{ $attributes->class(['icon']) }} xmlns="http://www.w3.org/2000/svg" width="24" height="24"
     viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
    @switch($name)
        @case('photo')
            <rect x="3" y="3" width="18" height="18" rx="2" /><circle cx="8.5" cy="8.5" r="1.5" /><path d="m3 16 5-5 5 5 3-3 5 5" />
            @break
        @case('upload')
            <path d="M12 16V3m-4 4 4-4 4 4M4 15v5h16v-5" />
            @break
        @case('folder')
            <path d="M3 7V5h6l2 2h10v13H3z" />
            @break
        @case('folder-plus')
            <path d="M3 7V5h6l2 2h10v13H3zM12 10v7m-3-3h6" />
            @break
        @case('refresh')
            <path d="M20 7v5h-5M4 17v-5h5M6 7a7 7 0 0 1 12-1l2 3M4 15l2 3a7 7 0 0 0 12-1" />
            @break
        @case('search')
            <circle cx="10" cy="10" r="6" /><path d="m15 15 5 5" />
            @break
        @case('file')
            <path d="M6 3h8l4 4v14H6zM14 3v5h4M9 13h6m-6 4h6" />
            @break
        @case('file-text')
            <path d="M6 3h8l4 4v14H6zM14 3v5h4M9 13h6m-6 4h6" />
            @break
        @case('file-pencil')
            <path d="M6 3h8l4 4v6M14 3v5h4M9 13h4m-4 4h3" />
            <path d="m15 19 4-4 2 2-4 4-3 1z" />
            @break
        @case('inbox')
            <path d="M4 4h16v15h-5l-2 2h-2l-2-2H4zM4 13h4l2 3h4l2-3h4" />
            @break
        @case('clock')
            <circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" />
            @break
        @case('star')
            <path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1 6.2-5.5-2.9-5.5 2.9 1-6.2L3 9.6l6.2-.9z" />
            @break
        @case('trash')
            <path d="M3 6h18M9 6V3h6v3M5 6l1 15h12l1-15M10 10v7m4-7v7" />
            @break
        @case('list')
            <path d="M9 6h12M9 12h12M9 18h12M3 6h1m-1 6h1m-1 6h1" />
            @break
        @case('x')
            <path d="m6 6 12 12M6 18 18 6" />
            @break
        @case('dots')
            <circle cx="12" cy="5" r="1" /><circle cx="12" cy="12" r="1" /><circle cx="12" cy="19" r="1" />
            @break
        @case('link')
            <path d="m10 13 4-4M8 16l-1 1a4 4 0 0 1-5-5l4-4a4 4 0 0 1 6 0m0 8a4 4 0 0 0 6 0l4-4a4 4 0 0 0-6-6l-1 1" />
            @break
        @case('download')
            <path d="M12 3v12m-4-4 4 4 4-4M4 16v5h16v-5" />
            @break
        @case('arrow-up')
            <path d="M12 20V4m-5 5 5-5 5 5" />
            @break
        @case('arrow-down')
            <path d="M12 4v16m-5-5 5 5 5-5" />
            @break
        @case('check')
            <path d="m5 12 4 4 10-10" />
            @break
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
        @case('moon')
            <path d="M12 3c-5 0-9 4-9 9s4 9 9 9 9-4 9-9a7 7 0 0 1-9-9z" />
            @break
        @case('sun')
            <circle cx="12" cy="12" r="4" />
            <path d="M12 2v2m0 16v2M2 12h2m16 0h2M5 5l1.5 1.5m11 11L19 19M5 19l1.5-1.5m11-11L19 5" />
            @break
        @case('bell')
            <path d="M10 5a2 2 0 0 1 4 0 6 6 0 0 1 4 6v3l2 3H4l2-3v-3a6 6 0 0 1 4-6M9 21h6" />
            @break
        @default
            <path d="M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h7v7h-7z" />
    @endswitch
</svg>
