<div class="d-flex gap-2">
    @can('projects.update')<a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-sm btn-outline-primary">Düzenle</a>@endcan
    @can('projects.delete')<form method="POST" action="{{ route('admin.projects.destroy', $project) }}" data-confirm="Projeyi silmek istediğinizden emin misiniz?">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Sil</button></form>@endcan
</div>