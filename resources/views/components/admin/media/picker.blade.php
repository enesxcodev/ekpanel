@props(['name', 'label' => 'Medya', 'config', 'kind' => '', 'extensions' => [], 'multiple' => false, 'value' => null, 'previewUrl' => null, 'removeName' => null, 'removeValue' => false, 'disabled' => false])
<div {{ $attributes->class(['media-picker-field']) }} data-media-field data-media-disabled="{{ $disabled ? 'true' : 'false' }}" data-media-file-endpoint="{{ $config['endpoints']['file'] }}">
    <label class="form-label">{{ $label }}
        <input type="hidden" name="{{ $name }}" value="{{ is_array($value) ? json_encode($value) : $value }}" data-media-value>
    </label>
    @if ($removeName)<input type="hidden" name="{{ $removeName }}" value="{{ $removeValue ? '1' : '0' }}" data-media-remove>@endif
    <div class="media-picker-preview" data-media-field-preview aria-live="polite">
        @if ($previewUrl && ! $removeValue)
            @if ($kind === 'image')<img src="{{ $previewUrl }}" alt="{{ $label }}">
            @else<a href="{{ $previewUrl }}" target="_blank" rel="noopener">Seçili dosyayı görüntüle</a>@endif
        @endif
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn" data-media-open data-media-kind="{{ $kind }}" data-media-extensions="{{ implode(',', $extensions) }}" data-media-multiple="{{ $multiple ? 'true' : 'false' }}" @disabled($disabled)><x-admin.icon name="photo" /> Medyadan seç</button>
        <button type="button" class="btn btn-ghost-secondary {{ $value || $previewUrl ? '' : 'd-none' }}" data-media-clear>Seçimi kaldır</button>
    </div>
</div>
<x-admin.media.modal :config="$config" />
