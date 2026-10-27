@inject('settings', 'App\Services\Settings\SettingsService')
<!doctype html>
<html lang="tr" class="scroll-smooth">
<head>
    @include('frontend.themes.main.partials.head', ['seoMetadata' => $seoMetadata])
    @vite(['resources/css/frontend/default/app.css', 'resources/js/frontend/default/app.js'])
    @fonts
    @stack('styles')
    {!! $settings->get('scripts.head', '') !!}
</head>
<body class="default-theme bg-white font-sans text-slate-900 antialiased">
    <a href="#main-content" class="skip-link">İçeriğe geç</a>
    @include('frontend.themes.default.partials.header', ['site' => $site, 'navigation' => $navigation, 'cartUrl' => $cartUrl])
    <main id="main-content">
        @yield('content')
    </main>
    @include('frontend.themes.default.partials.footer', ['site' => $site, 'navigation' => $navigation])
    @stack('scripts')
    {!! $settings->get('scripts.body', '') !!}
</body>
</html>
