@csrf
@isset($category) @method('PUT') @endisset
<input type="hidden" name="lang_id" value="{{ $selectedLanguage?->id }}">
@if (! $selectedLanguage)<div class="alert alert-warning">Önce Ayarlar üzerinden aktif dil ekleyin.</div>@endif
<fieldset @disabled(! $selectedLanguage)>
<div class="row g-4">
<div class="col-lg-8">
<x-admin.form.input name="name" label="Kategori adı" :value="old('name', $translation?->name)" required maxlength="150" />
@if (! isset($category))
    <x-admin.form.input name="slug" label="Slug" :value="old('slug')" required maxlength="80" help="Dil bağımsız tema filtresi anahtarıdır; sonraki dil çevirilerinde değişmez." />
    <x-admin.form.input name="sort_order" label="Sıralama" type="number" :value="old('sort_order', 0)" required min="0" max="65535" />
    <x-admin.form.boolean name="is_active" label="Durum" :value="old('is_active', true)" />
@else
    <div class="alert alert-info">Slug, durum ve sıralama tüm dillerde ortaktır. Kategori slug: <code>{{ $category->slug }}</code></div>
@endif
</div>
<div class="col-lg-4"><div class="card"><div class="card-header"><h3 class="card-title">Diller</h3></div><div class="list-group list-group-flush">
@foreach ($activeLanguages as $language)
    @php($details = config('languages.catalog.'.$language->code))
    <a href="{{ request()->fullUrlWithQuery(['lang' => $language->code]) }}" class="list-group-item list-group-item-action {{ $selectedLanguage?->id === $language->id ? 'active' : '' }}" data-branches-language-link><span class="flag flag-country-{{ $details['flag'] ?? '' }}" aria-hidden="true"></span> {{ $details['name'] ?? $language->code }}<small class="d-block">{{ in_array($language->id, $translatedLanguageIds ?? [], true) ? 'Çeviri mevcut' : 'Henüz eklenmedi' }}</small></a>
@endforeach
</div></div></div>
</div>
<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary">{{ isset($category) ? 'Değişiklikleri kaydet' : 'Kategori ekle' }}</button>
    <a href="{{ route('admin.branches.categories.index') }}" class="btn btn-link">Vazgeç</a>
</div>
</fieldset>
