<section class="px-5 py-8 lg:px-8 lg:py-10">
    <div class="mx-auto grid max-w-7xl gap-6 rounded-2xl border border-[#ee3e49]/25 bg-[radial-gradient(ellipse_at_left,_rgba(194,35,47,0.36),_transparent_64%),linear-gradient(110deg,#230d11,#12090b)] p-6 sm:p-9 lg:grid-cols-2 lg:items-center">
        <div>
            <p class="mb-3 flex items-center gap-3 text-[11px] font-bold tracking-[0.16em] text-[#ff5964]"><span class="text-xl" aria-hidden="true">⚒</span> {{ $ctaCopy['eyebrow'] }}</p>
            <h2 class="text-3xl leading-tight font-extrabold tracking-tight sm:text-4xl">{{ $ctaCopy['title'] }}</h2>
        </div>
        <div class="border-white/20 lg:border-l lg:pl-8">
            <p class="max-w-md text-sm leading-relaxed text-slate-300">{{ $ctaCopy['description'] }}</p>
                @if ($whatsAppUrl)
                <a href="{{ $whatsAppUrl }}" class="mt-4 inline-flex items-center gap-3 rounded-xl border border-white/60 px-5 py-3 text-sm font-bold text-white transition hover:bg-white/10">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20.5 11.5a8.5 8.5 0 0 1-12.6 7.4L3 20l1.2-4.6A8.5 8.5 0 1 1 20.5 11.5Z"/><path d="M8 8.3c.4 2.8 2.6 5 5.5 5.7l1.4-1.4 2 .8"/></svg>
                {{ $ctaCopy['button_text'] }} <span aria-hidden="true">→</span>
            </a>
                @endif
        </div>
    </div>
</section>
