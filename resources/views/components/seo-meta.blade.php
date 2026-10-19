@props(['metadata'])
<title>{{ $metadata['title'] }}</title>
@if ($metadata['description'])<meta name="description" content="{{ $metadata['description'] }}">@endif
@if ($metadata['keywords'])<meta name="keywords" content="{{ $metadata['keywords'] }}">@endif
<link rel="canonical" href="{{ $metadata['canonical'] }}">
@foreach ($metadata['og'] as $property => $value)
    <meta property="og:{{ $property }}" content="{{ $value }}">
@endforeach
@foreach ($metadata['twitter'] as $name => $value)
    <meta name="twitter:{{ $name }}" content="{{ $value }}">
@endforeach
<script type="application/ld+json">{!! $metadata['jsonLd'] !!}</script>
