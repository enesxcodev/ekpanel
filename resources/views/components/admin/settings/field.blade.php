@props(['definition', 'field'])
<x-admin.form.dynamic-field
    :definition="$definition->fieldDefinition()"
    :field="$field"
    :name="'settings['.$definition->id.']'"
    :error-key="'settings.'.$definition->id"
    :id="'setting-'.$definition->id"
    :remove-name="'remove['.$definition->id.']'"
/>
