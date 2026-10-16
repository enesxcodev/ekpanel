@props(['name', 'label', 'options' => [], 'value' => null, 'id' => null, 'errorKey' => null, 'help' => null, 'required' => false, 'placeholder' => 'Seçin'])
@php($id = $id ?? 'field-'.str($name)->slug())
@php($errorKey = $errorKey ?? $name)
@php($selectedValue = old($errorKey, $value))
<x-admin.form.field :name="$name" :id="$id" :label="$label" :help="$help" :required="$required" :error-key="$errorKey">
    <select {{ $attributes->class(['form-select', 'is-invalid' => $errors->has($errorKey)]) }} id="{{ $id }}" name="{{ $name }}" @required($required)>
        @if ($placeholder !== false)<option value="">{{ $placeholder }}</option>@endif
        @foreach ($options as $option)
            <option value="{{ $option['value'] }}" @selected((string) (is_scalar($selectedValue) ? $selectedValue : '') === (string) $option['value'])>{{ $option['label'] }}</option>
        @endforeach
    </select>
</x-admin.form.field>
