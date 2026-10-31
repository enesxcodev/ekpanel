<div class="card-body">
    <h2 class="mb-4">Diller</h2>
    <div class="row g-4">
        <div class="col-12 col-md-6">
            <h3>Eklenen diller <span class="badge bg-green-lt">{{ $languages->where('is_active', true)->count() }} aktif</span></h3>
            <div class="list-group list-group-flush">
                @forelse ($languages as $language)
                    @php($details = $languageCatalog[$language->code] ?? ['name' => $language->code, 'flag' => null])
                    <div class="list-group-item px-0 d-flex align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-2">
                            @if ($details['flag'])<span class="flag flag-country-{{ $details['flag'] }}" aria-hidden="true"></span>@endif
                            <div><strong>{{ $details['name'] }}</strong><div class="text-secondary small">{{ $language->code }}</div></div>
                        </div>
                        <form method="POST" action="{{ route('admin.settings.languages.update', $language) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="_section" value="languages">
                            <input type="hidden" name="is_active" value="0">
                            <label class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" data-language-status aria-label="{{ $details['name'] }} dilini aktif/pasif yap" @checked($language->is_active) @disabled(! auth('admin')->user()->can('settings.update'))>
                                <span class="form-check-label">{{ $language->is_active ? 'Aktif' : 'Pasif' }}</span>
                            </label>
                            <noscript><button type="submit" class="btn btn-sm" @disabled(! auth('admin')->user()->can('settings.update'))>Kaydet</button></noscript>
                        </form>
                    </div>
                @empty
                    <p class="text-secondary mb-0">Henüz dil eklenmedi. Sağdaki listeden dil ekleyebilirsiniz.</p>
                @endforelse
            </div>
        </div>
        <div class="col-12 col-md-6">
            <h3>Dil ekle</h3>
            <form method="POST" action="{{ route('admin.settings.languages.store') }}">
                @csrf
                <input type="hidden" name="_section" value="languages">
                <fieldset @disabled(! auth('admin')->user()->can('settings.update'))>
                    <label class="form-label" for="settings-language-code">Dil seçin</label>
                    <select id="settings-language-code" name="code" class="form-select js-select2" data-language-select required @disabled(empty($availableLanguages))>
                        <option value="">Dil arayın veya seçin</option>
                        @foreach ($availableLanguages as $code => $details)
                            <option value="{{ $code }}" data-flag="{{ $details['flag'] }}" @selected(old('code') === $code)>{{ $details['name'] }} ({{ $code }})</option>
                        @endforeach
                    </select>
                    @error('code')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    <p class="form-hint">Eklenen diller aktif olarak kaydedilir. Durumlarını soldaki listeden değiştirebilirsiniz.</p>
                    <button type="submit" class="btn btn-primary mt-3" @disabled(empty($availableLanguages))>Dil ekle</button>
                </fieldset>
                @cannot('settings.update')<p class="form-hint mt-3">Dil düzenleme izniniz bulunmuyor.</p>@endcannot
            </form>
        </div>
    </div>
</div>
