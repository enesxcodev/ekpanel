<x-admin.layout title="Projeler">
    <div class="card">
        <div class="card-header"><h2 class="card-title me-auto">Projeler</h2>@can('projects.create')<a href="{{ route('admin.projects.create') }}" class="btn btn-primary">Proje ekle</a>@endcan</div>
        <x-admin.data-table id="projects-table" :source="route('admin.projects.data')" :options="['order' => [[6, 'asc']]]"
            :columns="[
                ['title' => 'ID', 'data' => 'id', 'name' => 'projects.id'],
                ['title' => 'Kapak', 'data' => 'cover', 'orderable' => false, 'searchable' => false],
                ['title' => 'Başlık', 'data' => 'title', 'name' => 'project_translation.title'],
                ['title' => 'Kategori', 'data' => 'category_name', 'name' => 'category.name', 'orderable' => false],
                ['title' => 'Proje tarihi', 'data' => 'project_date', 'name' => 'projects.project_date'],
                ['title' => 'Yayın durumu', 'data' => 'is_active', 'name' => 'projects.is_active', 'searchable' => false],
                ['title' => 'Sıra', 'data' => 'sort_order', 'name' => 'projects.sort_order'],
                ['title' => 'İşlemler', 'data' => 'actions', 'orderable' => false, 'searchable' => false],
            ]" />
    </div>
</x-admin.layout>
