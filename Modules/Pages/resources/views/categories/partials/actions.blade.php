<div class="d-flex gap-2">
    @can('pages.categories.update')
        <a href="{{ route('admin.pages.categories.edit', $category) }}" class="btn btn-sm btn-outline-primary">Düzenle</a>
    @endcan
    @can('pages.categories.delete')
        <form method="POST" action="{{ route('admin.pages.categories.destroy', $category) }}" data-confirm="Bu sayfa kategorisini silmek istediğinizden emin misiniz? İçinde sayfa veya alt kategori bulunmamalıdır.">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger">Sil</button>
        </form>
    @endcan
</div>
