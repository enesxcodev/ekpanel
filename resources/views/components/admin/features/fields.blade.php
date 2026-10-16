@props(['form', 'fields'])
@if ($form['fields'] !== [])
    <fieldset class="mt-4" data-feature-fields>
        <legend class="h3">{{ $form['label'] }}</legend>
        @foreach ($form['fields'] as $key => $definition)
            <x-admin.form.dynamic-field
                :definition="$definition" :field="$fields[$key]"
                :name="'extra_fields['.$key.']'" :error-key="'extra_fields.'.$key"
                :id="'feature-'.$key" :remove-name="'remove_features['.$key.']'"
            />
        @endforeach
    </fieldset>
@endif
