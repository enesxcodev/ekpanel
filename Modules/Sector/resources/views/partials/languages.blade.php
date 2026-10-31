<div class="card mb-4">
    <div class="card-header"><h3 class="card-title">Diller</h3></div>
    @php($isEditing = isset($sector) && $sector->exists)
    <div class="list-group list-group-flush">
        @foreach ($languages as $language)
            @if ($isEditing || $language->code === 'tr')
                @php($details = config('languages.catalog.'.$language->code))
                @php($translated = in_array($language->id, $translatedLanguageIds, true) || in_array($language->id, $translatedLanguageIds))
                <a href="{{ $isEditing ? route('admin.sector.edit', ['sector' => $sector, 'lang' => $language->code]) : route('admin.sector.create', ['lang' => 'tr']) }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 {{ $selectedLanguage?->id === $language->id ? 'active' : '' }}">
                    <span class="flag flag-country-{{ $details['flag'] ?? '' }}" aria-hidden="true"></span>
                    <span>{{ $details['name'] ?? $language->code }}<small class="d-block">{{ $translated ? 'Çeviri mevcut' : 'Henüz eklenmedi' }}</small></span>
                </a>
            @endif
        @endforeach
    </div>
</div>
