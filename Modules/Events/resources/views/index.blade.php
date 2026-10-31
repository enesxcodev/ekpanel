<x-admin.layout title="Etkinlikler">
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h2 class="card-title me-auto">Etkinlikler</h2>
            <select class="form-select w-auto me-2" onchange="window.location.href=this.value" aria-label="Dil">
                @foreach($activeLanguages as $language)
                    <option value="{{ route('admin.events.index', ['lang' => $language->code]) }}" @selected($selectedLanguage?->id === $language->id)>{{ $language->name ?? strtoupper($language->code) }}</option>
                @endforeach
            </select>
            @can('events.view')
                <a class="btn btn-outline-secondary me-2" href="{{ route('admin.events.registrations.index', ['lang' => $selectedLanguage?->code]) }}">Katılımcılar</a>
            @endcan
            @can('events.create')
                <a class="btn btn-primary" href="{{ route('admin.events.create', ['lang' => 'tr']) }}">Etkinlik ekle</a>
            @endcan
        </div>
        <x-admin.data-table id="events-table" :source="route('admin.events.data', ['lang' => $selectedLanguage?->code])" :columns="[
            ['title' => 'Etkinlik', 'data' => 'title', 'name' => 'title'],
            ['title' => 'Mekân', 'data' => 'venue', 'name' => 'venue'],
            ['title' => 'Başlangıç', 'data' => 'starts_at', 'name' => 'starts_at'],
            ['title' => 'Kapasite', 'data' => 'capacity', 'name' => 'capacity'],
            ['title' => 'Durum', 'data' => 'is_active', 'name' => 'is_active'],
            ['title' => 'İşlemler', 'data' => 'actions', 'name' => 'actions', 'orderable' => false, 'searchable' => false],
        ]" />
    </div>
</x-admin.layout>
