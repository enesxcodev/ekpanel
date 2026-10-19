<x-admin.layout title="Kullanıcılar">
    <div class="card">
        <div class="card-header"><h2 class="card-title">Kullanıcılar</h2></div>
        <x-admin.data-table
            id="web-users-table" :source="route('admin.web-users.data')"
            :options="['order' => [[0, 'desc']]]"
            :columns="[
                ['title' => 'ID', 'data' => 'id', 'name' => 'id'],
                ['title' => 'Profil resmi', 'data' => 'photo', 'name' => 'photo', 'orderable' => false, 'searchable' => false],
                ['title' => 'Ad', 'data' => 'name', 'name' => 'name'],
                ['title' => 'Soyad', 'data' => 'surname', 'name' => 'surname'],
                ['title' => 'E-posta', 'data' => 'email', 'name' => 'email'],
                ['title' => 'Kullanıcı adı', 'data' => 'username', 'name' => 'username'],
                ['title' => 'Durum', 'data' => 'is_active', 'name' => 'is_active', 'searchable' => false],
                ['title' => 'E-posta doğrulama', 'data' => 'email_verified_at', 'name' => 'email_verified_at', 'searchable' => false],
                ['title' => 'Son giriş', 'data' => 'last_login_at', 'name' => 'last_login_at', 'searchable' => false],
                ['title' => 'Son giriş IP', 'data' => 'last_login_ip', 'name' => 'last_login_ip'],
                ['title' => 'Oluşturulma tarihi', 'data' => 'created_at', 'name' => 'created_at'],
                ['title' => 'İşlemler', 'data' => 'actions', 'name' => 'actions', 'orderable' => false, 'searchable' => false],
            ]"
        />
    </div>
</x-admin.layout>
