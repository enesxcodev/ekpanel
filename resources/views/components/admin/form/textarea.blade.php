@props(['name', 'label', 'value' => null, 'id' => null, 'errorKey' => null, 'help' => null, 'required' => false, 'rows' => 4])
@php($id = $id ?? 'field-'.str($name)->slug())
@php($errorKey = $errorKey ?? $name)
@php($selectedValue = old($errorKey, $value))
<x-admin.form.field :name="$name" :id="$id" :label="$label" :help="$help" :required="$required" :error-key="$errorKey">
    <textarea {{ $attributes->class(['form-control', 'is-invalid' => $errors->has($errorKey)]) }} id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}" @required($required)>{{ is_scalar($selectedValue) ? $selectedValue : '' }}</textarea>
</x-admin.form.field>
