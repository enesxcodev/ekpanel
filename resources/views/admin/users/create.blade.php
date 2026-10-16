<x-admin.layout title="Yönetici ekle">
    <div class="card">
        <div class="card-header"><h2 class="card-title">Yeni yönetici hesabı</h2></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.users.store') }}">
                @include('admin.users.partials.form')
            </form>
        </div>
    </div>
</x-admin.layout>
