@props(['name', 'id' => null, 'label' => null, 'help' => null, 'required' => false, 'errorKey' => null])
@php($fieldId = $id ?? 'field-'.str($name)->slug())
@php($errorKey = $errorKey ?? $name)
<div {{ $attributes->class(['mb-3']) }}>
    @if ($label)
        <label for="{{ $fieldId }}" class="form-label {{ $required ? 'required' : '' }}">{{ $label }}</label>
    @endif
    {{ $slot }}
    @if ($help)<div class="form-hint" id="{{ $fieldId }}-help">{{ $help }}</div>@endif
    @error($errorKey)<div class="invalid-feedback d-block" id="{{ $fieldId }}-error">{{ $message }}</div>@enderror
</div>
