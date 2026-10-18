<div class="d-flex gap-2">
    @can('projects.categories.update')<a href="{{ route('admin.projects.categories.edit', $category) }}" class="btn btn-sm btn-outline-primary">Düzenle</a>@endcan
    @can('projects.categories.delete')
        <form method="POST" action="{{ route('admin.projects.categories.destroy', $category) }}" data-confirm="Kategoriyi silmek istediğinizden emin misiniz?">
            @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Sil</button>
        </form>
    @endcan
</div>