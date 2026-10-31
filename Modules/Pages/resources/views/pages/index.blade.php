<x-admin.layout title="Sayfalar">
    <div class="card">
        <div class="card-header d-flex gap-3 align-items-center">
            <h2 class="card-title me-auto">Sayfalar</h2>
            <form method="GET" class="d-flex align-items-center gap-2">
                <label for="pages-language-filter" class="form-label mb-0">Dil</label>
                <select id="pages-language-filter" name="lang" class="form-select" onchange="this.form.submit()">
                    @foreach ($activeLanguages as $language)
                        <option value="{{ $language->code }}" @selected($selectedLanguage?->id === $language->id)>{{ config('languages.catalog.'.$language->code.'.name', $language->code) }}</option>
                    @endforeach
                </select>
            </form>
            @can('pages.create')
                <a href="{{ route('admin.pages.create', ['lang' => $selectedLanguage?->code]) }}" class="btn btn-primary">Sayfa ekle</a>
            @endcan
        </div>
        <x-admin.data-table
            id="pages-table" :source="route('admin.pages.data', ['lang' => $selectedLanguage?->code])"
            :options="['order' => [[5, 'asc']]]"
            :columns="[
                ['title' => 'ID', 'data' => 'id', 'name' => 'id'],
                ['title' => 'Başlık', 'data' => 'title', 'name' => 'title'],
                ['title' => 'Kategori', 'data' => 'category_name', 'name' => 'category.name', 'orderable' => false],
                ['title' => 'Kısa bağlantı', 'data' => 'slug', 'name' => 'slug'],
                ['title' => 'Durum', 'data' => 'status', 'name' => 'status', 'searchable' => false],
                ['title' => 'Sıra', 'data' => 'sort_order', 'name' => 'sort_order'],
                ['title' => 'İşlemler', 'data' => 'actions', 'name' => 'actions', 'orderable' => false, 'searchable' => false],
            ]"
        />
    </div>
</x-admin.layout>
