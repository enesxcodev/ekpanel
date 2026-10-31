@csrf
<input type="hidden" name="lang_id" value="{{ $selectedLanguage?->id }}">
@if (! $selectedLanguage)<div class="alert alert-warning">&Ouml;nce <a href="{{ route('admin.settings.index', ['section' => 'languages']) }}">Ayarlar &uuml;zerinden aktif dil ekleyin.</a></div>@endif
<fieldset @disabled(! $selectedLanguage)>
<p class="form-hint mb-3">Kategori, etiketler, yayın durumu, yayın tarihi, kapak görseli ve yazar tüm diller için geçerlidir.</p>
@isset($blog) @method('PUT') @endisset
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <p class="text-secondary mb-0">İçeriğinizi hazırlayın, yayın ve SEO ayarlarını düzenleyin.</p>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.blog.index') }}" class="btn">Vazgeç</a>
        <button type="submit" class="btn btn-primary"><x-admin.icon name="check" /> {{ isset($blog) ? 'Değişiklikleri kaydet' : 'İçeriği kaydet' }}</button>
    </div>
</div>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <x-admin.form.content-seo-tabs id="blog-form" content-label="İçerik bilgileri" seo-label="SEO ayarları" content-description="Temel bilgileri girin" seo-description="Arama motoru optimizasyonu">
                    <div class="border rounded-3 p-3 p-xl-4">
                        <div class="blog-form-section-title">
                            <span class="blog-form-section-icon"><x-admin.icon name="file" /></span>
                            <div><h2 class="h3 mb-1">Temel bilgiler</h2><p class="text-secondary mb-0 small">İçeriğinizin temel bilgilerini ve açıklamalarını girin.</p></div>
                        </div>
                        <x-admin.form.category-tree-select :tree="$categoryTree" name="blog_category_id" id="blog-category-search" label="Kategori" :selected="old('blog_category_id', $blog->blog_category_id ?? '')" required tree-label="İçerik kategorileri" />
                        <div class="row">
                            <div class="col-xl-6"><x-admin.form.input name="title" label="Başlık" :value="$translation?->title" required maxlength="255" data-slug-title /></div>
                            <div class="col-xl-6"><x-admin.form.input name="slug" label="Kısa bağlantı" :value="$translation?->slug" required maxlength="255" data-slug-field /></div>
                        </div>
                        <x-admin.form.textarea name="short_description" label="Kısa açıklama" :value="$translation?->short_description" rows="3" maxlength="500" />
                        <div class="mb-3">
                            <label class="form-label" for="blog-tags">Etiketler</label>
                            <select id="blog-tags" name="tags[]" class="form-select js-select2 @error('tags') is-invalid @enderror" multiple data-placeholder="Etiket arayın">
                                @foreach($tags as $tag)
                                    <option value="{{ $tag->id }}" @selected(in_array((string) $tag->id, array_map('strval', old('tags', isset($blog) ? $blog->tags->modelKeys() : [])), true))>{{ $tag->name }}</option>
                                @endforeach
                            </select>
                            @error('tags')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <x-admin.form.textarea name="content" label="İçerik" :value="$translation?->content" rows="16" maxlength="65535" data-hugerte />
                        <x-admin.features.fields :form="$featureForm" :fields="$featureFields" :media-config="$mediaConfig" />
                    </div>
                    <x-slot:seo>
                        <div class="border rounded-3 p-3 p-xl-4">
                            <x-admin.form.input name="meta_title" label="Meta başlık" :value="$translation?->meta_title" maxlength="255" />
                            <x-admin.form.textarea name="meta_description" label="Meta açıklama" :value="$translation?->meta_description" maxlength="1000" />
                        </div>
                    </x-slot:seo>
                </x-admin.form.content-seo-tabs>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        @include('blog::partials.languages')
        <div class="card mb-4">
            <div class="card-body">
                <div class="blog-form-section-title">
                    <span class="blog-form-section-icon"><x-admin.icon name="clock" /></span>
                    <div><h2 class="h3 mb-1">Yayın durumu</h2><p class="text-secondary mb-0 small">İçeriğin yayın ayarlarını yönetin.</p></div>
                </div>
                <x-admin.form.select name="status" label="Durum" :options="[['value' => 'draft', 'label' => 'Taslak'], ['value' => 'published', 'label' => 'Yayında'], ['value' => 'archived', 'label' => 'Arşiv']]" :value="old('status', $blog->status ?? 'draft')" required />
                <x-admin.form.input name="published_at" label="Yayın tarihi (isteğe bağlı)" type="datetime-local" :value="isset($blog) ? $blog->published_at?->format('Y-m-d\TH:i') : null" />
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-body">
                <div class="blog-form-section-title">
                    <span class="blog-form-section-icon"><x-admin.icon name="photo" /></span>
                    <div><h2 class="h3 mb-1">Öne çıkan görsel</h2><p class="text-secondary mb-0 small">İçeriğiniz için bir kapak görseli belirleyin.</p></div>
                </div>
                <x-admin.media.field name="image_media_id" label="Kapak görseli" :config="$mediaConfig" :value="$blog->image_media_id ?? null" :preview-url="isset($blog) ? $blog->image_url : null" remove-name="remove_image" />
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="blog-form-section-title">
                    <span class="blog-form-section-icon"><x-admin.icon name="users" /></span>
                    <div><h2 class="h3 mb-1">Yazar</h2><p class="text-secondary mb-0 small">İçeriğin yazarını belirleyin.</p></div>
                </div>
                <x-admin.form.select name="author_id" label="Yazar (isteğe bağlı)" :options="$authors->map(fn ($author) => ['value' => $author->id, 'label' => $author->name])->all()" :value="$blog->author_id ?? null" placeholder="Yazar seçin" class="js-select2" />
            </div>
        </div>
    </div>
</div>

</fieldset>
