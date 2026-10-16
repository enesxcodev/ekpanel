<?php

namespace App\Models\Admin;

use App\Enums\AdminType\AdminTypeEnum;
use Database\Factories\Admin\AdminFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['email', 'name', 'lastname', 'status', 'type', 'password'])]
#[Hidden(['password', 'remember_token'])]
class Admin extends Authenticatable
{
    /** @use HasFactory<AdminFactory> */
    use HasFactory, HasRoles;

    protected string $guard_name = 'admin';

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'type' => AdminTypeEnum::class,
            'password' => 'hashed',
        ];
    }
}
