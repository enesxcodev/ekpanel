<x-admin.layout title="Mağaza ürünleri">
<div class="card"><div class="card-header"><h2 class="card-title me-auto">Mağaza ürünleri</h2>@can('store.create')<a class="btn btn-primary" href="{{ route('admin.store.create',['lang'=>request('lang','tr')]) }}">Ürün ekle</a>@endcan</div>
<x-admin.data-table id="store-products" :source="$dataUrl" :columns="[
['title'=>'ID','data'=>'id','name'=>'id'],['title'=>'Ürün','data'=>'title','name'=>'title'],['title'=>'SKU','data'=>'sku','name'=>'sku'],
['title'=>'Fiyat','data'=>'price_minor','name'=>'price_minor'],['title'=>'Stok','data'=>'stock_summary','orderable'=>false,'searchable'=>false],['title'=>'Durum','data'=>'is_active','name'=>'is_active'],
['title'=>'İşlemler','data'=>'actions','name'=>'actions','orderable'=>false,'searchable'=>false]
]" /></div>
</x-admin.layout>
