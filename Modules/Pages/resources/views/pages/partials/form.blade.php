@csrf
@isset($page) @method('PUT') @endisset
@include('pages::pages.partials.category-tree-select', ['categoryTree' => $categoryTree, 'selectedCategoryId' => old('page_category_id', $page->page_category_id ?? '')])
<x-admin.form.input name="title" label="Başlık" :value="$page->title ?? null" required maxlength="255" data-slug-title />
<x-admin.form.input name="slug" label="Kısa bağlantı" :value="$page->slug ?? null" required maxlength="255" data-slug-field help="Kategorili sayfalarda kategori yoluna eklenir; kategorisiz sayfalarda /{slug} adresinde kullanılır." />
<x-admin.form.textarea name="short_description" label="Kısa açıklama" :value="$page->short_description ?? null" rows="3" maxlength="500" />
<x-admin.form.textarea name="content" label="Sayfa içeriği" :value="$page->content ?? null" rows="16" data-hugerte />
<x-admin.form.file name="image" label="Resim" :current-url="isset($page) && $page->image ? asset('storage/'.$page->image) : null" :is-image="true" remove-name="remove_image" accept=".jpg,.jpeg,.png,.webp" help="JPG, PNG veya WebP; en fazla 4 MB." />
<x-admin.form.select name="status" label="Yayın durumu" :options="[['value' => 'published', 'label' => 'Yayında'], ['value' => 'draft', 'label' => 'Taslak']]" :value="old('status', $page->status ?? 'published')" required />
<x-admin.form.input name="sort_order" label="Sıralama" type="number" :value="$page->sort_order ?? 0" min="0" max="65535" required help="Sayfa, seçili kategoride veya kategorisiz sayfalar arasında sıralanır." />
<x-admin.features.fields :form="$featureForm" :fields="$featureFields" />
<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary">{{ isset($page) ? 'Değişiklikleri kaydet' : 'Sayfa ekle' }}</button>
    <a href="{{ route('admin.pages.index') }}" class="btn btn-link">Vazgeç</a>
</div>
