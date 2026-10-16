<div class="d-flex gap-2">
    @can('services.categories.update')
        <a href="{{ route('admin.services.categories.edit', $category) }}" class="btn btn-sm btn-outline-primary">Düzenle</a>
    @endcan
    @can('services.categories.delete')
        <form method="POST" action="{{ route('admin.services.categories.destroy', $category) }}"
              data-confirm="Bu hizmet kategorisini silmek istediğinizden emin misiniz?">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger">Sil</button>
        </form>
    @endcan
</div>
