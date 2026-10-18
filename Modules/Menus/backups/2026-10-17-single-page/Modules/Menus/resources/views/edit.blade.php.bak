<x-admin.layout title="Menüyü düzenle">
    <div class="card mb-4">
        <div class="card-header"><h2 class="card-title">{{ $menu->name }}</h2></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.menus.update', $menu) }}">
                @include('menus::partials.form')
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-header d-flex gap-3 align-items-center">
            <div class="me-auto">
                <h2 class="card-title">Menü öğeleri</h2>
                <p class="text-secondary mb-0 mt-1">Öğeyi düzenleyerek üst öğesini ve aynı seviyedeki sırasını değiştirebilirsiniz.</p>
            </div>
            @can('menus.update')
                <a href="{{ route('admin.menus.items.create', $menu) }}" class="btn btn-primary">Öğe ekle</a>
            @endcan
        </div>
        <div class="card-body">
            @if (count($tree) > 0)
                @include('menus::partials.tree', ['nodes' => $tree])
            @else
                <div class="empty">
                    <p class="empty-title">Henüz menü öğesi yok</p>
                    <p class="empty-subtitle text-secondary">Bağlantı veya grup ekleyerek menünüzü oluşturun.</p>
                </div>
            @endif
        </div>
    </div>
</x-admin.layout>
