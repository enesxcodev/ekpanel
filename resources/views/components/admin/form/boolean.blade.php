@props(['name', 'label', 'value' => false, 'id' => null, 'errorKey' => null, 'help' => null])
@php($id = $id ?? 'field-'.str($name)->slug())
@php($errorKey = $errorKey ?? $name)
<x-admin.form.field :name="$name" :id="$id" :help="$help" :error-key="$errorKey">
    <input type="hidden" name="{{ $name }}" value="0">
    <label class="form-check form-switch" for="{{ $id }}">
        <input {{ $attributes->class(['form-check-input', 'is-invalid' => $errors->has($errorKey)]) }} type="checkbox" id="{{ $id }}" name="{{ $name }}" value="1" @checked((bool) old($errorKey, $value))>
        <span class="form-check-label">{{ $label }}</span>
    </label>
</x-admin.form.field>
