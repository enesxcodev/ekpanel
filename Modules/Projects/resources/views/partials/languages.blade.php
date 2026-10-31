<div class="card mb-4">
    <div class="card-header"><h3 class="card-title">Diller</h3></div>
    <div class="list-group list-group-flush">
        @foreach ($activeLanguages as $language)
            @php($hasTranslation = $record !== null && in_array($language->id, $translatedLanguageIds, true))
            @if($record !== null || $language->code === 'tr')
                <a href="{{ $record !== null ? ($recordType === 'project' ? route('admin.projects.edit', ['project' => $record->id, 'lang' => $language->code]) : route('admin.projects.categories.edit', ['category' => $record->id, 'lang' => $language->code])) : ($recordType === 'project' ? route('admin.projects.create', ['lang' => 'tr']) : route('admin.projects.categories.create', ['lang' => 'tr'])) }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between gap-2 {{ $selectedLanguage?->id === $language->id ? 'active' : '' }}">
                    <span>{{ config('languages.catalog.'.$language->code.'.name', strtoupper($language->code)) }}</span>
                    <small>{{ $record !== null ? ($hasTranslation ? 'Çeviri mevcut' : 'Henüz eklenmedi') : 'Yeni kayıt dili' }}</small>
                </a>
            @else
                <div class="list-group-item d-flex align-items-center justify-content-between gap-2 text-secondary"><span>{{ config('languages.catalog.'.$language->code.'.name', strtoupper($language->code)) }}</span><small>Türkçe kayıttan sonra eklenir</small></div>
            @endif
        @endforeach
        @if($activeLanguages->isEmpty())<div class="list-group-item text-warning">Aktif dil yok. <a href="{{ route('admin.settings.index', ['section' => 'languages']) }}">Ayarlar &gt; diller</a></div>@endif
    </div>
</div>
