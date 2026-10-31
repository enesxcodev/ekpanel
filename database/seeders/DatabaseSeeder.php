<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\Settings\SettingsSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Modules\Blog\Database\Seeders\BlogDatabaseSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([AdminAuthSeeder::class, SettingsSeeder::class, AdminPermissionSeeder::class, LanguageSeeder::class]);
        if (Schema::hasTable('blog_categories')) {
            $this->call(BlogDatabaseSeeder::class);
        }

        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
