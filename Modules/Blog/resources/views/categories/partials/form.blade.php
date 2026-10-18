@csrf
@isset($category) @method('PUT') @endisset
<x-admin.form.select name="parent_id" label="Üst kategori" :options="$categories->reject(fn ($item) => isset($category) && ($item->id === $category->id || in_array($item->id, $category->descendantIds(), true)))->map(fn ($item) => ['value' => $item->id, 'label' => $item->path])->values()->all()" :value="$category->parent_id ?? null" placeholder="Ana kategori" class="js-select2" />
<x-admin.form.input name="name" label="Kategori adı" :value="$category->name ?? null" required maxlength="255" data-slug-title />
<x-admin.form.input name="slug" label="Kısa bağlantı" :value="$category->slug ?? null" required maxlength="255" data-slug-field />
<x-admin.form.input name="sort_order" label="Sıralama" type="number" :value="$category->sort_order ?? 0" min="0" max="65535" required />
<x-admin.features.fields :form="$featureForm" :fields="$featureFields" :media-config="$mediaConfig" />
<div class="d-flex gap-2 mt-3"><button type="submit" class="btn btn-primary">{{ isset($category) ? 'Değişiklikleri kaydet' : 'Kategori ekle' }}</button><a href="{{ route('admin.blog.categories.index') }}" class="btn btn-link">Vazgeç</a></div>
