@props(['definition', 'field', 'name', 'errorKey', 'id', 'removeName' => null, 'mediaConfig' => null])
@php
    $required = (bool) ($definition->validation['required'] ?? false);
    $config = $definition->attributes ?? [];
    $validation = $definition->validation ?? [];
    $type = $definition->input_type->value;
@endphp
@switch($type)
    @case('radio')
        <x-admin.form.radio :name="$name" :id="$id" :error-key="$errorKey" :label="$definition->label" :value="$field['value']" :help="$definition->description" :required="$required" :options="$definition->options" :layout="$config['layout'] ?? 'vertical'" :variant="$config['variant'] ?? 'standard'" />
        @break
    @case('select')
        <x-admin.form.select :name="$name" :id="$id" :error-key="$errorKey" :label="$definition->label" :value="$field['value']" :help="$definition->description" :required="$required" :options="$definition->options" />
        @break
    @case('textarea')
        <x-admin.form.textarea :name="$name" :id="$id" :error-key="$errorKey" :label="$definition->label" :value="$field['value']" :help="$definition->description" :required="$required" :rows="$config['rows'] ?? 4" :placeholder="$config['placeholder'] ?? null" :minlength="$validation['min'] ?? null" :maxlength="$validation['max'] ?? null" />
        @break
    @case('boolean')
        <x-admin.form.boolean :name="$name" :id="$id" :error-key="$errorKey" :label="$definition->label" :value="$field['value']" :help="$definition->description" />
        @break
    @case('file')
    @case('image')
        @if ($definition->value_type === \App\Enums\Fields\FieldValueType::Media)
            <div class="mb-3">
                <x-admin.media.picker :name="$name" :label="$definition->label.($required ? ' *' : '')" :config="$mediaConfig"
                    :kind="$type === 'image' ? 'image' : ''" :extensions="$validation['extensions'] ?? ($type === 'image' ? ['jpg', 'jpeg', 'png', 'webp'] : \App\Services\Media\MediaFileInspector::EXTENSIONS)"
                    :value="old($errorKey, $field['value'])" :preview-url="$field['fileUrl']" :remove-name="$removeName"
                    :remove-value="(bool) old('remove_features.'.$definition->key, false)" :disabled="!auth('admin')->user()?->can('media.view')" />
                @if ($definition->description)<div class="form-hint">{{ $definition->description }}</div>@endif
                <div class="form-hint">En fazla {{ $validation['max_size'] ?? 2048 }} KB. Seçimi kaldırmak dosyayı kütüphaneden silmez.</div>
                @error($errorKey)<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
        @else
        <x-admin.form.file :name="$name" :id="$id" :error-key="$errorKey" :label="$definition->label" :help="$definition->description" :required="$required" :current-url="$field['fileUrl']" :is-image="$type === 'image'" :remove-name="$removeName" :accept="implode(',', array_map(fn ($extension) => '.'.$extension, $validation['extensions'] ?? []))" />
        @endif
        @break
    @case('password')
        <x-admin.form.input :name="$name" :id="$id" :error-key="$errorKey" :label="$definition->label" type="password" :help="$field['hasValue'] ? 'Kayıtlı değer mevcut. Boş bırakırsanız korunur.' : $definition->description" :required="$required && ! $field['hasValue']" autocomplete="new-password" :maxlength="$validation['max'] ?? null" />
        @if ($field['hasValue'])
            <label class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="{{ $removeName }}" value="1">
                <span class="form-check-label">Kayıtlı şifreyi kaldır</span>
            </label>
        @endif
        @break
    @case('number')
    @case('range')
        <x-admin.form.input :name="$name" :id="$id" :error-key="$errorKey" :label="$definition->label" :type="$type" :value="$field['value']" :help="$definition->description" :required="$required" :min="$validation['min'] ?? null" :max="$validation['max'] ?? null" :step="$config['step'] ?? ($definition->value_type->value === 'float' ? 'any' : 1)" :class="$type === 'range' ? 'form-range' : ''" :data-settings-range="$type === 'range' ? 'true' : null" />
        @if ($type === 'range')<output for="{{ $id }}" class="badge bg-secondary-lt mb-3" data-range-output="{{ $id }}">{{ is_scalar(old($errorKey)) ? old($errorKey) : ($field['value'] ?? $validation['min'] ?? 0) }}</output>@endif
        @break
    @default
        <x-admin.form.input :name="$name" :id="$id" :error-key="$errorKey" :label="$definition->label" :type="$type" :value="$field['value']" :help="$definition->description" :required="$required" :placeholder="$config['placeholder'] ?? null" :minlength="$validation['min'] ?? null" :maxlength="$validation['max'] ?? null" />
@endswitch
