<x-admin.layout title="Şubeler / Bayiler">
    <div class="card">
        <div class="card-header d-flex flex-wrap gap-3 align-items-center">
            <h2 class="card-title me-auto">Şubeler / Bayiler</h2>
            <label class="mb-0">Dil <select class="form-select" data-branches-language>@foreach ($activeLanguages as $language)<option value="{{ $language->code }}" @selected($selectedLanguage?->id === $language->id)>{{ config('languages.catalog.'.$language->code.'.name', $language->code) }}</option>@endforeach</select></label>
            <label class="mb-0">Kategori
                <select class="form-select" data-table-filter="category_id" data-table-target="branches-table" aria-label="Kategori filtresi">
                    <option value="">Tüm kategoriler</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="mb-0">Durum
                <select class="form-select" data-table-filter="is_active" data-table-target="branches-table" aria-label="Durum filtresi">
                    <option value="">Tümü</option>
                    <option value="1">Aktif</option>
                    <option value="0">Pasif</option>
                </select>
            </label>
            @can('branches.create')
                <a href="{{ route('admin.branches.create') }}" class="btn btn-primary">Şube / bayi ekle</a>
            @endcan
        </div>
        <x-admin.data-table
            id="branches-table"
            :source="route('admin.branches.data', ['lang' => $selectedLanguage?->code])"
            :options="['order' => [[0, 'desc']]]"
            :columns="[
                ['title' => 'ID', 'data' => 'id', 'name' => 'branches.id'],
                ['title' => 'Ad', 'data' => 'name', 'name' => 'localized.name'],
                ['title' => 'Kategori', 'data' => 'category_name', 'name' => 'category_localized.name'],
                ['title' => 'İl / ilçe', 'data' => 'location_name', 'name' => 'location_name', 'orderable' => false, 'searchable' => false],
                ['title' => 'Telefon', 'data' => 'phone', 'name' => 'localized.phone'],
                ['title' => 'Sıra', 'data' => 'sort_order', 'name' => 'branches.sort_order'],
                ['title' => 'Durum', 'data' => 'is_active', 'name' => 'branches.is_active', 'searchable' => false],
                ['title' => 'İşlemler', 'data' => 'actions', 'name' => 'actions', 'orderable' => false, 'searchable' => false],
            ]"
        />
    </div>
</x-admin.layout>
