@csrf
@isset($category) @method('PUT') @endisset
<input type="hidden" name="lang_id" value="{{ $selectedLanguage?->id }}">
@if (! $selectedLanguage)<div class="alert alert-warning">Önce <a href="{{ route('admin.settings.index', ['section' => 'languages']) }}">Ayarlar üzerinden aktif dil ekleyin.</a></div>@endif
<fieldset @disabled(! $selectedLanguage)>
<div class="row g-4">
    <div class="col-lg-9">
        <div class="mb-3"><label for="name" class="form-label">Kategori adı</label><input id="name" name="name" value="{{ old('name', $translation?->name ?? '') }}" class="form-control @error('name') is-invalid @enderror" maxlength="255" required autofocus>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <x-admin.features.fields :form="$featureForm" :fields="$featureFields" :media-config="$mediaConfig" />
    </div>
    <div class="col-lg-3">
        @include('products::partials.languages')
        <div class="card mb-4"><div class="card-body"><h3 class="h4">Ortak kategori ayarları</h3>
            <x-admin.form.input name="sort_order" label="Sıralama" type="number" :value="$category->sort_order ?? 0" min="0" max="65535" />
            <x-admin.form.boolean name="is_active" label="Durum" :value="$category->is_active ?? true" />
        </div></div>
    </div>
</div>
<button type="submit" class="btn btn-primary">{{ isset($category) ? 'Değişiklikleri kaydet' : 'Kategori ekle' }}</button>
<a href="{{ route('admin.products.categories.index') }}" class="btn btn-link">Vazgeç</a>
</fieldset>
