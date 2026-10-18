@props(['config'])
@pushOnce('scripts', 'media-picker-modal')
<div class="modal modal-blur fade" id="media-picker-modal" tabindex="-1" aria-labelledby="media-picker-title" aria-hidden="true" data-media-modal>
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="media-picker-title">Medya seç</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <x-admin.media.browser :config="$config" :picker="true" />
            </div>
            <div class="modal-footer">
                <span class="text-secondary me-auto" data-media-modal-count>Dosya seçilmedi</span>
                <button type="button" class="btn" data-bs-dismiss="modal">Vazgeç</button>
                <button type="button" class="btn btn-primary" data-media-confirm disabled>Seçimi kullan</button>
            </div>
        </div>
    </div>
</div>
@endPushOnce
