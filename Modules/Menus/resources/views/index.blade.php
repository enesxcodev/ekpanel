<x-admin.layout title="Menüler">
    <div class="card">
        <div class="card-header d-flex gap-3 align-items-center">
            <h2 class="card-title me-auto">Menüler</h2>
            @can('menus.create')
                <a href="{{ route('admin.menus.create') }}" class="btn btn-primary">Menü ekle</a>
            @endcan
        </div>
        <x-admin.data-table
            id="menus-table" :source="route('admin.menus.data')"
            :options="['order' => [[0, 'desc']]]"
            :columns="[
                ['title' => 'ID', 'data' => 'id', 'name' => 'id'],
                ['title' => 'Menü adı', 'data' => 'name', 'name' => 'name'],
                ['title' => 'Anahtar', 'data' => 'key', 'name' => 'key'],
                ['title' => 'Öğe sayısı', 'data' => 'items_count', 'name' => 'items_count', 'searchable' => false],
                ['title' => 'Durum', 'data' => 'is_active', 'name' => 'is_active', 'searchable' => false],
                ['title' => 'Oluşturulma tarihi', 'data' => 'created_at', 'name' => 'created_at'],
                ['title' => 'İşlemler', 'data' => 'actions', 'name' => 'actions', 'orderable' => false, 'searchable' => false],
            ]"
        />
    </div>
</x-admin.layout>
