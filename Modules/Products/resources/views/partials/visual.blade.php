@if($product->image_url)
    <img src="{{ $product->image_url }}" alt="{{ $product->title }}" class="avatar" loading="lazy">
@else
    <span class="text-secondary">—</span>
@endif
