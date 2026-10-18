@csrf
@isset($sector) @method('PUT') @endisset
<x-admin.form.content-seo-tabs id="sector-form" content-label="Sektör" seo-label="SEO">
<x-admin.media.field name="image_media_id" label="Sektör görseli" :config="$mediaConfig"
    :value="$sector->image_media_id ?? null" :preview-url="isset($sector) ? $sector->image_url : null" remove-name="remove_image" />
<x-admin.form.input name="title" label="Başlık" data-slug-title :value="$sector->title ?? null" required maxlength="255" />
<x-admin.form.input name="slug" label="Kısa bağlantı" :value="$sector->slug ?? null" maxlength="255" data-slug-field />
<x-admin.form.input name="sort_order" label="Sıralama" type="number" :value="$sector->sort_order ?? null" min="0" max="65535" />
<x-admin.form.textarea name="short_description" label="Kısa içerik" :value="$sector->short_description ?? null" rows="3" maxlength="1000" />
<x-admin.form.textarea name="content" label="İçerik" data-hugerte :value="$sector->content ?? null" rows="10" maxlength="65535" />

<x-admin.form.boolean name="is_active" label="Durum" :value="$sector->is_active ?? true" />
<x-admin.features.fields :form="$featureForm" :fields="$featureFields" :media-config="$mediaConfig" />
    <x-slot:seo>
<fieldset class="mt-4">

    <x-admin.form.input name="meta_title" label="Meta başlık" :value="$sector->meta_title ?? null" maxlength="255" />
    <x-admin.form.input name="meta_keywords" label="Meta anahtar" :value="$sector->meta_keywords ?? null" maxlength="1000" />
    <x-admin.form.textarea name="meta_description" label="Meta açıklama" :value="$sector->meta_description ?? null" rows="3" maxlength="1000" />
</fieldset>
    </x-slot:seo>
</x-admin.form.content-seo-tabs>
<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary">{{ isset($sector) ? 'Değişiklikleri kaydet' : 'Sektör ekle' }}</button>
    <a href="{{ route('admin.sector.index') }}" class="btn btn-link">Vazgeç</a>
</div>
