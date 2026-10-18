<?php

namespace Database\Seeders;

use App\Enums\AdminType\AdminTypeEnum;
use App\Models\Admin\Admin;
use Illuminate\Database\Seeder;

class AdminAuthSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['email' => 'admin@example.com', 'name' => 'Admin', 'lastname' => 'Kullanıcı', 'type' => AdminTypeEnum::Admin],
            ['email' => 'superadmin@example.com', 'name' => 'Super', 'lastname' => 'Admin', 'type' => AdminTypeEnum::SuperAdmin],
        ];

        foreach ($accounts as $account) {
            $admin = Admin::query()->firstOrCreate(
                ['email' => $account['email']],
                [...$account, 'status' => true, 'password' => 'Dalgagecmeaq1'],
            );

            if ($admin->wasRecentlyCreated) {
                $this->command?->line($admin->email.' hesabı oluşturuldu.');
            }
        }
    }
}
