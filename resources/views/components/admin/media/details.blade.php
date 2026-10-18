@props(['permissions', 'picker' => false])
<aside class="media-details" aria-label="Dosya detayları">
    <div class="media-detail-placeholder" data-media-detail-placeholder>
        <x-admin.icon name="photo" />
        <h3>Dosya detayları</h3>
        <p class="text-secondary">Önizleme ve dosya bilgileri için bir dosya seçin.</p>
    </div>
    <div class="d-none" data-media-detail>
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h3 class="mb-0">Dosya detayları</h3>
            <button type="button" class="btn btn-sm btn-icon btn-ghost-secondary" data-action="close-detail" title="Detayları kapat" aria-label="Detayları kapat"><x-admin.icon name="x" /></button>
        </div>
        <div class="media-detail-preview" data-media-preview></div>
        <dl class="media-metadata">
            <dt>Dosya adı</dt><dd data-detail="name"></dd>
            <dt>Tür</dt><dd><span class="badge bg-blue-lt" data-detail="extension"></span></dd>
            <dt>Boyut</dt><dd data-detail="size"></dd>
            <dt>Ölçüler</dt><dd data-detail="dimensions"></dd>
            <dt>Yükleme tarihi</dt><dd data-detail="date"></dd>
        </dl>
        <div class="media-detail-section">
            <label class="form-label">Alternatif metin
                <textarea class="form-control mt-2" data-media-alt rows="2" maxlength="500" @disabled(! $permissions['update'])></textarea>
            </label>
            @if ($permissions['update'])
                <button type="button" class="btn btn-sm w-100" data-action="save-alt">Alternatif metni kaydet</button>
            @endif
        </div>
        <div class="media-detail-actions">
            <button type="button" class="btn" data-action="copy"><x-admin.icon name="link" /> Bağlantıyı kopyala</button>
            <a class="btn" data-media-download><x-admin.icon name="download" /> İndir</a>
            <button type="button" class="btn" data-action="favorite"><x-admin.icon name="star" /> <span data-media-favorite-label>Favorilere ekle</span></button>
            @if ($permissions['update'])
                <button type="button" class="btn" data-action="rename"><x-admin.icon name="file" /> Yeniden adlandır</button>
                <button type="button" class="btn" data-action="move"><x-admin.icon name="folder" /> Taşı</button>
            @endif
        </div>
        <div class="media-detail-section">
            <div class="form-label">Kullanıldığı yerler</div>
            <div class="d-flex flex-column gap-2 small" data-media-usages></div>
        </div>
        @if ($permissions['delete'])
            <button type="button" class="btn btn-outline-danger w-100 mt-2" data-action="trash"><x-admin.icon name="trash" /> <span data-media-trash-label>Çöp kutusuna taşı</span></button>
        @endif
    </div>
</aside>
