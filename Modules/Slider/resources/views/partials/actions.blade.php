<div class="d-flex gap-2 justify-content-end">
    @can('slider.update')
        <a href="{{ route('admin.slider.edit', $slider) }}" class="btn btn-sm btn-outline-primary">Düzenle</a>
    @endcan
    @can('slider.delete')
        <form method="POST" action="{{ route('admin.slider.destroy', $slider) }}" data-confirm="Bu slider kaydını silmek istediğinizden emin misiniz?">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger">Sil</button>
        </form>
    @endcan
</div>
