<x-admin.layout title="Ürünler">
    <div class="card">
        <div class="card-header d-flex gap-3 align-items-center">
            <h2 class="card-title me-auto">Ürünler</h2>
            @can('products.create')
                <a href="{{ route('admin.products.create') }}" class="btn btn-primary">Ürün ekle</a>
            @endcan
        </div>
        <div class="card-body d-flex gap-3">
            <label>Kategori<select class="form-select" data-table-filter="category_id" data-table-target="products-table"><option value="">Tümü</option>@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select></label>
            <label>Yayın durumu<select class="form-select" data-table-filter="is_active" data-table-target="products-table"><option value="">Tümü</option><option value="1">Yayında</option><option value="0">Taslak</option></select></label>
        </div>
        <x-admin.data-table
            id="products-table"
            :source="route('admin.products.data')"
            :options="['order' => [[5, 'asc'], [0, 'asc']]]"
            :columns="[
                ['title' => 'ID', 'data' => 'id', 'name' => 'products.id'],
                ['title' => 'Kapak', 'data' => 'visual', 'name' => 'visual', 'orderable' => false, 'searchable' => false],
                ['title' => 'Ürün adı', 'data' => 'title', 'name' => 'products.title'],
                ['title' => 'Kategori', 'data' => 'category_name', 'name' => 'category.name'],
                ['title' => 'Kısa bağlantı', 'data' => 'slug', 'name' => 'products.slug'],
                ['title' => 'Sıra', 'data' => 'sort_order', 'name' => 'products.sort_order'],
                ['title' => 'İşlemler', 'data' => 'actions', 'name' => 'actions', 'orderable' => false, 'searchable' => false],
                ['title' => 'Durum', 'data' => 'is_active', 'name' => 'products.is_active', 'searchable' => false],
            ]"
        />
    </div>
</x-admin.layout>
