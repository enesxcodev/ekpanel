<div class="d-flex gap-2">
    @can('galeriler.categories.update')<a href="{{ route('admin.galeriler.categories.edit', $category) }}" class="btn btn-sm btn-outline-primary">Düzenle</a>@endcan
    @can('galeriler.categories.delete')
        <form method="POST" action="{{ route('admin.galeriler.categories.destroy', $category) }}" data-confirm="Kategoriyi silmek istediğinizden emin misiniz?">
            @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Sil</button>
        </form>
    @endcan
</div>