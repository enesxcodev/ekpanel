<?php

use App\Models\Admin\Admin;
use App\Models\Media\MediaFile;
use App\Models\Settings\SettingSection;
use App\Services\Settings\SettingsService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Store\Database\Seeders\StoreSettingsSeeder;
use Modules\Store\Services\StoreCartService;
use Nwidart\Modules\Contracts\ActivatorInterface;

function storeStatuses(array $statuses = []): void
{
    $disk = Storage::fake('store-commerce-status');
    $disk->put('statuses.json', json_encode(['Store' => true, 'Products' => true, 'Users' => true, ...$statuses]));
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    app()->forgetInstance(ActivatorInterface::class);
}
function storePayload(array $replace = []): array
{
    return array_replace(['title' => 'Çelik Kapı', 'sku' => 'STORE-1', 'price' => '120,50', 'tracks_stock' => 1, 'stock_quantity' => 10, 'low_stock_threshold' => 2, 'allow_backorders' => 0, 'is_active' => 1, 'has_variants' => 0, 'sort_order' => 0, 'preparation_days' => 2, 'image_media_ids' => []], $replace);
}
function storeImage(string $name): MediaFile
{
    $file = UploadedFile::fake()->image($name);
    $path = $file->store('_media', 'public');

    return MediaFile::factory()->create(['path' => $path, 'name' => $name, 'kind' => 'image', 'mime_type' => 'image/png', 'extension' => 'png', 'size' => Storage::disk('public')->size($path)]);
}
function storeSettings(array $overrides = []): void
{
    test()->seed(StoreSettingsSeeder::class);
    $admin = Admin::factory()->superAdmin()->create();
    $section = SettingSection::query()->where('key', 'store')->sole();
    $values = $section->definitions->mapWithKeys(fn ($definition): array => [$definition->id => $overrides[$definition->key] ?? $definition->default_value])->all();
    app(SettingsService::class)->updateSection($section, ['settings' => $values], $admin);
}
function storeCustomer(array $items, array $extra = []): array
{
    $delivery = $extra['delivery_method'] ?? 'shipping';

    return [...['customer_name' => 'Ada', 'customer_email' => 'ada@example.test', 'customer_phone' => '5551234567', 'delivery_method' => $delivery, 'address' => ['line1' => 'Adres 1', 'city' => 'İstanbul', 'country' => 'TR'], 'checkout_key' => (string) Str::uuid(), 'quote' => app(StoreCartService::class)->quote($items, $delivery)['fingerprint']], ...$extra];
}
