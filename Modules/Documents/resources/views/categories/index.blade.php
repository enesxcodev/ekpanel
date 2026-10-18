<x-admin.layout title="Belge kategorileri">
    <div class="card">
        <div class="card-header d-flex gap-3 align-items-center">
            <h2 class="card-title me-auto">Belge kategorileri</h2>
            @can('documents.categories.create')
                <a href="{{ route('admin.documents.categories.create') }}" class="btn btn-primary">Kategori ekle</a>
            @endcan
        </div>
        <x-admin.data-table
            id="document-categories-table"
            :source="route('admin.documents.categories.data')"
            :options="['order' => [[0, 'desc']]]"
            :columns="[
                ['title' => 'ID', 'data' => 'id', 'name' => 'document_categories.id'],
                ['title' => 'Kategori', 'data' => 'name', 'name' => 'document_categories.name'],
                ['title' => 'İkon', 'data' => 'icon', 'name' => 'document_categories.icon'],
                ['title' => 'Belge sayısı', 'data' => 'documents_count', 'name' => 'documents_count', 'searchable' => false],
                ['title' => 'Oluşturulma tarihi', 'data' => 'created_at', 'name' => 'document_categories.created_at'],
                ['title' => 'İşlemler', 'data' => 'actions', 'name' => 'actions', 'orderable' => false, 'searchable' => false],
            ]"
        />
    </div>
</x-admin.layout>
