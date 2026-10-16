<x-admin.layout title="Slider">
    <div class="card">
        <div class="card-header d-flex gap-3 align-items-center">
            <h2 class="card-title me-auto">Slider</h2>
            @can('slider.create')
                <a href="{{ route('admin.slider.create') }}" class="btn btn-primary">Slider ekle</a>
            @endcan
        </div>
        <x-admin.data-table
            id="sliders-table" :source="route('admin.slider.data')"
            :options="['order' => [[0, 'desc']]]"
            :columns="[
                ['title' => 'ID', 'data' => 'id', 'name' => 'id'],
                ['title' => 'Başlık', 'data' => 'title', 'name' => 'title'],
                ['title' => 'Alt başlık', 'data' => 'subtitle', 'name' => 'subtitle'],
                ['title' => 'İşlemler', 'data' => 'actions', 'name' => 'actions', 'orderable' => false, 'searchable' => false],
            ]"
        />
    </div>
</x-admin.layout>
