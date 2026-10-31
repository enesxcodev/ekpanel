@csrf
@isset($member) @method('PUT') @endisset
<input type="hidden" name="lang_id" value="{{ $selectedLanguage?->id }}">
<div class="row g-4">
<div class="col-lg-8">
@if(!isset($member) || $selectedLanguage?->code === 'tr')
<x-admin.media.field name="image_media_id" label="Görsel" :config="$mediaConfig"
    :value="$member->image_media_id ?? null" :preview-url="isset($member) ? $member->image_url : null" remove-name="remove_image" />
<x-admin.form.boolean name="is_active" label="Durum" :value="$member->is_active ?? true" />
@elseif(isset($member))
<div class="mb-3"><span class="form-label d-block">Fotoğraf</span>@if($member->image_url)<img src="{{ $member->image_url }}" alt="{{ $translation?->name ?? '' }}" class="img-thumbnail" style="max-width: 180px">@else<span class="text-secondary">Fotoğraf yok</span>@endif</div>
<div class="mb-3"><span class="form-label d-block">Durum</span><span class="badge {{ $member->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $member->is_active ? 'Aktif' : 'Pasif' }}</span><p class="form-hint">Fotoğraf ve durum ortak alanlardır; bunları Türkçe düzenleme ekranından değiştirin.</p></div>
@endif
<x-admin.form.input name="name" label="İsim" :value="old('name', $translation?->name)" required maxlength="255" />
<x-admin.form.input name="title" label="Ünvan" :value="old('title', $translation?->title)" maxlength="255" />
<x-admin.features.fields :form="$featureForm" :fields="$featureFields" :media-config="$mediaConfig" />
<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary">{{ isset($member) ? 'Değişiklikleri kaydet' : 'Ekip ekle' }}</button>
    <a href="{{ route('admin.team.index') }}" class="btn btn-link">Vazgeç</a>
</div>
</div>
<div class="col-lg-4">
<div class="card"><div class="card-body">
<h3 class="h5">Dil</h3>
<p class="text-secondary small">Yalnızca seçili dilin isim, ünvan ve ek alanları düzenlenir. Eksik çeviriler başka dilden tamamlanmaz.</p>
<div class="list-group">
@foreach($activeLanguages as $language)
    @php($hasTranslation = isset($member) && $member->translations()->where('lang_id', $language->id)->exists())
    <a class="list-group-item list-group-item-action d-flex justify-content-between {{ $selectedLanguage?->id === $language->id ? 'active' : '' }}"
       href="{{ isset($member) ? route('admin.team.edit', ['member' => $member->id, 'lang' => $language->code]) : route('admin.team.create', ['lang' => 'tr']) }}" @if(!isset($member) && $language->code !== 'tr') aria-disabled="true" tabindex="-1" @endif>
        <span>{{ $language->name ?? strtoupper($language->code) }}</span>
        <span class="small">{{ isset($member) ? ($hasTranslation ? 'Çeviri var' : 'Çeviri yok') : ($language->code === 'tr' ? 'Yeni kayıt dili' : 'Türkçe kayıttan sonra eklenir') }}</span>
    </a>
@endforeach
</div>
</div></div>
</div>
</div>
