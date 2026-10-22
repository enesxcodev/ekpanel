@extends('store::public.layout')
@section('store-content')<h1 class="mb-4 text-3xl">Siparişlerim</h1>@forelse($orders as $order)<p><a href="{{ route('store.account.order',$order->number) }}">{{ $order->number }}</a> — {{ $order->status }}</p>@empty<p>Sipariş bulunamadı.</p>@endforelse {{ $orders->links() }}@endsection
