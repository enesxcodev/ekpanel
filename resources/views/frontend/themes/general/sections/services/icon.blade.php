@switch($name)
    @case('engine')
        <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M7 7h10l2 3h3v7h-3l-2 2H8l-2-2H4v-7h3z"/><path d="M9 7V5h5v2M10 11v4m4-4v4"/></svg>
        @break
    @case('computer')
        <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="5" y="4" width="14" height="13" rx="1"/><path d="M3 20h18l-2-3H5z"/></svg>
        @break
    @case('bolt')
        <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m13 2-9 12h7l-1 8 10-13h-7z"/></svg>
        @break
    @case('oil')
        <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 9h11l3 3h4v5h-8l-3-3H3zM6 9l2-4h5l2 4m-7 8v3h5v-3"/></svg>
        @break
    @case('snowflake')
        <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 2v20M4 7l16 10M4 17 20 7M8 3l4 3 4-3M8 21l4-3 4 3"/></svg>
        @break
    @case('tire')
        <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1"/></svg>
        @break
    @case('exhaust')
        <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 14h5l4-4h5l3 3-3 4h-6l-2 2H5a3 3 0 0 1-2-5zM15 10l2-2m2 2 2-2"/></svg>
        @break
    @case('battery')
        <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V4h3v3m2 0V4h3v3m-9 7h6m-3-3v6"/></svg>
        @break
    @default
        <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
@endswitch
