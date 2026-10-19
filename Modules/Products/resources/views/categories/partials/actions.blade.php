<div class="d-flex gap-2">
    @can('products.categories.update')
        <a href="{{ route('admin.products.categories.edit', $category) }}" class="btn btn-sm btn-outline-primary">Düzenle</a>
    @endcan
    @can('products.categories.delete')
        <form method="POST" action="{{ route('admin.products.categories.destroy', $category) }}"
              data-confirm="Bu ürün kategorisini silmek istediğinizden emin misiniz?">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger">Sil</button>
        </form>
    @endcan
</div>
