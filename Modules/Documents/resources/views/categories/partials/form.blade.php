@csrf
@isset($category) @method('PUT') @endisset
<input type="hidden" name="lang_id" value="{{ $selectedLanguage?->id }}">
<div class="row g-4">
    <div class="col-lg-8">
        <x-admin.form.input name="name" label="Kategori adı" :value="old('name', $translation?->name)" required maxlength="255" />
        <x-admin.form.input name="icon" label="İkon" :value="old('icon', $category->icon ?? null)" maxlength="100" placeholder="file-text" help="İkon adı (ör. file-text, download, certificate); tüm dillerde ortaktır." />
        <x-admin.features.fields :form="$featureForm" :fields="$featureFields" :media-config="$mediaConfig" />
        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary" @disabled($selectedLanguage === null)>{{ isset($category) ? 'Değişiklikleri kaydet' : 'Kategori ekle' }}</button>
            <a href="{{ route('admin.documents.categories.index', array_filter(['lang' => $selectedLanguage?->code])) }}" class="btn btn-link">Vazgeç</a>
        </div>
    </div>
    <div class="col-lg-4">
        @include('documents::partials.languages')
    </div>
</div>
