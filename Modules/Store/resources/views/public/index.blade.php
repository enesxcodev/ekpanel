@extends('store::public.layout')
@section('store-content')
<h1 class="mb-6 text-3xl">Mağaza</h1>
<p class="mb-4">{{ $taxIncluded ? 'Fiyatlara vergi dahildir.' : 'Fiyatlara vergi dahil değildir; sipariş özetinde eklenir.' }}</p>
<form method="GET" class="mb-6 flex flex-wrap gap-3"><input name="q" value="{{ request('q') }}" placeholder="Ürün ara" class="rounded border p-2"><select name="category_id" class="rounded border p-2"><option value="">Tüm kategoriler</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string)request('category_id') === (string)$category->id)>{{ $category->name }}</option>@endforeach</select><button class="rounded border p-2">Filtrele</button></form>
<div class="grid gap-6 md:grid-cols-3">@forelse($products as $product)<article class="rounded border p-4">
@if($product->image_url)<img src="{{ $product->image_url }}" alt="{{ $product->title }}" class="mb-3 h-48 w-full object-contain">@endif
<h2 class="text-xl"><a href="{{ route('store.show',$product->slug) }}">{{ $product->title }}</a></h2>
<p>{{ $product->short_description }}</p><p>{{ $product->has_variants ? 'Temel fiyat: ' : '' }}{{ \Modules\Store\Services\StoreMoney::format($product->price_minor,$product->currency) }}</p>
</article>@empty<p>Ürün bulunamadı.</p>@endforelse</div><div class="mt-6">{{ $products->links() }}</div>
@endsection
