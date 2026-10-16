@csrf
@isset($member) @method('PUT') @endisset
<x-admin.form.file name="image" label="Görsel" :current-url="isset($member) && $member->image ? Storage::disk('public')->url($member->image) : null" :is-image="true" remove-name="remove_image" accept=".jpg,.jpeg,.png,.webp" help="JPG, PNG veya WebP; en fazla 4 MB." />
<x-admin.form.input name="name" label="İsim" :value="$member->name ?? null" required maxlength="255" />
<x-admin.form.input name="title" label="Ünvan" :value="$member->title ?? null" maxlength="255" />
<x-admin.form.boolean name="is_active" label="Durum" :value="$member->is_active ?? true" />
<x-admin.features.fields :form="$featureForm" :fields="$featureFields" />
<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary">{{ isset($member) ? 'Değişiklikleri kaydet' : 'Ekip ekle' }}</button>
    <a href="{{ route('admin.team.index') }}" class="btn btn-link">Vazgeç</a>
</div>
