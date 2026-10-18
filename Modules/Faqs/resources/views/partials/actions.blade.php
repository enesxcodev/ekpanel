<div class="d-flex gap-2">
    @can('faqs.update')
        <a href="{{ route('admin.faqs.edit', $faq) }}" class="btn btn-sm btn-outline-primary">Düzenle</a>
    @endcan
    @can('faqs.delete')
        <form method="POST" action="{{ route('admin.faqs.destroy', $faq) }}" data-confirm="Bu soruyu silmek istediğinizden emin misiniz?">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger">Sil</button>
        </form>
    @endcan
</div>
