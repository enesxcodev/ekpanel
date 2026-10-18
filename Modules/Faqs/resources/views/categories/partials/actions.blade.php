<div class="d-flex gap-2">
    @can('faqs.categories.update')
        <a href="{{ route('admin.faqs.categories.edit', $category) }}" class="btn btn-sm btn-outline-primary">Düzenle</a>
    @endcan
    @can('faqs.categories.delete')
        <form method="POST" action="{{ route('admin.faqs.categories.destroy', $category) }}" data-confirm="Bu kategoriyi silmek istediğinizden emin misiniz? Sorular kategorisiz kalır.">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger">Sil</button>
        </form>
    @endcan
</div>
