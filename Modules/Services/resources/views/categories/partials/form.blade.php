@csrf
<input type="hidden" name="lang_id" value="{{ $selectedLanguage?->id }}">
@if (! $selectedLanguage)<div class="alert alert-warning">Önce ayarlardan aktif bir dil ekleyin.</div>@endif
<fieldset @disabled(! $selectedLanguage)>
<p class="form-hint">Durum, sıralama ve görsel gibi ortak alanlar tüm diller için geçerlidir.</p>
@if (isset($category))
    @method('PUT')
@endif

<div class="row g-4">
<div class="col-lg-8">
<div class="mb-3">
    <label for="name" class="form-label">Kategori adı</label>
    <input id="name" name="name" value="{{ old('name', $translation?->name ?? '') }}"
           class="form-control @error('name') is-invalid @enderror" maxlength="255" required autofocus>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<x-admin.form.input name="sort_order" label="Sıralama" type="number" :value="$category->sort_order ?? null" min="0" max="65535" />
<x-admin.form.boolean name="is_active" label="Durum" :value="$category->is_active ?? true" />
<x-admin.features.fields :form="$featureForm" :fields="$featureFields" :media-config="$mediaConfig" />

<button type="submit" class="btn btn-primary">{{ isset($category) ? 'Değişiklikleri kaydet' : 'Kategori ekle' }}</button>
<a href="{{ route('admin.services.categories.index') }}" class="btn btn-link">Vazgeç</a>
 </div>
 <div class="col-lg-4">
     @include('services::partials.languages')
 </div>
</div>

</fieldset>
