@csrf
@isset($sector) @method('PUT') @endisset
<x-admin.form.file name="image" label="Sektör görseli" :current-url="isset($sector) && $sector->image ? Storage::disk('public')->url($sector->image) : null" :is-image="true" remove-name="remove_image" accept=".jpg,.jpeg,.png,.webp" help="JPG, PNG veya WebP; en fazla 4 MB." />
<x-admin.form.input name="title" label="Başlık" :value="$sector->title ?? null" required maxlength="255" />
<x-admin.form.textarea name="short_description" label="Kısa içerik" :value="$sector->short_description ?? null" rows="3" maxlength="1000" />
<x-admin.form.textarea name="content" label="İçerik" :value="$sector->content ?? null" rows="10" maxlength="65535" />
<fieldset class="mt-4">
    <legend class="h3">SEO ayarları</legend>
    <x-admin.form.input name="meta_title" label="Meta başlık" :value="$sector->meta_title ?? null" maxlength="255" />
    <x-admin.form.input name="meta_keywords" label="Meta anahtar" :value="$sector->meta_keywords ?? null" maxlength="1000" />
    <x-admin.form.textarea name="meta_description" label="Meta açıklama" :value="$sector->meta_description ?? null" rows="3" maxlength="1000" />
</fieldset>
<x-admin.form.boolean name="is_active" label="Durum" :value="$sector->is_active ?? true" />
<x-admin.features.fields :form="$featureForm" :fields="$featureFields" />
<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary">{{ isset($sector) ? 'Değişiklikleri kaydet' : 'Sektör ekle' }}</button>
    <a href="{{ route('admin.sector.index') }}" class="btn btn-link">Vazgeç</a>
</div>
