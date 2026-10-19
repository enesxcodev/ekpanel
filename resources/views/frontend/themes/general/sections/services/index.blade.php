<section id="hizmetler" aria-labelledby="services-title" class="border-b border-white/10">
    <div class="mx-auto max-w-7xl px-5 py-12 lg:px-8 lg:py-16">
        <div class="mb-7 flex flex-col gap-5 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="mb-3 flex items-center gap-3 text-[11px] font-bold tracking-wide text-[#ff515c]"><span class="h-px w-7 bg-[#f04450]"></span>{{ $servicesCopy['eyebrow'] }}</p>
                <h2 id="services-title" class="max-w-xl text-3xl leading-tight font-extrabold tracking-tight sm:text-4xl">{{ $servicesCopy['title'] }}</h2>
            </div>
            <div class="flex items-center justify-between gap-4 md:max-w-md">
                <p class="text-sm leading-relaxed text-slate-400">{{ $servicesCopy['description'] }}</p>
                <a href="{{ $servicesCopy['button_url'] }}" class="hidden shrink-0 items-center gap-2 rounded-full border border-[#e43b47]/60 px-4 py-2.5 text-xs font-semibold transition hover:bg-[#e43b47]/10 sm:inline-flex">{{ $servicesCopy['button_text'] }} <span class="text-[#ff515c]">→</span></a>
            </div>
        </div>

        @if ($featuredServices->isNotEmpty())
            <div id="service-cards" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($featuredServices as $service)
                    @include('frontend.themes.general.sections.services.card', ['service' => $service])
                @endforeach
            </div>
        @endif

        @include('frontend.themes.general.sections.services.other', ['otherServices' => $otherServices, 'servicesCopy' => $servicesCopy])
    </div>
</section>
