<header class="sticky top-0 z-40 border-b border-white/10 bg-[#090d0f]/95 backdrop-blur">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-5 px-5 py-3.5 lg:px-8">
        <a href="#anasayfa" class="shrink-0 leading-none" aria-label="{{ $companyName }} ana sayfa">
            @if ($brandLogoUrl)
                <img src="{{ $brandLogoUrl }}" alt="{{ $companyName }}" class="max-h-11 max-w-52 object-contain">
            @else
                <span class="block text-lg font-black tracking-tight sm:text-xl">{{ $companyName }}</span>
                <span class="mt-1 block text-[11px] font-extrabold tracking-[0.16em] text-[#f04450]">✦ AUTO SERVICE ✦</span>
            @endif
        </a>

        @if ($navigationItems !== [])
            <nav aria-label="Ana navigasyon" class="hidden items-center gap-8 text-sm text-slate-300 md:flex">
                @foreach ($navigationItems as $item)
                    @if ($item['url'] !== null)
                        <a class="transition hover:text-[#ff515c]" href="{{ $item['url'] }}" @if ($item['new_tab']) target="_blank" rel="noopener noreferrer" @endif>{{ $item['label'] }}</a>
                    @else
                        <span class="text-slate-300">{{ $item['label'] }}</span>
                    @endif
                @endforeach
            </nav>
        @endif

        @if ($whatsAppUrl)
            <a href="{{ $whatsAppUrl }}" class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-gradient-to-r from-[#e43b47] to-[#ba2632] px-3.5 py-3 text-xs font-bold text-white shadow-lg shadow-red-950/30 transition hover:brightness-110 sm:px-5 sm:text-sm">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20.5 11.5a8.5 8.5 0 0 1-12.6 7.4L3 20l1.2-4.6A8.5 8.5 0 1 1 20.5 11.5Z"/><path d="M8 8.3c.4 2.8 2.6 5 5.5 5.7l1.4-1.4 2 .8"/></svg>
                WhatsApp ile İletişim
            </a>
        @endif
    </div>
</header>
