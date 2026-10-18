@props(['definition', 'field', 'mediaConfig' => null])
@if ($definition->usesMediaLibrary())
    <div class="mb-3">
        <x-admin.media.picker
            :name="'settings['.$definition->id.']'" :label="$definition->label" :config="$mediaConfig"
            kind="image" :extensions="$definition->validation['extensions'] ?? ['jpg', 'jpeg', 'png', 'webp']"
            :value="old('settings.'.$definition->id, $field['mediaId'])" :preview-url="$field['fileUrl']"
            :remove-name="'remove['.$definition->id.']'" :disabled="!auth('admin')->user()->can('media.view')"
            :remove-value="(bool) old('remove.'.$definition->id, false)"
        />
        @if ($definition->description)<div class="form-hint">{{ $definition->description }}</div>@endif
        @error('settings.'.$definition->id)<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        @cannot('media.view')<div class="form-hint">Görsel seçmek için medya görüntüleme izni gerekir.</div>@endcannot
    </div>
@else
<x-admin.form.dynamic-field
    :definition="$definition->fieldDefinition()"
    :field="$field"
    :name="'settings['.$definition->id.']'"
    :error-key="'settings.'.$definition->id"
    :id="'setting-'.$definition->id"
    :remove-name="'remove['.$definition->id.']'"
/>
@endif
