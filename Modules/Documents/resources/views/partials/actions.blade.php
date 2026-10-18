<div class="d-flex gap-2">
    @can('documents.update')
        <a href="{{ route('admin.documents.edit', $document) }}" class="btn btn-sm btn-outline-primary">Düzenle</a>
    @endcan
    @can('documents.delete')
        <form method="POST" action="{{ route('admin.documents.destroy', $document) }}" data-confirm="Bu belgeyi silmek istediğinizden emin misiniz?">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger">Sil</button>
        </form>
    @endcan
</div>
