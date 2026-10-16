@if ($items !== [])
    <nav aria-label="Ana menü" class="mb-6 text-sm">
        @include('menus::public.items', ['items' => $items])
    </nav>
@endif
