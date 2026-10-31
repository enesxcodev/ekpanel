<div class="card mb-4">
    <div class="card-header"><h3 class="card-title">Diller</h3></div>
    <div class="list-group list-group-flush">
        @foreach ($activeLanguages as $language)
            @php($details = config('languages.catalog.'.$language->code))
            <a href="{{ request()->fullUrlWithQuery(['lang' => $language->code]) }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 {{ $selectedLanguage?->id === $language->id ? 'active' : '' }}" data-pages-language-link>
                <span class="flag flag-country-{{ $details['flag'] ?? '' }}" aria-hidden="true"></span>
                <span>{{ $details['name'] ?? $language->code }}<small class="d-block">{{ in_array($language->id, $translatedLanguageIds, true) ? 'Çeviri mevcut' : 'Henüz eklenmedi' }}</small></span>
            </a>
        @endforeach
    </div>
</div>
