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
    @stack('styles')
    {!! $headCode !!}
</head>
<body class="bg-[#090d0f] font-sans text-[#f3f5f6] antialiased selection:bg-[#e93643] selection:text-white">

@include('frontend.themes.general.partials.header', $header)
<main id="main-content">
    @yield('content')
</main>
@include('frontend.themes.general.partials.footer', $footer)
@include('frontend.themes.general.partials.floating-whatsapp', ['whatsAppUrl' => $whatsAppUrl])
@stack('scripts')
{!! $bodyCode !!}
</body>
</html>
