@csrf
@if ($project) @method('PUT') @endif
<input type="hidden" name="lang_id" value="{{ $selectedLanguage?->id }}">
@if (! $selectedLanguage)<div class="alert alert-warning">Önce <a href="{{ route('admin.settings.index', ['section' => 'languages']) }}">Ayarlar &gt; diller</a> bölümünden aktif dil ekleyin.</div>@endif
<fieldset @disabled(! $selectedLanguage)>
<div class="row g-4">
    <div class="col-lg-8">
        <x-admin.form.input name="title" label="Başlık" :value="old('title', $translation?->title)" required maxlength="255" data-slug-title />
        <x-admin.form.input name="slug" label="Kısa bağlantı" :value="old('slug', $translation?->slug)" required maxlength="255" data-slug-field />
        <x-admin.form.textarea name="content" label="İçerik" :value="old('content', $translation?->content)" data-hugerte rows="12" maxlength="65535" />
        <x-admin.features.fields :form="$featureForm" :fields="$featureFields" :media-config="$mediaConfig" />
    </div>
    <div class="col-lg-4">
        @include('projects::partials.languages', ['record' => $project, 'recordType' => 'project'])
        <div class="card"><div class="card-body">
            <h3 class="h5">Ortak proje alanları</h3>
            <p class="form-hint">Kategori, tarih, yayın durumu, sıralama, kapak ve galeri tüm dillerde ortaktır. Bunları Türkçe düzenlemesinden değiştirin.</p>
            <fieldset @disabled($selectedLanguage?->code !== \Modules\Projects\Services\ProjectsLanguageService::PrimaryCode)>
                <x-admin.form.select name="project_category_id" label="Kategori" :options="$categoryOptions" :value="old('project_category_id', $project?->project_category_id)" required />
                <x-admin.form.input name="project_date" label="Proje tarihi" type="date" :value="old('project_date', $project?->project_date?->format('Y-m-d'))" />
                <x-admin.form.select name="is_active" label="Yayın durumu" :options="[['value' => '1', 'label' => 'Yayında'], ['value' => '0', 'label' => 'Yayında değil']]" :value="old('is_active', (int)($project?->is_active ?? false))" :placeholder="false" required />
                <x-admin.form.input name="sort_order" label="Sıralama" type="number" :value="old('sort_order', $project?->sort_order ?? 0)" min="0" max="65535" required />
                <x-admin.media.picker name="image_media_ids" label="Proje görselleri" :config="$mediaConfig" kind="image" :multiple="true" :extensions="['jpg', 'jpeg', 'png', 'webp']" :value="old('image_media_ids', $project?->images->modelKeys() ?? [])" :disabled="!auth('admin')->user()->can('media.view')" />
                @error('image_media_ids')<div class="text-danger">{{ $message }}</div>@enderror
                @foreach ($errors->get('image_media_ids.*') as $messages) @foreach ($messages as $message)<div class="text-danger">{{ $message }}</div>@endforeach @endforeach
                <div class="mb-3 mt-3">
                    <label class="form-label" for="project-cover">Kapak görseli</label>
                    <select class="form-select" id="project-cover" name="cover_media_id" data-project-cover data-selected="{{ old('cover_media_id', $project?->cover_media_id ?? '') }}">
                        <option value="">İlk seçilen görsel</option>
                        @foreach ($project?->images ?? [] as $image)<option value="{{ $image->id }}" @selected((string) old('cover_media_id', $project?->cover_media_id) === (string) $image->id)>{{ $image->name }}</option>@endforeach
                    </select>
                    @error('cover_media_id')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
            </fieldset>
        </div></div>
    </div>
</div>
<div class="d-flex gap-2 mt-3"><button class="btn btn-primary" @disabled(! $selectedLanguage)>Kaydet</button><a href="{{ route('admin.projects.index') }}" class="btn btn-link">Vazgeç</a></div>
</fieldset>
