@csrf
<input type="hidden" name="lang_id" value="{{ $selectedLanguage?->id }}">
@if (! $selectedLanguage)<div class="alert alert-warning">Önce ayarlardan aktif bir dil ekleyin.</div>@endif
<fieldset @disabled(! $selectedLanguage)>
<p class="form-hint">Durum, sıralama ve görsel gibi ortak alanlar tüm diller için geçerlidir.</p>
@isset($service) @method('PUT') @endisset
<div class="row g-4">
<div class="col-lg-8">
<x-admin.form.content-seo-tabs id="service-form" content-label="Hizmet" seo-label="SEO">
    <x-admin.media.field name="image_media_id" label="Hizmet resim" :config="$mediaConfig"
    :value="$service->image_media_id ?? null" :preview-url="isset($service) ? $service->image_url : null" remove-name="remove_image" />
    <x-admin.form.select name="icon" :value="$service->icon ?? null" label="Hizmet ikon" :options="$icons" placeholder="İkonsuz" />
    <x-admin.form.select name="service_category_id" :value="$service->service_category_id ?? null" label="Kategori" :options="$categories" placeholder="Kategorisiz" />
    <x-admin.form.input name="title" :value="$translation?->title" label="Hizmet başlık" data-slug-title required maxlength="255" />
    <x-admin.form.input name="slug" label="Kısa bağlantı" :value="$translation?->slug" maxlength="255" data-slug-field />
    <x-admin.form.input name="sort_order" label="Sıralama" type="number" :value="$service->sort_order ?? null" min="0" max="65535" />
    <x-admin.form.textarea name="short_description" :value="$translation?->short_description" label="Hizmet kısa içerik" rows="3" maxlength="1000" />
    <x-admin.form.textarea name="content" :value="$translation?->content" label="Hizmet içerik" data-hugerte rows="10" maxlength="65535" />

    <x-admin.form.boolean name="is_active" label="Hizmet durum" :value="$service->is_active ?? true" />
    <x-admin.features.fields :form="$featureForm" :fields="$featureFields" :media-config="$mediaConfig" />
    <x-slot:seo>
        <x-admin.form.input name="meta_title" :value="$translation?->meta_title" label="Meta başlık" maxlength="255" />
        <x-admin.form.input name="meta_keywords" :value="$translation?->meta_keywords" label="Meta anahtar" maxlength="1000" />
        <x-admin.form.textarea name="meta_description" :value="$translation?->meta_description" label="Meta açıklama" rows="3" maxlength="1000" />
    </x-slot:seo>
</x-admin.form.content-seo-tabs>
<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary">{{ isset($service) ? 'Değişiklikleri kaydet' : 'Hizmet ekle' }}</button>
    <a href="{{ route('admin.services.index') }}" class="btn btn-link">Vazgeç</a>
</div>
 </div>
 <div class="col-lg-4">
     @include('services::partials.languages')
 </div>
</div>

</fieldset>
