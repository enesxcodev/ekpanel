<x-admin.layout :title="$installedModule->getName().' modül detayı'" :fluid="true">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="text-secondary mb-0">Modül bilgileri ve özelleştirilebilir ek alanlar.</p>
        <a href="{{ route('admin.modules.index') }}" class="btn">Modüllere dön</a>
    </div>
    <div class="card mb-4"><div class="card-body">
        <div class="d-flex gap-3 align-items-center"><h2 class="card-title mb-0">{{ $installedModule->getName() }}</h2><span class="badge {{ $installedModule->isEnabled() ? 'bg-success-lt' : 'bg-secondary-lt' }}">{{ $installedModule->isEnabled() ? 'Etkin' : 'Devre dışı' }}</span><span class="text-secondary">{{ $installedModule->get('version', 'Sürüm belirtilmemiş') }}</span></div>
        <p class="mb-0 mt-2">{{ $installedModule->getDescription() }}</p>
    </div></div>
    @if($errors->any())<div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>@endif
    @if($error)
        <div class="alert alert-danger" role="alert">Alan tanımları okunamadı. Mevcut dosya korunuyor. {{ $error }}</div>
    @else
        <form method="POST" action="{{ route('admin.modules.features.update', $installedModule->getName()) }}" data-form-builder data-builder-mode="features" data-document="{{ json_encode(old('document', $document), JSON_UNESCAPED_UNICODE) }}">
            @csrf @method('PUT')
            <input type="hidden" name="document" data-schema-value>
            <input type="hidden" name="revision" value="{{ old('revision', $revision) }}">
            <div class="card mb-3"><div class="card-body">
                <h2 class="card-title">Ek alan formları</h2>
                <p class="text-secondary">Kaydettiğiniz alanlar modülün ilgili ekleme ve düzenleme ekranlarında kullanılır. Form anahtarı, modülün kullandığı içerik formuyla eşleşmelidir. Mevcut form ve alan anahtarlarını değiştirmek kayıtlı içerikle bağlantıyı kesebilir.</p>
                <div class="row g-3 align-items-end">
                    <div class="col-md-4"><label class="form-label">Düzenlenecek form<select class="form-select" data-feature-form aria-label="Düzenlenecek form"></select></label></div>
                    <div class="col-md-5"><label class="form-label">Form başlığı<input class="form-control" maxlength="150" data-feature-label></label></div>
                    <div class="col-md-3"><button type="button" class="btn btn-outline-danger" data-feature-remove>Formu kaldır</button></div>
                </div>
                <div class="row g-3 align-items-end mt-1">
                    <div class="col-md-4"><label class="form-label">Yeni form anahtarı<input class="form-control" maxlength="100" placeholder="Örn. service, category" data-feature-new-key></label></div>
                    <div class="col-md-5"><label class="form-label">Yeni form başlığı<input class="form-control" maxlength="150" placeholder="Örn. Hizmet ek alanları" data-feature-new-label></label></div>
                    <div class="col-md-3"><button type="button" class="btn" data-feature-add>Form ekle</button></div>
                </div>
                <p class="text-secondary small mt-2 mb-0" data-feature-message role="status"></p>
            </div></div>
            <div data-feature-workspace><x-admin.form.builder :features="true" /></div>
            <details class="mt-3"><summary>Oluşturulan JSON</summary><pre class="card card-body mt-2 overflow-auto" data-feature-json></pre></details>
            <div class="card card-body mt-3 d-flex flex-row justify-content-between"><a href="{{ route('admin.modules.index') }}" class="btn">Vazgeç</a><button class="btn btn-primary" type="submit">Kaydet</button></div>
        </form>
    @endif
</x-admin.layout>
