@extends('store::public.layout')
@section('store-content')
<h1 class="mb-4 text-3xl">{{ __('store::store.cart_title') }}</h1>
@if($cartError)<p role="alert">{{ $cartError }} {{ __('store::store.cart_error_help') }}</p>@endif
@forelse($items as $index=>$item)
<div class="my-3 rounded border p-4">
@if($quote)@php($line = collect($quote['lines'])->first(fn($line)=>$line['product']->id === $item['product_id'] && $line['variant']?->id === $item['variant_id']))
<p>{{ $line['product']->title }} {{ $line['variant']?->title }} — {{ \Modules\Store\Services\StoreMoney::format($line['amounts']['gross'],$quote['currency']) }}</p>
@else<p>{{ __('store::store.product_number',['number'=>$item['product_id']]) }} / {{ __('store::store.variant_number',['number'=>$item['variant_id']]) }}</p>@endif
<form method="POST" action="{{ route('store.cart.update',['index'=>$index,'lang'=>request()->route('lang') ?? request('lang','tr')]) }}">@csrf @method('PUT')<label>{{ __('store::store.quantity') }} <input class="rounded border p-2" type="number" name="quantity" min="0" max="100" value="{{ $item['quantity'] }}"></label><button class="rounded border p-2">{{ __('store::store.update') }}</button><button class="rounded border p-2" name="quantity" value="0">{{ __('store::store.remove') }}</button></form>
</div>
@empty<p>{{ __('store::store.empty_cart') }}</p>@endforelse
@if($quote && count($items))<p>{{ __('store::store.total') }}: {{ \Modules\Store\Services\StoreMoney::format($quote['total'],$quote['currency']) }} ({{ __('store::store.shipping_included') }})</p><a class="my-4 inline-block rounded bg-green-700 p-3 text-white" href="{{ route('store.checkout.form',['lang'=>request()->route('lang') ?? request('lang','tr')]) }}">{{ __('store::store.complete_order') }}</a>@endif
@endsection
