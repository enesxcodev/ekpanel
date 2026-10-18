@csrf
@if ($category) @method('PUT') @endif
<x-admin.form.select name="parent_id" label="Üst kategori" :options="$categoryOptions" :value="$category->parent_id ?? null" placeholder="Üst kategori yok" />
<x-admin.form.input name="name" label="Kategori adı" :value="$category->name ?? null" required maxlength="255"  />

<x-admin.form.input name="icon" label="İkon" :value="$category->icon ?? null" maxlength="100" help="İsteğe bağlı Tabler ikon adı (ör. photo)." />
<x-admin.form.textarea name="short_description" label="Kısa açıklama" :value="$category->short_description ?? null" rows="3" maxlength="500" />
<x-admin.features.fields :form="$featureForm" :fields="$featureFields" :media-config="$mediaConfig" />
<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary">Kaydet</button>
    <a href="{{ route('admin.galeriler.categories.index') }}" class="btn btn-link">Vazgeç</a>
</div>