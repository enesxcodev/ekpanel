@csrf
<input type="hidden" name="lang_id" value="{{ $selectedLanguage?->id }}">
@isset($category) @method('PUT') @endisset
@if (! $selectedLanguage)<div class="alert alert-warning">Önce Ayarlar üzerinden aktif Türkçe veya başka bir dil ekleyin.</div>@endif
<fieldset @disabled(! $selectedLanguage)>
<div class="row g-4">
    <div class="col-lg-8">
        <p class="form-hint mb-3">Üst kategori ve sıralama tüm diller için ortaktır. Kategori adı, bağlantısı ve açıklaması seçili dile kaydedilir.</p>
        <x-admin.form.select name="parent_id" label="Üst kategori" :options="$categoryOptions" :value="old('parent_id', $category->parent_id ?? null)" placeholder="Üst kategori yok" />
        <x-admin.form.input name="name" label="Kategori adı" :value="old('name', $translation?->name)" required maxlength="255" data-slug-title />
        <x-admin.form.input name="slug" label="Kısa bağlantı" :value="old('slug', $translation?->slug)" required maxlength="255" data-slug-field help="Bu seviyede seçilen dil için benzersiz olmalıdır." />
        <x-admin.form.textarea name="short_description" label="Kısa açıklama" :value="old('short_description', $translation?->short_description)" rows="3" maxlength="500" />
        <x-admin.form.input name="sort_order" label="Aynı seviyedeki sıralama" type="number" :value="$category->sort_order ?? 0" min="0" max="65535" required />
        <x-admin.features.fields :form="$featureForm" :fields="$featureFields" :media-config="$mediaConfig" />
        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary">{{ isset($category) ? 'Değişiklikleri kaydet' : 'Kategori ekle' }}</button>
            <a href="{{ route('admin.pages.categories.index', ['lang' => $selectedLanguage?->code]) }}" class="btn btn-link">Vazgeç</a>
        </div>
    </div>
    <aside class="col-lg-4">@include('pages::partials.languages')</aside>
</div>
</fieldset>
