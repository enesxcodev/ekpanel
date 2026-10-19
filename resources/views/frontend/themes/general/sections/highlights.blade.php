<div class="grid gap-5 border-t border-white/10 py-6 sm:grid-cols-3 lg:-mt-24 lg:max-w-[760px] lg:border-t-0 lg:pb-8">
    @foreach ($highlights as $highlight)
        <article class="flex gap-3 border-white/10 sm:border-r sm:pr-4">
            <span class="mt-0.5 grid size-7 shrink-0 place-items-center text-[#f04450]">
                @if ($highlight['icon'] === 'search')
                    <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="10.5" cy="10.5" r="7"/><path d="m16 16 5 5"/></svg>
                @elseif ($highlight['icon'] === 'wrench')
                    <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M14.5 6.5a5 5 0 0 0-6-6L11 3l-3 3-2.5-2.5a5 5 0 0 0 6 6l8 8a2.1 2.1 0 0 0 3-3z" transform="translate(-1 2)"/></svg>
                @else
                    <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M21 11.5a8.4 8.4 0 0 1-9 8.4 9 9 0 0 1-4-.9L3 21l1.7-4.7A8.4 8.4 0 1 1 21 11.5z"/></svg>
                @endif
            </span>
            <div><h2 class="text-sm font-bold">{{ $highlight['title'] }}</h2><p class="mt-1 text-xs leading-relaxed text-slate-400">{{ $highlight['description'] }}</p></div>
        </article>
    @endforeach
</div>
