<div class="border-bottom px-3 py-3 fw-semibold d-flex justify-content-between gap-2">
    <span>Bildirimler</span><span class="text-secondary small">{{ $unreadNotificationCount }} okunmamış</span>
</div>
<div class="overflow-auto" style="max-height: 24rem">
    @forelse ($notificationItems as $notification)
        <div class="border-bottom px-3 py-3">
            <div class="d-flex align-items-start gap-2">
                @unless ($notification->read_at)<span class="badge bg-primary text-white mt-1" aria-label="Okunmamış">Yeni</span>@endunless
                <div class="flex-fill" style="min-width: 0">
                    @include('admin.notifications.message', ['notification' => $notification])
                    <time class="d-block text-secondary small mt-1" datetime="{{ $notification->created_at->toIso8601String() }}">{{ $notification->created_at->format('d.m.Y H:i') }}</time>
                </div>
            </div>
        </div>
    @empty
        <div class="text-center px-4 py-5">
            <span class="text-secondary"><x-admin.icon name="bell" /></span>
            <div class="fw-semibold mt-3">Henüz bildirim yok</div>
            <p class="text-secondary mb-0 mt-2">Yeni bildirimleriniz burada görünecek.</p>
        </div>
    @endforelse
</div>
<div class="p-3 border-top">
    <div class="row g-2 mb-2">
        <form class="col-6" method="POST" action="{{ route('admin.notifications.update') }}" data-notification-action>
            @csrf @method('PATCH')
            <input type="hidden" name="scope" value="all"><input type="hidden" name="action" value="read">
            <button class="btn btn-sm btn-outline-primary w-100" type="submit" @disabled($unreadNotificationCount === 0)>Tümünü okundu yap</button>
        </form>
        <form class="col-6" method="POST" action="{{ route('admin.notifications.update') }}" data-notification-action>
            @csrf @method('PATCH')
            <input type="hidden" name="scope" value="all"><input type="hidden" name="action" value="archive">
            <button class="btn btn-sm btn-outline-secondary w-100" type="submit" @disabled($notificationItems->isEmpty())>Tümünü arşivle</button>
        </form>
    </div>
    <p class="small text-secondary mb-2">Bu işlemler arşiv dışındaki tüm bildirimlerinize uygulanır.</p>
    <a class="btn btn-primary w-100" href="{{ route('admin.notifications.index') }}">Tüm bildirimler</a>
</div>
