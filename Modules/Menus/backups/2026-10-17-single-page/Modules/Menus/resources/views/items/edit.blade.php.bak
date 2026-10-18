<x-admin.layout title="Menü öğesini düzenle">
    <div class="card">
        <div class="card-header"><h2 class="card-title">{{ $menu->name }} — {{ $item->label }}</h2></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.menus.items.update', [$menu, $item]) }}" data-menu-item-form>
                @include('menus::items.partials.form')
            </form>
        </div>
    </div>
</x-admin.layout>
