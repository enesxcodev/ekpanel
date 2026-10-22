@csrf
@isset($branch) @method('PUT') @endisset

@if (! $locationReady)
    <div class="alert alert-warning" role="status">Türkiye Konum verisi hazır değil. Konum seçimi kullanılamıyor; bu alanlar boş bırakılabilir.</div>
@endif

<h3 class="h4">Genel</h3>
<div class="row">
    <div class="col-md-6"><x-admin.form.input name="name" label="Şube / bayi adı" :value="$branch->name ?? null" required maxlength="255" /></div>
    <div class="col-md-6"><x-admin.form.select name="branch_category_id" label="Kategori" :options="$categories" :value="old('branch_category_id', $branch->branch_category_id ?? null)" required placeholder="Kategori seçiniz" class="js-select2" data-placeholder="Kategori seçiniz" /></div>
    <div class="col-md-6"><x-admin.form.input name="sort_order" label="Sıralama" type="number" :value="$branch->sort_order ?? 0" min="0" max="65535" /></div>
    <div class="col-md-6"><x-admin.form.boolean name="is_active" label="Durum" :value="$branch->is_active ?? true" /></div>
</div>

<hr>
<h3 class="h4">Adres <span class="text-secondary fs-5">(isteğe bağlı)</span></h3>
@php
    $locationFields = [
        ['city_id', 'cities', 'İl', []],
        ['town_id', 'towns', 'İlçe', ['city_id']],
        ['district_id', 'districts', 'Ara bölge', ['city_id', 'town_id']],
        ['neighbourhood_id', 'neighbourhoods', 'Mahalle', ['city_id', 'town_id', 'district_id']],
        ['street_id', 'streets', 'Cadde / sokak', ['city_id', 'town_id', 'district_id', 'neighbourhood_id']],
    ];
@endphp
<div class="row" data-location-selects>
    @foreach ($locationFields as [$field, $entity, $label, $parents])
        @php
            $parentsAvailable = $parents === [] || collect($parents)->every(fn ($parent) => filled(old($parent, $branch?->{$parent})));
        @endphp
        <div class="col-md-6" data-location-field="{{ $field }}">
            <div class="mb-3">
                <label class="form-label" for="{{ $field }}">{{ $label }}</label>
                <select id="{{ $field }}" name="{{ $field }}" class="form-select" data-location-select data-entity="{{ $entity }}" data-parents="{{ implode(',', $parents) }}" data-placeholder="{{ $label }} seçiniz" @disabled(! $locationReady || ! $parentsAvailable)>
                    <option value="">{{ $label }} seçiniz</option>
                    @foreach ($locationOptions[$field] as $option)
                        <option value="{{ $option['id'] }}" @selected((string) old($field, $branch->$field ?? '') === (string) $option['id'])>{{ $option['name'] }}</option>
                    @endforeach
                </select>
                @error($field)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
        </div>
    @endforeach
    <div class="col-md-6"><x-admin.form.textarea name="address_detail" label="Adres detayı" :value="$branch->address_detail ?? null" rows="3" maxlength="5000" /></div>
</div>

<hr>
<h3 class="h4">İletişim</h3>
<div class="row">
    <div class="col-md-6"><x-admin.form.input name="phone" label="Telefon" :value="$branch->phone ?? null" maxlength="50" /></div>
    <div class="col-md-6"><x-admin.form.input name="email" label="E-posta" type="email" :value="$branch->email ?? null" maxlength="255" /></div>
    <div class="col-md-6"><x-admin.form.input name="website" label="Web sitesi" type="url" :value="$branch->website ?? null" maxlength="2048" /></div>
    <div class="col-md-6"><x-admin.form.input name="working_hours" label="Çalışma saatleri" :value="$branch->working_hours ?? null" maxlength="255" help="Örnek: Hafta içi 09.00–18.00, Cumartesi 10.00–14.00" /></div>
    <div class="col-md-6"><x-admin.form.input name="map_url" label="Harita bağlantısı (HTTPS)" type="url" :value="$branch->map_url ?? null" maxlength="2048" /></div>
    <div class="col-md-6"><x-admin.form.input name="latitude" label="Enlem" type="number" :value="$branch->latitude ?? null" step="any" min="-90" max="90" /></div>
    <div class="col-md-6"><x-admin.form.input name="longitude" label="Boylam" type="number" :value="$branch->longitude ?? null" step="any" min="-180" max="180" /></div>
</div>

<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary">{{ isset($branch) ? 'Değişiklikleri kaydet' : 'Şube / bayi ekle' }}</button>
    <a href="{{ route('admin.branches.index') }}" class="btn btn-link">Vazgeç</a>
</div>
