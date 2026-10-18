@props(['url' => null, 'alt' => '', 'width' => 80, 'height' => 48])
@if ($url)
    <img {{ $attributes->class(['rounded object-fit-cover']) }} src="{{ $url }}" alt="{{ $alt }}" width="{{ $width }}" height="{{ $height }}" loading="lazy">
@else
    <span class="text-secondary" aria-label="Görsel yok">—</span>
@endif