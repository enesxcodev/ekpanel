<div class="d-flex gap-2 justify-content-end">
    @can('popup.update')
        <a href="{{ route('admin.popup.edit', $popup) }}" class="btn btn-sm btn-outline-primary">Düzenle</a>
    @endcan
    @can('popup.delete')
        <form method="POST" action="{{ route('admin.popup.destroy', $popup) }}" data-confirm="Bu popup kaydını silmek istediğinizden emin misiniz?">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger">Sil</button>
        </form>
    @endcan
</div>
