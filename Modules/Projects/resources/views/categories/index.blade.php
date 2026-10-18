<x-admin.layout title="Proje kategorileri">
    <div class="card">
        <div class="card-header"><h2 class="card-title me-auto">Proje kategorileri</h2>
            @can('projects.categories.create')<a href="{{ route('admin.projects.categories.create') }}" class="btn btn-primary">Kategori ekle</a>@endcan
        </div>
        @if ($errors->any())<div class="alert alert-danger m-3">{{ $errors->first() }}</div>@endif
        <x-admin.data-table id="project-categories-table" :source="route('admin.projects.categories.data')"
            :columns="[
                ['title' => 'Kategori', 'data' => 'name', 'name' => 'name'],
                ['title' => 'Kısa bağlantı', 'data' => 'slug', 'name' => 'slug'],
                ['title' => 'İkon', 'data' => 'icon', 'name' => 'icon'],
                ['title' => 'İşlemler', 'data' => 'actions', 'orderable' => false, 'searchable' => false],
            ]" />
    </div>
</x-admin.layout>