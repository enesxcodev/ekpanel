@csrf
@isset($event) @method('PUT') @endisset
<input type="hidden" name="lang_id" value="{{ $selectedLanguage?->id }}">
<div class="row g-4">
    <div class="col-lg-8">
        <x-admin.form.input name="title" label="Etkinlik adı" :value="old('title', $translation?->title)" required maxlength="255" />
        <x-admin.form.input name="venue" label="Mekân" :value="old('venue', $translation?->venue)" maxlength="255" />
        <x-admin.form.textarea name="description" label="Açıklama" :value="old('description', $translation?->description)" maxlength="20000" />

        @if(!isset($event) || $selectedLanguage?->code === 'tr')
            <x-admin.form.input name="starts_at" label="Başlangıç" type="datetime-local" :value="old('starts_at', isset($event) ? $event->starts_at?->format('Y-m-d\\TH:i') : null)" required />
            <x-admin.form.input name="ends_at" label="Bitiş" type="datetime-local" :value="old('ends_at', isset($event) ? $event->ends_at?->format('Y-m-d\\TH:i') : null)" />
            <x-admin.form.input name="capacity" label="Kontenjan" type="number" :value="old('capacity', $event?->capacity)" min="1" />
            <x-admin.form.boolean name="is_active" label="Yayında" :value="$event?->is_active ?? true" />
        @elseif(isset($event))
            <div class="card card-body bg-light mt-3">
                <h3 class="h5">Ortak etkinlik bilgileri</h3>
                <dl class="row mb-0">
                    <dt class="col-sm-4">Başlangıç</dt><dd class="col-sm-8">{{ $event->starts_at?->format('d.m.Y H:i') }}</dd>
                    <dt class="col-sm-4">Bitiş</dt><dd class="col-sm-8">{{ $event->ends_at?->format('d.m.Y H:i') ?? '—' }}</dd>
                    <dt class="col-sm-4">Kontenjan</dt><dd class="col-sm-8">{{ $event->capacity ?? 'Sınırsız' }}</dd>
                    <dt class="col-sm-4">Yayında</dt><dd class="col-sm-8">{{ $event->is_active ? 'Evet' : 'Hayır' }}</dd>
                </dl>
                <p class="form-hint mb-0">Tarih, kontenjan ve yayın durumu Türkçe düzenlemesinden değiştirilir.</p>
            </div>
        @endif

        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary">Değişiklikleri kaydet</button>
            <a href="{{ route('admin.events.index', ['lang' => $selectedLanguage?->code]) }}" class="btn btn-link">Vazgeç</a>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card"><div class="card-body">
            <h3 class="h5">Dil</h3>
            <p class="text-secondary small">Yalnızca seçili dilin metin alanları düzenlenir.</p>
            <div class="list-group">
                @foreach($activeLanguages as $language)
                    @php($hasTranslation = isset($event) && in_array($language->id, $translatedLanguageIds, true))
                    <a class="list-group-item list-group-item-action d-flex justify-content-between {{ $selectedLanguage?->id === $language->id ? 'active' : '' }}" href="{{ isset($event) ? route('admin.events.edit', ['event' => $event->id, 'lang' => $language->code]) : route('admin.events.create', ['lang' => 'tr']) }}" @if(!isset($event) && $language->code !== 'tr') aria-disabled="true" tabindex="-1" @endif>
                        <span>{{ $language->name ?? strtoupper($language->code) }}</span>
                        <span class="small">{{ isset($event) ? ($hasTranslation ? 'Çeviri var' : 'Çeviri yok') : ($language->code === 'tr' ? 'Yeni kayıt dili' : 'Türkçe kayıttan sonra eklenir') }}</span>
                    </a>
                @endforeach
            </div>
        </div></div>
    </div>
</div>
