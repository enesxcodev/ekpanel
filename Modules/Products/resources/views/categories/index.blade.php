<x-admin.layout title="Ürün Kategorileri">
    <div class="card">
        <div class="card-header d-flex gap-3 align-items-center">
            <h2 class="card-title me-auto">Ürün Kategorileri</h2>
            @can('products.categories.create')
                <a href="{{ route('admin.products.categories.create') }}" class="btn btn-primary">Kategori ekle</a>
            @endcan
        </div>
        <x-admin.data-table
            id="product-categories-table"
            :source="route('admin.products.categories.data')"
            :options="['order' => [[0, 'desc']]]"
            :columns="[
                ['title' => 'ID', 'data' => 'id', 'name' => 'id'],
                ['title' => 'Kategori adı', 'data' => 'name', 'name' => 'name'],
                ['title' => 'Sıra', 'data' => 'sort_order', 'name' => 'sort_order'],
                ['title' => 'Durum', 'data' => 'is_active', 'name' => 'is_active'],
                ['title' => 'İşlemler', 'data' => 'actions', 'name' => 'actions', 'orderable' => false, 'searchable' => false],
            ]"
        />
    </div>
</x-admin.layout>
