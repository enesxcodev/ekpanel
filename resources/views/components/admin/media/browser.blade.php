@props(['config', 'picker' => false])
<section {{ $attributes->class(['media-browser', 'media-browser--picker' => $picker]) }}
    data-media-browser data-config="{{ json_encode($config) }}" data-picker="{{ $picker ? 'true' : 'false' }}"
    aria-label="Medya dosya yöneticisi">
    <x-admin.media.toolbar :permissions="$config['permissions']" />
    <div class="alert alert-danger d-none mt-3" data-media-error role="alert"></div>
    <div class="media-upload-queue d-none" data-media-queue aria-live="polite"></div>
    <div class="media-workspace card">
        <x-admin.media.sidebar />
        <main class="media-content">
            <div class="media-content-toolbar">
                <nav aria-label="Klasör yolu" class="media-breadcrumb" data-media-breadcrumb></nav>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <select class="form-select form-select-sm media-type-filter" data-media-filter aria-label="Dosya türü">
                        <option value="">Tür: Tümü</option><option value="image">Görseller</option>
                        <option value="video">Videolar</option><option value="document">Belgeler</option>
                    </select>
                    <select class="form-select form-select-sm media-sort" data-media-sort aria-label="Sıralama ölçütü">
                        <option value="created_at">Yükleme tarihi</option><option value="name">Dosya adı</option><option value="size">Dosya boyutu</option>
                    </select>
                    <div class="btn-group" role="group" aria-label="Sıralama yönü">
                        <button type="button" class="btn btn-sm" data-action="direction" data-value="asc" aria-pressed="false" title="Artan sıralama"><x-admin.icon name="arrow-up" /> Artan</button>
                        <button type="button" class="btn btn-sm active" data-action="direction" data-value="desc" aria-pressed="true" title="Azalan sıralama"><x-admin.icon name="arrow-down" /> Azalan</button>
                    </div>
                    <div class="btn-group ms-auto" role="group" aria-label="Görünüm">
                        <button type="button" class="btn btn-sm active" data-action="layout" data-value="grid" aria-pressed="true" title="Kart görünümü"><x-admin.icon name="layout-grid" /><span class="d-none d-xxl-inline">Kart</span></button>
                        <button type="button" class="btn btn-sm" data-action="layout" data-value="list" aria-pressed="false" title="Liste görünümü"><x-admin.icon name="list" /><span class="d-none d-xxl-inline">Liste</span></button>
                    </div>
                </div>
            </div>
            <div class="media-selection-bar d-none" data-media-selection-bar>
                <span data-media-selection-count></span>
                <button type="button" class="btn btn-sm btn-ghost-secondary" data-action="clear-selection">Seçimi temizle</button>
                @if ($config['permissions']['delete'] && ! $picker)
                    <button type="button" class="btn btn-sm btn-outline-danger ms-auto" data-action="trash-selected"><x-admin.icon name="trash" /> Seçilenleri kaldır</button>
                @endif
            </div>
            <div class="media-drop-area" data-media-drop>
                <div class="media-loading d-none" data-media-loading role="status"><span class="spinner-border spinner-border-sm"></span> Dosyalar yükleniyor…</div>
                <div class="media-grid-container" data-media-grid>
                    <div class="media-grid media-folder-grid" data-media-folders></div>
                    <div class="media-grid media-file-grid" data-media-files></div>
                </div>
                <div class="table-responsive d-none" data-media-table>
                    <table class="table table-vcenter media-table mb-0">
                        <thead><tr>
                            <th class="w-1"></th><th><button type="button" class="media-table-sort" data-action="sort-column" data-value="name">Dosya adı <span data-sort-marker="name"></span></button></th>
                            <th>Tür</th><th><button type="button" class="media-table-sort" data-action="sort-column" data-value="size">Boyut <span data-sort-marker="size"></span></button></th>
                            <th><button type="button" class="media-table-sort" data-action="sort-column" data-value="created_at">Yükleme tarihi <span data-sort-marker="created_at"></span></button></th><th class="w-1"></th>
                        </tr></thead>
                        <tbody data-media-rows></tbody>
                    </table>
                </div>
                <div class="media-empty d-none" data-media-empty>
                    <span class="media-empty-icon"><x-admin.icon name="folder" /></span>
                    <h3 data-media-empty-title>Bu klasör henüz boş</h3>
                    <p class="text-secondary mb-3" data-media-empty-text>Dosyaları buraya sürükleyin veya yükleme butonunu kullanın.</p>
                    @if ($config['permissions']['create'])
                        <button type="button" class="btn btn-primary" data-action="upload"><x-admin.icon name="upload" /> Dosya yükle</button>
                    @endif
                </div>
                <div class="media-drop-overlay"><x-admin.icon name="upload" /><strong>Dosyaları yüklemek için bırakın</strong></div>
            </div>
            <footer class="media-footer">
                <span class="text-secondary" data-media-total></span>
                <nav class="btn-group" data-media-pagination aria-label="Dosya sayfaları"></nav>
            </footer>
        </main>
        <x-admin.media.details :permissions="$config['permissions']" :picker="$picker" />
    </div>
    <x-admin.media.dialogs />
    <x-admin.media.templates />
</section>
