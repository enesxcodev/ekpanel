@props(['name', 'label', 'type' => 'text', 'value' => null, 'id' => null, 'errorKey' => null, 'help' => null, 'required' => false, 'useOld' => true])
@php
    $id ??= 'field-'.str($name)->slug();
    $errorKey ??= $name;
    $selectedValue = $type === 'password' ? null : ($useOld ? old($errorKey, $value) : $value);
    $selectedValue = is_scalar($selectedValue) ? $selectedValue : null;
    $selectedValue = is_bool($selectedValue) ? (int) $selectedValue : $selectedValue;
@endphp
<x-admin.form.field :name="$name" :id="$id" :label="$label" :help="$help" :required="$required" :error-key="$errorKey">
    <input {{ $attributes->class(['form-control' => $type !== 'range', 'form-range' => $type === 'range', 'is-invalid' => $errors->has($errorKey)]) }}
           id="{{ $id }}" type="{{ $type }}" name="{{ $name }}" @if ($type !== 'password') value="{{ $selectedValue }}" @endif
           @required($required) aria-invalid="{{ $errors->has($errorKey) ? 'true' : 'false' }}"
           @if ($help) aria-describedby="{{ $id }}-help" @endif>
</x-admin.form.field>
