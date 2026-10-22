<x-admin.layout title="Aday başvuruları">
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h2 class="card-title me-auto">Aday başvuruları</h2>
            @can('careers.create')
                <a class="btn btn-primary" href="{{ route('admin.careers.applications.create') }}">Başvuru ekle</a>
            @endcan
        </div>
        <div class="table-responsive">
            <table class="table card-table table-vcenter">
                <thead><tr><th>Aday</th><th>İlan</th><th>E-posta</th><th>Durum</th></tr></thead>
                <tbody>@forelse ($applications as $application)<tr><td>{{ $application->candidate_name }}</td><td>{{ $application->posting?->title }}</td><td>{{ $application->email }}</td><td>{{ $application->status }}</td></tr>@empty<tr><td colspan="4" class="text-muted">Başvuru bulunmuyor.</td></tr>@endforelse</tbody>
            </table>
        </div>
        <div class="card-footer">{{ $applications->links() }}</div>
    </div>
</x-admin.layout>
