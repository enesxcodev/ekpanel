<x-admin.layout title="Menü ekle">
    <div class="card">
        <div class="card-header"><h2 class="card-title">Yeni menü</h2></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.menus.store') }}">
                @include('menus::partials.form')
            </form>
        </div>
    </div>
</x-admin.layout>
