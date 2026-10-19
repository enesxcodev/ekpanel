@inject('settings', 'App\Services\Settings\SettingsService')
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('frontend.themes.main.partials.head', ['seoMetadata' => $seoMetadata])
    @stack('styles')
    {!! $settings->get('scripts.head', '') !!}
</head>
<body>
    @include('frontend.themes.main.partials.header')
    <main id="main-content">
        @yield('content')
    </main>
    @include('frontend.themes.main.partials.footer')
    @stack('scripts')
    {!! $settings->get('scripts.body', '') !!}
</body>
</html>
