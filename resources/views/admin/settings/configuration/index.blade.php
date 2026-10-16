<x-admin.layout title="Ayar yapılandırması">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <p class="text-secondary mb-0">Bölümleri ve alanları yönetin. Sistem alanlarının tanımları korunur.</p>
        <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-secondary">Ayarlara dön</a>
    </div>
    @if ($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
    <div class="card mb-3">
        <div class="card-header"><h2 class="card-title">Bölüm ekle</h2></div>
        <form method="POST" action="{{ route('admin.settings.sections.store') }}">
            @csrf
            <input type="hidden" name="_form" value="new-section">
            <div class="card-body row g-3">
                <div class="col-md-4"><x-admin.form.input name="key" id="new-section-key" label="Bölüm anahtarı" help="Örnek: contact. Oluşturulduktan sonra değiştirilemez." required maxlength="80" :use-old="old('_form') === 'new-section'" /></div>
                <div class="col-md-4"><x-admin.form.input name="label" id="new-section-label" label="Bölüm adı" required maxlength="150" :use-old="old('_form') === 'new-section'" /></div>
                <div class="col-md-2"><x-admin.form.input name="sort_order" id="new-section-order" label="Sıralama" type="number" value="0" min="0" max="100000" required :use-old="old('_form') === 'new-section'" /></div>
                <div class="col-md-2 d-flex align-items-start pt-md-4"><button class="btn btn-primary" type="submit">Bölüm ekle</button></div>
            </div>
        </form>
    </div>
    <div class="accordion" id="settings-configuration-accordion">
        @foreach ($sections as $section)
            @php($opened = (int) request('section', $sections->first()?->id) === $section->id || old('_form') === 'section-'.$section->id)
            <div class="accordion-item">
                <h2 class="accordion-header" id="configuration-heading-{{ $section->id }}">
                    <button class="accordion-button {{ $opened ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#configuration-section-{{ $section->id }}" aria-expanded="{{ $opened ? 'true' : 'false' }}" aria-controls="configuration-section-{{ $section->id }}">
                        {{ $section->label }} <span class="badge bg-secondary-lt ms-2">{{ $section->definitions->count() }} alan</span>
                        <span class="accordion-button-toggle"><x-admin.icon name="chevron-down" /></span>
                    </button>
                </h2>
                <div id="configuration-section-{{ $section->id }}" class="accordion-collapse collapse {{ $opened ? 'show' : '' }}" aria-labelledby="configuration-heading-{{ $section->id }}" data-bs-parent="#settings-configuration-accordion">
                    <div class="accordion-body">
                        <form method="POST" action="{{ route('admin.settings.sections.update', $section) }}" class="row g-3">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="_form" value="section-{{ $section->id }}">
                            <div class="col-md-4"><x-admin.form.input name="key" :id="'section-key-'.$section->id" label="Anahtar" :value="$section->key" readonly :use-old="false" /></div>
                            <div class="col-md-4"><x-admin.form.input name="label" :id="'section-label-'.$section->id" label="Bölüm adı" :value="$section->label" required maxlength="150" :use-old="old('_form') === 'section-'.$section->id" /></div>
                            <div class="col-md-2"><x-admin.form.input name="sort_order" :id="'section-order-'.$section->id" label="Sıralama" type="number" :value="$section->sort_order" min="0" max="100000" required :use-old="old('_form') === 'section-'.$section->id" /></div>
                            <div class="col-md-2 pt-md-4"><button class="btn btn-outline-primary" type="submit">Güncelle</button></div>
                        </form>
                        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
                            <h3 class="mb-0">Alanlar</h3>
                            <a class="btn btn-primary" href="{{ route('admin.settings.definitions.create', ['section' => $section->id]) }}">+ Alan ekle</a>
                        </div>
                        <div class="list-group mb-3">
                            @forelse ($section->definitions as $definition)
                                <div class="list-group-item d-flex flex-wrap align-items-center gap-3">
                                    <div class="flex-fill">
                                        <div class="fw-medium">{{ $definition->label }} @if ($definition->is_system)<span class="badge bg-azure-lt ms-1">Sistem</span>@endif</div>
                                        <div class="text-secondary small">{{ $definition->key }} · {{ $definition->input_type->label() }} · Sıra: {{ $definition->sort_order }}</div>
                                    </div>
                                    @unless ($definition->is_system)
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.settings.definitions.edit', $definition) }}">Düzenle</a>
                                        <form method="POST" action="{{ route('admin.settings.definitions.destroy', $definition) }}" data-confirm="Alan ve kayıtlı değeri silinsin mi?">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Sil</button>
                                        </form>
                                    @endunless
                                </div>
                            @empty
                                <div class="list-group-item text-secondary">Henüz alan eklenmedi.</div>
                            @endforelse
                        </div>
                        @if ($section->definitions->isEmpty())
                            <form method="POST" action="{{ route('admin.settings.sections.destroy', $section) }}" data-confirm="Boş bölüm silinsin mi?">
                                @csrf @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm" type="submit">Bölümü sil</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</x-admin.layout>
