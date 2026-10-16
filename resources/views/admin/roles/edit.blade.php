<x-admin.layout title="Rol izinleri">
    @php($locked = $role->name === 'super-admin')
    <form method="POST" action="{{ route('admin.roles.update', $role) }}" data-permission-matrix>
        @csrf
        @method('PUT')
        <div class="card">
            <div class="card-header d-flex flex-wrap gap-3">
                <h2 class="card-title me-auto">{{ $role->name === 'super-admin' ? 'Süper Admin' : ($role->name === 'customer-admin' ? 'Müşteri Admin' : $role->name) }} — İzinler</h2>
                <input type="search" class="form-control w-auto" placeholder="Alan ara…" aria-label="İzin alanı ara" data-permission-search>
            </div>
            @if ($locked)
                <div class="card-body"><div class="alert alert-info mb-0">Süper admin tüm izinlere sahiptir. Bu erişim değiştirilemez.</div></div>
            @endif
            @if ($errors->any())
                <div class="card-body"><div class="alert alert-danger" role="alert">{{ $errors->first() }}</div></div>
            @endif
            <div class="card-body text-secondary">Menü Show, ilgili alanın sidebar'da görünmesini sağlar. Menünün görünmesi için sayfaya erişim izni de gerekir. Bu seçenek tek başına sayfaya erişim vermez.</div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Alan</th>
                            @foreach ($actions as $label)
                                <th class="text-center">{{ $label }}</th>
                            @endforeach
                            <th class="text-center">Tümü</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($groups as $group => $permissions)
                            <tr data-permission-row>
                                <td>{{ $permissionLabels[$group] ?? ($group === 'users' ? 'Yönetici hesapları' : $group) }}</td>
                                @foreach ($actions as $action => $label)
                                    @php($permissionName = $group.'.'.$action)
                                    <td class="text-center">
                                        @if ($permissions->contains('name', $permissionName))
                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permissionName }}" aria-label="{{ $group }}: {{ $label }}" @checked($locked || in_array($permissionName, old('permissions', $errors->any() ? [] : $selected), true)) @disabled($locked) data-permission-checkbox>
                                        @else
                                            <span class="text-secondary" aria-label="Bu işlem mevcut değil">—</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="text-center"><input class="form-check-input" type="checkbox" aria-label="{{ $group }} tüm izinleri seç" @disabled($locked) data-permission-select-row></td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ count($actions) + 2 }}" class="text-secondary">Tanımlı izin bulunamadı.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex gap-2">
                @unless ($locked)<button type="submit" class="btn btn-primary">Değişiklikleri kaydet</button>@endunless
                <a class="btn btn-link" href="{{ route('admin.roles.index') }}">Rollere dön</a>
            </div>
        </div>
    </form>
</x-admin.layout>
