<?php

namespace App\Policies\Admin;

use App\Enums\AdminType\AdminTypeEnum;
use App\Models\Admin\Admin;

class AdminPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->can('admins.view');
    }

    public function create(Admin $admin): bool
    {
        return $admin->can('admins.create');
    }

    public function update(Admin $admin, Admin $target): bool
    {
        return $target->type !== AdminTypeEnum::SuperAdmin && $admin->can('admins.update');
    }
}
