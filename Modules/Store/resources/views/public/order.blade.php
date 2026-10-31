@extends('store::public.layout')
@section('store-content')
<h1 class="mb-4 text-3xl">{{ __('store::store.order_title',['number'=>$order->number]) }}</h1>
<p>{{ __('store::store.order_received') }} {{ __('store::store.payment') }}: {{ __('store::store.payment_statuses.'.$order->payment_status) }} / {{ __('store::store.order') }}: {{ __('store::store.statuses.'.$order->status) }}</p>
@foreach($order->lines as $line)<p>{{ $line->product_title }} {{ $line->variant_title }} × {{ $line->quantity }}: {{ \Modules\Store\Services\StoreMoney::format($line->line_total_minor,$order->currency) }}</p>@endforeach
<p>{{ __('store::store.total') }}: {{ \Modules\Store\Services\StoreMoney::format($order->total_minor,$order->currency) }}</p>
@if($order->tracking_number)<p>{{ __('store::store.tracking_number') }}: {{ $order->tracking_number }}</p>@endif
@if($order->status==='cancelled' && $order->payment_status==='paid')<p>{{ __('store::store.cancelled_refund_help') }}</p>@endif
@endsection
