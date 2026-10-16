<div class="d-flex gap-2 justify-content-end">
    @can('sector.update')
        <a href="{{ route('admin.sector.edit', $sector) }}" class="btn btn-sm btn-outline-primary">Düzenle</a>
    @endcan
    @can('sector.delete')
        <form method="POST" action="{{ route('admin.sector.destroy', $sector) }}" data-confirm="Bu sektörü silmek istediğinizden emin misiniz?">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger">Sil</button>
        </form>
    @endcan
</div>
