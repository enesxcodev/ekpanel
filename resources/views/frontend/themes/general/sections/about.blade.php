<section id="hakkimizda" aria-labelledby="about-title" class="border-b border-white/10">
    <div class="mx-auto max-w-7xl px-5 py-12 lg:px-8 lg:py-16">
        <div class="grid items-center gap-8 lg:grid-cols-[0.72fr_1.28fr]">
            <div class="max-w-lg">
                <p class="mb-3 flex items-center gap-3 text-[11px] font-bold tracking-wide text-[#ff515c]"><span class="h-px w-7 bg-[#f04450]"></span>{{ $aboutCopy['eyebrow'] }}</p>
                <h2 id="about-title" class="text-3xl leading-tight font-extrabold tracking-tight sm:text-4xl">{{ $aboutCopy['title'] }}</h2>
                <p class="mt-4 text-lg leading-relaxed text-slate-300">{{ $aboutCopy['short_description'] }}</p>
                <p class="mt-3 text-sm leading-relaxed text-slate-400">{{ $aboutCopy['description'] }}</p>
                @if ($whatsAppUrl)
                <a href="{{ $whatsAppUrl }}" class="mt-6 inline-flex items-center gap-3 rounded-xl bg-gradient-to-r from-[#e43b47] to-[#ba2632] px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-red-950/30 transition hover:brightness-110">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20.5 11.5a8.5 8.5 0 0 1-12.6 7.4L3 20l1.2-4.6A8.5 8.5 0 1 1 20.5 11.5Z"/><path d="M8 8.3c.4 2.8 2.6 5 5.5 5.7l1.4-1.4 2 .8"/></svg>
                    {{ $aboutCopy['button_text'] }} <span aria-hidden="true">→</span>
                </a>
                @endif
            </div>
            <div class="overflow-hidden rounded-2xl border border-white/10 shadow-2xl shadow-black/40">
                <img src="{{ $aboutImage }}" alt="{{ $companyName }} atölyesi" class="aspect-[1.55] w-full object-cover object-center">
            </div>
        </div>

        @include('frontend.themes.general.sections.contact', $contact)
    </div>
</section>
