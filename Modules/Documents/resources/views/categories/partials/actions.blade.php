<div class="d-flex gap-2">
    @can('documents.categories.update')
        <a href="{{ route('admin.documents.categories.edit', $category) }}" class="btn btn-sm btn-outline-primary">Düzenle</a>
    @endcan
    @can('documents.categories.delete')
        <form method="POST" action="{{ route('admin.documents.categories.destroy', $category) }}" data-confirm="Bu kategoriyi silmek istediğinizden emin misiniz? Belgeler kategorisiz kalır.">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger">Sil</button>
        </form>
    @endcan
</div>
