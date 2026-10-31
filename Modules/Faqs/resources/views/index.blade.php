<x-admin.layout title="Sıkça Sorulan Sorular">
    @include('faqs::partials.languages', ['showStatus' => false])
    <div class="card">
        <div class="card-header d-flex gap-3 align-items-center">
            <h2 class="card-title me-auto">Sıkça Sorulan Sorular</h2>
            @can('faqs.create')
                <a href="{{ route('admin.faqs.create', array_filter(['lang' => $selectedLanguage?->code])) }}" class="btn btn-primary">Soru ekle</a>
            @endcan
        </div>
        <x-admin.data-table
            id="faqs-table"
            :source="route('admin.faqs.data', array_filter(['lang' => $selectedLanguage?->code]))"
            :options="['order' => [[0, 'desc']]]"
            :columns="[
                ['title' => 'ID', 'data' => 'id', 'name' => 'faqs.id'],
                ['title' => 'Soru', 'data' => 'name', 'name' => 'localized.name'],
                ['title' => 'Kategori', 'data' => 'category_name', 'name' => 'category_name', 'orderable' => false, 'searchable' => false],
                ['title' => 'Yanıt', 'data' => 'content', 'name' => 'localized.content'],
                ['title' => 'Oluşturulma tarihi', 'data' => 'created_at', 'name' => 'faqs.created_at'],
                ['title' => 'İşlemler', 'data' => 'actions', 'name' => 'actions', 'orderable' => false, 'searchable' => false],
            ]"
        />
    </div>
</x-admin.layout>
