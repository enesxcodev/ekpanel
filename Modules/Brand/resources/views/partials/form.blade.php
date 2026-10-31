@csrf
@isset($brand) @method('PUT') @endisset
<input type="hidden" name="lang_id" value="{{ $selectedLanguage?->id }}">
<div class="row g-4"><div class="col-lg-8">
@if(!isset($brand) || $selectedLanguage?->code === 'tr')
<x-admin.media.field name="image_media_id" label="Marka görseli" :config="$mediaConfig" :value="$brand->image_media_id ?? null" :preview-url="isset($brand) ? $brand->image_url : null" remove-name="remove_image" required />
<x-admin.form.input name="link" label="Link" type="url" :value="$brand->link ?? null" maxlength="2048" placeholder="https://..." />
<x-admin.form.input name="sort_order" label="Sıralama" type="number" :value="$brand->sort_order ?? null" min="0" max="65535" />
<x-admin.form.boolean name="is_active" label="Aktif" :value="$brand->is_active ?? true" />
@elseif(isset($brand))
<div class="mb-3"><span class="form-label d-block">Logo ve ortak alanlar</span>@if($brand->image_url)<img src="{{ $brand->image_url }}" alt="{{ $translation?->name ?? '' }}" class="img-thumbnail" style="max-width:180px">@endif<p class="form-hint">Logo, bağlantı, sıralama ve durum Türkçe düzenlemesinden değiştirilir.</p></div>
@endif
<x-admin.form.input name="name" label="Marka ismi" :value="old('name', $translation?->name)" required maxlength="255" />
<x-admin.form.textarea name="short_description" label="Kısa açıklama" :value="old('short_description', $translation?->short_description)" maxlength="500" />
<x-admin.features.fields :form="$featureForm" :fields="$featureFields" :media-config="$mediaConfig" />
<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary">{{ isset($brand) ? 'Değişiklikleri kaydet' : 'Marka ekle' }}</button>
    <a href="{{ route('admin.brand.index') }}" class="btn btn-link">Vazgeç</a>
</div>
</div>
<div class="col-lg-4"><div class="card"><div class="card-body"><h3 class="h5">Dil</h3><p class="text-secondary small">Yalnız seçili dilin adı, açıklaması ve ek alanları düzenlenir.</p><div class="list-group">
@foreach($activeLanguages as $language)
@php($hasTranslation = isset($brand) && in_array($language->id, $translatedLanguageIds, true))
<a class="list-group-item list-group-item-action d-flex justify-content-between {{ $selectedLanguage?->id === $language->id ? 'active' : '' }}" href="{{ isset($brand) ? route('admin.brand.edit', ['brand' => $brand->id, 'lang' => $language->code]) : route('admin.brand.create', ['lang' => 'tr']) }}" @if(!isset($brand) && $language->code !== 'tr') aria-disabled="true" tabindex="-1" @endif><span>{{ $language->name ?? strtoupper($language->code) }}</span><span class="small">{{ isset($brand) ? ($hasTranslation ? 'Çeviri var' : 'Çeviri yok') : ($language->code === 'tr' ? 'Yeni kayıt dili' : 'Türkçe kayıttan sonra eklenir') }}</span></a>
@endforeach
</div></div></div></div></div>
