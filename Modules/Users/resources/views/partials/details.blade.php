<div class="modal modal-blur fade text-start" id="user-details-{{ $user->id }}" tabindex="-1" aria-labelledby="user-details-title-{{ $user->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="user-details-title-{{ $user->id }}">Kullanıcı detayları</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">@include('users::partials.photo')</div>
                <dl class="row mb-0">
                    <dt class="col-sm-4">ID</dt><dd class="col-sm-8">{{ $user->id }}</dd>
                    <dt class="col-sm-4">Ad Soyad</dt><dd class="col-sm-8 text-break">{{ $user->name }} {{ $user->surname }}</dd>
                    <dt class="col-sm-4">E-posta</dt><dd class="col-sm-8 text-break">{{ $user->email }}</dd>
                    <dt class="col-sm-4">Kullanıcı adı</dt><dd class="col-sm-8 text-break">{{ $user->username ?? '—' }}</dd>
                    <dt class="col-sm-4">Durum</dt><dd class="col-sm-8">@include('users::partials.status')</dd>
                    <dt class="col-sm-4">E-posta doğrulama</dt><dd class="col-sm-8">{{ $user->email_verified_at?->format('d.m.Y H:i') ?? 'Doğrulanmadı' }}</dd>
                    <dt class="col-sm-4">Son giriş</dt><dd class="col-sm-8">{{ $user->last_login_at?->format('d.m.Y H:i') ?? '—' }}</dd>
                    <dt class="col-sm-4">Son giriş IP</dt><dd class="col-sm-8 text-break">{{ $user->last_login_ip ?? '—' }}</dd>
                    <dt class="col-sm-4">Son giriş tarayıcı bilgisi</dt><dd class="col-sm-8 text-break">{{ $user->last_login_user_agent ?? '—' }}</dd>
                    <dt class="col-sm-4">Oluşturulma tarihi</dt><dd class="col-sm-8">{{ $user->created_at?->format('d.m.Y H:i') ?? '—' }}</dd>
                    <dt class="col-sm-4">Güncellenme tarihi</dt><dd class="col-sm-8">{{ $user->updated_at?->format('d.m.Y H:i') ?? '—' }}</dd>
                </dl>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>
