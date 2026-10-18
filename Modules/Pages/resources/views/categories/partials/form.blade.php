@csrf
@isset($category) @method('PUT') @endisset
<x-admin.form.select name="parent_id" label="Üst kategori" :options="$categoryOptions" :value="$category->parent_id ?? null" placeholder="Üst kategori yok" />
<x-admin.form.input name="name" label="Kategori adı" :value="$category->name ?? null" required maxlength="255" data-slug-title />
<x-admin.form.input name="slug" label="Kısa bağlantı" :value="$category->slug ?? null" required maxlength="255" data-slug-field help="Bu seviyede benzersiz olmalıdır." />
<x-admin.form.textarea name="short_description" label="Kısa açıklama" :value="$category->short_description ?? null" rows="3" maxlength="500" />
<x-admin.form.input name="sort_order" label="Aynı seviyedeki sıralama" type="number" :value="$category->sort_order ?? 0" min="0" max="65535" required />
<x-admin.features.fields :form="$featureForm" :fields="$featureFields" :media-config="$mediaConfig" />
<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary">{{ isset($category) ? 'Değişiklikleri kaydet' : 'Kategori ekle' }}</button>
    <a href="{{ route('admin.pages.categories.index') }}" class="btn btn-link">Vazgeç</a>
</div>
