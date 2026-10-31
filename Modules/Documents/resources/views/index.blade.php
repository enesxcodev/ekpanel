<x-admin.layout title="Belgeler">
    <div class="card">
        <div class="card-header d-flex gap-3 align-items-center">
            <h2 class="card-title me-auto">Belgeler</h2>
            @include('documents::partials.languages', ['showStatus' => false, 'asCard' => false])
            @can('documents.create')
                <a href="{{ route('admin.documents.create', array_filter(['lang' => $selectedLanguage?->code])) }}" class="btn btn-primary">Belge ekle</a>
            @endcan
        </div>
        <x-admin.data-table
            id="documents-table"
            :source="route('admin.documents.data', array_filter(['lang' => $selectedLanguage?->code]))"
            :options="['order' => [[0, 'desc']]]"
            :columns="[
                ['title' => 'ID', 'data' => 'id', 'name' => 'documents.id'],
                ['title' => 'Belge adı', 'data' => 'name', 'name' => 'localized.name'],
                ['title' => 'Kısa bağlantı', 'data' => 'slug', 'name' => 'localized.slug'],
                ['title' => 'Kategori', 'data' => 'category_name', 'name' => 'localized_category.name'],
                ['title' => 'İçerik', 'data' => 'content', 'name' => 'localized.content'],
                ['title' => 'Dosya', 'data' => 'file', 'name' => 'documents.file', 'orderable' => false, 'searchable' => false],
                ['title' => 'Durum', 'data' => 'status', 'name' => 'documents.status', 'searchable' => false],
                ['title' => 'Oluşturulma tarihi', 'data' => 'created_at', 'name' => 'documents.created_at'],
                ['title' => 'İşlemler', 'data' => 'actions', 'name' => 'actions', 'orderable' => false, 'searchable' => false],
            ]"
        />
    </div>
</x-admin.layout>
