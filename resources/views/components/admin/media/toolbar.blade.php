@props(['permissions'])
<div class="media-toolbar">
    <div class="d-flex flex-wrap gap-2">
        @if ($permissions['create'])
            <button type="button" class="btn btn-primary" data-action="upload"><x-admin.icon name="upload" /> Dosya yükle</button>
            <button type="button" class="btn" data-action="new-folder"><x-admin.icon name="folder-plus" /> Yeni klasör</button>
        @endif
        <button type="button" class="btn btn-icon" data-action="refresh" title="Mevcut klasörü yenile" aria-label="Mevcut klasörü yenile"><x-admin.icon name="refresh" /></button>
        @if ($permissions['create'])
            <button type="button" class="btn btn-ghost-secondary" data-action="sync" title="Uploads dizinindeki yeni dosyaları bul">Diskten tara</button>
        @endif
    </div>
    <div class="input-icon media-search">
        <span class="input-icon-addon"><x-admin.icon name="search" /></span>
        <input type="search" class="form-control" placeholder="Dosya veya klasör ara…" aria-label="Dosya veya klasör ara" data-media-search maxlength="150">
    </div>
    <input type="file" class="d-none" data-media-upload multiple accept=".jpg,.jpeg,.png,.webp,.mp4,.webm,.pdf,.doc,.docx,.pptx,.txt" aria-label="Yüklenecek dosyalar">
</div>
