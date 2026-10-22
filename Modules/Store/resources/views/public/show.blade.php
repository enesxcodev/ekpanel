@extends('store::public.layout')
@section('store-content')
<article data-store-product>
<h1 class="mb-4 text-3xl">{{ $product->title }}</h1>
@if($product->image_url)<img data-product-image src="{{ $product->image_url }}" alt="{{ $product->title }}" class="mb-4 max-h-96 object-contain">@endif
<div class="mb-4 flex flex-wrap gap-3">@foreach($product->images as $image)<img src="{{ Storage::disk($image->disk)->url($image->path) }}" alt="{{ $product->title }}" class="h-24 w-24 object-contain">@endforeach</div>
<p>{{ $product->short_description }}</p><div class="page-content my-4">{!! $safeContent !!}</div>
<form method="POST" action="{{ route('store.cart.add') }}" class="space-y-3">@csrf
<input type="hidden" name="product_id" value="{{ $product->id }}"><input type="hidden" name="variant_id" data-selected-variant>
@if($product->has_variants)
@foreach($product->option_groups ?? [] as $group)<label class="block">{{ $group['name'] }}<select data-option="{{ $group['name'] }}" class="ml-2 rounded border p-2" required><option value="">Seçin</option>@foreach(array_unique(array_filter(array_map('trim',explode(',',$group['values'])))) as $value)<option value="{{ $value }}">{{ $value }}</option>@endforeach</select></label>@endforeach
@endif
<p data-price>{{ \Modules\Store\Services\StoreMoney::format($product->price_minor,$product->currency) }}</p>
<p>{{ $taxIncluded ? 'Vergi dahil.' : 'Vergi hariç; sipariş özetinde eklenir.' }}</p>
<p data-stock>{{ $product->has_variants ? 'Varyant seçin.' : ((!$product->tracks_stock || $product->allow_backorders || $product->stock_quantity>0) ? 'Satışta' : 'Stokta yok') }}</p>
<label>Adet <input class="rounded border p-2" type="number" name="quantity" value="1" min="1" max="100" required></label>
<button data-add-cart class="rounded bg-green-700 px-4 py-2 text-white" @disabled($product->has_variants || ($product->tracks_stock && !$product->allow_backorders && $product->stock_quantity<=0))>Sepete ekle</button>
</form>
<p class="mt-4">{{ $product->delivery_description }}</p><p>Hazırlık süresi: {{ $product->preparation_days }} gün</p>
<script type="application/json" data-public-variants>@json($variantData)</script>
</article>
@endsection
@push('scripts')
<script>
document.querySelectorAll('[data-store-product]').forEach(root => {
    const variants = JSON.parse(root.querySelector('[data-public-variants]').textContent);
    root.querySelectorAll('[data-option]').forEach(select => select.addEventListener('change', () => {
        const selected = Object.fromEntries([...root.querySelectorAll('[data-option]')].map(el=>[el.dataset.option,el.value]));
        const variant = variants.find(item => Object.entries(item.options).every(([key,value])=>selected[key]===value));
        root.querySelector('[data-selected-variant]').value = variant?.id || '';
        root.querySelector('[data-add-cart]').disabled = !variant?.available;
        root.querySelector('[data-price]').textContent = variant?.price || 'Seçenekleri tamamlayın';
        root.querySelector('[data-stock]').textContent = variant ? (variant.available ? 'Satışta' : 'Stokta yok') : 'Bu kombinasyon satışta değil.';
        if (variant?.image && root.querySelector('[data-product-image]')) root.querySelector('[data-product-image]').src = variant.image;
    }));
});
</script>
@endpush
