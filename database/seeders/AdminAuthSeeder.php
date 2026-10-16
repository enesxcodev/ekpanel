<?php

namespace Database\Seeders;

use App\Enums\AdminType\AdminTypeEnum;
use App\Models\Admin\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminAuthSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['email' => 'admin@example.com', 'name' => 'Admin', 'lastname' => 'Kullanıcı', 'type' => AdminTypeEnum::Admin],
            ['email' => 'superadmin@example.com', 'name' => 'Super', 'lastname' => 'Admin', 'type' => AdminTypeEnum::SuperAdmin],
        ];

        foreach ($accounts as $account) {
            $password = Str::password(20);
            $admin = Admin::query()->firstOrCreate(
                ['email' => $account['email']],
                [...$account, 'status' => true, 'password' => $password],
            );

            if ($admin->wasRecentlyCreated) {
                $this->command?->line($admin->email.' | Şifre: '.$password);
            }
        }
    }
}
