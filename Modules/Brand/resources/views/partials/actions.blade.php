<div class="d-flex gap-2 justify-content-end">
    @can('brand.update')
        <a href="{{ route('admin.brand.edit', $brand) }}" class="btn btn-sm btn-outline-primary">Düzenle</a>
    @endcan
    @can('brand.delete')
        <form method="POST" action="{{ route('admin.brand.destroy', $brand) }}" data-confirm="Bu markayı silmek istediğinizden emin misiniz?">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger">Sil</button>
        </form>
    @endcan
</div>
