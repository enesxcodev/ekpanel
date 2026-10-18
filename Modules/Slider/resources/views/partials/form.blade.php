@csrf
@isset($slider) @method('PUT') @endisset
<x-admin.form.input name="title" label="Başlık" :value="$slider->title ?? null" required maxlength="255" />
<x-admin.form.textarea name="subtitle" label="Alt başlık" :value="$slider->subtitle ?? null" maxlength="10000" />
<div class="mb-3">
    <x-admin.media.picker name="image_media_id" label="Görsel *" :config="$mediaConfig" kind="image"
        :extensions="['jpg', 'jpeg', 'png', 'webp']" :value="old('image_media_id', $slider->image_media_id ?? null)"
        :preview-url="isset($slider) ? $slider->image_url : null" :disabled="!auth('admin')->user()->can('media.view')" />
    <div class="form-hint">Görsel zorunludur. JPG, PNG veya WebP; en fazla 4 MB. Seçimi değiştirmek dosyayı kütüphaneden silmez.</div>
    @error('image_media_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    @cannot('media.view')<div class="form-hint">Görsel seçmek için medya görüntüleme izni gerekir.</div>@endcannot
</div>
<x-admin.form.boolean name="is_active" label="Aktif" :value="$slider->is_active ?? true" />
<x-admin.features.fields :form="$featureForm" :fields="$featureFields" :media-config="$mediaConfig" />
<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary">{{ isset($slider) ? 'Değişiklikleri kaydet' : 'Slider ekle' }}</button>
    <a href="{{ route('admin.slider.index') }}" class="btn btn-link">Vazgeç</a>
</div>
