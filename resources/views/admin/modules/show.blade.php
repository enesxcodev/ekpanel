<x-admin.layout :title="$entry['name']">
    <div class="card">
        <div class="card-body">
            <h2 class="card-title">{{ $entry['name'] }} <span class="badge bg-blue-lt">{{ $entry['version'] }}</span></h2>
            <p>{{ $entry['description'] }}</p>
            <p class="text-secondary">{{ $entry['author'] }}</p>
            <dl class="row mb-0">
                <dt class="col-sm-3">PHP</dt><dd class="col-sm-9">{{ $entry['requires']['php'] }}</dd>
                <dt class="col-sm-3">Laravel</dt><dd class="col-sm-9">{{ $entry['requires']['laravel'] }}</dd>
                <dt class="col-sm-3">Gerekli modüller</dt>
                <dd class="col-sm-9">
                    @forelse ($entry['requires']['modules'] as $id => $version)
                        <span class="badge bg-secondary-lt">{{ $id }} {{ $version }}</span>
                    @empty
                        Yok
                    @endforelse
                </dd>
            </dl>
        </div>
        <div class="card-footer"><a href="{{ route('admin.modules.create') }}" class="btn">Kataloğa dön</a></div>
    </div>
</x-admin.layout>
