<x-admin.layout title="Şube / Bayi Kategorileri">
    <div class="card">
        <div class="card-header d-flex gap-3 align-items-center">
            <h2 class="card-title me-auto">Şube / Bayi Kategorileri</h2>
            @can('branches.categories.create')
                <a href="{{ route('admin.branches.categories.create') }}" class="btn btn-primary">Kategori ekle</a>
            @endcan
        </div>
        @error('category')<div class="alert alert-danger m-3" role="alert">{{ $message }}</div>@enderror
        <x-admin.data-table
            id="branch-categories-table"
            :source="route('admin.branches.categories.data')"
            :options="['order' => [[4, 'asc'], [0, 'asc']]]"
            :columns="[
                ['title' => 'ID', 'data' => 'id', 'name' => 'id'],
                ['title' => 'Kategori adı', 'data' => 'name', 'name' => 'name'],
                ['title' => 'Slug', 'data' => 'slug', 'name' => 'slug'],
                ['title' => 'Kayıt sayısı', 'data' => 'branches_count', 'name' => 'branches_count', 'searchable' => false],
                ['title' => 'Sıra', 'data' => 'sort_order', 'name' => 'sort_order'],
                ['title' => 'Durum', 'data' => 'is_active', 'name' => 'is_active', 'searchable' => false],
                ['title' => 'İşlemler', 'data' => 'actions', 'name' => 'actions', 'orderable' => false, 'searchable' => false],
            ]"
        />
    </div>
</x-admin.layout>
