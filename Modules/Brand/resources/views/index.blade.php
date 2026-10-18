<x-admin.layout title="Markalar">
    <div class="card">
        <div class="card-header d-flex gap-3 align-items-center">
            <h2 class="card-title me-auto">Markalar</h2>
            @can('brand.create')
                <a href="{{ route('admin.brand.create') }}" class="btn btn-primary">Marka ekle</a>
            @endcan
        </div>
        <x-admin.data-table
            id="brands-table" :source="route('admin.brand.data')"
            :options="['order' => [[0, 'desc']]]"
            :columns="[
                ['title' => 'ID', 'data' => 'id', 'name' => 'id'],
                ['title' => 'Görsel', 'data' => 'visual', 'name' => 'visual', 'orderable' => false, 'searchable' => false],
                ['title' => 'Marka ismi', 'data' => 'name', 'name' => 'name'],
                ['title' => 'Kısa açıklama', 'data' => 'short_description', 'name' => 'short_description'],
                ['title' => 'Link', 'data' => 'link', 'name' => 'link'],
                ['title' => 'Sıra', 'data' => 'sort_order', 'name' => 'sort_order'],
                ['title' => 'Durum', 'data' => 'is_active', 'name' => 'is_active'],
                ['title' => 'İşlemler', 'data' => 'actions', 'name' => 'actions', 'orderable' => false, 'searchable' => false],
            ]"
        />
    </div>
</x-admin.layout>
