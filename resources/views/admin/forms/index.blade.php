<x-admin.layout title="Form yönetimi">
    <div class="card">
        <div class="card-header gap-2"><div class="me-auto"><h2 class="card-title">Formlar</h2><div class="text-secondary small">Sitenizin formlarını ve alanlarını tek yerden yönetin.</div></div>
            @can('forms.submissions.view')<a href="{{ route('admin.forms.submissions.index') }}" class="btn">Gelen başvurular</a>@endcan
            @can('forms.create')<a href="{{ route('admin.forms.create') }}" class="btn btn-primary">Form oluştur</a>@endcan
        </div>
        @if($errors->any())<div class="alert alert-danger m-3">{{ $errors->first() }}</div>@endif
        <x-admin.data-table id="site-forms-table" :source="route('admin.forms.data')" :columns="[
            ['title'=>'Form adı','data'=>'name','name'=>'name'],
            ['title'=>'Anahtar','data'=>'key','name'=>'key'],
            ['title'=>'Alan','data'=>'fields_count','searchable'=>false],
            ['title'=>'Başvuru','data'=>'submissions_count','searchable'=>false],
            ['title'=>'Durum','data'=>'is_active','name'=>'is_active','searchable'=>false],
            ['title'=>'İşlemler','data'=>'actions','orderable'=>false,'searchable'=>false],
        ]" />
    </div>
</x-admin.layout>