@extends('store::public.layout')
@section('store-content')
<h1 class="mb-6 text-3xl">{{ __('store::store.store') }}</h1>
<p class="mb-4">{{ $taxIncluded ? __('store::store.tax_included_prices') : __('store::store.tax_excluded_prices') }}</p>
<form method="GET" action="{{ route('store.localized.index',['lang'=>$languageCode]) }}" class="mb-6 flex flex-wrap gap-3"><input name="q" value="{{ request('q') }}" placeholder="{{ __('store::store.search_products') }}" class="rounded border p-2"><select name="category_id" class="rounded border p-2"><option value="">{{ __('store::store.all_categories') }}</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string)request('category_id') === (string)$category->id)>{{ $category->name }}</option>@endforeach</select><button class="rounded border p-2">{{ __('store::store.filter') }}</button></form>
<div class="grid gap-6 md:grid-cols-3">@forelse($products as $product)<article class="rounded border p-4">
@if($product->image_url)<img src="{{ $product->image_url }}" alt="{{ $product->title }}" class="mb-3 h-48 w-full object-contain">@endif
<h2 class="text-xl"><a href="{{ route('store.localized.show',['lang'=>$languageCode,'slug'=>$product->slug]) }}">{{ $product->title }}</a></h2>
<p>{{ $product->short_description }}</p><p>{{ $product->has_variants ? __('store::store.base_price') : '' }}{{ \Modules\Store\Services\StoreMoney::format($product->price_minor,$product->currency) }}</p>
</article>@empty<p>{{ __('store::store.no_products') }}</p>@endforelse</div><div class="mt-6">{{ $products->appends(['lang'=>$languageCode])->links() }}</div>
@endsection
