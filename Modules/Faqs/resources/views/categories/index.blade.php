<x-admin.layout title="SSS kategorileri">
    @include('faqs::partials.languages', ['showStatus' => false])
    <div class="card">
        <div class="card-header d-flex gap-3 align-items-center">
            <h2 class="card-title me-auto">SSS kategorileri</h2>
            @can('faqs.categories.create')
                <a href="{{ route('admin.faqs.categories.create', array_filter(['lang' => $selectedLanguage?->code])) }}" class="btn btn-primary">Kategori ekle</a>
            @endcan
        </div>
        <x-admin.data-table
            id="faq-categories-table"
            :source="route('admin.faqs.categories.data', array_filter(['lang' => $selectedLanguage?->code]))"
            :options="['order' => [[0, 'desc']]]"
            :columns="[
                ['title' => 'ID', 'data' => 'id', 'name' => 'faq_categories.id'],
                ['title' => 'Kategori', 'data' => 'name', 'name' => 'localized.name'],
                ['title' => 'Soru sayısı', 'data' => 'faqs_count', 'name' => 'faqs_count'],
                ['title' => 'Oluşturulma tarihi', 'data' => 'created_at', 'name' => 'faq_categories.created_at'],
                ['title' => 'İşlemler', 'data' => 'actions', 'name' => 'actions', 'orderable' => false, 'searchable' => false],
            ]"
        />
    </div>
</x-admin.layout>
