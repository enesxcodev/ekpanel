<div class="d-flex gap-2 justify-content-end">
    <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#user-details-{{ $user->id }}">Görüntüle</button>
    @can('users.update')
        <a href="{{ route('admin.web-users.edit', $user) }}" class="btn btn-sm btn-outline-primary">Düzenle</a>
    @endcan
    @can('users.delete')
        <form method="POST" action="{{ route('admin.web-users.destroy', $user) }}" data-confirm="Bu kullanıcıyı silmek istediğinizden emin misiniz?">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger">Sil</button>
        </form>
    @endcan
</div>
@include('users::partials.details')
