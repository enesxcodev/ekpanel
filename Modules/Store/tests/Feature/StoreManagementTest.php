<?php

require_once __DIR__.'/../../database/seeders/StoreSettingsSeeder.php';

use App\Models\Admin\Admin;
use App\Models\Settings\SettingSection;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Modules\Store\Database\Seeders\StoreSettingsSeeder;
use Nwidart\Modules\Contracts\ActivatorInterface;

uses(LazilyRefreshDatabase::class);

function setStoreModuleEnabled(bool $enabled): void
{
    $disk = Storage::fake('store-status');
    $disk->put('statuses.json', json_encode(['Store' => $enabled], JSON_THROW_ON_ERROR));
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    app()->forgetInstance(ActivatorInterface::class);
}

test('store settings appear only while the Store module is enabled', function () {
    $this->seed(StoreSettingsSeeder::class);
    $admin = Admin::factory()->superAdmin()->create();
    $section = SettingSection::query()->where('key', 'store')->sole();

    setStoreModuleEnabled(true);
    $this->actingAs($admin, 'admin')->get(route('admin.settings.index'))->assertOk()->assertSee('Mağaza')->assertSee('Misafir checkout etkin');

    setStoreModuleEnabled(false);
    $this->get(route('admin.settings.index'))->assertOk()->assertDontSee('Mağaza')->assertDontSee('Misafir checkout etkin');
    expect($section->fresh()->key)->toBe('store');
});
