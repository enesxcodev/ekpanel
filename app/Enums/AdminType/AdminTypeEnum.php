<?php

namespace App\Enums\AdminType;

enum AdminTypeEnum: int
{
    case Admin = 0;
    case SuperAdmin = 1;

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::SuperAdmin => 'Süper admin',
        };
    }
}
