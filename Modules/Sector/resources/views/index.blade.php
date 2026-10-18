<x-admin.layout title="Sektörler">
    <div class="card">
        <div class="card-header d-flex gap-3 align-items-center">
            <h2 class="card-title me-auto">Sektörler</h2>
            @can('sector.create')
                <a href="{{ route('admin.sector.create') }}" class="btn btn-primary">Sektör ekle</a>
            @endcan
        </div>
        <x-admin.data-table
            id="sectors-table" :source="route('admin.sector.data')"
            :options="['order' => [[0, 'desc']]]"
            :columns="[
                ['title' => 'ID', 'data' => 'id', 'name' => 'id'],
                ['title' => 'Görsel', 'data' => 'photo', 'name' => 'photo', 'orderable' => false, 'searchable' => false],
                ['title' => 'Başlık', 'data' => 'title', 'name' => 'title'],
                ['title' => 'Kısa bağlantı', 'data' => 'slug', 'name' => 'slug'],
                ['title' => 'Sıra', 'data' => 'sort_order', 'name' => 'sort_order'],
                ['title' => 'Durum', 'data' => 'is_active', 'name' => 'is_active', 'searchable' => false],
                ['title' => 'Oluşturulma tarihi', 'data' => 'created_at', 'name' => 'created_at'],
                ['title' => 'İşlemler', 'data' => 'actions', 'name' => 'actions', 'orderable' => false, 'searchable' => false],
            ]"
        />
    </div>
</x-admin.layout>
