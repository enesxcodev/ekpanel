<x-admin.layout title="Katılımcı kayıtları">
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h2 class="card-title me-auto">Katılımcı kayıtları</h2>
            @can('events.create')
                <a class="btn btn-primary" href="{{ route('admin.events.registrations.create') }}">Katılımcı ekle</a>
            @endcan
        </div>
        <div class="table-responsive">
            <table class="table card-table table-vcenter">
                <thead><tr><th>Katılımcı</th><th>Etkinlik</th><th>E-posta</th><th>Durum</th></tr></thead>
                <tbody>@forelse ($registrations as $registration)<tr><td>{{ $registration->attendee_name }}</td><td>{{ $registration->event?->title }}</td><td>{{ $registration->email }}</td><td>{{ $registration->status }}</td></tr>@empty<tr><td colspan="4" class="text-muted">Katılımcı bulunmuyor.</td></tr>@endforelse</tbody>
            </table>
        </div>
        <div class="card-footer">{{ $registrations->links() }}</div>
    </div>
</x-admin.layout>
