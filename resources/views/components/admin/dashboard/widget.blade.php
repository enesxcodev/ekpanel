@props(['definition', 'result' => null, 'status' => 'loading'])
<article class="dashboard-widget-card card" data-widget-card="{{ $definition['id'] }}">
    <div class="dashboard-widget-controls" aria-label="Widget düzenleme kontrolleri">
        <span class="dashboard-widget-drag" aria-hidden="true"><x-admin.icon name="menu" /></span>
        <button type="button" class="btn btn-sm btn-ghost-light" data-widget-move="-1" aria-label="{{ $definition['title'] }} öne taşı"><x-admin.icon name="arrow-up" /></button>
        <button type="button" class="btn btn-sm btn-ghost-light" data-widget-move="1" aria-label="{{ $definition['title'] }} arkaya taşı"><x-admin.icon name="arrow-down" /></button>
        <button type="button" class="btn btn-sm btn-ghost-light" data-widget-remove aria-label="{{ $definition['title'] }} kaldır"><x-admin.icon name="trash" /></button>
    </div>
    <div class="card-body">
        <div class="d-flex align-items-center gap-3">
            <span class="dashboard-widget-icon"><x-admin.icon :name="$definition['icon']" /></span>
            <div class="flex-fill">
                <h3 class="dashboard-widget-title">{{ $definition['title'] }}</h3>
                @if ($status === 'ready')
                    <div class="dashboard-widget-value">{{ is_int($result['value']) ? number_format($result['value'], 0, ',', '.') : $result['value'] }}</div>
                @elseif ($status === 'error')
                    <div class="dashboard-widget-error" role="status">Veri yüklenemedi</div>
                    <button type="button" class="btn btn-sm btn-outline-light mt-2" data-widget-retry>Yeniden dene</button>
                @else
                    <div class="dashboard-widget-value" aria-label="Yükleniyor"><span class="placeholder col-5"></span></div>
                    <span class="visually-hidden" role="status">Yükleniyor</span>
                @endif
            </div>
        </div>
        @if ($status === 'ready')
            <p class="dashboard-widget-description mb-0 mt-3">{{ $result['description'] ?? '' }}</p>
            @isset ($result['url'])
                <a class="dashboard-widget-link mt-3" href="{{ $result['url'] }}">İlgili ekranı aç <span aria-hidden="true">→</span></a>
            @endisset
        @endif
    </div>
</article>
