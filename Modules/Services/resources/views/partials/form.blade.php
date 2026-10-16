@csrf
<x-admin.form.file name="image" label="Hizmet resim" :is-image="true" accept=".jpg,.jpeg,.png,.webp" help="JPG, PNG veya WebP; en fazla 4 MB. İsteğe bağlı." />
<x-admin.form.select name="icon" label="Hizmet ikon" :options="$icons" placeholder="İkonsuz" />
<x-admin.form.select name="service_category_id" label="Kategori" :options="$categories" placeholder="Kategorisiz" />
<x-admin.form.input name="title" label="Hizmet başlık" required maxlength="255" />
<x-admin.form.textarea name="short_description" label="Hizmet kısa içerik" rows="3" maxlength="1000" />
<x-admin.form.textarea name="content" label="Hizmet içerik" rows="10" maxlength="65535" />
<fieldset class="mt-4">
    <legend class="h3">SEO ayarları</legend>
    <x-admin.form.input name="meta_title" label="Meta başlık" maxlength="255" />
    <x-admin.form.input name="meta_keywords" label="Meta anahtar" maxlength="1000" />
    <x-admin.form.textarea name="meta_description" label="Meta açıklama" rows="3" maxlength="1000" />
</fieldset>
<x-admin.form.boolean name="is_active" label="Hizmet durum" :value="true" />
<x-admin.features.fields :form="$featureForm" :fields="$featureFields" />
<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary">Hizmet ekle</button>
    <a href="{{ route('admin.services.index') }}" class="btn btn-link">Vazgeç</a>
</div>
