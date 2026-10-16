<x-admin.layout title="Menü öğesi ekle">
    <div class="card">
        <div class="card-header"><h2 class="card-title">{{ $menu->name }} — Yeni öğe</h2></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.menus.items.store', $menu) }}" data-menu-item-form>
                @include('menus::items.partials.form')
            </form>
        </div>
    </div>
</x-admin.layout>
