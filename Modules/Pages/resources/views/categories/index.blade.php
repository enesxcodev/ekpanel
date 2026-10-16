<x-admin.layout title="Sayfa kategorileri">
    <div class="card">
        <div class="card-header d-flex gap-3 align-items-center">
            <h2 class="card-title me-auto">Sayfa kategorileri</h2>
            @can('pages.categories.create')
                <a href="{{ route('admin.pages.categories.create') }}" class="btn btn-primary">Kategori ekle</a>
            @endcan
        </div>
        <x-admin.data-table
            id="page-categories-table" :source="route('admin.pages.categories.data')"
            :options="['order' => [[4, 'asc']]]"
            :columns="[
                ['title' => 'ID', 'data' => 'id', 'name' => 'id'],
                ['title' => 'Kategori adı', 'data' => 'name', 'name' => 'name'],
                ['title' => 'Üst kategori', 'data' => 'parent_name', 'name' => 'parent.name', 'orderable' => false],
                ['title' => 'Kısa bağlantı', 'data' => 'slug', 'name' => 'slug'],
                ['title' => 'Sıra', 'data' => 'sort_order', 'name' => 'sort_order'],
                ['title' => 'İşlemler', 'data' => 'actions', 'name' => 'actions', 'orderable' => false, 'searchable' => false],
            ]"
        />
    </div>
</x-admin.layout>
