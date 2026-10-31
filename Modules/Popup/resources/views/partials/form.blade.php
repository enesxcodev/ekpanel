@csrf
<input type="hidden" name="lang_id" value="{{ old('lang_id', $selectedLanguage?->id) }}">
@isset($popup) @method('PUT') @endisset
@if (! $selectedLanguage)<div class="alert alert-warning">Önce <a href="{{ route('admin.settings.index', ['section' => 'languages']) }}">Ayarlar üzerinden aktif dil ekleyin.</a></div>@endif
<fieldset @disabled(! $selectedLanguage)>
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <p class="text-secondary mb-0">Başlık ve içerik dile özeldir. Yayın ve gösterim ayarları tüm dillerde ortaktır.</p>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.popup.index') }}" class="btn">Vazgeç</a>
            <button type="submit" class="btn btn-primary">{{ isset($popup) ? 'Değişiklikleri kaydet' : 'Popup ekle' }}</button>
        </div>
    </div>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card"><div class="card-body">
                @if ($selectedLanguage)
                    <x-admin.form.input name="title" label="Başlık" :value="$translation?->title" required maxlength="255" />
                    <x-admin.form.textarea name="content" label="İçerik" :value="$translation?->content" required maxlength="100000" rows="8" />
                    <x-admin.features.fields :form="$featureForm" :fields="$featureFields" :media-config="$mediaConfig" />
                @endif
            </div></div>
        </div>
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header"><h3 class="card-title">Diller</h3></div>
                <div class="list-group list-group-flush">
                    @foreach ($languages as $language)
                        @php($details = config('languages.catalog.'.$language->code))
                        <a href="{{ request()->fullUrlWithQuery(['lang' => $language->code]) }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 {{ $selectedLanguage?->id === $language->id ? 'active' : '' }}">
                            <span class="flag flag-country-{{ $details['flag'] ?? '' }}" aria-hidden="true"></span>
                            <span>{{ $details['name'] ?? $language->code }}<small class="d-block">{{ in_array($language->id, $translatedLanguageIds, true) ? 'Çeviri mevcut' : 'Henüz eklenmedi' }}</small></span>
                        </a>
                    @endforeach
                </div>
            </div>
            <div class="card mb-4"><div class="card-body">
                <h3 class="card-title">Ortak yayın ayarları</h3>
                <x-admin.media.picker name="image_media_id" label="Görsel" :config="$mediaConfig" kind="image"
                    :extensions="['jpg', 'jpeg', 'png', 'webp']" :value="old('image_media_id', $popup->image_media_id ?? null)"
                    :preview-url="isset($popup) ? $popup->image_url : null" :disabled="!auth('admin')->user()->can('media.view')" />
                <div class="form-hint mb-3">İsteğe bağlı görsel; tüm dillerde ortaktır.</div>
                @error('image_media_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                @cannot('media.view')<div class="form-hint">Görsel seçmek için medya görüntüleme izni gerekir.</div>@endcannot
                <x-admin.form.input name="starts_at" label="Başlangıç" type="datetime-local" :value="old('starts_at', isset($popup) ? $popup->starts_at?->format('Y-m-d\\TH:i') : null)" required />
                <x-admin.form.input name="ends_at" label="Bitiş" type="datetime-local" :value="old('ends_at', isset($popup) ? $popup->ends_at?->format('Y-m-d\\TH:i') : null)" required />
                <div class="form-hint mb-3">Saat dilimi: {{ config('app.timezone') }}. Başlangıç dahildir; bitiş anında yayın sona erer.</div>
                <x-admin.form.select name="repeat_policy" label="Tekrar gösterim politikası" :value="old('repeat_policy', $popup->repeat_policy ?? 'never_after_close')" :placeholder="false" :options="[['value' => 'never_after_close', 'label' => 'Kapatıldıktan sonra tekrar gösterme'], ['value' => 'interval', 'label' => 'Belirlenen aralıkla tekrar göster']]" required />
                <x-admin.form.input name="repeat_interval" label="Tekrar aralığı" type="number" :value="old('repeat_interval', $popup->repeat_interval ?? null)" min="1" max="36500" />
                <x-admin.form.select name="repeat_unit" label="Aralık birimi" :value="old('repeat_unit', $popup->repeat_unit ?? 'hours')" :placeholder="false" :options="[['value' => 'hours', 'label' => 'Saat'], ['value' => 'days', 'label' => 'Gün']]" />
                <div class="form-hint mb-3">Aralıklı gösterimde sayı ve birim zorunludur. Süre, son gösterim veya kapatmanın daha yenisinden başlar; bir gün 24 saattir.</div>
                <x-admin.form.boolean name="is_active" label="Aktif" :value="old('is_active', $popup->is_active ?? true)" />
            </div></div>
        </div>
    </div>
</fieldset>
