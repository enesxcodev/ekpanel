<x-admin.layout title="Ürün düzenle"><form method="POST" action="{{ route('admin.store.update', $product) }}">@include('store::products.partials.form')</form></x-admin.layout>
