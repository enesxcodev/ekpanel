<x-admin.layout title="Medya Kütüphanesi" :fluid="true">
    <x-slot:topbar><x-admin.topbar /></x-slot:topbar>
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <div class="text-secondary mb-1"><a href="{{ route('admin.dashboard') }}">Yönetim</a> / Medya</div>
            <p class="text-secondary mb-0">Dosyalarınızı düzenleyin ve içeriklerinizde kullanın.</p>
        </div>
        <button type="button" class="btn btn-outline-primary" data-media-open data-media-kind="image">
            <x-admin.icon name="photo" /> Görsel seçiciyi dene
        </button>
    </div>
    <x-admin.media.browser :config="$mediaConfig" />
    <div class="card mt-3">
        <div class="card-body d-flex flex-wrap gap-3 align-items-center">
            <div class="me-auto">
                <h3 class="card-title mb-1">Ortak medya seçici</h3>
                <div class="text-secondary">Seçimi burada deneyebilirsiniz. Bu alan içerik kaydı oluşturmaz.</div>
            </div>
            <x-admin.media.picker name="preview_media_id" label="Seçici önizlemesi" :config="$mediaConfig" kind="image" />
        </div>
    </div>
    <x-admin.media.modal :config="$mediaConfig" />
</x-admin.layout>
