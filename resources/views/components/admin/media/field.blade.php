@props(['name' => 'image_media_id', 'label' => 'Görsel', 'config', 'kind' => 'image', 'extensions' => ['jpg', 'jpeg', 'png', 'webp'], 'value' => null, 'previewUrl' => null, 'removeName' => 'remove_image', 'required' => false, 'maxSize' => 4096])
<div {{ $attributes->class(['mb-3']) }}>
    <x-admin.media.picker :name="$name" :label="$label.($required ? ' *' : '')" :config="$config" :kind="$kind"
        :extensions="$extensions" :value="old($name, $value)" :preview-url="$previewUrl"
        :remove-name="$removeName" :remove-value="(bool) old($removeName, false)"
        :disabled="!auth('admin')->user()?->can('media.view')" />
    <div class="form-hint">{{ $required ? 'Bu alan zorunludur.' : 'İsteğe bağlı.' }} En fazla {{ $maxSize }} KB. Seçimi kaldırmak dosyayı kütüphaneden silmez.</div>
    @cannot('media.view')<div class="form-hint">Dosya seçmek için medya görüntüleme izni gerekir.</div>@endcannot
    @error($name)<div class="text-danger small mt-1">{{ $message }}</div>@enderror
</div>
