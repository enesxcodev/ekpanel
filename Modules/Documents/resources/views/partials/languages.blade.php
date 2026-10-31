@if ($activeLanguages->isNotEmpty())
    <div class="{{ ($asCard ?? true) ? 'card mb-4' : 'btn-group' }}">
        @if ($asCard ?? true)
            <div class="card-header"><h3 class="card-title">Diller</h3></div>
            <div class="list-group list-group-flush">
        @endif
        @foreach ($activeLanguages as $language)
            @php($details = config('languages.catalog.'.$language->code))
            <a href="{{ request()->fullUrlWithQuery(['lang' => $language->code]) }}" class="{{ ($asCard ?? true) ? 'list-group-item list-group-item-action d-flex align-items-center gap-2' : 'btn btn-outline-secondary' }} {{ $selectedLanguage?->id === $language->id ? 'active' : '' }}">
                <span class="flag flag-country-{{ $details['flag'] ?? '' }}" aria-hidden="true"></span>
                <span>{{ $details['name'] ?? $language->code }}@if (($showStatus ?? true) && ($asCard ?? true))<small class="d-block">{{ in_array($language->id, $translatedLanguageIds, true) ? 'Çeviri mevcut' : 'Çeviri eksik' }}</small>@endif</span>
            </a>
        @endforeach
        @if ($asCard ?? true)
            </div>
        @endif
    </div>
@else
    <div class="alert alert-warning mb-0">
        Aktif dil bulunamadı. Kaydetmeden önce <a href="{{ route('admin.settings.index', ['section' => 'languages']) }}">Ayarlar &gt; Diller</a> bölümünden bir dili etkinleştirin.
    </div>
@endif
