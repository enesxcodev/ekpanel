@props(['name', 'label', 'options' => [], 'value' => null, 'id' => null, 'errorKey' => null, 'help' => null, 'required' => false, 'layout' => 'vertical', 'variant' => 'standard'])
@php($id = $id ?? 'field-'.str($name)->slug())
@php($errorKey = $errorKey ?? $name)
@php($selectedValue = old($errorKey, $value))
<fieldset {{ $attributes->class(['mb-3']) }}>
    <legend class="form-label {{ $required ? 'required' : '' }}">{{ $label }}</legend>
    <input type="hidden" name="{{ $name }}" value="">
    <div @class(['form-selectgroup' => $variant === 'buttons', 'd-flex flex-wrap gap-3' => $variant !== 'buttons' && $layout === 'horizontal', 'd-flex flex-column gap-2' => $layout === 'vertical'])>
        @foreach ($options as $option)
            <label @class(['form-selectgroup-item' => $variant === 'buttons', 'form-check mb-0' => $variant !== 'buttons']) for="{{ $id }}-{{ $loop->index }}">
                <input type="radio" id="{{ $id }}-{{ $loop->index }}" name="{{ $name }}" value="{{ $option['value'] }}"
                       @class(['form-selectgroup-input' => $variant === 'buttons', 'form-check-input' => $variant !== 'buttons', 'is-invalid' => $errors->has($errorKey)])
                       @checked((string) (is_scalar($selectedValue) ? $selectedValue : '') === (string) $option['value']) @required($required)>
                <span class="{{ $variant === 'buttons' ? 'form-selectgroup-label' : 'form-check-label' }}">{{ $option['label'] }}</span>
            </label>
        @endforeach
    </div>
    @unless ($required)<button class="btn btn-link btn-sm px-0 mt-1" type="button" data-clear-radio>Seçimi temizle</button>@endunless
    @if ($help)<div class="form-hint">{{ $help }}</div>@endif
    @error($errorKey)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</fieldset>
