@csrf
@isset($category) @method('PUT') @endisset
<x-admin.form.input name="name" label="Kategori adı" :value="$category->name ?? null" required maxlength="255" />
<x-admin.form.input name="icon" label="İkon" :value="$category->icon ?? null" maxlength="100" placeholder="file-text" help="İkon adı (ör. file-text, download, certificate); isteğe bağlı." />
<x-admin.features.fields :form="$featureForm" :fields="$featureFields" :media-config="$mediaConfig" />
<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary">{{ isset($category) ? 'Değişiklikleri kaydet' : 'Kategori ekle' }}</button>
    <a href="{{ route('admin.documents.categories.index') }}" class="btn btn-link">Vazgeç</a>
</div>
