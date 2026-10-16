<x-admin.layout title="Ayarlar">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <p class="text-secondary mb-0">Düzenlemek istediğiniz bölümü seçin. Her bölüm ayrı kaydedilir.</p>
        @can('manage-setting-definitions')
            <a href="{{ route('admin.settings.configuration.index') }}" class="btn btn-outline-primary"><x-admin.icon name="settings" class="me-2" />Ayar yapılandırması</a>
        @endcan
    </div>
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif
    @php($activeSectionId = (int) old('_section', request('section', $sections->first()?->id)))
    @if ($sections->isNotEmpty())
        <ul class="nav nav-tabs flex-nowrap overflow-x-auto mb-3" id="settings-tabs" role="tablist">
            @foreach ($sections as $section)
                @php($active = $activeSectionId === $section->id)
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $active ? 'active' : '' }}" id="settings-tab-{{ $section->id }}" data-bs-toggle="tab" data-bs-target="#settings-panel-{{ $section->id }}" type="button" role="tab" aria-controls="settings-panel-{{ $section->id }}" aria-selected="{{ $active ? 'true' : 'false' }}">
                        {{ $section->label }}
                    </button>
                </li>
            @endforeach
        </ul>
        <div class="tab-content" id="settings-tab-content">
        @foreach ($sections as $section)
            @php($active = $activeSectionId === $section->id)
            <div class="tab-pane fade {{ $active ? 'show active' : '' }}" id="settings-panel-{{ $section->id }}" role="tabpanel" aria-labelledby="settings-tab-{{ $section->id }}" tabindex="0">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.settings.update', $section) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="_section" value="{{ $section->id }}">
                            <fieldset @disabled(! auth('admin')->user()->can('update', $section))>
                                @forelse ($section->definitions as $definition)
                                    <x-admin.settings.field :definition="$definition" :field="$fields[$definition->id]" />
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
            </div>
        @endforeach
        </div>
    @else
        <div class="card"><div class="card-body text-secondary">Görüntüleyebileceğiniz bir ayar bölümü bulunmuyor.</div></div>
    @endif
</x-admin.layout>
