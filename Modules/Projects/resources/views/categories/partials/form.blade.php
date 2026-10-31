@csrf
@if ($category) @method('PUT') @endif
<input type="hidden" name="lang_id" value="{{ $selectedLanguage?->id }}">
@if (! $selectedLanguage)<div class="alert alert-warning">Önce <a href="{{ route('admin.settings.index', ['section' => 'languages']) }}">Ayarlar &gt; diller</a> bölümünden aktif dil ekleyin.</div>@endif
<fieldset @disabled(! $selectedLanguage)>
<div class="row g-4">
    <div class="col-lg-9">
        <x-admin.form.input name="name" label="Kategori adı" :value="old('name', $translation?->name)" required maxlength="255" data-slug-title />
        <x-admin.form.input name="slug" label="Kısa bağlantı" :value="old('slug', $translation?->slug)" required maxlength="255" data-slug-field />
        <x-admin.features.fields :form="$featureForm" :fields="$featureFields" :media-config="$mediaConfig" />
    </div>
    <div class="col-lg-3">
        @include('projects::partials.languages', ['record' => $category, 'recordType' => 'category'])
        <div class="card"><div class="card-body">
            <h3 class="h5">Ortak kategori alanı</h3>
            <p class="form-hint">Kategori ikonu tüm dillerde ortaktır. Türkçe düzenlemesinden değiştirin.</p>
            <fieldset @disabled($selectedLanguage?->code !== \Modules\Projects\Services\ProjectsLanguageService::PrimaryCode)>
                <x-admin.form.input name="icon" label="İkon" :value="old('icon', $category?->icon)" maxlength="100" help="İsteğe bağlı Tabler ikon adı (ör. photo)." />
            </fieldset>
        </div></div>
    </div>
</div>
<div class="d-flex gap-2 mt-3"><button type="submit" class="btn btn-primary" @disabled(! $selectedLanguage)>Kaydet</button><a href="{{ route('admin.projects.categories.index') }}" class="btn btn-link">Vazgeç</a></div>
</fieldset>
