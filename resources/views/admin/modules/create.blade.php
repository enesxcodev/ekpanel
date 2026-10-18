<x-admin.layout title="Yeni Modül Ekle">
    <div class="d-flex gap-3 mb-4 align-items-center">
        <a href="{{ route('admin.modules.index') }}" class="btn">Kurulu Modüller</a>
        <form method="GET" class="d-flex gap-2 flex-grow-1">
            <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Modül ara" aria-label="Modül ara">
            <button class="btn">Ara</button>
        </form>
    </div>
    @if (! config('module-distribution.catalog_url'))
        <div class="alert alert-info">Modül kataloğu henüz bağlanmadı. Katalog adresi yapılandırıldığında modüller burada görünecek.</div>
    @elseif ($error)
        <div class="alert alert-danger" role="alert">{{ $error }}</div>
    @endif
    @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('admin.modules.store') }}">
        @csrf
        <div class="row g-3">
            @forelse ($modules as $entry)
                @php($installed = \Nwidart\Modules\Facades\Module::find($entry['id']))
                <div class="col-md-6 col-xl-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <label class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" name="modules[]" value="{{ $entry['id'] }}" @disabled($installed)>
                                <span class="form-check-label fw-bold">{{ $entry['name'] }}</span>
                            </label>
                            <div class="text-secondary mb-2">{{ $entry['version'] }} · {{ $entry['author'] }}</div>
                            <p>{{ $entry['description'] }}</p>
                            @if ($installed)<span class="badge bg-secondary-lt">Kurulu · {{ $installed->isEnabled() ? 'Etkin' : 'Devre dışı' }}</span>@endif
                        </div>
                        <div class="card-footer"><a href="{{ route('admin.modules.show', $entry['id']) }}">Detayları incele</a></div>
                    </div>
                </div>
            @empty
                @if (config('module-distribution.catalog_url') && ! $error)
                    <div class="col-12"><p class="text-secondary">Aramanıza uygun modül bulunamadı.</p></div>
                @endif
            @endforelse
        </div>
        @if (collect($modules)->contains(fn (array $entry): bool => \Nwidart\Modules\Facades\Module::find($entry['id']) === null))
            <button class="btn btn-primary mt-4">Seçilen modülleri kur</button>
        @endif
    </form>
</x-admin.layout>
