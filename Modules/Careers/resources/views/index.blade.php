<x-admin.layout title="Kariyer">
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h2 class="card-title me-auto">İş ilanları</h2>
            @can('careers.view')
                <a class="btn btn-outline-secondary me-2" href="{{ route('admin.careers.applications.index') }}">Başvurular</a>
            @endcan
            @can('careers.create')
                <a class="btn btn-primary" href="{{ route('admin.careers.create') }}">İlan ekle</a>
            @endcan
        </div>
        <x-admin.data-table id="careers-table" :source="route('admin.careers.data')" :columns="[
            ['title' => 'İlan', 'data' => 'title', 'name' => 'title'],
            ['title' => 'Konum', 'data' => 'location', 'name' => 'location'],
            ['title' => 'Çalışma türü', 'data' => 'employment_type', 'name' => 'employment_type'],
            ['title' => 'Son başvuru', 'data' => 'application_deadline', 'name' => 'application_deadline'],
            ['title' => 'Durum', 'data' => 'is_active', 'name' => 'is_active'],
            ['title' => 'İşlemler', 'data' => 'actions', 'name' => 'actions', 'orderable' => false, 'searchable' => false],
        ]" />
    </div>
</x-admin.layout>
