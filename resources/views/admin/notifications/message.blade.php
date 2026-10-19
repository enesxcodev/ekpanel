<a href="{{ $destination ?? $notification->destination ?? route('admin.notifications.show', $notification->id) }}" data-notification-open data-read-url="{{ route('admin.notifications.read', $notification->id) }}" class="d-block text-wrap {{ $notification->read_at ? '' : 'fw-bold' }}">{{ $notification->data['title'] ?? 'Bildirim' }}</a>
<div class="text-secondary text-wrap small">{{ \Illuminate\Support\Str::limit($notification->data['content'] ?? '', 120) }}</div>
<a href="{{ route('admin.notifications.show', $notification->id) }}" class="small">Bildirim detayı</a>
