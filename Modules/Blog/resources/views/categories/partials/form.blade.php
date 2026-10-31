@csrf
<input type="hidden" name="lang_id" value="{{ $selectedLanguage?->id }}">
@if (! $selectedLanguage)<div class="alert alert-warning">&Ouml;nce <a href="{{ route('admin.settings.index', ['section' => 'languages']) }}">Ayarlar &uuml;zerinden aktif dil ekleyin.</a></div>@endif
<fieldset @disabled(! $selectedLanguage)>
<p class="form-hint mb-3">Üst kategori ve sıralama tüm diller için geçerlidir.</p>
@isset($category) @method('PUT') @endisset
<div class="row g-4"><div class="col-lg-8">
<x-admin.form.select name="parent_id" label="Üst kategori" :options="$categories->reject(fn ($item) => in_array($item->id, $excludedCategoryIds, true))->map(fn ($item) => ['value' => $item->id, 'label' => $item->path])->values()->all()" :value="$category->parent_id ?? null" placeholder="Ana kategori" class="js-select2" />
<x-admin.form.input name="name" label="Kategori adı" :value="$translation?->name" required maxlength="255" data-slug-title />
<x-admin.form.input name="slug" label="Kısa bağlantı" :value="$translation?->slug" required maxlength="255" data-slug-field />
<x-admin.form.input name="sort_order" label="Sıralama" type="number" :value="$category->sort_order ?? 0" min="0" max="65535" required />
<x-admin.features.fields :form="$featureForm" :fields="$featureFields" :media-config="$mediaConfig" />
<div class="d-flex gap-2 mt-3"><button type="submit" class="btn btn-primary">{{ isset($category) ? 'Değişiklikleri kaydet' : 'Kategori ekle' }}</button><a href="{{ route('admin.blog.categories.index') }}" class="btn btn-link">Vazgeç</a></div>

</div><div class="col-lg-4">@include('blog::partials.languages')</div></div>
</fieldset>
