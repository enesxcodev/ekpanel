<div class="d-flex gap-2 justify-content-end">
    @can('testimonials.update')
        <a href="{{ route('admin.testimonials.edit', $testimonial) }}" class="btn btn-sm btn-outline-primary">Düzenle</a>
    @endcan
    @can('testimonials.delete')
        <form method="POST" action="{{ route('admin.testimonials.destroy', $testimonial) }}" data-confirm="Bu müşteri yorumunu silmek istediğinizden emin misiniz?">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger">Sil</button>
        </form>
    @endcan
</div>
