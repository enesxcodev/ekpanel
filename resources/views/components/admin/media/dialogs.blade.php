<dialog class="media-action-dialog" data-media-dialog aria-label="Medya işlemi">
    <form data-media-dialog-form>
        <div class="media-dialog-header">
            <h3 class="mb-0" data-media-dialog-title></h3>
            <button type="button" class="btn-close" data-action="cancel-dialog" aria-label="Pencereyi kapat"></button>
        </div>
        <div class="media-dialog-body">
            <p class="text-secondary" data-media-dialog-description></p>
            <label class="form-label" data-media-dialog-name-label>Ad
                <input type="text" class="form-control mt-2" data-media-dialog-name maxlength="255">
            </label>
            <label class="form-label" data-media-dialog-folder-label>Klasör
                <select class="form-select mt-2" data-media-dialog-folder></select>
            </label>
            <div class="alert alert-danger d-none mb-0 mt-3" data-media-dialog-error role="alert"></div>
        </div>
        <div class="media-dialog-footer">
            <button type="button" class="btn" data-action="cancel-dialog">Vazgeç</button>
            <button type="submit" class="btn btn-primary" data-media-dialog-submit>Kaydet</button>
        </div>
    </form>
</dialog>
