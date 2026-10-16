<div class="d-flex align-items-center">
    <span class="avatar avatar-sm me-3">{{ mb_substr($admin->name, 0, 1) }}</span>
    <div>
        <div class="font-weight-medium">{{ $admin->name }} {{ $admin->lastname }}</div>
        <div class="text-secondary">{{ $admin->email }}</div>
    </div>
</div>
