<x-admin.layout title="Gelen başvurular">
    <div class="card"><div class="card-header flex-wrap gap-2"><h2 class="card-title me-auto">Gelen başvurular</h2>
        <select class="form-select w-auto" data-table-filter="form_id" data-table-target="form-submissions-table" aria-label="Form filtresi"><option value="">Tüm formlar</option>@foreach($forms as $form)<option value="{{ $form->id }}" @selected((int)request('form_id')===$form->id)>{{ $form->name }}</option>@endforeach</select>
        <select class="form-select w-auto" data-table-filter="status" data-table-target="form-submissions-table" aria-label="Durum filtresi"><option value="">Tüm durumlar</option><option value="new">Yeni</option><option value="read">Okundu</option><option value="in_progress">İşleme alındı</option></select>
    </div>
    <x-admin.data-table id="form-submissions-table" :source="route('admin.forms.submissions.data')" :options="['order'=>[[5,'desc']]]" :columns="[
        ['title'=>'Form','data'=>'form_name','name'=>'form_name'],['title'=>'Ad soyad','data'=>'name','name'=>'name'],
        ['title'=>'E-posta','data'=>'email','name'=>'email'],['title'=>'Telefon','data'=>'phone','name'=>'phone'],
        ['title'=>'Durum','data'=>'status','name'=>'status','searchable'=>false],
        ['title'=>'Tarih','data'=>'created_at','name'=>'created_at'],['title'=>'İşlemler','data'=>'actions','orderable'=>false,'searchable'=>false],
    ]" /></div>
</x-admin.layout>