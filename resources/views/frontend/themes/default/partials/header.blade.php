<header class="relative z-30 border-b border-slate-100 bg-white" id="anasayfa">
    <div class="site-container flex h-20 items-center justify-between gap-6">
        @include('frontend.themes.default.partials.logo', ['site' => $site, 'logo' => $site['logo']])
        <nav class="hidden items-center gap-6 lg:flex" aria-label="Ana menü">
            @include('frontend.themes.default.partials.navigation', ['items' => $navigation])
        </nav>
        <div class="flex items-center gap-2 sm:gap-4">
            @if($contactAvailable)<a href="#iletisim" class="icon-button hidden sm:inline-flex" aria-label="İletişim ve destek"><x-frontend.default.icon name="user" /></a>@endif
            @if($cartUrl)
                <a href="{{ $cartUrl }}" class="icon-button" aria-label="Sepetim"><x-frontend.default.icon name="shopping-cart" /></a>
            @endif
            @if($copy['header_action_label'] && $copy['header_action_url'])<a href="{{ $copy['header_action_url'] }}" class="button-primary !min-h-10 !px-5 !text-xs">{{ $copy['header_action_label'] }}</a>@endif
            <button type="button" class="icon-button lg:hidden" data-menu-toggle aria-expanded="false" aria-controls="mobile-navigation" aria-label="Menüyü aç"><x-frontend.default.icon name="menu" /></button>
        </div>
    </div>
    <nav id="mobile-navigation" class="mobile-navigation site-container border-t border-slate-100 py-4 lg:!hidden" aria-label="Mobil menü" hidden>
        @include('frontend.themes.default.partials.navigation', ['items' => $navigation])
    </nav>
</header>
