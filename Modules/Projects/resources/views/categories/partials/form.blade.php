@csrf
@if ($category) @method('PUT') @endif

<x-admin.form.input name="name" label="Kategori adı" :value="$category->name ?? null" required maxlength="255" data-slug-title />
<x-admin.form.input name="slug" label="Kısa bağlantı" :value="$category->slug ?? null" required maxlength="255" data-slug-field />
<x-admin.form.input name="icon" label="İkon" :value="$category->icon ?? null" maxlength="100" help="İsteğe bağlı Tabler ikon adı (ör. photo)." />

<x-admin.features.fields :form="$featureForm" :fields="$featureFields" :media-config="$mediaConfig" />
<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary">Kaydet</button>
    <a href="{{ route('admin.projects.categories.index') }}" class="btn btn-link">Vazgeç</a>
</div>