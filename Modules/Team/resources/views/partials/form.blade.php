@csrf
@isset($member) @method('PUT') @endisset
<x-admin.media.field name="image_media_id" label="Görsel" :config="$mediaConfig"
    :value="$member->image_media_id ?? null" :preview-url="isset($member) ? $member->image_url : null" remove-name="remove_image" />
<x-admin.form.input name="name" label="İsim" :value="$member->name ?? null" required maxlength="255" />
<x-admin.form.input name="title" label="Ünvan" :value="$member->title ?? null" maxlength="255" />
<x-admin.form.boolean name="is_active" label="Durum" :value="$member->is_active ?? true" />
<x-admin.features.fields :form="$featureForm" :fields="$featureFields" :media-config="$mediaConfig" />
<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary">{{ isset($member) ? 'Değişiklikleri kaydet' : 'Ekip ekle' }}</button>
    <a href="{{ route('admin.team.index') }}" class="btn btn-link">Vazgeç</a>
</div>
