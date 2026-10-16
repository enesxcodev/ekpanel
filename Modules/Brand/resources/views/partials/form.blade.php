@csrf
@isset($brand) @method('PUT') @endisset
<x-admin.form.input name="name" label="Marka ismi" :value="$brand->name ?? null" required maxlength="255" />
<x-admin.form.file name="image" label="Marka görseli" :current-url="isset($brand) ? Storage::disk('public')->url($brand->image) : null" :is-image="true" :required="!isset($brand)" accept=".jpg,.jpeg,.png,.webp" help="JPG, PNG veya WebP; en fazla 4 MB." />
<x-admin.form.textarea name="short_description" label="Kısa açıklama" :value="$brand->short_description ?? null" maxlength="500" />
<x-admin.form.input name="link" label="Link" type="url" :value="$brand->link ?? null" maxlength="2048" placeholder="https://..." />
<x-admin.features.fields :form="$featureForm" :fields="$featureFields" />
<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary">{{ isset($brand) ? 'Değişiklikleri kaydet' : 'Marka ekle' }}</button>
    <a href="{{ route('admin.brand.index') }}" class="btn btn-link">Vazgeç</a>
</div>
