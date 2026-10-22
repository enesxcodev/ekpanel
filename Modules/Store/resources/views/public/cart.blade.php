@extends('store::public.layout')
@section('store-content')
<h1 class="mb-4 text-3xl">Sepet</h1>
@if($cartError)<p role="alert">{{ $cartError }} İlgili satırı kaldırarak devam edebilirsiniz.</p>@endif
@forelse($items as $index=>$item)
<div class="my-3 rounded border p-4">
@if($quote)@php($line = collect($quote['lines'])->first(fn($line)=>$line['product']->id === $item['product_id'] && $line['variant']?->id === $item['variant_id']))
<p>{{ $line['product']->title }} {{ $line['variant']?->title }} — {{ \Modules\Store\Services\StoreMoney::format($line['amounts']['gross'],$quote['currency']) }}</p>
@else<p>Ürün #{{ $item['product_id'] }} / varyant #{{ $item['variant_id'] }}</p>@endif
<form method="POST" action="{{ route('store.cart.update',$index) }}">@csrf @method('PUT')<label>Adet <input class="rounded border p-2" type="number" name="quantity" min="0" max="100" value="{{ $item['quantity'] }}"></label><button class="rounded border p-2">Güncelle</button><button class="rounded border p-2" name="quantity" value="0">Kaldır</button></form>
</div>
@empty<p>Sepetiniz boş.</p>@endforelse
@if($quote && count($items))<p>Toplam: {{ \Modules\Store\Services\StoreMoney::format($quote['total'],$quote['currency']) }} (teslimat dahil)</p><a class="my-4 inline-block rounded bg-green-700 p-3 text-white" href="{{ route('store.checkout.form') }}">Siparişi tamamla</a>@endif
@endsection
