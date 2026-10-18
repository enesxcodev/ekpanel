<div class="ek-form__fields">
@foreach($form->fields as $field)
    @php($id = 'site-form-'.$form->id.'-'.$field->key)
    @php($errorKey = 'answers.'.$field->key)
    @php($oldValue = (int)old('_form_id') === $form->id ? old($errorKey) : null)
    <div class="ek-form__field ek-form__field--{{ $field->width }} {{ $field->css_class }}" data-field="{{ $field->key }}">
        <label class="ek-form__label" for="{{ $id }}">{{ $field->label }}@if($field->required)<span aria-hidden="true"> *</span>@endif</label>
        @if($field->input_type === 'textarea')
            <textarea id="{{ $id }}" name="answers[{{ $field->key }}]" class="ek-form__input {{ $field->input_class }}" placeholder="{{ $field->placeholder }}" rows="4" maxlength="{{ $field->validation['max'] ?? 10000 }}" @required($field->required) aria-describedby="{{ $id }}-help {{ $id }}-error">{{ is_scalar($oldValue) ? $oldValue : '' }}</textarea>
        @elseif($field->input_type === 'select')
            <select id="{{ $id }}" name="answers[{{ $field->key }}]" class="ek-form__input {{ $field->input_class }}" @required($field->required) aria-describedby="{{ $id }}-help {{ $id }}-error"><option value="">{{ $field->placeholder ?: 'Seçin' }}</option>@foreach($field->options as $option)<option value="{{ $option['value'] }}" @selected($oldValue===$option['value'])>{{ $option['label'] }}</option>@endforeach</select>
        @elseif($field->input_type === 'radio')
            <div role="group" aria-label="{{ $field->label }}">@foreach($field->options as $index=>$option)<label class="ek-form__choice"><input type="radio" id="{{ $id }}-{{ $index }}" name="answers[{{ $field->key }}]" value="{{ $option['value'] }}" @checked($oldValue===$option['value']) @required($field->required)> {{ $option['label'] }}</label>@endforeach</div>
        @elseif($field->input_type === 'boolean')
            <input type="checkbox" id="{{ $id }}" name="answers[{{ $field->key }}]" value="1" class="ek-form__checkbox {{ $field->input_class }}" @checked($oldValue) @required($field->required)>
        @else
            <input id="{{ $id }}" type="{{ $field->input_type }}" name="answers[{{ $field->key }}]" class="ek-form__input {{ $field->input_class }}"
                @if($field->input_type !== 'file') value="{{ is_scalar($oldValue) ? $oldValue : '' }}" placeholder="{{ $field->placeholder }}" @endif
                @if(in_array($field->input_type,['text','email','tel'],true)) maxlength="{{ $field->validation['max'] ?? 10000 }}" @endif
                @if($field->input_type==='number') step="any" @endif
                @if($field->input_type==='file') accept="{{ implode(',', array_map(fn (string $ext): string => '.'.$ext, $field->validation['extensions'] ?? ['pdf','docx'])) }}" @endif
                @required($field->required) aria-describedby="{{ $id }}-help {{ $id }}-error">
        @endif
        @if($field->description)<small id="{{ $id }}-help" class="ek-form__help">{{ $field->description }}</small>@endif
        @error($errorKey,'form_'.$form->id)<small id="{{ $id }}-error" class="ek-form__error">{{ $message }}</small>@enderror
    </div>
@endforeach
</div>
