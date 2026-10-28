<x-admin.layout :title="$form ? $form->name.' formunu düzenle' : 'Form oluştur'" :fluid="true">
    <p class="text-secondary">Form alanlarını düzenleyin, ayarlarını yapılandırın ve önizleyin.</p>
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>@endif
    <form method="POST" action="{{ $form ? route('admin.forms.update',$form) : route('admin.forms.store') }}" data-form-builder data-fields="{{ json_encode(old('fields',$editorFields), JSON_UNESCAPED_UNICODE) }}" data-import-url="{{ route('admin.forms.import') }}">
        @csrf @if($form) @method('PUT') @endif
        <input type="hidden" name="fields" data-schema-value>
        <ul class="nav nav-tabs mb-3" role="tablist" data-form-tabs>
            <li class="nav-item"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#form-settings" role="tab">Form ayarları</button></li>
            <li class="nav-item"><button class="nav-link active" type="button" data-bs-toggle="tab" data-bs-target="#form-fields" role="tab">Alanlar</button></li>
            @if($form && auth('admin')->user()->can('forms.submissions.view'))<li class="nav-item"><a class="nav-link" href="{{ route('admin.forms.submissions.index',['form_id'=>$form->id]) }}">Gelen başvurular</a></li>@endif
        </ul>
        <div class="tab-content">
            <div class="tab-pane" id="form-settings" role="tabpanel">
                <div class="card card-body">
                    <div class="row">
                        <div class="col-md-6"><x-admin.form.input name="name" label="Form adı" :value="$form->name ?? null" required maxlength="255" /></div>
                        <div class="col-md-6"><x-admin.form.input name="key" label="Form anahtarı" :value="$form->key ?? null" required maxlength="100" :readonly="(bool)$form" help="Örnek: iletisim. Oluşturulduktan sonra değişmez." /></div>
                        <div class="col-md-6"><x-admin.form.input name="submit_label" label="Gönder butonu metni" :value="$form->submit_label ?? 'Gönder'" required maxlength="100" data-submit-label /></div>
                        <div class="col-md-6"><x-admin.form.input name="notification_email" label="Bildirim e-postası" type="email" :value="$form->notification_email ?? null" maxlength="255" help="Boş bırakılırsa başvuru yalnız panelde saklanır." /></div>
                        <div class="col-md-12"><x-admin.form.input name="success_message" label="Başarı mesajı" :value="$form->success_message ?? 'Başvurunuz alındı. Teşekkür ederiz.'" required maxlength="500" /></div>
                        <div class="col-md-6"><x-admin.form.input name="css_class" label="Form CSS sınıfları" :value="$form->css_class ?? null" maxlength="255" help="Temanızdaki sınıfları boşlukla ayırın." /></div>
                        <div class="col-md-6"><x-admin.form.select name="is_active" label="Durum" :value="(int)($form->is_active ?? false)" :options="[['value'=>'0','label'=>'Pasif'],['value'=>'1','label'=>'Aktif']]" :placeholder="false" required /></div>
                    </div>
                    @if($form)<div class="form-hint">Temada kullanım: <code>&lt;x-site-form key="{{ $form->key }}" /&gt;</code>. Özel şablon için <code>variant="contact"</code> kullanabilirsiniz.</div>@endif
                </div>
            </div>
            <div class="tab-pane active show" id="form-fields" role="tabpanel">
                <x-admin.form.builder />
            </div>
        </div>
        <div class="card card-body mt-3 d-flex flex-row justify-content-between"><a class="btn" href="{{ route('admin.forms.index') }}">Vazgeç</a><button type="submit" class="btn btn-primary">Değişiklikleri kaydet</button></div>
    </form>
</x-admin.layout>
