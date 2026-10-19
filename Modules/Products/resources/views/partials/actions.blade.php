<div class="d-flex gap-2">
    @can('products.update')
        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-outline-primary">Düzenle</a>
    @endcan
    @can('products.delete')
        <form method="POST" action="{{ route('admin.products.destroy', $product) }}"
              data-confirm="Bu ürüni silmek istediğinizden emin misiniz?">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger">Sil</button>
        </form>
    @endcan
</div>
