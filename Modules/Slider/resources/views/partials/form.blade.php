@csrf
<input type="hidden" name="lang_id" value="{{ old('lang_id', $selectedLanguage?->id) }}">
@isset($slider) @method('PUT') @endisset
@if (! $selectedLanguage)<div class="alert alert-warning">Önce <a href="{{ route('admin.settings.index', ['section' => 'languages']) }}">Ayarlar üzerinden aktif dil ekleyin.</a></div>@endif
<fieldset @disabled(! $selectedLanguage)>
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <p class="text-secondary mb-0">Başlık, alt başlık ve ek alanlar dile özeldir. Görsel ve aktiflik tüm dillerde ortaktır.</p>
        <div class="d-flex gap-2"><a href="{{ route('admin.slider.index') }}" class="btn">Vazgeç</a><button type="submit" class="btn btn-primary">{{ isset($slider) ? 'Değişiklikleri kaydet' : 'Slider ekle' }}</button></div>
    </div>
    <div class="row g-4">
        <div class="col-lg-8"><div class="card"><div class="card-body">
            @if ($selectedLanguage)
                <x-admin.form.input name="title" label="Başlık" :value="$translation?->title" required maxlength="255" />
                <x-admin.form.textarea name="subtitle" label="Alt başlık" :value="$translation?->subtitle" maxlength="10000" />
                <x-admin.features.fields :form="$featureForm" :fields="$featureFields" :media-config="$mediaConfig" />
            @endif
        </div></div></div>
        <div class="col-lg-4">
            <div class="card mb-4"><div class="card-header"><h3 class="card-title">Diller</h3></div><div class="list-group list-group-flush">
                @foreach ($languages as $language)
                    @php($details = config('languages.catalog.'.$language->code))
                    <a href="{{ request()->fullUrlWithQuery(['lang' => $language->code]) }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 {{ $selectedLanguage?->id === $language->id ? 'active' : '' }}">
                        <span class="flag flag-country-{{ $details['flag'] ?? '' }}" aria-hidden="true"></span><span>{{ $details['name'] ?? $language->code }}<small class="d-block">{{ in_array($language->id, $translatedLanguageIds, true) ? 'Çeviri mevcut' : 'Henüz eklenmedi' }}</small></span>
                    </a>
                @endforeach
            </div></div>
            <div class="card mb-4"><div class="card-body">
                <h3 class="card-title">Ortak slider ayarları</h3>
                <x-admin.media.picker name="image_media_id" label="Görsel *" :config="$mediaConfig" kind="image" :extensions="['jpg', 'jpeg', 'png', 'webp']" :value="old('image_media_id', $slider->image_media_id ?? null)" :preview-url="isset($slider) ? $slider->image_url : null" :disabled="!auth('admin')->user()->can('media.view')" />
                <div class="form-hint mb-3">Görsel zorunludur. JPG, PNG veya WebP; en fazla 4 MB.</div>
                @error('image_media_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                @cannot('media.view')<div class="form-hint">Görsel seçmek için medya görüntüleme izni gerekir.</div>@endcannot
                <x-admin.form.boolean name="is_active" label="Aktif" :value="old('is_active', $slider->is_active ?? true)" />
            </div></div>
        </div>
    </div>
</fieldset>
