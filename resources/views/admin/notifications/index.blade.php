<x-admin.layout title="Bildirimler">
    <div class="card" data-notifications-page>
        <div class="card-header flex-wrap gap-3">
            <h2 class="card-title me-auto">Bildirimler</h2>
            <select class="form-select w-auto" data-table-filter="filter" data-table-target="notifications-table" aria-label="Bildirim filtresi">
                @foreach (['all' => 'Tümü', 'unread' => 'Okunmamış', 'read' => 'Okunmuş', 'archive' => 'Arşiv'] as $value => $label)
                    <option value="{{ $value }}" @selected($filter === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="card-body border-bottom">
            <label class="form-check mb-2">
                <input class="form-check-input" type="checkbox" data-notification-select-page>
                <span class="form-check-label">Yalnız bu sayfadaki bildirimlerin hepsini seç</span>
            </label>
            <p class="text-secondary small" data-notification-selection-status aria-live="polite">0 bildirim seçildi. Seçim yalnız mevcut sayfayı kapsar.</p>
            <form method="POST" action="{{ route('admin.notifications.update') }}" data-notification-bulk class="d-flex flex-wrap gap-2">
                @csrf @method('PATCH')
                <input type="hidden" name="scope" value="selected">
                <button class="btn btn-outline-primary" type="submit" name="action" value="read" disabled>Seçilenleri okunmuş yap</button>
                <button class="btn btn-outline-secondary" type="submit" name="action" value="archive" data-notification-archive @disabled(true) @if($filter === 'archive') hidden @endif>Seçilenleri arşivle</button>
                <button class="btn btn-outline-secondary" type="submit" name="action" value="restore" data-notification-restore @disabled(true) @if($filter !== 'archive') hidden @endif>Seçilenleri arşivden çıkar</button>
            </form>
            <noscript><p class="text-secondary mt-3">Bildirim tablosu ve toplu seçim için JavaScript'i etkinleştirin.</p></noscript>
        </div>
        <x-admin.data-table id="notifications-table" :source="route('admin.notifications.data')" :options="['order' => [[3, 'desc']], 'searching' => false]" :columns="[
            ['title' => 'Seç', 'data' => 'selection', 'orderable' => false, 'searchable' => false],
            ['title' => 'Bildirim', 'data' => 'message', 'orderable' => false, 'searchable' => false],
            ['title' => 'Durum', 'data' => 'status', 'orderable' => false, 'searchable' => false],
            ['title' => 'Tarih', 'data' => 'created_at', 'name' => 'created_at', 'searchable' => false],
        ]" />
    </div>
</x-admin.layout>
