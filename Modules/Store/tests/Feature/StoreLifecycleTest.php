<?php

use App\Jobs\InstallQueuedModule;
use App\Models\Admin\Admin;
use App\Models\Settings\SettingSection;
use App\Services\Admin\Modules\ModuleInstallations;
use App\Services\Settings\SettingsService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Nwidart\Modules\Contracts\ActivatorInterface;

uses(LazilyRefreshDatabase::class);
test('queued source activation creates Store settings and repeated activation preserves values', function () {
    $this->withoutVite();
    $admin = Admin::factory()->superAdmin()->create();
    $disk = Storage::fake('store-lifecycle');
    $disk->put('statuses.json', json_encode(['Store' => false], JSON_THROW_ON_ERROR));
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json'), 'module-distribution.working_path' => $disk->path('work')]);
    app()->forgetInstance(ActivatorInterface::class);
    Queue::fake([InstallQueuedModule::class]);
    Process::preventStrayProcesses();
    Process::fake(function ($process) {
        if (in_array('db:seed', $process->command, true)) {
            $code = Artisan::call('db:seed', ['--class' => 'Modules\\Store\\Database\\Seeders\\StoreSettingsSeeder', '--force' => true, '--no-interaction' => true]);

            return Process::result(exitCode: $code);
        }

        return Process::result();
    });
    $service = app(ModuleInstallations::class);
    $this->actingAs($admin, 'admin')->post(route('admin.modules.enable', 'Store'))->assertRedirectToRoute('admin.modules.index');
    $id = $service->latest()['id'];
    $service->claim($id, 0);
    $this->artisan('module:install-queued', ['installation' => $id, 'index' => 0])->assertSuccessful();
    $service->finish($id, 0);
    $section = SettingSection::query()->where('key', 'store')->sole();
    $this->get(route('admin.settings.index'))->assertOk()->assertSee('Misafir checkout etkin');
    $payload = $section->definitions->mapWithKeys(fn ($definition): array => [$definition->id => $definition->key === 'store.guest_checkout_enabled' ? true : $definition->default_value])->all();
    app(SettingsService::class)->updateSection($section, ['settings' => $payload], $admin);
    $disk->put('statuses.json', json_encode(['Store' => false]));
    app()->forgetInstance(ActivatorInterface::class);
    $id = $service->enqueueEnable('Store', $admin->id);
    $service->claim($id, 0);
    $this->artisan('module:install-queued', ['installation' => $id, 'index' => 0])->assertSuccessful();
    $service->finish($id, 0);
    expect(app(SettingsService::class)->get('store.guest_checkout_enabled'))->toBeTrue();
    $this->assertDatabaseCount('setting_sections', 1);
    Queue::assertPushed(InstallQueuedModule::class, 2);
    Process::assertRanTimes(fn ($process): bool => in_array('db:seed', $process->command, true), 2);
});
