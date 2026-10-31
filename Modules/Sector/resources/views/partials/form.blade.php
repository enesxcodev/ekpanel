@php($isEditing = isset($sector) && $sector->exists)
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ $selectedLanguage?->code === 'tr' ? 'Sektör bilgileri' : 'Çeviri bilgileri' }}</h3></div>
            <div class="card-body">
                @csrf
                @if ($isEditing) @method('PUT') @endif
                <input type="hidden" name="lang_id" value="{{ $selectedLanguage?->id }}">
                @if ($selectedLanguage?->code === 'tr')
                    <x-admin.media.field name="image_media_id" label="Sektör görseli" :config="$mediaConfig" :value="$sector->image_media_id ?? null" :preview-url="$isEditing ? $sector->image_url : null" remove-name="remove_image" />
                @elseif ($isEditing)
                    <div class="mb-3"><label class="form-label">Sektör görseli</label><div><img src="{{ $sector->image_url ?? '' }}" alt="" class="avatar avatar-lg object-fit-cover"></div></div>
                    <div class="alert alert-info">Görsel, sıralama ve yayın durumu ortak bilgileridir ve Türkçe formdan yönetilir.</div>
                @endif
                <x-admin.form.input name="title" label="Başlık" data-slug-title :value="$sector->title ?? null" required maxlength="255" />
                <x-admin.form.input name="slug" label="Kısa bağlantı" :value="$sector->slug ?? null" maxlength="255" data-slug-field />
                @if ($selectedLanguage?->code === 'tr')
                    <x-admin.form.input name="sort_order" label="Sıralama" type="number" :value="$sector->sort_order ?? null" min="0" max="65535" />
                @elseif ($isEditing)
                    <div class="mb-3"><label class="form-label">Sıralama</label><input class="form-control" value="{{ $sector->sort_order }}" disabled></div>
                @endif
                <x-admin.form.textarea name="short_description" label="Kısa içerik" :value="$sector->short_description ?? null" rows="3" maxlength="1000" />
                <x-admin.form.textarea name="content" label="İçerik" data-hugerte :value="$sector->content ?? null" rows="10" maxlength="65535" />
                @if ($selectedLanguage?->code === 'tr')
                    <x-admin.form.boolean name="is_active" label="Durum" :value="$sector->is_active ?? true" />
                @elseif ($isEditing)
                    <div class="mb-3"><label class="form-label">Durum</label><input class="form-control" value="{{ $sector->is_active ? 'Aktif' : 'Pasif' }}" disabled></div>
                @endif
                <x-admin.features.fields :form="$featureForm" :fields="$featureFields" :media-config="$mediaConfig" />
                <fieldset class="mt-4"><legend>SEO</legend>
                    <x-admin.form.input name="meta_title" label="Meta başlık" :value="$sector->meta_title ?? null" maxlength="255" />
                    <x-admin.form.input name="meta_keywords" label="Meta anahtar" :value="$sector->meta_keywords ?? null" maxlength="1000" />
                    <x-admin.form.textarea name="meta_description" label="Meta açıklama" :value="$sector->meta_description ?? null" rows="3" maxlength="1000" />
                </fieldset>
            </div>
        </div>
        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary">{{ $isEditing ? 'Değişiklikleri kaydet' : 'Sektör ekle' }}</button>
            <a href="{{ route('admin.sector.index', ['lang' => $selectedLanguage?->code]) }}" class="btn btn-link">Vazgeç</a>
        </div>
    </div>
    <div class="col-lg-4">
        @include('sector::partials.languages')
    </div>
</div>
