<div class="card mb-4">
    <div class="card-header"><h3 class="card-title">Diller</h3></div>
    <div class="list-group list-group-flush">
        @foreach ($languages as $language)
            @php($details = config('languages.catalog.'.$language->code))
            @if (request()->routeIs('admin.products.categories.*'))
                @php($url = isset($category) && $category->exists ? route('admin.products.categories.edit', ['category' => $category, 'lang' => $language->code]) : route('admin.products.categories.create', ['lang' => $language->code]))
            @else
                @php($url = isset($product) ? route('admin.products.edit', ['product' => $product, 'lang' => $language->code]) : route('admin.products.create', ['lang' => $language->code]))
            @endif
            <a href="{{ $url }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 {{ $selectedLanguage?->id === $language->id ? 'active' : '' }}">
                <span class="flag flag-country-{{ $details['flag'] ?? '' }}" aria-hidden="true"></span>
                <span>{{ $details['name'] ?? $language->code }}<small class="d-block">{{ in_array($language->id, $translatedLanguageIds, true) ? 'Çeviri mevcut' : 'Henüz eklenmedi' }}</small></span>
            </a>
        @endforeach
    </div>
</div>
