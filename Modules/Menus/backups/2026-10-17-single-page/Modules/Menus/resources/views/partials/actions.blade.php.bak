<div class="d-flex gap-2 justify-content-end">
    @can('menus.update')
        <a href="{{ route('admin.menus.edit', $menu) }}" class="btn btn-sm btn-outline-primary">Düzenle</a>
    @endcan
    @can('menus.delete')
        <form method="POST" action="{{ route('admin.menus.destroy', $menu) }}" data-confirm="Bu menüyü ve tüm öğelerini silmek istediğinizden emin misiniz?">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger">Sil</button>
        </form>
    @endcan
</div>
