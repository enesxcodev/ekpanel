@extends('store::public.layout')
@section('store-content')
<h1 class="mb-4 text-3xl">Sipariş {{ $order->number }}</h1>
<p>Sipariş alındı. Ödeme: {{ $order->payment_status }} / Sipariş: {{ $order->status }}</p>
@foreach($order->lines as $line)<p>{{ $line->product_title }} {{ $line->variant_title }} × {{ $line->quantity }}: {{ \Modules\Store\Services\StoreMoney::format($line->line_total_minor,$order->currency) }}</p>@endforeach
<p>Toplam: {{ \Modules\Store\Services\StoreMoney::format($order->total_minor,$order->currency) }}</p>
@if($order->tracking_number)<p>Takip numarası: {{ $order->tracking_number }}</p>@endif
@if($order->status==='cancelled' && $order->payment_status==='paid')<p>İptal edildi. Ödeme iadesi için mağazayla iletişime geçin.</p>@endif
@endsection
