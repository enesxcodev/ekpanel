@csrf
@isset($testimonial) @method('PUT') @endisset
<input type="hidden" name="lang_id" value="{{ $selectedLanguage?->id }}"><input type="hidden" name="lang" value="{{ $selectedLanguage?->code }}">
<div class="row g-4"><div class="col-lg-8">
<x-admin.form.input name="customer_name" label="Müşteri adı" :value="old('customer_name', $translation?->customer_name)" required maxlength="255" />
<x-admin.form.input name="company" label="Firma" :value="old('company', $translation?->company)" maxlength="255" />
<x-admin.form.input name="job_title" label="Unvan" :value="old('job_title', $translation?->job_title)" maxlength="255" />
<x-admin.form.textarea name="content" label="Yorum" :value="old('content', $translation?->content)" rows="16" maxlength="65535" required data-hugerte />
@if(!isset($testimonial) || $selectedLanguage?->code === 'tr')
<x-admin.media.field name="image_media_id" label="Müşteri görseli" :config="$mediaConfig"
    :value="$testimonial->image_media_id ?? null" :preview-url="isset($testimonial) ? $testimonial->image_url : null" remove-name="remove_image" />
<x-admin.form.select name="is_active" label="Yayın durumu" :options="[['value' => '1', 'label' => 'Yayında'], ['value' => '0', 'label' => 'Yayında değil']]" :value="(int)($testimonial->is_active ?? true)" :placeholder="false" required />
<x-admin.form.input name="sort_order" label="Sıralama" type="number" :value="$testimonial->sort_order ?? 0" min="0" max="65535" required />
@else
<div class="mb-3"><span class="form-label d-block">Yayın durumu / sıralama / görsel</span><p class="form-hint">{{ $testimonial->is_active ? 'Yayında' : 'Yayında değil' }} · {{ $testimonial->sort_order }} · {{ $testimonial->image_url ? 'Görsel var' : 'Görsel yok' }}. Ortak alanları Türkçe ekranından düzenleyin.</p></div>
@endif
<x-admin.features.fields :form="$featureForm" :fields="$featureFields" :media-config="$mediaConfig" />
<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary">{{ isset($testimonial) ? 'Değişiklikleri kaydet' : 'Yorum ekle' }}</button>
    <a href="{{ route('admin.testimonials.index', ['lang' => $selectedLanguage?->code]) }}" class="btn btn-link">Vazgeç</a>
</div>
</div><div class="col-lg-4"><div class="card"><div class="card-body"><h3 class="h5">Dil</h3><p class="text-secondary small">Ad, firma, unvan, yorum ve ek alanlar seçili dile aittir. Eksik çeviriler dışarıda kalır.</p><div class="list-group">
@foreach($activeLanguages as $language)
@php($hasTranslation = isset($testimonial) && $testimonial->translations()->where('lang_id', $language->id)->exists())
<a class="list-group-item list-group-item-action d-flex justify-content-between {{ $selectedLanguage?->id === $language->id ? 'active' : '' }}" href="{{ isset($testimonial) ? route('admin.testimonials.edit', ['testimonial' => $testimonial->id, 'lang' => $language->code]) : route('admin.testimonials.create', ['lang' => $language->code]) }}" @if(!isset($testimonial) && $language->code !== 'tr') aria-disabled="true" tabindex="-1" @endif><span>{{ strtoupper($language->code) }}</span><span class="small">{{ isset($testimonial) ? ($hasTranslation ? 'Çeviri var' : 'Çeviri yok') : ($language->code === 'tr' ? 'Yeni kayıt dili' : 'Türkçe kayıttan sonra eklenir') }}</span></a>
@endforeach
</div></div></div></div></div>
