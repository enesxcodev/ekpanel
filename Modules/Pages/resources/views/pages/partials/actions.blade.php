<div class="d-flex gap-2">
    @can('pages.update')
        <a href="{{ route('admin.pages.edit', $page) }}" class="btn btn-sm btn-outline-primary">Düzenle</a>
    @endcan
    @can('pages.delete')
        <form method="POST" action="{{ route('admin.pages.destroy', $page) }}" data-confirm="Bu sayfayı silmek istediğinizden emin misiniz?">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger">Sil</button>
        </form>
    @endcan
</div>
