<x-admin.layout title="Roller & İzinler">
    <div class="card">
        <div class="card-header"><h2 class="card-title">Roller &amp; İzinler</h2></div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead><tr><th>Rol</th><th>Erişim</th><th class="w-1">İşlemler</th></tr></thead>
                <tbody>
                    @foreach ($roles as $role)
                        <tr>
                            <td>{{ $role->name === 'super-admin' ? 'Süper Admin' : ($role->name === 'customer-admin' ? 'Müşteri Admin' : $role->name) }}</td>
                            <td>
                                @if ($role->name === 'super-admin')
                                    <span class="badge bg-success-lt">Tüm izinler</span>
                                @else
                                    {{ $role->permissions_count }} izin
                                @endif
                            </td>
                            <td><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.roles.edit', $role) }}">{{ $role->name === 'super-admin' ? 'İzinleri görüntüle' : 'İzinleri düzenle' }}</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-admin.layout>
