<x-admin.layout title="Kullanıcılar">
    <div class="card">
        <div class="card-header"><h2 class="card-title">Kullanıcılar</h2></div>
        <x-admin.data-table
            id="web-users-table" :source="route('admin.web-users.data')"
            :options="['order' => [[0, 'desc']]]"
            :columns="[
                ['title' => 'ID', 'data' => 'id', 'name' => 'id'],
                ['title' => 'Ad Soyad', 'data' => 'full_name', 'name' => 'full_name'],
                ['title' => 'E-posta', 'data' => 'email', 'name' => 'email'],
                ['title' => 'İşlemler', 'data' => 'actions', 'name' => 'actions', 'orderable' => false, 'searchable' => false],
            ]"
        />
    </div>
</x-admin.layout>
