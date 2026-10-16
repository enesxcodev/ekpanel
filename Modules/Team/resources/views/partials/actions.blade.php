<div class="d-flex gap-2 justify-content-end">
    @can('team.update')
        <a href="{{ route('admin.team.edit', $member) }}" class="btn btn-sm btn-outline-primary">Düzenle</a>
    @endcan
    @can('team.delete')
        <form method="POST" action="{{ route('admin.team.destroy', $member) }}" data-confirm="Bu ekip üyesini silmek istediğinizden emin misiniz?">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger">Sil</button>
        </form>
    @endcan
</div>
