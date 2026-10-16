<x-admin.layout title="Hizmetler">
    <div class="card">
        <div class="card-header d-flex gap-3 align-items-center">
            <h2 class="card-title me-auto">Hizmetler</h2>
            @can('services.create')
                <a href="{{ route('admin.services.create') }}" class="btn btn-primary">Hizmet ekle</a>
            @endcan
        </div>
        <x-admin.data-table
            id="services-table"
            :source="route('admin.services.data')"
            :options="['order' => [[0, 'desc']]]"
            :columns="[
                ['title' => 'ID', 'data' => 'id', 'name' => 'services.id'],
                ['title' => 'Resim / ikon', 'data' => 'visual', 'name' => 'visual', 'orderable' => false, 'searchable' => false],
                ['title' => 'Hizmet başlık', 'data' => 'title', 'name' => 'services.title'],
                ['title' => 'Kategori', 'data' => 'category_name', 'name' => 'category.name'],
                ['title' => 'Durum', 'data' => 'is_active', 'name' => 'services.is_active', 'searchable' => false],
            ]"
        />
    </div>
</x-admin.layout>
