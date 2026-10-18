<x-admin.layout title="Yöneticiler">
    <div class="card">
        <div class="card-header d-flex flex-wrap gap-3 align-items-center">
            <h2 class="card-title me-auto">Yönetici hesapları</h2>
            <label for="admin-status-filter" class="visually-hidden">Duruma göre filtrele</label>
            <select id="admin-status-filter" class="form-select w-auto" data-table-filter="status" data-table-target="admin-users-table">
                <option value="">Tüm durumlar</option>
                <option value="1">Aktif</option>
                <option value="0">Pasif</option>
            </select>
            @can('create', \App\Models\Admin\Admin::class)
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>
                Yönetici ekle
            </a>
            @endcan
        </div>
        <x-admin.data-table
            id="admin-users-table"
            :source="route('admin.users.data')"
            :options="['order' => [[3, 'desc']]]"
            :columns="[
                ['title' => 'Ad soyad', 'data' => 'full_name', 'name' => 'full_name'],
                ['title' => 'Tür', 'data' => 'type_label', 'name' => 'type_label', 'orderable' => false],
                ['title' => 'Durum', 'data' => 'status_badge', 'name' => 'status', 'orderable' => false, 'searchable' => false],
                ['title' => 'Eklenme tarihi', 'data' => 'created_at', 'name' => 'created_at'],
                ['title' => 'İşlemler', 'data' => 'actions', 'name' => 'actions', 'orderable' => false, 'searchable' => false],
            ]"
        />
    </div>
</x-admin.layout>
