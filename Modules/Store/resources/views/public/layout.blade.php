@extends('frontend.themes.main.layouts.app')
@push('styles')
    @vite(['resources/css/app.css'])
@endpush
@section('content')
<div class="mx-auto max-w-6xl px-4 py-8">
<nav class="mb-6 flex gap-4"><a href="{{ route('store.index') }}">Mağaza</a><a href="{{ route('store.cart') }}">Sepet</a><a href="{{ route('store.account') }}">Siparişlerim</a></nav>
@if($errors->any())<div role="alert" class="mb-4 rounded border border-red-500 p-4">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
@yield('store-content')
</div>
@endsection
