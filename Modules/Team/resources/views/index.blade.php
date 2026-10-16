<x-admin.layout title="Ekipler">
    <div class="card">
        <div class="card-header d-flex gap-3 align-items-center">
            <h2 class="card-title me-auto">Ekipler</h2>
            @can('team.create')
                <a href="{{ route('admin.team.create') }}" class="btn btn-primary">Ekip ekle</a>
            @endcan
        </div>
        <x-admin.data-table
            id="team-members-table" :source="route('admin.team.data')"
            :options="['order' => [[0, 'desc']]]"
            :columns="[
                ['title' => 'ID', 'data' => 'id', 'name' => 'id'],
                ['title' => 'Görsel', 'data' => 'photo', 'name' => 'photo', 'orderable' => false, 'searchable' => false],
                ['title' => 'İsim', 'data' => 'name', 'name' => 'name'],
                ['title' => 'Ünvan', 'data' => 'title', 'name' => 'title'],
                ['title' => 'Durum', 'data' => 'is_active', 'name' => 'is_active', 'searchable' => false],
                ['title' => 'Oluşturulma tarihi', 'data' => 'created_at', 'name' => 'created_at'],
                ['title' => 'İşlemler', 'data' => 'actions', 'name' => 'actions', 'orderable' => false, 'searchable' => false],
            ]"
        />
    </div>
</x-admin.layout>
