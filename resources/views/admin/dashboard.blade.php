<x-admin.layout :title="$fullPageList ? 'En Çok Ziyaret Edilen Sayfalar' : 'Dashboard'">
    <x-slot:topbar>
        <x-admin.topbar />
    </x-slot:topbar>
    @if (! $fullPageList)
        <x-slot:description>Sitenizin genel durumunu takip edin.</x-slot:description>
        <x-slot:pageActions>
            <button type="button" class="btn btn-outline-success" data-widget-toggle aria-expanded="false" aria-controls="dashboard-widget-manager"><x-admin.icon name="settings" /> Widget’ları Yönet</button>
        </x-slot:pageActions>
    @endif

    @php
        $visitChange = $visitsThisMonth - $visitsLastMonth;
        $submissionChange = $submissionsThisMonth - $submissionsLastMonth;
        $visitPercent = $visitsLastMonth > 0 ? round(($visitChange / $visitsLastMonth) * 100) : null;
        $submissionPercent = $submissionsLastMonth > 0 ? round(($submissionChange / $submissionsLastMonth) * 100) : null;
        $pages = $fullPageList ? $data['popularPages'] : array_slice($data['popularPages'], 0, 5);
    @endphp

    @if (! $fullPageList)
        @include('admin.dashboard.widgets', ['widgetDashboard' => $widgetDashboard])
        <div class="row row-cards mb-3">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="avatar avatar-lg bg-green-lt text-green"><x-admin.icon name="list" /></span>
                        <div class="flex-fill">
                            <div class="subheader">Bu Ay Ziyaret</div>
                            <div class="h1 mb-1">{{ number_format($visitsThisMonth, 0, ',', '.') }}</div>
                            <div class="text-secondary">
                                @if ($visitPercent === null)
                                    <span class="badge bg-green-lt">{{ $visitsThisMonth > 0 ? 'Yeni' : '0%' }}</span>
                                @else
                                    <span class="badge {{ $visitChange >= 0 ? 'bg-green-lt text-green' : 'bg-red-lt text-red' }}">{{ $visitChange >= 0 ? '↗' : '↘' }} %{{ abs($visitPercent) }}</span>
                                @endif
                                <span class="ms-2">Geçen ay {{ number_format($visitsLastMonth, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @if ($canViewSubmissions)
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="avatar avatar-lg bg-blue-lt text-blue"><x-admin.icon name="file-text" /></span>
                        <div class="flex-fill">
                            <div class="subheader">Yeni Form Başvuruları</div>
                            <div class="h1 mb-1">{{ number_format($submissionsThisMonth, 0, ',', '.') }}</div>
                            <div class="text-secondary">
                                @if ($submissionPercent === null)
                                    <span class="badge bg-green-lt">{{ $submissionsThisMonth > 0 ? 'Yeni' : '0%' }}</span>
                                @else
                                    <span class="badge {{ $submissionChange >= 0 ? 'bg-green-lt text-green' : 'bg-red-lt text-red' }}">{{ $submissionChange >= 0 ? '↗' : '↘' }} %{{ abs($submissionPercent) }}</span>
                                @endif
                                <span class="ms-2">Geçen ay {{ number_format($submissionsLastMonth, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <a class="btn btn-outline-primary" href="{{ route('admin.forms.submissions.index') }}">Başvuruları Gör <x-admin.icon name="link" /></a>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <form method="GET" action="{{ route('admin.dashboard') }}" class="card card-sm mb-3">
            <div class="card-body d-flex flex-wrap align-items-center gap-2">
                <label class="form-label mb-0" for="dashboard-year">Rapor yılı</label>
                <select class="form-select w-auto" id="dashboard-year" name="year">
                    @foreach (range($currentYear - 1, $currentYear) as $availableYear)
                        <option value="{{ $availableYear }}" @selected($year === $availableYear)>{{ $availableYear }}</option>
                    @endforeach
                </select>
                <label class="form-label mb-0 ms-md-3" for="dashboard-device">Cihaz</label>
                <select class="form-select w-auto" id="dashboard-device" name="device">
                    <option value="all" @selected($device === 'all')>Tümü</option>
                    <option value="computer" @selected($device === 'computer')>Bilgisayar</option>
                    <option value="phone" @selected($device === 'phone')>Telefon</option>
                    <option value="other" @selected($device === 'other')>Diğer</option>
                </select>
                <button class="btn btn-primary" type="submit">Uygula</button>
            </div>
        </form>

        <div class="row row-cards mb-3">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header"><h3 class="card-title">Yıllık Toplam Ziyaretler · {{ $year }}</h3></div>
                    <div class="card-body">
                        <div class="d-flex justify-content-end gap-3 small mb-2"><span><span class="text-primary">●</span> Misafir</span><span><span class="text-green">●</span> Kullanıcı</span></div>
                        <div class="analytics-chart" data-chart="visitors" role="img" aria-label="{{ $year }} yılı aylık misafir ve kullanıcı ziyaretleri"><svg viewBox="0 0 720 270" class="w-100" aria-hidden="true"></svg></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header"><h3 class="card-title">Yıllık Cihaz Kullanımları · {{ $year }}</h3></div>
                    <div class="card-body">
                        <div class="d-flex justify-content-end flex-wrap gap-3 small mb-2"><span><span class="text-primary">●</span> Bilgisayar</span><span><span class="text-info">●</span> Telefon</span><span><span class="text-green">●</span> Diğer</span></div>
                        <div class="analytics-chart" data-chart="devices" role="img" aria-label="{{ $year }} yılı aylık cihaz kullanımları"><svg viewBox="0 0 720 270" class="w-100" aria-hidden="true"></svg></div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div><h2 class="h3 mb-1">En Çok Ziyaret Edilen Sayfalar</h2><div class="text-secondary">{{ $year }} yılına ait ilk 20 sayfa</div></div>
            <a class="btn btn-outline-secondary" href="{{ route('admin.dashboard', ['year' => $year, 'device' => $device]) }}">Dashboard'a dön</a>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">En Çok Ziyaret Edilen Sayfalar</h3>
            @if (! $fullPageList)
                <div class="card-actions"><a href="{{ route('admin.dashboard.pages', ['year' => $year, 'device' => $device]) }}">Tüm 20 sayfayı gör <x-admin.icon name="link" /></a></div>
            @endif
        </div>
        <div class="table-responsive">
            <table class="table card-table table-vcenter">
                <thead><tr><th>Sayfa</th><th class="text-end">Ziyaret</th><th class="text-end">Trend</th></tr></thead>
                <tbody>
                    @forelse ($pages as $page)
                        @php
                            $trendValues = array_values($page['trend']);
                            $trendMax = max(1, ...$trendValues);
                            $trendPoints = collect($trendValues)->map(fn (int $value, int $index): string => (string) (4 + ($index * 88 / 11)).','.(28 - ($value * 24 / $trendMax)))->implode(' ');
                            $pageUrl = $page['path'] === '/' ? url('/') : url(ltrim($page['path'], '/'));
                        @endphp
                        <tr>
                            <td><a href="{{ $pageUrl }}" target="_blank" rel="noopener">{{ $page['path'] }}</a></td>
                            <td class="text-end">{{ number_format($page['visits'], 0, ',', '.') }}</td>
                            <td class="text-end"><svg width="104" height="32" viewBox="0 0 104 32" role="img" aria-label="Aylık ziyaret trendi"><polyline points="{{ $trendPoints }}" fill="none" stroke="var(--tblr-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg></td>
                        </tr>
                    @empty
                        <tr><td class="text-secondary" colspan="3">Bu yıl için henüz ziyaret verisi yok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @unless ($fullPageList)
        <script type="application/json" data-analytics-chart-data>{!! json_encode(['months' => $data['months'], 'visitors' => $data['visitors'], 'devices' => $data['devices']], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
        @push('scripts')
            <script>
                (() => {
                    const source = document.querySelector('[data-analytics-chart-data]');
                    if (!source) return;
                    const data = JSON.parse(source.textContent);
                    const colors = { guest: '#206bc4', user: '#2fb344', computer: '#206bc4', phone: '#4299e1', other: '#2fb344' };
                    const labels = { guest: 'Misafir', user: 'Kullanıcı', computer: 'Bilgisayar', phone: 'Telefon', other: 'Diğer' };
                    const ns = 'http://www.w3.org/2000/svg';
                    const element = (name, attributes = {}) => {
                        const node = document.createElementNS(ns, name);
                        Object.entries(attributes).forEach(([key, value]) => node.setAttribute(key, value));
                        return node;
                    };
                    document.querySelectorAll('[data-chart]').forEach((container) => {
                        const type = container.dataset.chart;
                        const series = data[type];
                        const svg = container.querySelector('svg');
                        const width = 720, height = 270, left = 52, right = 8, top = 12, bottom = 34;
                        const plotHeight = height - top - bottom;
                        const totals = data.months.map((_, index) => Object.values(series).reduce((sum, values) => sum + Number(values[index + 1] || 0), 0));
                        const max = Math.max(1, ...totals);
                        const step = Math.max(1, Math.ceil(max / 5 / 1000) * 1000);
                        const ceiling = Math.ceil(max / step) * step;
                        for (let tick = 0; tick <= ceiling; tick += step) {
                            const y = top + plotHeight - (tick / ceiling * plotHeight);
                            svg.append(element('line', { x1: left, x2: width - right, y1: y, y2: y, stroke: 'var(--tblr-border-color)', 'stroke-dasharray': '3 4' }));
                            const label = element('text', { x: left - 7, y: y + 4, 'text-anchor': 'end', fill: 'var(--tblr-secondary)', 'font-size': '11' });
                            label.textContent = tick.toLocaleString('tr-TR');
                            svg.append(label);
                        }
                        const groupWidth = (width - left - right) / 12;
                        const barWidth = Math.min(30, groupWidth * 0.58);
                        data.months.forEach((month, index) => {
                            let stack = 0;
                            Object.entries(series).forEach(([key, values]) => {
                                const value = Number(values[index + 1] || 0);
                                if (!value) return;
                                const barHeight = value / ceiling * plotHeight;
                                const x = left + index * groupWidth + (groupWidth - barWidth) / 2;
                                const y = top + plotHeight - ((stack + value) / ceiling * plotHeight);
                                const bar = element('rect', { x, y, width: barWidth, height: Math.max(1, barHeight), fill: colors[key], rx: '2' });
                                const title = element('title');
                                title.textContent = `${month} · ${labels[key]}: ${value.toLocaleString('tr-TR')}`;
                                bar.append(title);
                                svg.append(bar);
                                stack += value;
                            });
                            const label = element('text', { x: left + index * groupWidth + groupWidth / 2, y: height - 8, 'text-anchor': 'middle', fill: 'var(--tblr-secondary)', 'font-size': '11' });
                            label.textContent = month;
                            svg.append(label);
                        });
                    });
                })();
            </script>
        @endpush
    @endunless
</x-admin.layout>
