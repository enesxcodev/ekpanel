<?php

namespace App\Services\Admin\Dashboard;

use App\Models\Admin\Admin;

interface DashboardWidget
{
    /** @return array{id: string, title: string, icon: string, permissions: list<string>, default: bool, order: int, type: string} */
    public function definition(): array;

    /** @return array{value: int|string, description?: string, url?: string} */
    public function data(Admin $admin): array;
}
