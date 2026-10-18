@csrf
@isset($document) @method('PUT') @endisset
<x-admin.form.input name="name" label="Belge adı" :value="$document->name ?? null" required maxlength="255" data-slug-title />
<x-admin.form.input name="slug" label="Kısa bağlantı" :value="$document->slug ?? null" required maxlength="255" data-slug-field help="Belge adına göre otomatik oluşturulur; benzersiz olmalıdır." />
<x-admin.form.select name="category_id" label="Kategori (isteğe bağlı)" :options="$categoryOptions" :value="$document->category_id ?? null" placeholder="Kategorisiz" />
<x-admin.form.textarea name="content" label="İçerik" :value="$document->content ?? null" rows="16" maxlength="65535" data-hugerte />
<x-admin.media.field name="file_media_id" label="Dosya" :config="$mediaConfig"
    :value="$document->file_media_id ?? null" :preview-url="isset($document) ? $document->file_url : null" remove-name="remove_file" kind="" :extensions="['png', 'jpg', 'jpeg', 'webp', 'pdf', 'doc', 'pptx']" :max-size="15360" />
<x-admin.form.select name="status" label="Yayın durumu" :value="$document->status ?? 'draft'" :options="[['value' => 'draft', 'label' => 'Taslak'], ['value' => 'published', 'label' => 'Yayında']]" :placeholder="false" required />
<x-admin.features.fields :form="$featureForm" :fields="$featureFields" :media-config="$mediaConfig" />
<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary">{{ isset($document) ? 'Değişiklikleri kaydet' : 'Belge ekle' }}</button>
    <a href="{{ route('admin.documents.index') }}" class="btn btn-link">Vazgeç</a>
</div>
