<footer class="bg-slate-900 text-slate-300">
    <div class="site-container grid gap-10 py-12 sm:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1fr_1.3fr]">
        <div>
            <div class="text-white">@include('frontend.themes.default.partials.logo', ['site' => $site, 'logo' => $site['footer_logo']])</div>
            <p class="mt-4 max-w-64 text-sm leading-6 text-slate-400">{{ $site['description'] }}</p>
            @if($site['socials'])
                <div class="mt-5 flex flex-wrap gap-3">
                    @foreach($site['socials'] as $network => $url)
                        <a href="{{ $url }}" class="rounded-md bg-white/5 px-2 py-1 text-xs transition hover:bg-white/15" target="_blank" rel="noopener noreferrer">{{ ucfirst($network) }}</a>
                    @endforeach
                </div>
            @endif
        </div>
        @if($footerCorporate)
        <div>
            <h2 class="mb-4 text-sm font-bold text-white">{{ $copy['footer_corporate_title'] }}</h2>
            <div class="footer-navigation flex flex-col items-start gap-2.5 text-sm">
                @include('frontend.themes.default.partials.navigation', ['items' => $footerCorporate])
            </div>
        </div>
        @endif
        @if($footerExplore)
        <div>
            <h2 class="mb-4 text-sm font-bold text-white">{{ $copy['footer_explore_title'] }}</h2>
            <div class="footer-navigation flex flex-col items-start gap-2.5 text-sm">
                @include('frontend.themes.default.partials.navigation', ['items' => $footerExplore])
            </div>
        </div>
        @endif
        <div>
            <h2 class="mb-4 text-sm font-bold text-white">{{ $copy['footer_contact_title'] }}</h2>
            <div class="flex flex-col gap-3 text-sm">
                @if($site['address'])<p class="flex items-start gap-2.5"><x-frontend.default.icon name="map-pin" class="mt-0.5 size-4 shrink-0" /><span>{{ $site['address'] }}</span></p>@endif
                @if($site['phone'])<a href="{{ $site['phone_url'] }}" class="flex items-center gap-2.5"><x-frontend.default.icon name="phone" class="size-4" />{{ $site['phone'] }}</a>@endif
                @if($site['email'])<a href="mailto:{{ $site['email'] }}" class="flex items-center gap-2.5 break-all"><x-frontend.default.icon name="mail" class="size-4 shrink-0" />{{ $site['email'] }}</a>@endif
            </div>
        </div>
    </div>
    <div class="border-t border-white/10">
        <div class="site-container flex flex-wrap items-center justify-between gap-3 py-5 text-xs text-slate-400">
            <p>© {{ date('Y') }} {{ $site['name'] }} {{ $copy['footer_copyright'] }}</p>
            @if($copy['footer_back_top_label'])<a href="#anasayfa" class="inline-flex items-center gap-2">{{ $copy['footer_back_top_label'] }} <x-frontend.default.icon name="arrow-up" class="size-3.5" /></a>@endif
        </div>
    </div>
</footer>
