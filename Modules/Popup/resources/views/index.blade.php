<x-admin.layout title="Popup">
    <div class="card">
        <div class="card-header d-flex gap-3 align-items-center">
            <h2 class="card-title me-auto">Popup</h2>
            @can('popup.create')
                <a href="{{ route('admin.popup.create') }}" class="btn btn-primary">Popup ekle</a>
            @endcan
        </div>
        <x-admin.data-table
            id="popups-table" :source="route('admin.popup.data')"
            :options="['order' => [[0, 'desc']]]"
            :columns="[
                ['title' => 'ID', 'data' => 'id', 'name' => 'id'],
                ['title' => 'Görsel', 'data' => 'image', 'name' => 'image', 'orderable' => false, 'searchable' => false],
                ['title' => 'Başlık', 'data' => 'title', 'name' => 'title'],
                ['title' => 'Başlangıç', 'data' => 'starts_at', 'name' => 'starts_at'],
                ['title' => 'Bitiş', 'data' => 'ends_at', 'name' => 'ends_at'],
                ['title' => 'Tekrar gösterim', 'data' => 'repeat_policy', 'name' => 'repeat_policy'],
                ['title' => 'Durum', 'data' => 'is_active', 'name' => 'is_active'],
                ['title' => 'İşlemler', 'data' => 'actions', 'name' => 'actions', 'orderable' => false, 'searchable' => false],
            ]"
        />
    </div>
</x-admin.layout>
