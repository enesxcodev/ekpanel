@props(['name', 'label', 'id' => null, 'errorKey' => null, 'help' => null, 'required' => false, 'currentUrl' => null, 'isImage' => false, 'removeName' => null])
@php($id = $id ?? 'field-'.str($name)->slug())
@php($errorKey = $errorKey ?? $name)
<x-admin.form.field :name="$name" :id="$id" :label="null" :help="$help" :required="$required" :error-key="$errorKey">
    <div class="d-flex align-items-center justify-content-between gap-3 mb-2">
        <label for="{{ $id }}" class="form-label mb-0 {{ $required && ! $currentUrl ? 'required' : '' }}">{{ $label }}</label>
        @if ($isImage && $currentUrl)
            <a href="{{ $currentUrl }}" target="_blank" rel="noopener" aria-label="{{ $label }} görselini yeni sekmede aç">
                <img src="{{ $currentUrl }}" class="rounded border object-fit-contain" width="35" height="48" alt="{{ $label }} önizlemesi">
            </a>
        @endif
    </div>
    @if ($currentUrl)
        @unless ($isImage)<div class="mb-2"><a href="{{ $currentUrl }}" target="_blank" rel="noopener">Mevcut dosyayı görüntüle</a></div>@endunless
    @endif
    <input {{ $attributes->class(['form-control', 'is-invalid' => $errors->has($errorKey)]) }} type="file" id="{{ $id }}" name="{{ $name }}" @required($required && ! $currentUrl)>
    @if ($currentUrl && $removeName)
        <label class="form-check mt-2">
            <input class="form-check-input" type="checkbox" name="{{ $removeName }}" value="1">
            <span class="form-check-label">Mevcut dosyayı kaldır</span>
        </label>
    @endif
</x-admin.form.field>
