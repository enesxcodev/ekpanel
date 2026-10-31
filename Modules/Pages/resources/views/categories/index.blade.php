<x-admin.layout title="Sayfa kategorileri">
    <div class="card">
        <div class="card-header d-flex gap-3 align-items-center">
            <h2 class="card-title me-auto">Sayfa kategorileri</h2>
            <form method="GET" class="d-flex align-items-center gap-2">
                <label for="page-categories-language-filter" class="form-label mb-0">Dil</label>
                <select id="page-categories-language-filter" name="lang" class="form-select" onchange="this.form.submit()">
                    @foreach ($activeLanguages as $language)
                        <option value="{{ $language->code }}" @selected($selectedLanguage?->id === $language->id)>{{ config('languages.catalog.'.$language->code.'.name', $language->code) }}</option>
                    @endforeach
                </select>
            </form>
            @can('pages.categories.create')
                <a href="{{ route('admin.pages.categories.create', ['lang' => $selectedLanguage?->code]) }}" class="btn btn-primary">Kategori ekle</a>
            @endcan
        </div>
        <x-admin.data-table
            id="page-categories-table" :source="route('admin.pages.categories.data', ['lang' => $selectedLanguage?->code])"
            :options="['order' => [[4, 'asc']]]"
            :columns="[
                ['title' => 'ID', 'data' => 'id', 'name' => 'id'],
                ['title' => 'Kategori adı', 'data' => 'name', 'name' => 'name'],
                ['title' => 'Üst kategori', 'data' => 'parent_name', 'name' => 'parent.name', 'orderable' => false],
                ['title' => 'Kısa bağlantı', 'data' => 'slug', 'name' => 'slug'],
                ['title' => 'Sıra', 'data' => 'sort_order', 'name' => 'sort_order'],
                ['title' => 'İşlemler', 'data' => 'actions', 'name' => 'actions', 'orderable' => false, 'searchable' => false],
            ]"
        />
    </div>
</x-admin.layout>
