@csrf
@isset($faq) @method('PUT') @endisset
@include('faqs::partials.languages')
<input type="hidden" name="lang_id" value="{{ $selectedLanguage?->id }}">
<x-admin.form.input name="name" label="Soru" :value="old('name', $translation?->name)" required maxlength="255" />
<x-admin.form.select name="category_id" label="Kategori (isteğe bağlı)" :options="$categoryOptions" :value="$faq->category_id ?? null" placeholder="Kategorisiz" />
<x-admin.form.textarea name="content" label="Yanıt" :value="old('content', $translation?->content)" rows="16" maxlength="65535" required data-hugerte />
<x-admin.features.fields :form="$featureForm" :fields="$featureFields" :media-config="$mediaConfig" />
<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary" @disabled($selectedLanguage === null)>{{ isset($faq) ? 'Değişiklikleri kaydet' : 'Soru ekle' }}</button>
    <a href="{{ route('admin.faqs.index', array_filter(['lang' => $selectedLanguage?->code])) }}" class="btn btn-link">Vazgeç</a>
</div>
