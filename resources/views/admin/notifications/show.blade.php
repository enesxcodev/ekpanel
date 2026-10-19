<x-admin.layout title="Bildirim detayı">
    <article class="card" @if(!$notification->read_at) data-notification-read-url="{{ route('admin.notifications.read', $notification->id) }}" @endif>
        <div class="card-header"><h2 class="card-title text-break">{{ $notification->data['title'] ?? 'Bildirim' }}</h2></div>
        <div class="card-body">
            <p class="text-secondary">{{ $notification->created_at->format('d.m.Y H:i') }} · {{ $notification->archived_at ? 'Arşivde' : 'Aktif' }}</p>
            <div class="text-break" style="white-space: pre-wrap">{{ $notification->data['content'] ?? '' }}</div>
            @if ($destination)
                <form method="POST" action="{{ route('admin.notifications.open', $notification->id) }}" class="mt-3">
                    @csrf
                    <button class="btn btn-primary" type="submit">İlgili sayfaya git</button>
                </form>
                <p class="small text-secondary mt-2">İlgili kayıt kaldırılmış veya erişim yetkiniz değişmiş olabilir. Bildirim içeriği burada saklanır.</p>
            @elseif (!empty($notification->data['link']))
                <div class="alert alert-info mt-3">İlgili sayfa veya kayıt artık mevcut değil. Bildirim içeriğini burada okuyabilirsiniz.</div>
            @endif
            @unless ($notification->read_at)
                <noscript><form method="POST" action="{{ route('admin.notifications.open', $notification->id) }}" class="mt-3">@csrf<button class="btn btn-outline-primary" type="submit">Okundu olarak işaretle ve aç</button></form></noscript>
            @endunless
        </div>
        <div class="card-footer d-flex flex-wrap gap-2">
            <a href="{{ route('admin.notifications.index') }}" class="btn">Tüm bildirimler</a>
            <form method="POST" action="{{ route('admin.notifications.update') }}">
                @csrf @method('PATCH')
                <input type="hidden" name="scope" value="selected"><input type="hidden" name="ids[]" value="{{ $notification->id }}">
                <button class="btn btn-outline-secondary" type="submit" name="action" value="{{ $notification->archived_at ? 'restore' : 'archive' }}">{{ $notification->archived_at ? 'Arşivden çıkar' : 'Arşivle' }}</button>
            </form>
        </div>
    </article>
</x-admin.layout>
