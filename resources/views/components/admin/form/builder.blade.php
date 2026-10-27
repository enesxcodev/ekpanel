@props(['features' => false])
<div {{ $attributes->class(['card fb-workspace']) }}>
    <section class="fb-edit">
        <div class="d-flex gap-2 align-items-start mb-3"><div class="me-auto"><h2 class="card-title">Form alanları</h2><p class="text-secondary small mb-0">Alan ekleyin, düzenleyin ve sıralayın.</p></div><button type="button" class="btn btn-primary" data-field-add>+ Alan ekle</button></div>
        @if (!$features)
        <div class="d-flex gap-2 flex-wrap mb-3"><span class="text-secondary small align-self-center">Hızlı başlangıç:</span><button type="button" class="btn btn-sm" data-form-preset="contact">İletişim</button><button type="button" class="btn btn-sm" data-form-preset="callback">Sizi arayalım</button><button type="button" class="btn btn-sm" data-form-preset="application">Bize katıl</button></div>
        @endif
        <div data-field-list class="fb-field-list"></div>
        <div class="alert alert-info mt-3 small">Alanları sürükleyerek veya sıra butonlarıyla taşıyabilirsiniz.</div>
        <div data-field-editor hidden>
            <h3 class="h4 border-top pt-3">Seçili alanın ayarları</h3>
            <div class="row g-3">
                <div class="col-sm-6"><label class="form-label">Etiket<input class="form-control" data-field-prop="label" maxlength="150"></label></div>
                <div class="col-sm-6"><label class="form-label">Alan anahtarı<input class="form-control" data-field-prop="key" maxlength="100"></label></div>
                <div class="col-sm-6"><label class="form-label">Alan türü<select class="form-select" data-field-prop="input_type"><option value="text">Metin</option><option value="email">E-posta</option>@unless($features)<option value="tel">Telefon</option>@endunless<option value="textarea">Uzun metin</option><option value="number">Sayı</option><option value="select">Seçim listesi</option><option value="radio">Radio grubu</option><option value="boolean">Onay kutusu</option><option value="file">Dosya</option>@if($features)<option value="image">Görsel</option><option value="range">Aralık</option>@endif</select></label></div>
                @unless($features)<div class="col-sm-6"><label class="form-label">Genişlik<select class="form-select" data-field-prop="width"><option value="full">Tam genişlik</option><option value="half">Yarım genişlik</option></select></label></div>@endunless
                <div class="col-12"><label class="form-check"><input type="checkbox" class="form-check-input" data-field-prop="{{ $features ? 'validation.required' : 'required' }}"><span class="form-check-label">Zorunlu alan</span></label></div>
                <div class="col-12"><label class="form-label">Placeholder<input class="form-control" data-field-prop="{{ $features ? 'attributes.placeholder' : 'placeholder' }}" maxlength="255"></label></div>
                <div class="col-12"><label class="form-label">Açıklama<input class="form-control" data-field-prop="description" maxlength="{{ $features ? 1000 : 500 }}"></label></div>
                @unless($features)<div class="col-sm-6"><label class="form-label">Alan CSS sınıfları<input class="form-control" data-field-prop="css_class" maxlength="255"></label></div>
                <div class="col-sm-6"><label class="form-label">Input CSS sınıfları<input class="form-control" data-field-prop="input_class" maxlength="255"></label></div>
                <div class="col-12"><label class="form-label">Listede iletişim bilgisi eşleşmesi<select class="form-select" data-field-prop="role"><option value="">Detayda göster</option><option value="name">Ad soyad</option><option value="email">E-posta</option><option value="phone">Telefon</option></select></label></div>
                @endunless
                <div class="col-12" data-options-setting><label class="form-label">Seçenekler<textarea class="form-control" rows="4" data-field-options placeholder="deger|Görünen etiket (her satıra bir seçenek)"></textarea></label></div>
                <div class="col-12" data-length-setting><label class="form-label">En fazla karakter<input class="form-control" type="number" min="1" max="{{ $features ? 1000000000 : 10000 }}" data-field-prop="{{ $features ? 'validation.max' : 'max_length' }}"></label></div>
                <div class="col-sm-6" data-file-setting><label class="form-label">Dosya sınırı (KB)<input class="form-control" type="number" min="1" max="{{ $features ? 51200 : 10240 }}" data-field-prop="{{ $features ? 'validation.max_size' : 'max_size' }}"></label></div>
                <div class="col-sm-6" data-file-setting><label class="form-label">Dosya uzantıları<input class="form-control" data-field-prop="{{ $features ? 'validation.extensions' : 'extensions' }}" @if($features) data-field-list-value @endif placeholder="pdf,docx"></label></div>
                @if($features)
                    <div class="col-sm-6"><label class="form-label">Değer türü<select class="form-select" data-field-prop="value_type"><option value="string">Metin</option><option value="integer">Tam sayı</option><option value="float">Ondalıklı sayı</option><option value="boolean">Açık / kapalı</option><option value="file">Dosya yükleme</option><option value="media">Medya kütüphanesi</option></select></label></div>
                    <div class="col-sm-6" data-default-setting><label class="form-label">Varsayılan değer<input class="form-control" data-field-prop="default_value" data-field-default><small class="form-hint">Boş: değer yok. Açık / kapalı: true veya false.</small></label></div>
                    @foreach(['min' => 'Minimum değer / uzunluk', 'max' => 'Maksimum değer / uzunluk'] as $bound => $label)
                        <div class="col-sm-6" data-bound-setting><label class="form-label">{{ $label }}<input class="form-control" type="number" step="any" data-field-prop="validation.{{ $bound }}"></label></div>
                    @endforeach
                    <div class="col-sm-6" data-number-setting><label class="form-label">Adım<input class="form-control" type="number" step="any" min="0.000001" data-field-prop="attributes.step"></label></div>
                    <div class="col-sm-6" data-textarea-setting><label class="form-label">Satır sayısı<input class="form-control" type="number" min="2" max="20" data-field-prop="attributes.rows"></label></div>
                    <div class="col-sm-6" data-radio-setting><label class="form-label">Yerleşim<select class="form-select" data-field-prop="attributes.layout"><option value="">Varsayılan</option><option value="vertical">Dikey</option><option value="horizontal">Yatay</option></select></label></div>
                    <div class="col-sm-6" data-radio-setting><label class="form-label">Görünüm<select class="form-select" data-field-prop="attributes.variant"><option value="">Varsayılan</option><option value="standard">Standart</option><option value="buttons">Butonlar</option></select></label></div>
                    @foreach(['min_width' => 'Minimum genişlik', 'max_width' => 'Maksimum genişlik', 'min_height' => 'Minimum yükseklik', 'max_height' => 'Maksimum yükseklik'] as $bound => $label)
                        <div class="col-sm-6" data-image-setting><label class="form-label">{{ $label }} (px)<input class="form-control" type="number" min="1" max="10000" data-field-prop="validation.{{ $bound }}"></label></div>
                    @endforeach
                @endif
            </div>
        </div>
        @unless($features)<details class="mt-4"><summary>Mevcut HTML formundan alanları al</summary><p class="text-secondary small mt-2">HTML yalnız alan tanımlarını okumak için kullanılır. Scriptler ve gönderim adresi alınmaz. İçe aldıktan sonra alanları kontrol edin.</p><textarea class="form-control" rows="5" data-import-html aria-label="İçe alınacak HTML"></textarea><button type="button" class="btn mt-2" data-import-fields>Alanları içeri al</button><div class="small mt-2" role="status" data-import-message></div></details>@endunless
    </section>
    <section class="fb-preview"><h2 class="card-title">Canlı önizleme</h2><p class="text-secondary small">{{ $features ? 'Ek alanların sırasını ve türlerini önizleyin.' : 'Ziyaretçi formunun yerleşimini görün. Renkler ve sınıflar temanızda özelleştirilebilir.' }}</p><div class="fb-preview-canvas"><div data-form-preview class="ek-form"></div></div></section>
</div>
