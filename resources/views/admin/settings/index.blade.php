<x-admin.layout title="Ayarlar">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <p class="text-secondary mb-0">Düzenlemek istediğiniz bölümü seçin. Her bölüm ayrı kaydedilir.</p>
        {{-- @can('forms.view')<a href="{{ route('admin.forms.index') }}" class="btn btn-outline-primary">Form yönetimi</a>@endcan --}}
        @can('manage-setting-definitions')
            <a href="{{ route('admin.settings.configuration.index') }}" class="btn btn-outline-primary"><x-admin.icon name="settings" class="me-2" />Ayar yapılandırması</a>
        @endcan
    </div>
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif
    @php
        $requestedSection = old('_section', request('section', $sections->first()?->id ?? 'languages'));
        $activeSectionId = $requestedSection === 'languages' ? 'languages' : (int) $requestedSection;
        $activeSectionId = $activeSectionId === 'languages' || $sections->contains('id', $activeSectionId) ? $activeSectionId : ($sections->first()?->id ?? 'languages');
    @endphp
    <div class="card">
      <div class="row g-0">
        <div class="col-12 col-md-3 border-end">
        <div class="card-body">
        <h2 class="subheader mb-3">Ayar bölümleri</h2>
        <div class="nav nav-pills flex-column gap-1" id="settings-tabs" role="tablist" aria-orientation="vertical">
            @foreach ($sections as $section)
                @php
                    $active = $activeSectionId === $section->id;
                    $sectionIcon = match ($section->key) {
                        'site' => 'briefcase',
                        'seo' => 'search',
                        'contact' => 'users',
                        'social' => 'link',
                        'scripts' => 'code',
                        'mail' => 'mail',
                        default => 'settings-cog',
                    };
                @endphp
                    <button class="nav-link justify-content-start gap-2 {{ $active ? 'active' : '' }}" id="settings-tab-{{ $section->id }}" data-bs-toggle="tab" data-bs-target="#settings-panel-{{ $section->id }}" type="button" role="tab" aria-controls="settings-panel-{{ $section->id }}" aria-selected="{{ $active ? 'true' : 'false' }}">
                        <x-admin.icon :name="$sectionIcon" class="flex-shrink-0" />
                        {{ $section->label }}
                    </button>
            @endforeach
            <button class="nav-link justify-content-start gap-2 {{ $activeSectionId === 'languages' ? 'active' : '' }}" id="settings-tab-languages" data-bs-toggle="tab" data-bs-target="#settings-panel-languages" type="button" role="tab" aria-controls="settings-panel-languages" aria-selected="{{ $activeSectionId === 'languages' ? 'true' : 'false' }}">
                <x-admin.icon name="settings-cog" class="flex-shrink-0" />Diller
            </button>
        </div>
        </div>
        </div>
        <div class="col-12 col-md-9">
        <div class="tab-content" id="settings-tab-content">
            <div class="tab-pane fade {{ $activeSectionId === 'languages' ? 'show active' : '' }}" id="settings-panel-languages" role="tabpanel" aria-labelledby="settings-tab-languages" tabindex="0">
                @include('admin.settings.languages', ['languages' => $languages, 'languageCatalog' => $languageCatalog, 'availableLanguages' => $availableLanguages])
            </div>
        @foreach ($sections as $section)
            @php
                $active = $activeSectionId === $section->id;
            @endphp
            <div class="tab-pane fade {{ $active ? 'show active' : '' }}" id="settings-panel-{{ $section->id }}" role="tabpanel" aria-labelledby="settings-tab-{{ $section->id }}" tabindex="0">
                    <div class="card-body">
                        <h2 class="mb-4">{{ $section->label }}</h2>
                        <form method="POST" action="{{ route('admin.settings.update', $section) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="_section" value="{{ $section->id }}">
                            <fieldset @disabled(! auth('admin')->user()->can('update', $section))>
                                @forelse ($sectionGroups[$section->id] as $group)
                                    <section class="mb-4">
                                        @if ($group['label'])<h3 class="mb-3">{{ $group['label'] }}</h3>@endif
                                        <div class="row">
                                            @foreach ($group['fields'] as $item)
                                                @php
                                                    $definition = $item['definition'];
                                                @endphp
                                                <div class="{{ $item['class'] }}">
                                                    <x-admin.settings.field :definition="$definition" :field="$fields[$definition->id]" :media-config="$mediaConfig" />
                                                </div>
                                            @endforeach
                                        </div>
                                    </section>
                                @empty
                                    <p class="text-secondary mb-0">Bu bölümde henüz alan bulunmuyor.</p>
                                @endforelse
                                @if ($section->definitions->isNotEmpty())
                                    @can('update', $section)<button class="btn btn-primary" type="submit">{{ $section->label }} ayarlarını kaydet</button>@endcan
                                @endif
                            </fieldset>
                            @cannot('update', $section)<p class="form-hint mt-3">Bu bölümü görüntüleyebilirsiniz; düzenleme izniniz bulunmuyor.</p>@endcannot
                        </form>
                    </div>
            </div>
        @endforeach
        </div>
        </div>
      </div>
    </div>
</x-admin.layout>
