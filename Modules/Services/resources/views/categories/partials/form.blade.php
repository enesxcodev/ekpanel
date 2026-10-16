@csrf
@if (isset($category))
    @method('PUT')
@endif

<div class="mb-3">
    <label for="name" class="form-label">Kategori adı</label>
    <input id="name" name="name" value="{{ old('name', $category->name ?? '') }}"
           class="form-control @error('name') is-invalid @enderror" maxlength="255" required autofocus>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<x-admin.features.fields :form="$featureForm" :fields="$featureFields" />

<button type="submit" class="btn btn-primary">{{ isset($category) ? 'Değişiklikleri kaydet' : 'Kategori ekle' }}</button>
<a href="{{ route('admin.services.categories.index') }}" class="btn btn-link">Vazgeç</a>
