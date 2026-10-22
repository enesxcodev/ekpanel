<section data-dashboard-widgets data-data-url="{{ route('admin.dashboard.widgets.data') }}" data-preference-url="{{ route('admin.dashboard.preferences.update') }}" class="mb-4">
    <script type="application/json" data-widget-state>{!! json_encode($widgetDashboard, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
    <div class="dashboard-widget-manager card mb-3" id="dashboard-widget-manager" hidden>
        <div class="card-body">
            <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mb-3">
                <div><h2 class="h2 mb-1">Dashboard Widget’ları</h2><p class="text-secondary mb-0">Görmek istediğiniz bilgileri seçerek dashboardunuzu kişiselleştirin.</p></div>
                <div><label for="dashboard-widget-search" class="visually-hidden">Widget ara</label><input id="dashboard-widget-search" type="search" class="form-control" placeholder="Widget ara…" data-widget-search></div>
            </div>
            <div class="row g-3 dashboard-widget-options">
                @foreach (collect($widgetDashboard['definitions'])->groupBy('group') as $group => $definitions)
                    <div class="col-12 col-sm-6 col-xl-3" data-widget-group>
                        <fieldset class="dashboard-widget-group h-100">
                            <legend class="h4">{{ $group }}</legend>
                            @foreach ($definitions as $definition)
                                <label class="form-check mb-2" data-widget-option data-search="{{ $group }} {{ $definition['title'] }}">
                                    <input class="form-check-input" type="checkbox" value="{{ $definition['id'] }}" data-widget-checkbox @checked(in_array($definition['id'], $widgetDashboard['selected'], true))>
                                    <span class="form-check-label">{{ $definition['title'] }}</span>
                                </label>
                            @endforeach
                        </fieldset>
                    </div>
                @endforeach
            </div>
            @if ($widgetDashboard['definitions'] === [])
                <p class="text-secondary">Etkin modüllerde yetkinize açık widget bulunmuyor.</p>
            @endif
            <p class="text-secondary mt-3 mb-0" data-widget-search-empty hidden>Aramanızla eşleşen widget bulunamadı.</p>
            <div class="dashboard-widget-savebar mt-3 d-flex flex-wrap align-items-center gap-2">
                <span class="fw-semibold me-auto" data-widget-count aria-live="polite"></span>
                <button class="btn" type="button" data-widget-cancel>Vazgeç</button>
                <button class="btn btn-success" type="button" data-widget-save>Düzeni Kaydet</button>
            </div>
            <div class="alert alert-warning mt-3 mb-0" data-widget-save-error role="alert" hidden></div>
        </div>
    </div>
    <div class="dashboard-widget-grid" data-widget-grid>
        @foreach ($widgetDashboard['selected'] as $id)
            <x-admin.dashboard.widget :definition="$widgetDashboard['definitions'][$id]" />
        @endforeach
    </div>
    <div class="card card-body text-center text-secondary" data-widget-empty @if ($widgetDashboard['selected'] !== []) hidden @endif>
        @if ($widgetDashboard['definitions'] === [])
            Etkin modüllerde yetkinize açık widget bulunmuyor.
        @else
            Henüz widget seçmediniz. “Widget’ları Yönet” düğmesinden dashboardunuzu düzenleyebilirsiniz.
        @endif
    </div>
    @foreach ($widgetDashboard['definitions'] as $definition)
        <template data-widget-template="{{ $definition['id'] }}"><x-admin.dashboard.widget :definition="$definition" /></template>
    @endforeach
</section>
