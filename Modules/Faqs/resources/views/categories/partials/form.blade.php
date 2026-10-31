@csrf
@isset($category) @method('PUT') @endisset
@include('faqs::partials.languages')
<input type="hidden" name="lang_id" value="{{ $selectedLanguage?->id }}">
<x-admin.form.input name="name" label="Kategori adı" :value="old('name', $translation?->name)" required maxlength="255" />
<x-admin.features.fields :form="$featureForm" :fields="$featureFields" :media-config="$mediaConfig" />
<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary" @disabled($selectedLanguage === null)>{{ isset($category) ? 'Değişiklikleri kaydet' : 'Kategori ekle' }}</button>
    <a href="{{ route('admin.faqs.categories.index', array_filter(['lang' => $selectedLanguage?->code])) }}" class="btn btn-link">Vazgeç</a>
</div>
