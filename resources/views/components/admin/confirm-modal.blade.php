@props([
    'id' => 'admin-confirm-modal',
    'title' => 'İşlemi onayla',
    'message' => 'Bu işlemi gerçekleştirmek istediğinizden emin misiniz?',
    'confirmLabel' => 'Onayla',
    'cancelLabel' => 'Vazgeç',
    'confirmClass' => 'btn-danger',
])

<div class="modal modal-blur fade" id="{{ $id }}" tabindex="-1" aria-hidden="true" data-confirm-modal-dialog>
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" data-confirm-modal-title>{{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body" data-confirm-modal-message>{{ $message }}</div>
            <div class="modal-footer">
                <button type="button" class="btn" data-bs-dismiss="modal" data-confirm-modal-cancel>{{ $cancelLabel }}</button>
                <button type="button" class="btn {{ $confirmClass }}" data-confirm-modal-submit>{{ $confirmLabel }}</button>
            </div>
        </div>
    </div>
</div>
