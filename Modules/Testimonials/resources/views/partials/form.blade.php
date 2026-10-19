@csrf
@isset($testimonial) @method('PUT') @endisset
<x-admin.form.input name="customer_name" label="Müşteri adı" :value="$testimonial->customer_name ?? null" required maxlength="255" />
<x-admin.form.input name="company" label="Firma" :value="$testimonial->company ?? null" maxlength="255" />
<x-admin.form.input name="job_title" label="Unvan" :value="$testimonial->job_title ?? null" maxlength="255" />
<x-admin.form.textarea name="content" label="Yorum" :value="$testimonial->content ?? null" rows="16" maxlength="65535" required data-hugerte />
<x-admin.media.field name="image_media_id" label="Müşteri görseli" :config="$mediaConfig"
    :value="$testimonial->image_media_id ?? null" :preview-url="isset($testimonial) ? $testimonial->image_url : null" remove-name="remove_image" />
<x-admin.form.select name="is_active" label="Yayın durumu" :options="[['value' => '1', 'label' => 'Yayında'], ['value' => '0', 'label' => 'Yayında değil']]" :value="(int)($testimonial->is_active ?? true)" :placeholder="false" required />
<x-admin.form.input name="sort_order" label="Sıralama" type="number" :value="$testimonial->sort_order ?? 0" min="0" max="65535" required />
<x-admin.features.fields :form="$featureForm" :fields="$featureFields" :media-config="$mediaConfig" />
<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary">{{ isset($testimonial) ? 'Değişiklikleri kaydet' : 'Yorum ekle' }}</button>
    <a href="{{ route('admin.testimonials.index') }}" class="btn btn-link">Vazgeç</a>
</div>
