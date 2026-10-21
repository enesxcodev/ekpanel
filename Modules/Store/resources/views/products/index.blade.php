<x-admin.layout title="Mağaza ürünleri">
    <div class="card">
        <div class="card-header d-flex align-items-center gap-3"><h2 class="card-title me-auto">Mağaza ürünleri</h2>@can('store.create')<a class="btn btn-primary" href="{{ route('admin.store.create') }}">Ürün ekle</a>@endcan</div>
        <div class="table-responsive"><table class="table table-vcenter"><thead><tr><th>Ürün</th><th>SKU</th><th>Fiyat</th><th>Stok</th><th>Durum</th><th></th></tr></thead><tbody>@forelse($products as $product)<tr><td>{{ $product->title }}</td><td>{{ $product->sku }}</td><td>{{ number_format($product->price_minor / 100, 2, ',', '.') }} {{ $product->currency }}</td><td>{{ $product->stock_quantity }}</td><td>{{ $product->is_active ? 'Yayında' : 'Taslak' }}</td><td>@can('store.update')<a href="{{ route('admin.store.edit', $product) }}" class="btn btn-sm btn-outline-primary">Düzenle</a>@endcan</td></tr>@empty<tr><td colspan="6" class="text-secondary">Henüz ürün yok.</td></tr>@endforelse</tbody></table></div>
    </div>
</x-admin.layout>
