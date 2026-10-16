@csrf
@isset($slider) @method('PUT') @endisset
<x-admin.form.input name="title" label="Başlık" :value="$slider->title ?? null" required maxlength="255" />
<x-admin.form.textarea name="subtitle" label="Alt başlık" :value="$slider->subtitle ?? null" maxlength="10000" />
<x-admin.form.file name="image" label="Görsel" :current-url="isset($slider) ? Storage::disk('public')->url($slider->image) : null" :is-image="true" required accept=".jpg,.jpeg,.png,.webp" help="JPG, PNG veya WebP; en fazla 4 MB." />
<x-admin.features.fields :form="$featureForm" :fields="$featureFields" />
<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary">{{ isset($slider) ? 'Değişiklikleri kaydet' : 'Slider ekle' }}</button>
    <a href="{{ route('admin.slider.index') }}" class="btn btn-link">Vazgeç</a>
</div>
