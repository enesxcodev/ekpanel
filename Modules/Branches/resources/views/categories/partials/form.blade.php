@csrf
@isset($category) @method('PUT') @endisset
<x-admin.form.input name="name" label="Kategori adı" :value="$category->name ?? null" required maxlength="150" />
<x-admin.form.input name="slug" label="Slug" :value="$category->slug ?? null" maxlength="80" help="Boş bırakılırsa kategori adından oluşturulur. Tema filtrelerinde kullanılabilir; değiştirirken mevcut filtreleri kontrol edin." />
<x-admin.form.input name="sort_order" label="Sıralama" type="number" :value="$category->sort_order ?? 0" required min="0" max="65535" />
<x-admin.form.boolean name="is_active" label="Durum" :value="$category->is_active ?? true" />
<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary">{{ isset($category) ? 'Değişiklikleri kaydet' : 'Kategori ekle' }}</button>
    <a href="{{ route('admin.branches.categories.index') }}" class="btn btn-link">Vazgeç</a>
</div>
