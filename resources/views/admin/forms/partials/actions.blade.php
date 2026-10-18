<div class="d-flex gap-2">
@can('forms.update')<a class="btn btn-sm btn-outline-primary" href="{{ route('admin.forms.edit',$form) }}">Düzenle</a>@endcan
@can('forms.submissions.view')<a class="btn btn-sm" href="{{ route('admin.forms.submissions.index',['form_id'=>$form->id]) }}">Başvurular</a>@endcan
@can('forms.delete')<form method="POST" action="{{ route('admin.forms.destroy',$form) }}" data-confirm="Bu formu silmek istediğinizden emin misiniz?">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Sil</button></form>@endcan
</div>