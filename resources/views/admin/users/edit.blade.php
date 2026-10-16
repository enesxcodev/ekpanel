<x-admin.layout title="Yönetici düzenle">
    <div class="card">
        <div class="card-header"><h2 class="card-title">{{ $admin->name }} {{ $admin->lastname }}</h2></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.users.update', $admin) }}">
                @include('admin.users.partials.form')
            </form>
        </div>
    </div>
</x-admin.layout>
