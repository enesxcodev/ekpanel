<div class="d-flex gap-2">
    @can('services.update')
        <a href="{{ route('admin.services.edit', $service) }}" class="btn btn-sm btn-outline-primary">Düzenle</a>
    @endcan
    @can('services.delete')
        <form method="POST" action="{{ route('admin.services.destroy', $service) }}"
              data-confirm="Bu hizmeti silmek istediğinizden emin misiniz?">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger">Sil</button>
        </form>
    @endcan
</div>
