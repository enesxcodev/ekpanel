<x-admin.layout :title="$submission->form_name.' başvurusu'">
    <div class="card"><div class="card-header"><h2 class="card-title">{{ $submission->created_at->format('d.m.Y H:i') }}</h2></div><div class="card-body">
        @if($submission->related_content)
            <p>İlgili ürün: <strong>{{ $submission->related_content['title'] }}</strong> ({{ $submission->related_content['slug'] }}, #{{ $submission->related_content['id'] }})</p>
        @endif
        <dl class="row">@foreach($submission->answers as $key=>$answer)
            <dt class="col-md-3 mb-3">{{ $answer['label'] }}</dt><dd class="col-md-9 mb-3">
                @if($answer['type']==='file' && is_array($answer['value']))<a href="{{ route('admin.forms.submissions.download',[$submission,$key]) }}">{{ $answer['value']['name'] }}</a>
                @elseif($answer['type']==='boolean'){{ $answer['value']?'Evet':'Hayır' }}
                @else<span class="text-break" style="white-space:pre-wrap">{{ $answer['display'] ?? $answer['value'] ?? '—' }}</span>@endif
            </dd>
        @endforeach</dl>
        @can('forms.submissions.update')<form method="POST" action="{{ route('admin.forms.submissions.update',$submission) }}" class="d-flex gap-2">@csrf @method('PATCH')
            <select class="form-select w-auto" name="status" aria-label="Başvuru durumu">@foreach(['new'=>'Yeni','read'=>'Okundu','in_progress'=>'İşleme alındı'] as $value=>$label)<option value="{{ $value }}" @selected($submission->status===$value)>{{ $label }}</option>@endforeach</select><button class="btn btn-primary">Durumu kaydet</button></form>@endcan
    </div><div class="card-footer d-flex gap-2"><a href="{{ route('admin.forms.submissions.index') }}" class="btn">Listeye dön</a>@can('forms.submissions.delete')<form class="ms-auto" method="POST" action="{{ route('admin.forms.submissions.destroy',$submission) }}" data-confirm-modal data-confirm-title="Başvuruyu sil" data-confirm-message="Başvuru ve eklerini silmek istediğinizden emin misiniz?" data-confirm-button="Sil">@csrf @method('DELETE')<button class="btn btn-outline-danger">Başvuruyu sil</button></form>@endcan</div></div>
</x-admin.layout>
