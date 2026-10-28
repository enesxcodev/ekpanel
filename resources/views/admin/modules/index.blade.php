<x-admin.layout title="Kurulu Modüller">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <p class="text-secondary mb-0">Bu kurulumda bulunan modülleri yönetin.</p>
        <a href="{{ route('admin.modules.create') }}" class="btn btn-primary">Yeni Modül Ekle</a>
    </div>
    @if ($installation ?? null)
        @php
            $installationStatuses = ['queued' => 'Sırada', 'running' => 'İşleniyor', 'succeeded' => 'Tamamlandı', 'failed' => 'Başarısız', 'skipped' => 'Atlandı'];
            $installationPercentage = max(0, min($installation['status'] === 'succeeded' ? 100 : 99, (int) $installation['percentage']));
        @endphp
        <div class="card mb-4" data-module-installation data-status="{{ $installation['status'] }}" data-status-url="{{ route('admin.modules.installations.show', $installation['id']) }}">
            <div class="card-header d-flex justify-content-between">
                <h2 class="card-title">{{ ($installation['action'] ?? 'install') === 'enable' ? 'Modül etkinleştirme' : 'Modül kurulumu' }}</h2>
                <span class="badge bg-secondary-lt" data-installation-status>{{ $installationStatuses[$installation['status']] ?? $installation['status'] }}</span>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span data-installation-stage>{{ $installation['stage'] }}</span>
                    <strong data-installation-percentage>%{{ $installationPercentage }}</strong>
                </div>
                <div class="progress" role="progressbar" aria-label="Modül işlemi ilerlemesi" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $installationPercentage }}" data-installation-progress>
                    <div class="progress-bar" style="width: {{ $installationPercentage }}%" data-installation-bar></div>
                </div>
                <p class="text-secondary mt-2 mb-0" data-installation-wait @if ($installation['status'] !== 'queued') hidden @endif>İşlem sırada; kuyruk çalışanının işi alması bekleniyor.</p>
                <p class="text-danger mt-2 mb-0" data-installation-error @if (empty($installation['error'])) hidden @endif>{{ $installation['error'] ?? '' }}</p>
                <p class="text-warning mt-2 mb-0" data-installation-network hidden role="status"></p>
                <button type="button" class="btn btn-sm mt-2" data-installation-retry hidden>Şimdi yeniden kontrol et</button>
                <small class="text-secondary d-block mt-2">Son güncelleme: <span data-installation-updated>{{ $installation['updated_at'] }}</span></small>
            </div>
            <div class="list-group list-group-flush" data-installation-results>
                @foreach ($installation['results'] as $result)
                    <div class="list-group-item">
                        <strong>{{ $result['name'] }}</strong>
                        <span class="ms-2">{{ $installationStatuses[$result['status']] ?? $result['status'] }}</span>
                        <div class="text-secondary">{{ $result['stage'] ?? '' }}</div>
                        @if (! empty($result['error']))<div class="text-danger">{{ $result['error'] }}</div>@endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif
    <div class="row g-3">
        @forelse ($modules as $module)
            <div class="col-md-6 col-xl-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h2 class="card-title mb-0">{{ $module->getName() }}</h2>
                            <span class="badge {{ $module->isEnabled() ? 'bg-success-lt' : 'bg-secondary-lt' }}">{{ $module->isEnabled() ? 'Etkin' : 'Devre dışı' }}</span>
                        </div>
                        <div class="text-secondary mb-2">{{ $module->get('version', 'Sürüm belirtilmemiş') }}</div>
                        <p class="mb-0">{{ $module->getDescription() }}</p>
                    </div>
                    <div class="card-footer d-flex gap-2 flex-wrap">
                        <a href="{{ route('admin.modules.features.show', $module->getName()) }}" class="btn btn-sm btn-primary">Detay</a>
                        <form method="POST" action="{{ route($module->isEnabled() ? 'admin.modules.disable' : 'admin.modules.enable', $module->getName()) }}">
                            @csrf
                            <button class="btn btn-sm">{{ $module->isEnabled() ? 'Devre dışı bırak' : 'Etkinleştir' }}</button>
                        </form>
                        <form method="POST" action="{{ route('admin.modules.destroy', $module->getName()) }}" data-confirm-modal data-confirm-title="Modülü kaldır" data-confirm-message="Modül kaldırılacak. İçerik verileri korunacaktır. Devam edilsin mi?" data-confirm-button="Kaldır">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Kaldır</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12"><div class="empty"><p class="empty-title">Kurulu modül bulunmuyor.</p></div></div>
        @endforelse
    </div>
    @if ($history !== [])
        <div class="card mt-4">
            <div class="card-header"><h2 class="card-title">Son işlemler</h2></div>
            <div class="list-group list-group-flush">
                @foreach ($history as $operation)
                    <div class="list-group-item">
                        <div class="d-flex gap-2 align-items-center flex-wrap">
                            <strong>{{ $operation['module'] }}</strong>
                            <span>{{ ['install' => 'Kurulum', 'enable' => 'Etkinleştirme', 'disable' => 'Devre dışı bırakma', 'remove' => 'Kaldırma'][$operation['action']] ?? $operation['action'] }}</span>
                            <span class="badge {{ $operation['status'] === 'success' ? 'bg-success-lt' : 'bg-danger-lt' }}">{{ $operation['status'] === 'success' ? 'Tamamlandı' : 'Başarısız' }}</span>
                            <small class="text-secondary">{{ $operation['at'] }}</small>
                        </div>
                        @isset($operation['error'])<p class="text-danger mb-0 mt-1">{{ $operation['error'] }}</p>@endisset
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</x-admin.layout>
