@csrf
@isset($brand) @method('PUT') @endisset
<x-admin.form.input name="name" label="Marka ismi" :value="$brand->name ?? null" required maxlength="255" />
<x-admin.media.field name="image_media_id" label="Marka görseli" :config="$mediaConfig"
    :value="$brand->image_media_id ?? null" :preview-url="isset($brand) ? $brand->image_url : null" remove-name="remove_image" required />
<x-admin.form.textarea name="short_description" label="Kısa açıklama" :value="$brand->short_description ?? null" maxlength="500" />
<x-admin.form.input name="link" label="Link" type="url" :value="$brand->link ?? null" maxlength="2048" placeholder="https://..." />
<x-admin.form.input name="sort_order" label="Sıralama" type="number" :value="$brand->sort_order ?? null" min="0" max="65535" />
<x-admin.form.boolean name="is_active" label="Aktif" :value="$brand->is_active ?? true" />
<x-admin.features.fields :form="$featureForm" :fields="$featureFields" :media-config="$mediaConfig" />
<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary">{{ isset($brand) ? 'Değişiklikleri kaydet' : 'Marka ekle' }}</button>
    <a href="{{ route('admin.brand.index') }}" class="btn btn-link">Vazgeç</a>
</div>
