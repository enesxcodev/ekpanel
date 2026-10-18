<x-admin.layout title="Menü Yönetimi" :fluid="true">
    <div class="menu-workspace" data-menu-workspace>
        <div class="mb-4">
            <p class="text-secondary mb-0">Mevcut menüyü seçin, öğelerini düzenleyin ve ayarlarını yapılandırın.</p>
        </div>
        <div class="alert alert-danger" data-menu-error role="alert" hidden></div>
        <div class="card mb-3">
            <div class="card-body menu-overview">
                <div>
                    <label for="menu-selector" class="form-label">Menü seçin</label>
                    <select id="menu-selector" class="form-select" data-menu-selector aria-label="Düzenlenecek menü"><option value="">Menü seçin</option></select>
                </div>
                <div class="menu-summary"><span class="text-secondary small">Menü anahtarı</span><strong data-menu-key>—</strong></div>
                <div class="menu-summary"><span class="text-secondary small">Öğe sayısı</span><strong data-menu-count>0</strong></div>
                <div class="menu-summary"><span class="text-secondary small">Durum</span><span data-menu-status>—</span></div>
                <div class="d-flex gap-2 flex-wrap">
                    @can('menus.update')<button type="button" class="btn btn-outline-secondary" data-menu-edit>Menü bilgileri</button>@endcan
                    @can('menus.create')<button type="button" class="btn btn-primary" data-menu-new><x-admin.icon name="plus" /> Yeni menü</button>@endcan
                </div>
            </div>
            <form class="card-body border-top" data-menu-form hidden>
                @csrf
                <h2 class="h3" data-menu-form-title>Menü bilgileri</h2>
                <div class="alert alert-danger" data-form-errors role="alert" hidden></div>
                <div class="row g-3">
                    <div class="col-md-4"><label for="menu-name" class="form-label required">Menü adı</label><input id="menu-name" name="name" class="form-control" maxlength="255" required></div>
                    <div class="col-md-3"><label for="menu-key" class="form-label required">Menü anahtarı</label><input id="menu-key" name="key" class="form-control" maxlength="100" pattern="[a-z][a-z0-9_-]*" required><div class="form-hint">Benzersiz anahtar: main, footer, mobile-menu</div></div>
                    <div class="col-md-3"><label for="menu-icon" class="form-label">İkon</label><input id="menu-icon" name="icon" class="form-control" maxlength="100" pattern="[a-z][a-z0-9-]*" placeholder="layout-grid"></div>
                    <div class="col-md-2"><label for="menu-active" class="form-label">Durum</label><label class="form-check form-switch"><input id="menu-active" name="is_active" type="checkbox" class="form-check-input" checked><span class="form-check-label">Aktif</span></label></div>
                </div>
                <div class="d-flex gap-2 mt-3 flex-wrap"><button class="btn btn-primary" type="submit">Menüyü kaydet</button><button class="btn btn-outline-secondary" type="button" data-menu-cancel>Vazgeç</button>@can('menus.delete')<button class="btn btn-outline-danger ms-auto" type="button" data-menu-delete>Menüyü sil</button>@endcan</div>
            </form>
        </div>
        <div class="empty card py-5" data-menu-empty><p class="empty-title">Bir menü seçin</p><p class="empty-subtitle text-secondary">Mevcut bir menüyü seçerek başlayın veya yeni menü oluşturun.</p></div>
        <div class="menu-builder" data-menu-builder hidden>
            @can('menus.update')
                <aside class="card menu-sources">
                    <div class="card-body"><h2 class="h3 mb-1">Menü öğesi ekle</h2><p class="text-secondary small">Menüye eklenecek öğe kaynağını seçin.</p>
                        @foreach ($configuration['sources'] as $source)
                            <details class="menu-source" @if($loop->first) open @endif>
                                <summary><x-admin.icon name="file" /> {{ $source['label'] }}</summary>
                                <form data-menu-add data-type="content" data-source="{{ $source['key'] }}" class="menu-source-body">
                                    <label class="form-label" for="source-search-{{ $loop->index }}">İçerik arayın</label><input id="source-search-{{ $loop->index }}" type="search" class="form-control mb-2" placeholder="İçeriklerde ara…" data-source-search>
                                    <div class="menu-source-options">
                                        @forelse ($source['options'] as $option)
                                            <label class="form-check menu-source-option"><input type="radio" class="form-check-input" name="source_id" value="{{ $option['value'] }}" data-label="{{ $option['label'] }}" required><span class="form-check-label">{{ $option['label'] }}</span></label>
                                        @empty
                                            <p class="text-secondary small">Bu kaynakta seçilebilir kayıt yok.</p>
                                        @endforelse
                                    </div>
                                    <p class="small text-secondary" data-source-no-results hidden>Aramanızla eşleşen içerik yok.</p>
                                    <label class="form-check form-switch"><input type="checkbox" name="is_active" class="form-check-input" checked><span class="form-check-label">Aktif olarak ekle</span></label>
                                    <p class="form-hint mb-3">Henüz kullanılabilir bağlantısı olmayan içerikleri pasif olarak ekleyebilirsiniz.</p>
                                    <div data-form-errors class="text-danger small mb-2" role="alert" hidden></div>
                                    <button type="submit" class="btn btn-primary w-100" @disabled(count($source['options']) === 0)><x-admin.icon name="plus" /> Seçili içeriği menüye ekle</button>
                                </form>
                            </details>
                        @endforeach
                        @foreach (['url' => 'Özel URL', 'route' => 'Site rotası', 'anchor' => 'Sayfa içi bağlantı', 'group' => 'Bağlantısız grup başlığı'] as $type => $label)
                            <details class="menu-source"><summary><x-admin.icon :name="$type === 'group' ? 'folder' : 'link'" /> {{ $label }}</summary>
                                <form data-menu-add data-type="{{ $type }}" class="menu-source-body">
                                    <label class="form-label required" for="add-label-{{ $type }}">Görünen metin</label><input id="add-label-{{ $type }}" name="label" class="form-control mb-3" maxlength="255" required>
                                    @include('menus::workspace.target-fields', ['prefix' => 'add-'.$type, 'type' => $type])
                                    <div data-form-errors class="text-danger small mb-2" role="alert" hidden></div>
                                    <button type="submit" class="btn btn-primary w-100"><x-admin.icon name="plus" /> Menüye ekle</button>
                                </form>
                            </details>
                        @endforeach
                    </div>
                </aside>
            @endcan
            <div class="menu-builder-main">
                <section class="card">
                    <div class="card-body">
                        <div class="d-flex gap-2 justify-content-between flex-wrap mb-2"><div><h2 class="h3 mb-1">Menü yapısı</h2><p class="text-secondary small mb-0">Öğeleri sürükleyerek sıralayın; satırın ortasına bırakarak alt öğe yapın.</p></div><div class="d-flex gap-2 align-self-start"><button type="button" class="btn btn-sm btn-outline-secondary" data-menu-expand>Tümünü genişlet</button><button type="button" class="btn btn-sm btn-outline-secondary" data-menu-collapse>Tümünü kapat</button></div></div>
                        <p class="text-secondary small" id="menu-drag-help">Taşıma tutamacında yön tuşları: yukarı/aşağı sırala, sağa alt öğe yap, sola üst seviyeye taşı. Değişiklikler işlem sonrası kaydedilir.</p>
                        <div data-menu-tree role="list" aria-label="Menü öğeleri"></div>
                        <p class="text-secondary text-center py-4" data-menu-tree-empty hidden>Bu menüde henüz öğe yok. Soldaki kaynaklardan ilk öğeyi ekleyin.</p>
                    </div>
                </section>
                @can('menus.update')
                    <section class="card menu-item-editor" data-item-editor hidden>
                        <div class="card-body"><div class="d-flex justify-content-between gap-2 mb-3"><div><h2 class="h3 mb-1">Seçili öğe ayarları</h2><p class="text-secondary small mb-0">Seçili öğenin ayrıntılarını düzenleyin.</p></div><button type="button" class="btn btn-sm btn-outline-danger align-self-start" data-item-delete><x-admin.icon name="trash" /> Bu öğeyi sil</button></div>
                            <form data-item-form>
                                @csrf
                                <input type="hidden" name="target_type"><input type="hidden" name="source">
                                <div class="alert alert-danger" data-form-errors role="alert" hidden></div>
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label required" for="item-label">Görünen metin</label><input id="item-label" name="label" class="form-control" required maxlength="255"></div>
                                    <div class="col-md-6"><label class="form-label" for="item-icon">İkon</label><input id="item-icon" name="icon" class="form-control" maxlength="100" pattern="[a-z][a-z0-9-]*" list="menu-icon-options" placeholder="folder"><datalist id="menu-icon-options">@foreach(['file','folder','link','list','users','settings','briefcase','layout-grid','photo','star'] as $icon)<option value="{{ $icon }}">@endforeach</datalist><div class="form-hint">Mevcut ikon adını girin.</div></div>
                                    <div class="col-12">
                                        @foreach (['url', 'route', 'anchor', 'content', 'group'] as $type)
                                            <fieldset data-item-target="{{ $type }}" hidden disabled>
                                                @include('menus::workspace.target-fields', ['prefix' => 'edit-'.$type, 'type' => $type])
                                            </fieldset>
                                        @endforeach
                                    </div>
                                    <div class="col-md-6"><label class="form-label" for="item-parent">Üst öğe</label><select id="item-parent" name="parent_id" class="form-select"></select></div>
                                    <div class="col-md-3"><label class="form-label required" for="item-order">Sıra</label><input id="item-order" name="sort_order" type="number" min="0" max="65535" class="form-control" required></div>
                                    <div class="col-md-3"><label class="form-label" for="item-active">Durum</label><label class="form-check form-switch"><input id="item-active" name="is_active" class="form-check-input" type="checkbox"><span class="form-check-label">Aktif</span></label></div>
                                    <div class="col-12"><label class="form-check form-switch"><input name="new_tab" class="form-check-input" type="checkbox"><span class="form-check-label">Yeni sekmede aç</span></label></div>
                                </div>
                                <div class="mt-3 d-flex gap-2"><button class="btn btn-primary" type="submit">Öğeyi kaydet</button><button class="btn btn-outline-secondary" type="button" data-item-cancel>Vazgeç</button><span class="text-secondary small align-self-center" data-item-unsaved hidden>Kaydedilmemiş değişiklikler</span></div>
                            </form>
                        </div>
                    </section>
                @endcan
            </div>
        </div>
        <div class="visually-hidden" aria-live="polite" data-menu-live></div>
        @foreach (['file', 'folder', 'link', 'list', 'users', 'settings', 'briefcase', 'layout-grid', 'photo', 'star', 'trash', 'plus', 'chevron-down'] as $icon)
            <template data-menu-icon="{{ $icon }}"><x-admin.icon :name="$icon" /></template>
        @endforeach
        <noscript><div class="alert alert-warning">Menü düzenleyicisini kullanmak için JavaScript'i etkinleştirin.</div></noscript>
        <script type="application/json" data-menu-configuration>{!! json_encode($configuration, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) !!}</script>
    </div>
</x-admin.layout>
