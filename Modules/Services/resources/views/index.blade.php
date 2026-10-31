<x-admin.layout title="Hizmetler">
    <div class="card">
        <div class="card-body"><label class="form-label" for="services-table-language">Dil</label><select id="services-table-language" class="form-select" data-table-filter="lang" data-table-target="services-table">@foreach (app(\Modules\Services\Services\ServicesLanguageService::class)->active() as $language)<option value="{{ $language->code }}">{{ config('languages.catalog.'.$language->code.'.name', $language->code) }}</option>@endforeach</select></div>
        <div class="card-header d-flex gap-3 align-items-center">
            <h2 class="card-title me-auto">Hizmetler</h2>
            @can('services.create')
                <a href="{{ route('admin.services.create') }}" class="btn btn-primary">Hizmet ekle</a>
            @endcan
        </div>
        <x-admin.data-table
            id="services-table"
            :source="route('admin.services.data')"
            :options="['order' => [[0, 'desc']]]"
            :columns="[
                ['title' => 'ID', 'data' => 'id', 'name' => 'services.id'],
                ['title' => 'Resim / ikon', 'data' => 'visual', 'name' => 'visual', 'orderable' => false, 'searchable' => false],
                ['title' => 'Hizmet başlık', 'data' => 'title', 'name' => 'title'],
                ['title' => 'Kategori', 'data' => 'category_name', 'name' => 'category_name', 'orderable' => false],
                ['title' => 'Kısa bağlantı', 'data' => 'slug', 'name' => 'slug'],
                ['title' => 'Sıra', 'data' => 'sort_order', 'name' => 'services.sort_order'],
                ['title' => 'İşlemler', 'data' => 'actions', 'name' => 'actions', 'orderable' => false, 'searchable' => false],
                ['title' => 'Durum', 'data' => 'is_active', 'name' => 'services.is_active', 'searchable' => false],
            ]"
        />
    </div>
</x-admin.layout>
