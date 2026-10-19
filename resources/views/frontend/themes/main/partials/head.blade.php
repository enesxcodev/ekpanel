@inject('settings', 'App\Services\Settings\SettingsService')
@php
    $faviconUrl = $settings->mediaUrl('site.favicon');
@endphp
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<x-seo-meta :metadata="$seoMetadata" />
@if ($faviconUrl)
    <link rel="icon" href="{{ $faviconUrl }}">
@endif
