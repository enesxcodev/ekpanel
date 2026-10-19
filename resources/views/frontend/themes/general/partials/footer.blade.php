<footer class="border-t border-white/10 bg-[#080b0d]">
    <div class="mx-auto flex max-w-7xl flex-col gap-5 px-5 pt-7 pb-24 md:flex-row md:items-center md:justify-between md:py-7 lg:px-8">
        <a href="#anasayfa" class="leading-none" aria-label="{{ $companyName }} ana sayfa">
            @if ($footerLogoUrl)
                <img src="{{ $footerLogoUrl }}" alt="{{ $companyName }}" class="max-h-11 max-w-52 object-contain">
            @else
                <span class="block text-lg font-black tracking-tight">{{ $companyName }}</span><span class="mt-1 block text-[10px] font-extrabold tracking-[0.16em] text-[#f04450]">✦ AUTO SERVICE ✦</span>
            @endif
        </a>
        @if ($navigationItems !== [])
            <nav aria-label="Alt menü" class="flex flex-wrap gap-x-5 gap-y-2 text-xs text-slate-400">
                @foreach ($navigationItems as $item)
                    @if ($item['url'] !== null)
                        <a class="hover:text-white" href="{{ $item['url'] }}" @if ($item['new_tab']) target="_blank" rel="noopener noreferrer" @endif>{{ $item['label'] }}</a>
                    @else
                        <span>{{ $item['label'] }}</span>
                    @endif
                @endforeach
            </nav>
        @endif
        <div class="flex items-center gap-4"><p class="text-xs leading-relaxed text-slate-500">© {{ date('Y') }} {{ $companyName }}.<br>Tüm hakları saklıdır.</p>@if ($instagramUrl)<a href="{{ $instagramUrl }}" target="_blank" rel="noopener noreferrer" aria-label="Instagram" class="text-slate-400 transition hover:text-white"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="18" cy="6" r=".8" fill="currentColor"/></svg></a>@endif</div>
    </div>
</footer>
