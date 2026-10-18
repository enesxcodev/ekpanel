<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#090d0f">
    <title>{{ $siteTitle }}</title>
    <meta name="description" content="{{ $siteDescription }}">
    @if ($siteKeywords)<meta name="keywords" content="{{ $siteKeywords }}">@endif
    @vite('resources/css/app.css')
</head>
<body class="bg-[#090d0f] font-sans text-[#f3f5f6] antialiased selection:bg-[#e93643] selection:text-white">
    @php
        $garageImage = $banner?->image_url ?? asset('images/themes/general/route66-sanayi.jpg');
        $aboutImage = $aboutCopy['image_url'] ?? $garageImage;
        $heroTitle = $banner?->title ?: 'Aracınız için';
        $heroHighlight = $banner?->extra_fields['highlighted_title'] ?? 'güvenilir servis';
        $heroSubtitle = $banner?->subtitle ?: 'Bakım, arıza tespiti ve onarım hizmetleri tek noktada.';
        $heroButtonText = $banner?->extra_fields['button_text'] ?? 'WhatsApp ile İletişim';
        $heroEyebrow = $banner?->extra_fields['eyebrow'] ?? 'GÜVENİLİR SERVİS, UZMAN ÇÖZÜM';
        $heroDirectionsText = $banner?->extra_fields['directions_button_text'] ?? 'Yol Tarifi Al';
    @endphp

    <header class="sticky top-0 z-40 border-b border-white/10 bg-[#090d0f]/95 backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-5 px-5 py-3.5 lg:px-8">
            <a href="#anasayfa" class="shrink-0 leading-none" aria-label="Cihan Caner Auto Service ana sayfa">
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

    <main id="main-content">
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
                    <div class="hidden lg:block"></div>
                </div>

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
            </div>
        </section>

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
                            <article class="rounded-xl border border-white/[0.08] bg-gradient-to-br from-white/[0.045] to-white/[0.015] p-5 transition hover:border-[#e43b47]/40">
                                <span class="mb-3 grid size-14 place-items-center rounded-full bg-[#e43b47]/10 text-[#f04450]">@include('frontend.themes.general.partials.service-icon', ['name' => $service->icon])</span>
                                <h3 class="font-bold">{{ $service->title }}</h3>
                                @if ($service->short_description)
                                    <p class="mt-1 text-sm leading-relaxed text-slate-400">{{ $service->short_description }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif

                @if ($otherServices->isNotEmpty())
                <div class="mt-4 flex items-start gap-4 rounded-xl border border-white/[0.08] bg-white/[0.025] p-5 sm:items-center">
                    <span class="grid size-12 shrink-0 place-items-center rounded-full bg-[#e43b47]/10 text-[#f04450]"><svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="m19.4 15 1 1.7-2 3.4-2 .1-.9-1.6h-3l-.9 1.6-2-.1-2-3.4 1-1.7-1.5-2.6-1.9-.1v-4l1.9-.1 1.5-2.6-1-1.7 2-3.4 2-.1.9 1.6h3l.9-1.6 2 .1 2 3.4-1 1.7 1.5 2.6 1.9.1v4l-1.9.1z"/></svg></span>
                    <div><h3 class="font-bold">{{ $servicesCopy['other_title'] }}</h3><p class="mt-1 flex flex-wrap gap-x-2 text-sm leading-relaxed text-slate-400">@foreach ($otherServices as $service)<span>{{ $service->title }}</span>@if (! $loop->last)<span class="text-[#f04450]">•</span>@endif @endforeach</p></div>
                </div>
                @endif
            </div>
        </section>

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

                <div id="iletisim" class="mt-6 grid gap-3 md:grid-cols-3">
                    <article class="relative overflow-hidden rounded-xl border border-white/[0.08] bg-gradient-to-br from-white/[0.045] to-white/[0.015] p-5">
                        <svg class="mb-4 size-7 text-[#f04450]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                        <h3 class="font-bold">Çalışma Saatleri</h3><p class="mt-1 whitespace-pre-line text-sm text-slate-400">{{ $workingHours ?: 'Çalışma saatleri için lütfen iletişime geçin.' }}</p>
                    </article>
                    <article class="relative overflow-hidden rounded-xl border border-white/[0.08] bg-gradient-to-br from-white/[0.045] to-white/[0.015] p-5">
                        <svg class="mb-4 size-7 text-[#f04450]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg>
                        <h3 class="font-bold">Konumumuz</h3><p class="mt-1 whitespace-pre-line text-sm text-slate-400">{{ $address ?: 'Haritada görüntüleyin ve yol tarifi alın.' }}</p>
                        <a href="{{ $directionsUrl }}" target="_blank" rel="noopener noreferrer" class="mt-4 inline-flex items-center gap-2 rounded-lg border border-[#e43b47]/60 px-3.5 py-2 text-xs font-bold transition hover:bg-[#e43b47]/10"><span class="text-[#ff515c]">⌖</span> {{ $heroDirectionsText }} <span class="text-[#ff515c]">→</span></a>
                    </article>
                    <article class="relative overflow-hidden rounded-xl border border-white/[0.08] bg-gradient-to-br from-white/[0.045] to-white/[0.015] p-5">
                        <svg class="mb-4 size-7 text-[#f04450]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M21 11.5a8.4 8.4 0 0 1-9 8.4 9 9 0 0 1-4-.9L3 21l1.7-4.7A8.4 8.4 0 1 1 21 11.5z"/></svg>
                        <h3 class="font-bold">İletişim</h3><p class="mt-1 text-sm text-slate-400">Randevu ve bilgi için<br>bize ulaşın.</p>
                        @if ($whatsAppUrl)
                        <a href="{{ $whatsAppUrl }}" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-[#e43b47] to-[#ba2632] px-3.5 py-2.5 text-xs font-bold text-white"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20.5 11.5a8.5 8.5 0 0 1-12.6 7.4L3 20l1.2-4.6A8.5 8.5 0 1 1 20.5 11.5Z"/><path d="M8 8.3c.4 2.8 2.6 5 5.5 5.7l1.4-1.4 2 .8"/></svg> WhatsApp ile İletişim <span>→</span></a>
                        @endif
                    </article>
                </div>
            </div>
        </section>

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
    </main>

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

    @if ($whatsAppUrl)
    <a href="{{ $whatsAppUrl }}" class="fixed right-5 bottom-5 z-40 grid size-14 place-items-center rounded-full bg-[#20c773] text-white shadow-xl shadow-black/40 transition hover:scale-105" aria-label="WhatsApp ile iletişim">
        <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20.5 11.5a8.5 8.5 0 0 1-12.6 7.4L3 20l1.2-4.6A8.5 8.5 0 1 1 20.5 11.5Z"/><path d="M8 8.3c.4 2.8 2.6 5 5.5 5.7l1.4-1.4 2 .8"/></svg>
    </a>
    @endif
</body>
</html>
