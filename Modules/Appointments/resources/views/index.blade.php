<x-admin.layout title="Randevular">
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h2 class="card-title me-auto">Randevular</h2>
            @can('appointments.create')
                <a class="btn btn-primary" href="{{ route('admin.appointments.create') }}">Randevu ekle</a>
            @endcan
        </div>
        <x-admin.data-table id="appointments-table" :source="route('admin.appointments.data')" :columns="[
            ['title' => 'Müşteri', 'data' => 'customer_name', 'name' => 'customer_name'],
            ['title' => 'Hizmet', 'data' => 'service_name', 'name' => 'service_name'],
            ['title' => 'Başlangıç', 'data' => 'starts_at', 'name' => 'starts_at'],
            ['title' => 'Süre', 'data' => 'duration_minutes', 'name' => 'duration_minutes'],
            ['title' => 'Durum', 'data' => 'status', 'name' => 'status'],
            ['title' => 'İşlemler', 'data' => 'actions', 'name' => 'actions', 'orderable' => false, 'searchable' => false],
        ]" />
    </div>
</x-admin.layout>
