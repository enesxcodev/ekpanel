<x-admin.layout title="Hizmet Kategorileri">
    <div class="card">
        <div class="card-body"><label class="form-label" for="service-categories-table-language">Dil</label><select id="service-categories-table-language" class="form-select" data-table-filter="lang" data-table-target="service-categories-table">@foreach (app(\Modules\Services\Services\ServicesLanguageService::class)->active() as $language)<option value="{{ $language->code }}">{{ config('languages.catalog.'.$language->code.'.name', $language->code) }}</option>@endforeach</select></div>
        <div class="card-header d-flex gap-3 align-items-center">
            <h2 class="card-title me-auto">Hizmet Kategorileri</h2>
            @can('services.categories.create')
                <a href="{{ route('admin.services.categories.create') }}" class="btn btn-primary">Kategori ekle</a>
            @endcan
        </div>
        <x-admin.data-table
            id="service-categories-table"
            :source="route('admin.services.categories.data')"
            :options="['order' => [[0, 'desc']]]"
            :columns="[
                ['title' => 'ID', 'data' => 'id', 'name' => 'service_categories.id'],
                ['title' => 'Kategori adı', 'data' => 'name', 'name' => 'name'],
                ['title' => 'Sıra', 'data' => 'sort_order', 'name' => 'service_categories.sort_order'],
                ['title' => 'Durum', 'data' => 'is_active', 'name' => 'service_categories.is_active'],
                ['title' => 'İşlemler', 'data' => 'actions', 'name' => 'actions', 'orderable' => false, 'searchable' => false],
            ]"
        />
    </div>
</x-admin.layout>
