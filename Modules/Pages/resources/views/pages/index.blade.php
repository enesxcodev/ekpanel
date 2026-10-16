<x-admin.layout title="Sayfalar">
    <div class="card">
        <div class="card-header d-flex gap-3 align-items-center">
            <h2 class="card-title me-auto">Sayfalar</h2>
            @can('pages.create')
                <a href="{{ route('admin.pages.create') }}" class="btn btn-primary">Sayfa ekle</a>
            @endcan
        </div>
        <x-admin.data-table
            id="pages-table" :source="route('admin.pages.data')"
            :options="['order' => [[5, 'asc']]]"
            :columns="[
                ['title' => 'ID', 'data' => 'id', 'name' => 'id'],
                ['title' => 'Başlık', 'data' => 'title', 'name' => 'title'],
                ['title' => 'Kategori', 'data' => 'category_name', 'name' => 'category.name', 'orderable' => false],
                ['title' => 'Kısa bağlantı', 'data' => 'slug', 'name' => 'slug'],
                ['title' => 'Durum', 'data' => 'status', 'name' => 'status', 'searchable' => false],
                ['title' => 'Sıra', 'data' => 'sort_order', 'name' => 'sort_order'],
                ['title' => 'İşlemler', 'data' => 'actions', 'name' => 'actions', 'orderable' => false, 'searchable' => false],
            ]"
        />
    </div>
</x-admin.layout>
