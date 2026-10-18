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
                <div class="card fb-workspace">
                    <section class="fb-edit">
                        <div class="d-flex gap-2 align-items-start mb-3"><div class="me-auto"><h2 class="card-title">Form alanları</h2><p class="text-secondary small mb-0">Alan ekleyin, düzenleyin ve sıralayın.</p></div><button type="button" class="btn btn-primary" data-field-add>+ Alan ekle</button></div>
                        <div class="d-flex gap-2 flex-wrap mb-3"><span class="text-secondary small align-self-center">Hızlı başlangıç:</span><button type="button" class="btn btn-sm" data-form-preset="contact">İletişim</button><button type="button" class="btn btn-sm" data-form-preset="callback">Sizi arayalım</button><button type="button" class="btn btn-sm" data-form-preset="application">Bize katıl</button></div>
                        <div data-field-list class="fb-field-list"></div>
                        <div class="alert alert-info mt-3 small">Alanları sürükleyerek veya sıra butonlarıyla taşıyabilirsiniz.</div>
                        <div data-field-editor hidden>
                            <h3 class="h4 border-top pt-3">Seçili alanın ayarları</h3>
                            <div class="row g-3">
                                <div class="col-sm-6"><label class="form-label">Etiket<input class="form-control" data-field-prop="label" maxlength="150"></label></div>
                                <div class="col-sm-6"><label class="form-label">Alan anahtarı<input class="form-control" data-field-prop="key" maxlength="100"></label></div>
                                <div class="col-sm-6"><label class="form-label">Alan türü<select class="form-select" data-field-prop="input_type"><option value="text">Metin</option><option value="email">E-posta</option><option value="tel">Telefon</option><option value="textarea">Uzun metin</option><option value="number">Sayı</option><option value="select">Seçim listesi</option><option value="radio">Radio grubu</option><option value="boolean">Onay kutusu</option><option value="file">Dosya</option></select></label></div>
                                <div class="col-sm-6"><label class="form-label">Genişlik<select class="form-select" data-field-prop="width"><option value="full">Tam genişlik</option><option value="half">Yarım genişlik</option></select></label></div>
                                <div class="col-12"><label class="form-check"><input type="checkbox" class="form-check-input" data-field-prop="required"><span class="form-check-label">Zorunlu alan</span></label></div>
                                <div class="col-12"><label class="form-label">Placeholder<input class="form-control" data-field-prop="placeholder" maxlength="255"></label></div>
                                <div class="col-12"><label class="form-label">Açıklama<input class="form-control" data-field-prop="description" maxlength="500"></label></div>
                                <div class="col-sm-6"><label class="form-label">Alan CSS sınıfları<input class="form-control" data-field-prop="css_class" maxlength="255"></label></div>
                                <div class="col-sm-6"><label class="form-label">Input CSS sınıfları<input class="form-control" data-field-prop="input_class" maxlength="255"></label></div>
                                <div class="col-12"><label class="form-label">Listede iletişim bilgisi eşleşmesi<select class="form-select" data-field-prop="role"><option value="">Detayda göster</option><option value="name">Ad soyad</option><option value="email">E-posta</option><option value="phone">Telefon</option></select></label></div>
                                <div class="col-12" data-options-setting><label class="form-label">Seçenekler<textarea class="form-control" rows="4" data-field-options placeholder="deger|Görünen etiket (her satıra bir seçenek)"></textarea></label></div>
                                <div class="col-12" data-length-setting><label class="form-label">En fazla karakter<input class="form-control" type="number" min="1" max="10000" data-field-prop="max_length"></label></div>
                                <div class="col-sm-6" data-file-setting><label class="form-label">Dosya sınırı (KB)<input class="form-control" type="number" min="1" max="10240" data-field-prop="max_size"></label></div>
                                <div class="col-sm-6" data-file-setting><label class="form-label">Dosya uzantıları<input class="form-control" data-field-prop="extensions" placeholder="pdf,docx"></label></div>
                            </div>
                        </div>
                        <details class="mt-4"><summary>Mevcut HTML formundan alanları al</summary><p class="text-secondary small mt-2">HTML yalnız alan tanımlarını okumak için kullanılır. Scriptler ve gönderim adresi alınmaz. İçe aldıktan sonra alanları kontrol edin.</p><textarea class="form-control" rows="5" data-import-html aria-label="İçe alınacak HTML"></textarea><button type="button" class="btn mt-2" data-import-fields>Alanları içeri al</button><div class="small mt-2" role="status" data-import-message></div></details>
                    </section>
                    <section class="fb-preview"><h2 class="card-title">Canlı önizleme</h2><p class="text-secondary small">Ziyaretçi formunun yerleşimini görün. Renkler ve sınıflar temanızda özelleştirilebilir.</p><div class="fb-preview-canvas"><div data-form-preview class="ek-form"></div></div></section>
                </div>
            </div>
        </div>
        <div class="card card-body mt-3 d-flex flex-row justify-content-between"><a class="btn" href="{{ route('admin.forms.index') }}">Vazgeç</a><button type="submit" class="btn btn-primary">Değişiklikleri kaydet</button></div>
    </form>
</x-admin.layout>
