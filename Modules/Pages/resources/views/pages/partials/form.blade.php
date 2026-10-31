@csrf
<input type="hidden" name="lang_id" value="{{ $selectedLanguage?->id }}">
@isset($page) @method('PUT') @endisset
@if (! $selectedLanguage)<div class="alert alert-warning">Önce Ayarlar üzerinden aktif Türkçe veya başka bir dil ekleyin.</div>@endif
<fieldset @disabled(! $selectedLanguage)>
<div class="row g-4">
    <div class="col-lg-8">
        <x-admin.form.content-seo-tabs id="page-form" content-label="Sayfa ekleme">
            <p class="form-hint mb-3">Kategori, yayın durumu, sıralama ve ana görsel tüm diller için ortaktır. Metin ve SEO alanları seçili dile kaydedilir.</p>
            <x-admin.form.category-tree-select :tree="$categoryTree" name="page_category_id" id="page-category-search" label="Kategori (isteğe bağlı)" :selected="old('page_category_id', $page->page_category_id ?? '')" allow-empty empty-label="Kategorisiz" hint="Kategorisiz seçilirse sayfa adresi /{slug} olur." tree-label="Sayfa kategorileri" />
            <x-admin.form.input name="title" label="Başlık" :value="old('title', $translation?->title)" required maxlength="255" data-slug-title />
            <x-admin.form.input name="slug" label="Kısa bağlantı" :value="old('slug', $translation?->slug)" required maxlength="255" data-slug-field help="Kategorili sayfalarda kategori yoluna eklenir; kategorisiz sayfalarda /{slug} adresinde kullanılır." />
            <x-admin.form.textarea name="short_description" label="Kısa açıklama" :value="old('short_description', $translation?->short_description)" rows="3" maxlength="500" />
            <x-admin.form.textarea name="content" label="Sayfa içeriği" :value="old('content', $translation?->content)" rows="16" data-hugerte />
            <x-admin.media.field name="image_media_id" label="Resim" :config="$mediaConfig" :value="$page->image_media_id ?? null" :preview-url="isset($page) ? $page->image_url : null" remove-name="remove_image" />
            <x-admin.form.select name="status" label="Yayın durumu" :options="[['value' => 'published', 'label' => 'Yayında'], ['value' => 'draft', 'label' => 'Taslak']]" :value="old('status', $page->status ?? 'published')" required />
            <x-admin.form.input name="sort_order" label="Sıralama" type="number" :value="$page->sort_order ?? 0" min="0" max="65535" required help="Sayfa, seçili kategoride veya kategorisiz sayfalar arasında sıralanır." />
            <x-admin.features.fields :form="$featureForm" :fields="$featureFields" :media-config="$mediaConfig" />
            <x-slot:seo>
                <x-admin.form.input name="meta_title" label="Meta başlık" :value="old('meta_title', $translation?->meta_title)" maxlength="255" />
                <x-admin.form.input name="meta_keywords" label="Meta anahtar" :value="old('meta_keywords', $translation?->meta_keywords)" maxlength="1000" />
                <x-admin.form.textarea name="meta_description" label="Meta açıklama" :value="old('meta_description', $translation?->meta_description)" maxlength="1000" />
            </x-slot:seo>
        </x-admin.form.content-seo-tabs>
        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary">{{ isset($page) ? 'Değişiklikleri kaydet' : 'Sayfa ekle' }}</button>
            <a href="{{ route('admin.pages.index', ['lang' => $selectedLanguage?->code]) }}" class="btn btn-link">Vazgeç</a>
        </div>
    </div>
    <aside class="col-lg-4">@include('pages::partials.languages')</aside>
</div>
</fieldset>
