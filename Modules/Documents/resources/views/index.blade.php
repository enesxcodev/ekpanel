<x-admin.layout title="Belgeler">
    <div class="card">
        <div class="card-header d-flex gap-3 align-items-center">
            <h2 class="card-title me-auto">Belgeler</h2>
            @can('documents.create')
                <a href="{{ route('admin.documents.create') }}" class="btn btn-primary">Belge ekle</a>
            @endcan
        </div>
        <x-admin.data-table
            id="documents-table"
            :source="route('admin.documents.data')"
            :options="['order' => [[0, 'desc']]]"
            :columns="[
                ['title' => 'ID', 'data' => 'id', 'name' => 'documents.id'],
                ['title' => 'Belge adı', 'data' => 'name', 'name' => 'documents.name'],
                ['title' => 'Kısa bağlantı', 'data' => 'slug', 'name' => 'documents.slug'],
                ['title' => 'Kategori', 'data' => 'category_name', 'name' => 'category_name', 'orderable' => false, 'searchable' => false],
                ['title' => 'İçerik', 'data' => 'content', 'name' => 'documents.content'],
                ['title' => 'Dosya', 'data' => 'file', 'name' => 'documents.file', 'orderable' => false, 'searchable' => false],
                ['title' => 'Durum', 'data' => 'status', 'name' => 'documents.status', 'searchable' => false],
                ['title' => 'Oluşturulma tarihi', 'data' => 'created_at', 'name' => 'documents.created_at'],
                ['title' => 'İşlemler', 'data' => 'actions', 'name' => 'actions', 'orderable' => false, 'searchable' => false],
            ]"
        />
    </div>
</x-admin.layout>
