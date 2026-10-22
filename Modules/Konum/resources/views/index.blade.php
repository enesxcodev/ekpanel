<x-admin.layout title="Türkiye Konum">
    <div class="card mb-3">
        <div class="card-header"><h2 class="card-title">Türkiye Konum</h2></div>
        <div class="card-body">
            <p class="text-secondary">Türkiye adres referans verileri. Kaynak dökümü: 11 Mayıs 2024; güncel resmî adres kaydı garantisi taşımaz.</p>
            @unless($ready)
                <div class="alert alert-warning mb-0">Konum verisi henüz kullanıma hazır değil. Aktarım durumu: {{ ['pending' => 'Bekliyor', 'running' => 'Aktarılıyor', 'failed' => 'Başarısız', 'complete' => 'Tamamlandı'][$status->status ?? 'pending'] }}.</div>
            @else
                @if($status?->status === 'warnings')
                    <div class="alert alert-warning">Kaynakta adres ilişkileri tutarsız kayıtlar bulundu. Bu kayıtlar korunuyor ve adres seçimlerine dahil edilmiyor.</div>
                @endif
                <div class="d-flex flex-wrap gap-2">
                    @foreach($labels as $key => $label)
                        <a href="{{ route('admin.konum.index', ['entity' => $key]) }}" class="btn {{ $entity === $key ? 'btn-primary' : 'btn-outline-secondary' }}">{{ $label }}</a>
                    @endforeach
                </div>
                @if($choices !== [])
                    <form method="GET" action="{{ route('admin.konum.index') }}" class="row g-2 mt-2">
                        <input type="hidden" name="entity" value="{{ $entity }}">
                        @foreach($choices as $column => $options)
                            <div class="col-12 col-sm-6 col-lg-3">
                                <label for="konum-{{ $column }}" class="form-label">{{ ['city_id' => 'İl', 'town_id' => 'İlçe', 'district_id' => 'Ara bölge', 'neighbourhood_id' => 'Mahalle'][$column] }}</label>
                                <select id="konum-{{ $column }}" name="{{ $column }}" class="form-select">
                                    <option value="">Tümü</option>
                                    @foreach($options as $option)
                                        <option value="{{ $option->id }}" @selected(($filters[$column] ?? null) == $option->id)>{{ $option->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach
                        <div class="col-12 d-flex gap-2">
                            <button class="btn btn-primary" type="submit">Filtrele</button>
                            <a class="btn btn-link" href="{{ route('admin.konum.index', ['entity' => $entity]) }}">Temizle</a>
                        </div>
                    </form>
                @endif
            @endunless
        </div>
    </div>
    @if($ready)
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ $labels[$entity] }}</h3></div>
            <x-admin.data-table id="konum-records-table"
                :source="route('admin.konum.data', ['entity' => $entity] + $filters)"
                :options="['order' => [[1, 'asc']], 'pageLength' => 25, 'lengthMenu' => [10, 25, 50, 100]]"
                :columns="[
                    ['title' => 'Kod', 'data' => 'id', 'name' => 'id'],
                    ['title' => 'Ad', 'data' => 'location_name', 'name' => 'name'],
                    ...(in_array($entity, ['districts', 'neighbourhoods'], true) ? [['title' => 'Posta kodu', 'data' => 'zip_code', 'name' => 'zip_code']] : []),
                ]" />
        </div>
    @endif
</x-admin.layout>
