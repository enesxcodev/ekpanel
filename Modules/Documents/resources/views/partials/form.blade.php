@csrf
@isset($document) @method('PUT') @endisset
<input type="hidden" name="lang_id" value="{{ $selectedLanguage?->id }}">
<div class="row g-4">
    <div class="col-lg-8">
        <x-admin.form.input name="name" label="Belge adı" :value="old('name', $translation?->name)" required maxlength="255" data-slug-title />
        <x-admin.form.input name="slug" label="Kısa bağlantı" :value="old('slug', $translation?->slug)" required maxlength="255" data-slug-field help="Belge adına göre otomatik oluşturulur; bu dilde benzersiz olmalıdır." />
        <x-admin.form.select name="category_id" label="Kategori (isteğe bağlı)" :options="$categoryOptions" :value="old('category_id', $document->category_id ?? null)" placeholder="Kategorisiz" />
        <x-admin.form.textarea name="content" label="İçerik" :value="old('content', $translation?->content)" rows="16" maxlength="65535" data-hugerte />
        <x-admin.media.field name="file_media_id" label="Dosya" :config="$mediaConfig"
            :value="$document->file_media_id ?? null" :preview-url="isset($document) ? $document->file_url : null" remove-name="remove_file" kind="" :extensions="['png', 'jpg', 'jpeg', 'webp', 'pdf', 'doc', 'pptx']" :max-size="15360" />
        <x-admin.form.select name="status" label="Yayın durumu" :value="$document->status ?? 'draft'" :options="[['value' => 'draft', 'label' => 'Taslak'], ['value' => 'published', 'label' => 'Yayında']]" :placeholder="false" required />
        <x-admin.features.fields :form="$featureForm" :fields="$featureFields" :media-config="$mediaConfig" />
        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary" @disabled($selectedLanguage === null)>{{ isset($document) ? 'Değişiklikleri kaydet' : 'Belge ekle' }}</button>
            <a href="{{ route('admin.documents.index', array_filter(['lang' => $selectedLanguage?->code])) }}" class="btn btn-link">Vazgeç</a>
        </div>
    </div>
    <div class="col-lg-4">
        @include('documents::partials.languages')
    </div>
</div>
