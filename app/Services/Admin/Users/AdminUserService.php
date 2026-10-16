<?php

namespace App\Services\Admin\Users;

use App\Enums\AdminType\AdminTypeEnum;
use App\Models\Admin\Admin;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminUserService
{
    /** @param array{name: string, lastname: string, email: string, password: string, type: AdminTypeEnum|string|int, status: bool|string|int} $data */
    public function create(array $data): Admin
    {
        return DB::transaction(function () use ($data): Admin {
            $admin = Admin::query()->create([...$data, 'status' => (bool) $data['status']]);
            $admin->syncRoles($admin->type === AdminTypeEnum::SuperAdmin ? 'super-admin' : 'customer-admin');

            return $admin;
        });
    }

    /** @param array{name: string, lastname: string, email: string, password?: ?string, type: AdminTypeEnum|string|int, status: bool|string|int} $data */
    public function update(Admin $admin, array $data, Admin $actor): Admin
    {
        return DB::transaction(function () use ($admin, $data, $actor): Admin {
            $lockedAdmin = Admin::query()->whereKey($admin->getKey())->lockForUpdate()->firstOrFail();
            $activeSuperAdmins = Admin::query()
                ->where('type', AdminTypeEnum::SuperAdmin->value)
                ->where('status', true)
                ->orderBy('id')
                ->lockForUpdate()
                ->get(['id']);
            $nextType = $data['type'] instanceof AdminTypeEnum
                ? $data['type']
                : AdminTypeEnum::from((int) $data['type']);
            $nextStatus = (bool) $data['status'];
            $willRemainActiveSuperAdmin = $nextType === AdminTypeEnum::SuperAdmin && $nextStatus;

            if ($actor->is($lockedAdmin) && (! $nextStatus || $nextType !== $lockedAdmin->type)) {
                throw ValidationException::withMessages([
                    'status' => 'Kendi hesabınızı devre dışı bırakamaz veya yönetici türünüzü değiştiremezsiniz.',
                ]);
            }

            if (
                $lockedAdmin->type === AdminTypeEnum::SuperAdmin
                && $lockedAdmin->status
                && ! $willRemainActiveSuperAdmin
                && ! $activeSuperAdmins->contains(fn (Admin $superAdmin): bool => $superAdmin->isNot($lockedAdmin))
            ) {
                throw ValidationException::withMessages([
                    'type' => 'En az bir aktif süper admin hesabı bulunmalıdır.',
                ]);
            }

            $password = $data['password'] ?? null;
            unset($data['password']);
            $lockedAdmin->fill([...$data, 'status' => $nextStatus]);

            if (! empty($password)) {
                $lockedAdmin->password = $password;
            }

            $lockedAdmin->save();
            $lockedAdmin->syncRoles($nextType === AdminTypeEnum::SuperAdmin ? 'super-admin' : 'customer-admin');

            return $lockedAdmin;
        });
    }
}
