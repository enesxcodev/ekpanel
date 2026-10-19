<section id="anasayfa" aria-labelledby="hero-title" class="relative isolate overflow-hidden border-b border-white/10">
    <div class="absolute inset-y-0 right-0 -z-10 hidden w-[61%] lg:block">
        <img src="{{ $garageImage }}" alt="{{ $companyName }} atölyesinin dış görünümü" class="h-full w-full object-cover object-center">
        <div class="absolute inset-0 bg-gradient-to-r from-[#090d0f] via-[#090d0f]/65 to-[#090d0f]/10"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-[#090d0f]/80 via-transparent to-[#090d0f]/10"></div>
    </div>
    <div class="mx-auto max-w-7xl px-5 pt-12 lg:px-8 lg:pt-16">
        <div class="grid min-h-[540px] items-center lg:grid-cols-[0.9fr_1.1fr]">
            <div class="relative z-10 max-w-xl pb-10 lg:pb-28">
                <p class="mb-5 flex items-center gap-3 text-[11px] font-bold tracking-wide text-[#ff515c]"><span class="h-px w-7 bg-[#f04450]"></span>{{ $heroEyebrow }}</p>
                <h1 id="hero-title" class="text-4xl leading-[1.08] font-extrabold tracking-tight sm:text-5xl lg:text-[58px]">{{ $heroTitle }}<br><span class="text-[#ed414d]">{{ $heroHighlight }}</span></h1>
                <p class="mt-5 max-w-md text-lg leading-relaxed text-slate-300">{{ $heroSubtitle }}</p>
                <div class="mt-7 flex flex-wrap gap-3">
                    @if ($whatsAppUrl)
                    <a href="{{ $whatsAppUrl }}" class="inline-flex items-center gap-3 rounded-xl bg-gradient-to-r from-[#e43b47] to-[#ba2632] px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-red-950/30 transition hover:brightness-110">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20.5 11.5a8.5 8.5 0 0 1-12.6 7.4L3 20l1.2-4.6A8.5 8.5 0 1 1 20.5 11.5Z"/><path d="M8 8.3c.4 2.8 2.6 5 5.5 5.7l1.4-1.4 2 .8"/></svg>
                        {{ $heroButtonText }} <span aria-hidden="true">→</span>
                    </a>
                    @endif
                    <a href="{{ $directionsUrl }}" class="inline-flex items-center gap-2 rounded-xl border border-[#e43b47]/70 bg-black/20 px-5 py-3.5 text-sm font-bold text-white transition hover:bg-[#e43b47]/10">
                        <svg class="size-4 text-[#ff515c]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg>
                        {{ $heroDirectionsText }}
                    </a>
                </div>
            </div>

            <div class="relative -mx-5 h-64 overflow-hidden sm:h-80 lg:hidden">
                <img src="{{ $garageImage }}" alt="{{ $companyName }} atölyesi" class="h-full w-full object-cover object-center">
                <div class="absolute inset-0 bg-gradient-to-t from-[#090d0f] via-[#090d0f]/15 to-transparent"></div>
            </div>
            <aside class="relative z-10 mx-auto w-full max-w-xl pb-10 lg:mx-0 lg:pb-0" aria-labelledby="hero-contact-title">
                <div class="hero-contact-panel rounded-2xl border border-white/10 bg-[#101619]/95 p-5 shadow-2xl shadow-black/40 backdrop-blur sm:p-7">
                    <p class="mb-2 text-xs font-bold uppercase tracking-[0.18em] text-[#ff515c]">Hızlı İletişim</p>
                    <h2 id="hero-contact-title" class="mb-5 text-2xl font-bold tracking-tight text-white">Size nasıl yardımcı olabiliriz?</h2>
                    <x-site-form key="contact" class="hero-contact-form" />
                </div>
            </aside>
        </div>

        @include('frontend.themes.general.sections.highlights', ['highlights' => $highlights])
    </div>
</section>
